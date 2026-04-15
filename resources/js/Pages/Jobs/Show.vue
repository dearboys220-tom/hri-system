<script setup>
import { Head, Link, usePage, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t } = useI18n()

const props = defineProps({
    job:            Object,
    company:        Object,
    categoryName:   String,
    subcategoryName: String,
    alreadyApplied: Boolean,
    isBookmarked:   Boolean,
})

const auth = usePage().props.auth

const applyForm = useForm({})
const apply = () => { applyForm.post(`/jobs/${props.job.id}/apply`) }

// ブックマーク
const bookmarked      = ref(props.isBookmarked)
const bookmarkLoading = ref(false)
async function toggleBookmark() {
    if (bookmarkLoading.value) return
    bookmarkLoading.value = true
    try {
        const res = await axios.post(`/applicant/bookmarks/${props.job.id}/toggle`)
        bookmarked.value = res.data.bookmarked
    } finally {
        bookmarkLoading.value = false
    }
}

const formatSalary = (val) => {
    if (!val) return null
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}

const salaryText = computed(() => {
    const min = formatSalary(props.job.salary_min)
    const max = formatSalary(props.job.salary_max)
    if (min && max) return `${min} – ${max}`
    if (min) return `${min}+`
    return t('job_list.negotiable')
})

const daysLeft = computed(() => {
    if (!props.job.application_deadline) return null
    return Math.ceil((new Date(props.job.application_deadline) - new Date()) / 86400000)
})

const deadlineLabel = computed(() => {
    if (daysLeft.value === null) return '-'
    if (daysLeft.value < 0)  return t('job_list.closed')
    if (daysLeft.value === 0) return t('job_list.today')
    return t('job_list.days_left', { n: daysLeft.value })
})

const deadlineColor = computed(() => {
    if (daysLeft.value === null || daysLeft.value < 0) return 'text-red-500'
    if (daysLeft.value <= 3) return 'text-orange-500 font-bold'
    return 'text-green-600'
})

const employmentColor = (type) => {
    const map = {
        'Full-time':  'bg-blue-100 text-blue-700',
        'Part-time':  'bg-purple-100 text-purple-700',
        'Contract':   'bg-orange-100 text-orange-700',
        'Freelance':  'bg-teal-100 text-teal-700',
        'Internship': 'bg-pink-100 text-pink-700',
    }
    return map[type] ?? 'bg-gray-100 text-gray-700'
}

const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    const d = new Date(dateStr)
    return `${String(d.getUTCDate()).padStart(2,'0')}-${String(d.getUTCMonth()+1).padStart(2,'0')}-${d.getUTCFullYear()}`
}
</script>

<template>
    <Head :title="job.title + ' — HRI'" />

    <div class="min-h-screen bg-gray-50">

        <!-- ナビバー -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/"><img src="/images/logo.png" class="h-8 w-auto" alt="HRI" /></Link>
                <div class="flex items-center gap-3">
                    <Link href="/jobs" class="text-sm text-gray-600 hover:text-gray-900">
                        ← {{ t('nav.search_job') }}
                    </Link>
                    <LanguageSwitcher :dark="false" />
                    <template v-if="!auth?.user">
                        <Link href="/login"
                              class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                            {{ t('nav.login') }}
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <div class="max-w-5xl mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 左：メインコンテンツ -->
            <div class="lg:col-span-2 space-y-5">

                <!-- ヘッダーカード -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden
                                    flex items-center justify-center flex-shrink-0">
                            <img v-if="company?.company_logo"
                                 :src="`/storage/${company.company_logo}`"
                                 class="w-full h-full object-cover" />
                            <span v-else class="text-2xl font-bold text-indigo-600">
                                {{ company?.company_name?.charAt(0)?.toUpperCase() ?? '?' }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ job.title }}</h1>
                            <p class="text-gray-500 mt-1">{{ company?.company_name ?? '-' }}</p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <span :class="['text-xs px-3 py-1 rounded-full font-medium',
                                               employmentColor(job.employment_type)]">
                                    {{ job.employment_type }}
                                </span>
                                <span v-if="categoryName"
                                      class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-600">
                                    {{ categoryName }}
                                </span>
                                <span v-if="subcategoryName"
                                      class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-600">
                                    {{ subcategoryName }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 概要グリッド -->
                    <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">💰 Gaji</p>
                            <p class="text-sm font-semibold text-gray-800">{{ salaryText }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">📍 Lokasi</p>
                            <p class="text-sm font-semibold text-gray-800">{{ job.location }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">⏰ {{ t('job_list.deadline') }}</p>
                            <p class="text-sm font-semibold" :class="deadlineColor">{{ deadlineLabel }}</p>
                        </div>
                        <div v-if="job.education_requirement" class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">🎓 Pendidikan</p>
                            <p class="text-sm font-semibold text-gray-800">{{ job.education_requirement }}</p>
                        </div>
                        <div v-if="job.experience_level" class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">💼 Pengalaman</p>
                            <p class="text-sm font-semibold text-gray-800">{{ job.experience_level }}</p>
                        </div>
                        <div v-if="job.gender" class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">👤 Gender</p>
                            <p class="text-sm font-semibold text-gray-800">{{ job.gender }}</p>
                        </div>
                    </div>
                </div>

                <!-- 求人詳細 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-800 mb-3">📋 Deskripsi Pekerjaan</h2>
                    <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ job.job_description }}</div>
                </div>

                <div v-if="job.required_skills" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-800 mb-3">✅ Keahlian yang Dibutuhkan</h2>
                    <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ job.required_skills }}</div>
                </div>

                <div v-if="job.preferred_skills" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-800 mb-3">⭐ Keahlian Tambahan (Diutamakan)</h2>
                    <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ job.preferred_skills }}</div>
                </div>

                <div v-if="job.special_requirements" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-800 mb-3">📌 Persyaratan Khusus</h2>
                    <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ job.special_requirements }}</div>
                </div>

                <!-- 勤務条件 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-800 mb-4">🏢 Kondisi Kerja</h2>
                    <div class="space-y-3 text-sm">
                        <div v-if="job.working_hours"
                             class="flex justify-between border-b border-gray-50 pb-2">
                            <span class="text-gray-500">Jam Kerja</span>
                            <span class="text-gray-800 font-medium">{{ job.working_hours }}</span>
                        </div>
                        <div v-if="job.working_days?.length"
                             class="flex justify-between border-b border-gray-50 pb-2">
                            <span class="text-gray-500">Hari Kerja</span>
                            <span class="text-gray-800 font-medium">{{ job.working_days.join(', ') }}</span>
                        </div>
                        <div v-if="job.language_requirements?.length"
                             class="flex justify-between border-b border-gray-50 pb-2">
                            <span class="text-gray-500">Bahasa</span>
                            <span class="text-gray-800 font-medium">{{ job.language_requirements.join(', ') }}</span>
                        </div>
                        <div v-if="job.age_min || job.age_max"
                             class="flex justify-between border-b border-gray-50 pb-2">
                            <span class="text-gray-500">Usia</span>
                            <span class="text-gray-800 font-medium">
                                <template v-if="job.age_min && job.age_max">{{ job.age_min }}–{{ job.age_max }} tahun</template>
                                <template v-else-if="job.age_min">Min. {{ job.age_min }} tahun</template>
                                <template v-else>Maks. {{ job.age_max }} tahun</template>
                            </span>
                        </div>
                        <div v-if="job.marital_status" class="flex justify-between">
                            <span class="text-gray-500">Status Pernikahan</span>
                            <span class="text-gray-800 font-medium">{{ job.marital_status }}</span>
                        </div>
                    </div>
                </div>

                <!-- 職場写真 -->
                <div v-if="job.workplace_photo"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h2 class="font-bold text-gray-800">📷 Foto Tempat Kerja</h2>
                    </div>
                    <img :src="`/storage/${job.workplace_photo}`" class="w-full object-cover max-h-96" />
                </div>

            </div>

            <!-- 右：サイドバー -->
            <div class="space-y-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:sticky lg:top-20">
                    <div class="text-center mb-4">
                        <p class="text-2xl font-bold text-gray-900">{{ salaryText }}</p>
                        <p class="text-xs text-gray-400 mt-1">per bulan</p>
                    </div>

                    <!-- ブックマーク（applicantのみ） -->
                    <template v-if="auth?.user?.role_type === 'applicant'">
                        <button @click="toggleBookmark" :disabled="bookmarkLoading"
                                :class="bookmarked
                                    ? 'w-full mb-3 flex items-center justify-center gap-2 border border-yellow-400 bg-yellow-50 text-yellow-600 font-medium py-2.5 rounded-xl transition text-sm'
                                    : 'w-full mb-3 flex items-center justify-center gap-2 border border-gray-200 hover:border-yellow-400 bg-white hover:bg-yellow-50 text-gray-500 hover:text-yellow-600 font-medium py-2.5 rounded-xl transition text-sm'">
                            <span>🔖</span>
                            <span>{{ bookmarked ? 'Tersimpan' : 'Simpan Lowongan' }}</span>
                        </button>
                    </template>

                    <!-- 応募ボタン（applicant） -->
                    <template v-if="auth?.user?.role_type === 'applicant'">
                        <div v-if="$page.props.flash?.success"
                             class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-xl p-3 mb-3 text-center">
                            {{ $page.props.flash.success }}
                        </div>
                        <div v-if="$page.props.flash?.error"
                             class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl p-3 mb-3 text-center">
                            {{ $page.props.flash.error }}
                        </div>
                        <div v-if="alreadyApplied"
                             class="w-full text-center bg-green-50 border border-green-200 text-green-700
                                    font-bold py-3 rounded-xl text-sm">
                            ✅ Sudah Melamar
                        </div>
                        <button v-else @click="apply" :disabled="applyForm.processing"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300
                                       text-white font-bold py-3 rounded-xl transition text-sm">
                            {{ applyForm.processing ? 'Memproses...' : '🚀 ' + t('job_list.btn_apply') }}
                        </button>
                        <p class="text-xs text-center text-gray-400 mt-2">Pastikan CV kamu sudah lengkap</p>
                    </template>

                    <!-- 未ログイン -->
                    <template v-else-if="!auth?.user">
                        <Link href="/login"
                              class="block w-full text-center bg-indigo-600 hover:bg-indigo-700
                                     text-white font-bold py-3 rounded-xl transition text-sm">
                            {{ t('job_list.btn_login_apply') }}
                        </Link>
                        <p class="text-xs text-center text-gray-400 mt-2">
                            Belum punya akun?
                            <Link href="/login" class="text-indigo-600 underline">Daftar gratis</Link>
                        </p>
                    </template>

                    <!-- 企業・スタッフ -->
                    <template v-else>
                        <div class="text-center text-sm text-gray-400 py-2">
                            Akun ini tidak bisa melamar lowongan
                        </div>
                    </template>

                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-xs text-gray-500">
                        <div class="flex items-center gap-2">
                            <span>📅</span>
                            <span>{{ t('job_list.deadline') }}: {{ formatDate(job.application_deadline) }}</span>
                        </div>
                        <div v-if="job.start_date" class="flex items-center gap-2">
                            <span>🗓️</span>
                            <span>Mulai Kerja: {{ formatDate(job.start_date) }}</span>
                        </div>
                    </div>
                </div>

                <!-- 企業情報 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 mb-3">🏢 Tentang Perusahaan</h3>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 min-w-[48px] overflow-hidden rounded-xl border border-indigo-100
                                    bg-indigo-50 flex items-center justify-center flex-shrink-0">
                            <img v-if="company?.company_logo"
                                 :src="`/storage/${company.company_logo}`"
                                 class="w-full h-full object-cover" />
                            <span v-else class="text-lg font-bold text-indigo-600">
                                {{ company?.company_name?.charAt(0)?.toUpperCase() ?? '?' }}
                            </span>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-gray-800">{{ company?.company_name ?? '-' }}</p>
                            <p v-if="company?.industry_type" class="text-xs text-gray-400">{{ company.industry_type }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-xs text-gray-600">
                        <div v-if="company?.company_size" class="flex items-center gap-2">
                            <span>👥</span><span>{{ company.company_size }}</span>
                        </div>
                        <div v-if="company?.company_address" class="flex items-start gap-2">
                            <span>📍</span><span>{{ company.company_address }}</span>
                        </div>
                        <div v-if="company?.company_website" class="flex items-center gap-2">
                            <span>🌐</span>
                            <a :href="company.company_website" target="_blank"
                               class="text-indigo-600 hover:underline truncate">
                                {{ company.company_website }}
                            </a>
                        </div>
                    </div>
                    <div v-if="company?.company_description" class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-600 leading-relaxed line-clamp-4">{{ company.company_description }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>