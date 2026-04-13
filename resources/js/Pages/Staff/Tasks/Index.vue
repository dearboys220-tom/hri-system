<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-3xl mx-auto space-y-6">

            <!-- ヘッダー -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h1 class="text-xl font-bold text-gray-800">📋 Tugas Saya</h1>
                <p class="text-sm text-gray-500 mt-1">Daftar instruksi kerja yang ditugaskan kepada Anda.</p>
            </div>

            <!-- フラッシュ -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-3 text-sm">
                ✅ {{ $page.props.flash.success }}
            </div>

            <!-- フィルター -->
            <div class="flex gap-2 flex-wrap">
                <button v-for="f in filters" :key="f.value"
                        @click="activeFilter = f.value"
                        :class="activeFilter === f.value ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200'"
                        class="px-4 py-2 rounded-xl text-sm font-medium transition">
                    {{ f.label }}（{{ countByStatus(f.value) }}）
                </button>
            </div>

            <!-- タスク一覧 -->
            <div v-if="filteredAssignments.length === 0"
                 class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-sm text-gray-400">
                Tidak ada tugas.
            </div>

            <div v-for="a in filteredAssignments" :key="a.id"
                 class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span :class="priorityClass(a.priority)"
                                  class="text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ priorityLabel(a.priority) }}
                            </span>
                            <span :class="taskStatusClass(a.task_status)"
                                  class="text-xs font-medium px-2 py-0.5 rounded-full">
                                {{ taskStatusLabel(a.task_status) }}
                            </span>
                        </div>

                        <p class="font-semibold text-gray-800">{{ a.title }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ a.description }}</p>

                        <div class="flex gap-4 mt-2 text-xs text-gray-400 flex-wrap">
                            <span>📅 Ditugaskan: {{ a.assigned_at }}</span>
                            <span v-if="a.due_date" :class="isOverdue(a.due_date) ? 'text-red-500 font-semibold' : ''">
                                ⏰ Batas: {{ a.due_date }}
                                <span v-if="isOverdue(a.due_date)">（期限超過）</span>
                            </span>
                            <span v-if="a.started_at">🚀 Mulai: {{ a.started_at }}</span>
                            <span v-if="a.completed_at">✅ Selesai: {{ a.completed_at }}</span>
                        </div>
                    </div>

                    <!-- アクションボタン -->
                    <div class="flex flex-col gap-2 min-w-[110px]">
                        <button v-if="a.task_status === 'ASSIGNED'"
                                @click="startTask(a.id)"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                            🚀 Mulai
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    assignments: Array,
})

const activeFilter = ref('ALL')
const filters = [
    { value: 'ALL',         label: 'Semua' },
    { value: 'ASSIGNED',    label: 'Ditugaskan' },
    { value: 'IN_PROGRESS', label: 'Berjalan' },
    { value: 'COMPLETED',   label: 'Selesai' },
]

const filteredAssignments = computed(() =>
    activeFilter.value === 'ALL'
        ? props.assignments
        : props.assignments.filter(a => a.task_status === activeFilter.value)
)
const countByStatus = (s) =>
    s === 'ALL' ? props.assignments.length : props.assignments.filter(a => a.task_status === s).length

const startTask = (id) => {
    router.post(route('staff.tasks.start', id), {}, {
        onSuccess: () => {},
    })
}

const isOverdue = (dueDate) => dueDate && new Date(dueDate) < new Date()

const priorityLabel = (p) => ({ LOW: '🟢 Rendah', NORMAL: '🔵 Normal', HIGH: '🟠 Tinggi', URGENT: '🔴 Mendesak' }[p] ?? p)
const priorityClass = (p) => ({ LOW: 'bg-green-100 text-green-700', NORMAL: 'bg-blue-100 text-blue-700', HIGH: 'bg-orange-100 text-orange-700', URGENT: 'bg-red-100 text-red-700' }[p] ?? 'bg-gray-100')
const taskStatusLabel = (s) => ({ ASSIGNED: 'Ditugaskan', IN_PROGRESS: 'Sedang Berjalan', COMPLETED: 'Selesai', ESCALATED: 'Eskalasi', CANCELLED: 'Dibatalkan' }[s] ?? s)
const taskStatusClass = (s) => ({ ASSIGNED: 'bg-yellow-100 text-yellow-700', IN_PROGRESS: 'bg-blue-100 text-blue-700', COMPLETED: 'bg-green-100 text-green-700', ESCALATED: 'bg-red-100 text-red-700' }[s] ?? 'bg-gray-100 text-gray-600')
</script>