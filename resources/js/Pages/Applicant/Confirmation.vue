<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    profile: Object,
    educations: Array,
    works: Array,
    certs: Array,
    isFreeAvailable: Boolean,
    daysRemaining: Number,
    price: Number,
    isAlreadySubmitted: Boolean,
});

const confirmed = ref(false);

const form = useForm({});

const submit = () => {
    if (!confirmed.value) return;
    form.post(route('applicant.confirmation.store'));
};
</script>

<template>
    <Head title="Konfirmasi CV" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Konfirmasi CV</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- タイトル -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                    <p class="text-3xl mb-2">📋</p>
                    <h1 class="text-xl font-bold text-gray-800">Konfirmasi CV Lengkap</h1>
                    <p class="text-sm text-gray-500 mt-1">Silakan periksa semua informasi CV Anda sebelum mengajukan sertifikasi</p>
                </div>

                <!-- 無料バナー -->
                <div v-if="isFreeAvailable" class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-4 flex items-center gap-3 text-white">
                    <span class="text-2xl">🎁</span>
                    <div>
                        <p class="font-bold">Sertifikasi GRATIS!</p>
                        <p class="text-sm opacity-90">Berakhir dalam <strong>{{ daysRemaining }} hari</strong></p>
                    </div>
                    <span class="ml-auto font-bold text-lg">Rp 0</span>
                </div>
                <div v-else class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 flex items-center gap-3">
                    <span class="text-2xl">💳</span>
                    <div>
                        <p class="font-semibold text-indigo-800">Biaya Sertifikasi</p>
                        <p class="text-sm text-indigo-600">Pembayaran akan diproses setelah konfirmasi</p>
                    </div>
                    <span class="ml-auto font-bold text-indigo-700 text-lg">Rp 35.000</span>
                </div>

                <!-- 基本情報 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-indigo-600">👤</span> Informasi Dasar
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Nama Lengkap</p>
                            <p class="text-sm font-medium text-gray-800 mt-1">{{ profile?.full_name ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">NIK</p>
                            <p class="text-sm font-medium text-gray-800 mt-1 font-mono">{{ profile?.nik ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Jenis Kelamin</p>
                            <p class="text-sm font-medium text-gray-800 mt-1">{{ profile?.gender ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Tanggal Lahir</p>
                            <p class="text-sm font-medium text-gray-800 mt-1">{{ profile?.birth_date ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Nomor Telepon</p>
                            <p class="text-sm font-medium text-gray-800 mt-1">{{ profile?.phone_number ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Status Pernikahan</p>
                            <p class="text-sm font-medium text-gray-800 mt-1">{{ profile?.marital_status ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <!-- 学歴 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-indigo-600">🎓</span> Pendidikan
                        <span class="ml-auto text-xs text-gray-400">{{ educations.length }} data</span>
                    </h3>
                    <div v-if="educations.length === 0" class="text-sm text-gray-400 text-center py-4">
                        ✗ Belum diisi
                    </div>
                    <div v-for="edu in educations" :key="edu.id" class="bg-gray-50 rounded-xl p-3 mb-2">
                        <p class="font-semibold text-sm text-gray-800">{{ edu.school }}</p>
                        <p class="text-xs text-gray-500">{{ edu.level }}{{ edu.major ? ' — ' + edu.major : '' }}</p>
                        <p class="text-xs text-gray-400">{{ edu.enrollment_date ?? '?' }} — {{ edu.graduation_date ?? 'Sekarang' }}</p>
                    </div>
                </div>

                <!-- 職歴 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-indigo-600">💼</span> Pengalaman Kerja
                        <span class="ml-auto text-xs text-gray-400">{{ works.length }} data</span>
                    </h3>
                    <div v-if="works.length === 0" class="text-sm text-gray-400 text-center py-4">
                        ✗ Belum diisi
                    </div>
                    <div v-for="work in works" :key="work.id" class="bg-gray-50 rounded-xl p-3 mb-2">
                        <p class="font-semibold text-sm text-gray-800">{{ work.company }}</p>
                        <p class="text-xs text-gray-500">{{ work.position }} — {{ work.employment_type }}</p>
                        <p class="text-xs text-gray-400">{{ work.start_date }} — {{ work.end_date ?? 'Sekarang' }}</p>
                    </div>
                </div>

                <!-- 資格 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-indigo-600">📜</span> Sertifikasi
                        <span class="ml-auto text-xs text-gray-400">{{ certs.length }} data</span>
                    </h3>
                    <div v-if="certs.length === 0" class="text-sm text-gray-400 text-center py-4">
                        ✗ Belum diisi
                    </div>
                    <div v-for="cert in certs" :key="cert.id" class="bg-gray-50 rounded-xl p-3 mb-2">
                        <p class="font-semibold text-sm text-gray-800">{{ cert.name }}</p>
                        <p class="text-xs text-gray-500">{{ cert.organization }}</p>
                        <p class="text-xs text-gray-400">{{ cert.issued_date }}</p>
                    </div>
                </div>

                <!-- 重要事項 -->
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                    <h3 class="font-bold text-red-700 mb-3 flex items-center gap-2">
                        ⚠️ Hal Penting Sebelum Mengajukan
                    </h3>
                    <ul class="space-y-2 text-sm text-red-700">
                        <li>• Setelah mengajukan sertifikasi, Anda <strong>tidak dapat mengajukan lagi selama 3 bulan</strong></li>
                        <li>• Pastikan semua informasi yang diisi adalah <strong>benar dan akurat</strong></li>
                        <li>• Data yang salah akan mempengaruhi <strong>penilaian sertifikasi Anda</strong></li>
                        <li>• Proses verifikasi memakan waktu <strong>3-5 hari kerja</strong></li>
                    </ul>
                </div>

                <!-- 最終確認 -->
                <div v-if="!isAlreadySubmitted" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 text-center">✅ Konfirmasi Final</h3>
                    <label class="flex items-start gap-3 cursor-pointer bg-gray-50 rounded-xl p-4">
                        <input
                            type="checkbox"
                            v-model="confirmed"
                            class="mt-1 w-5 h-5 text-indigo-600 rounded cursor-pointer"
                        />
                        <span class="text-sm text-gray-700">
                            Saya telah memeriksa semua informasi CV dan memastikan data yang diisi adalah benar
                        </span>
                    </label>

                    <div class="flex gap-3 mt-6">
                        <Link
                            href="/applicant/cv"
                            class="flex-1 text-center border border-gray-300 text-gray-600 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition"
                        >
                            ✗ Kembali ke CV
                        </Link>
                        <button
                            @click="submit"
                            :disabled="!confirmed || form.processing"
                            class="flex-1 py-3 rounded-xl text-white text-sm font-semibold transition"
                            :style="confirmed ? 'background-color: #16a34a;' : 'background-color: #d1d5db; cursor: not-allowed;'"
                        >
                            {{ form.processing ? 'Memproses...' : '✓ Kirim Permintaan Sertifikasi' }}
                        </button>
                    </div>
                </div>

                <!-- 申請済みの場合 -->
                <div v-else class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-center">
                    <p class="text-blue-700 font-semibold text-lg">✅ Pengajuan sedang diproses</p>
                    <p class="text-sm text-blue-500 mt-1">Tim HRI sedang memverifikasi data Anda.</p>
                    <Link
                        href="/applicant/dashboard"
                        class="mt-4 inline-block text-sm text-indigo-600 hover:underline"
                    >
                        ← Kembali ke Dashboard
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>