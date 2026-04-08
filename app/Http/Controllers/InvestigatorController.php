<?php

namespace App\Http\Controllers;

use App\Models\CertificationRequest;
use App\Models\InvestigationItem;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use App\Models\InvestigationPriorityReport;   // ★ 追加
use App\Services\HriAiScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvestigatorController extends Controller
{
    // 案件一覧 + 選択中の案件詳細
    public function index(Request $request)
    {
        $user = Auth::user();

        $cases = CertificationRequest::with(['applicant:id,name,email'])
            ->where('assigned_investigator', $user->id)
            ->where('survey_status', 'under_investigation')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($c) {
                $profile = \App\Models\ApplicantProfile::where('user_id', $c->user_id)
                    ->first(['member_id', 'full_name']);
                return [
                    'id'            => $c->id,
                    'name'          => optional($profile)->full_name ?? optional($c->applicant)->name,
                    'member_id'     => optional($profile)->member_id ?? '-',
                    'submitted_at'  => $c->created_at->format('d/m/Y'),
                    'survey_status' => $c->survey_status,
                ];
            });

        $selectedId = $request->query('id', optional($cases->first())['id']);
        $detail = null;

        if ($selectedId) {
            $cr = CertificationRequest::with([
                'applicant:id,name,email',
                'investigationItems',
            ])->where('assigned_investigator', $user->id)
              ->where('id', $selectedId)
              ->first();

            if ($cr) {
                $profile    = \App\Models\ApplicantProfile::where('user_id', $cr->user_id)->first();
                $educations = EducationHistory::where('user_id', $cr->user_id)->get();
                $works      = WorkHistory::where('user_id', $cr->user_id)->get();
                $certs      = Certification::where('user_id', $cr->user_id)->get();

                // ★ AI事前分析レポートを取得
                $priorityReport = InvestigationPriorityReport::where('certification_request_id', $cr->id)
                    ->latest()
                    ->first();

                $detail = [
                    'id'                  => $cr->id,
                    'survey_status'       => $cr->survey_status,
                    'investigation_notes' => $cr->investigation_notes,
                    'return_reason'       => $cr->return_reason,

                    'profile' => $profile ? [
                        'full_name'       => $profile->full_name,
                        'nik'             => $profile->nik,
                        'ktp_address'     => $profile->ktp_address,
                        'ktp_card'        => $profile->ktp_card,
                        'gender'          => $profile->gender,
                        'marital_status'  => $profile->marital_status,
                        'nationality'     => $profile->nationality,
                        'birth_date'      => optional($profile->birth_date)->format('d/m/Y'),
                        'current_address' => $profile->current_address,
                        'phone_number'    => $profile->phone_number,
                        'whatsapp_number' => $profile->whatsapp_number,
                        'profile_photo'   => $profile->profile_photo,
                        'member_id'       => $profile->member_id,
                    ] : null,

                    'educations' => $educations->map(fn($e) => [
                        'id'                    => $e->id,
                        'school_name'           => $e->school_name,
                        'education_level'       => $e->education_level,
                        'school_location'       => $e->school_location,
                        'degree_name'           => $e->degree_name,
                        'enrollment_date'       => $e->enrollment_date,
                        'graduation_date'       => $e->graduation_date,
                        'graduation_status'     => $e->graduation_status,
                        'ipk_gpa'               => $e->ipk_gpa,
                        'academic_achievements' => $e->academic_achievements,
                        'ijazah_transcript'     => $e->ijazah_transcript,
                    ]),

                    'works' => $works->map(fn($w) => [
                        'id'                      => $w->id,
                        'company_name'            => $w->company_name,
                        'company_address'         => $w->company_address,
                        'department_position'     => $w->department_position,
                        'employment_type'         => $w->employment_type,
                        'employment_start_date'   => $w->employment_start_date,
                        'employment_end_date'     => $w->employment_end_date,
                        'job_description'         => $w->job_description,
                        'resignation_reason'      => $w->resignation_reason,
                        'employment_achievements' => $w->employment_achievements,
                        'supervisor_full_name'    => $w->supervisor_full_name,
                        'supervisor_position'     => $w->supervisor_position,
                        'supervisor_phone'        => $w->supervisor_phone,
                        'employment_certificate'  => $w->employment_certificate,
                    ]),

                    'certifications' => $certs->map(fn($c) => [
                        'id'                     => $c->id,
                        'certificate_name'       => $c->certificate_name,
                        'issuing_organization'   => $c->issuing_organization,
                        'issue_date'             => $c->issue_date,
                        'expiration_date'        => $c->expiration_date,
                        'certificate_score'      => $c->certificate_score,
                        'certificate_notes'      => $c->certificate_notes,
                        'certificate_file'       => $c->certificate_file,
                        'certificate_attachment' => $c->certificate_attachment,
                    ]),

                    'investigation_map' => $cr->investigationItems
                        ->mapWithKeys(fn($i) => [$i->item_name => [
                            'validity' => $i->validity,
                            'notes'    => $i->notes,
                        ]]),

                    // ★ AI事前分析レポート
                    'priority_report' => $priorityReport ? [
                        'priority_high'    => $priorityReport->priority_high_json ?? [],
                        'priority_medium'  => $priorityReport->priority_medium_json ?? [],
                        'priority_low'     => $priorityReport->priority_low_json ?? [],
                        'conduct_contacts' => $priorityReport->conduct_contacts_json ?? [],
                        'risk_flags'       => $priorityReport->risk_flags_json ?? [],
                        'summary'          => $priorityReport->ai_analysis_summary,
                        'generated_at'     => optional($priorityReport->generated_at)->format('d/m/Y H:i'),
                    ] : null,
                ];
            }
        }

        return Inertia::render('Admin/Investigator/InvestigatorMain', [
            'cases'      => $cases,
            'detail'     => $detail,
            'selectedId' => (int) $selectedId,
        ]);
    }

    // 調査結果を保存
    public function save(Request $request, int $id)
    {
        $user = Auth::user();

        $cr = CertificationRequest::where('id', $id)
            ->where('assigned_investigator', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'investigation_notes'  => 'nullable|string',
            'items'                => 'array',
            'items.*.item_name'    => 'required|string|max:100',
            'items.*.category'     => 'required|string|max:50',
            'items.*.validity'     => 'required|in:VALID,INVALID,UNVERIFIED',
            'items.*.notes'        => 'nullable|string',
        ]);

        $cr->update([
            'investigation_notes' => $validated['investigation_notes'] ?? $cr->investigation_notes,
        ]);

        foreach ($validated['items'] ?? [] as $item) {
            InvestigationItem::updateOrCreate(
                [
                    'certification_request_id' => $cr->id,
                    'item_name'                => $item['item_name'],
                ],
                [
                    'category'   => $item['category'],
                    'validity'   => $item['validity'],
                    'notes'      => $item['notes'] ?? null,
                    'checked_by' => $user->id,
                    'checked_at' => now(),
                ]
            );
        }

        return back()->with('success', 'Data berhasil disimpan.');
    }

    // ★ 調査完了 → AI自動採点 → 管理審査部へ送付
    public function complete(int $id)
    {
        $user = Auth::user();

        $cr = CertificationRequest::where('id', $id)
            ->where('assigned_investigator', $user->id)
            ->firstOrFail();

        // investigation_items が1件もない場合はエラー
        $itemCount = InvestigationItem::where('certification_request_id', $id)->count();
        if ($itemCount === 0) {
            return back()->withErrors([
                'error' => 'Harap isi minimal 1 item investigasi sebelum menyelesaikan.',
            ]);
        }

        // ステータスを一時的に「レビュー中」に
        $cr->update(['survey_status' => 'under_review']);

        // AI自動採点を実行
        $service = new HriAiScoringService();
        $success = $service->score($cr->id);

        if (!$success) {
            // AI失敗時はフォールバック：そのまま管理部へ送付
            $cr->update([
                'survey_status'    => 'pending_admin',
                'ready_for_review' => true,
            ]);

            return redirect()->route('admin.investigator.index')
                ->with('warning', 'Investigasi selesai. AI scoring gagal — kasus dikirim ke Admin secara manual.');
        }

        return redirect()->route('admin.investigator.index')
            ->with('success', 'Investigasi selesai. AI scoring berhasil — kasus dikirim ke Tim Admin.');
    }

    // ユーザーへ差戻し
    public function correction(Request $request, int $id)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'return_reason' => 'required|string',
        ]);

        $cr = CertificationRequest::where('id', $id)
            ->where('assigned_investigator', $user->id)
            ->firstOrFail();

        $cr->update([
            'survey_status' => 'Perlu Koreksi',
            'return_reason' => $validated['return_reason'],
        ]);

        return back()->with('success', 'Permintaan koreksi berhasil dikirim ke anggota.');
    }
}