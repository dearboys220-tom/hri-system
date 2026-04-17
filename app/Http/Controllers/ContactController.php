<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Inquiry;
use Carbon\Carbon;

class ContactController extends Controller
{
    public function show()
    {
        return Inertia::render('Contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255',
            'phone'    => 'nullable|string|max:30',
            'category' => 'required|string|max:50',
            'subject'  => 'required|string|max:200',
            'message'  => 'required|string|max:5000',
        ]);

        // 問い合わせ番号採番（HRI-QRY-YYYYMMDD-NNNN）
        $today = Carbon::now('Asia/Jakarta')->format('Ymd');
        $count = Inquiry::whereDate('created_at', Carbon::today())->count() + 1;
        $inquiry_no = 'HRI-QRY-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        // SLA期限を計算（user_type=guest は個人会員と同じ2営業日）
        $sla_deadline = Inquiry::calcSlaDeadline('guest', $validated['category']);

        Inquiry::create([
            'inquiry_no'   => $inquiry_no,
            'user_id'      => auth()->id() ?? null,
            'user_type'    => 'guest',
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'] ?? null,
            'category'     => $validated['category'],
            'subject'      => $validated['subject'],
            'body'         => $validated['message'],
            'status'       => Inquiry::STATUS_RECEIVED,
            'sla_deadline' => $sla_deadline,
            'source'       => 'contact_form',
        ]);

        return redirect()->back()->with('inquiry_no', $inquiry_no);
    }
}