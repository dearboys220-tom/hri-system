<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- ヘッダー -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h1 class="text-xl font-bold text-gray-800">📋 Manajemen Pengajuan Izin</h1>
                <p class="text-sm text-gray-500 mt-1">Setujui atau tolak pengajuan izin staf.</p>
            </div>

            <!-- フラッシュメッセージ -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-3 text-sm">
                ✅ {{ $page.props.flash.success }}
            </div>

            <!-- フィルター -->
            <div class="flex gap-2 flex-wrap">
                <button v-for="f in filters" :key="f.value"
                        @click="activeFilter = f.value"
                        :class="activeFilter === f.value
                            ? 'bg-blue-600 text-white'
                            : 'bg-white text-gray-600 border border-gray-200'"
                        class="px-4 py-2 rounded-xl text-sm font-medium transition">
                    {{ f.label }} ({{ countByStatus(f.value) }})
                </button>
            </div>

            <!-- 申請一覧 -->
            <div v-if="filteredRequests.length === 0"
                 class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-sm text-gray-400">
                Tidak ada pengajuan.
            </div>

            <div v-for="r in filteredRequests" :key="r.id"
                 class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-gray-800">{{ r.staff_name }}</p>
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">
                                {{ roleLabel(r.role_type) }}
                            </span>
                            <span :class="statusClass(r.approval_status)"
                                  class="text-xs font-semibold px-3 py-0.5 rounded-full">
                                {{ statusLabel(r.approval_status) }}
                            </span>
                        </div>

                        <div class="mt-2 text-sm text-gray-600 space-y-1">
                            <p>📅 <span class="font-medium">{{ absenceTypeLabel(r.absence_type) }}</span>
                               　{{ r.start_date }} ~ {{ r.end_date }}</p>
                            <p>📝 {{ r.reason }}</p>
                            <p v-if="r.document_url">
                                🔗 <a :href="r.document_url" target="_blank"
                                      class="text-blue-500 underline text-xs">Lihat Dokumen</a>
                            </p>
                            <p v-if="r.manager_note" class="text-gray-400 text-xs">
                                💬 Catatan: {{ r.manager_note }}
                            </p>
                        </div>

                        <p class="text-xs text-gray-400 mt-2">Diajukan: {{ r.created_at }}</p>
                    </div>

                    <!-- 承認・却下ボタン（PENDINGのみ） -->
                    <div v-if="r.approval_status === 'PENDING'" class="flex flex-col gap-2 min-w-[120px]">
                        <button @click="openApprove(r)"
                                class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                            ✅ Setujui
                        </button>
                        <button @click="openReject(r)"
                                class="bg-red-100 hover:bg-red-200 text-red-600 text-xs font-semibold px-4 py-2 rounded-xl transition">
                            ❌ Tolak
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 承認モーダル -->
    <div v-if="approveModal.show"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="font-bold text-gray-800 mb-3">✅ Setujui Pengajuan Izin</h3>
            <p class="text-sm text-gray-600 mb-1">
                <strong>{{ approveModal.request?.staff_name }}</strong> —
                {{ absenceTypeLabel(approveModal.request?.absence_type) }}
            </p>
            <p class="text-sm text-gray-500 mb-4">
                {{ approveModal.request?.start_date }} ~ {{ approveModal.request?.end_date }}
            </p>
            <label class="text-sm font-medium text-gray-700">Catatan (opsional)</label>
            <textarea v-model="approveModal.note" rows="2"
                      class="w-full mt-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"
                      placeholder="Pesan tambahan untuk staf..."></textarea>
            <div class="flex gap-3 mt-4">
                <button @click="approveModal.show = false"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition">
                    Batal
                </button>
                <button @click="submitApprove"
                        :disabled="approveModal.loading"
                        class="flex-1 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                    {{ approveModal.loading ? 'Memproses...' : 'Setujui' }}
                </button>
            </div>
        </div>
    </div>

    <!-- 却下モーダル -->
    <div v-if="rejectModal.show"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="font-bold text-gray-800 mb-3">❌ Tolak Pengajuan Izin</h3>
            <p class="text-sm text-gray-600 mb-4">
                <strong>{{ rejectModal.request?.staff_name }}</strong> —
                {{ absenceTypeLabel(rejectModal.request?.absence_type) }}
            </p>
            <label class="text-sm font-medium text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
            <textarea v-model="rejectModal.note" rows="3"
                      class="w-full mt-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                      placeholder="Jelaskan alasan penolakan..."></textarea>
            <p v-if="rejectModal.error" class="text-red-500 text-xs mt-1">{{ rejectModal.error }}</p>
            <div class="flex gap-3 mt-4">
                <button @click="rejectModal.show = false"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition">
                    Batal
                </button>
                <button @click="submitReject"
                        :disabled="rejectModal.loading"
                        class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                    {{ rejectModal.loading ? 'Memproses...' : 'Tolak Pengajuan' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    requests: Array,
})

// フィルター
const activeFilter = ref('ALL')
const filters = [
    { value: 'ALL', label: 'Semua' },
    { value: 'PENDING', label: 'Menunggu' },
    { value: 'APPROVED', label: 'Disetujui' },
    { value: 'REJECTED', label: 'Ditolak' },
]
const filteredRequests = computed(() =>
    activeFilter.value === 'ALL'
        ? props.requests
        : props.requests.filter(r => r.approval_status === activeFilter.value)
)
const countByStatus = (status) =>
    status === 'ALL'
        ? props.requests.length
        : props.requests.filter(r => r.approval_status === status).length

// 承認モーダル
const approveModal = ref({ show: false, request: null, note: '', loading: false })
const openApprove = (r) => { approveModal.value = { show: true, request: r, note: '', loading: false } }
const submitApprove = () => {
    approveModal.value.loading = true
    router.post(route('manager.absence.approve', approveModal.value.request.id), {
        manager_note: approveModal.value.note,
    }, {
        onSuccess: () => { approveModal.value.show = false },
        onFinish: () => { approveModal.value.loading = false },
    })
}

// 却下モーダル
const rejectModal = ref({ show: false, request: null, note: '', loading: false, error: '' })
const openReject = (r) => { rejectModal.value = { show: true, request: r, note: '', loading: false, error: '' } }
const submitReject = () => {
    if (!rejectModal.value.note.trim()) {
        rejectModal.value.error = 'Alasan penolakan wajib diisi.'
        return
    }
    rejectModal.value.loading = true
    router.post(route('manager.absence.reject', rejectModal.value.request.id), {
        manager_note: rejectModal.value.note,
    }, {
        onSuccess: () => { rejectModal.value.show = false },
        onFinish: () => { rejectModal.value.loading = false },
    })
}

const absenceTypeLabel = (type) => ({
    SICK: '🤒 Sakit', PERSONAL: '👤 Keperluan Pribadi',
    ANNUAL_LEAVE: '🌴 Cuti Tahunan', OTHER: '📝 Lainnya',
}[type] ?? type)

const statusLabel = (s) => ({ PENDING: 'Menunggu', APPROVED: 'Disetujui', REJECTED: 'Ditolak' }[s] ?? s)
const statusClass = (s) => ({
    PENDING: 'bg-yellow-100 text-yellow-700',
    APPROVED: 'bg-green-100 text-green-700',
    REJECTED: 'bg-red-100 text-red-700',
}[s] ?? 'bg-gray-100 text-gray-600')

const roleLabel = (role) => ({
    investigator_user: 'Investigator', admin_user: 'Admin',
    em_staff: 'Staff', strategy_user: 'Strategy',
    ai_dev_user: 'AI Dev', marketing_user: 'Marketing',
}[role] ?? role)
</script>