<script setup>
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    applicant:      { type: Object, required: true },
    education:      { type: Array,  default: () => [] },
    work:           { type: Array,  default: () => [] },
    certifications: { type: Array,  default: () => [] },
    isPurchased:    { type: Boolean, default: false },
})

// 日付はControllerで d/m/Y に変換済みなのでそのまま表示
function displayDate(d) {
    return d || '-'
}

function scoreColor(score) {
    if (score >= 80) return 'text-green-600'
    if (score >= 60) return 'text-yellow-600'
    return 'text-red-600'
}

function goToScore() {
    router.visit('/company/score/' + props.applicant.member_id)
}
</script>

<template>
    <Head :title="'Profil — ' + applicant.full_name" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

            <!-- 戻るボタン -->
            <button @click="router.visit('/company/dashboard')"
                class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                ← Kembali ke Dashboard
            </button>

            <!-- プロフィールカード -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-start gap-5 flex-wrap">

                    <!-- 写真 -->
                    <div class="w-20 h-20 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center border border-gray-200 flex-shrink-0">
                        <img v-if="applicant.profile_photo"
                            :src="`/storage/${applicant.profile_photo}`"
                            class="w-full h-full object-cover" />
                        <span v-else class="text-3xl">👤</span>
                    </div>

                    <!-- 基本情報 -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-xl font-bold text-gray-800">{{ applicant.full_name }}</h1>
                            <span :class="[
                                'text-xs px-2 py-0.5 rounded-full font-medium',
                                applicant.cert_status === 'Terverifikasi'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-500'
                            ]">
                                {{ applicant.cert_status === 'Terverifikasi' ? '✅ Terverifikasi' : applicant.cert_status ?? 'Belum Terverifikasi' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-400 mt-1 font-mono">{{ applicant.member_id }}</p>

                        <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                            <div>
                                <span class="text-gray-400">Jenis Kelamin: </span>
                                <span class="text-gray-700">{{ applicant.gender ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Kewarganegaraan: </span>
                                <span class="text-gray-700">{{ applicant.nationality ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Tgl Lahir: </span>
                                <span class="text-gray-700">{{ displayDate(applicant.birth_date) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">No. HP: </span>
                                <span class="text-gray-700">{{ applicant.phone_number ?? '-' }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-400">Alamat: </span>
                                <span class="text-gray-700">{{ applicant.current_address ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- スコア表示 -->
                    <div v-if="applicant.hri_score" class="text-center shrink-0">
                        <p class="text-xs text-gray-400 mb-1">Skor HRI</p>
                        <p :class="['text-4xl font-black', scoreColor(applicant.hri_score)]">
                            {{ applicant.hri_score }}
                        </p>
                        <p class="text-xs text-gray-400">/ 100</p>
                        <p v-if="applicant.cert_expiry" class="text-xs text-gray-400 mt-1">
                            s/d {{ displayDate(applicant.cert_expiry) }}
                        </p>
                    </div>
                </div>

                <!-- 自己PR -->
                <div v-if="applicant.self_pr" class="mt-5 pt-5 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-1">Tentang Saya</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ applicant.self_pr }}</p>
                </div>
            </div>

            <!-- 学歴 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">🎓 Riwayat Pendidikan</h2>
                </div>
                <div v-if="education.length === 0" class="px-6 py-6 text-center text-gray-400 text-sm">
                    Belum ada data pendidikan.
                </div>
                <div v-else class="divide-y divide-gray-50">
                    <div v-for="edu in education" :key="edu.id" class="px-6 py-4">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <p class="font-semibold text-gray-800">{{ edu.school_name }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ edu.education_level }}
                                    <span v-if="edu.degree_name"> — {{ edu.degree_name }}</span>
                                </p>
                                <p v-if="edu.school_location" class="text-xs text-gray-400 mt-0.5">
                                    📍 {{ edu.school_location }}
                                </p>
                                <p v-if="edu.graduation_status" class="text-xs text-gray-400 mt-0.5">
                                    Status: {{ edu.graduation_status }}
                                </p>
                                <p v-if="edu.ipk_gpa" class="text-xs text-gray-400 mt-0.5">
                                    IPK: {{ edu.ipk_gpa }}
                                </p>
                            </div>
                            <div class="text-right text-sm text-gray-400 shrink-0">
                                <p>{{ displayDate(edu.enrollment_date) }}</p>
                                <p>{{ edu.graduation_date ? displayDate(edu.graduation_date) : 'Sekarang' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 職歴 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">💼 Riwayat Pekerjaan</h2>
                </div>
                <div v-if="work.length === 0" class="px-6 py-6 text-center text-gray-400 text-sm">
                    Belum ada data pekerjaan.
                </div>
                <div v-else class="divide-y divide-gray-50">
                    <div v-for="job in work" :key="job.id" class="px-6 py-4">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <p class="font-semibold text-gray-800">{{ job.department_position }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">{{ job.company_name }}</p>
                                <p v-if="job.company_address" class="text-xs text-gray-400 mt-0.5">
                                    📍 {{ job.company_address }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ job.employment_type }}</p>
                            </div>
                            <div class="text-right text-sm text-gray-400 shrink-0">
                                <p>{{ displayDate(job.employment_start_date) }}</p>
                                <p>{{ job.employment_end_date ? displayDate(job.employment_end_date) : 'Sekarang' }}</p>
                            </div>
                        </div>
                        <p v-if="job.job_description" class="text-sm text-gray-500 mt-2 leading-relaxed">
                            {{ job.job_description }}
                        </p>
                        <p v-if="job.employment_achievements" class="text-xs text-gray-400 mt-1">
                            🏆 {{ job.employment_achievements }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 資格 -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">📜 Sertifikat &amp; Keahlian</h2>
                </div>
                <div v-if="certifications.length === 0" class="px-6 py-6 text-center text-gray-400 text-sm">
                    Belum ada data sertifikat.
                </div>
                <div v-else class="divide-y divide-gray-50">
                    <div v-for="cert in certifications" :key="cert.id" class="px-6 py-4 flex justify-between items-start gap-4">
                        <div>
                            <p class="font-semibold text-gray-800">{{ cert.certificate_name }}</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ cert.issuing_organization }}</p>
                            <p v-if="cert.certificate_score" class="text-xs text-gray-400 mt-0.5">
                                Skor: {{ cert.certificate_score }}
                            </p>
                        </div>
                        <div class="text-right text-sm text-gray-400 shrink-0">
                            <p>{{ displayDate(cert.issue_date) }}</p>
                            <p v-if="cert.expiration_date">s/d {{ displayDate(cert.expiration_date) }}</p>
                            <p v-else class="text-xs">Seumur hidup</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- スコア詳細購入ボタン -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div v-if="isPurchased" class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <p class="font-semibold text-gray-800">Detail Skor Sudah Dibeli</p>
                        <p class="text-sm text-gray-400 mt-1">Anda dapat melihat laporan detail skor kandidat ini.</p>
                    </div>
                    <button @click="goToScore"
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl text-sm transition shadow-sm">
                        📊 Lihat Detail Skor
                    </button>
                </div>
                <div v-else class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <p class="font-semibold text-gray-800">Ingin melihat detail penilaian?</p>
                        <p class="text-sm text-gray-400 mt-1">Lihat rincian skor per kategori dan hasil investigasi — <strong>Rp 50.000</strong></p>
                    </div>
                    <button @click="goToScore"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition shadow-sm">
                        📊 Lihat Detail Skor — Rp 50.000
                    </button>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>