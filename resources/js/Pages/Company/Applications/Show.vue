<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    application: Object,
    job: Object,
    applicantUser: Object,
    profile: Object,
    education: Array,
    work: Array,
    certifications: Array,
});

const activeTab = ref('basic');

const statusForm = useForm({
    status: props.application.status,
    company_notes: props.application.company_notes ?? '',
});

const submitStatus = () => {
    statusForm.post(`/company/applications/${props.application.id}/status`);
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
    <Head :title="`${profile?.full_name ?? applicantUser?.name} – Pelamar`" />

    <div class="min-h-screen bg-gray-50">

        <!-- ナビバー -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <Link :href="`/company/jobs/${job.id}/applications`" class="hover:text-gray-800">
                        ← Daftar Pelamar
                    </Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-700 font-medium">{{ profile?.full_name ?? applicantUser?.name }}</span>
                </div>
                <Link href="/company/dashboard" class="text-sm text-indigo-600 hover:underline">
                    Dashboard
                </Link>
            </div>
        </nav>

        <div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 左：メインコンテンツ -->
            <div class="lg:col-span-2 space-y-5">

                <!-- プロフィールヘッダー -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-4">
                        <!-- アバター -->
                        <div style="width:64px; height:64px; min-width:64px; border-radius:50%; overflow:hidden; background:#eef2ff; display:flex; align-items:center; justify-content:center; border:2px solid #c7d2fe; flex-shrink:0;">
                            <img v-if="profile?.profile_photo"
                                 :src="`/storage/${profile.profile_photo}`"
                                 style="width:64px; height:64px; object-fit:cover; display:block;" />
                            <span v-else style="font-size:24px; font-weight:700; color:#4f46e5;">
                                {{ (profile?.full_name ?? applicantUser?.name ?? '?').charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-xl font-bold text-gray-900">
                                    {{ profile?.full_name ?? applicantUser?.name }}
                                </h1>
                                <span :class="['text-xs px-2.5 py-1 rounded-full font-medium', statusLabel(application.status).color]">
                                    {{ statusLabel(application.status).label }}
                                </span>
                                <span v-if="profile?.certification_status === 'Terverifikasi'"
                                      class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-medium">
                                    ✅ HRI Verified
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-gray-500">
                                <span>🪪 {{ profile?.member_id ?? '-' }}</span>
                                <span>📅 Melamar: {{ formatDate(application.applied_at) }}</span>
                                <span v-if="profile?.hri_score">⭐ Score: <strong class="text-indigo-600">{{ profile.hri_score }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- タブ -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex border-b border-gray-100">
                        <button v-for="tab in [
                            { key: 'basic',  label: '👤 Info Dasar' },
                            { key: 'edu',    label: '🎓 Pendidikan' },
                            { key: 'work',   label: '💼 Pengalaman' },
                            { key: 'cert',   label: '🏆 Sertifikasi' },
                        ]"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                'flex-1 py-3 text-xs font-medium transition border-b-2',
                                activeTab === tab.key
                                    ? 'border-indigo-500 text-indigo-600 bg-indigo-50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                            ]">
                            {{ tab.label }}
                        </button>
                    </div>

                    <div class="p-6">

                        <!-- 基本情報 -->
                        <div v-if="activeTab === 'basic'" class="space-y-3">
                            <div v-if="!profile" class="text-center text-gray-400 py-8">
                                <p>プロフィール情報がありません</p>
                            </div>
                            <template v-else>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">Nama Lengkap</p>
                                        <p class="text-sm font-medium text-gray-800">{{ profile.full_name ?? '-' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">Email</p>
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ applicantUser?.email ?? '-' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">No. HP</p>
                                        <p class="text-sm font-medium text-gray-800">{{ profile.phone_number ?? '-' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">WhatsApp</p>
                                        <p class="text-sm font-medium text-gray-800">{{ profile.whatsapp_number ?? '-' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">Jenis Kelamin</p>
                                        <p class="text-sm font-medium text-gray-800">{{ profile.gender ?? '-' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">Tanggal Lahir</p>
                                        <p class="text-sm font-medium text-gray-800">{{ formatDate(profile.birth_date) }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">Kewarganegaraan</p>
                                        <p class="text-sm font-medium text-gray-800">{{ profile.nationality ?? '-' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">Status Pernikahan</p>
                                        <p class="text-sm font-medium text-gray-800">{{ profile.marital_status ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-400 mb-1">Alamat</p>
                                    <p class="text-sm font-medium text-gray-800">{{ profile.current_address ?? '-' }}</p>
                                </div>
                                <div v-if="profile.self_pr" class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-400 mb-1">Self PR</p>
                                    <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ profile.self_pr }}</p>
                                </div>
                            </template>
                        </div>

                        <!-- 学歴 -->
                        <div v-if="activeTab === 'edu'">
                            <div v-if="education.length === 0" class="text-center text-gray-400 py-8">
                                <p class="text-3xl mb-2">🎓</p>
                                <p>Data pendidikan belum diisi</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="edu in education" :key="edu.id"
                                     class="border border-gray-100 rounded-xl p-4">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ edu.school }}</p>
                                            <p class="text-sm text-gray-500 mt-0.5">{{ edu.level }}{{ edu.major ? ' – ' + edu.major : '' }}</p>
                                        </div>
                                        <span class="text-xs bg-blue-50 text-blue-600 px-2.5 py-1 rounded-full flex-shrink-0">
                                            {{ edu.level }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                        <span v-if="edu.enrollment_date">📅 Masuk: {{ formatDate(edu.enrollment_date) }}</span>
                                        <span v-if="edu.graduation_date">🎓 Lulus: {{ formatDate(edu.graduation_date) }}</span>
                                        <span v-if="edu.gpa">📊 IPK: {{ edu.gpa }}</span>
                                        <span v-if="edu.graduation_status">✅ {{ edu.graduation_status }}</span>
                                    </div>
                                    <div v-if="edu.achievements" class="mt-2 text-xs text-gray-600 bg-yellow-50 rounded-lg px-3 py-2">
                                        🏅 {{ edu.achievements }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 職歴 -->
                        <div v-if="activeTab === 'work'">
                            <div v-if="work.length === 0" class="text-center text-gray-400 py-8">
                                <p class="text-3xl mb-2">💼</p>
                                <p>Data pengalaman kerja belum diisi</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="w in work" :key="w.id"
                                     class="border border-gray-100 rounded-xl p-4">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ w.position }}</p>
                                            <p class="text-sm text-gray-500 mt-0.5">{{ w.company }}</p>
                                        </div>
                                        <span class="text-xs bg-purple-50 text-purple-600 px-2.5 py-1 rounded-full flex-shrink-0">
                                            {{ w.employment_type }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                        <span>📅 {{ formatDate(w.start_date) }} – {{ w.end_date ? formatDate(w.end_date) : 'Sekarang' }}</span>
                                        <span v-if="w.company_address">📍 {{ w.company_address }}</span>
                                    </div>
                                    <div v-if="w.duties" class="mt-2 text-xs text-gray-600 whitespace-pre-line leading-relaxed">
                                        {{ w.duties }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 資格 -->
                        <div v-if="activeTab === 'cert'">
                            <div v-if="certifications.length === 0" class="text-center text-gray-400 py-8">
                                <p class="text-3xl mb-2">🏆</p>
                                <p>Data sertifikasi belum diisi</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="cert in certifications" :key="cert.id"
                                     class="border border-gray-100 rounded-xl p-4">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ cert.name }}</p>
                                            <p class="text-sm text-gray-500 mt-0.5">{{ cert.organization }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                        <span>📅 Terbit: {{ formatDate(cert.issued_date) }}</span>
                                        <span v-if="cert.valid_until">⏳ Berlaku s/d: {{ formatDate(cert.valid_until) }}</span>
                                        <span v-if="cert.certificate_score">⭐ {{ cert.certificate_score }}</span>
                                    </div>
                                    <div v-if="cert.notes" class="mt-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                                        📝 {{ cert.notes }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- 右：ステータス管理サイドバー -->
            <div class="space-y-5">

                <!-- HRIスコアカード -->
                <div v-if="profile?.hri_score"
                     class="bg-indigo-600 rounded-2xl p-6 text-white text-center shadow-sm">
                    <p class="text-5xl font-bold">{{ profile.hri_score }}</p>
                    <p class="text-indigo-200 text-sm mt-2">Skor Sertifikasi HRI</p>
                    <p v-if="profile.certification_date" class="text-indigo-300 text-xs mt-1">
                        📅 {{ formatDate(profile.certification_date) }}
                    </p>
                </div>

                <!-- ステータス管理カード -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 mb-4">⚙️ Manajemen Status</h3>

                    <!-- ステータス選択 -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Status Lamaran</label>
                        <div class="space-y-2">
                            <button
                                v-for="s in ['pending', 'shortlisted', 'interviewing', 'hired', 'rejected']"
                                :key="s"
                                @click="statusForm.status = s"
                                :class="[
                                    'w-full text-left py-2 px-3 rounded-xl text-xs font-medium border transition',
                                    statusForm.status === s
                                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                        : 'border-gray-200 text-gray-600 hover:border-indigo-300'
                                ]">
                                {{ statusLabel(s).label }}
                            </button>
                        </div>
                    </div>

                    <!-- メモ -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Catatan Internal <span class="text-gray-400">(opsional)</span>
                        </label>
                        <textarea
                            v-model="statusForm.company_notes"
                            rows="3"
                            placeholder="Catatan untuk pelamar ini..."
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none">
                        </textarea>
                    </div>

                    <button
                        @click="submitStatus"
                        :disabled="statusForm.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white py-2.5 rounded-xl text-sm font-bold transition">
                        {{ statusForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>

                <!-- 応募求人情報 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 mb-3">📋 Info Lowongan</h3>
                    <p class="font-semibold text-sm text-gray-800">{{ job.title }}</p>
                    <div class="mt-2 space-y-1 text-xs text-gray-500">
                        <p>📍 {{ job.location }}</p>
                        <p>💼 {{ job.employment_type }}</p>
                        <p>📅 Deadline: {{ formatDate(job.application_deadline) }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>