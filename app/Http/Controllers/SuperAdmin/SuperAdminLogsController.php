<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiActivityLog;
use App\Models\AiChatLog;
use App\Models\AiDataTransferLog;
use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\PersonalDataAccessLog;
use App\Models\StaffActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminLogsController extends Controller
{
    // -------------------------------------------------------
    // AI活動ログ
    // -------------------------------------------------------
    public function aiLogs(Request $request): Response
    {
        $query = AiActivityLog::with('triggeredBy')
            ->orderByDesc('created_at');

        // フィルタ
        if ($request->filled('log_type')) {
            $query->where('log_type', $request->log_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/AiLogs', [
            'logs'    => $logs,
            'filters' => $request->only(['log_type', 'status', 'date_from', 'date_to']),
        ]);
    }

    // -------------------------------------------------------
    // AIチャットログ
    // -------------------------------------------------------
    public function aiChatLogs(Request $request): Response
    {
        $query = AiChatLog::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('role_type')) {
            $query->where('role_type', $request->role_type);
        }
        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/AiChatLogs', [
            'logs'    => $logs,
            'filters' => $request->only(['role_type', 'session_id', 'date_from', 'date_to']),
        ]);
    }

    // -------------------------------------------------------
    // スタッフ操作ログ
    // -------------------------------------------------------
    public function staffLogs(Request $request): Response
    {
        $query = StaffActivityLog::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('role_type')) {
            $query->where('role_type', $request->role_type);
        }
        if ($request->filled('action')) {
            $query->where('action', 'LIKE', "%{$request->action}%");
        }
        if ($request->filled('case_no')) {
            $query->where('case_no', $request->case_no);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/StaffLogs', [
            'logs'    => $logs,
            'filters' => $request->only(['role_type', 'action', 'case_no', 'date_from', 'date_to']),
        ]);
    }

    // -------------------------------------------------------
    // 個人データアクセスログ
    // -------------------------------------------------------
    public function dataAccessLogs(Request $request): Response
    {
        $query = PersonalDataAccessLog::with(['accessor', 'targetUser'])
            ->orderByDesc('accessed_at');

        if ($request->filled('accessor_role')) {
            $query->where('accessor_role', $request->accessor_role);
        }
        if ($request->filled('data_type')) {
            $query->where('data_type', $request->data_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('accessed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('accessed_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/DataAccessLogs', [
            'logs'    => $logs,
            'filters' => $request->only(['accessor_role', 'data_type', 'date_from', 'date_to']),
        ]);
    }

    // -------------------------------------------------------
    // AIデータ送信ログ（越境移転）
    // -------------------------------------------------------
    public function aiTransferLogs(Request $request): Response
    {
        $query = AiDataTransferLog::with('certificationRequest')
            ->orderByDesc('transferred_at');

        if ($request->filled('transfer_purpose')) {
            $query->where('transfer_purpose', $request->transfer_purpose);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transferred_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transferred_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/AiTransferLogs', [
            'logs'    => $logs,
            'filters' => $request->only(['transfer_purpose', 'date_from', 'date_to']),
        ]);
    }

    // -------------------------------------------------------
    // 監査ログ（audit_logs）
    // -------------------------------------------------------
    public function auditLogs(Request $request): Response
    {
        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        if ($request->filled('actor_type')) {
            $query->where('actor_type', $request->actor_type);
        }
        if ($request->filled('case_no')) {
            $query->where('case_no', $request->case_no);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/AuditLogs', [
            'logs'       => $logs,
            'filters'    => $request->only(['action_type', 'actor_type', 'case_no', 'date_from', 'date_to']),
            'actionTypes' => AuditLog::getActionTypes(),
        ]);
    }

    // -------------------------------------------------------
    // 同意記録
    // -------------------------------------------------------
    public function consentRecords(Request $request): Response
    {
        $query = ConsentRecord::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('consent_type')) {
            $query->where('consent_type', $request->consent_type);
        }
        if ($request->boolean('active_only')) {
            $query->active();
        }
        if ($request->filled('date_from')) {
            $query->whereDate('consented_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('consented_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/ConsentRecords', [
            'logs'    => $logs,
            'filters' => $request->only(['consent_type', 'active_only', 'date_from', 'date_to']),
        ]);
    }
}