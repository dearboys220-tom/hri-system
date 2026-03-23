<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    applications: Array,
});

const statusConfig = {
    pending:      { label: 'Menunggu',      bg: 'bg-yellow-100', text: 'text-yellow-700', icon: '⏳' },
    shortlisted:  { label: 'Lolos Seleksi', bg: 'bg-blue-100',   text: 'text-blue-700',   icon: '✅' },
    interviewing: { label: 'Interview',     bg: 'bg-purple-100', text: 'text-purple-700', icon: '🎯' },
    hired:        { label: 'Diterima',      bg: 'bg-green-100',  text: 'text-green-700',  icon: '🎉' },
    rejected:     { label: 'Tidak Lolos',   bg: 'bg-red-100',    text: 'text-red-700',    icon: '❌' },
};

function getStatus(status) {
    return statusConfig[status] ?? { label: status, bg: 'bg-gray-100', text: 'text-gray-600', icon: '•' };
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
}

const summary = computed(() => ({
    total:    props.applications.length,
    pending:  props.applications.filter(a => a.status === 'pending').length,
    progress: props.applications.filter(a => ['shortlisted', 'interviewing'].includes(a.status)).length,
    hired:    props.applications.filter(a => a.status === 'hired').length,
}));
</script>

<template>
    <Head title="Riwayat Lamaran" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Riwayat Lamaran Kerja
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- サマリーカード -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 text-center">
                        <p class="text-2xl font-bold text-gray-800">{{ summary.total }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total Lamaran</p>
                    </div>
                    <div class="bg-yellow-50 rounded-2xl border border-yellow-100 p-4 text-center">
                        <p class="text-2xl font-bold text-yellow-700">{{ summary.pending }}</p>
                        <p class="text-xs text-yellow-600 mt-1">Menunggu</p>
                    </div>
                    <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4 text-center">
                        <p class="text-2xl font-bold text-blue-700">{{ summary.progress }}</p>
                        <p class="text-xs text-blue-600 mt-1">Proses Seleksi</p>
                    </div>
                    <div class="bg-green-50 rounded-2xl border border-green-100 p-4 text-center">
                        <p class="text-2xl font-bold text-green-700">{{ summary.hired }}</p>
                        <p class="text-xs text-green-600 mt-1">Diterima</p>
                    </div>
                </div>

                <!-- 応募なし -->
                <div v-if="applications.length === 0"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <p class="text-4xl mb-3">📭</p>
                    <p class="text-gray-500 font-medium">Belum ada lamaran</p>
                    <p class="text-gray-400 text-sm mt-1">Cari lowongan dan mulai melamar sekarang!</p>
                    <Link href="/jobs"
                          class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition">
                        Lihat Lowongan
                    </Link>
                </div>

                <!-- 応募履歴リスト -->
                <div v-else class="space-y-3">
                    <div v-for="app in applications" :key="app.id"
                         class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">

                        <!-- 求人削除済み -->
                        <div v-if="app.job_deleted || !app.job"
                             class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm text-gray-400 italic">
                                    ⚠️ Lowongan ini sudah dihapus oleh perusahaan
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Tanggal lamar: {{ formatDate(app.applied_at) }}
                                </p>
                            </div>
                            <span :class="[getStatus(app.status).bg, getStatus(app.status).text]"
                                  class="flex-shrink-0 text-xs font-semibold px-3 py-1 rounded-full">
                                {{ getStatus(app.status).icon }} {{ getStatus(app.status).label }}
                            </span>
                        </div>

                        <!-- 通常表示 -->
                        <div v-else>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-800 truncate">{{ app.job.title }}</p>
                                    <p class="text-sm text-gray-500 mt-0.5">{{ app.job.company_name }}</p>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-gray-400">
                                        <span>📍 {{ app.job.location }}</span>
                                        <span>💼 {{ app.job.employment_type }}</span>
                                        <span>📅 Lamar: {{ formatDate(app.applied_at) }}</span>
                                    </div>
                                </div>
                                <span :class="[getStatus(app.status).bg, getStatus(app.status).text]"
                                      class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full whitespace-nowrap">
                                    {{ getStatus(app.status).icon }} {{ getStatus(app.status).label }}
                                </span>
                            </div>

                            <!-- 企業メモ -->
                            <div v-if="app.company_notes"
                                 class="mt-3 bg-gray-50 rounded-xl px-4 py-3 text-xs text-gray-600 border border-gray-100">
                                <span class="font-semibold text-gray-700">Catatan Perusahaan: </span>
                                {{ app.company_notes }}
                            </div>

                            <!-- 採用バナー -->
                            <div v-if="app.status === 'hired'"
                                 class="mt-3 bg-green-50 border border-green-200 rounded-xl px-4 py-2.5 text-xs text-green-700 font-medium">
                                🎉 Selamat! Kamu diterima bekerja di perusahaan ini.
                            </div>

                            <!-- 求人詳細リンク -->
                            <div class="mt-3 flex justify-end">
                                <Link :href="`/jobs/${app.job.id}`"
                                      class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                                    Lihat Detail Lowongan →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 求人一覧へ -->
                <div class="text-center pt-2">
                    <Link href="/jobs"
                          class="inline-block bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-6 py-2.5 rounded-xl transition">
                        ← Kembali ke Daftar Lowongan
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>