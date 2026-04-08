<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\JobApplication;
use App\Jobs\RunPriorityAnalysisJob;
use App\Services\CaseNoService;          // ★ 追加
use Carbon\Carbon;

class ApplicantController extends Controller
{
    public function __construct(private CaseNoService $caseNoService) {}  // ★ DI

    public function dashboard()
    {
        $user = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->first();

        $freeExpiresAt = $profile?->free_certification_expires_at;
        $daysRemaining = $freeExpiresAt
            ? max(0, Carbon::now()->diffInDays(Carbon::parse($freeExpiresAt), false))
            : 0;
        $isFreeAvailable = $profile
            && !$profile->free_certification_used
            && $daysRemaining > 0;

        $latestRequest = CertificationRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        $applicationCount = JobApplication::where('applicant_id', $user->id)->count();

        return Inertia::render('Applicant/Dashboard', [
            'profile'          => $profile,
            'daysRemaining'    => (int) $daysRemaining,
            'isFreeAvailable'  => $isFreeAvailable,
            'latestRequest'    => $latestRequest,
            'applicationCount' => $applicationCount,
        ]);
    }

    /**
     * 認証申請の新規作成
     * ★ 申請作成後にAI事前分析Jobをキューに投入する
     */
    public function submitRequest(Request $request)
    {
        $user = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'payment_id' => 'nullable|exists:payments,id',
        ]);

        $isFree = !$profile->free_certification_used
            && $profile->free_certification_expires_at
            && Carbon::now()->lessThanOrEqualTo(Carbon::parse($profile->free_certification_expires_at));

        if (!$isFree && !$request->payment_id) {
            return back()->withErrors(['message' => 'Pembayaran diperlukan untuk mengajukan sertifikasi.']);
        }

        // ★ CaseNoService で採番
        $caseNo = $this->caseNoService->generate();

        $certRequest = CertificationRequest::create([
            'case_no'         => $caseNo,
            'user_id'         => $user->id,
            'current_status'  => 'under_investigation',
            'external_status' => 'under_review',
            'survey_status'   => 'under_investigation',
            'payment_id'      => $request->payment_id ?? null,
        ]);

        if ($isFree) {
            $profile->update(['free_certification_used' => true]);
        }

        // ★ AI事前分析 Job をキューに投入
        RunPriorityAnalysisJob::dispatch($certRequest->id)
            ->delay(now()->addSeconds(3));

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Pengajuan sertifikasi berhasil dikirim.');
    }
}