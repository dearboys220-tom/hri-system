<?php

namespace App\Http\Controllers;

use App\Models\CertificationRequest;
use App\Models\ApplicantProfile;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use App\Models\CaseReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ================================================================
    // 案件一覧 + 詳細
    // ================================================================

    public function index(Request $request)
    {
        // pending_admin に加えて escalated_to_human も表示
        $cases = CertificationRequest::with(['applicant:id,name'])
            ->whereIn('survey_status', ['pending_admin', 'escalated_to_human'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($c) {
                $profile = ApplicantProfile::where('user_id', $c->user_id)
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
                'reviewItems',
                'latestCaseReview',
            ])->where('id', $selectedId)
              ->whereIn('survey_status', ['pending_admin', 'escalated_to_human'])
              ->first();

            if ($cr) {
                $profile    = ApplicantProfile::where('user_id', $cr->user_id)->first();
                $invMap     = $cr->investigationItems->keyBy('item_name');
                $educations = EducationHistory::where('user_id', $cr->user_id)->get();
                $works      = WorkHistory::where('user_id', $cr->user_id)->get();
                $certs      = Certification::where('user_id', $cr->user_id)->get();

                // v2.4 加点方式スコア
                $scoreData  = $this->buildScoreData($cr);

                $detail = [
                    'id'            => $cr->id,
                    'survey_status' => $cr->survey_status,
                    'admin_notes'   => $cr->admin_notes,

                    // v2.4 Claudeスコア指標
                    'base_score'               => $cr->base_score,
                    'truthfulness_percent'     => $cr->truthfulness_percent,
                    'consistency_percent'      => $cr->consistency_percent,
                    'hri_suitability_score'    => $cr->hri_suitability_score,
                    'work_ability_score'       => $cr->work_ability_score,
                    'work_ability_band'        => $cr->work_ability_band,
                    'claude_overall_judgment'  => $cr->claude_overall_judgment,
                    'claude_overall_reason'    => $cr->claude_overall_reason,
                    'enterprise_view_summary'  => $cr->enterprise_view_summary,
                    'final_decision'           => $cr->final_decision,
                    'ai_review_completed_at'   => $cr->ai_review_completed_at
                        ? Carbon::parse($cr->ai_review_completed_at)->format('d/m/Y H:i')
                        : null,

                    // review_items（項目別スコア詳細）
                    'score_items' => $scoreData,

                    // case_reviews（Claude総合判定の生データ）
                    'case_review' => $cr->latestCaseReview ? [
                        'final_decision'          => $cr->latestCaseReview->final_decision,
                        'verified_items'          => $cr->latestCaseReview->verified_items_json ?? [],
                        'unverified_items'        => $cr->latestCaseReview->unverified_items_json ?? [],
                        'risk_flags'              => $cr->latestCaseReview->risk_flags_json ?? [],
                        'conditions'              => $cr->latestCaseReview->conditions_json ?? [],
                        'compliance_return'       => $cr->latestCaseReview->compliance_return_json ?? null,
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
                            ? Carbon::parse($profile->birth_date)->format('d/m/Y')
                            : null,
                        'current_address' => $profile->current_address,
                        'phone_number'    => $profile->phone_number,
                        'whatsapp_number' => $profile->whatsapp_number,
                        'profile_photo'   => $profile->profile_photo,
                        'member_id'       => $profile->member_id,
                    ] : null,

                    // 調査結果（カテゴリ別）
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
    // 承認アクション
    // ================================================================

    // ✅ 承認
    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $cr = CertificationRequest::whereIn('survey_status', ['pending_admin', 'escalated_to_human'])
            ->findOrFail($id);

        $profile    = ApplicantProfile::where('user_id', $cr->user_id)->firstOrFail();
        $finalScore = (int) round($cr->base_score ?? 0);

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

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Sertifikasi berhasil disetujui.');
    }

    // ✅ 条件付き承認 [v2.4]
    public function conditionalApprove(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $cr = CertificationRequest::whereIn('survey_status', ['pending_admin', 'escalated_to_human'])
            ->findOrFail($id);

        $profile    = ApplicantProfile::where('user_id', $cr->user_id)->firstOrFail();
        $finalScore = (int) round($cr->base_score ?? 0);

        $cr->update([
            'admin_approved'      => true,
            'admin_approval_date' => now(),
            'admin_notes'         => $validated['admin_notes'],
            'survey_status'       => 'conditional_approved',
        ]);

        $profile->update([
            'certification_status'      => 'conditional_approved',
            'hri_score'                 => $finalScore,
            'certification_date'        => now(),
            'certification_expiry_date' => now()->addMonths(3),
        ]);

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Sertifikasi disetujui dengan kondisi.');
    }

    // ❌ 却下
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $cr = CertificationRequest::whereIn('survey_status', ['pending_admin', 'escalated_to_human'])
            ->findOrFail($id);

        $cr->update([
            'survey_status'  => 'Ditolak',
            'admin_notes'    => $validated['admin_notes'],
            'admin_approved' => false,
        ]);

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Kasus berhasil ditolak.');
    }

    // ⚠️ 調査部へ差し戻し
    public function returnToReviewer(Request $request, int $id)
    {
        $validated = $request->validate([
            'return_reason' => 'required|string|max:1000',
        ]);

        $cr = CertificationRequest::whereIn('survey_status', ['pending_admin', 'escalated_to_human'])
            ->findOrFail($id);

        $cr->update([
            'survey_status'         => 'under_investigation',
            'returned_to_applicant' => false,
            'return_reason'         => $validated['return_reason'],
            'ready_for_review'      => false,
        ]);

        return redirect()->route('admin.admin.evaluate')
            ->with('success', 'Kasus dikembalikan ke Tim Investigator.');
    }

    // 👤 人間確認へエスカレート [v2.4]
    public function escalateToHuman(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $cr = CertificationRequest::where('survey_status', 'pending_admin')
            ->findOrFail($id);

        $cr->update([
            'survey_status' => 'escalated_to_human',
            'admin_notes'   => $validated['admin_notes'],
        ]);

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
            'pending_payment'     => CertificationRequest::where('survey_status', 'pending_payment')->count(),
            'under_investigation' => CertificationRequest::where('survey_status', 'under_investigation')->count(),
            'under_review'        => CertificationRequest::where('survey_status', 'under_review')->count(),
            'pending_admin'       => CertificationRequest::where('survey_status', 'pending_admin')->count(),
            'escalated_to_human'  => CertificationRequest::where('survey_status', 'escalated_to_human')->count(),
            'perlu_koreksi'       => CertificationRequest::where('survey_status', 'Perlu Koreksi')->count(),
            'terverifikasi'       => ApplicantProfile::where('certification_status', 'Terverifikasi')->count(),
            'conditional'         => ApplicantProfile::where('certification_status', 'conditional_approved')->count(),
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

    private function formatDate($date): ?string
    {
        if (!$date) return null;
        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    // v2.4 加点方式スコアデータ構築
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
        foreach ($cr->reviewItems->where('is_ai_scored', true) as $item) {
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
                'item_name'   => $f['key'],
                'label'       => $f['label'],
                'value'       => $f['value'] ?? '-',
                'validity'    => $inv ? $inv->validity : null,
                'inv_notes'   => $inv ? $inv->notes    : null,
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
                $inv      = $invMap->get($key);
                $result[] = [
                    'item_name' => $key,
                    'label'     => $label,
                    'value'     => $val ?? '-',
                    'validity'  => $inv ? $inv->validity : null,
                    'inv_notes' => $inv ? $inv->notes    : null,
                ];
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
                $inv      = $invMap->get($key);
                $result[] = [
                    'item_name' => $key,
                    'label'     => $label,
                    'value'     => $val ?? '-',
                    'validity'  => $inv ? $inv->validity : null,
                    'inv_notes' => $inv ? $inv->notes    : null,
                ];
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
                $inv      = $invMap->get($key);
                $result[] = [
                    'item_name' => $key,
                    'label'     => $label,
                    'value'     => $val ?? '-',
                    'validity'  => $inv ? $inv->validity : null,
                    'inv_notes' => $inv ? $inv->notes    : null,
                ];
            }
        }
        return $result;
    }

    // v2.4 素行（conduct）セクション
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
            $companyName = $w->company_name ?? "Perusahaan " . ($i + 1);
            foreach ($conductFields as $fieldKey => $fieldLabel) {
                $itemName = "conduct_{$i}_{$fieldKey}";
                $inv      = $invMap->get($itemName);
                $result[] = [
                    'item_name'    => $itemName,
                    'label'        => $fieldLabel,
                    'company_name' => $companyName,
                    'validity'     => $inv ? $inv->validity : null,
                    'inv_notes'    => $inv ? $inv->notes    : null,
                ];
            }
        }
        return $result;
    }
}