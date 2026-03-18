<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ConfirmationController extends Controller
{
    // 最終確認ページ表示
    public function show()
    {
        $user    = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->first();

        // 未入力チェック
        if (!$profile || !$profile->nik || !$profile->ktp_card) {
            return redirect()->route('applicant.identity')
                ->with('error', 'Lengkapi verifikasi identitas terlebih dahulu.');
        }

        $educations = EducationHistory::where('user_id', $user->id)->get();
        $works      = WorkHistory::where('user_id', $user->id)->get();
        $certs      = Certification::where('user_id', $user->id)->get();

        // 無料チェック
        $isFreeAvailable = false;
        $daysRemaining   = 0;
        if ($profile) {
            $expires = Carbon::parse($profile->free_certification_expires_at);
            $daysRemaining = max(0, (int) now()->diffInDays($expires, false));
            $isFreeAvailable = !$profile->free_certification_used && $daysRemaining > 0;

            // 補正依頼中は無料
            $latestRequest = CertificationRequest::where('user_id', $user->id)->latest()->first();
            if ($latestRequest && $latestRequest->survey_status === 'Perlu Koreksi') {
                $isFreeAvailable = true;
            }
        }

        $price = $isFreeAvailable ? 0 : 35000;

        // 申請済みかチェック
        $isAlreadySubmitted = CertificationRequest::where('user_id', $user->id)
            ->whereNotIn('survey_status', ['Terverifikasi', 'Ditolak'])
            ->exists();

        return Inertia::render('Applicant/Confirmation', [
            'profile'         => $profile,
            'educations'      => $educations,
            'works'           => $works,
            'certs'           => $certs,
            'isFreeAvailable' => $isFreeAvailable,
            'daysRemaining'   => $daysRemaining,
            'price'           => $price,
            'isAlreadySubmitted'  => $isAlreadySubmitted,
        ]);
    }

    // 申請送信
    public function store()
    {
        $user    = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->first();

        // 無料チェック
        $expires = Carbon::parse($profile->free_certification_expires_at);
        $daysRemaining = max(0, (int) now()->diffInDays($expires, false));
        $isFreeAvailable = !$profile->free_certification_used && $daysRemaining > 0;

        // 補正依頼中は無料
        $latestRequest = CertificationRequest::where('user_id', $user->id)
            ->whereNotIn('survey_status', ['Terverifikasi', 'Ditolak'])
            ->latest()->first();

        if ($latestRequest && $latestRequest->survey_status !== 'Perlu Koreksi') {
            return back()->with('error', 'Pengajuan sedang diproses.');
        }

        if ($isFreeAvailable || ($latestRequest && $latestRequest->survey_status === 'Perlu Koreksi')) {
            // 無料申請
            $request = CertificationRequest::create([
                'user_id'       => $user->id,
                'survey_status' => 'under_investigation',
                'admin_approved'          => false,
                'returned_to_applicant'   => false,
                'ready_for_review'        => false,
            ]);

            // paymentsレコード作成（amount=0）
            \App\Models\Payment::create([
                'user_id'                  => $user->id,
                'payment_type'             => 'certification',
                'amount'                   => 0,
                'is_free'                  => true,
                'payment_status'           => 'free',
                'related_certification_id' => $request->id,
            ]);

            // 無料フラグ更新
            $profile->update(['free_certification_used' => true]);

            return redirect()->route('applicant.dashboard')
                ->with('success', 'Pengajuan sertifikasi berhasil dikirim!');
        }

        // 有料の場合は支払いページへ（今後実装）
        return redirect()->route('applicant.dashboard')
            ->with('info', 'Silakan lakukan pembayaran untuk melanjutkan.');
    }
}