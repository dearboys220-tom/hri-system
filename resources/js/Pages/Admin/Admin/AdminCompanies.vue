<script setup>
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
import AiChatWidget from '@/Components/AiChatWidget.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: (h, page) =>
        h(AdminLayout, {
            title: 'Manajemen Perusahaan',
            subtitle: 'Verifikasi & pengelolaan akun perusahaan'
        }, () => page)
});

const props = defineProps({
    companies:      { type: Array,  default: () => [] },
    companiesStats: { type: Object, default: () => ({}) },
    search:         { type: String, default: '' },
    statusFilter:   { type: String, default: 'all' },
});

const searchInput = ref(props.search);
const modalOpen   = ref(false);
const selected    = ref(null);
const newStatus   = ref('');
const processing  = ref(false);
const chatOpen    = ref(false);

function applySearch() {
    router.get(route('admin.admin.companies'), { search: searchInput.value, status: props.statusFilter });
}
function applyFilter(s) {
    router.get(route('admin.admin.companies'), { search: searchInput.value, status: s });
}
function openModal(company) {
    selected.value  = company;
    newStatus.value = company.company_verification_status;
    modalOpen.value = true;
}
function closeModal() {
    modalOpen.value = false;
    selected.value  = null;
}
function saveStatus() {
    if (!selected.value) return;
    processing.value = true;
    router.post(
        route('admin.admin.companies.status', selected.value.id),
        { status: newStatus.value },
        {
            onSuccess: () => { closeModal(); },
            onFinish:  () => { processing.value = false; }
        }
    );
}

const statusMap = {
    pending:   { label: 'Menunggu',      cls: 'bg-yellow-100 text-yellow-700' },
    verified:  { label: 'Terverifikasi', cls: 'bg-green-100 text-green-700' },
    suspended: { label: 'Ditangguhkan',  cls: 'bg-orange-100 text-orange-700' },
    rejected:  { label: 'Ditolak',       cls: 'bg-red-100 text-red-700' },
};

function statusBadge(s) {
    return statusMap[s] ?? { label: s, cls: 'bg-slate-100 text-slate-500' };
}

const filterButtons = [
    { value: 'all',       label: 'Semua' },
    { value: 'pending',   label: 'Menunggu' },
    { value: 'verified',  label: 'Terverifikasi' },
    { value: 'suspended', label: 'Ditangguhkan' },
    { value: 'rejected',  label: 'Ditolak' },
];
</script>

<template>
    <div class="space-y-6">

        <!-- 統計 -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-yellow-50 rounded-2xl p-5 text-center border border-yellow-100">
                <div class="text-3xl font-extrabold text-yellow-600">{{ companiesStats.pending ?? 0 }}</div>
                <div class="text-xs text-yellow-600 mt-1 font-medium">Menunggu Verifikasi</div>
            </div>
            <div class="bg-green-50 rounded-2xl p-5 text-center border border-green-100">
                <div class="text-3xl font-extrabold text-green-600">{{ companiesStats.verified ?? 0 }}</div>
                <div class="text-xs text-green-600 mt-1 font-medium">Terverifikasi</div>
            </div>
            <div class="bg-orange-50 rounded-2xl p-5 text-center border border-orange-100">
                <div class="text-3xl font-extrabold text-orange-600">{{ companiesStats.suspended ?? 0 }}</div>
                <div class="text-xs text-orange-600 mt-1 font-medium">Ditangguhkan</div>
            </div>
            <div class="bg-red-50 rounded-2xl p-5 text-center border border-red-100">
                <div class="text-3xl font-extrabold text-red-600">{{ companiesStats.rejected ?? 0 }}</div>
                <div class="text-xs text-red-600 mt-1 font-medium">Ditolak</div>
            </div>
        </div>

        <!-- 検索 & フィルター -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari nama perusahaan atau email..."
                    class="flex-1 px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-admin-primary-600 focus:outline-none"
                    @keydown.enter="applySearch"
                />
                <button
                    @click="applySearch"
                    class="px-5 py-2.5 bg-admin-primary-700 hover:bg-admin-primary-800 text-white text-sm font-semibold rounded-xl transition"
                >🔍 Cari</button>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="fb in filterButtons"
                    :key="fb.value"
                    @click="applyFilter(fb.value)"
                    class="px-4 py-1.5 rounded-full text-sm font-medium border transition"
                    :class="statusFilter === fb.value
                        ? 'bg-admin-primary-700 text-white border-admin-primary-700'
                        : 'bg-white text-slate-600 border-slate-300 hover:border-admin-primary-400'"
                >{{ fb.label }}</button>
            </div>
        </div>

        <!-- テーブル -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-700">Daftar Perusahaan
                    <span class="text-sm font-normal text-slate-400 ml-2">{{ companies.length }} perusahaan</span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-6 py-3 font-semibold">Nama Perusahaan</th>
                            <th class="text-left px-6 py-3 font-semibold">PIC</th>
                            <th class="text-left px-6 py-3 font-semibold">Kontak</th>
                            <th class="text-center px-6 py-3 font-semibold">Status</th>
                            <th class="text-left px-6 py-3 font-semibold">Tgl Daftar</th>
                            <th class="text-center px-6 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="companies.length === 0">
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <div class="text-4xl mb-3">🏢</div>
                                <p>Tidak ada perusahaan ditemukan</p>
                            </td>
                        </tr>
                        <tr
                            v-for="c in companies"
                            :key="c.id"
                            class="border-t border-slate-100 hover:bg-slate-50 transition"
                        >
                            <td class="px-6 py-3">
                                <p class="font-medium text-slate-800">{{ c.company_name }}</p>
                                <p class="text-xs text-slate-400">{{ c.company_email }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-slate-700">{{ c.pic_name ?? '-' }}</p>
                                <p class="text-xs text-slate-400">NIB: {{ c.nib ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs">{{ c.company_phone ?? '-' }}</td>
                            <td class="px-6 py-3 text-center">
                                <span :class="['text-xs px-2.5 py-1 rounded-full font-medium', statusBadge(c.company_verification_status).cls]">
                                    {{ statusBadge(c.company_verification_status).label }}
                                </span>
                                <p v-if="c.verified_at" class="text-xs text-slate-400 mt-1">{{ c.verified_at }}</p>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs">{{ c.registered_at }}</td>
                            <td class="px-6 py-3 text-center">
                                <button
                                    @click="openModal(c)"
                                    class="text-xs px-3 py-1.5 bg-admin-primary-50 hover:bg-admin-primary-100 text-admin-primary-700 font-medium rounded-lg transition"
                                >Ubah Status</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ステータス変更モーダル -->
    <Teleport to="body">
        <div v-if="modalOpen" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="font-bold text-slate-800 text-lg mb-1">Ubah Status Perusahaan</h3>
                <p class="text-sm text-slate-500 mb-5">{{ selected?.company_name }}</p>
                <div class="space-y-3 mb-6">
                    <label
                        v-for="(opt, val) in statusMap"
                        :key="val"
                        class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                        :class="newStatus === val ? 'border-admin-primary-600 bg-admin-primary-50' : 'border-slate-200 hover:border-slate-300'"
                    >
                        <input type="radio" v-model="newStatus" :value="val" class="sr-only" />
                        <span :class="['text-xs px-2.5 py-1 rounded-full font-medium', opt.cls]">{{ opt.label }}</span>
                        <span class="text-sm text-slate-600">{{ opt.label }}</span>
                    </label>
                </div>
                <div class="flex gap-3 justify-end">
                    <button @click="closeModal"
                        class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Batal
                    </button>
                    <button @click="saveStatus" :disabled="processing"
                        class="px-5 py-2 text-sm text-white bg-admin-primary-700 hover:bg-admin-primary-800 disabled:opacity-50 rounded-xl transition font-semibold">
                        {{ processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- ===== フローティング AI チャット ===== -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="chatOpen"
            class="fixed bottom-24 right-6 z-50 w-96 h-[520px] bg-gray-800 rounded-2xl shadow-2xl border border-gray-700 flex flex-col overflow-hidden"
        >
            <div class="flex items-center justify-between px-4 py-3 bg-gray-900 border-b border-gray-700 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🤖</span>
                    <div>
                        <p class="text-sm font-semibold text-white">AI Asisten Admin</p>
                        <p class="text-xs text-gray-400">Konsultasi verifikasi perusahaan</p>
                    </div>
                </div>
                <button @click="chatOpen = false" class="text-gray-400 hover:text-white transition-colors text-xl leading-none">✕</button>
            </div>
            <AiChatWidget :requests="[]" auto-case="" />
        </div>
    </Transition>

    <button
        @click="chatOpen = !chatOpen"
        class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-2xl transition-all duration-200"
        :class="chatOpen ? 'bg-gray-700 hover:bg-gray-600' : 'bg-red-500 hover:bg-red-400'"
        :title="chatOpen ? 'Tutup AI Chat' : 'Konsultasi AI'"
    >
        {{ chatOpen ? '✕' : '🤖' }}
    </button>
</template>