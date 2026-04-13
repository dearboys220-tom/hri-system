<?php

namespace App\Http\Controllers;

use App\Models\AiTaskOrder;
use App\Models\AiTaskAssignment;
use App\Models\StaffAvailability;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TaskOrderController extends Controller
{
    // ─────────────────────────────────────────────
    // order_no 採番ヘルパー
    // ─────────────────────────────────────────────
    private function generateOrderNo(): string
    {
        $today = now()->format('Ymd');
        $count = AiTaskOrder::whereDate('created_at', today())->count() + 1;
        return 'TO-' . $today . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────
    // マネージャー側：指示一覧
    // ─────────────────────────────────────────────
    public function index()
    {
        $manager = Auth::user();

        $orders = AiTaskOrder::with(['assignments.employee:id,name,role_type'])
            ->where('issued_by_user_id', $manager->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($o) => [
                'id'              => $o->id,
                'order_no'        => $o->order_no,
                'title'           => $o->instruction_title,
                'description'     => $o->instruction_body,
                'target_division' => $o->target_division,
                'priority'        => $o->priority_level,
                'due_date'        => $o->due_at?->format('Y-m-d'),
                'approval_status' => $o->approval_status,
                'created_at'      => $o->created_at->format('Y-m-d H:i'),
                'assignments'     => $o->assignments->map(fn($a) => [
                    'id'          => $a->id,
                    'staff_name'  => optional($a->employee)->name ?? '—',
                    'role_type'   => optional($a->employee)->role_type ?? '—',
                    'task_status' => $a->task_status,
                ]),
            ]);

        // 空きスタッフ一覧（割当候補）
        $availableStaff = StaffAvailability::with('user:id,name,role_type')
            ->where('availability_status', StaffAvailability::STATUS_AVAILABLE)
            ->orderBy('active_task_count', 'asc')
            ->get()
            ->map(fn($a) => [
                'user_id'           => $a->staff_user_id,
                'name'              => optional($a->user)->name ?? '—',
                'role_type'         => optional($a->user)->role_type ?? '—',
                'department_code'   => $a->department_code,
                'active_task_count' => $a->active_task_count,
            ]);

        return Inertia::render('Manager/TaskOrders/Index', [
            'orders'         => $orders,
            'availableStaff' => $availableStaff,
        ]);
    }

    // ─────────────────────────────────────────────
    // マネージャー側：指示を作成
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:200',
            'description'     => 'required|string|max:3000',
            'target_division' => 'required|string',
            'priority'        => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'due_date'        => 'nullable|date|after_or_equal:today',
            'assignee_ids'    => 'required|array|min:1',
            'assignee_ids.*'  => 'integer|exists:users,id',
        ]);

        $manager = Auth::user();

        DB::transaction(function () use ($request, $manager) {

            $orderNo = $this->generateOrderNo();

            // 指示レコード作成
            $order = AiTaskOrder::create([
                'order_no'             => $orderNo,
                'issued_by_user_id'    => $manager->id,
                'instruction_title'    => $request->title,
                'instruction_body'     => $request->description,
                'target_division'      => $request->target_division,
                'priority_level'       => $request->priority,
                'due_at'               => $request->due_date,
                'approval_status'      => AiTaskOrder::APPROVAL_APPROVED,
                'ai_processing_status' => AiTaskOrder::AI_ASSIGNED,
            ]);

            // 担当者別割当を生成
            foreach ($request->assignee_ids as $userId) {

                $assignmentData = [
                    'task_order_id'     => $order->id,
                    'employee_user_id'  => $userId,
                    'task_status'       => AiTaskAssignment::STATUS_ASSIGNED,
                    'assigned_by_ai_at' => now(),
                    'due_at'            => $request->due_date,
                ];

                // order_no が fillable に含まれていれば追加
                if (in_array('order_no', (new AiTaskAssignment())->getFillable())) {
                    $assignmentData['order_no'] = $orderNo;
                }

                AiTaskAssignment::create($assignmentData);

                // active_task_count 更新・BUSY判定
                $avail = StaffAvailability::where('staff_user_id', $userId)->first();
                if ($avail) {
                    $avail->incrementTaskCount();
                }
            }

            // 監査ログ（recordHuman を使用）
            AuditLog::recordHuman(
                'TASK_ORDER_CREATED',
                null,
                [
                    'new' => [
                        'order_no'          => $orderNo,
                        'instruction_title' => $order->instruction_title,
                        'priority_level'    => $order->priority_level,
                        'assignee_count'    => count($request->assignee_ids),
                    ],
                ]
            );
        });

        return back()->with('success', 'Instruksi berhasil dibuat dan ditugaskan kepada staf.');
    }

    // ─────────────────────────────────────────────
    // マネージャー側：指示をキャンセル
    // ─────────────────────────────────────────────
    public function cancel($id)
    {
        $manager = Auth::user();
        $order   = AiTaskOrder::where('issued_by_user_id', $manager->id)->findOrFail($id);

        if (!in_array($order->approval_status, [
            AiTaskOrder::APPROVAL_DRAFT,
            AiTaskOrder::APPROVAL_APPROVED,
        ])) {
            return back()->withErrors(['error' => 'Instruksi yang sedang berjalan tidak dapat dibatalkan.']);
        }

        DB::transaction(function () use ($order, $manager) {

            foreach ($order->assignments as $assignment) {
                if (in_array($assignment->task_status, [
                    AiTaskAssignment::STATUS_ASSIGNED,
                    AiTaskAssignment::STATUS_ACKNOWLEDGED,
                ])) {
                    $avail = StaffAvailability::where('staff_user_id', $assignment->employee_user_id)->first();
                    if ($avail) {
                        $avail->decrementTaskCount();
                    }

                    $assignment->update(['task_status' => AiTaskAssignment::STATUS_FAILED]);
                }
            }

            $order->update(['approval_status' => AiTaskOrder::APPROVAL_CANCELLED]);

            AuditLog::recordHuman(
                'TASK_ORDER_CREATED',
                null,
                ['new' => ['order_no' => $order->order_no, 'status' => 'CANCELLED']]
            );
        });

        return back()->with('success', 'Instruksi berhasil dibatalkan.');
    }

    // ─────────────────────────────────────────────
    // スタッフ側：自分宛て指示一覧
    // ─────────────────────────────────────────────
    public function staffIndex()
    {
        $user = Auth::user();

        $assignments = AiTaskAssignment::with(['taskOrder'])
            ->where('employee_user_id', $user->id)
            ->orderByRaw("FIELD(task_status,
                'ASSIGNED','ACKNOWLEDGED','IN_PROGRESS',
                'DELAYED','ESCALATED','COMPLETED','FAILED')")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($a) => [
                'id'            => $a->id,
                'task_order_id' => $a->task_order_id,
                'title'         => optional($a->taskOrder)->instruction_title ?? '—',
                'description'   => optional($a->taskOrder)->instruction_body ?? '—',
                'priority'      => optional($a->taskOrder)->priority_level ?? 'NORMAL',
                'due_date'      => $a->due_at?->format('Y-m-d'),
                'task_status'   => $a->task_status,
                'delay_flag'    => $a->delay_flag,
                'started_at'    => $a->started_at?->format('Y-m-d H:i'),
                'completed_at'  => $a->completed_at?->format('Y-m-d H:i'),
                'assigned_at'   => $a->assigned_by_ai_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Staff/Tasks/Index', [
            'assignments' => $assignments,
        ]);
    }

    // ─────────────────────────────────────────────
    // スタッフ側：着手（ASSIGNED / ACKNOWLEDGED → IN_PROGRESS）
    // ─────────────────────────────────────────────
    public function startTask($id)
    {
        $user       = Auth::user();
        $assignment = AiTaskAssignment::where('employee_user_id', $user->id)->findOrFail($id);

        if (!in_array($assignment->task_status, [
            AiTaskAssignment::STATUS_ASSIGNED,
            AiTaskAssignment::STATUS_ACKNOWLEDGED,
        ])) {
            return back()->withErrors(['error' => 'Status tugas tidak valid untuk dimulai.']);
        }

        $assignment->update([
            'task_status' => AiTaskAssignment::STATUS_IN_PROGRESS,
            'started_at'  => now(),
        ]);

        // staff_availability の last_reported_at を更新
        StaffAvailability::where('staff_user_id', $user->id)
            ->update(['last_reported_at' => now()]);

        AuditLog::recordHuman(
            'TASK_ASSIGNED',
            null,
            ['new' => ['assignment_id' => $assignment->id, 'status' => 'IN_PROGRESS']]
        );

        return back()->with('success', 'Tugas dimulai. Selamat bekerja!');
    }
}