<?php

namespace App\Services;

use App\Models\CaseReview;
use App\Models\CertificationRequest;
use App\Models\InvestigationItem;
use App\Models\ReviewItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HriAiScoringService
{
    // ===== v2.4 加点方式・6カテゴリ =====
    private array $categoryConfig = [
        'identity' => [
            'max_score' => 20,
            'label'     => 'Informasi Dasar & Identitas',
        ],
        'education' => [
            'max_score' => 15,
            'label'     => 'Riwayat Pendidikan',
        ],
        'work' => [
            'max_score' => 25,
            'label'     => 'Riwayat Pekerjaan',
        ],
        'certification' => [
            'max_score' => 10,
            'label'     => 'Sertifikat & Keahlian',
        ],
        'conduct' => [
            'max_score' => 20,
            'label'     => 'Perilaku Kerja (Conduct)',
            'items' => [
                'stabilitas_kehadiran' => 4,
                'kepatuhan_instruksi'  => 4,
                'kerja_sama_tim'       => 4,
                'sikap_kerja'          => 4,
                'pelanggaran_disiplin' => 4,
            ],
        ],
        'consistency' => [
            'max_score' => 10,
            'label'     => 'Konsistensi Keseluruhan',
        ],
    ];

    // investigation_items.category → review_items.category のマッピング
    private array $categoryMap = [
        'basic_info'    => 'identity',
        'education'     => 'education',
        'work'          => 'work',
        'certification' => 'certification',
        'conduct'       => 'conduct',
    ];

    // ===== メインエントリーポイント =====

    public function score(int $certificationRequestId): bool
    {
        try {
            $cr = CertificationRequest::with([
                'investigationItems',
                'workHistories',
                'educationHistories',
            ])->findOrFail($certificationRequestId);

            $items = $cr->investigationItems;

            if ($items->isEmpty()) {
                Log::warning("AiScoring: investigation_items が空 (cr_id: {$certificationRequestId})");
                return false;
            }

            // 禁止事項チェック（1件でも検知したら即差し戻し）
            $prohibitionCheck = $this->checkProhibitedContent($items);
            if ($prohibitionCheck['has_violation']) {
                $this->saveReturnToInvestigation($cr, $prohibitionCheck);
                return true;
            }

            // Claude API で総合審査
            $claudeResult = $this->runClaudeReview($cr, $items);

            // review_items 保存（項目別スコア詳細）
            $caseReview = $this->saveCaseReview($cr->id, $claudeResult);

            // review_items 保存
            $this->saveReviewItems($cr->id, $caseReview->id, $claudeResult);

            // certification_requests を更新
            $cr->update([
                'survey_status'          => 'pending_admin',
                'ready_for_review'       => true,
                'ai_review_completed_at' => now(),
                'ai_model_name'          => 'claude-sonnet-4-20250514',
                'latest_case_review_id'  => $caseReview->id,
                'base_score'             => $claudeResult['base_score'],
                'truthfulness_percent'   => $claudeResult['truthfulness_percent'],
                'consistency_percent'    => $claudeResult['consistency_percent'],
                'hri_suitability_score'  => $claudeResult['hri_suitability_score'],
                'work_ability_score'     => $claudeResult['work_ability_score'],
                'work_ability_band'      => $claudeResult['work_ability_band'],
                'claude_overall_judgment'=> $claudeResult['claude_overall_judgment'],
                'claude_overall_reason'  => $claudeResult['claude_overall_reason'],
                'enterprise_view_summary'=> $claudeResult['enterprise_view_summary'],
                'final_decision'         => $claudeResult['final_decision'],
            ]);

            Log::info("AiScoring: 完了", [
                'cr_id'          => $certificationRequestId,
                'final_decision' => $claudeResult['final_decision'],
                'base_score'     => $claudeResult['base_score'],
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("AiScoring 失敗: " . $e->getMessage(), [
                'cr_id' => $certificationRequestId,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    // ===== 禁止事項チェック =====

    private function checkProhibitedContent($items): array
    {
        $prohibitedKeywords = [
            'agama', 'religion',
            'kesehatan', 'health', 'sakit',
            'keluarga', 'family', 'anak', 'suami', 'istri',
            'politik', 'partai',
            'seksual', 'orientasi',
            'gosip', 'rumor', 'kabar',
            'rekam diam', 'rekaman tanpa',
            'perangkat pribadi',
            'cloud pribadi',
        ];

        $violations = [];
        foreach ($items as $item) {
            $notesLower = strtolower($item->notes ?? '');
            foreach ($prohibitedKeywords as $keyword) {
                if (str_contains($notesLower, $keyword)) {
                    $violations[] = [
                        'item_id'   => $item->id,
                        'item_name' => $item->item_name,
                        'keyword'   => $keyword,
                        'notes'     => $item->notes,
                    ];
                    break;
                }
            }
        }

        return [
            'has_violation' => !empty($violations),
            'violations'    => $violations,
        ];
    }

    // ===== Claude API 総合審査 =====

    private function runClaudeReview(CertificationRequest $cr, $items): array
    {
        $apiKey = config('services.anthropic.api_key');

        // 調査結果をカテゴリ別に整形
        $investigationSummary = $this->buildInvestigationSummary($items);

        $prompt = $this->buildPrompt($cr, $investigationSummary);

        if (!$apiKey) {
            Log::warning('AiScoring: APIキーなし、フォールバック使用');
            return $this->buildFallbackResult($items);
        }

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-sonnet-4-20250514',
                    'max_tokens' => 2000,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('AiScoring: API失敗', ['status' => $response->status()]);
                return $this->buildFallbackResult($items);
            }

            $data = $response->json();
            $text = $data['content'][0]['text'] ?? '';

            // JSONを抽出
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $text = $matches[0];
            }

            $parsed = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($parsed)) {
                Log::warning('AiScoring: JSONパース失敗', ['raw' => $text]);
                return $this->buildFallbackResult($items);
            }

            return $this->validateAndNormalizeResult($parsed, $items);

        } catch (\Exception $e) {
            Log::warning('AiScoring: API例外 ' . $e->getMessage());
            return $this->buildFallbackResult($items);
        }
    }

    // ===== プロンプト生成 =====

    private function buildPrompt(CertificationRequest $cr, string $investigationSummary): string
    {
        return <<<PROMPT
Anda adalah sistem AI HRI (Human Resource Intelligence) yang bertugas melakukan penilaian sertifikasi secara profesional.

Tugas Anda: Nilai hasil investigasi berikut dan kembalikan JSON penilaian lengkap.

=== HASIL INVESTIGASI ===
{$investigationSummary}

=== ATURAN PENILAIAN (v2.4 - Sistem Penambahan Poin) ===

**A. Base Score (100 poin)**
Berikan actual_score untuk setiap kategori:
- identity (maks 20): NIK, KTP, nama, tanggal lahir
- education (maks 15): nama sekolah, kelulusan, gelar, GPA
- work (maks 25): nama perusahaan, periode, jabatan, jenis pekerjaan
- certification (maks 10): nama sertifikat, lembaga penerbit, masa berlaku
- conduct (maks 20): kehadiran, kepatuhan, kerja sama, sikap kerja, pelanggaran disiplin
- consistency (maks 10): konsistensi antar dokumen, kronologi, tidak ada kontradiksi

Panduan pemberian poin:
- Nilai penuh: Terkonfirmasi, baik/valid
- Nilai penuh - 1: Umumnya baik, ada sedikit reservasi
- Setengah nilai: Terkonfirmasi sebagian, ada reservasi
- 1 poin: Ada kekhawatiran, bukti terbatas
- 0 poin: Masalah serius, kontradiksi besar, pemalsuan jelas
- UNVERIFIED: Jangan otomatis 0, pertimbangkan alasannya

**B. Truthfulness % (0-100)**
Bobot: identity 25%, education 20%, work 30%, certification 15%, lainnya 10%
Nilai konfirmasi: Fully Verified=1.00, Partially=0.70, Unverified=0.40, Contradicted=0.00
Rumus: Σ(nilai_konfirmasi × bobot) × 100

**C. Consistency % (0-100)**
Mulai dari 100, kurangi:
- Kontradiksi ringan (typo, perbedaan kecil): -5
- Kontradiksi sedang (beda tanggal, isi tidak cocok): -15
- Kontradiksi besar (periode tumpang tindih, logika tidak masuk akal): -30
- Dugaan pemalsuan: -40

**D. HRI Suitability Score (0-100)**
Estimasi berdasarkan: stabilitas riwayat (30), keandalan (30), perilaku kerja (25), kesiapan kerja (15)

**E. Work Ability Score**
Rumus: (HRI_Suitability × 0.70) + (Truthfulness% × 0.15) + (Consistency% × 0.15)
Band: HIGH=85-100, MODERATE=70-84, LIMITED=55-69, LOW=0-54

**F. Final Decision**
- APPROVE: base_score≥75, truthfulness≥80%, consistency≥80%, tidak ada pelanggaran serius
- CONDITIONAL_APPROVE: base_score≥60, truthfulness≥65%, sebagian tidak terkonfirmasi, tidak ada pemalsuan besar
- REJECT: identitas tidak valid, pemalsuan jelas, manipulasi dokumen terkonfirmasi
- ESCALATE_TO_HUMAN: risiko hukum, risiko pencemaran nama, catatan disiplin sulit ditafsirkan
- RETURN_TO_INVESTIGATION: ada konten terlarang, investigasi tidak lengkap, kontradiksi catatan

**G. Claude Overall Judgment**
Pilih salah satu:
- STRONGLY_RECOMMENDED_FOR_VERIFIED_VIEW
- VERIFIED_WITH_RESERVATIONS
- LIMITED_RELIABILITY
- HUMAN_REVIEW_STRONGLY_RECOMMENDED
- RETURN_REQUIRED

=== FORMAT OUTPUT (JSON saja, tanpa penjelasan tambahan) ===
{
  "scores": {
    "identity": 0,
    "education": 0,
    "work": 0,
    "certification": 0,
    "conduct": 0,
    "consistency": 0
  },
  "base_score": 0,
  "truthfulness_percent": 0,
  "consistency_percent": 0,
  "hri_suitability_score": 0,
  "work_ability_score": 0,
  "work_ability_band": "MODERATE",
  "claude_overall_judgment": "VERIFIED_WITH_RESERVATIONS",
  "claude_overall_reason": "Penjelasan singkat 100-250 karakter mengapa penilaian ini diberikan.",
  "enterprise_view_summary": "Ringkasan singkat untuk perusahaan tentang kandidat ini.",
  "final_decision": "APPROVE",
  "score_reasons": {
    "identity": "Alasan singkat untuk skor identity.",
    "education": "Alasan singkat untuk skor education.",
    "work": "Alasan singkat untuk skor work.",
    "certification": "Alasan singkat untuk skor certification.",
    "conduct": "Alasan singkat untuk skor conduct.",
    "consistency": "Alasan singkat untuk skor consistency."
  },
  "verified_items": ["item yang terkonfirmasi"],
  "unverified_items": ["item yang tidak terkonfirmasi"],
  "risk_flags": ["risiko yang ditemukan"],
  "conditions": []
}
PROMPT;
    }

    // ===== 調査結果の整形 =====

    private function buildInvestigationSummary($items): string
    {
        $byCategory = [];
        foreach ($items as $item) {
            $cat = $item->category;
            $byCategory[$cat][] = $item;
        }

        $lines = [];
        $categoryLabels = [
            'basic_info'    => '基本情報 / Informasi Dasar',
            'education'     => '学歴 / Pendidikan',
            'work'          => '職歴 / Pekerjaan',
            'certification' => '資格 / Sertifikat',
            'conduct'       => '素行 / Perilaku Kerja',
        ];

        foreach ($categoryLabels as $cat => $label) {
            if (!isset($byCategory[$cat])) continue;

            $lines[] = "\n[{$label}]";
            foreach ($byCategory[$cat] as $item) {
                $validity = $item->validity;
                $notes    = $item->notes_id ?? $item->notes ?? '(tidak ada catatan)';
                $lines[]  = "  - {$item->item_name}: {$validity} | {$notes}";
            }
        }

        return implode("\n", $lines);
    }

    // ===== 結果の検証・正規化 =====

    private function validateAndNormalizeResult(array $parsed, $items): array
    {
        $scores = $parsed['scores'] ?? [];

        // 各カテゴリのスコアを上限でキャップ
        $normalizedScores = [];
        foreach ($this->categoryConfig as $cat => $config) {
            $rawScore              = (int) ($scores[$cat] ?? 0);
            $normalizedScores[$cat] = max(0, min($config['max_score'], $rawScore));
        }

        $baseScore = array_sum($normalizedScores);

        // 数値の正規化
        $truthfulness  = min(100, max(0, (float) ($parsed['truthfulness_percent']  ?? 0)));
        $consistency   = min(100, max(0, (float) ($parsed['consistency_percent']   ?? 100)));
        $suitability   = min(100, max(0, (float) ($parsed['hri_suitability_score'] ?? 0)));

        // Work Ability Score 再計算（確実に計算式通りに）
        $workAbility = ($suitability * 0.70) + ($truthfulness * 0.15) + ($consistency * 0.15);
        $workAbility = round(min(100, max(0, $workAbility)), 2);

        // Band 判定
        $band = $this->getWorkAbilityBand($workAbility);

        // final_decision の検証
        $validDecisions = ['APPROVE', 'CONDITIONAL_APPROVE', 'REJECT', 'ESCALATE_TO_HUMAN', 'RETURN_TO_INVESTIGATION'];
        $finalDecision  = in_array($parsed['final_decision'] ?? '', $validDecisions)
            ? $parsed['final_decision']
            : $this->determineFinalDecision($baseScore, $truthfulness, $consistency);

        // claude_overall_judgment の検証
        $validJudgments = [
            'STRONGLY_RECOMMENDED_FOR_VERIFIED_VIEW',
            'VERIFIED_WITH_RESERVATIONS',
            'LIMITED_RELIABILITY',
            'HUMAN_REVIEW_STRONGLY_RECOMMENDED',
            'RETURN_REQUIRED',
        ];
        $judgment = in_array($parsed['claude_overall_judgment'] ?? '', $validJudgments)
            ? $parsed['claude_overall_judgment']
            : 'LIMITED_RELIABILITY';

        return [
            'scores'                  => $normalizedScores,
            'base_score'              => $baseScore,
            'truthfulness_percent'    => round($truthfulness, 2),
            'consistency_percent'     => round($consistency, 2),
            'hri_suitability_score'   => round($suitability, 2),
            'work_ability_score'      => $workAbility,
            'work_ability_band'       => $band,
            'claude_overall_judgment' => $judgment,
            'claude_overall_reason'   => $parsed['claude_overall_reason'] ?? '',
            'enterprise_view_summary' => $parsed['enterprise_view_summary'] ?? '',
            'final_decision'          => $finalDecision,
            'score_reasons'           => $parsed['score_reasons'] ?? [],
            'verified_items'          => $parsed['verified_items'] ?? [],
            'unverified_items'        => $parsed['unverified_items'] ?? [],
            'risk_flags'              => $parsed['risk_flags'] ?? [],
            'conditions'              => $parsed['conditions'] ?? [],
        ];
    }

    // ===== Work Ability Band 判定 =====

    private function getWorkAbilityBand(float $score): string
    {
        if ($score >= 85) return 'HIGH';
        if ($score >= 70) return 'MODERATE';
        if ($score >= 55) return 'LIMITED';
        return 'LOW';
    }

    // ===== フォールバック final_decision =====

    private function determineFinalDecision(float $baseScore, float $truthfulness, float $consistency): string
    {
        if ($baseScore >= 75 && $truthfulness >= 80 && $consistency >= 80) {
            return 'APPROVE';
        }
        if ($baseScore >= 60 && $truthfulness >= 65 && $consistency >= 65) {
            return 'CONDITIONAL_APPROVE';
        }
        if ($baseScore < 40) {
            return 'REJECT';
        }
        return 'ESCALATE_TO_HUMAN';
    }

    // ===== case_reviews 保存 =====

    private function saveCaseReview(int $crId, array $result): CaseReview
    {
        return CaseReview::create([
            'certification_request_id' => $crId,
            'prompt_version'           => 'v2.4',
            'model_name'               => 'claude-sonnet-4-20250514',
            'final_decision'           => $result['final_decision'],
            'base_score'               => $result['base_score'],
            'truthfulness_percent'     => $result['truthfulness_percent'],
            'consistency_percent'      => $result['consistency_percent'],
            'hri_suitability_score'    => $result['hri_suitability_score'],
            'work_ability_score'       => $result['work_ability_score'],
            'work_ability_band'        => $result['work_ability_band'],
            'claude_overall_judgment'  => $result['claude_overall_judgment'],
            'claude_overall_reason'    => $result['claude_overall_reason'],
            'enterprise_view_summary'  => $result['enterprise_view_summary'],
            'verified_items_json'      => $result['verified_items'],
            'unverified_items_json'    => $result['unverified_items'],
            'risk_flags_json'          => $result['risk_flags'],
            'conditions_json'          => $result['conditions'],
            'reviewed_at'              => now(),
        ]);
    }

    // ===== review_items 保存（項目別スコア詳細）=====

    private function saveReviewItems(int $crId, int $caseReviewId, array $result): void
    {
        // 既存のAI生成レコードを削除
        ReviewItem::where('certification_request_id', $crId)
            ->where('is_ai_scored', true)
            ->delete();

        $scores      = $result['scores'] ?? [];
        $scoreReasons = $result['score_reasons'] ?? [];

        foreach ($this->categoryConfig as $category => $config) {
            $actualScore = $scores[$category] ?? 0;
            $reason      = $scoreReasons[$category] ?? null;

            // verification_status の判定
            $verificationStatus = $this->determineVerificationStatus($actualScore, $config['max_score']);

            ReviewItem::create([
                'certification_request_id' => $crId,
                'case_review_id'           => $caseReviewId,
                'category'                 => $category,
                'item_name'                => $config['label'],
                // 旧カラム（互換性維持）
                'max_deduction'            => 0,
                'actual_deduction'         => 0,
                'weight'                   => 1.0,
                // v2.4 加点方式
                'max_score'                => $config['max_score'],
                'actual_score'             => $actualScore,
                'score_reason'             => $reason,
                'verification_status'      => $verificationStatus,
                'ai_model'                 => 'claude-sonnet-4-20250514',
                'is_ai_scored'             => true,
                'reviewed_by'              => null,
            ]);
        }
    }

    // ===== verification_status 判定 =====

    private function determineVerificationStatus(int $actualScore, int $maxScore): string
    {
        if ($maxScore === 0) return 'UNVERIFIED';

        $ratio = $actualScore / $maxScore;

        if ($ratio >= 0.9)  return 'VERIFIED';
        if ($ratio >= 0.5)  return 'PARTIALLY_VERIFIED';
        if ($ratio > 0)     return 'UNVERIFIED';
        return 'CONTRADICTED';
    }

    // ===== 差し戻し保存 =====

    private function saveReturnToInvestigation(CertificationRequest $cr, array $prohibitionCheck): void
    {
        $violations = $prohibitionCheck['violations'] ?? [];

        $caseReview = CaseReview::create([
            'certification_request_id' => $cr->id,
            'prompt_version'           => 'v2.4',
            'model_name'               => 'claude-sonnet-4-20250514',
            'final_decision'           => 'RETURN_TO_INVESTIGATION',
            'claude_overall_judgment'  => 'RETURN_REQUIRED',
            'claude_overall_reason'    => 'Ditemukan konten terlarang dalam catatan investigasi. Harap lakukan investigasi ulang sesuai pedoman.',
            'enterprise_view_summary'  => 'Dalam proses investigasi ulang.',
            'compliance_return_json'   => [
                'return_required'   => true,
                'violation_types'   => array_column($violations, 'keyword'),
                'violation_details' => $violations,
            ],
            'reviewed_at' => now(),
        ]);

        $cr->update([
            'survey_status'          => 'under_investigation',
            'ai_review_completed_at' => now(),
            'ai_model_name'          => 'claude-sonnet-4-20250514',
            'latest_case_review_id'  => $caseReview->id,
            'final_decision'         => 'RETURN_TO_INVESTIGATION',
            'claude_overall_judgment'=> 'RETURN_REQUIRED',
        ]);

        Log::warning('AiScoring: 禁止事項検知・差し戻し', [
            'cr_id'      => $cr->id,
            'violations' => $violations,
        ]);
    }

    // ===== フォールバック結果 =====

    private function buildFallbackResult($items): array
    {
        // APIが使えない場合の安全なデフォルト値
        $scores = [];
        foreach ($this->categoryConfig as $cat => $config) {
            // VALID が多ければ半分、少なければ低め
            $catItems = $items->filter(fn($i) => ($this->categoryMap[$i->category] ?? null) === $cat);
            if ($catItems->isEmpty()) {
                $scores[$cat] = (int) round($config['max_score'] * 0.5);
                continue;
            }
            $validCount = $catItems->where('validity', 'VALID')->count();
            $total      = $catItems->count();
            $ratio      = $total > 0 ? $validCount / $total : 0.5;
            $scores[$cat] = (int) round($config['max_score'] * $ratio);
        }

        $baseScore   = array_sum($scores);
        $truthfulness = 60.0;
        $consistency  = 70.0;
        $suitability  = 55.0;
        $workAbility  = ($suitability * 0.70) + ($truthfulness * 0.15) + ($consistency * 0.15);

        return [
            'scores'                  => $scores,
            'base_score'              => $baseScore,
            'truthfulness_percent'    => $truthfulness,
            'consistency_percent'     => $consistency,
            'hri_suitability_score'   => $suitability,
            'work_ability_score'      => round($workAbility, 2),
            'work_ability_band'       => $this->getWorkAbilityBand($workAbility),
            'claude_overall_judgment' => 'LIMITED_RELIABILITY',
            'claude_overall_reason'   => 'Penilaian otomatis (API tidak tersedia). Mohon tinjau manual.',
            'enterprise_view_summary' => 'Penilaian sementara. Verifikasi manual diperlukan.',
            'final_decision'          => 'ESCALATE_TO_HUMAN',
            'score_reasons'           => [],
            'verified_items'          => [],
            'unverified_items'        => [],
            'risk_flags'              => ['API tidak tersedia saat penilaian'],
            'conditions'              => [],
        ];
    }
}