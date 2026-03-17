<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApplicantProfile;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;

class GoogleAuthController extends Controller
{
    // Google認証ページへリダイレクト
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Google認証後のコールバック処理
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google認証に失敗しました。もう一度お試しください。',
            ]);
        }

        // すでに登録済みか確認（google_id または email で検索）
        $user = User::where('google_id', $googleUser->getId())
                    ->orWhere('email', $googleUser->getEmail())
                    ->first();

        if ($user) {
            // 既存ユーザー：google_id を更新してログイン
            $user->update([
                'google_id'    => $googleUser->getId(),
                'google_token' => $googleUser->token,
            ]);
        } else {
            // 新規ユーザー：users レコード作成
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'google_token'      => $googleUser->token,
                'role_type'         => 'applicant',
                'email_verified_at' => now(),
            ]);

            // applicant_profiles レコードを自動作成
            ApplicantProfile::create([
                'user_id'                        => $user->id,
                'member_id'                      => $this->generateMemberId(),
                'free_certification_used'        => false,
                'free_certification_expires_at'  => Carbon::now()->addDays(90),
                'certification_status'           => 'not_applied',
            ]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        // 未同意の場合は同意画面へ
        if (!$user->agreed_terms_at || !$user->agreed_investigation_at) {
            return redirect()->route('applicant.consent');
        }

        return redirect()->route('applicant.dashboard');
    }

    // 会員ID自動採番（HRI-00001 形式）
    private function generateMemberId(): string
    {
        $last = ApplicantProfile::orderByDesc('id')->first();
        $nextNumber = $last ? (intval(substr($last->member_id, 4)) + 1) : 1;
        return 'HRI-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
