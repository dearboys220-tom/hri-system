<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

class CertifiedResumeController extends Controller
{
    public function show()
    {
        $user    = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->firstOrFail();

        // 認証済みでない場合はダッシュボードへ
        if ($profile->certification_status !== 'Terverifikasi') {
            return redirect()->route('applicant.dashboard');
        }

        // 有効期限チェック
        $isValid       = false;
        $daysRemaining = 0;
        if ($profile->certification_expiry_date) {
            $expiry        = Carbon::parse($profile->certification_expiry_date);
            $daysRemaining = (int) now()->diffInDays($expiry, false);
            $isValid       = $daysRemaining >= 0;
        }

        // ===== user_id で学歴・職歴・資格を取得 =====
        $educations = EducationHistory::where('user_id', $user->id)
            ->orderBy('graduation_date', 'desc')
            ->get();

        $works = WorkHistory::where('user_id', $user->id)
            ->orderBy('employment_start_date', 'desc')
            ->get();

        $certifications = Certification::where('user_id', $user->id)
            ->orderBy('issue_date', 'desc')
            ->get();
        // ============================================

        return Inertia::render('Applicant/CertifiedResume', [
            'profile' => [
                'member_id'                 => $profile->member_id,
                'full_name'                 => $profile->full_name,
                'gender'                    => $profile->gender,
                'birth_date'                => $profile->birth_date,
                'nationality'               => $profile->nationality,
                'marital_status'            => $profile->marital_status,
                'phone_number'              => $profile->phone_number,
                'whatsapp_number'           => $profile->whatsapp_number,
                'current_address'           => $profile->current_address,
                'profile_photo'             => $profile->profile_photo,
                'self_pr'                   => $profile->self_pr,
                'certification_status'      => $profile->certification_status,
                'certification_date'        => $profile->certification_date,
                'certification_expiry_date' => $profile->certification_expiry_date,
                'email'                     => $user->email,
            ],
            'isValid'        => $isValid,
            'daysRemaining'  => $daysRemaining,
            'educations'     => $educations,
            'works'          => $works,
            'certifications' => $certifications,
        ]);
    }
}