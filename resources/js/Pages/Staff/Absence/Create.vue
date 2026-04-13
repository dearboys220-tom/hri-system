<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-2xl mx-auto space-y-6">

            <!-- ヘッダー -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">📋 Pengajuan Izin / Sakit</h1>
                    <p class="text-sm text-gray-500 mt-1">Ajukan izin tidak masuk kerja kepada manajer Anda.</p>
                </div>
                <a :href="route('admin.investigator.index')"
                   class="text-sm text-blue-500 hover:underline">← Kembali</a>
            </div>

            <!-- 成功メッセージ -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-3 text-sm">
                ✅ {{ $page.props.flash.success }}
            </div>

            <!-- 申請フォーム -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4">Formulir Pengajuan</h2>

                <div class="space-y-4">

                    <!-- 種別 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Ketidakhadiran *</label>
                        <select v-model="form.absence_type"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="SICK">Sakit</option>
                            <option value="PERSONAL">Keperluan Pribadi</option>
                            <option value="ANNUAL_LEAVE">Cuti Tahunan</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                        <p v-if="errors.absence_type" class="text-red-500 text-xs mt-1">{{ errors.absence_type }}</p>
                    </div>

                    <!-- 開始日・終了日 -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai *</label>
                            <input type="date" v-model="form.start_date"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                            <p v-if="errors.start_date" class="text-red-500 text-xs mt-1">{{ errors.start_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai *</label>
                            <input type="date" v-model="form.end_date"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                            <p v-if="errors.end_date" class="text-red-500 text-xs mt-1">{{ errors.end_date }}</p>
                        </div>
                    </div>

                    <!-- 理由 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan / Keterangan *</label>
                        <textarea v-model="form.reason" rows="3"
                                  placeholder="Jelaskan alasan ketidakhadiran..."
                                  class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
                        <p v-if="errors.reason" class="text-red-500 text-xs mt-1">{{ errors.reason }}</p>
                    </div>

                    <!-- 書類URL -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            URL Dokumen Pendukung <span class="text-gray-400">(opsional)</span>
                        </label>
                        <input type="url" v-model="form.document_url"
                               placeholder="https://drive.google.com/..."
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        <p class="text-xs text-gray-400 mt-1">Surat sakit, surat keterangan, dll.</p>
                    </div>

                    <button type="button" @click="submit"
                            :disabled="loading"
                            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-3 rounded-xl transition text-sm">
                        {{ loading ? 'Mengirim...' : '📤 Kirim Pengajuan' }}
                    </button>
                </div>
            </div>

            <!-- 申請履歴 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4">Riwayat Pengajuan</h2>

                <div v-if="myRequests.length === 0" class="text-sm text-gray-400 text-center py-4">
                    Belum ada pengajuan.
                </div>

                <div v-else class="space-y-3">
                    <div v-for="r in myRequests" :key="r.id"
                         class="flex items-start justify-between p-3 rounded-xl border border-gray-100 bg-gray-50">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ absenceTypeLabel(r.absence_type) }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ r.start_date }} ~ {{ r.end_date }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ r.reason }}</p>
                        </div>
                        <span :class="statusClass(r.approval_status)"
                              class="text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap">
                            {{ statusLabel(r.approval_status) }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    myRequests: { type: Array, default: () => [] },
})

const loading = ref(false)
const errors  = ref({})

const form = ref({
    absence_type: '',
    start_date:   '',
    end_date:     '',
    reason:       '',
    document_url: '',
})

const submit = () => {
    loading.value = true
    errors.value  = {}

    router.post(route('staff.absence.store'), form.value, {
        onSuccess: () => {
            form.value = { absence_type: '', start_date: '', end_date: '', reason: '', document_url: '' }
        },
        onError:  (e) => { errors.value = e },
        onFinish: ()  => { loading.value = false },
    })
}

const absenceTypeLabel = (type) => ({
    SICK:         '🤒 Sakit',
    PERSONAL:     '👤 Keperluan Pribadi',
    ANNUAL_LEAVE: '🌴 Cuti Tahunan',
    OTHER:        '📝 Lainnya',
}[type] ?? type)

const statusLabel = (s) => ({
    PENDING:  'Menunggu',
    APPROVED: 'Disetujui',
    REJECTED: 'Ditolak',
}[s] ?? s)

const statusClass = (s) => ({
    PENDING:  'bg-yellow-100 text-yellow-700',
    APPROVED: 'bg-green-100 text-green-700',
    REJECTED: 'bg-red-100 text-red-700',
}[s] ?? 'bg-gray-100 text-gray-600')
</script>