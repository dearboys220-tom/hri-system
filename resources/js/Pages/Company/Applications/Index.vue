<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    job: Object,
    applications: Array,
});

const selectedApp = ref(null);
const showModal = ref(false);

const statusForm = useForm({
    status: '',
    company_notes: '',
});

const openModal = (app) => {
    selectedApp.value = app;
    statusForm.status = app.status;
    statusForm.company_notes = app.company_notes ?? '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedApp.value = null;
};

const submitStatus = () => {
    statusForm.post(`/company/applications/${selectedApp.value.id}/status`, {
        onSuccess: () => closeModal(),
    });
};

const statusLabel = (s) => {
    const map = {
        pending:      { label: 'Menunggu',      color: 'bg-gray-100 text-gray-600' },
        shortlisted:  { label: 'Lolos Seleksi', color: 'bg-blue-100 text-blue-700' },
        interviewing: { label: 'Interview',     color: 'bg-yellow-100 text-yellow-700' },
        hired:        { label: 'Diterima ✅',   color: 'bg-green-100 text-green-700' },
        rejected:     { label: 'Ditolak',       color: 'bg-red-100 text-red-600' },
    };
    return map[s] ?? { label: s, color: 'bg-gray-100 text-gray-600' };
};

const formatDate = (d) => {
    if (!d) return '-';
    const dt = new Date(d);
    return `${dt.getDate()}-${dt.getMonth() + 1}-${dt.getFullYear()}`;
};
</script>

<template>
    <Head :title="`Pelamar – ${job.title}`" />

    <div class="min-h-screen bg-gray-50">

        <!-- ナビバー -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <Link href="/company/jobs" class="hover:text-gray-800">← Kelola Lowongan</Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-700 font-medium truncate max-w-xs">{{ job.title }}</span>
                </div>
                <Link href="/company/dashboard" class="text-sm text-indigo-600 hover:underline">
                    Dashboard
                </Link>
            </div>
        </nav>

        <div class="max-w-5xl mx-auto px-4 py-8 space-y-5">

            <!-- ヘッダー -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h1 class="text-xl font-bold text-gray-900">{{ job.title }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    📍 {{ job.location }} &nbsp;·&nbsp; {{ job.employment_type }}
                </p>
                <p class="text-sm font-semibold text-indigo-600 mt-2">
                    {{ applications.length }} pelamar
                </p>
            </div>

            <!-- 応募者なし -->
            <div v-if="applications.length === 0"
                 class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <p class="text-4xl mb-3">📭</p>
                <p class="font-semibold text-gray-600">Belum ada pelamar</p>
                <p class="text-sm text-gray-400 mt-1">Tunggu pelamar masuk ya!</p>
            </div>

            <!-- 応募者カード一覧 -->
            <div v-else class="space-y-4">
                <div v-for="app in applications" :key="app.id"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

                    <!-- 上段：アバター・名前・ステータス・ボタン -->
                    <div class="flex items-center gap-3">

                        <!-- アバター（固定サイズ） -->
                        <div style="width:44px; height:44px; min-width:44px; border-radius:50%; overflow:hidden; background:#eef2ff; display:flex; align-items:center; justify-content:center; border:1px solid #c7d2fe; flex-shrink:0;">
                            <img v-if="app.profile_photo"
                                 :src="`/storage/${app.profile_photo}`"
                                 style="width:44px; height:44px; object-fit:cover; display:block;" />
                            <span v-else style="font-size:16px; font-weight:700; color:#4f46e5;">
                                {{ app.applicant_name.charAt(0).toUpperCase() }}
                            </span>
                        </div>

                        <!-- 名前・バッジ -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-gray-900 text-sm">{{ app.applicant_name }}</span>
                                <span :class="['text-xs px-2.5 py-0.5 rounded-full font-medium', statusLabel(app.status).color]">
                                    {{ statusLabel(app.status).label }}
                                </span>
                                <span v-if="app.certification_status === 'Terverifikasi'"
                                      class="text-xs px-2.5 py-0.5 rounded-full font-medium bg-emerald-100 text-emerald-700">
                                    ✅ HRI Verified
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">
                                📅 Melamar: {{ formatDate(app.applied_at) }}
                            </p>
                        </div>

                        <!-- 詳細ボタン -->
                        <Link :href="`/company/applications/${app.id}`"
                            class="flex-shrink-0 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium px-4 py-2 rounded-xl transition">
                            Lihat Detail
                        </Link>

                        <!-- ステータス変更ボタン -->
                        <button @click="openModal(app)"
                                class="flex-shrink-0 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-semibold px-4 py-2 rounded-xl transition">
                            Ubah Status
                        </button>
                    </div>

                    <!-- 下段：詳細情報グリッド -->
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">📧 Email</p>
                            <p class="text-xs font-medium text-gray-700 truncate">{{ app.applicant_email }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">📱 No. HP</p>
                            <p class="text-xs font-medium text-gray-700">{{ app.phone_number || '-' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">🪪 Member ID</p>
                            <p class="text-xs font-medium text-gray-700">{{ app.member_id }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">⭐ HRI Score</p>
                            <p class="text-xs font-bold"
                               :class="app.hri_score ? 'text-indigo-600' : 'text-gray-400'">
                                {{ app.hri_score ?? 'Belum ada' }}
                            </p>
                        </div>
                    </div>

                    <!-- 企業メモ -->
                    <div v-if="app.company_notes"
                         class="mt-3 bg-yellow-50 border border-yellow-100 rounded-xl px-4 py-2 text-xs text-yellow-800">
                        📝 {{ app.company_notes }}
                    </div>

                </div>
            </div>

        </div>

        <!-- ステータス変更モーダル -->
        <div v-if="showModal"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                <h3 class="font-bold text-gray-900 text-base mb-1">Ubah Status Lamaran</h3>
                <p class="text-sm text-gray-500 mb-5">{{ selectedApp?.applicant_name }}</p>

                <!-- ステータス選択 -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="s in ['pending', 'shortlisted', 'interviewing', 'hired', 'rejected']"
                            :key="s"
                            @click="statusForm.status = s"
                            :class="[
                                'py-2 px-3 rounded-xl text-xs font-medium border transition text-left',
                                statusForm.status === s
                                    ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                    : 'border-gray-200 text-gray-600 hover:border-indigo-300'
                            ]">
                            {{ statusLabel(s).label }}
                        </button>
                    </div>
                </div>

                <!-- メモ -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea
                        v-model="statusForm.company_notes"
                        rows="3"
                        placeholder="Tambahkan catatan untuk pelamar ini..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none">
                    </textarea>
                </div>

                <!-- ボタン -->
                <div class="flex gap-3">
                    <button @click="closeModal"
                            class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button @click="submitStatus"
                            :disabled="statusForm.processing"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white py-2.5 rounded-xl text-sm font-bold transition">
                        {{ statusForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>

            </div>
        </div>

    </div>
</template>