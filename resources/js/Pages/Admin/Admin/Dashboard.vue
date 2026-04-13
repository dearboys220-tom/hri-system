<script setup>
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
import AiChatWidget from '@/Components/AiChatWidget.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: (h, page) =>
        h(AdminLayout, {
            title: 'Dashboard Admin',
            subtitle: 'Manajemen Anggota & Statistik'
        }, () => page)
});

const props = defineProps({
    stats:        { type: Object, default: () => ({}) },
    members:      { type: Array,  default: () => [] },
    total:        { type: Number, default: 0 },
    currentPage:  { type: Number, default: 1 },
    perPage:      { type: Number, default: 20 },
    statusFilter: { type: String, default: 'all' },
    search:       { type: String, default: '' },
});

const searchInput = ref(props.search);
const chatOpen    = ref(false);

function applyFilter(status) {
    router.get(route('admin.admin.index'), { status, search: searchInput.value, page_num: 1 });
}
function applySearch() {
    router.get(route('admin.admin.index'), { status: props.statusFilter, search: searchInput.value, page_num: 1 });
}
function goPage(p) {
    router.get(route('admin.admin.index'), { status: props.statusFilter, search: searchInput.value, page_num: p });
}

const totalPages = Math.ceil(props.total / props.perPage);

// ★ Sedang Dinilai を削除・Kondisional を追加
const statCards = [
    { key: 'pending_payment',     label: 'Menunggu Pembayaran', color: 'bg-gray-100 text-gray-600',     icon: '💳' },
    { key: 'under_investigation', label: 'Sedang Diselidiki',   color: 'bg-orange-100 text-orange-600', icon: '🔍' },
    { key: 'pending_admin',       label: 'Menunggu Admin',      color: 'bg-red-100 text-red-600',       icon: '⏳' },
    { key: 'perlu_koreksi',       label: 'Perlu Koreksi',       color: 'bg-purple-100 text-purple-600', icon: '⚠️' },
    { key: 'terverifikasi',       label: 'Terverifikasi',       color: 'bg-green-100 text-green-600',   icon: '✅' },
    { key: 'conditional',         label: 'Kondisional',         color: 'bg-yellow-100 text-yellow-600', icon: '🔶' },
    { key: 'ditolak',             label: 'Ditolak',             color: 'bg-slate-100 text-slate-500',   icon: '❌' },
];

const filterButtons = [
    { value: 'all',                 label: 'Semua' },
    { value: 'Terdaftar',           label: 'Terdaftar' },
    { value: 'Terverifikasi',       label: 'Terverifikasi' },
    { value: 'conditional_approved',label: 'Kondisional' },
    { value: 'Ditolak',             label: 'Ditolak' },
    { value: 'Perlu Koreksi',       label: 'Perlu Koreksi' },
];

function statusBadge(status) {
    const map = {
        'Terverifikasi':        'bg-green-100 text-green-700',
        'conditional_approved': 'bg-yellow-100 text-yellow-700',
        'Ditolak':              'bg-red-100 text-red-700',
        'Perlu Koreksi':        'bg-purple-100 text-purple-700',
        'not_certified':        'bg-orange-100 text-orange-600',
        'not_applied':          'bg-slate-100 text-slate-500',
        'Terdaftar':            'bg-slate-100 text-slate-600',
    };
    return map[status] ?? 'bg-slate-100 text-slate-500';
}

function statusLabel(status) {
    const map = {
        'Terverifikasi':        'Terverifikasi',
        'conditional_approved': 'Kondisional',
        'Ditolak':              'Ditolak',
        'Perlu Koreksi':        'Perlu Koreksi',
        'not_certified':        'Belum Tersertifikasi',
        'not_applied':          'Belum Mendaftar',
        'Terdaftar':            'Terdaftar',
    };
    return map[status] ?? status ?? 'Terdaftar';
}
</script>

<template>
    <div class="space-y-6">

        <!-- 統計カード（7枚） -->
        <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-4">
            <div
                v-for="card in statCards"
                :key="card.key"
                :class="['rounded-2xl p-4 text-center shadow-sm border border-white/80', card.color]"
            >
                <div class="text-2xl mb-1">{{ card.icon }}</div>
                <div class="text-3xl font-extrabold">{{ stats[card.key] ?? 0 }}</div>
                <div class="text-xs mt-1 font-medium leading-tight">{{ card.label }}</div>
            </div>
        </div>

        <!-- 検索 & フィルター -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari nama atau ID anggota..."
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

        <!-- 会員テーブル -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-700">
                    Daftar Anggota
                    <span class="text-sm font-normal text-slate-400 ml-2">{{ total }} orang</span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-6 py-3 font-semibold">Nama Lengkap</th>
                            <th class="text-left px-6 py-3 font-semibold">ID Anggota</th>
                            <th class="text-left px-6 py-3 font-semibold">Status</th>
                            <th class="text-center px-6 py-3 font-semibold">Skor</th>
                            <th class="text-left px-6 py-3 font-semibold">Tgl Registrasi</th>
                            <th class="text-left px-6 py-3 font-semibold">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="members.length === 0">
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <div class="text-4xl mb-3">🔍</div>
                                <p>Tidak ada data yang ditemukan</p>
                            </td>
                        </tr>
                        <tr
                            v-for="m in members"
                            :key="m.id"
                            class="border-t border-slate-100 hover:bg-slate-50 transition"
                        >
                            <td class="px-6 py-3 font-medium text-slate-800">{{ m.full_name ?? '-' }}</td>
                            <td class="px-6 py-3 font-mono text-admin-primary-600 text-xs">{{ m.member_id }}</td>
                            <td class="px-6 py-3">
                                <span :class="['text-xs px-2.5 py-1 rounded-full font-medium', statusBadge(m.certification_status)]">
                                    {{ statusLabel(m.certification_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span v-if="m.hri_score !== null" class="font-bold text-slate-700">{{ m.hri_score }}</span>
                                <span v-else class="text-slate-300">-</span>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs">{{ m.registered_at }}</td>
                            <td class="px-6 py-3">
                                <a
                                    :href="'/admin/admin/evaluate?id=' + m.user_id"
                                    class="text-xs text-admin-primary-600 hover:underline"
                                >Lihat →</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ページネーション -->
            <div v-if="totalPages > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-400">
                    {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, total) }} / {{ total }}
                </p>
                <div class="flex gap-1">
                    <button
                        v-for="p in totalPages"
                        :key="p"
                        @click="goPage(p)"
                        class="w-8 h-8 rounded-lg text-sm font-medium transition"
                        :class="p === currentPage
                            ? 'bg-admin-primary-700 text-white'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >{{ p }}</button>
                </div>
            </div>
        </div>
    </div>

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
                        <p class="text-xs text-gray-400">Konsultasi kebijakan & panduan umum</p>
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