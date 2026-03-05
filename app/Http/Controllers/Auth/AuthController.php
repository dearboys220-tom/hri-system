<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function loginStaff()
    {
        return Inertia::render('Auth/LoginAdmin');
    }

    public function store(Request $request)
    {
        $user = $this->attemptLogin(
            $request,
            'email',
            'Email atau password salah.',
            'web'
        );

        $this->ensureRoleAllowed($user, [
            RoleType::COMPANY,
            RoleType::APPLICANT,
        ], 'Gunakan login staff.');

        return redirect()->route($this->redirectRoute($user));
    }

    public function storeStaff(Request $request)
    {
        $user = $this->attemptLogin(
            $request,
            'name',
            'Username atau password salah.',
            'staff'
        );

        $this->ensureRoleAllowed($user, [
            RoleType::ADMIN,
            RoleType::INVESTIGATOR,
            RoleType::REVIEWER,
        ], 'Gunakan login user.');

        return redirect()->route($this->redirectRoute($user));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function logoutStaff(Request $request)
    {
        Auth::guard('staff')->logout();
        // dd(Auth::guard());

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login-staff');
    }

    /**
     * Handle login attempt
     */
    private function attemptLogin(Request $request, string $field, string $message, string $guard = 'web')
    {
        $rules = [
            'password' => ['required'],
            $field => ['required'],
        ];

        if ($field === 'email') {
            $rules[$field][] = 'email';
        }

        $request->validate($rules);

        $credentials = [
            $field => $request->input($field),
            'password' => $request->password
        ];

        if (!Auth::guard($guard)->attempt($credentials)) {
            throw ValidationException::withMessages([
                $field => $message,
            ]);
        }

        $request->session()->regenerate();

        return Auth::guard($guard)->user();
    }

    /**
     * Validate allowed role
     */
    private function ensureRoleAllowed($user, array $allowedRoles, string $message)
    {
        if (!in_array($user->role_type, $allowedRoles)) {

            Auth::logout();

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }
    }

    /**
     * Redirect based on role
     */
    private function redirectRoute($user): string
    {
        return match ($user->role_type) {

            RoleType::ADMIN => 'admin.dashboard',
            RoleType::INVESTIGATOR => 'investigator.dashboard',
            RoleType::REVIEWER => 'reviewer.dashboard',

            RoleType::COMPANY => $this->handleCompany($user),

            RoleType::APPLICANT => 'applicant.dashboard',

            default => $this->logoutWithError(),
        };
    }

    private function handleCompany($user): string
    {
        $company = $user->companyProfile;

        if (!$company) {
            return $this->logoutWithError('Profil perusahaan tidak ditemukan.');
        }

        return match ($company->company_verification_status) {

            'verified' => 'company.dashboard',

            'pending' => 'company.pending',

            'suspended' => $this->logoutWithError(
                'Akun perusahaan sedang ditangguhkan.'
            ),

            'rejected' => $this->logoutWithError(
                'Akun perusahaan ditolak.'
            ),

            default => $this->logoutWithError(
                'Status perusahaan tidak valid.'
            ),
        };
    }

    private function logoutWithError(string $message = 'Role tidak dikenali.')
    {
        Auth::logout();

        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }
}