<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\ApplicantProfile;
use App\Models\CertificationRequest;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class CertificationPaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    // Snapトークン発行
    public function createSnap(Request $request)
    {
        $user    = Auth::user();
        $profile = ApplicantProfile::where('user_id', $user->id)->firstOrFail();

        // 既存のpending_paymentレコードを再利用
        $certRequest = CertificationRequest::where('user_id', $user->id)
            ->where('survey_status', 'pending_payment')
            ->latest()->first();

        if (!$certRequest) {
            $certRequest = CertificationRequest::create([
                'user_id'               => $user->id,
                'survey_status'         => 'pending_payment',
                'admin_approved'        => false,
                'returned_to_applicant' => false,
                'ready_for_review'      => false,
            ]);
        }

        // 常に新しいorder_idを発行（Midtransは同じorder_idを再利用不可）
        $orderId = 'CERT-' . $user->id . '-' . time();

        // 古いpendingペイメントは失効扱いにする
        Payment::where('related_certification_id', $certRequest->id)
            ->where('payment_status', 'pending')
            ->update(['payment_status' => 'failed']);

        // 新しいペイメントレコードを作成
        $payment = Payment::create([
            'user_id'                  => $user->id,
            'payment_type'             => 'certification',
            'amount'                   => 35000,
            'is_free'                  => false,
            'payment_status'           => 'pending',
            'midtrans_order_id'        => $orderId,
            'related_certification_id' => $certRequest->id,
        ]);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => 35000,
            ],
            'customer_details' => [
                'first_name' => $profile->full_name ?? $user->name,
                'email'      => $user->email,
                'phone'      => $profile->phone_number ?? '',
            ],
            'item_details' => [
                [
                    'id'       => 'CERT-HRI',
                    'price'    => 35000,
                    'quantity' => 1,
                    'name'     => 'Sertifikasi HRI',
                ],
            ],
            'callbacks' => [
                'finish' => route('applicant.certification.payment.finish') . '?order_id=' . $orderId,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
            'client_key' => config('midtrans.client_key'),
            'snap_url'   => config('midtrans.snap_url'),
        ]);
    }

    // Midtransからのコールバック（サーバー通知）
    public function callback(Request $request)
    {
        $serverKey   = config('midtrans.server_key');
        $orderId     = $request->order_id;
        $statusCode  = $request->status_code;
        $grossAmount = $request->gross_amount;

        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($signature !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $payment = Payment::where('midtrans_order_id', $orderId)->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $transactionStatus = $request->transaction_status;
        $fraudStatus       = $request->fraud_status ?? 'accept';

        if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
            $this->markPaymentSuccess($payment);
        } elseif ($transactionStatus === 'settlement') {
            $this->markPaymentSuccess($payment);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $payment->update(['payment_status' => 'failed']);
            if ($payment->certificationRequest) {
                $payment->certificationRequest->update(['survey_status' => 'pending_payment']);
            }
        }

        return response()->json(['message' => 'OK']);
    }

    // 支払い完了ページ
    public function finish(Request $request)
    {
        $orderId = $request->query('order_id');
        $payment = Payment::where('midtrans_order_id', $orderId)->first();

        if ($payment && $payment->payment_status === 'success') {
            return redirect()->route('applicant.dashboard')
                ->with('success', 'Pembayaran berhasil! Pengajuan sertifikasi sedang diproses.');
        }

        return redirect()->route('applicant.dashboard')
            ->with('info', 'Pembayaran sedang diverifikasi. Mohon tunggu.');
    }

    private function markPaymentSuccess(Payment $payment)
    {
        $payment->update([
            'payment_status' => 'success',
            'payment_date'   => now(),
        ]);

        if ($payment->related_certification_id) {
            CertificationRequest::where('id', $payment->related_certification_id)
                ->update(['survey_status' => 'under_investigation']);

            $certRequest = CertificationRequest::find($payment->related_certification_id);
            if ($certRequest) {
                $profile = ApplicantProfile::where('user_id', $certRequest->user_id)->first();
                if ($profile) {
                    $profile->update(['free_certification_used' => true]);
                }
            }
        }
    }
}