<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ConfirmationController extends Controller
{
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
        $daysRemaining   = 0;
        $isFreeAvailable = false;
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

        // pending_payment のレコードを取得（再決済用）
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

            // ===== 調査員をラウンドロビンで割り当て =====
            $investigators = User::where('role_type', 'investigator_user')->get();
            $assignedInvestigator = null;

            if ($investigators->isNotEmpty()) {
                // 現在 under_investigation 件数が最も少ない調査員を選択
                $assignedInvestigator = $investigators->sortBy(function ($inv) {
                    return CertificationRequest::where('assigned_investigator', $inv->id)
                        ->where('survey_status', 'under_investigation')
                        ->count();
                })->first();
            }
            // =============================================

            $request = CertificationRequest::create([
                'user_id'               => $user->id,
                'survey_status'         => 'under_investigation',
                'admin_approved'        => false,
                'returned_to_applicant' => false,
                'ready_for_review'      => false,
                'assigned_investigator' => $assignedInvestigator?->id,
            ]);

            Payment::create([
                'user_id'                  => $user->id,
                'payment_type'             => 'certification',
                'amount'                   => 0,
                'is_free'                  => true,
                'payment_status'           => 'free',
                'related_certification_id' => $request->id,
            ]);

            $profile->update(['free_certification_used' => true]);

            return redirect()->route('applicant.dashboard')
                ->with('success', 'Pengajuan sertifikasi berhasil dikirim!');
        }

        return redirect()->route('applicant.dashboard')
            ->with('info', 'Silakan lakukan pembayaran untuk melanjutkan.');
    }
}