<script setup>
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
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

const statCards = [
    { key: 'pending_payment',     label: 'Menunggu Pembayaran', color: 'bg-gray-100 text-gray-600',     icon: '💳' },
    { key: 'under_investigation', label: 'Sedang Diselidiki',   color: 'bg-orange-100 text-orange-600', icon: '🔍' },
    { key: 'under_review',        label: 'Sedang Dinilai',      color: 'bg-blue-100 text-blue-600',     icon: '📋' },
    { key: 'pending_admin',       label: 'Menunggu Admin',      color: 'bg-red-100 text-red-600',       icon: '⏳' },
    { key: 'perlu_koreksi',       label: 'Perlu Koreksi',       color: 'bg-purple-100 text-purple-600', icon: '⚠️' },
    { key: 'terverifikasi',       label: 'Terverifikasi',       color: 'bg-green-100 text-green-600',   icon: '✅' },
    { key: 'ditolak',             label: 'Ditolak',             color: 'bg-slate-100 text-slate-500',   icon: '❌' },
];

const filterButtons = [
    { value: 'all',           label: 'Semua' },
    { value: 'Terdaftar',     label: 'Terdaftar' },
    { value: 'Terverifikasi', label: 'Terverifikasi' },
    { value: 'Ditolak',       label: 'Ditolak' },
    { value: 'Perlu Koreksi', label: 'Perlu Koreksi' },
];

function statusBadge(status) {
    const map = {
        'Terverifikasi': 'bg-green-100 text-green-700',
        'Ditolak':       'bg-red-100 text-red-700',
        'Perlu Koreksi': 'bg-purple-100 text-purple-700',
        'Terdaftar':     'bg-slate-100 text-slate-600',
    };
    return map[status] ?? 'bg-slate-100 text-slate-500';
}
</script>

<template>
    <div class="space-y-6">

        <!-- 統計カード -->
        <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-4">
            <div
                v-for="card in statCards"
                :key="card.key"
                :class="['rounded-2xl p-4 text-center shadow-sm border border-white/80', card.color]"
            >
                <div class="text-2xl mb-1">{{ card.icon }}</div>
                <div class="text-3xl font-extrabold">{{ stats[card.key] ?? 0 }}</div>
                <div class="text-xs mt-1 font-medium">{{ card.label }}</div>
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
                                    {{ m.certification_status ?? 'Terdaftar' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span v-if="m.hri_score !== null" class="font-bold text-slate-700">{{ m.hri_score }}</span>
                                <span v-else class="text-slate-300">-</span>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs">{{ m.registered_at }}</td>
                            <td class="px-6 py-3">
                                <a href="/admin/admin/evaluate" class="text-xs text-admin-primary-600 hover:underline">Lihat →</a>
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
</template>