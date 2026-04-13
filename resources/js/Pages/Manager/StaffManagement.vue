<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'

const props = defineProps({
    staffList:    { type: Array, default: () => [] },
    allowedRoles: { type: Array, default: () => [] },
})

const page = usePage()

// ---- 成功メッセージ・仮パスワード ----
const flashSuccess  = computed(() => page.props.flash?.success)
const flashTempPass = computed(() => page.props.flash?.temp_password)

// ---- モーダル制御 ----
const showRegisterModal = ref(false)
const showEditModal     = ref(false)
const selectedStaff     = ref(null)

// ---- 登録フォーム ----
const registerForm = useForm({
    name:           '',
    email:          '',
    role_type:      '',
    full_name:      '',
    phone:          '',
    whatsapp:       '',
    position_title: '',
    join_date:      '',
    contract_type:  'PERMANENT',
    base_salary:    '',
})

// ---- 編集フォーム ----
const editForm = useForm({
    full_name:      '',
    phone:          '',
    whatsapp:       '',
    position_title: '',
    contract_type:  'PERMANENT',
    base_salary:    '',
    is_active:      true,
})

// ---- ロール表示名 ----
const roleLabels = {
    investigator_user: '調査部',
    admin_user:        '審査管理部',
    em_staff:          '一般社員',
    strategy_user:     '戦略マネジメント部',
    ai_dev_user:       'AI開発部',
    marketing_user:    'マーケティング部',
}

// ---- 稼働状態バッジ ----
const availBadge = {
    AVAILABLE: { label: 'Tersedia',  cls: 'bg-green-100 text-green-700' },
    BUSY:      { label: 'Sibuk',     cls: 'bg-yellow-100 text-yellow-700' },
    ON_LEAVE:  { label: 'Cuti',      cls: 'bg-blue-100 text-blue-700' },
    SUSPENDED: { label: 'Ditangguh', cls: 'bg-red-100 text-red-700' },
}

// ---- 登録送信 ----
function submitRegister() {
    registerForm.post(route('manager.staff.store'), {
        onSuccess: () => {
            showRegisterModal.value = false
            registerForm.reset()
        },
    })
}

// ---- 編集モーダルを開く ----
function openEdit(staff) {
    selectedStaff.value     = staff
    editForm.full_name      = staff.full_name
    editForm.phone          = staff.phone ?? ''
    editForm.whatsapp       = staff.whatsapp ?? ''
    editForm.position_title = staff.position_title ?? ''
    editForm.contract_type  = staff.contract_type ?? 'PERMANENT'
    editForm.base_salary    = staff.base_salary ?? ''
    editForm.is_active      = staff.is_active
    showEditModal.value     = true
}

// ---- 編集送信 ----
function submitEdit() {
    editForm.post(route('manager.staff.update', selectedStaff.value.id), {
        onSuccess: () => {
            showEditModal.value = false
        },
    })
}

// ---- 削除確認 ----
function confirmDelete(staff) {
    if (!confirm(`Nonaktifkan staf "${staff.full_name}"?\nAkun tidak akan bisa login setelah ini.`)) return
    router.delete(route('manager.staff.destroy', staff.id))
}

// ---- 金額フォーマット ----
function formatRp(val) {
    if (!val) return '-'
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <!-- ヘッダー -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Manajemen Staf</h1>
                <p class="text-sm text-gray-500 mt-0.5">Daftar dan pendaftaran staf internal</p>
            </div>
            <button
                @click="showRegisterModal = true"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition"
            >
                + Daftarkan Staf Baru
            </button>
        </div>

        <div class="max-w-6xl mx-auto px-6 py-6">

            <!-- 成功メッセージ -->
            <div v-if="flashSuccess" class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                <p class="text-green-700 text-sm font-semibold">{{ flashSuccess }}</p>
                <div v-if="flashTempPass" class="mt-2 bg-white border border-green-300 rounded px-3 py-2">
                    <p class="text-xs text-gray-500">Password sementara (sampaikan ke staf):</p>
                    <p class="text-base font-mono font-bold text-gray-800 mt-1">{{ flashTempPass }}</p>
                    <p class="text-xs text-red-500 mt-1">※ Staf wajib mengganti password setelah login pertama.</p>
                </div>
            </div>

            <!-- スタッフ一覧テーブル -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 text-gray-600 font-semibold">Nama</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-semibold">Departemen</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-semibold">Status Akun</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-semibold">Ketersediaan</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-semibold">Tugas Aktif</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-semibold">Bergabung</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="staffList.length === 0">
                            <td colspan="7" class="text-center py-10 text-gray-400">
                                Belum ada staf terdaftar.
                            </td>
                        </tr>
                        <tr v-for="staff in staffList" :key="staff.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-800">{{ staff.full_name }}</p>
                                <p class="text-xs text-gray-400">{{ staff.email }}</p>
                                <p v-if="staff.position_title" class="text-xs text-gray-500 mt-0.5">{{ staff.position_title }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-medium text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                    {{ roleLabels[staff.role_type] ?? staff.role_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="text-xs px-2 py-0.5 rounded font-medium"
                                    :class="staff.status === 'active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'"
                                >
                                    {{ staff.status === 'active' ? 'Aktif' : 'Ditangguh' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="text-xs px-2 py-0.5 rounded font-medium"
                                    :class="availBadge[staff.availability_status]?.cls ?? 'bg-gray-100 text-gray-600'"
                                >
                                    {{ availBadge[staff.availability_status]?.label ?? staff.availability_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700 font-semibold">
                                {{ staff.active_task_count }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ staff.join_date ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="openEdit(staff)"
                                        class="text-xs text-indigo-600 hover:underline font-medium"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="confirmDelete(staff)"
                                        class="text-xs text-red-500 hover:underline font-medium"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== 登録モーダル ===== -->
        <div v-if="showRegisterModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800">Daftarkan Staf Baru</h2>
                    <button @click="showRegisterModal = false" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Nama Login *</label>
                            <input v-model="registerForm.name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Nama akun" />
                            <p v-if="registerForm.errors.name" class="text-red-500 text-xs mt-1">{{ registerForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Email *</label>
                            <input v-model="registerForm.email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="email@hri.com" />
                            <p v-if="registerForm.errors.email" class="text-red-500 text-xs mt-1">{{ registerForm.errors.email }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 block mb-1">Nama Lengkap *</label>
                        <input v-model="registerForm.full_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Nama lengkap staf" />
                        <p v-if="registerForm.errors.full_name" class="text-red-500 text-xs mt-1">{{ registerForm.errors.full_name }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 block mb-1">Departemen / Role *</label>
                        <select v-model="registerForm.role_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">-- Pilih departemen --</option>
                            <option v-for="role in allowedRoles" :key="role" :value="role">
                                {{ roleLabels[role] ?? role }}
                            </option>
                        </select>
                        <p v-if="registerForm.errors.role_type" class="text-red-500 text-xs mt-1">{{ registerForm.errors.role_type }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">No. HP</label>
                            <input v-model="registerForm.phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="+62..." />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">WhatsApp</label>
                            <input v-model="registerForm.whatsapp" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="+62..." />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Jabatan</label>
                            <input v-model="registerForm.position_title" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="例: Senior Investigator" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Tanggal Bergabung</label>
                            <input v-model="registerForm.join_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Jenis Kontrak</label>
                            <select v-model="registerForm.contract_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="PERMANENT">Permanen</option>
                                <option value="CONTRACT">Kontrak</option>
                                <option value="FREELANCE">Freelance</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Gaji Pokok (IDR)</label>
                            <input v-model="registerForm.base_salary" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="0" />
                        </div>
                    </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button @click="showRegisterModal = false" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2">Batal</button>
                    <button
                        @click="submitRegister"
                        :disabled="registerForm.processing"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold px-5 py-2 rounded-lg transition"
                    >
                        {{ registerForm.processing ? 'Menyimpan...' : 'Daftarkan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ===== 編集モーダル ===== -->
        <div v-if="showEditModal && selectedStaff" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-gray-800">Edit Staf</h2>
                        <p class="text-xs text-gray-500">{{ selectedStaff.full_name }}</p>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">

                    <div>
                        <label class="text-xs font-semibold text-gray-600 block mb-1">Nama Lengkap *</label>
                        <input v-model="editForm.full_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                        <p v-if="editForm.errors.full_name" class="text-red-500 text-xs mt-1">{{ editForm.errors.full_name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">No. HP</label>
                            <input v-model="editForm.phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">WhatsApp</label>
                            <input v-model="editForm.whatsapp" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Jabatan</label>
                            <input v-model="editForm.position_title" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Jenis Kontrak</label>
                            <select v-model="editForm.contract_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="PERMANENT">Permanen</option>
                                <option value="CONTRACT">Kontrak</option>
                                <option value="FREELANCE">Freelance</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 block mb-1">Gaji Pokok (IDR)</label>
                        <input v-model="editForm.base_salary" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                    </div>

                    <div class="flex items-center gap-3">
                        <input v-model="editForm.is_active" type="checkbox" id="is_active" class="w-4 h-4 rounded" />
                        <label for="is_active" class="text-sm text-gray-700">Akun Aktif</label>
                        <span class="text-xs text-red-500">（非アクティブにすると稼働状態も停止になります）</span>
                    </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button @click="showEditModal = false" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2">Batal</button>
                    <button
                        @click="submitEdit"
                        :disabled="editForm.processing"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold px-5 py-2 rounded-lg transition"
                    >
                        {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>