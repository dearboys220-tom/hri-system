<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * スーパー管理者専用ミドルウェア。
     *
     * チェック内容:
     *   ① 認証済みか
     *   ② role_type = 'super_admin' か
     *   ③ アクセス自体を audit_logs に記録する
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ① 未認証の場合はログイン画面へ
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // ② super_admin 以外はトップページへ
        if ($user->role_type !== 'super_admin') {
            abort(403, 'このページへのアクセス権限がありません。');
        }

        // ③ アクセスを audit_logs に記録
        AuditLog::recordHuman(
            AuditLog::ACTION_SUPER_ADMIN_ACCESS,
            null,
            ['new' => ['page' => $request->getRequestUri()]]
        );

        return $next($request);
    }
}