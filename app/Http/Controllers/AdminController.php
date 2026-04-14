<?php

namespace App\Http\Controllers;

use App\Models\CertificationRequest;
use App\Models\ApplicantProfile;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use App\Models\CaseReview;
use App\Models\CaseReturn;
use App\Models\CaseDeliverable;
use App\Services\CaseDeliverableService;
use App\Services\AuditLogService;
use App\Services\ClaudeSubpromptService;
use App\Services\NumberingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct(
        private CaseDeliverableService  $deliverableService,
        private AuditLogService         $auditLogService,
        private ClaudeSubpromptService  $subprompt,
        private NumberingService        $numbering,
    ) {}

    // ================================================================
    // 案件一覧 + 詳細
    // ================================================================

    public function index(Request $request)
    {
        $cases = CertificationRequest::with(['applicant:id,name'])
            ->where(function ($q) {
                $q->whereIn('current_status', [
                        CertificationRequest::STATUS_AI_REVIEW_PENDING,
                        CertificationRequest::STATUS_HUMAN_REVIEW,
                    ])
                  ->orWhereIn('survey_status', ['pending_admin', 'escalated_to_human']);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($c) {
                $profile = ApplicantProfile::where('user_id', $c->user_id)
                    ->first(['member_id', 'full_name']);
                return [
                    'id'             => $c->id,
                    'case_no'        => $c->case_no,
                    'name'           => optional($profile)->full_name ?? optional($c->applicant)->name,
                    'member_id'      => optional($profile)->member_id ?? '-',
                    'submitted_at'   => $c->created_at->format('d/m/Y'),
                    'current_status' => $c->current_status ?? $c->survey_status,
                ];
            });

        $selectedId = $request->query('id', optional($cases->first())['id']);
        $detail     = null;

        if ($selectedId) {
            $cr = CertificationRequest::with([
                'applicant:id,name,email',
                'investigationItems',
                'reviewItems',
                'latestCaseReview',
                'priorityReport',
            ])->where('id', $selectedId)
              ->where(function ($q) {
                  $q->whereIn('current_status', [
                          CertificationRequest::STATUS_AI_REVIEW_PENDING,
                          CertificationRequest::STATUS_HUMAN_REVIEW,
                      ])
                    ->orWhereIn('survey_status', ['pending_admin', 'escalated_to_human']);
              })
              ->first();

            if ($cr) {
                $profile    = ApplicantProfile::where('user_id', $cr->user_id)->first();
                $invMap     = $cr->investigationItems->keyBy('item_name');
                $educations = EducationHistory::where('certification_request_id', $cr->id)->get();
                $works      = WorkHistory::where('certification_request_id', $cr->id)->get();
                $certs      = Certification::where('certification_request_id', $cr->id)->get();
                $scoreData  = $this->buildScoreData($cr);

                // 差し戻し回数（Section 26.4 準拠）
                $returnCount = CaseReturn::where('certification_request_id', $cr->id)->count();

                $caseReview = null;
                if ($cr->latestCaseReview) {
                    $rev = $cr->latestCaseReview;
                    $caseReview = [
                        'ai_proposed_decision'    => $rev->ai_proposed_decision,
                        'human_override_decision' => $rev->human_override_decision,
                        'effective_decision'      => $rev->effective_decision,
                        'confidence_level'        => $rev->confidence_level,
                        'verified_items'          => $rev->verified_items_json   ?? [],
                        'unverified_items'        => $rev->unverified_items_json ?? [],
                        'risk_flags'              => $rev->risk_flags_json       ?? [],
                        'conditions'              => $rev->conditions_json       ?? [],
                        'compliance_return'       => $rev->compliance_return_json ?? null,
                    ];
                }

                $detail = [
                    'id'              => $cr->id,
                    'case_no'         => $cr->case_no,
                    'current_status'  => $cr->current_status ?? $cr->survey_status,
                    'external_status' => $cr->external_status,
                    'admin_notes'     => $cr->admin_notes,
                    'return_count'    => $returnCount, // ★ 差し戻し回数

                    'base_score'              => $cr->base_score,
                    'truthfulness_percent'    => $cr->truthfulness_percent,
                    'consistency_percent'     => $cr->consistency_percent,
                    'hri_suitability_score'   => $cr->hri_suitability_score,
                    'work_ability_score'      => $cr->work_ability_score,
                    'work_ability_band'       => $cr->work_ability_band,
                    'claude_overall_judgment' => $cr->claude_overall_judgment,
                    'claude_overall_reason'   => $cr->claude_overall_reason,
                    'enterprise_view_summary' => $cr->enterprise_view_summary,
                    'ai_review_completed_at'  => $cr->ai_review_completed_at
                        ? Carbon::parse($cr->ai_review_completed_at)->format('d/m/Y H:i') : null,

                    'score_items'  => $scoreData,
                    'case_review'  => $caseReview,

                    'priority_report' => $cr->priorityReport ? [
                        'priority_high'   => $cr->priorityReport->priority_high_json   ?? [],
                        'priority_medium' => $cr->priorityReport->priority_medium_json ?? [],
                        'priority_low'    => $cr->priorityReport->priority_low_json    ?? [],
                        'risk_flags'      => $cr->priorityReport->risk_flags_json      ?? [],
                        'summary'         => $cr->priorityReport->ai_analysis_summary,
                        'generated_at'    => $cr->priorityReport->generated_at
                            ? Carbon::parse($cr->priorityReport->generated_at)->format('d/m/Y H:i') : null,
                    ] : null,

                    'profile' => $profile ? [
                        'full_name'       => $profile->full_name,
                        'nik'             => $profile->nik,
                        'ktp_address'     => $profile->ktp_address,
                        'ktp_card'        => $profile->ktp_card,
                        'gender'          => $profile->gender,
                        'marital_status'  => $profile->marital_status,
                        'nationality'     => $profile->nationality,
                        'birth_date'      => $profile->birth_date
                            ? Carbon::parse($profile->birth_date)->format('d/m/Y') : null,
                        'current_address' => $profile->current_address,
                        'phone_number'    => $profile->phone_number,
                        'whatsapp_number' => $profile->whatsapp_number,
                        'profile_photo'   => $profile->profile_photo,
                        'member_id'       => $profile->member_id,
                    ] : null,

                    'inv_basic'   => $this->buildSection($this->basicFields($profile), $invMap),
                    'inv_edu'     => $this->buildEduSection($educations, $invMap),
                    'inv_work'    => $this->buildWorkSection($works, $invMap),
                    'inv_cert'    => $this->buildCertSection($certs, $invMap),
                    'inv_conduct' => $this->buildConductSection($works, $invMap),
                ];
            }
        }

        return Inertia::render('Admin/Admin/AdminMain', [
            'cases'      => $cases,
            'detail'     => $detail,
            'selectedId' => (int) $selectedId,
        ]);
    }

    // ================================================================
    // ★ A-1: サブプロンプト審査実行（AdminMainからAjax呼び出し）
    // ================================================================

    public function runAiSubpromptReview(Request $request, int $id)
    {
        $cr = $this->findPendingCase($id);

        $profile    = ApplicantProfile::where('user_id', $cr->user_id)->first();
        $educations = EducationHistory::where('certification_request_id', $cr->id)->get();
        $works      = WorkHistory::where('certification_request_id', $cr->id)->get();
        $certs      = Certification::where('certification_request_id', $cr->id)->get();

        // 差し戻し履歴（Section 26.4 差し戻し3回制限）
        $returnHistory = CaseReturn::where('certification_request_id', $cr->id)
            ->orderBy('created_at')
            ->get(['id', 'return_reason_summary', 'returned_at'])
            ->toArray();

        // A-1 入力データを組み立て
        $input = [
            'case_id'                 => $cr->case_no,
            'applicant_basic_info'    => [
                'full_name'      => $profile?->full_name,
                'nik'            => $profile?->nik,
                'gender'         => $profile?->gender,
                'nationality'    => $profile?->nationality,
                'marital_status' => $profile?->marital_status,
            ],
            'consent_scope'           => 'hri_certification',
            'education_records'       => $educations->map(fn($e) => [
                'school_name'       => $e->school_name,
                'education_level'   => $e->education_level,
                'degree_name'       => $e->degree_name,
                'graduation_status' => $e->graduation_status,
            ])->toArray(),
            'employment_records'      => $works->map(fn($w) => [
                'company_name'         => $w->company_name,
                'department_position'  => $w->department_position,
                'employment_type'      => $w->employment_type,
                'supervisor_full_name' => $w->supervisor_full_name,
            ])->toArray(),
            'qualification_records'   => $certs->map(fn($c) => [
                'certificate_name'     => $c->certificate_name,
                'issuing_organization' => $c->issuing_organization,
            ])->toArray(),
            'investigation_records'   => $cr->investigationItems->map(fn($i) => [
                'item_name' => $i->item_name,
                'category'  => $i->category,
                'validity'  => $i->validity,
                'notes'     => $i->notes,
            ])->toArray(),
            'current_scores'          => [
                'base_score'            => $cr->base_score,
                'truthfulness_percent'  => $cr->truthfulness_percent,
                'consistency_percent'   => $cr->consistency_percent,
                'hri_suitability_score' => $cr->hri_suitability_score,
            ],
            'prior_return_history'    => $returnHistory,
            'language'                => 'id',
        ];

        // A-1 実行
        $a1Result = $this->subprompt->runA1($input);

        // audit_logs に記録
        \App\Models\AuditLog::recordAi(
            'AI_REVIEW_RUN',
            $cr->case_no,
            'A-1',
            ['new' => ['subprompt' => 'A-1', 'result_status' => $a1Result['review_status'] ?? 'unknown']]
        );

        // human_takeover_required = true → 4回目以降強制人間処理
        if (!empty($a1Result['human_takeover_required'])) {
            $cr->update(['admin_notes' => '[A-1] Human takeover required: 差し戻し上限超過']);
        }

        return response()->json([
            'success'   => true,
            'a1_result' => $a1Result,
            'case_no'   => $cr->case_no,
        ]);
    }

    // ================================================================
    // ✅ 承認（I-3 + NumberingService でVRシリアル発行）
    // ================================================================

    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes'             => 'nullable|string|max:1000',
            'human_override_decision' => 'nullable|in:APPROVE,CONDITIONAL_APPROVE,REJECT',
        ]);

        $cr        = $this->findPendingCase($id);
        $profile   = ApplicantProfile::where('user_id', $cr->user_id)->firstOrFail();
        $adminUser = Auth::user();
        $finalScore = (int) round($cr->base_score ?? 0);

        if ($cr->latestCaseReview) {
            $override  = $validated['human_override_decision'] ?? null;
            $effective = $override ?? $cr->latestCaseReview->ai_proposed_decision ?? 'APPROVE';

            $cr->latestCaseReview->update([
                'human_override_decision' => $override,
                'human_override_reason'   => $validated['admin_notes'] ?? null,
                'effective_decision'      => $effective,
                'approved_by_user_id'     => $adminUser->id,
                'approved_at'             => now(),
            ]);
        }

        $cr->updateStatus(
            CertificationRequest::STATUS_VERIFIED,
            CertificationRequest::EXT_VERIFIED
        );

        $cr->update([
            'admin_approved'      => true,
            'admin_approval_date' => now(),
            'admin_notes'         => $validated['admin_notes'] ?? null,
            'approved_by_user_id' => $adminUser->id,
            'approved_at'         => now(),
        ]);

        $profile->update([
            'certification_status'      => 'Terverifikasi',
            'hri_score'                 => $finalScore,
            'certification_date'        => now(),
            'certification_expiry_date' => now()->addMonths(3),
        ]);

        // ★ I-3: VRシリアル発行可否を確認してから NumberingService で採番
        try {
            $i3Input = [
                'case_id'       => $cr->case_no,
                'review_status' => 'approved',
                'issuance_type' => 'initial',
            ];
            $i3Result = $this->subprompt->runI3($i3Input);

            if (!empty($i3Result['issuable'])) {
                // I-3 が発行可と判断 → NumberingService で正式採番
                $vrSerial = $this->numbering->issueVrSerial(
                    $i3Result['version_code'] ?? 'A'
                );

                // audit_logs に VR_ISSUED を記録
                \App\Models\AuditLog::recordHuman(
                    'VR_ISSUED',
                    $cr->case_no,
                    ['serial_no' => $vrSerial, 'new' => ['vr_serial' => $vrSerial]]
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('I-3 / VR serial error: ' . $e->getMessage());
        }

        // VR / IR を ISSUED に発行
        try {
            $this->deliverableService->issueAll($cr->id, $adminUser->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("deliverableService::issueAll 失敗: " . $e->getMessage());
        }

        $this->auditLogService->log(
            actionType: 'APPROVED',
            caseNo: $cr->case_no,
            userId: $adminUser->id,
            actorType: 'human',
            newValues: ['status' => 'verified', 'score' => $finalScore],
        );

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Sertifikasi berhasil disetujui.');
    }

    // ================================================================
    // ✅ 条件付き承認
    // ================================================================

    public function conditionalApprove(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $cr        = $this->findPendingCase($id);
        $profile   = ApplicantProfile::where('user_id', $cr->user_id)->firstOrFail();
        $adminUser = Auth::user();
        $finalScore = (int) round($cr->base_score ?? 0);

        if ($cr->latestCaseReview) {
            $cr->latestCaseReview->update([
                'human_override_decision' => 'CONDITIONAL_APPROVE',
                'human_override_reason'   => $validated['admin_notes'],
                'effective_decision'      => 'CONDITIONAL_APPROVE',
                'approved_by_user_id'     => $adminUser->id,
                'approved_at'             => now(),
            ]);
        }

        $cr->updateStatus(
            CertificationRequest::STATUS_CONDITIONALLY_VERIFIED,
            CertificationRequest::EXT_CONDITIONALLY_VERIFIED
        );

        $cr->update([
            'admin_approved'      => true,
            'admin_approval_date' => now(),
            'admin_notes'         => $validated['admin_notes'],
            'approved_by_user_id' => $adminUser->id,
            'approved_at'         => now(),
        ]);

        $profile->update([
            'certification_status'      => 'conditional_approved',
            'hri_score'                 => $finalScore,
            'certification_date'        => now(),
            'certification_expiry_date' => now()->addMonths(3),
        ]);

        $this->auditLogService->log(
            actionType: 'CONDITIONAL_APPROVED',
            caseNo: $cr->case_no,
            userId: $adminUser->id,
            actorType: 'human',
            newValues: ['status' => 'conditionally_verified'],
        );

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Sertifikasi disetujui dengan kondisi.');
    }

    // ================================================================
    // ❌ 却下
    // ================================================================

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $cr        = $this->findPendingCase($id);
        $adminUser = Auth::user();

        if ($cr->latestCaseReview) {
            $cr->latestCaseReview->update([
                'human_override_decision' => 'REJECT',
                'human_override_reason'   => $validated['admin_notes'],
                'effective_decision'      => 'REJECT',
                'approved_by_user_id'     => $adminUser->id,
                'approved_at'             => now(),
            ]);
        }

        $cr->updateStatus(
            CertificationRequest::STATUS_REJECTED,
            CertificationRequest::EXT_REJECTED
        );

        $cr->update([
            'admin_approved' => false,
            'admin_notes'    => $validated['admin_notes'],
        ]);

        CaseDeliverable::where('certification_request_id', $cr->id)
            ->whereIn('deliverable_type', [CaseDeliverable::TYPE_VR, CaseDeliverable::TYPE_IR])
            ->update([
                'deliverable_status' => CaseDeliverable::STATUS_VOID,
                'is_active'          => false,
            ]);

        $this->auditLogService->log(
            actionType: 'REJECTED',
            caseNo: $cr->case_no,
            userId: $adminUser->id,
            actorType: 'human',
            newValues: ['status' => 'rejected'],
        );

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Kasus berhasil ditolak.');
    }

    // ================================================================
    // ⚠️ 差し戻し（★ A-2 で差し戻し文草案を自動生成）
    // ================================================================

    public function returnToReviewer(Request $request, int $id)
    {
        $validated = $request->validate([
            'return_reason' => 'required|string|max:1000',
        ]);

        $cr        = $this->findPendingCase($id);
        $adminUser = Auth::user();

        // 差し戻し回数チェック（Section 26.4: 4回目以降は人間処理強制）
        $returnCount = CaseReturn::where('certification_request_id', $cr->id)->count();
        if ($returnCount >= 3) {
            $cr->update([
                'admin_notes' => '[強制人間処理] 差し戻し回数上限（3回）超過。人間判断へ切替。',
            ]);
            return redirect()->route('admin.admin.evaluate')
                ->with('error', 'Batas pengembalian (3 kali) terlampaui. Kasus dialihkan ke pemrosesan manusia.');
        }

        // ★ A-2: 差し戻し判断 → 差し戻し文草案を自動生成
        $a2DraftMessage = $validated['return_reason']; // フォールバック用
        try {
            $a2Input = [
                'case_id'            => $cr->case_no,
                'prior_return_count' => $returnCount,
                'return_reason'      => $validated['return_reason'],
                'current_scores'     => [
                    'base_score'           => $cr->base_score,
                    'truthfulness_percent' => $cr->truthfulness_percent,
                    'consistency_percent'  => $cr->consistency_percent,
                ],
                'investigation_summary' => $cr->investigationItems->map(fn($i) => [
                    'item_name' => $i->item_name,
                    'validity'  => $i->validity,
                    'notes'     => $i->notes,
                ])->toArray(),
            ];

            $a2Result = $this->subprompt->runA2($a2Input);

            // A-2 が差し戻し文を生成した場合は採用
            if (!empty($a2Result['draft_return_message'])) {
                $a2DraftMessage = $a2Result['draft_return_message'];
            }

            // LEE通知フラグ（3回目差し戻し時）
            if (!empty($a2Result['notify_lee'])) {
                \Illuminate\Support\Facades\Log::info(
                    "[A-2] LEE通知: case_no={$cr->case_no}, return_count=" . ($returnCount + 1)
                );
            }

            \App\Models\AuditLog::recordAi(
                'RETURN_CREATED',
                $cr->case_no,
                'A-2',
                ['new' => ['subprompt' => 'A-2', 'draft_generated' => !empty($a2Result['draft_return_message'])]]
            );

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('A-2 error: ' . $e->getMessage());
        }

        if ($cr->latestCaseReview) {
            $cr->latestCaseReview->update([
                'human_override_decision' => 'RETURN_TO_INVESTIGATION',
                'human_override_reason'   => $validated['return_reason'],
                'effective_decision'      => 'RETURN_TO_INVESTIGATION',
                'approved_by_user_id'     => $adminUser->id,
                'approved_at'             => now(),
            ]);
        }

        $caseReturn = CaseReturn::create([
            'case_no'                  => $cr->case_no,
            'certification_request_id' => $cr->id,
            'case_review_id'           => $cr->latest_case_review_id,
            'return_reason_code'       => 'INCOMPLETE_INVESTIGATION',
            'return_reason_summary'    => $a2DraftMessage, // ★ A-2 草案を使用
            'returned_at'              => now(),
        ]);

        try {
            $this->deliverableService->issueReturnNotice($cr->id, $caseReturn->id);
        } catch (\Exception $e) {
            CaseDeliverable::where('certification_request_id', $cr->id)
                ->whereIn('deliverable_type', [CaseDeliverable::TYPE_VR, CaseDeliverable::TYPE_IR])
                ->update(['deliverable_status' => CaseDeliverable::STATUS_PENDING]);

            CaseDeliverable::updateOrCreate(
                [
                    'certification_request_id' => $cr->id,
                    'deliverable_type'         => CaseDeliverable::TYPE_RN,
                ],
                [
                    'case_no'            => $cr->case_no,
                    'deliverable_status' => CaseDeliverable::STATUS_ISSUED,
                    'visibility_scope'   => 'INTERNAL_ONLY',
                    'is_active'          => true,
                    'generated_at'       => now(),
                ]
            );
        }

        $cr->updateStatus(
            CertificationRequest::STATUS_RETURNED_INTERNAL,
            CertificationRequest::EXT_ADDITIONAL_CHECK
        );

        $cr->update([
            'internal_return_required' => true,
            'latest_return_id'         => $caseReturn->id,
            'ready_for_review'         => false,
        ]);

        $this->auditLogService->log(
            actionType: 'RETURN_CREATED',
            caseNo: $cr->case_no,
            userId: $adminUser->id,
            actorType: 'human',
            newValues: [
                'status'        => 'returned_internal',
                'return_reason' => $validated['return_reason'],
                'return_count'  => $returnCount + 1,
            ],
        );

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Kasus dikembalikan ke Tim Investigator.');
    }

    // ================================================================
    // 👤 人間確認へエスカレート
    // ================================================================

    public function escalateToHuman(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $cr        = $this->findPendingCase($id);
        $adminUser = Auth::user();

        if ($cr->latestCaseReview) {
            $cr->latestCaseReview->update([
                'human_override_decision' => 'ESCALATE_TO_HUMAN',
                'human_override_reason'   => $validated['admin_notes'],
                'effective_decision'      => 'ESCALATE_TO_HUMAN',
                'approved_by_user_id'     => $adminUser->id,
                'approved_at'             => now(),
            ]);
        }

        $cr->updateStatus(
            CertificationRequest::STATUS_HUMAN_REVIEW,
            CertificationRequest::EXT_UNDER_REVIEW
        );

        $cr->update(['admin_notes' => $validated['admin_notes']]);

        $this->auditLogService->log(
            actionType: 'HUMAN_REVIEW_ASSIGNED',
            caseNo: $cr->case_no,
            userId: $adminUser->id,
            actorType: 'human',
            newValues: ['status' => 'human_review_required'],
        );

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Kasus dieskalasi untuk pemeriksaan manusia.');
    }

    // ================================================================
    // ダッシュボード
    // ================================================================

    public function dashboard(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $search       = $request->query('search', '');
        $page         = max(1, (int) $request->query('page_num', 1));
        $perPage      = 20;

        $stats = [
            'pending_payment' => CertificationRequest::where('survey_status', 'pending_payment')->count(),

            'under_investigation' => CertificationRequest::where(function ($q) {
                $q->whereIn('current_status', [
                    CertificationRequest::STATUS_UNDER_INVESTIGATION,
                    CertificationRequest::STATUS_RETURNED_INTERNAL,
                ])->orWhere('survey_status', 'under_investigation');
            })->count(),

            'pending_admin' => CertificationRequest::where(function ($q) {
                $q->whereIn('current_status', [
                    CertificationRequest::STATUS_AI_REVIEW_PENDING,
                    CertificationRequest::STATUS_HUMAN_REVIEW,
                ])->orWhereIn('survey_status', ['pending_admin', 'escalated_to_human']);
            })->count(),

            'perlu_koreksi' => CertificationRequest::where(function ($q) {
                $q->where('current_status', CertificationRequest::STATUS_RETURNED_INTERNAL)
                  ->orWhere('survey_status', 'Perlu Koreksi');
            })->count(),

            'terverifikasi' => ApplicantProfile::where('certification_status', 'Terverifikasi')->count(),
            'conditional'   => ApplicantProfile::where('certification_status', 'conditional_approved')->count(),

            'ditolak' => CertificationRequest::where(function ($q) {
                $q->where('current_status', CertificationRequest::STATUS_REJECTED)
                  ->orWhere('survey_status', 'Ditolak');
            })->count(),
        ];

        $query = ApplicantProfile::with('user:id,name,email,created_at')
            ->select(['id', 'user_id', 'member_id', 'full_name', 'certification_status', 'hri_score', 'certification_date']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%");
            });
        }

        if ($statusFilter !== 'all') {
            $query->where('certification_status', $statusFilter);
        }

        $total   = $query->count();
        $members = $query->latest('id')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn($p) => [
                'id'                   => $p->id,
                'user_id'              => $p->user_id,
                'member_id'            => $p->member_id ?? '-',
                'full_name'            => $p->full_name ?? optional($p->user)->name,
                'email'                => optional($p->user)->email,
                'certification_status' => $p->certification_status ?? 'Terdaftar',
                'hri_score'            => $p->hri_score,
                'certification_date'   => optional($p->certification_date)->format('d/m/Y'),
                'registered_at'        => optional(optional($p->user)->created_at)->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Admin/Dashboard', [
            'stats'        => $stats,
            'members'      => $members,
            'total'        => $total,
            'currentPage'  => $page,
            'perPage'      => $perPage,
            'statusFilter' => $statusFilter,
            'search'       => $search,
        ]);
    }

    // ================================================================
    // 企業管理
    // ================================================================

    public function companies(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', 'all');

        $query = \App\Models\CompanyProfile::with('user:id,name,email,created_at')
            ->select(['id', 'user_id', 'company_name', 'company_verification_status',
                      'company_email', 'company_phone', 'verified_at', 'nib', 'pic_name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('company_email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('company_verification_status', $status);
        }

        $companies = $query->latest('id')->get()->map(fn($c) => [
            'id'                          => $c->id,
            'user_id'                     => $c->user_id,
            'company_name'                => $c->company_name,
            'company_email'               => $c->company_email ?? optional($c->user)->email,
            'company_phone'               => $c->company_phone,
            'nib'                         => $c->nib,
            'pic_name'                    => $c->pic_name,
            'company_verification_status' => $c->company_verification_status ?? 'pending',
            'verified_at'                 => optional($c->verified_at)->format('d/m/Y H:i'),
            'registered_at'               => optional(optional($c->user)->created_at)->format('d/m/Y'),
        ]);

        $companiesStats = [
            'pending'   => \App\Models\CompanyProfile::where('company_verification_status', 'pending')->count(),
            'verified'  => \App\Models\CompanyProfile::where('company_verification_status', 'verified')->count(),
            'suspended' => \App\Models\CompanyProfile::where('company_verification_status', 'suspended')->count(),
            'rejected'  => \App\Models\CompanyProfile::where('company_verification_status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Admin/AdminCompanies', [
            'companies'      => $companies,
            'companiesStats' => $companiesStats,
            'search'         => $search,
            'statusFilter'   => $status,
        ]);
    }

    public function updateCompanyStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,verified,suspended,rejected',
        ]);

        $company = \App\Models\CompanyProfile::findOrFail($id);
        $company->update([
            'company_verification_status' => $validated['status'],
            'verified_by' => $validated['status'] === 'verified' ? Auth::id() : null,
            'verified_at' => $validated['status'] === 'verified' ? now() : null,
        ]);

        return back()->with('success', 'Status perusahaan berhasil diperbarui.');
    }

    // ================================================================
    // ヘルパー
    // ================================================================

    private function findPendingCase(int $id): CertificationRequest
    {
        return CertificationRequest::where('id', $id)
            ->where(function ($q) {
                $q->whereIn('current_status', [
                        CertificationRequest::STATUS_AI_REVIEW_PENDING,
                        CertificationRequest::STATUS_HUMAN_REVIEW,
                    ])
                  ->orWhereIn('survey_status', ['pending_admin', 'escalated_to_human']);
            })
            ->with('latestCaseReview')
            ->firstOrFail();
    }

    private function formatDate($date): ?string
    {
        if (!$date) return null;
        try { return Carbon::parse($date)->format('d/m/Y'); }
        catch (\Exception $e) { return $date; }
    }

    private function buildScoreData(CertificationRequest $cr): array
    {
        $categoryLabels = [
            'identity'      => ['label' => 'Identitas & Dasar', 'max' => 20],
            'education'     => ['label' => 'Pendidikan',        'max' => 15],
            'work'          => ['label' => 'Pengalaman Kerja',  'max' => 25],
            'certification' => ['label' => 'Sertifikat',        'max' => 10],
            'conduct'       => ['label' => 'Perilaku Kerja',    'max' => 20],
            'consistency'   => ['label' => 'Konsistensi',       'max' => 10],
        ];

        $result = [];
        foreach ($cr->reviewItems as $item) {
            $cat    = $item->category;
            $config = $categoryLabels[$cat] ?? ['label' => $cat, 'max' => 0];
            $result[] = [
                'category'            => $cat,
                'label'               => $config['label'],
                'max_score'           => $item->max_score ?? $config['max'],
                'actual_score'        => $item->actual_score ?? 0,
                'score_reason'        => $item->score_reason,
                'verification_status' => $item->verification_status,
            ];
        }
        return $result;
    }

    private function buildSection($fields, $invMap): array
    {
        return array_map(function ($f) use ($invMap) {
            $inv = $invMap->get($f['key']);
            return [
                'item_name' => $f['key'],
                'label'     => $f['label'],
                'value'     => $f['value'] ?? '-',
                'validity'  => $inv ? $inv->validity : null,
                'inv_notes' => $inv ? $inv->notes    : null,
            ];
        }, $fields);
    }

    private function basicFields($profile): array
    {
        if (!$profile) return [];
        return [
            ['key' => 'full_name',       'label' => 'Nama Lengkap',      'value' => $profile->full_name],
            ['key' => 'nik',             'label' => 'NIK',               'value' => $profile->nik],
            ['key' => 'ktp_address',     'label' => 'Alamat KTP',        'value' => $profile->ktp_address],
            ['key' => 'gender',          'label' => 'Jenis Kelamin',     'value' => $profile->gender],
            ['key' => 'marital_status',  'label' => 'Status Pernikahan', 'value' => $profile->marital_status],
            ['key' => 'nationality',     'label' => 'Kewarganegaraan',   'value' => $profile->nationality],
            ['key' => 'birth_date',      'label' => 'Tanggal Lahir',     'value' => $this->formatDate($profile->birth_date)],
            ['key' => 'current_address', 'label' => 'Alamat Saat Ini',   'value' => $profile->current_address],
            ['key' => 'phone_number',    'label' => 'Telepon',           'value' => $profile->phone_number],
            ['key' => 'whatsapp_number', 'label' => 'WhatsApp',          'value' => $profile->whatsapp_number],
        ];
    }

    private function buildEduSection($educations, $invMap): array
    {
        $result = [];
        foreach ($educations as $i => $edu) {
            $fields = [
                ["edu_{$i}_school_name",       'Pendidikan '.($i+1).' - Nama Sekolah',       $edu->school_name],
                ["edu_{$i}_education_level",   'Pendidikan '.($i+1).' - Tingkat Pendidikan', $edu->education_level],
                ["edu_{$i}_school_location",   'Pendidikan '.($i+1).' - Alamat Sekolah',     $edu->school_location],
                ["edu_{$i}_degree_name",       'Pendidikan '.($i+1).' - Jurusan',            $edu->degree_name],
                ["edu_{$i}_enrollment_date",   'Pendidikan '.($i+1).' - Tanggal Masuk',      $this->formatDate($edu->enrollment_date)],
                ["edu_{$i}_graduation_date",   'Pendidikan '.($i+1).' - Tanggal Lulus',      $this->formatDate($edu->graduation_date)],
                ["edu_{$i}_graduation_status", 'Pendidikan '.($i+1).' - Status Kelulusan',   $edu->graduation_status],
                ["edu_{$i}_ipk_gpa",           'Pendidikan '.($i+1).' - IPK / Nilai Akhir',  $edu->ipk_gpa],
            ];
            foreach ($fields as [$key, $label, $val]) {
                $inv = $invMap->get($key);
                $result[] = ['item_name' => $key, 'label' => $label, 'value' => $val ?? '-', 'validity' => $inv?->validity, 'inv_notes' => $inv?->notes];
            }
        }
        return $result;
    }

    private function buildWorkSection($works, $invMap): array
    {
        $result = [];
        foreach ($works as $i => $w) {
            $fields = [
                ["work_{$i}_company_name",          'Kerja '.($i+1).' - Nama Perusahaan',   $w->company_name],
                ["work_{$i}_company_address",       'Kerja '.($i+1).' - Alamat Perusahaan', $w->company_address],
                ["work_{$i}_department_position",   'Kerja '.($i+1).' - Jabatan',           $w->department_position],
                ["work_{$i}_employment_type",       'Kerja '.($i+1).' - Jenis Pekerjaan',   $w->employment_type],
                ["work_{$i}_employment_start_date", 'Kerja '.($i+1).' - Tanggal Mulai',     $this->formatDate($w->employment_start_date)],
                ["work_{$i}_employment_end_date",   'Kerja '.($i+1).' - Tanggal Selesai',   $w->employment_end_date ? $this->formatDate($w->employment_end_date) : 'Masih Bekerja'],
                ["work_{$i}_supervisor_full_name",  'Kerja '.($i+1).' - Nama Atasan',       $w->supervisor_full_name],
                ["work_{$i}_supervisor_phone",      'Kerja '.($i+1).' - No. Telp Atasan',   $w->supervisor_phone],
            ];
            foreach ($fields as [$key, $label, $val]) {
                $inv = $invMap->get($key);
                $result[] = ['item_name' => $key, 'label' => $label, 'value' => $val ?? '-', 'validity' => $inv?->validity, 'inv_notes' => $inv?->notes];
            }
        }
        return $result;
    }

    private function buildCertSection($certs, $invMap): array
    {
        $result = [];
        foreach ($certs as $i => $c) {
            $fields = [
                ["cert_{$i}_certificate_name",     'Sertifikat '.($i+1).' - Nama Sertifikat',   $c->certificate_name],
                ["cert_{$i}_issuing_organization", 'Sertifikat '.($i+1).' - Instansi Penerbit', $c->issuing_organization],
                ["cert_{$i}_issue_date",           'Sertifikat '.($i+1).' - Tanggal Terbit',    $this->formatDate($c->issue_date)],
                ["cert_{$i}_expiration_date",      'Sertifikat '.($i+1).' - Masa Berlaku',      $this->formatDate($c->expiration_date)],
                ["cert_{$i}_certificate_score",    'Sertifikat '.($i+1).' - Skor / Level',      $c->certificate_score],
            ];
            foreach ($fields as [$key, $label, $val]) {
                $inv = $invMap->get($key);
                $result[] = ['item_name' => $key, 'label' => $label, 'value' => $val ?? '-', 'validity' => $inv?->validity, 'inv_notes' => $inv?->notes];
            }
        }
        return $result;
    }

    private function buildConductSection($works, $invMap): array
    {
        $conductFields = [
            'stabilitas_kehadiran' => 'Stabilitas Kehadiran',
            'kepatuhan_instruksi'  => 'Kepatuhan Instruksi',
            'kerja_sama_tim'       => 'Kerja Sama Tim',
            'sikap_kerja'          => 'Sikap Kerja',
            'pelanggaran_disiplin' => 'Pelanggaran Disiplin',
        ];

        $result = [];
        foreach ($works as $i => $w) {
            foreach ($conductFields as $fieldKey => $fieldLabel) {
                $itemName = "conduct_{$i}_{$fieldKey}";
                $inv      = $invMap->get($itemName);
                $result[] = [
                    'item_name'    => $itemName,
                    'label'        => $fieldLabel,
                    'company_name' => $w->company_name ?? "Perusahaan " . ($i + 1),
                    'validity'     => $inv?->validity,
                    'inv_notes'    => $inv?->notes,
                ];
            }
        }
        return $result;
    }
}