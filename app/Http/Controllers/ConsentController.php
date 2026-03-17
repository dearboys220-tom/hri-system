<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

class ConsentController extends Controller
{
    // 同意画面を表示
    public function show()
    {
        $user = Auth::user();

        // すでに同意済みならダッシュボードへ
        if ($user->agreed_terms_at && $user->agreed_investigation_at) {
            return redirect()->route('applicant.dashboard');
        }

        return Inertia::render('Applicant/Consent');
    }

    // 同意を保存
    public function store(Request $request)
    {
        $request->validate([
            'agreed_terms'       => 'accepted',
            'agreed_investigation' => 'accepted',
        ]);

        $user = Auth::user();
        $user->agreed_terms_at       = Carbon::now();
        $user->agreed_investigation_at = Carbon::now();
        $user->terms_version         = '2025-01';
        $user->save();

        return redirect()->route('applicant.dashboard');
    }
}