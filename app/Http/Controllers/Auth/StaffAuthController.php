<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StaffAuthController extends Controller
{
    // スタッフログインページを表示
    public function create()
    {
        return Inertia::render('Auth/StaffLogin');
    }

    // ログイン処理
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
        $allowedRoles = [
            'admin_user',
            'investigator_user',
            'reviewer_user',
            'super_admin',  // ★ v2.5追加
        ];

        if (!in_array($user->role_type, $allowedRoles)) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses staff.',
            ]);
        }

        $request->session()->regenerate();

        // role_type によってリダイレクト先を変える
        return match($user->role_type) {
            'admin_user'        => redirect()->route('admin.admin.index'),
            'investigator_user' => redirect()->route('admin.investigator.index'),
            'reviewer_user'     => redirect()->route('admin.investigator.index'),
            'super_admin'       => redirect()->route('super-admin.dashboard'),  // ★ v2.5追加
        };
    }

    // ログアウト処理
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('staff.login');
    }
}