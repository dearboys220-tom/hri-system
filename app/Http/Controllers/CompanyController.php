<?php
namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\JobPost;
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
use Carbon\Carbon;

class CompanyController extends Controller
{
    // 企業会員登録フォーム表示
    public function create(): Response
    {
        return Inertia::render('Auth/RegisterCompany');
    }

    // 会員ID生成
    private function generateMemberId(): string
    {
        do {
            $code = 'HRIC-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7));
        } while (CompanyProfile::where('member_id', $code)->exists());
        return $code;
    }

    // 企業会員登録処理
    public function store(Request $request): RedirectResponse
    {
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

        $aktaPath = null;
        if ($request->hasFile('power_letter')) {
            $aktaPath = $request->file('power_letter')
                ->store('company_docs', 'public');
        }

        $user = User::create([
            'name'      => $request->company_name,
            'email'     => $request->company_email,
            'password'  => Hash::make($request->password),
            'role_type' => 'company',
        ]);

        CompanyProfile::create([
            'user_id'                       => $user->id,
            'member_id'                     => $this->generateMemberId(),
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
        return redirect()->route('company.dashboard');
    }

    // 企業会員ダッシュボード
    public function dashboard(): Response
    {
        $user = Auth::user();
        $profile = CompanyProfile::where('user_id', $user->id)->first();

        // 無料投稿期間の残り日数
        $freePostDaysLeft = 0;
        $isFreePostAvailable = false;
        if ($profile && $profile->free_job_post_expires_at && !$profile->free_job_post_used) {
            $expires = Carbon::parse($profile->free_job_post_expires_at);
            if ($expires->isFuture()) {
                $freePostDaysLeft = (int) now()->diffInDays($expires);
                $isFreePostAvailable = true;
            }
        }

        // 求人統計（JobPostモデルがある場合）
        $jobStats = [
            'total'  => 0,
            'open'   => 0,
            'closed' => 0,
        ];

        // applicationsはJobPost実装後に追加予定
        $totalApplications = 0;

        return Inertia::render('Company/Dashboard', [
            'profile'              => $profile,
            'freePostDaysLeft'     => $freePostDaysLeft,
            'isFreePostAvailable'  => $isFreePostAvailable,
            'jobStats'             => $jobStats,
            'totalApplications'    => $totalApplications,
        ]);
    }

    // 企業プロフィール表示
    public function showProfile(): Response
    {
        $user = Auth::user();
        $profile = CompanyProfile::where('user_id', $user->id)->first();

        return Inertia::render('Company/Profile', [
            'profile' => $profile,
        ]);
    }

    // 企業プロフィール更新
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $profile = CompanyProfile::where('user_id', $user->id)->first();

        $request->validate([
            'company_name'        => 'required|string|max:255',
            'pic_name'            => 'required|string|max:255',
            'pic_position'        => 'nullable|string|max:255',
            'pic_phone'           => 'required|string|max:20',
            'company_address'     => 'nullable|string|max:1000',
            'company_phone'       => 'nullable|string|max:20',
            'company_website'     => 'nullable|url|max:255',
            'company_description' => 'nullable|string|max:2000',
            'industry_type'       => 'nullable|string|max:255',
            'company_size'        => 'nullable|string|max:50',
            'company_logo'        => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // ロゴ画像の保存
        $logoPath = $profile->company_logo;
        if ($request->hasFile('company_logo')) {
            // 古いロゴを削除
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('company_logo')->store('company_logos', 'public');
        }

        $profile->update([
            'company_name'        => $request->company_name,
            'pic_name'            => $request->pic_name,
            'pic_position'        => $request->pic_position,
            'pic_phone'           => $request->pic_phone,
            'company_address'     => $request->company_address,
            'company_phone'       => $request->company_phone,
            'company_website'     => $request->company_website,
            'company_description' => $request->company_description,
            'industry_type'       => $request->industry_type,
            'company_size'        => $request->company_size,
            'company_logo'        => $logoPath,
        ]);

        // usersテーブルのnameも更新
        $user->update(['name' => $request->company_name]);

        return redirect()->route('company.profile')->with('success', 'Profil perusahaan berhasil diperbarui!');
    }
}