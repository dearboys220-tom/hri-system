<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- ヘッダー -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h1 class="text-xl font-bold text-gray-800">📋 Laporan Tugas Staf</h1>
                <p class="text-sm text-gray-500 mt-1">Daftar laporan yang dikirim oleh staf.</p>
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
                    {{ f.label }}
                </button>
            </div>

            <!-- 報告一覧 -->
            <div v-if="filteredReports.length === 0"
                 class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-sm text-gray-400">
                Tidak ada laporan.
            </div>

            <div v-for="r in filteredReports" :key="r.id"
                 class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <p class="font-semibold text-gray-800 text-sm">{{ r.staff_name }}</p>
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">
                                {{ roleLabel(r.role_type) }}
                            </span>
                            <span v-if="r.inconsistency_flag"
                                  class="text-xs bg-red-100 text-red-600 font-semibold px-2 py-0.5 rounded-full">
                                ⚠️ Ketidaksesuaian
                            </span>
                            <span v-if="r.evidence_attached_flag"
                                  class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded-full">
                                📎 Bukti terlampir
                            </span>
                        </div>

                        <p class="text-xs text-gray-400 mb-2">
                            📋 {{ r.task_title }} ·
                            🕐 {{ r.reported_at }}
                        </p>

                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ r.report_body }}</p>

                        <div v-if="r.ai_summary"
                             class="mt-3 bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700">
                            🤖 AI要約: {{ r.ai_summary }}
                        </div>
                    </div>

                    <!-- フラグボタン（矛盾なしのみ） -->
                    <button v-if="!r.inconsistency_flag"
                            @click="openFlag(r)"
                            class="text-xs text-orange-400 hover:text-orange-600 border border-orange-200 hover:border-orange-400 px-3 py-1.5 rounded-xl transition whitespace-nowrap">
                        ⚠️ Flag
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- フラグモーダル -->
    <div v-if="flagModal.show"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="font-bold text-gray-800 mb-3">⚠️ Tandai Ketidaksesuaian</h3>
            <p class="text-sm text-gray-600 mb-4">
                Laporan dari <strong>{{ flagModal.staffName }}</strong> akan ditandai sebagai tidak sesuai.
            </p>
            <label class="text-sm font-medium text-gray-700">Alasan <span class="text-red-500">*</span></label>
            <textarea v-model="flagModal.reason" rows="3"
                      class="w-full mt-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"
                      placeholder="Jelaskan alasan ketidaksesuaian..."></textarea>
            <p v-if="flagModal.error" class="text-red-500 text-xs mt-1">{{ flagModal.error }}</p>
            <div class="flex gap-3 mt-4">
                <button @click="flagModal.show = false"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition">
                    Batal
                </button>
                <button @click="submitFlag"
                        :disabled="flagModal.loading"
                        class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                    {{ flagModal.loading ? 'Memproses...' : 'Tandai' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    reports: Object, // paginate
})

const activeFilter = ref('ALL')
const filters = [
    { value: 'ALL',      label: 'Semua' },
    { value: 'NORMAL',   label: '✅ Normal' },
    { value: 'FLAGGED',  label: '⚠️ Bermasalah' },
]

const filteredReports = computed(() => {
    const data = props.reports.data ?? []
    if (activeFilter.value === 'FLAGGED') return data.filter(r => r.inconsistency_flag)
    if (activeFilter.value === 'NORMAL')  return data.filter(r => !r.inconsistency_flag)
    return data
})

// フラグモーダル
const flagModal = ref({ show: false, reportId: null, staffName: '', reason: '', loading: false, error: '' })
const openFlag  = (r) => {
    flagModal.value = { show: true, reportId: r.id, staffName: r.staff_name, reason: '', loading: false, error: '' }
}
const submitFlag = () => {
    if (!flagModal.value.reason.trim()) {
        flagModal.value.error = 'Alasan wajib diisi.'
        return
    }
    flagModal.value.loading = true
    router.post(route('manager.reports.flag', flagModal.value.reportId), {
        reason: flagModal.value.reason,
    }, {
        onSuccess: () => { flagModal.value.show = false },
        onFinish:  () => { flagModal.value.loading = false },
    })
}

const roleLabel = (r) => ({
    investigator_user: 'Investigator', admin_user: 'Admin',
    em_staff: 'Staff', strategy_user: 'Strategy',
    ai_dev_user: 'AI Dev', marketing_user: 'Marketing',
}[r] ?? r)
</script>