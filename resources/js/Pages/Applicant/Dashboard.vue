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

// 認証ステータスの表示設定
const statusConfig = {
    Terverifikasi: { label: 'Terverifikasi', color: 'bg-green-100 text-green-800' },
    pending_payment: { label: 'Menunggu Pembayaran', color: 'bg-yellow-100 text-yellow-800' },
    under_investigation: { label: 'Sedang Investigasi', color: 'bg-blue-100 text-blue-800' },
    under_review: { label: 'Sedang Direview', color: 'bg-purple-100 text-purple-800' },
    pending_admin: { label: 'Menunggu Persetujuan', color: 'bg-orange-100 text-orange-800' },
    Ditolak: { label: 'Ditolak', color: 'bg-red-100 text-red-800' },
    'Perlu Koreksi': { label: 'Perlu Koreksi', color: 'bg-red-100 text-red-800' },
};

const getStatus = (status) =>
    statusConfig[status] ?? { label: status ?? 'Belum Mengajukan', color: 'bg-gray-100 text-gray-600' };
</script>

<template>
    <Head title="Dashboard Member" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Member
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- 無料期間バナー -->
                <div
                    v-if="isFreeAvailable"
                    class="bg-green-50 border border-green-300 rounded-lg p-4 flex items-center gap-3"
                >
                    <span class="text-2xl">🎁</span>
                    <div>
                        <p class="font-semibold text-green-800">
                            Sertifikasi GRATIS tersedia!
                        </p>
                        <p class="text-sm text-green-700">
                            Masa gratis berakhir dalam <strong>{{ daysRemaining }} hari</strong>.
                            Segera ajukan sertifikasi sebelum masa gratis habis.
                        </p>
                    </div>
                </div>

                <!-- 無料期間終了バナー -->
                <div
                    v-else-if="profile && !profile.free_certification_used && daysRemaining === 0"
                    class="bg-gray-50 border border-gray-300 rounded-lg p-4 flex items-center gap-3"
                >
                    <span class="text-2xl">⏰</span>
                    <p class="text-sm text-gray-600">
                        Masa sertifikasi gratis telah berakhir. Biaya sertifikasi: <strong>Rp 35.000</strong>.
                    </p>
                </div>

                <!-- プロフィールカード -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center gap-4">
                        <!-- アバター -->
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-600">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <!-- 情報 -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ user.name }}</h3>
                            <p class="text-sm text-gray-500">{{ user.email }}</p>
                            <p v-if="profile?.member_id" class="text-xs text-gray-400 mt-1">
                                ID Anggota: <span class="font-mono font-semibold">{{ profile.member_id }}</span>
                            </p>
                        </div>
                        <!-- 認証ステータス -->
                        <div class="ml-auto">
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium"
                                :class="getStatus(profile?.certification_status).color"
                            >
                                {{ getStatus(profile?.certification_status).label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ステータスカード一覧 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <!-- 認証申請カード -->
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-gray-500 mb-3">Status Sertifikasi HRI</h4>

                        <div v-if="latestRequest">
                            <span
                                class="inline-block px-3 py-1 rounded-full text-sm font-medium mb-3"
                                :class="getStatus(latestRequest.survey_status).color"
                            >
                                {{ getStatus(latestRequest.survey_status).label }}
                            </span>
                            <p class="text-xs text-gray-400">
                                Diajukan: {{ new Date(latestRequest.created_at).toLocaleDateString('id-ID') }}
                            </p>
                        </div>

                        <div v-else>
                            <p class="text-sm text-gray-500 mb-4">Belum ada pengajuan sertifikasi.</p>
                        </div>

                        <!-- 申請ボタン -->
                        <div class="mt-4">
                            <Link
                                v-if="!latestRequest || ['Terverifikasi','Ditolak'].includes(latestRequest?.survey_status)"
                                href="/applicant/certification/apply"
                                class="inline-block bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700 transition"
                            >
                                {{ isFreeAvailable ? 'Ajukan Sertifikasi (GRATIS)' : 'Ajukan Sertifikasi' }}
                            </Link>
                            <span v-else class="text-xs text-gray-400">
                                Pengajuan sedang diproses.
                            </span>
                        </div>
                    </div>

                    <!-- 求人応募カード -->
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-gray-500 mb-3">Lamaran Pekerjaan</h4>
                        <p class="text-3xl font-bold text-indigo-600">{{ applicationCount }}</p>
                        <p class="text-sm text-gray-500 mt-1">Total lamaran yang diajukan</p>
                        <div class="mt-4">
                            <Link
                                href="/applicant/applications"
                                class="text-sm text-indigo-600 hover:underline"
                            >
                                Lihat riwayat lamaran →
                            </Link>
                        </div>
                    </div>

                </div>

                <!-- KTP未登録の警告 -->
                <div
                    v-if="profile && !profile.nik"
                    class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 flex items-center gap-3"
                >
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <p class="font-semibold text-yellow-800">Verifikasi identitas belum dilengkapi</p>
                        <p class="text-sm text-yellow-700">
                            Lengkapi NIK dan foto KTP untuk mengajukan sertifikasi.
                            <Link href="/applicant/identity" class="underline font-medium">Lengkapi sekarang →</Link>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>