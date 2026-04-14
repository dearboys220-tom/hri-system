<?php

namespace App\Http\Middleware;

use App\Models\StaffEducationRecord;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEducationCompleted
{
    /**
     * 対象スタッフロール
     */
    private const STAFF_ROLES = [
        'investigator_user',
        'admin_user',
        'em_staff',
        'local_manager',
        'strategy_user',
        'ai_dev_user',
        'marketing_user',
        'president',
    ];

    /**
     * @param string $modules  カンマ区切りのモジュールコード
     *                         例: 'company_rules'
     *                         例: 'pdp_basic,privacy_and_data_handling'
     */
    public function handle(Request $request, Closure $next, string $modules = 'company_rules'): mixed
    {
        $user = Auth::user();

        // 対象外ロールはスルー（super_admin / company / applicant 等）
        if (!$user || !in_array($user->role_type, self::STAFF_ROLES)) {
            return $next($request);
        }

        $requiredModules = explode(',', $modules);

        foreach ($requiredModules as $moduleCode) {
            $moduleCode = trim($moduleCode);
            if (!StaffEducationRecord::isCompleted($user->id, $moduleCode)) {
                // Ajax/API リクエストの場合は JSON で返す
                if ($request->expectsJson()) {
                    return response()->json([
                        'message'         => 'Modul pelatihan belum diselesaikan: ' . $moduleCode,
                        'redirect'        => route('staff.education.index'),
                        'incomplete_module' => $moduleCode,
                    ], 403);
                }

                return redirect()->route('staff.education.index')
                    ->with('warning', 'Selesaikan modul pelatihan "' . $this->moduleLabel($moduleCode) . '" terlebih dahulu untuk mengakses fitur ini.');
            }
        }

        return $next($request);
    }

    private function moduleLabel(string $code): string
    {
        return match ($code) {
            'company_rules'               => 'Peraturan Perusahaan',
            'pdp_basic'                   => 'Dasar Perlindungan Data Pribadi',
            'privacy_and_data_handling'   => 'Privasi & Penanganan Data',
            'prohibition_on_private_storage' => 'Larangan Penyimpanan Pribadi',
            'payment_and_approval_rules'  => 'Aturan Pembayaran & Persetujuan',
            default                       => $code,
        };
    }
}