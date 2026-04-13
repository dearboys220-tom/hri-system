<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Mail\StaffWelcomeMail;
use App\Models\StaffAvailability;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StaffManagementController extends Controller
{
    // 登録可能なロール（local_manager が登録できるロール）
    private array $allowedRoles = [
        'investigator_user',
        'admin_user',
        'em_staff',
        'strategy_user',
        'ai_dev_user',
        'marketing_user',
    ];

    // 部署コードとロールのマッピング
    private array $roleDeptMap = [
        'investigator_user' => 'INVESTIGATION',
        'admin_user'        => 'ADMIN',
        'em_staff'          => 'ADMIN',
        'strategy_user'     => 'STRATEGY',
        'ai_dev_user'       => 'AI_DEV',
        'marketing_user'    => 'MARKETING',
    ];

    // メール送信用ロール表示名
    private array $roleLabels = [
        'investigator_user' => 'Divisi Investigasi',
        'admin_user'        => 'Divisi Admin & Verifikasi',
        'em_staff'          => 'Staf Umum',
        'strategy_user'     => 'Divisi Strategy Management',
        'ai_dev_user'       => 'Divisi AI Development',
        'marketing_user'    => 'Divisi Marketing',
    ];

    // ----------------------------------------------------------------
    // スタッフ一覧
    // ----------------------------------------------------------------
    public function index()
    {
        $staffList = User::with(['staffProfile', 'staffAvailability'])
            ->whereIn('role_type', $this->allowedRoles)
            ->where('status', '!=', 'deleted')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($u) => $this->formatStaff($u));

        return Inertia::render('Manager/StaffManagement', [
            'staffList'    => $staffList,
            'allowedRoles' => $this->allowedRoles,
        ]);
    }

    // ----------------------------------------------------------------
    // スタッフ登録
    // ----------------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'role_type'      => 'required|in:' . implode(',', $this->allowedRoles),
            'full_name'      => 'required|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'whatsapp'       => 'nullable|string|max:30',
            'position_title' => 'nullable|string|max:100',
            'join_date'      => 'nullable|date',
            'contract_type'  => 'nullable|in:PERMANENT,CONTRACT,FREELANCE',
            'base_salary'    => 'nullable|numeric|min:0',
        ]);

        // 仮パスワード生成
        $tempPassword = Str::random(12);

        // User を作成
        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($tempPassword),
            'role_type'         => $request->role_type,
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        // 部署コードを自動設定
        $deptCode = $this->roleDeptMap[$request->role_type] ?? 'ADMIN';

        // StaffProfile を作成
        StaffProfile::create([
            'user_id'         => $user->id,
            'full_name'       => $request->full_name,
            'role_type'       => $request->role_type,
            'department_code' => $deptCode,
            'position_title'  => $request->position_title,
            'phone'           => $request->phone,
            'whatsapp'        => $request->whatsapp,
            'join_date'       => $request->join_date,
            'contract_type'   => $request->contract_type ?? 'PERMANENT',
            'base_salary'     => $request->base_salary,
            'is_active'       => true,
        ]);

        // StaffAvailability を初期化
        StaffAvailability::create([
            'staff_user_id'        => $user->id,
            'availability_status'  => 'AVAILABLE',
            'active_task_count'    => 0,
            'max_concurrent_tasks' => 3,
            'department_code'      => $deptCode,
        ]);

        // メール送信（失敗しても登録は成功扱いにする）
        $mailSent = false;
        try {
            Mail::to($user->email)->send(new StaffWelcomeMail(
                fullName:     $request->full_name,
                email:        $user->email,
                tempPassword: $tempPassword,
                roleLabel:    $this->roleLabels[$request->role_type] ?? $request->role_type,
            ));
            $mailSent = true;
        } catch (\Exception $e) {
            $mailSent = false;
        }

        $mailMsg = $mailSent
            ? "Email konfirmasi telah dikirim ke {$user->email}."
            : "（メール送信失敗 — 仮パスワードを直接お伝えください）";

        return back()->with([
            'success'       => "Staf {$request->full_name} berhasil didaftarkan. {$mailMsg}",
            'temp_password' => $tempPassword,
        ]);
    }

    // ----------------------------------------------------------------
    // スタッフ詳細取得（API）
    // ----------------------------------------------------------------
    public function show(int $userId)
    {
        $user = User::with(['staffProfile', 'staffAvailability'])
            ->whereIn('role_type', $this->allowedRoles)
            ->findOrFail($userId);

        return response()->json($this->formatStaff($user));
    }

    // ----------------------------------------------------------------
    // スタッフ情報更新
    // ----------------------------------------------------------------
    public function update(Request $request, int $userId)
    {
        $request->validate([
            'full_name'      => 'required|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'whatsapp'       => 'nullable|string|max:30',
            'position_title' => 'nullable|string|max:100',
            'contract_type'  => 'nullable|in:PERMANENT,CONTRACT,FREELANCE',
            'base_salary'    => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
        ]);

        $user = User::whereIn('role_type', $this->allowedRoles)->findOrFail($userId);

        // StaffProfile を更新
        $user->staffProfile?->update([
            'full_name'      => $request->full_name,
            'phone'          => $request->phone,
            'whatsapp'       => $request->whatsapp,
            'position_title' => $request->position_title,
            'contract_type'  => $request->contract_type,
            'base_salary'    => $request->base_salary,
            'is_active'      => $request->is_active ?? true,
        ]);

        // アクティブ状態に応じて users.status と availability_status を同期
        if (!($request->is_active ?? true)) {
            $user->update(['status' => 'suspended']);
            $user->staffAvailability?->update(['availability_status' => 'SUSPENDED']);
        } else {
            $user->update(['status' => 'active']);
            $user->staffAvailability?->update(['availability_status' => 'AVAILABLE']);
        }

        return back()->with('success', "Informasi staf {$user->staffProfile?->full_name} berhasil diperbarui.");
    }

    // ----------------------------------------------------------------
    // スタッフ削除（論理削除）
    // ----------------------------------------------------------------
    public function destroy(int $userId)
    {
        $user = User::whereIn('role_type', $this->allowedRoles)->findOrFail($userId);

        $staffName = $user->staffProfile?->full_name ?? $user->name;

        // 論理削除（status = deleted）
        $user->update(['status' => 'deleted']);
        $user->staffProfile?->update(['is_active' => false]);
        $user->staffAvailability?->update(['availability_status' => 'SUSPENDED']);

        return back()->with('success', "Staf {$staffName} telah dinonaktifkan dari sistem.");
    }

    // ----------------------------------------------------------------
    // フォーマット用プライベートメソッド
    // ----------------------------------------------------------------
    private function formatStaff(User $u): array
    {
        return [
            'id'                  => $u->id,
            'name'                => $u->name,
            'email'               => $u->email,
            'role_type'           => $u->role_type,
            'status'              => $u->status,
            'full_name'           => $u->staffProfile?->full_name ?? $u->name,
            'department_code'     => $u->staffProfile?->department_code,
            'position_title'      => $u->staffProfile?->position_title,
            'phone'               => $u->staffProfile?->phone,
            'whatsapp'            => $u->staffProfile?->whatsapp,
            'join_date'           => $u->staffProfile?->join_date?->format('Y-m-d'),
            'contract_type'       => $u->staffProfile?->contract_type ?? 'PERMANENT',
            'base_salary'         => $u->staffProfile?->base_salary,
            'is_active'           => $u->staffProfile?->is_active ?? true,
            'availability_status' => $u->staffAvailability?->availability_status ?? 'AVAILABLE',
            'active_task_count'   => $u->staffAvailability?->active_task_count ?? 0,
            'created_at'          => $u->created_at?->format('Y-m-d'),
        ];
    }
}