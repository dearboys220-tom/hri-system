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
                            <span v-if="a.delay_flag"
                                  class="text-xs bg-red-100 text-red-600 font-semibold px-2 py-0.5 rounded-full">
                                ⚠️ Terlambat
                            </span>
                        </div>

                        <p class="font-semibold text-gray-800">{{ a.title }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ a.description }}</p>

                        <div class="flex gap-4 mt-2 text-xs text-gray-400 flex-wrap">
                            <span>📅 Ditugaskan: {{ a.assigned_at }}</span>
                            <span v-if="a.due_date"
                                  :class="isOverdue(a.due_date) && a.task_status !== 'COMPLETED' ? 'text-red-500 font-semibold' : ''">
                                ⏰ Batas: {{ a.due_date }}
                                <span v-if="isOverdue(a.due_date) && a.task_status !== 'COMPLETED'">（期限超過）</span>
                            </span>
                            <span v-if="a.started_at">🚀 Mulai: {{ a.started_at }}</span>
                            <span v-if="a.completed_at">✅ Selesai: {{ a.completed_at }}</span>
                        </div>
                    </div>

                    <!-- アクションボタン -->
                    <div class="flex flex-col gap-2 min-w-[110px]">
                        <!-- 着手ボタン -->
                        <button v-if="a.task_status === 'ASSIGNED' || a.task_status === 'ACKNOWLEDGED'"
                                @click="startTask(a.id)"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                            🚀 Mulai
                        </button>

                        <!-- 報告ボタン（IN_PROGRESS のみ） -->
                        <button v-if="a.task_status === 'IN_PROGRESS'"
                                @click="openReport(a)"
                                class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                            📝 Laporan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 報告提出モーダル -->
    <div v-if="reportModal.show"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
            <h3 class="font-bold text-gray-800 mb-1">📝 Kirim Laporan Tugas</h3>
            <p class="text-sm text-gray-500 mb-4">{{ reportModal.title }}</p>

            <label class="text-sm font-medium text-gray-700">
                Isi Laporan <span class="text-red-500">*</span>
            </label>
            <textarea v-model="reportModal.body" rows="5"
                      placeholder="Jelaskan hasil pelaksanaan tugas, kendala yang dihadapi, dan pencapaian..."
                      class="w-full mt-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
            <p v-if="reportModal.error" class="text-red-500 text-xs mt-1">{{ reportModal.error }}</p>

            <label class="flex items-center gap-2 mt-3 cursor-pointer">
                <input type="checkbox" v-model="reportModal.evidence" class="rounded" />
                <span class="text-sm text-gray-700">📎 Bukti / dokumen telah dilampirkan</span>
            </label>

            <div class="flex gap-3 mt-5">
                <button @click="reportModal.show = false"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition">
                    Batal
                </button>
                <button @click="submitReport"
                        :disabled="reportModal.loading"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                    {{ reportModal.loading ? 'Mengirim...' : '📤 Kirim Laporan' }}
                </button>
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

// ── フィルター ──
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
    s === 'ALL'
        ? props.assignments.length
        : props.assignments.filter(a => a.task_status === s).length

// ── 着手 ──
const startTask = (id) => {
    router.post(route('staff.tasks.start', id), {}, {
        preserveScroll: true,
    })
}

// ── 報告モーダル ──
const reportModal = ref({
    show:         false,
    assignmentId: null,
    title:        '',
    body:         '',
    evidence:     false,
    loading:      false,
    error:        '',
})

const openReport = (a) => {
    reportModal.value = {
        show:         true,
        assignmentId: a.id,
        title:        a.title,
        body:         '',
        evidence:     false,
        loading:      false,
        error:        '',
    }
}

const submitReport = () => {
    if (!reportModal.value.body.trim()) {
        reportModal.value.error = 'Isi laporan wajib diisi.'
        return
    }
    reportModal.value.loading = true
    reportModal.value.error   = ''

    router.post(route('staff.reports.store'), {
        task_assignment_id:     reportModal.value.assignmentId,
        report_body:            reportModal.value.body,
        evidence_attached_flag: reportModal.value.evidence,
    }, {
        onSuccess: () => { reportModal.value.show = false },
        onError:   () => { reportModal.value.error = 'Terjadi kesalahan. Silakan coba lagi.' },
        onFinish:  () => { reportModal.value.loading = false },
    })
}

// ── ユーティリティ ──
const isOverdue = (dueDate) => dueDate && new Date(dueDate) < new Date()

const priorityLabel = (p) => ({
    LOW: '🟢 Rendah', NORMAL: '🔵 Normal',
    HIGH: '🟠 Tinggi', URGENT: '🔴 Mendesak',
}[p] ?? p)

const priorityClass = (p) => ({
    LOW:    'bg-green-100 text-green-700',
    NORMAL: 'bg-blue-100 text-blue-700',
    HIGH:   'bg-orange-100 text-orange-700',
    URGENT: 'bg-red-100 text-red-700',
}[p] ?? 'bg-gray-100 text-gray-600')

const taskStatusLabel = (s) => ({
    ASSIGNED:    'Ditugaskan',
    ACKNOWLEDGED:'Diterima',
    IN_PROGRESS: 'Sedang Berjalan',
    COMPLETED:   'Selesai',
    DELAYED:     'Terlambat',
    ESCALATED:   'Eskalasi',
    FAILED:      'Gagal',
}[s] ?? s)

const taskStatusClass = (s) => ({
    ASSIGNED:    'bg-yellow-100 text-yellow-700',
    ACKNOWLEDGED:'bg-purple-100 text-purple-700',
    IN_PROGRESS: 'bg-blue-100 text-blue-700',
    COMPLETED:   'bg-green-100 text-green-700',
    DELAYED:     'bg-orange-100 text-orange-700',
    ESCALATED:   'bg-red-100 text-red-700',
    FAILED:      'bg-gray-100 text-gray-500',
}[s] ?? 'bg-gray-100 text-gray-600')
</script>