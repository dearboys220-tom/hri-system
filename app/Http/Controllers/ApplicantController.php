<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\JobApplication;
use Carbon\Carbon;

class ApplicantController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->first();

        // 無料期間の残り日数を計算
        $freeExpiresAt = $profile?->free_certification_expires_at;
        $daysRemaining = $freeExpiresAt
            ? max(0, Carbon::now()->diffInDays(Carbon::parse($freeExpiresAt), false))
            : 0;
        $isFreeAvailable = $profile
            && !$profile->free_certification_used
            && $daysRemaining > 0;

        // 最新の認証申請
        $latestRequest = CertificationRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        // 求人応募数
        $applicationCount = JobApplication::where('applicant_id', $user->id)->count();

        return Inertia::render('Applicant/Dashboard', [
            'profile'          => $profile,
            'daysRemaining'    => (int) $daysRemaining,
            'isFreeAvailable'  => $isFreeAvailable,
            'latestRequest'    => $latestRequest,
            'applicationCount' => $applicationCount,
        ]);
    }
}