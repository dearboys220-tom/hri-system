<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\RoleType;

class EnsureUserHasCorrectRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {

            if ($request->is('investigator/*') || 
                $request->is('reviewer/*') || 
                $request->is('admin/*')) {
                return redirect()->route('login-staff');
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role_type !== $role) {
            return redirect()->route($this->redirectToDashboard($user));
        }

        return $next($request);
    }

    private function redirectToDashboard($user): string
    {
        return match ($user->role_type) {
            RoleType::ADMIN => 'admin.dashboard',
            RoleType::INVESTIGATOR => 'investigator.dashboard',
            RoleType::REVIEWER => 'reviewer.dashboard',
            RoleType::COMPANY => 'company.dashboard',
            RoleType::APPLICANT => 'applicant.dashboard',
            default => 'login',
        };
    }
}