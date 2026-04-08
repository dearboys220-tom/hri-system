<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Jobs\RunPriorityAnalysisJob;
use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use App\Models\Payment;
use App\Models\User;
use App\Services\CaseNoService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ConfirmationController extends Controller
{
    public function __construct(private CaseNoService $caseNoService) {}

    // ─────────────────────────────────────────────────────────────────
    // 確認画面の表示
    // ─────────────────────────────────────────────────────────────────

    public function show()
    {
        $user    = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->first();

        if (!$profile || !$profile->nik || !$profile->ktp_card) {
            return redirect()->route('applicant.identity')
                ->with('error', 'Lengkapi verifikasi identitas terlebih dahulu.');
        }

        $educations = EducationHistory::where('user_id', $user->id)->get();
        $works      = WorkHistory::where('user_id', $user->id)->get();
        $certs      = Certification::where('user_id', $user->id)->get();

        // 無料チェック
        $expires         = Carbon::parse($profile->free_certification_expires_at);
        $daysRemaining   = max(0, (int) now()->diffInDays($expires, false));
        $isFreeAvailable = !$profile->free_certification_used && $daysRemaining > 0;

        $latestRequest = CertificationRequest::where('user_id', $user->id)->latest()->first();
        if ($latestRequest && $latestRequest->survey_status === 'Perlu Koreksi') {
            $isFreeAvailable = true;
        }

        $price = $isFreeAvailable ? 0 : 35000;

        // pending_payment は申請済みに含めない（再決済可能）
        $isAlreadySubmitted = CertificationRequest::where('user_id', $user->id)
            ->whereNotIn('survey_status', ['Terverifikasi', 'Ditolak', 'pending_payment'])
            ->exists();

        $pendingPaymentRequest = CertificationRequest::where('user_id', $user->id)
            ->where('survey_status', 'pending_payment')
            ->latest()->first();

        return Inertia::render('Applicant/Confirmation', [
            'profile'               => $profile,
            'educations'            => $educations,
            'works'                 => $works,
            'certs'                 => $certs,
            'isFreeAvailable'       => $isFreeAvailable,
            'daysRemaining'         => $daysRemaining,
            'price'                 => $price,
            'isAlreadySubmitted'    => $isAlreadySubmitted,
            'pendingPaymentRequest' => $pendingPaymentRequest,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 申請送信処理
    // ─────────────────────────────────────────────────────────────────

    public function store()
    {
        $user    = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->first();

        $expires         = Carbon::parse($profile->free_certification_expires_at);
        $daysRemaining   = max(0, (int) now()->diffInDays($expires, false));
        $isFreeAvailable = !$profile->free_certification_used && $daysRemaining > 0;

        $latestRequest = CertificationRequest::where('user_id', $user->id)
            ->whereNotIn('survey_status', ['Terverifikasi', 'Ditolak', 'pending_payment'])
            ->latest()->first();

        if ($latestRequest && $latestRequest->survey_status !== 'Perlu Koreksi') {
            return back()->with('error', 'Pengajuan sedang diproses.');
        }

        if ($isFreeAvailable || ($latestRequest && $latestRequest->survey_status === 'Perlu Koreksi')) {

            // ── 調査員をラウンドロビンで割り当て ──────────────────────
            $investigators    = User::where('role_type', 'investigator_user')->get();
            $assignedInvestigator = null;

            if ($investigators->isNotEmpty()) {
                $assignedInvestigator = $investigators->sortBy(function ($inv) {
                    return CertificationRequest::where('assigned_investigator', $inv->id)
                        ->where('current_status', CertificationRequest::STATUS_UNDER_INVESTIGATION)
                        ->count();
                })->first();
            }
            // ──────────────────────────────────────────────────────────

            // ★ case_no を採番
            $caseNo = $this->caseNoService->generate();

            // ★ current_status / external_status を正しく設定
            $certRequest = CertificationRequest::create([
                'case_no'               => $caseNo,
                'user_id'               => $user->id,
                'current_status'        => CertificationRequest::STATUS_UNDER_INVESTIGATION,
                'external_status'       => CertificationRequest::EXT_UNDER_REVIEW,
                'survey_status'         => CertificationRequest::STATUS_UNDER_INVESTIGATION,
                'admin_approved'        => false,
                'returned_to_applicant' => false,
                'ready_for_review'      => false,
                'assigned_investigator' => $assignedInvestigator?->id,
            ]);

            // 学歴・職歴・資格に certification_request_id を紐付け
            EducationHistory::where('user_id', $user->id)
                ->whereNull('certification_request_id')
                ->update([
                    'certification_request_id' => $certRequest->id,
                    'case_no'                  => $caseNo,
                ]);

            WorkHistory::where('user_id', $user->id)
                ->whereNull('certification_request_id')
                ->update([
                    'certification_request_id' => $certRequest->id,
                    'case_no'                  => $caseNo,
                ]);

            Certification::where('user_id', $user->id)
                ->whereNull('certification_request_id')
                ->update([
                    'certification_request_id' => $certRequest->id,
                    'case_no'                  => $caseNo,
                ]);

            // 無料決済レコードを作成
            Payment::create([
                'user_id'                  => $user->id,
                'payment_type'             => 'certification',
                'amount'                   => 0,
                'is_free'                  => true,
                'payment_status'           => 'free',
                'related_certification_id' => $certRequest->id,
            ]);

            $profile->update(['free_certification_used' => true]);

            // ★ AI事前分析 Job をキューに投入（3秒後に実行）
            RunPriorityAnalysisJob::dispatch($certRequest->id)
                ->delay(now()->addSeconds(3));

            return redirect()->route('applicant.dashboard')
                ->with('success', 'Pengajuan sertifikasi berhasil dikirim!');
        }

        return redirect()->route('applicant.dashboard')
            ->with('info', 'Silakan lakukan pembayaran untuk melanjutkan.');
    }
}