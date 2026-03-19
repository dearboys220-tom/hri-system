<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    jobs: Array,
});

const flash = computed(() => usePage().props.flash ?? {});

function formatSalary(min, max) {
    if (!min && !max) return 'Tidak disebutkan';
    const fmt = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
    if (min && max) return `${fmt(min)} – ${fmt(max)}`;
    if (min) return `${fmt(min)}+`;
    return `s/d ${fmt(max)}`;
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    const day   = String(d.getUTCDate()).padStart(2, '0');
    const month = String(d.getUTCMonth() + 1).padStart(2, '0');
    const year  = d.getUTCFullYear();
    return `${day}-${month}-${year}`;
}

const statusConfig = {
    active:  { label: 'Aktif',   color: 'bg-green-100 text-green-700' },
    draft:   { label: 'Draft',   color: 'bg-gray-100 text-gray-600' },
    closed:  { label: 'Ditutup', color: 'bg-red-100 text-red-600' },
    deleted: { label: 'Dihapus', color: 'bg-red-200 text-red-800' },
};
</script>

<template>
    <Head title="Kelola Lowongan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Kelola Lowongan</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-5">

                <!-- 成功メッセージ -->
                <div v-if="flash.success" class="bg-green-50 border border-green-200 rounded-xl p-4 text-green-700 text-sm font-medium">
                    ✅ {{ flash.success }}
                </div>

                <!-- ヘッダー -->
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">{{ jobs.length }} lowongan ditemukan</p>
                    <Link href="/company/jobs/create"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-semibold transition">
                        + Posting Lowongan
                    </Link>
                </div>

                <!-- 求人なし -->
                <div v-if="jobs.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <p class="text-4xl mb-3">📋</p>
                    <p class="font-semibold text-gray-700">Belum ada lowongan</p>
                    <p class="text-sm text-gray-500 mt-1">Mulai posting lowongan pertama Anda!</p>
                    <Link href="/company/jobs/create"
                        class="inline-block mt-4 bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                        + Posting Sekarang
                    </Link>
                </div>

                <!-- 求人一覧 -->
                <div v-else class="space-y-3">
                    <div v-for="job in jobs" :key="job.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-semibold text-gray-800 hover:text-indigo-600">
                                    <Link :href="`/company/jobs/${job.id}`">{{ job.title }}</Link>
                                </h3>
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusConfig[job.status]?.color ?? 'bg-gray-100 text-gray-600']">
                                    {{ statusConfig[job.status]?.label ?? job.status }}
                                </span>
                                <span v-if="job.is_free_post" class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    🎁 GRATIS
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-3 mt-2 text-xs text-gray-500">
                                <span>📍 {{ job.location }}</span>
                                <span>💼 {{ job.employment_type }}</span>
                                <span>💰 {{ formatSalary(job.salary_min, job.salary_max) }}</span>
                                <span>📅 Deadline: {{ formatDate(job.application_deadline) }}</span>
                                <span>👥 {{ job.application_count }} pelamar</span>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <span class="text-xs text-gray-400">{{ job.views }} views</span>
                        </div>
                    </div>
                </div>

                <!-- ダッシュボードへ戻る -->
                <div class="pb-8">
                    <Link href="/company/dashboard" class="text-sm text-indigo-600 hover:underline">
                        ← Kembali ke Dashboard
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>