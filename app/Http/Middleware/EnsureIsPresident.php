<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsPresident extends \Illuminate\Foundation\Http\Middleware\Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role_type, ['president', 'super_admin'])) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}