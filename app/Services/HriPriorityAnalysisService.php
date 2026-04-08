<?php

namespace App\Services;

use App\Models\CertificationRequest;
use App\Models\InvestigationPriorityReport;
use App\Models\InvestigationItem;
use App\Models\AiActivityLog;
use App\Models\AiDataTransferLog;
use App\Models\PersonalDataAccessLog;
use App\Models\ApplicantProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HriPriorityAnalysisService
{
    private string $model         = 'claude-sonnet-4-20250514';
    private string $promptVersion = 'priority-v1.0';

    // ─────────────────────────────────────────────────────────────────
    // メインエントリ
    // ─────────────────────────────────────────────────────────────────

    public function analyze(int $certRequestId): ?InvestigationPriorityReport
    {
        $request = CertificationRequest::with([
            'educationHistories',
            'workHistories',
            'certifications',
            'applicantProfile', // ★ CertificationRequest モデルにリレーション定義済み
        ])->find($certRequestId);

        if (!$request) {
            Log::error("HriPriorityAnalysis: certRequestId={$certRequestId} not found");
            return null;
        }

        // ① 匿名化データを構築して Anthropic API へ送信
        $anonymizedData = $this->buildAnonymizedData($request);

        $startTime   = microtime(true);
        $rawResponse = $this->callAnthropicApi($anonymizedData, $request->case_no);
        $elapsedMs   = (int)((microtime(true) - $startTime) * 1000);

        if (!$rawResponse) {
            Log::error("HriPriorityAnalysis: no API response certRequestId={$certRequestId}");
            return null;
        }

        $parsed = $this->parseResponse($rawResponse);

        // ② investigation_priority_reports に保存
        $report = InvestigationPriorityReport::create([
            'case_no'                  => $request->case_no,
            'certification_request_id' => $certRequestId,
            'ai_model'                 => $this->model,
            'prompt_version'           => $this->promptVersion,
            'priority_high_json'       => $parsed['priority_high']    ?? [],
            'priority_medium_json'     => $parsed['priority_medium']  ?? [],
            'priority_low_json'        => $parsed['priority_low']     ?? [],
            'conduct_contacts_json'    => $parsed['conduct_contacts'] ?? [],
            'risk_flags_json'          => $parsed['risk_flags']       ?? [],
            'ai_analysis_summary'      => $parsed['summary']          ?? '',
            'generated_at'             => now(),
        ]);

        // ③ investigation_items を自動セット
        $this->autoSetInvestigationItems($request);

        $inputTokens  = $rawResponse['usage']['input_tokens']  ?? 0;
        $outputTokens = $rawResponse['usage']['output_tokens'] ?? 0;

        // ④ ai_activity_logs に記録
        try {
            AiActivityLog::create([
                'log_type'             => 'priority_analysis',
                'related_type'         => 'CertificationRequest',
                'related_id'           => $certRequestId,
                'triggered_by_user_id' => null,
                'triggered_by_role'    => 'system',
                'model_name'           => $this->model,
                'prompt_version'       => $this->promptVersion,
                'input_summary'        => 'AI事前分析: ' . $request->case_no,
                'output_summary'       => $parsed['summary'] ?? '',
                'tokens_input'         => $inputTokens,
                'tokens_output'        => $outputTokens,
                'tokens_total'         => $inputTokens + $outputTokens,
                'latency_ms'           => $elapsedMs,
                'status'               => 'success',
            ]);
        } catch (\Exception $e) {
            Log::warning("ai_activity_logs 保存失敗: " . $e->getMessage());
        }

        // ⑤ ai_data_transfer_logs に記録
        try {
            AiDataTransferLog::create([
                'certification_request_id' => $certRequestId,
                'transfer_purpose'         => 'priority_analysis',
                'ai_model'                 => $this->model,
                'ai_provider'              => 'anthropic',
                'data_categories_sent'     => json_encode(['education', 'work', 'certification']),
                'is_anonymized'            => true,
                'legal_basis'              => 'consent',
                'transferred_at'           => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning("ai_data_transfer_logs 保存失敗: " . $e->getMessage());
        }

        // ⑥ personal_data_access_logs に記録
        try {
            PersonalDataAccessLog::create([
                'case_no'          => $request->case_no,
                'accessor_user_id' => null,
                'accessor_role'    => 'ai',
                'target_user_id'   => $request->user_id,
                'data_type'        => 'full_profile',
                'action'           => 'ai_send',
                'access_reason'    => 'investigation',
                'related_id'       => $certRequestId,
                'accessed_at'      => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning("personal_data_access_logs 保存失敗: " . $e->getMessage());
        }

        return $report;
    }

    // ─────────────────────────────────────────────────────────────────
    // investigation_items 自動セット
    // ─────────────────────────────────────────────────────────────────

    private function autoSetInvestigationItems(CertificationRequest $request): void
    {
        $profile = $request->applicantProfile
            ?? ApplicantProfile::where('user_id', $request->user_id)->first();

        // ── basic_info ──────────────────────────────────────────────
        $basicFields = [
            'full_name'       => $profile?->full_name,
            'nik'             => $profile?->nik,
            'ktp_address'     => $profile?->ktp_address,
            'gender'          => $profile?->gender,
            'marital_status'  => $profile?->marital_status,
            'nationality'     => $profile?->nationality,
            'birth_date'      => $profile?->birth_date,
            'current_address' => $profile?->current_address,
            'phone_number'    => $profile?->phone_number,
            'whatsapp_number' => $profile?->whatsapp_number,
        ];

        foreach ($basicFields as $fieldName => $value) {
            $validity = !empty($value) ? 'VALID'          : 'UNVERIFIED';
            $notes    = !empty($value) ? 'AI自動確認：データあり' : 'データ未入力のため未確認';
            $this->upsertItem($request, 'basic_info', $fieldName, $validity, $notes);
        }

        // ── education ───────────────────────────────────────────────
        foreach ($request->educationHistories as $i => $edu) {
            $fields = [
                'school_name'           => $edu->school_name,
                'education_level'       => $edu->education_level,
                'school_location'       => $edu->school_location,
                'degree'                => $edu->degree,
                'degree_name'           => $edu->degree_name,
                'enrollment_date'       => $edu->enrollment_date,
                'graduation_date'       => $edu->graduation_date,
                'graduation_status'     => $edu->graduation_status,
                'ipk_gpa'               => $edu->ipk_gpa,
                'academic_achievements' => $edu->academic_achievements,
            ];

            foreach ($fields as $fieldName => $value) {
                $validity = 'UNVERIFIED';
                $notes    = 'AI自動判別：要調査員確認';

                if ($fieldName === 'graduation_date'
                    && !empty($edu->enrollment_date)
                    && !empty($edu->graduation_date)) {
                    try {
                        $enroll = Carbon::parse($edu->enrollment_date);
                        $grad   = Carbon::parse($edu->graduation_date);
                        $validity = $grad->greaterThan($enroll) ? 'VALID'    : 'INVALID';
                        $notes    = $grad->greaterThan($enroll)
                            ? 'AI自動確認：卒業日 > 入学日（正常）'
                            : 'AI自動検出：卒業日が入学日より前（要確認）';
                    } catch (\Exception $e) {
                        $validity = 'UNVERIFIED';
                    }
                } elseif (!empty($value)) {
                    $validity = 'VALID';
                    $notes    = 'AI自動確認：データあり';
                }

                $this->upsertItem($request, 'education', "edu_{$i}_{$fieldName}", $validity, $notes);
            }
        }

        // ── work ────────────────────────────────────────────────────
        $workList = $request->workHistories->toArray();

        foreach ($request->workHistories as $i => $work) {
            $fields = [
                'company_name'            => $work->company_name,
                'company_address'         => $work->company_address,
                'department_position'     => $work->department_position,
                'employment_type'         => $work->employment_type,
                'employment_start_date'   => $work->employment_start_date,
                'employment_end_date'     => $work->employment_end_date,
                'job_description'         => $work->job_description,
                'resignation_reason'      => $work->resignation_reason,
                'employment_achievements' => $work->employment_achievements,
                'supervisor_full_name'    => $work->supervisor_full_name,
                'supervisor_position'     => $work->supervisor_position,
                'supervisor_phone'        => $work->supervisor_phone,
            ];

            foreach ($fields as $fieldName => $value) {
                $validity = 'UNVERIFIED';
                $notes    = 'AI自動判別：要調査員確認';

                if ($fieldName === 'employment_end_date') {
                    if (empty($value)) {
                        $validity = 'VALID';
                        $notes    = 'AI自動確認：在職中（終了日なし）';
                    } else {
                        try {
                            $start    = Carbon::parse($work->employment_start_date);
                            $end      = Carbon::parse($work->employment_end_date);
                            $validity = $end->greaterThan($start) ? 'VALID'    : 'INVALID';
                            $notes    = $end->greaterThan($start)
                                ? 'AI自動確認：退職日 > 入社日（正常）'
                                : 'AI自動検出：退職日が入社日より前（要確認）';
                        } catch (\Exception $e) {
                            $validity = 'UNVERIFIED';
                        }
                    }
                } elseif (in_array($fieldName, ['supervisor_full_name', 'supervisor_position', 'supervisor_phone'])) {
                    $validity = 'UNVERIFIED';
                    $notes    = '要調査員確認：上司への電話確認が必要';
                } elseif (!empty($value)) {
                    $validity = 'VALID';
                    $notes    = 'AI自動確認：データあり';
                }

                $this->upsertItem($request, 'work', "work_{$i}_{$fieldName}", $validity, $notes);
            }

            if (!empty($work->employment_start_date)) {
                $this->checkWorkOverlap($request, $i, $work, $workList);
            }
        }

        // ── certification ───────────────────────────────────────────
        foreach ($request->certifications as $i => $cert) {
            $fields = [
                'certificate_name'     => $cert->certificate_name,
                'issuing_organization' => $cert->issuing_organization,
                'issue_date'           => $cert->issue_date,
                'expiration_date'      => $cert->expiration_date,
                'certificate_score'    => $cert->certificate_score,
                'certificate_notes'    => $cert->certificate_notes,
            ];

            foreach ($fields as $fieldName => $value) {
                $validity = 'UNVERIFIED';
                $notes    = 'AI自動判別：要調査員確認';

                if ($fieldName === 'expiration_date') {
                    if (empty($value)) {
                        $validity = 'VALID';
                        $notes    = 'AI自動確認：有効期限なし（永久資格）';
                    } else {
                        try {
                            $expiry   = Carbon::parse($value);
                            $validity = $expiry->isFuture() ? 'VALID'    : 'INVALID';
                            $notes    = $expiry->isFuture()
                                ? 'AI自動確認：有効期限内（' . $expiry->format('Y-m-d') . '）'
                                : 'AI自動検出：有効期限切れ（' . $expiry->format('Y-m-d') . '）';
                        } catch (\Exception $e) {
                            $validity = 'UNVERIFIED';
                        }
                    }
                } elseif (!empty($value)) {
                    $validity = 'VALID';
                    $notes    = 'AI自動確認：データあり';
                }

                $this->upsertItem($request, 'certification', "cert_{$i}_{$fieldName}", $validity, $notes);
            }
        }

        // ── conduct（全てUNVERIFIED・手動確認必須） ──────────────────
        foreach ($request->workHistories as $i => $work) {
            $conductFields = [
                'stabilitas_kehadiran' => '勤怠安定性：調査員が上司に電話確認してください',
                'kepatuhan_instruksi'  => '指示遵守：調査員が上司に電話確認してください',
                'kerja_sama_tim'       => 'チームワーク：調査員が上司に電話確認してください',
                'sikap_kerja'          => '業務態度：調査員が上司に電話確認してください',
                'pelanggaran_disiplin' => '規律違反歴：調査員が上司に電話確認してください',
            ];

            foreach ($conductFields as $fieldName => $note) {
                $this->upsertItem($request, 'conduct', "conduct_{$i}_{$fieldName}", 'UNVERIFIED', $note);
            }
        }

        if ($request->workHistories->isEmpty()) {
            $this->upsertItem(
                $request,
                'conduct',
                'conduct_no_work_history',
                'UNVERIFIED',
                '職歴なし：素行確認不可。基本情報のみで審査してください。'
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 在職期間の重複チェック
    // ─────────────────────────────────────────────────────────────────

    private function checkWorkOverlap(
        CertificationRequest $request,
        int $currentIndex,
        $currentWork,
        array $workList
    ): void {
        $currentStart = Carbon::parse($currentWork->employment_start_date);
        $currentEnd   = !empty($currentWork->employment_end_date)
            ? Carbon::parse($currentWork->employment_end_date)
            : Carbon::now();

        foreach ($workList as $j => $otherWork) {
            if ($j <= $currentIndex) continue;
            if (empty($otherWork['employment_start_date'])) continue;

            $otherStart = Carbon::parse($otherWork['employment_start_date']);
            $otherEnd   = !empty($otherWork['employment_end_date'])
                ? Carbon::parse($otherWork['employment_end_date'])
                : Carbon::now();

            if ($currentStart->lessThan($otherEnd) && $currentEnd->greaterThan($otherStart)) {
                $this->upsertItem(
                    $request,
                    'work',
                    "work_{$currentIndex}_overlap_with_{$j}",
                    'INVALID',
                    "AI自動検出：職歴{$currentIndex}と職歴{$j}の在職期間が重複しています（要確認）"
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // investigation_items の upsert
    // ─────────────────────────────────────────────────────────────────

    private function upsertItem(
        CertificationRequest $request,
        string $category,
        string $itemName,
        string $validity,
        string $notes
    ): void {
        try {
            InvestigationItem::updateOrCreate(
                [
                    'certification_request_id' => $request->id,
                    'item_name'                => $itemName,
                ],
                [
                    'case_no'    => $request->case_no,
                    'category'   => $category,
                    'validity'   => $validity,
                    'notes'      => $notes,
                    'checked_by' => null,
                    'checked_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::warning("investigation_items upsert失敗 [{$itemName}]: " . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 匿名化データの構築
    // ─────────────────────────────────────────────────────────────────

    private function buildAnonymizedData(CertificationRequest $request): array
    {
        $education = $request->educationHistories->map(fn($e) => [
            'level'             => $e->education_level,
            'school'            => $e->school_name,
            'location'          => $e->school_location,
            'degree'            => $e->degree,
            'major'             => $e->degree_name,
            'enrollment_date'   => $e->enrollment_date,
            'graduation_date'   => $e->graduation_date,
            'graduation_status' => $e->graduation_status,
            'gpa'               => $e->ipk_gpa,
            'achievements'      => $e->academic_achievements,
        ])->toArray();

        $work = $request->workHistories->map(fn($w) => [
            'company'             => $w->company_name,
            'position'            => $w->department_position,
            'employment_type'     => $w->employment_type,
            'start_date'          => $w->employment_start_date,
            'end_date'            => $w->employment_end_date,
            'duties'              => $w->job_description,
            'resignation_reason'  => $w->resignation_reason,
            'achievements'        => $w->employment_achievements,
            'has_supervisor_info' => !empty($w->supervisor_full_name),
        ])->toArray();

        $certifications = $request->certifications->map(fn($c) => [
            'name'         => $c->certificate_name,
            'organization' => $c->issuing_organization,
            'issued_date'  => $c->issue_date,
            'valid_until'  => $c->expiration_date,
            'score'        => $c->certificate_score,
        ])->toArray();

        $profile = $request->applicantProfile;

        return [
            'case_id'        => $request->case_no,
            'basic_info'     => [
                'has_nik'        => !empty($profile?->nik),
                'has_ktp'        => !empty($profile?->ktp_card),
                'has_address'    => !empty($profile?->current_address),
                'has_phone'      => !empty($profile?->phone_number),
                'has_whatsapp'   => !empty($profile?->whatsapp_number),
                'has_photo'      => !empty($profile?->profile_photo),
                'nationality'    => $profile?->nationality,
                'marital_status' => $profile?->marital_status,
            ],
            'education'      => $education,
            'work'           => $work,
            'certifications' => $certifications,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // Anthropic API 呼び出し
    // ─────────────────────────────────────────────────────────────────

    private function callAnthropicApi(array $data, string $caseNo): ?array
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            Log::error('HriPriorityAnalysis: APIキー未設定');
            return null;
        }

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'      => $this->model,
                'max_tokens' => 2000,
                'messages'   => [
                    ['role' => 'user', 'content' => $this->buildPrompt($data, $caseNo)],
                ],
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            Log::error("HriPriorityAnalysis: cURLエラー: {$curlErr}");
            return null;
        }

        if ($httpCode !== 200 || !$response) {
            Log::error("HriPriorityAnalysis: APIエラー httpCode={$httpCode} body={$response}");
            return null;
        }

        return json_decode($response, true);
    }

    // ─────────────────────────────────────────────────────────────────
    // プロンプト構築
    // ─────────────────────────────────────────────────────────────────

    private function buildPrompt(array $data, string $caseNo): string
    {
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
Anda adalah asisten investigasi HR yang bertugas menganalisis data resume untuk menentukan prioritas investigasi.

Data berikut adalah data resume yang diajukan oleh pelamar (ID Kasus: {$caseNo}).
Data ini sudah dianonimkan dan tidak mengandung informasi yang dapat langsung mengidentifikasi individu.

=== DATA RESUME ===
{$dataJson}
===================

Berdasarkan data di atas, analisis dan tentukan prioritas investigasi.
Perhatikan hal-hal berikut:
- Apakah ada celah waktu (gap) yang mencurigakan di antara riwayat kerja?
- Apakah periode pendidikan dan kerja saling tumpang tindih (overlap)?
- Apakah klaim sertifikasi terlihat konsisten dengan latar belakang pendidikan dan kerja?
- Apakah ada potensi risiko atau ketidakkonsistenan yang perlu diselidiki?
- Apakah alasan pengunduran diri (resignation_reason) terlihat mencurigakan?
- Jika resume kosong atau sangat minim data, tandai sebagai suspicious dan prioritas tinggi.

PENTING: Jawab HANYA dalam format JSON berikut, tanpa teks tambahan apapun:

{
  "priority_high": [
    {
      "category": "work|education|certification|basic_info",
      "item": "nama item yang harus diperiksa",
      "reason": "alasan mengapa ini prioritas tinggi"
    }
  ],
  "priority_medium": [
    {
      "category": "work|education|certification|basic_info",
      "item": "nama item",
      "reason": "alasan"
    }
  ],
  "priority_low": [
    {
      "category": "work|education|certification|basic_info",
      "item": "nama item",
      "reason": "alasan dapat diabaikan atau hanya perlu verifikasi visual"
    }
  ],
  "conduct_contacts": [
    {
      "company": "nama perusahaan",
      "supervisor_name": "nama atasan jika tersedia, atau kosong",
      "note": "catatan khusus untuk investigator terkait perusahaan ini"
    }
  ],
  "risk_flags": [
    {
      "type": "gap|overlap|inconsistency|suspicious|expired_cert",
      "description": "deskripsi risiko yang terdeteksi"
    }
  ],
  "summary": "Ringkasan analisis keseluruhan dalam 2-3 kalimat untuk investigator."
}
PROMPT;
    }

    // ─────────────────────────────────────────────────────────────────
    // レスポンスのパース
    // ─────────────────────────────────────────────────────────────────

    private function parseResponse(array $rawResponse): array
    {
        try {
            $text = $rawResponse['content'][0]['text'] ?? '';
            $text = preg_replace('/```json\s*/i', '', $text);
            $text = preg_replace('/```/', '', $text);
            $text = trim($text);

            $parsed = json_decode($text, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('HriPriorityAnalysis: JSONパースエラー: ' . json_last_error_msg() . ' / text: ' . $text);
                return [];
            }
            return $parsed;
        } catch (\Exception $e) {
            Log::error('HriPriorityAnalysis: parseResponseエラー: ' . $e->getMessage());
            return [];
        }
    }
}