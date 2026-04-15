<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeSubpromptService
{
    private string $model  = 'claude-sonnet-4-20250514';
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';

    // ══════════════════════════════════════════════════════════════
    // Umum: Panggil Claude API + K-1 (normalisasi JSON) otomatis
    // ══════════════════════════════════════════════════════════════
    private function callClaude(string $systemPrompt, string $userContent, string $code): array
    {
        $apiKey = config('services.anthropic.key');

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ])->timeout(60)->post($this->apiUrl, [
                'model'      => $this->model,
                'max_tokens' => 2000,
                'system'     => $systemPrompt,
                'messages'   => [
                    ['role' => 'user', 'content' => $userContent],
                ],
            ]);

            if ($response->failed()) {
                Log::error("Subprompt [{$code}] API error: " . $response->body());
                return $this->errorResult($code, 'Panggilan API gagal');
            }

            $rawText = $response->json()['content'][0]['text'] ?? '';

            // K-1: Normalisasi JSON (hapus ```json fence → decode)
            return $this->normalizeJson($rawText, $code);

        } catch (\Exception $e) {
            Log::error("Subprompt [{$code}] Exception: " . $e->getMessage());
            return $this->errorResult($code, $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────
    // K-1: Normalisasi JSON
    // ──────────────────────────────────────────────────────────────
    public function normalizeJson(string $raw, string $code = 'K-1'): array
    {
        $cleaned = preg_replace('/^```json\s*/m', '', $raw);
        $cleaned = preg_replace('/^```\s*/m', '', $cleaned);
        $cleaned = trim($cleaned);

        $decoded = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("Subprompt [{$code}] JSON parse error. raw=" . mb_substr($cleaned, 0, 300));
            return $this->errorResult($code, 'Gagal parse JSON', ['raw_snippet' => mb_substr($cleaned, 0, 500)]);
        }

        return $decoded;
    }

    private function errorResult(string $code, string $message, array $extra = []): array
    {
        return array_merge([
            '_error'     => true,
            '_code'      => $code,
            '_message'   => $message,
            '_timestamp' => now()->toIso8601String(),
        ], $extra);
    }

    // ══════════════════════════════════════════════════════════════
    // A-1: Penilaian Sertifikasi HRI
    // ══════════════════════════════════════════════════════════════
    public function runA1(array $input): array
    {
        $system = <<<PROMPT
Anda adalah AI Divisi Manajemen Penilaian HRI. Tugas Anda adalah membaca catatan investigasi, dokumen bukti, dan riwayat sebelumnya dari pemohon, lalu memberikan bantuan penilaian sertifikasi.
Anda BUKAN pengambil keputusan akhir perusahaan.

[PRINSIP UTAMA]
1. Hanya membuat penilaian dalam batas persetujuan yang diberikan oleh pemohon.
2. Tidak menampilkan informasi pribadi secara berlebihan.
3. Jika ada indikasi pelanggaran ketentuan investigasi, tangani sebagai risiko tinggi.
4. Hal yang tidak diketahui harus dinyatakan sebagai "tidak diketahui", "belum dikonfirmasi", atau "perlu verifikasi".
5. Dugaan/asumsi harus selalu dinyatakan sebagai "hipotesis".
6. Jangan menulis konten yang belum disetujui seolah-olah sudah dikonfirmasi secara eksternal.

[KLASIFIKASI TINGKAT BUKTI]
A: Dokumen primer, dokumen resmi, sudah dikonfirmasi secara resmi
B: Konsisten dari beberapa sumber terpercaya
C: Satu sumber atau dasar terbatas
D: Belum dikonfirmasi, informasi sementara

[EKSPRESI YANG DILARANG]
"melanggar hukum", "pasti menang di pengadilan", "pemecatan sah", "100%", "dijamin", "pasti"

Kembalikan HANYA dalam format JSON. Tanpa penjelasan atau teks pendahuluan.
PROMPT;

        $schema = '{
  "case_id": "",
  "review_status": "approved_candidate | return_candidate | hold_candidate | reject_candidate",
  "identity_verification": { "status": "verified | partially_verified | unverified | unclear", "score": 0, "reason": "" },
  "education_verification": { "status": "verified | partially_verified | unverified | unclear", "score": 0, "reason": "" },
  "employment_verification": { "status": "verified | partially_verified | unverified | unclear", "score": 0, "reason": "" },
  "qualification_verification": { "status": "verified | partially_verified | unverified | unclear", "score": 0, "reason": "" },
  "conduct_review": { "status": "acceptable | caution | unclear | excluded", "score": 0, "reason": "" },
  "truthfulness_score": 0,
  "consistency_score": 0,
  "hri_suitability_score": 0,
  "work_ability_evaluation": { "score": 0, "summary": "" },
  "claude_overall_judgment": { "score": 0, "summary": "" },
  "evidence_level_summary": { "A": 0, "B": 0, "C": 0, "D": 0, "comment": "" },
  "violation_flags": [],
  "unconfirmed_items": [],
  "return_recommendation": false,
  "return_reasons": [],
  "human_takeover_required": false,
  "internal_notes": "",
  "external_display_summary": ""
}';

        $user = "Periksa data berikut dan keluarkan dalam format JSON yang ditentukan.\n\n" .
                json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        $result = $this->callClaude($system, $user, 'A-1');

        // Jika riwayat pengembalian >= 3 kali → paksa alih ke pemrosesan manusia
        $returnCount = count($input['prior_return_history'] ?? []);
        if ($returnCount >= 3) {
            $result['return_recommendation']   = false;
            $result['human_takeover_required'] = true;
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════
    // A-2: Penilaian Pengembalian Berkas
    // ══════════════════════════════════════════════════════════════
    public function runA2(array $input): array
    {
        $system = <<<PROMPT
Anda adalah AI Divisi Manajemen Penilaian HRI. Berdasarkan hasil penilaian sertifikasi, tugas Anda adalah menentukan apakah pengembalian berkas diperlukan dan apa yang perlu diperbaiki secara berurutan berdasarkan prioritas.
Anda BUKAN pengambil keputusan akhir.

[ATURAN JUMLAH PENGEMBALIAN]
- prior_return_count 0-1: Kandidat pengembalian biasa
- prior_return_count 2: Kandidat pengembalian ke-3, notify_lee = true
- prior_return_count 3 ke atas: human_takeover_required = true, pengulangan otomatis dilarang

[KATEGORI ALASAN PENGEMBALIAN]
missing_identity_data / missing_education_data / missing_employment_data /
missing_qualification_data / document_unclear / inconsistency_detected /
insufficient_evidence / prohibited_investigation_risk / consent_scope_problem /
external_confirmation_failure / repeated_incomplete_submission / other

[LARANGAN]
Jangan gunakan pernyataan hukum, pernyataan sanksi, atau ekspresi seperti "pasti", "dijamin", "sudah terbukti"

Kembalikan HANYA dalam format JSON. Tanpa penjelasan atau teks pendahuluan.
PROMPT;

        $schema = '{
  "case_id": "",
  "return_required": false,
  "return_category": [],
  "priority_level": "low | normal | high | critical",
  "main_reason_summary": "",
  "fix_instructions": [
    { "priority": 1, "item": "", "instruction": "", "reason": "" }
  ],
  "resubmission_possible": true,
  "high_risk_case": false,
  "notify_lee": false,
  "human_takeover_required": false,
  "draft_return_message": "",
  "internal_notes": "",
  "risk_flags": []
}';

        $user = "Analisis data berikut dan keluarkan dalam format JSON yang ditentukan.\n\n" .
                json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        $result = $this->callClaude($system, $user, 'A-2');

        // Terapkan aturan jumlah pengembalian secara paksa
        $priorCount = (int)($input['prior_return_count'] ?? 0);
        if ($priorCount >= 3) {
            $result['human_takeover_required'] = true;
            $result['return_required']         = false;
        } elseif ($priorCount === 2) {
            $result['notify_lee'] = true;
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════
    // D-1: Klasifikasi Pertanyaan Anggota Umum
    // ══════════════════════════════════════════════════════════════
    public function runD1(array $input): array
    {
        $system = <<<PROMPT
Anda adalah AI dukungan pelanggan HRI. Tugas Anda adalah mengklasifikasikan pertanyaan dari anggota umum, menentukan apakah dapat dijawab langsung, apakah perlu verifikasi identitas, dan apakah perlu eskalasi.
Anda BUKAN penjawab akhir.

[ATURAN PENILAIAN]
- complaint / objection: prioritas minimal "high"
- answer_prohibited = true: paksa can_answer_immediately = false
- Pertanyaan hukum/pajak: requires_legal_review = true
- Pertanyaan PDP: requires_pdp_review = true
- Permintaan refund: requires_supervisor_review = true
- Pendapat hukum yang belum disetujui / penilaian pajak / isi lengkap standar penilaian internal / informasi kasus perusahaan lain → answer_prohibited = true

[KATEGORI PERTANYAAN]
account_registration / login_issue / verification_application / verification_result /
verified_resume / payment_issue / refund_request / privacy_request / deletion_request /
correction_request / complaint / objection / technical_issue / job_application_issue /
general_guidance / legal_related / tax_related / other

Kembalikan HANYA dalam format JSON. Tanpa penjelasan atau teks pendahuluan.
PROMPT;

        $schema = '{
  "inquiry_id": "",
  "category": "",
  "priority": "low | normal | high | urgent",
  "identity_check_required": false,
  "can_answer_immediately": false,
  "requires_supervisor_review": false,
  "requires_pdp_review": false,
  "requires_external_accounting_review": false,
  "requires_legal_review": false,
  "should_escalate_to_complaint_handling": false,
  "answer_prohibited": false,
  "reason_summary": "",
  "recommended_next_action": "",
  "draft_reply_direction": "",
  "risk_flags": []
}';

        $user = "Klasifikasikan pertanyaan berikut dan keluarkan dalam format JSON yang ditentukan.\n\n" .
                json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        $result = $this->callClaude($system, $user, 'D-1');

        if (!empty($result['answer_prohibited'])) {
            $result['can_answer_immediately'] = false;
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════
    // D-3: Klasifikasi Pertanyaan Anggota Perusahaan
    // ══════════════════════════════════════════════════════════════
    public function runD3(array $input): array
    {
        $system = <<<PROMPT
Anda adalah AI dukungan pelanggan HRI. Tugas Anda adalah mengklasifikasikan pertanyaan dari anggota perusahaan: siapa yang boleh menjawab, apakah dapat dijawab langsung, perlu verifikasi wewenang, dan perlu persetujuan atasan.
Anda BUKAN penjawab akhir.

[HAL YANG HARUS DITANGANI DENGAN HATI-HATI]
- Permintaan diskon / syarat khusus → requires_special_escalation = true
- Pertanyaan tentang anggota lain / kandidat lain → answer_prohibited = true
- Permintaan pengungkapan standar penilaian internal / logika penilaian → answer_prohibited = true
- Permintaan pendapat hukum / pajak → requires_legal_review = true
- Permintaan fitur di luar kontrak → limited_answer_only = true
- Permintaan refund → requires_supervisor_review = true

Kembalikan HANYA dalam format JSON. Tanpa penjelasan atau teks pendahuluan.
PROMPT;

        $schema = '{
  "inquiry_id": "",
  "category": "",
  "priority": "low | normal | high | urgent",
  "can_answer_immediately": false,
  "requires_contact_authority_check": false,
  "requires_supervisor_review": false,
  "requires_legal_review": false,
  "requires_pdp_review": false,
  "requires_external_accounting_review": false,
  "requires_special_escalation": false,
  "answer_prohibited": false,
  "limited_answer_only": false,
  "reason_summary": "",
  "recommended_next_action": "",
  "draft_reply_direction": "",
  "risk_flags": []
}';

        $user = "Klasifikasikan pertanyaan berikut dan keluarkan dalam format JSON yang ditentukan.\n\n" .
                json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        $result = $this->callClaude($system, $user, 'D-3');

        if (!empty($result['answer_prohibited'])) {
            $result['can_answer_immediately'] = false;
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════
    // G-1: Pembuatan Penawaran Harga (Estimasi)
    // ══════════════════════════════════════════════════════════════
    public function runG1(array $input): array
    {
        $system = <<<PROMPT
Anda adalah AI asisten penjualan HRI. Berdasarkan layanan yang akan diberikan, tugas Anda adalah menyusun draft penawaran harga untuk konfirmasi internal atau sebelum dikirim ke pihak luar.
Anda BUKAN pihak yang memberikan persetujuan akhir.
Penawaran harga adalah dokumen awal dari kontrak, penagihan, pembayaran, dan proses akuntansi.

[LARANGAN]
- Jangan gunakan ekspresi jaminan seperti "pasti", "100%", "dijamin", "sudah pasti"
- Jangan mencantumkan pendapat hukum atau penilaian pajak yang belum disetujui
- Cantumkan dalam approval_note bahwa penagihan tidak boleh dimulai sebelum penerimaan pesanan dikonfirmasi

[SUSUNAN ISI PENAWARAN (dalam estimate_body)]
① Judul ② Penerima ③ Ringkasan layanan ④ Ruang lingkup yang termasuk
⑤ Ruang lingkup yang tidak termasuk ⑥ Jumlah & ada/tidaknya diskon
⑦ Syarat pembayaran ⑧ Masa berlaku ⑨ Catatan khusus
⑩ Perlu/tidaknya kontrak & NDA ⑪ Item yang perlu dikonfirmasi

[KANDIDAT risk_flags]
discount_requires_approval / contract_review_recommended / tax_confirmation_recommended /
unclear_scope / special_condition_risk / high_value_attention / missing_input_data

[KANDIDAT JENIS LAYANAN]
hri_verification / verified_resume / corporate_membership / job_posting /
paid_viewing / investigation_service / risk_management / foreign_company_support /
system_development / employee_management_system / maintenance_operation / consulting / other

Kembalikan HANYA dalam format JSON. Tanpa penjelasan atau teks pendahuluan.
PROMPT;

        $schema = '{
  "estimate_id": "",
  "estimate_number": "",
  "title": "",
  "client_name": "",
  "service_type": "",
  "validity_days": 14,
  "contract_required": false,
  "nda_required": false,
  "risk_flags": [],
  "scope_included_summary": "",
  "scope_excluded_summary": "",
  "price_summary": {
    "currency": "IDR",
    "subtotal": "",
    "discount_exists": false,
    "discount_amount": "",
    "final_amount": "",
    "tax_note": ""
  },
  "payment_terms_summary": "",
  "milestone_terms_summary": "",
  "special_notes_summary": "",
  "estimate_body": "",
  "cover_email_draft": "",
  "approval_note": "",
  "missing_items": []
}';

        $user = "Buat draft penawaran harga berdasarkan informasi berikut dan keluarkan dalam format JSON yang ditentukan.\n\n" .
                json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        return $this->callClaude($system, $user, 'G-1');
    }

    // ══════════════════════════════════════════════════════════════
    // I-3: Penerbitan Serial Verified Resume
    // ══════════════════════════════════════════════════════════════
    public function runI3(array $input): array
    {
        $system = <<<PROMPT
Anda adalah AI manajemen sistem HRI. Tugas Anda adalah menentukan apakah serial number untuk resume terverifikasi dapat diterbitkan, menyusun catatan manajemen versi dan status.
Penomoran aktual dilakukan oleh sistem Laravel, bukan oleh Anda.

[FORMAT NOMOR]
HRI-VR-[YYYYMM]-[NNNNNN]-[V]
Contoh: HRI-VR-202604-000127-A

[ATURAN KODE VERSI]
- Penerbitan pertama: A
- Penerbitan ulang (ada perubahan isi): B dan seterusnya secara alfabetis
- Penerbitan ulang karena hilang (tanpa perubahan isi): versi sama + nomor cabang

[ATURAN WAJIB]
- Jika kasus belum disetujui (review_status != approved): paksa issuable = false
- Penomoran aktual tidak dilakukan dalam prompt ini (hanya saran format & catatan manajemen)

Kembalikan HANYA dalam format JSON. Tanpa penjelasan atau teks pendahuluan.
PROMPT;

        $schema = '{
  "case_id": "",
  "issuance_type": "initial | reissue | replacement | cancellation",
  "issuable": false,
  "reason_if_not_issuable": "",
  "suggested_serial_format": "HRI-VR-[YYYYMM]-[NNNNNN]-[V]",
  "version_code": "",
  "prior_serial_reference": "",
  "status_after_issuance": "valid | superseded | cancelled",
  "validity_note": "",
  "qr_url_template": "",
  "internal_notes": "",
  "risk_flags": []
}';

        $user = "Periksa data berikut dan keluarkan dalam format JSON yang ditentukan.\n\n" .
                json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        $result = $this->callClaude($system, $user, 'I-3');

        // Kasus yang belum disetujui → paksa issuable = false
        $reviewStatus = $input['review_status'] ?? '';
        if ($reviewStatus !== 'approved') {
            $result['issuable']               = false;
            $result['reason_if_not_issuable'] = 'Kasus belum disetujui, penerbitan tidak dapat dilakukan (review_status: ' . $reviewStatus . ')';
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════
    // K-2: Penyamaran Data untuk Tampilan Eksternal
    // ══════════════════════════════════════════════════════════════
    public function runK2(array $internalData): array
    {
        $system = <<<PROMPT
Anda adalah AI penyamaran data HRI. Tugas Anda adalah mengekstrak hanya informasi yang boleh ditampilkan kepada pihak eksternal (anggota perusahaan / anggota umum) dari data penilaian internal.

[INFORMASI YANG HARUS SELALU DIHAPUS]
- internal_notes (catatan internal)
- Nama staf penilai / ID staf
- Logika penilaian internal / detail rumus penghitungan skor
- Informasi perusahaan / kasus lain
- Metode investigasi / alat investigasi
- Ekspresi yang menyatakan kepastian hukum
- Informasi yang belum dikonfirmasi / hipotesis
- Detail violation_flags (hanya ringkasan yang boleh ditampilkan)

Kembalikan HANYA dalam format JSON. Tanpa penjelasan atau teks pendahuluan.
PROMPT;

        $schema = '{
  "external_display_only": {},
  "masked_fields_count": 0,
  "display_safe": true,
  "masking_notes": ""
}';

        $user = "Ekstrak hanya informasi yang aman untuk ditampilkan secara eksternal dari data internal berikut dan keluarkan dalam format JSON yang ditentukan.\n\n" .
                json_encode($internalData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        return $this->callClaude($system, $user, 'K-2');
    }

    // ══════════════════════════════════════════════════════════════
    // K-3: Penyusunan Tampilan untuk Menunggu Persetujuan Manusia
    // ══════════════════════════════════════════════════════════════
    public function runK3(array $draftData, string $approverRole = 'admin_user'): array
    {
        $system = <<<PROMPT
Anda adalah AI asisten operasional HRI. Tugas Anda adalah menyusun ulang draft yang dihasilkan oleh Claude agar mudah diperiksa dan diputuskan oleh pihak yang berwenang memberikan persetujuan.

[PRINSIP PENYUSUNAN]
- Daftarkan dengan jelas item-item yang memerlukan persetujuan
- Pisahkan antara yang dihasilkan AI dan yang perlu dikonfirmasi oleh manusia
- Jika ada ekspresi seperti "pasti", "sudah pasti", "dijamin", catat di risk_warnings
- Sesuaikan item tampilan berdasarkan peran pihak yang memberikan persetujuan

Kembalikan HANYA dalam format JSON. Tanpa penjelasan atau teks pendahuluan.
PROMPT;

        $schema = '{
  "summary_for_approver": "",
  "items_requiring_approval": [],
  "items_ai_generated": [],
  "items_human_must_verify": [],
  "risk_warnings": [],
  "recommended_action": "approve | modify | reject | hold",
  "approval_deadline_suggestion": "",
  "formatted_preview": {}
}';

        $user = "Susun ulang draft berikut agar mudah diperiksa oleh pihak yang berwenang, dan keluarkan dalam format JSON yang ditentukan.\n\n" .
                json_encode([
                    'draft_data'    => $draftData,
                    'approver_role' => $approverRole,
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        return $this->callClaude($system, $user, 'K-3');
    }

    // ─────────────────────────────────────────────
    // H-1: 会計月次送付補助用
    // ─────────────────────────────────────────────
    public function organizeMonthlyAccounting(array $input): array
    {
        $system = <<<PROMPT
Anda adalah asisten akuntansi internal HRI System.
Tugas Anda adalah membantu merangkum data keuangan bulanan dan menyiapkan dokumen untuk dikirim ke perusahaan akuntan eksternal.

PRINSIP UTAMA:
1. Jangan membuat keputusan pajak secara sepihak. Keputusan pajak adalah wewenang akuntan eksternal.
2. Jangan membuat pernyataan pasti seperti "pasti tidak ada masalah pajak".
3. Jika ada item yang tidak biasa, catat sebagai catatan risiko dan rekomendasikan konfirmasi ke akuntan eksternal.
4. Ringkasan harus akurat dan berdasarkan data yang diberikan saja.
5. Surat pengantar harus formal dan profesional dalam Bahasa Indonesia.
6. Kembalikan HANYA dalam format JSON. Tanpa teks penjelasan.
PROMPT;

        $schema = '{
  "report_month": "",
  "summary": {
    "total_revenue_idr": 0,
    "total_pending_idr": 0,
    "total_expenses_idr": 0,
    "net_balance_idr": 0,
    "paid_invoice_count": 0,
    "pending_invoice_count": 0
  },
  "anomaly_notes": "",
  "tax_related_items": [],
  "checklist": [
    { "item": "", "status": "required | recommended | optional" }
  ],
  "draft_cover_letter": "",
  "risk_flags": [],
  "disclaimer": ""
}';

        $user = "Rangkum data keuangan bulanan berikut dan siapkan dokumen untuk akuntan eksternal.\n\n" .
                json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) .
                "\n\nFormat output:\n" . $schema;

        return $this->callClaude($system, $user, 'H-1');
    }
}