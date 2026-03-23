<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\CompanyProfile;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class JobPaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    // Snapトークン発行
    public function createSnap(Request $request, int $jobId)
    {
        $user    = Auth::user();
        $profile = CompanyProfile::where('user_id', $user->id)->firstOrFail();

        $job = JobPost::where('id', $jobId)
            ->where('company_id', $user->id)
            ->firstOrFail();

        // 常に新しいorder_idを発行
        $orderId = 'JOB-' . $user->id . '-' . $jobId . '-' . time();

        // 古いpendingペイメントを失効
        Payment::where('related_job_post_id', $jobId)
            ->where('payment_status', 'pending')
            ->update(['payment_status' => 'failed']);

        // 新しいペイメントレコードを作成
        Payment::create([
            'user_id'             => $user->id,
            'payment_type'        => 'job_post',
            'amount'              => 250000,
            'is_free'             => false,
            'payment_status'      => 'pending',
            'midtrans_order_id'   => $orderId,
            'related_job_post_id' => $jobId,
        ]);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => 250000,
            ],
            'customer_details' => [
                'first_name' => $profile->company_name ?? $user->name,
                'email'      => $user->email,
                'phone'      => $profile->company_phone ?? '',
            ],
            'item_details' => [
                [
                    'id'       => 'JOB-POST',
                    'price'    => 250000,
                    'quantity' => 1,
                    'name'     => 'Posting Lowongan: ' . $job->title,
                ],
            ],
            'callbacks' => [
                'finish' => route('company.jobs.payment.finish') . '?order_id=' . $orderId,
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

    // Midtransからのコールバック
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
        }

        return response()->json(['message' => 'OK']);
    }

    // 支払い完了ページ
    public function finish(Request $request)
    {
        $orderId = $request->query('order_id');
        $payment = Payment::where('midtrans_order_id', $orderId)->first();

        if ($payment && $payment->payment_status === 'success') {
            return redirect()->route('company.jobs.index')
                ->with('success', 'Pembayaran berhasil! Lowongan sudah aktif.');
        }

        return redirect()->route('company.jobs.index')
            ->with('info', 'Pembayaran sedang diverifikasi. Lowongan akan aktif setelah pembayaran dikonfirmasi.');
    }

    private function markPaymentSuccess(Payment $payment)
    {
        $payment->update([
            'payment_status' => 'success',
            'payment_date'   => now(),
        ]);

        // 求人をactiveに更新
        if ($payment->related_job_post_id) {
            JobPost::where('id', $payment->related_job_post_id)
                ->update(['status' => 'active']);
        }
    }
}