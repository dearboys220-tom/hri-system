<?php

namespace App\Http\Controllers;

use App\Models\CertificationRequest;
use App\Models\ApplicantProfile;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $cases = CertificationRequest::with(['user:id,name'])
            ->where('survey_status', 'pending_admin')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($c) {
                $profile = ApplicantProfile::where('user_id', $c->user_id)
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
              ->where('survey_status', 'pending_admin')
              ->first();

            if ($cr) {
                $profile = ApplicantProfile::where('user_id', $cr->user_id)->first();
                $invMap    = $cr->investigationItems->keyBy('item_name');
                $reviewMap = $cr->reviewItems->keyBy('item_name');

                // user_id で学歴・職歴・資格を取得
                $educations = EducationHistory::where('user_id', $cr->user_id)->get();
                $works      = WorkHistory::where('user_id', $cr->user_id)->get();
                $certs      = Certification::where('user_id', $cr->user_id)->get();

                $deductions = $this->calcDeductions($cr->reviewItems);
                $finalScore = max(0, round(100 - $deductions['total_weighted'], 1));

                $detail = [
                    'id'                    => $cr->id,
                    'survey_status'         => $cr->survey_status,
                    'admin_notes'           => $cr->admin_notes,
                    'reviewer_comments'     => $cr->reviewer_comments,
                    'review_completed_date' => $cr->review_completed_date
                        ? Carbon::parse($cr->review_completed_date)->format('d/m/Y H:i')
                        : null,
                    'final_score'           => $finalScore,
                    'deductions'            => $deductions,

                    'profile' => $profile ? [
                        'full_name'       => $profile->full_name,
                        'nik'             => $profile->nik,
                        'ktp_address'     => $profile->ktp_address,
                        'ktp_card'        => $profile->ktp_card,
                        'gender'          => $profile->gender,
                        'marital_status'  => $profile->marital_status,
                        'nationality'     => $profile->nationality,
                        'birth_date'      => $profile->birth_date
                            ? Carbon::parse($profile->birth_date)->format('d/m/Y')
                            : null,
                        'current_address' => $profile->current_address,
                        'phone_number'    => $profile->phone_number,
                        'whatsapp_number' => $profile->whatsapp_number,
                        'profile_photo'   => $profile->profile_photo,
                        'member_id'       => $profile->member_id,
                    ] : null,

                    'inv_basic' => $this->buildSection(
                        $this->basicFields($profile),
                        $invMap, $reviewMap, 'basic_info'
                    ),
                    'inv_edu'  => $this->buildEduSection($educations, $invMap, $reviewMap),
                    'inv_work' => $this->buildWorkSection($works,      $invMap, $reviewMap),
                    'inv_cert' => $this->buildCertSection($certs,      $invMap, $reviewMap),
                ];
            }
        }

        return Inertia::render('Admin/Admin/AdminMain', [
            'cases'      => $cases,
            'detail'     => $detail,
            'selectedId' => (int) $selectedId,
        ]);
    }

    // ---- 承認 ----
    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $cr = CertificationRequest::where('id', $id)
            ->where('survey_status', 'pending_admin')
            ->firstOrFail();

        $profile = ApplicantProfile::where('user_id', $cr->user_id)->firstOrFail();

        $deductions = $this->calcDeductions($cr->reviewItems);
        $finalScore = max(0, round(100 - $deductions['total_weighted'], 1));

        $cr->update([
            'admin_approved'      => true,
            'admin_approval_date' => now(),
            'admin_notes'         => $validated['admin_notes'] ?? null,
            'survey_status'       => 'Terverifikasi',
        ]);

        $profile->update([
            'certification_status'      => 'Terverifikasi',
            'hri_score'                 => $finalScore,
            'certification_date'        => now(),
            'certification_expiry_date' => now()->addMonths(3),
        ]);

        return redirect()->route('admin.admin.index')
            ->with('success', 'Sertifikasi berhasil disetujui.');
    }

    // ---- 却下 ----
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $cr = CertificationRequest::where('id', $id)
            ->where('survey_status', 'pending_admin')
            ->firstOrFail();

        $cr->update([
            'survey_status'  => 'Ditolak',
            'admin_notes'    => $validated['admin_notes'],
            'admin_approved' => false,
        ]);

        return redirect()->route('admin.admin.index')
            ->with('success', 'Kasus berhasil ditolak.');
    }

    // ---- レビューチームへ差戻し ----
    public function returnToReviewer(Request $request, int $id)
    {
        $validated = $request->validate([
            'return_reason' => 'required|string|max:1000',
        ]);

        $cr = CertificationRequest::where('id', $id)
            ->where('survey_status', 'pending_admin')
            ->firstOrFail();

        $cr->update([
            'survey_status'         => 'under_review',
            'returned_to_applicant' => true,
            'return_reason'         => $validated['return_reason'],
        ]);

        return redirect()->route('admin.admin.index')
            ->with('success', 'Kasus dikembalikan ke Tim Reviewer.');
    }

    // ---- ダッシュボード ----
    public function dashboard(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $search       = $request->query('search', '');
        $page         = max(1, (int) $request->query('page_num', 1));
        $perPage      = 20;

        $stats = [
            'pending_payment'     => CertificationRequest::where('survey_status', 'pending_payment')->count(),
            'under_investigation' => CertificationRequest::where('survey_status', 'under_investigation')->count(),
            'under_review'        => CertificationRequest::where('survey_status', 'under_review')->count(),
            'pending_admin'       => CertificationRequest::where('survey_status', 'pending_admin')->count(),
            'perlu_koreksi'       => CertificationRequest::where('survey_status', 'Perlu Koreksi')->count(),
            'terverifikasi'       => ApplicantProfile::where('certification_status', 'Terverifikasi')->count(),
            'ditolak'             => CertificationRequest::where('survey_status', 'Ditolak')->count(),
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

    // ---- 企業管理 ----
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

    // ---- 企業ステータス変更 ----
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

    private function formatDate($date): ?string
    {
        if (!$date) return null;
        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    private function calcDeductions($reviewItems)
    {
        $weights = [
            'basic_info'    => 0.15,
            'education'     => 0.35,
            'work'          => 0.40,
            'certification' => 0.10,
        ];
        $maxDed = [
            'basic_info'    => 15,
            'education'     => 35,
            'work'          => 40,
            'certification' => 10,
        ];

        $byCategory = array_fill_keys(array_keys($weights), 0);
        foreach ($reviewItems as $r) {
            $cat = $r->category ?? 'other';
            if (isset($byCategory[$cat])) {
                $byCategory[$cat] += $r->actual_deduction;
            }
        }

        $totalWeighted = 0;
        foreach ($weights as $cat => $w) {
            $max    = $maxDed[$cat] ?? 1;
            $actual = $byCategory[$cat] ?? 0;
            $ratio  = $max > 0 ? min($actual / $max, 1.0) : 0;
            $totalWeighted += $ratio * $w * 100;
        }

        return [
            'by_category'    => $byCategory,
            'total_weighted' => round($totalWeighted, 2),
        ];
    }

    private function buildSection($fields, $invMap, $reviewMap, $category)
    {
        return array_map(function ($f) use ($invMap, $reviewMap) {
            $inv    = $invMap->get($f['key']);
            $review = $reviewMap->get($f['key']);
            return [
                'item_name'        => $f['key'],
                'label'            => $f['label'],
                'value'            => $f['value'] ?? '-',
                'validity'         => $inv    ? $inv->validity            : null,
                'inv_notes'        => $inv    ? $inv->notes               : null,
                'actual_deduction' => $review ? $review->actual_deduction : null,
                'max_deduction'    => $review ? $review->max_deduction    : null,
                'review_notes'     => $review ? $review->notes            : null,
            ];
        }, $fields);
    }

    private function basicFields($profile)
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

    private function buildEduSection($educations, $invMap, $reviewMap)
    {
        $result = [];
        foreach ($educations as $i => $edu) {
            $fields = [
                ["edu_{$i}_school_name",        'Pendidikan '.($i+1).' - Nama Sekolah',         $edu->school_name],
                ["edu_{$i}_education_level",    'Pendidikan '.($i+1).' - Tingkat Pendidikan',   $edu->education_level],
                ["edu_{$i}_school_location",    'Pendidikan '.($i+1).' - Alamat Sekolah',       $edu->school_location],
                ["edu_{$i}_degree_name",        'Pendidikan '.($i+1).' - Jurusan',              $edu->degree_name],
                ["edu_{$i}_enrollment_date",    'Pendidikan '.($i+1).' - Tanggal Masuk',        $this->formatDate($edu->enrollment_date)],
                ["edu_{$i}_graduation_date",    'Pendidikan '.($i+1).' - Tanggal Lulus',        $this->formatDate($edu->graduation_date)],
                ["edu_{$i}_graduation_status",  'Pendidikan '.($i+1).' - Status Kelulusan',     $edu->graduation_status],
                ["edu_{$i}_ipk_gpa",            'Pendidikan '.($i+1).' - IPK / Nilai Akhir',    $edu->ipk_gpa],
            ];
            foreach ($fields as [$key, $label, $val]) {
                $inv    = $invMap->get($key);
                $review = $reviewMap->get($key);
                $result[] = [
                    'item_name'        => $key,
                    'label'            => $label,
                    'value'            => $val ?? '-',
                    'validity'         => $inv    ? $inv->validity            : null,
                    'inv_notes'        => $inv    ? $inv->notes               : null,
                    'actual_deduction' => $review ? $review->actual_deduction : null,
                    'max_deduction'    => $review ? $review->max_deduction    : null,
                    'review_notes'     => $review ? $review->notes            : null,
                ];
            }
        }
        return $result;
    }

    private function buildWorkSection($works, $invMap, $reviewMap)
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
                $inv    = $invMap->get($key);
                $review = $reviewMap->get($key);
                $result[] = [
                    'item_name'        => $key,
                    'label'            => $label,
                    'value'            => $val ?? '-',
                    'validity'         => $inv    ? $inv->validity            : null,
                    'inv_notes'        => $inv    ? $inv->notes               : null,
                    'actual_deduction' => $review ? $review->actual_deduction : null,
                    'max_deduction'    => $review ? $review->max_deduction    : null,
                    'review_notes'     => $review ? $review->notes            : null,
                ];
            }
        }
        return $result;
    }

    private function buildCertSection($certs, $invMap, $reviewMap)
    {
        $result = [];
        foreach ($certs as $i => $c) {
            $fields = [
                ["cert_{$i}_certificate_name",     'Sertifikat '.($i+1).' - Nama Sertifikat',          $c->certificate_name],
                ["cert_{$i}_issuing_organization", 'Sertifikat '.($i+1).' - Instansi Penerbit',        $c->issuing_organization],
                ["cert_{$i}_issue_date",           'Sertifikat '.($i+1).' - Tanggal Terbit',           $this->formatDate($c->issue_date)],
                ["cert_{$i}_expiration_date",      'Sertifikat '.($i+1).' - Masa Berlaku',             $this->formatDate($c->expiration_date)],
                ["cert_{$i}_certificate_score",    'Sertifikat '.($i+1).' - Skor / Level / Tingkatan', $c->certificate_score],
            ];
            foreach ($fields as [$key, $label, $val]) {
                $inv    = $invMap->get($key);
                $review = $reviewMap->get($key);
                $result[] = [
                    'item_name'        => $key,
                    'label'            => $label,
                    'value'            => $val ?? '-',
                    'validity'         => $inv    ? $inv->validity            : null,
                    'inv_notes'        => $inv    ? $inv->notes               : null,
                    'actual_deduction' => $review ? $review->actual_deduction : null,
                    'max_deduction'    => $review ? $review->max_deduction    : null,
                    'review_notes'     => $review ? $review->notes            : null,
                ];
            }
        }
        return $result;
    }
}