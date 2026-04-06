<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DataDeletionRequest;
use App\Models\StaffActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminUsersController extends Controller
{
    // -------------------------------------------------------
    // 全ユーザー管理
    // -------------------------------------------------------
    public function index(Request $request): Response
    {
        $query = User::with(['applicantProfile', 'companyProfile'])
            ->orderByDesc('created_at');

        if ($request->filled('role_type')) {
            $query->where('role_type', $request->role_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            });
        }

        $users = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/UsersAll', [
            'users'   => $users,
            'filters' => $request->only(['role_type', 'status', 'search']),
        ]);
    }

    // -------------------------------------------------------
    // アカウント停止
    // -------------------------------------------------------
    public function suspend(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // super_admin 自身は停止不可
        if ($user->role_type === 'super_admin') {
            return back()->with('error', 'スーパー管理者アカウントは停止できません。');
        }

        $user->update(['status' => 'suspended']);

        // 監査ログ
        AuditLog::recordHuman(
            AuditLog::ACTION_ACCOUNT_SUSPENDED,
            null,
            ['new' => ['target_user_id' => $user->id, 'suspended_by' => auth()->id()]]
        );

        StaffActivityLog::record(
            'account_suspended',
            'User',
            $user->id,
            [
                'description' => "アカウントを停止: {$user->email}",
                'before'      => ['status' => 'active'],
                'after'       => ['status' => 'suspended'],
            ]
        );

        return back()->with('success', "アカウントを停止しました: {$user->email}");
    }

    // -------------------------------------------------------
    // データ削除申請一覧
    // -------------------------------------------------------
    public function deletionRequests(Request $request): Response
    {
        $query = DataDeletionRequest::with('user')
            ->orderByDesc('requested_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/DeletionRequests', [
            'requests' => $requests,
            'filters'  => $request->only(['status']),
        ]);
    }

    // -------------------------------------------------------
    // データ削除申請を承認
    // -------------------------------------------------------
    public function approveDeletion(Request $request, int $id): RedirectResponse
    {
        $deletionRequest = DataDeletionRequest::findOrFail($id);

        $deletionRequest->update([
            'status'       => DataDeletionRequest::STATUS_APPROVED,
            'processed_by' => auth()->id(),
        ]);

        // 監査ログ
        AuditLog::recordHuman(
            AuditLog::ACTION_DELETE_REQUESTED,
            null,
            ['new' => ['deletion_request_id' => $id, 'status' => 'approved']]
        );

        return back()->with('success', '削除申請を承認しました。実際のデータ削除処理を実施してください。');
    }

    // -------------------------------------------------------
    // データ削除申請を却下
    // -------------------------------------------------------
    public function rejectDeletion(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        $deletionRequest = DataDeletionRequest::findOrFail($id);

        $deletionRequest->update([
            'status'        => DataDeletionRequest::STATUS_REJECTED,
            'reject_reason' => $request->reject_reason,
            'processed_by'  => auth()->id(),
        ]);

        return back()->with('success', '削除申請を却下しました。');
    }
}