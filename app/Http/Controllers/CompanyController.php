<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * 企業会員登録フォームを表示
     */
    public function create(): Response
    {
        return Inertia::render('Auth/RegisterCompany');
    }

    /**
     * 企業会員登録処理
     */
    public function store(Request $request): RedirectResponse
    {
        // バリデーション
        $request->validate([
            'company_name'  => 'required|string|max:255',
            'nib'           => 'required|string|max:255',
            'person_name'   => 'required|string|max:255',
            'position'      => 'nullable|string|max:255',
            'person_phone'  => 'required|string|max:20',
            'company_email' => 'required|string|email|max:255|unique:users,email',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'power_letter'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // ファイルアップロード処理
        $aktaPath = null;
        if ($request->hasFile('power_letter')) {
            $aktaPath = $request->file('power_letter')
                ->store('company_docs', 'public');
        }

        // usersテーブルにレコード作成
        $user = User::create([
            'name'      => $request->company_name,
            'email'     => $request->company_email,
            'password'  => Hash::make($request->password),
            'role_type' => 'company',
        ]);

        // company_profilesテーブルにレコード作成
        CompanyProfile::create([
            'user_id'                       => $user->id,
            'company_name'                  => $request->company_name,
            'nib'                           => $request->nib,
            'pic_name'                      => $request->person_name,
            'pic_position'                  => $request->position,
            'pic_phone'                     => $request->person_phone,
            'company_email'                 => $request->company_email,
            'akta_pendirian'                => $aktaPath,
            'company_verification_status'   => 'pending',
            'free_job_post_used'            => false,
            'free_job_post_expires_at'      => now()->addDays(30),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect('/login');
    }
}