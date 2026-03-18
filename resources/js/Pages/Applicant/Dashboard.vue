<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';

const props = defineProps({
    profile: Object,
    daysRemaining: Number,
    isFreeAvailable: Boolean,
    latestRequest: Object,
    applicationCount: Number,
});

const user = usePage().props.auth.user;

const statusConfig = {
    Terverifikasi:       { label: 'Terverifikasi ✓', color: 'text-green-700 bg-green-100 border-green-300' },
    pending_payment:     { label: 'Menunggu Pembayaran', color: 'text-yellow-700 bg-yellow-100 border-yellow-300' },
    under_investigation: { label: 'Sedang Investigasi', color: 'text-blue-700 bg-blue-100 border-blue-300' },
    under_review:        { label: 'Sedang Direview', color: 'text-purple-700 bg-purple-100 border-purple-300' },
    pending_admin:       { label: 'Menunggu Persetujuan', color: 'text-orange-700 bg-orange-100 border-orange-300' },
    Ditolak:             { label: 'Ditolak', color: 'text-red-700 bg-red-100 border-red-300' },
    'Perlu Koreksi':     { label: 'Perlu Koreksi', color: 'text-red-700 bg-red-100 border-red-300' },
};

const getStatus = (status) =>
    statusConfig[status] ?? { label: 'Belum Mengajukan', color: 'text-gray-500 bg-gray-100 border-gray-300' };

// ステップ完了チェック（CV→本人確認→認証申請）
const step1Done = () => !!props.profile?.full_name && !!props.profile?.phone_number;
const step2Done = () => !!props.profile?.nik && !!props.profile?.ktp_card;
const step3Done = () => !!props.latestRequest;
const step4Done = () => props.latestRequest?.survey_status === 'Terverifikasi';
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- プロフィールカード -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-600 shrink-0 overflow-hidden">
                        <img
                            v-if="profile?.profile_photo"
                            :src="`/storage/${profile.profile_photo}`"
                            class="w-full h-full object-cover"
                        />
                        <span v-else>{{ (profile?.full_name || user.name).charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 truncate">{{ profile?.full_name || user.name }}</h2>
                        <p class="text-sm text-gray-400 truncate">{{ user.email }}</p>
                        <p v-if="profile?.member_id" class="text-xs text-gray-400 mt-1">
                            ID: <span class="font-mono font-semibold text-gray-600">{{ profile.member_id }}</span>
                        </p>
                    </div>
                    <span
                        class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold border"
                        :class="getStatus(latestRequest?.survey_status).color"
                    >
                        {{ getStatus(latestRequest?.survey_status).label }}
                    </span>
                </div>

                <!-- 無料バナー -->
                <div
                    v-if="isFreeAvailable"
                    class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-5 flex items-center gap-4 text-white shadow"
                >
                    <span class="text-3xl">🎁</span>
                    <div class="flex-1">
                        <p class="font-bold text-lg">Sertifikasi GRATIS!</p>
                        <p class="text-sm opacity-90">Berakhir dalam <strong>{{ daysRemaining }} hari</strong>. Ajukan sekarang sebelum masa gratis habis.</p>
                    </div>
                    <Link
                        href="/applicant/certification/apply"
                        class="shrink-0 bg-white text-emerald-700 font-semibold text-sm px-5 py-2 rounded-xl hover:bg-emerald-50 transition"
                    >
                        Ajukan
                    </Link>
                </div>

                <!-- ステップガイド -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 text-base mb-6">Langkah Sertifikasi HRI</h3>

                    <div class="space-y-4">

                        <!-- STEP 1：CV入力 -->
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 flex flex-col items-center">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold"
                                    :class="step1Done() ? 'bg-green-500 text-white' : 'bg-indigo-600 text-white'"
                                >
                                    {{ step1Done() ? '✓' : '1' }}
                                </div>
                                <div class="w-0.5 h-8 bg-gray-200 mt-1"></div>
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-gray-800 text-sm">Isi Formulir CV</p>
                                    <span v-if="step1Done() && step3Done()" class="text-xs text-green-600 font-medium">Selesai ✓</span>
                                    <Link v-else-if="step1Done()" href="/applicant/cv" class="text-xs text-green-600 font-medium hover:underline">
                                        Selesai ✓ (Edit)
                                    </Link>
                                    <Link v-else href="/applicant/cv" class="text-xs text-indigo-600 font-medium hover:underline">
                                        Isi CV →
                                    </Link>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Lengkapi data diri, pendidikan, pengalaman, dan sertifikasi</p>
                            </div>
                        </div>

                        <!-- STEP 2：本人確認 -->
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 flex flex-col items-center">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold"
                                    :class="step2Done() ? 'bg-green-500 text-white' : step1Done() ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400'"
                                >
                                    {{ step2Done() ? '✓' : '2' }}
                                </div>
                                <div class="w-0.5 h-8 bg-gray-200 mt-1"></div>
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-sm" :class="step1Done() ? 'text-gray-800' : 'text-gray-400'">
                                        Verifikasi Identitas
                                    </p>
                                    <span v-if="step2Done() && step3Done()" class="text-xs text-green-600 font-medium">Selesai ✓</span>
                                    <Link v-else-if="step2Done()" href="/applicant/identity" class="text-xs text-green-600 font-medium hover:underline">
                                        Selesai ✓ (Edit)
                                    </Link>
                                    <Link v-else-if="step1Done()" href="/applicant/identity" class="text-xs text-indigo-600 font-medium hover:underline">
                                        Lengkapi →
                                    </Link>
                                    <span v-else class="text-xs text-gray-300">Belum bisa diakses</span>
                                </div>
                                <p class="text-xs mt-1" :class="step1Done() ? 'text-gray-400' : 'text-gray-300'">
                                    Upload foto KTP dan isi NIK Anda
                                </p>
                            </div>
                        </div>

                        <!-- STEP 3：認証申請 -->
                        <div class="flex items-start gap-4">
                            <div class="shrink-0">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold"
                                    :class="step4Done() ? 'bg-green-500 text-white' : step3Done() ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400'"
                                >
                                    {{ step4Done() ? '✓' : '3' }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-sm" :class="step2Done() ? 'text-gray-800' : 'text-gray-400'">
                                        Ajukan Sertifikasi HRI
                                    </p>
                                    <span v-if="step4Done()" class="text-xs text-green-600 font-semibold">Terverifikasi ✓</span>
                                    <span v-else-if="step3Done() && latestRequest" class="text-xs font-medium px-2 py-0.5 rounded-full border" :class="getStatus(latestRequest.survey_status).color">
                                        {{ getStatus(latestRequest.survey_status).label }}
                                    </span>
                                    <Link
                                        v-else-if="step2Done()"
                                        href="/applicant/certification/apply"
                                        class="text-xs text-indigo-600 font-medium hover:underline"
                                    >
                                        {{ isFreeAvailable ? 'Ajukan (GRATIS) →' : 'Ajukan →' }}
                                    </Link>
                                    <span v-else class="text-xs text-gray-300">Belum bisa diakses</span>
                                </div>
                                <p class="text-xs mt-1" :class="step2Done() ? 'text-gray-400' : 'text-gray-300'">
                                    Proses investigasi dan verifikasi oleh tim HRI
                                </p>
                                <Link
                                    v-if="step3Done()"
                                    href="/applicant/confirmation"
                                    class="text-xs text-indigo-500 hover:underline mt-1 inline-block"
                                >
                                    Lihat CV saya →
                                </Link>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 下部2カード -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <!-- 求人応募 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-800">Riwayat Lamaran</h3>
                            <Link href="/applicant/applications" class="text-xs text-indigo-600 hover:underline">Lihat semua →</Link>
                        </div>
                        <div class="flex items-end gap-2">
                            <p class="text-4xl font-bold text-indigo-600">{{ applicationCount }}</p>
                            <p class="text-sm text-gray-400 mb-1">lamaran diajukan</p>
                        </div>
                    </div>

                    <!-- 求人検索 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-2">Cari Lowongan</h3>
                        <p class="text-sm text-gray-400 mb-4">Temukan pekerjaan yang sesuai dengan keahlian Anda.</p>
                        <Link
                            href="/jobs"
                            class="block text-center bg-indigo-600 text-white text-sm px-4 py-2 rounded-xl hover:bg-indigo-700 transition"
                        >
                            Lihat Lowongan
                        </Link>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>