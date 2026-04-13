<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- ヘッダー -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">📋 Manajemen Instruksi Tugas</h1>
                    <p class="text-sm text-gray-500 mt-1">Buat dan kelola instruksi kerja untuk staf.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a :href="route('manager.dashboard')"
                       class="text-sm text-blue-500 hover:underline flex items-center gap-1">
                        ← Dashboard
                    </a>
                    <button @click="showForm = !showForm"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
                        {{ showForm ? '✕ Tutup' : '＋ Buat Instruksi' }}
                    </button>
                </div>
            </div>

            <!-- フラッシュ -->
            <div v-if="$page.props.flash?.success"
                 class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-3 text-sm">
                ✅ {{ $page.props.flash.success }}
            </div>

            <!-- 作成フォーム -->
            <div v-if="showForm" class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4">Formulir Instruksi Baru</h2>

                <div class="space-y-4">

                    <!-- タイトル -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Instruksi *</label>
                        <input type="text" v-model="form.title"
                               placeholder="Contoh: Riset pasar tenaga kerja Q2 2026"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        <p v-if="errors.title" class="text-red-500 text-xs mt-1">{{ errors.title }}</p>
                    </div>

                    <!-- 詳細 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Detail Instruksi *</label>
                        <textarea v-model="form.description" rows="4"
                                  placeholder="Jelaskan secara detail apa yang harus dilakukan, metode pelaksanaan, dan hasil yang diharapkan..."
                                  class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
                        <p v-if="errors.description" class="text-red-500 text-xs mt-1">{{ errors.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <!-- 対象部署 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Divisi Target *</label>
                            <select v-model="form.target_division"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">-- Pilih Divisi --</option>
                                <option value="INVESTIGATION">Investigasi</option>
                                <option value="ADMIN">Manajemen Audit</option>
                                <option value="STRATEGY">Manajemen Strategi</option>
                                <option value="AI_DEV">Pengembangan AI</option>
                                <option value="MARKETING">Marketing</option>
                            </select>
                            <p v-if="errors.target_division" class="text-red-500 text-xs mt-1">{{ errors.target_division }}</p>
                        </div>

                        <!-- 優先度 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas *</label>
                            <select v-model="form.priority"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="LOW">🟢 Rendah</option>
                                <option value="NORMAL">🔵 Normal</option>
                                <option value="HIGH">🟠 Tinggi</option>
                                <option value="URGENT">🔴 Mendesak</option>
                            </select>
                        </div>

                        <!-- 期限 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batas Waktu</label>
                            <input type="date" v-model="form.due_date"
                                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        </div>
                    </div>

                    <!-- 担当者選択 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Staf yang Ditugaskan *
                            <span class="text-xs text-gray-400 ml-1">（空きスタッフのみ表示）</span>
                        </label>

                        <div v-if="availableStaff.length === 0"
                             class="text-sm text-gray-400 bg-gray-50 rounded-xl p-4 text-center">
                            Tidak ada staf yang tersedia saat ini.
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label v-for="staff in availableStaff" :key="staff.user_id"
                                   :class="form.assignee_ids.includes(staff.user_id)
                                       ? 'border-blue-400 bg-blue-50'
                                       : 'border-gray-200 bg-white hover:bg-gray-50'"
                                   class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition">
                                <input type="checkbox" :value="staff.user_id" v-model="form.assignee_ids"
                                       class="rounded" />
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ staff.name }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ roleLabel(staff.role_type) }} ·
                                        {{ deptLabel(staff.department_code) }} ·
                                        {{ staff.active_task_count }}件対応中
                                    </p>
                                </div>
                            </label>
                        </div>
                        <p v-if="errors.assignee_ids" class="text-red-500 text-xs mt-1">{{ errors.assignee_ids }}</p>
                    </div>

                    <button @click="submitOrder" :disabled="loading"
                            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-3 rounded-xl transition text-sm">
                        {{ loading ? 'Mengirim...' : '📤 Buat & Tugaskan' }}
                    </button>
                </div>
            </div>

            <!-- 指示一覧 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4">Daftar Instruksi</h2>

                <div v-if="orders.length === 0" class="text-sm text-gray-400 text-center py-6">
                    Belum ada instruksi.
                </div>

                <div class="space-y-4">
                    <div v-for="order in orders" :key="order.id"
                         class="border border-gray-100 rounded-xl p-4">

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span :class="priorityClass(order.priority)"
                                          class="text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ priorityLabel(order.priority) }}
                                    </span>
                                    <p class="font-semibold text-gray-800 text-sm">{{ order.title }}</p>
                                    <span :class="statusClass(order.approval_status)"
                                          class="text-xs font-medium px-2 py-0.5 rounded-full">
                                        {{ statusLabel(order.approval_status) }}
                                    </span>
                                </div>

                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ order.description }}</p>

                                <div class="flex gap-3 mt-2 text-xs text-gray-400 flex-wrap">
                                    <span>📅 Dibuat: {{ order.created_at }}</span>
                                    <span v-if="order.due_date">⏰ Batas: {{ order.due_date }}</span>
                                </div>

                                <!-- 担当者タグ -->
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span v-for="a in order.assignments" :key="a.id"
                                          :class="assignStatusClass(a.task_status)"
                                          class="text-xs px-2 py-1 rounded-full border">
                                        {{ a.staff_name }} — {{ assignStatusLabel(a.task_status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- キャンセルボタン -->
                            <button v-if="['DRAFT','APPROVED'].includes(order.approval_status)"
                                    @click="confirmCancel(order)"
                                    class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-xl transition whitespace-nowrap">
                                キャンセル
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- キャンセル確認モーダル -->
    <div v-if="cancelTarget"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <h3 class="font-bold text-gray-800 mb-2">⚠️ Batalkan Instruksi?</h3>
            <p class="text-sm text-gray-600 mb-4">
                「{{ cancelTarget.title }}」を本当にキャンセルしますか？割当済みの担当者の指示も取り消されます。
            </p>
            <div class="flex gap-3">
                <button @click="cancelTarget = null"
                        class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition">
                    戻る
                </button>
                <button @click="submitCancel"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                    キャンセルする
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    orders:         Array,
    availableStaff: Array,
})

const showForm    = ref(false)
const loading     = ref(false)
const errors      = ref({})
const cancelTarget = ref(null)

const form = ref({
    title:           '',
    description:     '',
    target_division: '',
    priority:        'NORMAL',
    due_date:        '',
    assignee_ids:    [],
})

const submitOrder = () => {
    loading.value = true
    errors.value  = {}
    router.post(route('manager.task-orders.store'), form.value, {
        onSuccess: () => {
            showForm.value = false
            form.value = {
                title: '', description: '', target_division: '',
                priority: 'NORMAL', due_date: '', assignee_ids: [],
            }
        },
        onError:  (e) => { errors.value = e },
        onFinish: ()  => { loading.value = false },
    })
}

const confirmCancel = (order) => { cancelTarget.value = order }
const submitCancel  = () => {
    router.post(route('manager.task-orders.cancel', cancelTarget.value.id), {}, {
        onSuccess: () => { cancelTarget.value = null },
    })
}

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

const statusLabel = (s) => ({
    DRAFT: 'Draft', PENDING_APPROVAL: 'Menunggu', APPROVED: 'Disetujui',
    IN_PROGRESS: 'Berjalan', COMPLETED: 'Selesai',
    CANCELLED: 'Dibatalkan', CLOSED: 'Ditutup',
}[s] ?? s)

const statusClass = (s) => ({
    APPROVED:    'bg-green-100 text-green-700',
    IN_PROGRESS: 'bg-blue-100 text-blue-700',
    COMPLETED:   'bg-gray-100 text-gray-600',
    CANCELLED:   'bg-red-100 text-red-500',
}[s] ?? 'bg-yellow-100 text-yellow-700')

const assignStatusLabel = (s) => ({
    ASSIGNED:    'Ditugaskan',
    ACKNOWLEDGED:'Diterima',
    IN_PROGRESS: 'Berjalan',
    COMPLETED:   'Selesai',
    ESCALATED:   'Eskalasi',
    FAILED:      'Gagal',
}[s] ?? s)

const assignStatusClass = (s) => ({
    ASSIGNED:    'bg-yellow-50 text-yellow-700 border-yellow-200',
    ACKNOWLEDGED:'bg-purple-50 text-purple-700 border-purple-200',
    IN_PROGRESS: 'bg-blue-50 text-blue-700 border-blue-200',
    COMPLETED:   'bg-green-50 text-green-700 border-green-200',
    ESCALATED:   'bg-red-50 text-red-700 border-red-200',
    FAILED:      'bg-gray-50 text-gray-500 border-gray-200',
}[s] ?? 'bg-gray-50 text-gray-500 border-gray-200')

const roleLabel = (r) => ({
    investigator_user: 'Investigator', admin_user: 'Admin',
    em_staff: 'Staff', strategy_user: 'Strategy',
    ai_dev_user: 'AI Dev', marketing_user: 'Marketing',
}[r] ?? r)

const deptLabel = (d) => ({
    INVESTIGATION: 'Investigasi', ADMIN: 'Admin',
    STRATEGY: 'Strategi', AI_DEV: 'AI Dev', MARKETING: 'Marketing',
}[d] ?? d)
</script>