<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    jobs: Array,
});

const auth = usePage().props.auth;

// 検索・フィルター
const keyword = ref('');
const selectedType = ref('');

const employmentTypes = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'];

const filtered = computed(() => {
    return props.jobs.filter(job => {
        const matchKeyword = !keyword.value ||
            job.title.toLowerCase().includes(keyword.value.toLowerCase()) ||
            job.location.toLowerCase().includes(keyword.value.toLowerCase()) ||
            (job.company_name ?? '').toLowerCase().includes(keyword.value.toLowerCase());
        const matchType = !selectedType.value || job.employment_type === selectedType.value;
        return matchKeyword && matchType;
    });
});

// 給与フォーマット
const formatSalary = (min, max) => {
    const fmt = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
    if (min && max) return `${fmt(min)} – ${fmt(max)}`;
    if (min) return `${fmt(min)}+`;
    return 'Negotiable';
};

// 締切残り日数
const daysLeft = (deadline) => {
    if (!deadline) return null;
    return Math.ceil((new Date(deadline) - new Date()) / 86400000);
};

const deadlineLabel = (deadline) => {
    const d = daysLeft(deadline);
    if (d === null) return '-';
    if (d < 0) return 'Ditutup';
    if (d === 0) return 'Hari ini!';
    return `${d} hari lagi`;
};

const deadlineColor = (deadline) => {
    const d = daysLeft(deadline);
    if (d === null || d < 0) return 'text-red-400';
    if (d <= 3) return 'text-orange-500 font-bold';
    return 'text-green-600';
};

const employmentColor = (type) => {
    const map = {
        'Full-time': 'bg-blue-100 text-blue-700',
        'Part-time': 'bg-purple-100 text-purple-700',
        'Contract': 'bg-orange-100 text-orange-700',
        'Freelance': 'bg-teal-100 text-teal-700',
        'Internship': 'bg-pink-100 text-pink-700',
    };
    return map[type] ?? 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="Cari Lowongan – HRI" />

    <div class="min-h-screen bg-gray-50">

        <!-- ナビバー -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2">
                    <img src="/images/logo.png" class="h-8 w-auto" alt="HRI" />
                </Link>
                <div class="flex items-center gap-3">
                    <template v-if="!auth?.user">
                        <Link href="/login"
                              class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                            Login / Daftar
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/applicant/dashboard"
                              v-if="auth.user.role_type === 'applicant'"
                              class="text-sm text-indigo-600 hover:underline">
                            Dashboard
                        </Link>
                        <Link href="/company/dashboard"
                              v-else-if="auth.user.role_type === 'company'"
                              class="text-sm text-indigo-600 hover:underline">
                            Dashboard
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- ヒーローバナー -->
        <div class="bg-indigo-700 text-white py-10 px-4">
            <div class="max-w-5xl mx-auto text-center">
                <h1 class="text-2xl font-bold mb-2">Cari Lowongan Kerja</h1>
                <p class="text-indigo-200 text-sm mb-6">Temukan pekerjaan impianmu bersama HRI</p>

                <!-- 検索バー -->
                <div class="max-w-2xl mx-auto flex gap-2">
                    <input
                        v-model="keyword"
                        type="text"
                        placeholder="Cari jabatan, perusahaan, atau lokasi..."
                        class="flex-1 px-4 py-3 rounded-xl text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    />
                    <button class="bg-white text-indigo-700 font-bold px-5 py-3 rounded-xl text-sm hover:bg-indigo-50 transition">
                        🔍 Cari
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-8">

            <!-- フィルター -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button
                    @click="selectedType = ''"
                    :class="['text-xs px-4 py-2 rounded-full border transition', selectedType === '' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-400']">
                    Semua
                </button>
                <button
                    v-for="type in employmentTypes"
                    :key="type"
                    @click="selectedType = type"
                    :class="['text-xs px-4 py-2 rounded-full border transition', selectedType === type ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-400']">
                    {{ type }}
                </button>
            </div>

            <!-- 件数表示 -->
            <p class="text-sm text-gray-500 mb-4">
                {{ filtered.length }} lowongan ditemukan
            </p>

            <!-- 求人なし -->
            <div v-if="filtered.length === 0" class="text-center py-20 text-gray-400">
                <p class="text-4xl mb-3">🔍</p>
                <p class="font-medium">Lowongan tidak ditemukan</p>
                <p class="text-sm mt-1">Coba ubah kata kunci pencarian</p>
            </div>

            <!-- 求人カード一覧 -->
            <div class="space-y-4">
                <Link
                    v-for="job in filtered"
                    :key="job.id"
                    :href="`/jobs/${job.id}`"
                    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:border-indigo-300 hover:shadow-md transition group">

                    <div class="flex items-start gap-4">
                        <!-- 企業ロゴ -->
                        <div style="width:52px; height:52px; min-width:52px; overflow:hidden; border-radius:10px; border:1px solid #e5e7eb; background:#eef2ff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <img v-if="job.company_logo"
                                 :src="`/storage/${job.company_logo}`"
                                 style="width:52px; height:52px; object-fit:cover; display:block;" />
                            <span v-else class="text-xl font-bold text-indigo-500">
                                {{ (job.company_name ?? '?').charAt(0).toUpperCase() }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <!-- タイトル -->
                            <h2 class="font-bold text-gray-900 group-hover:text-indigo-600 transition truncate">
                                {{ job.title }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-0.5">{{ job.company_name ?? '-' }}</p>

                            <!-- バッジ行 -->
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span :class="['text-xs px-2.5 py-1 rounded-full font-medium', employmentColor(job.employment_type)]">
                                    {{ job.employment_type }}
                                </span>
                                <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                    📍 {{ job.location }}
                                </span>
                                <span class="text-xs px-2.5 py-1 rounded-full bg-green-50 text-green-700">
                                    💰 {{ formatSalary(job.salary_min, job.salary_max) }}
                                </span>
                            </div>
                        </div>

                        <!-- 締切 -->
                        <div class="text-right flex-shrink-0 hidden sm:block">
                            <span class="text-xs" :class="deadlineColor(job.application_deadline)">
                                ⏳ {{ deadlineLabel(job.application_deadline) }}
                            </span>
                        </div>
                    </div>

                </Link>
            </div>

        </div>
    </div>
</template>