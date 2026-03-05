<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->isCompany()) {
            return $next($request);
        }

        $company = $user->companyProfile;

        if (!$company) {
            Auth::logout();
            return redirect()->route('login');
        }

        return match ($company->company_verification_status) {

            'verified' => $next($request),

            'pending' => redirect()->route('company.pending'),

            'suspended' => tap(Auth::logout(), function () {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Akun perusahaan sedang ditangguhkan.']);
            }),

            'rejected' => tap(Auth::logout(), function () {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Akun perusahaan ditolak.']);
            }),

            default => redirect()->route('login'),
        };
    }
}