<?php

namespace App\Http\Controllers;

use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\CompanyProfile;
use App\Models\EducationHistory;
use App\Models\WorkHistory;
use App\Models\Certification;
use App\Models\Payment;
use App\Models\ReviewItem;
use App\Models\InvestigationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class ScoreDetailController extends Controller
{
    // ====================================================
    // スコア詳細ページへのエントリポイント
    // 購入済みなら view へ、未購入なら payment へ
    // ====================================================
    public function show(string $memberId)
    {
        $user    = Auth::user();
        $company = CompanyProfile::where('user_id', $user->id)->firstOrFail();

        $applicant = ApplicantProfile::where('member_id', $memberId)->firstOrFail();

        if ($this->isPurchased($company, $memberId)) {
            return redirect()->route('company.score.view', $memberId);
        }

        return Inertia::render('Company/ScorePayment', [
            'memberId'      => $memberId,
            'applicantName' => $applicant->full_name,
            'amount'        => 50000,
        ]);
    }

    // ====================================================
    // Midtrans Snap トークン生成
    // ====================================================
    public function createSnap(Request $request)
    {
        $request->validate([
            'member_id' => 'required|string',
        ]);

        $user      = Auth::user();
        $company   = CompanyProfile::where('user_id', $user->id)->firstOrFail();
        $memberId  = $request->member_id;
        $applicant = ApplicantProfile::where('member_id', $memberId)->firstOrFail();

        if ($this->isPurchased($company, $memberId)) {
            return response()->json(['error' => 'Sudah dibeli sebelumnya.'], 422);
        }

        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        $orderId = 'SCORE-' . $user->id . '-' . $memberId . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => 50000,
            ],
            'item_details' => [[
                'id'       => 'SCORE_DETAIL',
                'price'    => 50000,
                'quantity' => 1,
                'name'     => 'Detail Skor HRI - ' . $memberId,
            ]],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            Payment::create([
                'user_id'             => $user->id,
                'payment_type'        => 'score_detail',
                'amount'              => 50000,
                'is_free'             => false,
                'payment_status'      => 'pending',
                'midtrans_order_id'   => $orderId,
                'midtrans_snap_token' => $snapToken,
                'target_member_id'    => $memberId,
            ]);

            return response()->json(['snap_token' => $snapToken, 'order_id' => $orderId]);

        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat transaksi.'], 500);
        }
    }

    // ====================================================
    // Midtrans Webhook
    // ====================================================
    public function callback(Request $request)
    {
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');

        $notification = new \Midtrans\Notification();
        $orderId      = $notification->order_id;
        $status       = $notification->transaction_status;
        $fraudStatus  = $notification->fraud_status ?? null;

        $payment = Payment::where('midtrans_order_id', $orderId)->first();
        if (!$payment) return response('not found', 404);

        $isSuccess = in_array($status, ['capture', 'settlement']) &&
                     (!$fraudStatus || $fraudStatus === 'accept');

        if ($isSuccess) {
            $payment->update([
                'payment_status'          => 'success',
                'payment_date'            => now(),
                'midtrans_transaction_id' => $notification->transaction_id ?? null,
                'payment_method'          => $notification->payment_type ?? null,
            ]);
            $this->recordPurchase($payment->user_id, $payment->target_member_id, $payment->id);
        } elseif (in_array($status, ['deny', 'cancel', 'expire'])) {
            $payment->update(['payment_status' => 'failed']);
        }

        return response('ok', 200);
    }

    // ====================================================
    // 決済完了後リダイレクト
    // ====================================================
    public function finish(Request $request)
    {
        $orderId = $request->query('order_id');

        if (!$orderId) {
            return redirect()->route('company.dashboard');
        }

        $payment = Payment::where('midtrans_order_id', $orderId)->first();

        if (!$payment) {
            return redirect()->route('company.dashboard');
        }

        if ($payment->payment_status === 'pending') {
            try {
                \Midtrans\Config::$serverKey    = config('midtrans.server_key');
                \Midtrans\Config::$isProduction = config('midtrans.is_production');
                $status = \Midtrans\Transaction::status($orderId);
                if (in_array($status->transaction_status, ['capture', 'settlement'])) {
                    $payment->update([
                        'payment_status'          => 'success',
                        'payment_date'            => now(),
                        'midtrans_transaction_id' => $status->transaction_id ?? null,
                        'payment_method'          => $status->payment_type ?? null,
                    ]);
                    $this->recordPurchase($payment->user_id, $payment->target_member_id, $payment->id);
                }
            } catch (\Exception $e) {
                Log::error('Midtrans status check error: ' . $e->getMessage());
            }
        }

        if ($payment->payment_status === 'success') {
            return redirect()->route('company.score.view', $payment->target_member_id)
                ->with('success', 'Pembayaran berhasil! Detail skor sudah dapat dilihat.');
        }

        return redirect()->route('company.score.payment', $payment->target_member_id)
            ->with('error', 'Pembayaran belum berhasil. Silakan coba lagi.');
    }

    // ====================================================
    // 一般履歴書表示（無料・企業向け）
    // ====================================================
    public function resume(string $memberId)
    {
        $applicant = ApplicantProfile::where('member_id', $memberId)
            ->with('user:id,name')
            ->first();

        if (!$applicant) {
            return redirect()->route('company.dashboard')
                ->with('error', 'Anggota dengan ID tersebut tidak ditemukan.');
        }

        // ===== user_id で学歴・職歴・資格を取得 =====
        $educations     = EducationHistory::where('user_id', $applicant->user_id)->get();
        $works          = WorkHistory::where('user_id', $applicant->user_id)->get();
        $certifications = Certification::where('user_id', $applicant->user_id)->get();
        // ============================================

        $user        = Auth::user();
        $company     = CompanyProfile::where('user_id', $user->id)->first();
        $isPurchased = $company ? $this->isPurchased($company, $memberId) : false;

        return Inertia::render('Company/ApplicantResume', [
            'applicant' => [
                'member_id'       => $applicant->member_id,
                'full_name'       => $applicant->full_name,
                'gender'          => $applicant->gender,
                'birth_date'      => $applicant->birth_date
                    ? Carbon::parse($applicant->birth_date)->format('d/m/Y')
                    : null,
                'nationality'     => $applicant->nationality,
                'phone_number'    => $applicant->phone_number,
                'current_address' => $applicant->current_address,
                'self_pr'         => $applicant->self_pr,
                'profile_photo'   => $applicant->profile_photo,
                'hri_score'       => $applicant->hri_score,
                'cert_status'     => $applicant->certification_status,
                'cert_expiry'     => $applicant->certification_expiry_date
                    ? Carbon::parse($applicant->certification_expiry_date)->format('d/m/Y')
                    : null,
            ],
            'education' => $educations->map(fn($e) => [
                'id'                    => $e->id,
                'school_name'           => $e->school_name,
                'education_level'       => $e->education_level,
                'school_location'       => $e->school_location,
                'degree_name'           => $e->degree_name,
                'enrollment_date'       => $e->enrollment_date
                    ? Carbon::parse($e->enrollment_date)->format('d/m/Y')
                    : null,
                'graduation_date'       => $e->graduation_date
                    ? Carbon::parse($e->graduation_date)->format('d/m/Y')
                    : null,
                'graduation_status'     => $e->graduation_status,
                'ipk_gpa'               => $e->ipk_gpa,
                'academic_achievements' => $e->academic_achievements,
            ]),
            'work' => $works->map(fn($w) => [
                'id'                     => $w->id,
                'company_name'           => $w->company_name,
                'company_address'        => $w->company_address,
                'department_position'    => $w->department_position,
                'employment_type'        => $w->employment_type,
                'employment_start_date'  => $w->employment_start_date
                    ? Carbon::parse($w->employment_start_date)->format('d/m/Y')
                    : null,
                'employment_end_date'    => $w->employment_end_date
                    ? Carbon::parse($w->employment_end_date)->format('d/m/Y')
                    : null,
                'job_description'        => $w->job_description,
                'employment_achievements'=> $w->employment_achievements,
            ]),
            'certifications' => $certifications->map(fn($c) => [
                'id'                   => $c->id,
                'certificate_name'     => $c->certificate_name,
                'issuing_organization' => $c->issuing_organization,
                'issue_date'           => $c->issue_date
                    ? Carbon::parse($c->issue_date)->format('d/m/Y')
                    : null,
                'expiration_date'      => $c->expiration_date
                    ? Carbon::parse($c->expiration_date)->format('d/m/Y')
                    : null,
                'certificate_score'    => $c->certificate_score,
            ]),
            'isPurchased' => $isPurchased,
        ]);
    }

    // ====================================================
    // スコア詳細表示（購入済み確認 → データ取得）
    // ====================================================
    public function view(string $memberId)
    {
        $user    = Auth::user();
        $company = CompanyProfile::where('user_id', $user->id)->firstOrFail();

        if (!$this->isPurchased($company, $memberId)) {
            return redirect()->route('company.score.payment', $memberId)
                ->with('error', 'Silakan lakukan pembayaran terlebih dahulu.');
        }

        $applicant = ApplicantProfile::where('member_id', $memberId)
            ->with('user:id,name')
            ->firstOrFail();

        $certRequest = CertificationRequest::where('user_id', $applicant->user_id)
            ->whereIn('survey_status', ['Terverifikasi', 'pending_admin', 'under_review'])
            ->latest()
            ->first();

        $reviewItems        = [];
        $investigationItems = [];

        if ($certRequest) {
            $reviewItems = ReviewItem::where('certification_request_id', $certRequest->id)
                ->get(['category', 'item_name', 'max_deduction', 'actual_deduction', 'weight', 'notes']);

            $investigationItems = InvestigationItem::where('certification_request_id', $certRequest->id)
                ->get(['category', 'item_name', 'validity', 'notes_id', 'notes']);
        }

        $purchaseRecord = collect(json_decode($company->purchased_score_details ?? '[]', true))
            ->firstWhere('member_id', $memberId);

        return Inertia::render('Company/ScoreDetailView', [
            'memberId'           => $memberId,
            'applicant'          => [
                'member_id'   => $applicant->member_id,
                'full_name'   => $applicant->full_name,
                'hri_score'   => $applicant->hri_score,
                'cert_status' => $applicant->certification_status,
                'cert_expiry' => $applicant->certification_expiry_date
                    ? Carbon::parse($applicant->certification_expiry_date)->format('d/m/Y')
                    : null,
            ],
            'reviewItems'        => $reviewItems,
            'investigationItems' => $investigationItems,
            'purchasedAt'        => $purchaseRecord['purchased_at'] ?? null,
        ]);
    }

    // ====================================================
    // private: 購入済みチェック
    // ====================================================
    private function isPurchased(CompanyProfile $company, string $memberId): bool
    {
        $purchased = json_decode($company->purchased_score_details ?? '[]', true);
        return collect($purchased)->contains('member_id', $memberId);
    }

    // ====================================================
    // private: 購入記録を company_profiles に保存
    // ====================================================
    private function recordPurchase(int $userId, string $memberId, int $paymentId): void
    {
        $company = CompanyProfile::where('user_id', $userId)->first();
        if (!$company) return;

        $purchased   = json_decode($company->purchased_score_details ?? '[]', true);
        $purchased[] = [
            'member_id'    => $memberId,
            'purchased_at' => now()->toDateTimeString(),
            'payment_id'   => $paymentId,
        ];
        $company->update(['purchased_score_details' => json_encode($purchased)]);
    }
}