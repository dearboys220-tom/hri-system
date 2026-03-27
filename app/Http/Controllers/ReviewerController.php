<?php

namespace App\Http\Controllers;

use App\Models\CertificationRequest;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use App\Models\ReviewItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReviewerController extends Controller
{
    const WEIGHTS = [
        'basic_info'    => 0.15,
        'education'     => 0.35,
        'work'          => 0.40,
        'certification' => 0.10,
        'other'         => 0.00,
    ];

    const MAX_DEDUCTIONS = [
        'basic_info'    => 15,
        'education'     => 35,
        'work'          => 40,
        'certification' => 10,
    ];

    public function index(Request $request)
    {
        $user = Auth::user();

        $cases = CertificationRequest::with(['user:id,name'])
            ->where('survey_status', 'under_review')
            ->where('ready_for_review', true)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($c) {
                $profile = \App\Models\ApplicantProfile::where('user_id', $c->user_id)
                    ->first(['member_id', 'full_name']);
                return [
                    'id'           => $c->id,
                    'name'         => optional($profile)->full_name ?? optional($c->user)->name,
                    'member_id'    => optional($profile)->member_id ?? '-',
                    'submitted_at' => $c->created_at->format('d/m/Y'),
                ];
            });

        $selectedId = $request->query('id', optional($cases->first())['id']);
        $detail = null;

        if ($selectedId) {
            $cr = CertificationRequest::with([
                'user:id,name,email',
                'investigationItems',
                'reviewItems',
            ])->where('id', $selectedId)
              ->where('survey_status', 'under_review')
              ->first();

            if ($cr) {
                $profile = \App\Models\ApplicantProfile::where('user_id', $cr->user_id)->first();
                $invMap  = $cr->investigationItems->groupBy('category');

                // ===== user_id で学歴・職歴・資格を取得 =====
                $educations = EducationHistory::where('user_id', $cr->user_id)->get();
                $works      = WorkHistory::where('user_id', $cr->user_id)->get();
                $certs      = Certification::where('user_id', $cr->user_id)->get();
                // ===========================================

                $reviewMap = $cr->reviewItems->mapWithKeys(fn($r) => [
                    $r->item_name => [
                        'actual_deduction' => $r->actual_deduction,
                        'notes'            => $r->notes,
                    ]
                ]);

                $deductions = $this->calcDeductions($cr->reviewItems);

                $detail = [
                    'id'                => $cr->id,
                    'survey_status'     => $cr->survey_status,
                    'reviewer_comments' => $cr->reviewer_comments,
                    'profile' => $profile ? [
                        'full_name'       => $profile->full_name,
                        'nik'             => $profile->nik,
                        'ktp_address'     => $profile->ktp_address,
                        'ktp_card'        => $profile->ktp_card,
                        'gender'          => $profile->gender,
                        'marital_status'  => $profile->marital_status,
                        'nationality'     => $profile->nationality,
                        'birth_date'      => $profile->birth_date
                            ? \Carbon\Carbon::parse($profile->birth_date)->format('d/m/Y')
                            : null,
                        'current_address' => $profile->current_address,
                        'phone_number'    => $profile->phone_number,
                        'whatsapp_number' => $profile->whatsapp_number,
                        'profile_photo'   => $profile->profile_photo,
                        'member_id'       => $profile->member_id,
                    ] : null,
                    'inv_basic' => $this->mergeBasicInfo($profile, $invMap->get('basic_info', collect())),
                    'inv_edu'   => $this->mergeEducation($educations, $invMap->get('education', collect())),
                    'inv_work'  => $this->mergeWork($works, $invMap->get('work', collect())),
                    'inv_cert'  => $this->mergeCert($certs, $invMap->get('certification', collect())),
                    'review_map'  => $reviewMap,
                    'deductions'  => $deductions,
                    'final_score' => max(0, 100 - $deductions['total_weighted']),
                ];
            }
        }

        return Inertia::render('Admin/Reviewer/ReviewerMain', [
            'cases'      => $cases,
            'detail'     => $detail,
            'selectedId' => (int) $selectedId,
        ]);
    }

    public function save(Request $request, int $id)
    {
        $user = Auth::user();

        $cr = CertificationRequest::where('id', $id)
            ->where('survey_status', 'under_review')
            ->firstOrFail();

        $validated = $request->validate([
            'reviewer_comments'        => 'nullable|string',
            'items'                    => 'array',
            'items.*.item_name'        => 'required|string|max:100',
            'items.*.category'         => 'required|string|max:50',
            'items.*.actual_deduction' => 'required|numeric|min:0|max:100',
            'items.*.notes'            => 'nullable|string',
        ]);

        $cr->update([
            'reviewer_comments' => $validated['reviewer_comments'] ?? $cr->reviewer_comments,
            'assigned_reviewer' => $user->id,
        ]);

        foreach ($validated['items'] ?? [] as $item) {
            $cat    = $item['category'] ?? 'other';
            $weight = self::WEIGHTS[$cat] ?? 0.00;
            $max    = self::MAX_DEDUCTIONS[$cat] ?? 10;

            ReviewItem::updateOrCreate(
                [
                    'certification_request_id' => $cr->id,
                    'item_name'                => $item['item_name'],
                ],
                [
                    'category'         => $cat,
                    'max_deduction'    => $max,
                    'actual_deduction' => $item['actual_deduction'],
                    'weight'           => $weight,
                    'notes'            => $item['notes'] ?? null,
                    'reviewed_by'      => $user->id,
                    'reviewed_at'      => now(),
                ]
            );
        }

        return back()->with('success', 'Penilaian berhasil disimpan.');
    }

    public function complete(int $id)
    {
        $cr = CertificationRequest::where('id', $id)
            ->where('survey_status', 'under_review')
            ->firstOrFail();

        $cr->update([
            'survey_status'         => 'pending_admin',
            'review_completed_date' => now(),
        ]);

        return redirect()->route('admin.reviewer.index')
            ->with('success', 'Kasus berhasil dikirim ke Tim Admin.');
    }

    public function returnToInvestigator(Request $request, int $id)
    {
        $validated = $request->validate([
            'return_reason' => 'required|string',
        ]);

        $cr = CertificationRequest::where('id', $id)
            ->where('survey_status', 'under_review')
            ->firstOrFail();

        $cr->update([
            'survey_status'    => 'under_investigation',
            'ready_for_review' => false,
            'return_reason'    => $validated['return_reason'],
        ]);

        return back()->with('success', 'Kasus dikembalikan ke Tim Investigasi.');
    }

    // ---- ヘルパー ----

    private function calcDeductions($reviewItems)
    {
        $byCategory = [
            'basic_info'    => 0,
            'education'     => 0,
            'work'          => 0,
            'certification' => 0,
            'other'         => 0,
        ];

        foreach ($reviewItems as $r) {
            $cat = $r->category ?? 'other';
            $byCategory[$cat] = ($byCategory[$cat] ?? 0) + $r->actual_deduction;
        }

        $totalWeighted = 0;
        foreach (array_keys(self::WEIGHTS) as $cat) {
            $max    = self::MAX_DEDUCTIONS[$cat] ?? 1;
            $actual = $byCategory[$cat] ?? 0;
            $ratio  = $max > 0 ? min($actual / $max, 1.0) : 0;
            $totalWeighted += $ratio * self::WEIGHTS[$cat] * 100;
        }

        return [
            'by_category'    => $byCategory,
            'total_weighted' => round($totalWeighted, 2),
        ];
    }

    private function mergeBasicInfo($profile, $invItems)
    {
        if (!$profile) return [];

        $invByName = $invItems->keyBy('item_name');

        $fields = [
            'full_name'       => ['label' => 'Nama Lengkap',      'max' => 2],
            'nik'             => ['label' => 'NIK',               'max' => 3],
            'ktp_address'     => ['label' => 'Alamat KTP',        'max' => 3],
            'gender'          => ['label' => 'Jenis Kelamin',     'max' => 1],
            'marital_status'  => ['label' => 'Status Pernikahan', 'max' => 1],
            'nationality'     => ['label' => 'Kewarganegaraan',   'max' => 1],
            'birth_date'      => ['label' => 'Tanggal Lahir',     'max' => 2],
            'current_address' => ['label' => 'Alamat Saat Ini',   'max' => 3],
            'phone_number'    => ['label' => 'Telepon',           'max' => 2],
            'whatsapp_number' => ['label' => 'WhatsApp',          'max' => 1],
        ];

        $result = [];
        foreach ($fields as $key => $f) {
            $inv = $invByName->get($key);

            // birth_date は dd/mm/yyyy に変換
            $value = $profile->$key ?? '-';
            if ($key === 'birth_date' && $value && $value !== '-') {
                $value = \Carbon\Carbon::parse($value)->format('d/m/Y');
            }

            $result[] = [
                'item_name'     => $key,
                'label'         => $f['label'],
                'max_deduction' => $f['max'],
                'value'         => $value,
                'validity'      => $inv ? $inv->validity : null,
                'notes'         => $inv ? $inv->notes : null,
            ];
        }
        return $result;
    }

    private function mergeEducation($educations, $invItems)
    {
        $invByName = $invItems->keyBy('item_name');
        $result = [];

        foreach ($educations as $i => $edu) {
            $fields = [
                "edu_{$i}_school_name"      => ['label' => 'Nama Sekolah',        'value' => $edu->school_name,       'max' => 4],
                "edu_{$i}_education_level"  => ['label' => 'Tingkat Pendidikan',  'value' => $edu->education_level,   'max' => 3],
                "edu_{$i}_school_location"  => ['label' => 'Alamat Sekolah',      'value' => $edu->school_location,   'max' => 2],
                "edu_{$i}_degree_name"      => ['label' => 'Jurusan',             'value' => $edu->degree_name,       'max' => 3],
                "edu_{$i}_enrollment_date"  => ['label' => 'Tanggal Masuk',       'value' => $edu->enrollment_date,   'max' => 2],
                "edu_{$i}_graduation_date"  => ['label' => 'Tanggal Lulus',       'value' => $edu->graduation_date,   'max' => 3],
                "edu_{$i}_graduation_status"=> ['label' => 'Status Kelulusan',    'value' => $edu->graduation_status, 'max' => 2],
                "edu_{$i}_ipk_gpa"          => ['label' => 'IPK / Nilai Akhir',   'value' => $edu->ipk_gpa,           'max' => 3],
            ];
            foreach ($fields as $key => $f) {
                $inv = $invByName->get($key);
                $result[] = [
                    'item_name'     => $key,
                    'label'         => 'Pendidikan ' . ($i + 1) . ' - ' . $f['label'],
                    'max_deduction' => $f['max'],
                    'value'         => $f['value'] ?? '-',
                    'validity'      => $inv ? $inv->validity : null,
                    'notes'         => $inv ? $inv->notes : null,
                ];
            }
        }
        return $result;
    }

    private function mergeWork($works, $invItems)
    {
        $invByName = $invItems->keyBy('item_name');
        $result = [];

        foreach ($works as $i => $w) {
            $fields = [
                "work_{$i}_company_name"          => ['label' => 'Nama Perusahaan',   'value' => $w->company_name,                          'max' => 4],
                "work_{$i}_company_address"       => ['label' => 'Alamat Perusahaan', 'value' => $w->company_address,                       'max' => 2],
                "work_{$i}_department_position"   => ['label' => 'Jabatan',           'value' => $w->department_position,                   'max' => 4],
                "work_{$i}_employment_type"       => ['label' => 'Jenis Pekerjaan',   'value' => $w->employment_type,                       'max' => 2],
                "work_{$i}_employment_start_date" => ['label' => 'Tanggal Mulai',     'value' => $w->employment_start_date,                 'max' => 3],
                "work_{$i}_employment_end_date"   => ['label' => 'Tanggal Selesai',   'value' => $w->employment_end_date ?? 'Masih Bekerja', 'max' => 3],
                "work_{$i}_supervisor_full_name"  => ['label' => 'Nama Atasan',       'value' => $w->supervisor_full_name,                  'max' => 3],
                "work_{$i}_supervisor_phone"      => ['label' => 'No. Telp Atasan',   'value' => $w->supervisor_phone,                      'max' => 2],
            ];
            foreach ($fields as $key => $f) {
                $inv = $invByName->get($key);
                $result[] = [
                    'item_name'     => $key,
                    'label'         => 'Kerja ' . ($i + 1) . ' - ' . $f['label'],
                    'max_deduction' => $f['max'],
                    'value'         => $f['value'] ?? '-',
                    'validity'      => $inv ? $inv->validity : null,
                    'notes'         => $inv ? $inv->notes : null,
                ];
            }
        }
        return $result;
    }

    private function mergeCert($certs, $invItems)
    {
        $invByName = $invItems->keyBy('item_name');
        $result = [];

        foreach ($certs as $i => $c) {
            $fields = [
                "cert_{$i}_certificate_name"     => ['label' => 'Nama Sertifikat',        'value' => $c->certificate_name,     'max' => 3],
                "cert_{$i}_issuing_organization" => ['label' => 'Instansi Penerbit',      'value' => $c->issuing_organization, 'max' => 2],
                "cert_{$i}_issue_date"           => ['label' => 'Tanggal Terbit',         'value' => $c->issue_date,           'max' => 2],
                "cert_{$i}_expiration_date"      => ['label' => 'Masa Berlaku',           'value' => $c->expiration_date,      'max' => 2],
                "cert_{$i}_certificate_score"    => ['label' => 'Skor / Level / Tingkatan','value' => $c->certificate_score,   'max' => 1],
            ];
            foreach ($fields as $key => $f) {
                $inv = $invByName->get($key);
                $result[] = [
                    'item_name'     => $key,
                    'label'         => 'Sertifikat ' . ($i + 1) . ' - ' . $f['label'],
                    'max_deduction' => $f['max'],
                    'value'         => $f['value'] ?? '-',
                    'validity'      => $inv ? $inv->validity : null,
                    'notes'         => $inv ? $inv->notes : null,
                ];
            }
        }
        return $result;
    }
}