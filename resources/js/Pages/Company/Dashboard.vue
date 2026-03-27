<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const user = usePage().props.auth.user;

const props = defineProps({
    profile: Object,
    freePostDaysLeft: Number,
    isFreePostAvailable: Boolean,
    jobStats: Object,
    totalApplications: Number,
});

const verificationConfig = {
    pending:   { label: 'Menunggu Verifikasi', color: 'bg-yellow-100 text-yellow-800', icon: '⏳' },
    verified:  { label: 'Terverifikasi',        color: 'bg-green-100 text-green-700',  icon: '✅' },
    suspended: { label: 'Ditangguhkan',         color: 'bg-red-100 text-red-700',      icon: '🚫' },
    rejected:  { label: 'Ditolak',              color: 'bg-gray-100 text-gray-600',    icon: '❌' },
};

const verif = verificationConfig[props.profile?.company_verification_status] ?? verificationConfig.pending;

// 検索
const searchId    = ref('')
const searchError = ref('')
const searching   = ref(false)

function searchApplicant() {
    if (!searchId.value.trim()) {
        searchError.value = 'Masukkan nomor anggota terlebih dahulu.'
        return
    }
    searchError.value = ''
    searching.value   = true
    router.visit('/company/applicant/' + searchId.value.trim(), {
        onError: () => {
            searchError.value = 'Anggota tidak ditemukan.'
            searching.value   = false
        },
        onFinish: () => { searching.value = false },
    })
}
</script>

<template>
    <Head title="Dashboard Perusahaan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Perusahaan
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- ① ウェルカムカード -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center border border-gray-200 flex-shrink-0">
                            <img v-if="profile?.company_logo"
                                 :src="`/storage/${profile.company_logo}`"
                                 class="w-full h-full object-cover" />
                            <span v-else class="text-2xl">🏢</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Selamat datang kembali,</p>
                            <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ user.name }}</h1>
                            <p class="text-sm text-gray-500 mt-1">Member ID: <span class="font-mono font-semibold text-indigo-600">{{ profile?.member_id ?? '-' }}</span></p>
                        </div>
                    </div>
                    <div :class="['px-4 py-2 rounded-full text-sm font-semibold', verif.color]">
                        {{ verif.icon }} {{ verif.label }}
                    </div>
                </div>

                <!-- ★ 会員番号検索ボックス -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-700 mb-1">🔍 Cari Kandidat</h3>
                    <p class="text-sm text-gray-400 mb-4">Masukkan nomor anggota HRI untuk melihat profil kandidat.</p>
                    <div class="flex gap-3">
                        <input
                            v-model="searchId"
                            type="text"
                            placeholder="Contoh: HRIM-Y0TQXH9"
                            class="flex-1 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 font-mono"
                            @keyup.enter="searchApplicant"
                        />
                        <button
                            @click="searchApplicant"
                            :disabled="searching"
                            class="px-6 py-3 rounded-xl font-semibold text-sm text-white transition-all"
                            :class="searching
                                ? 'bg-gray-400 cursor-not-allowed'
                                : 'bg-indigo-600 hover:bg-indigo-700 shadow-sm'"
                        >
                            <span v-if="searching">...</span>
                            <span v-else>Cari</span>
                        </button>
                    </div>
                    <p v-if="searchError" class="text-sm text-red-500 mt-2">{{ searchError }}</p>
                </div>

                <!-- ② 未認証バナー -->
                <div v-if="profile?.company_verification_status === 'pending'" class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 flex gap-4 items-start">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <p class="font-semibold text-yellow-800">Akun perusahaan Anda sedang dalam proses verifikasi</p>
                        <p class="text-sm text-yellow-700 mt-1">Posting lowongan baru tersedia setelah akun diverifikasi oleh tim admin HRI. Proses biasanya memakan waktu 1–3 hari kerja.</p>
                    </div>
                </div>

                <!-- ③ 無料投稿バナー -->
                <div v-if="profile?.company_verification_status === 'verified' && isFreePostAvailable"
                     class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-5 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-lg">🎉 Posting Lowongan GRATIS tersedia!</p>
                        <p class="text-sm text-emerald-100 mt-1">Masa gratis berakhir dalam <strong>{{ freePostDaysLeft }} hari</strong>. Gunakan sebelum habis!</p>
                    </div>
                    <Link href="/company/jobs/create"
                          class="bg-white text-emerald-700 font-semibold px-5 py-2 rounded-xl text-sm hover:bg-emerald-50 transition whitespace-nowrap">
                        + Posting Sekarang (GRATIS)
                    </Link>
                </div>

                <!-- ④ 統計カード -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center">
                        <p class="text-3xl font-bold text-indigo-600">{{ jobStats.total }}</p>
                        <p class="text-sm text-gray-500 mt-1">Total Lowongan</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center">
                        <p class="text-3xl font-bold text-green-600">{{ jobStats.open }}</p>
                        <p class="text-sm text-gray-500 mt-1">Lowongan Aktif</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center col-span-2 sm:col-span-1">
                        <p class="text-3xl font-bold text-orange-500">{{ totalApplications }}</p>
                        <p class="text-sm text-gray-500 mt-1">Total Pelamar</p>
                    </div>
                </div>

                <!-- ⑤ クイックアクション -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Menu Utama</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <Link href="/company/jobs/create"
                              class="flex flex-col items-center gap-2 p-4 rounded-xl bg-indigo-50 hover:bg-indigo-100 transition text-indigo-700 text-sm font-medium text-center">
                            <span class="text-2xl">📝</span>
                            Posting Lowongan
                        </Link>
                        <Link href="/company/jobs"
                              class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition text-gray-700 text-sm font-medium text-center">
                            <span class="text-2xl">📋</span>
                            Kelola Lowongan
                        </Link>
                        <Link href="/company/applicants"
                              class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition text-gray-700 text-sm font-medium text-center">
                            <span class="text-2xl">👥</span>
                            Data Pelamar
                        </Link>
                        <Link href="/company/profile"
                              class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition text-gray-700 text-sm font-medium text-center">
                            <span class="text-2xl">🏢</span>
                            Profil Perusahaan
                        </Link>
                    </div>
                </div>

                <!-- ⑥ プロフィール情報 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-700 mb-4">Informasi Perusahaan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="flex flex-col gap-1">
                            <span class="text-gray-400">NIB</span>
                            <span class="font-medium text-gray-800">{{ profile?.nib ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-gray-400">Email Perusahaan</span>
                            <span class="font-medium text-gray-800">{{ profile?.company_email ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-gray-400">Penanggung Jawab</span>
                            <span class="font-medium text-gray-800">{{ profile?.pic_name ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-gray-400">No. HP PIC</span>
                            <span class="font-medium text-gray-800">{{ profile?.pic_phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>