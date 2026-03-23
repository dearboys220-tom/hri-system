<?php

namespace App\Http\Controllers;

use App\Models\CertificationRequest;
use App\Models\InvestigationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class InvestigatorController extends Controller
{
    // 案件一覧 + 選択中の案件詳細
    public function index(Request $request)
    {
        $user = Auth::user();

        // 自分に割り当てられた調査中の案件一覧
        $cases = CertificationRequest::with(['user:id,name,email'])
            ->where('assigned_investigator', $user->id)
            ->where('survey_status', 'under_investigation')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($c) {
                $profile = \App\Models\ApplicantProfile::where('user_id', $c->user_id)
                    ->first(['member_id', 'full_name']);
                return [
                    'id'            => $c->id,
                    'name'          => optional($profile)->full_name ?? optional($c->user)->name,
                    'member_id'     => optional($profile)->member_id ?? '-',
                    'submitted_at'  => $c->created_at->format('d/m/Y'),
                    'survey_status' => $c->survey_status,
                ];
            });

        // 選択中の案件ID（クエリパラメータ or 最初の案件）
        $selectedId = $request->query('id', optional($cases->first())['id']);
        $detail = null;

        if ($selectedId) {
            $cr = CertificationRequest::with([
                'user:id,name,email',
                'educationHistory',
                'workHistory',
                'certifications',
                'investigationItems',
            ])->where('assigned_investigator', $user->id)
              ->where('id', $selectedId)
              ->first();

            if ($cr) {
                // applicant_profiles を別途取得
                $profile = \App\Models\ApplicantProfile::where('user_id', $cr->user_id)->first();

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
                    'educations'     => $cr->educationHistory->map(fn($e) => [
                        'id'             => $e->id,
                        'school'         => $e->school,
                        'level'          => $e->level,
                        'major'          => $e->major,
                        'degree'         => $e->degree,
                        'enrollment_date'=> $e->enrollment_date,
                        'graduation_date'=> $e->graduation_date,
                        'gpa'            => $e->gpa,
                        'achievements'   => $e->achievements,
                    ]),
                    'works' => $cr->workHistory->map(fn($w) => [
                        'id'                 => $w->id,
                        'company'            => $w->company,
                        'position'           => $w->position,
                        'employment_type'    => $w->employment_type,
                        'start_date'         => $w->start_date,
                        'end_date'           => $w->end_date,
                        'duties'             => $w->duties,
                        'supervisor_name'    => $w->supervisor_name,
                        'supervisor_contact' => $w->supervisor_contact,
                    ]),
                    'certifications' => $cr->certifications->map(fn($c) => [
                        'id'           => $c->id,
                        'name'         => $c->name,
                        'organization' => $c->organization,
                        'issued_date'  => $c->issued_date,
                        'valid_until'  => $c->valid_until,
                    ]),
                    // 既存の調査記録をitem_name => validityのマップで返す
                    'investigation_map' => $cr->investigationItems
                        ->mapWithKeys(fn($i) => [$i->item_name => [
                            'validity' => $i->validity,
                            'notes'    => $i->notes,
                        ]]),
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
            'items.*.validity'     => 'required|in:VALID,INVALID',
            'items.*.notes'        => 'nullable|string',
        ]);

        // 内部メモを保存
        $cr->update([
            'investigation_notes' => $validated['investigation_notes'] ?? $cr->investigation_notes,
        ]);

        // 各調査項目をupsert
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

    // レビューチームへ送付（調査完了）
    public function complete(int $id)
    {
        $user = Auth::user();

        $cr = CertificationRequest::where('id', $id)
            ->where('assigned_investigator', $user->id)
            ->firstOrFail();

        $cr->update([
            'ready_for_review' => true,
            'survey_status'    => 'under_review',
        ]);

        return redirect()->route('admin.investigator.index')
            ->with('success', 'Kasus berhasil dikirim ke Tim Reviewer.');
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