<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // applicant_profiles 取得
        $profile = ApplicantProfile::where('user_id', $user->id)->first();

        // 無料期間チェック
        $daysRemaining = 0;
        $isFreeAvailable = false;

        if ($profile) {
            $expires = Carbon::parse($profile->free_certification_expires_at);
            $daysRemaining = max(0, (int) now()->diffInDays($expires, false));
            $isFreeAvailable = !$profile->free_certification_used && $daysRemaining > 0;
        }

        // 最新の認証申請
        $latestRequest = CertificationRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        // 求人応募件数
        $applicationCount = JobApplication::where('applicant_id', $user->id)->count();

        return Inertia::render('Applicant/Dashboard', [
            'profile'          => $profile,
            'daysRemaining'    => $daysRemaining,
            'isFreeAvailable'  => $isFreeAvailable,
            'latestRequest'    => $latestRequest,
            'applicationCount' => $applicationCount,
        ]);
    }
}