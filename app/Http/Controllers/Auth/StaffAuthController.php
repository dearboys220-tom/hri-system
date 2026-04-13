<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StaffAuthController extends Controller
{
    // -------------------------------------------------------
    // スタッフログインページを表示
    // -------------------------------------------------------
    public function create()
    {
        return Inertia::render('Auth/StaffLogin');
    }

    // -------------------------------------------------------
    // ログイン処理
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ]);
        }

        $user = Auth::user();

        // スタッフ・スーパー管理者以外はここからログインさせない
        // v2.8完全版: 新3部署ロールを追加
        $allowedRoles = [
            'investigator_user',
            'admin_user',
            'em_staff',
            'local_manager',
            'president',
            'super_admin',
            'strategy_user',   // ★ v2.8追加
            'ai_dev_user',     // ★ v2.8追加
            'marketing_user',  // ★ v2.8追加
        ];

        if (!in_array($user->role_type, $allowedRoles)) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses staff.',
            ]);
        }

        // アカウントが停止中の場合はログインさせない
        if (isset($user->status) && $user->status === 'suspended') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun Anda telah ditangguhkan. Hubungi administrator.',
            ]);
        }

        $request->session()->regenerate();

        // 最終ログイン情報を更新
        $user->updateLastLogin();

        // role_type によってリダイレクト先を変える
        return match($user->role_type) {
            'investigator_user' => redirect()->route('admin.investigator.index'),
            'admin_user'        => redirect()->route('admin.admin.index'),
            'em_staff'          => redirect()->route('em.dashboard'),
            'local_manager'     => redirect()->route('manager.dashboard'),
            'president'         => redirect()->route('president.dashboard'),
            'super_admin'       => redirect()->route('super-admin.dashboard'),
            'strategy_user'     => redirect()->route('strategy.dashboard'),   // ★ v2.8追加（画面未作成時は仮）
            'ai_dev_user'       => redirect()->route('ai-dev.dashboard'),     // ★ v2.8追加（画面未作成時は仮）
            'marketing_user'    => redirect()->route('marketing.dashboard'),  // ★ v2.8追加（画面未作成時は仮）
            default             => redirect('/'),
        };
    }

    // -------------------------------------------------------
    // ログアウト処理
    // -------------------------------------------------------
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('staff.login');
    }
}