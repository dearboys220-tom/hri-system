<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleType;
use App\Models\ApplicantProfile;
use App\Services\MemberIdService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;

class ApplicantAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(MemberIdService $memberIdService)
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        DB::beginTransaction();

        try {

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {

                $user = User::create([
                    'name'        => $googleUser->getName(),
                    'email'       => $googleUser->getEmail(),
                    'google_id'   => $googleUser->getId(),
                    'google_token'=> $googleUser->token,
                    'password'    => bcrypt(Str::random(16)),
                    'role_type'   => RoleType::APPLICANT,
                    'email_verified_at' => now(),
                ]);

                // Sync role to Spatie
                $user->syncRoleWithSpatie();

                ApplicantProfile::create([
                    'user_id' => $user->id,
                    'member_id' => $memberIdService->generate(),
                    'full_name' => $googleUser->getName(),
                    'certification_status' => 'Belum Apply',
                    'free_certification_used' => false,
                    'free_certification_expires_at' => now()->addDays(90),
                ]);

            } else {

                $user->update([
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                ]);

                if (!$user->role_type) {
                    $user->update(['role_type' => RoleType::APPLICANT]);
                }

                $user->syncRoleWithSpatie();

                if (!$user->applicantProfile) {
                    ApplicantProfile::create([
                        'user_id' => $user->id,
                        'member_id' => $memberIdService->generate(),
                        'full_name' => $user->name,
                        'certification_status' => 'Belum Apply',
                        'free_certification_used' => false,
                        'free_certification_expires_at' => now()->addDays(90),
                    ]);
                }
            }

            DB::commit();

            Auth::login($user);

            return redirect()->route('applicant.dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login'); 
    }
}
