<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t } = useI18n()

const props = defineProps({
    jobs:              Object,
    filters:           Object,
    categoriesGrouped: Array,
})

const auth = usePage().props.auth

// 検索フォーム
const keyword      = ref(props.filters?.query    ?? '')
const selectedCat  = ref(props.filters?.category ?? '')
const selectedLoc  = ref(props.filters?.location ?? '')
const selectedType = ref(props.filters?.type     ?? '')

const employmentTypes = [
    { value: 'Full-time',  label: 'Full Time' },
    { value: 'Part-time',  label: 'Part Time' },
    { value: 'Contract',   label: 'Kontrak' },
    { value: 'Freelance',  label: 'Freelance' },
    { value: 'Internship', label: 'Magang' },
]

const indonesianCities = [
    'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Bekasi',
    'Tangerang', 'Depok', 'Semarang', 'Palembang', 'Makassar',
    'Bogor', 'Batam', 'Pekanbaru', 'Bali / Denpasar', 'Yogyakarta',
]

const doSearch = () => {
    router.get('/jobs', {
        q:        keyword.value,
        category: selectedCat.value,
        location: selectedLoc.value,
        type:     selectedType.value,
    }, { preserveScroll: false })
}

const clearFilters = () => {
    keyword.value      = ''
    selectedCat.value  = ''
    selectedLoc.value  = ''
    selectedType.value = ''
    router.get('/jobs')
}

const hasFilter = computed(() =>
    keyword.value || selectedCat.value || selectedLoc.value || selectedType.value
)

// 選択中カテゴリ名
const selectedCatName = computed(() => {
    if (!selectedCat.value) return ''
    for (const parent of (props.categoriesGrouped ?? [])) {
        if (String(parent.id) === String(selectedCat.value)) return parent.name
        for (const child of parent.children) {
            if (String(child.id) === String(selectedCat.value)) return child.name
        }
    }
    return ''
})

// ユーティリティ
const formatSalary = (min, max) => {
    const fmt = (v) => 'Rp ' + Number(v).toLocaleString('id-ID')
    if (min && max) return `${fmt(min)} – ${fmt(max)}`
    if (min) return `${fmt(min)}+`
    return t('job_list.negotiable')
}

const daysLeft = (deadline) => {
    if (!deadline) return null
    return Math.ceil((new Date(deadline) - new Date()) / 86400000)
}

const deadlineLabel = (deadline) => {
    const d = daysLeft(deadline)
    if (d === null) return '-'
    if (d < 0)   return t('job_list.closed')
    if (d === 0) return t('job_list.today')
    return t('job_list.days_left', { n: d })
}

const deadlineColor = (deadline) => {
    const d = daysLeft(deadline)
    if (d === null || d < 0) return 'text-red-400'
    if (d <= 3) return 'text-orange-500 font-bold'
    return 'text-green-600'
}

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
</script>

<template>
    <Head title="Cari Lowongan — HRI" />

    <div class="min-h-screen bg-gray-50">

        <!-- ナビバー -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/">
                    <img src="/images/logo.png" class="h-8 w-auto" alt="HRI" />
                </Link>
                <div class="flex items-center gap-3">
                    <LanguageSwitcher :dark="false" />
                    <template v-if="!auth?.user">
                        <Link href="/login"
                              class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                            {{ t('nav.login') }}
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/applicant/dashboard"
                              v-if="auth.user.role_type === 'applicant'"
                              class="text-sm text-indigo-600 hover:underline">
                            {{ t('nav.my_page') }}
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

        <!-- ヒーロー＋検索バー -->
        <div class="text-white py-10 px-4"
             style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #0ea5e9 100%);">
            <div class="max-w-5xl mx-auto text-center">
                <h1 class="text-2xl font-bold mb-2">{{ t('nav.search_job') }}</h1>
                <p class="text-blue-200 text-sm mb-6">{{ t('job_hero.desc') }}</p>

                <!-- 検索フォーム -->
                <div class="bg-white rounded-2xl p-4 max-w-4xl mx-auto shadow-xl">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">

                        <!-- キーワード -->
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input v-model="keyword" type="text"
                                   :placeholder="t('job_hero.search_keyword')"
                                   class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm
                                          text-gray-800 focus:outline-none focus:border-blue-400"
                                   @keyup.enter="doSearch" />
                        </div>

                        <!-- カテゴリ（階層） -->
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6h16M4 12h8m-8 6h16"/>
                                </svg>
                            </div>
                            <select v-model="selectedCat"
                                    class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm
                                           text-gray-800 focus:outline-none focus:border-blue-400
                                           appearance-none bg-white">
                                <option value="">{{ t('job_hero.search_category') }}</option>
                                <template v-for="parent in categoriesGrouped" :key="parent.id">
                                    <optgroup :label="parent.name">
                                        <option v-for="child in parent.children"
                                                :key="child.id" :value="child.id">
                                            {{ child.name }}
                                        </option>
                                    </optgroup>
                                </template>
                            </select>
                        </div>

                        <!-- 地域 -->
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                            </div>
                            <select v-model="selectedLoc"
                                    class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm
                                           text-gray-800 focus:outline-none focus:border-blue-400
                                           appearance-none bg-white">
                                <option value="">{{ t('job_hero.search_location') }}</option>
                                <option v-for="city in indonesianCities" :key="city" :value="city">
                                    {{ city }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button @click="doSearch"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold
                                       py-2.5 rounded-xl text-sm transition">
                            🔍 {{ t('job_hero.btn_search') }}
                        </button>
                        <button v-if="hasFilter" @click="clearFilters"
                                class="px-4 py-2.5 border border-gray-200 text-gray-500
                                       hover:bg-gray-50 rounded-xl text-sm transition">
                            {{ t('job_hero.btn_reset') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-8">

            <!-- 雇用形態フィルター -->
            <div class="flex flex-wrap gap-2 mb-5">
                <button @click="selectedType = ''; doSearch()"
                        :class="['text-xs px-4 py-2 rounded-full border transition',
                                 selectedType === ''
                                     ? 'bg-blue-600 text-white border-blue-600'
                                     : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400']">
                    Semua
                </button>
                <button v-for="tp in employmentTypes" :key="tp.value"
                        @click="selectedType = tp.value; doSearch()"
                        :class="['text-xs px-4 py-2 rounded-full border transition',
                                 selectedType === tp.value
                                     ? 'bg-blue-600 text-white border-blue-600'
                                     : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400']">
                    {{ tp.label }}
                </button>
            </div>

            <!-- 結果ヘッダー -->
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-500">
                    <span class="font-semibold text-gray-800">{{ jobs.total }}</span>
                    {{ t('job_list.found', { n: jobs.total }).replace(jobs.total + ' ', '') }}
                    <template v-if="selectedCatName">
                        — <span class="text-blue-600 font-medium">{{ selectedCatName }}</span>
                    </template>
                </p>
                <button v-if="hasFilter" @click="clearFilters"
                        class="text-xs text-gray-400 hover:text-red-500 transition">
                    × Hapus semua filter
                </button>
            </div>

            <!-- 求人なし -->
            <div v-if="jobs.data.length === 0" class="text-center py-20 text-gray-400">
                <p class="text-4xl mb-3">🔍</p>
                <p class="font-medium text-gray-600">{{ t('job_list.empty_title') }}</p>
                <p class="text-sm mt-1">{{ t('job_list.empty_desc') }}</p>
                <button @click="clearFilters"
                        class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-full text-sm hover:bg-blue-700 transition">
                    {{ t('job_list.btn_all') }}
                </button>
            </div>

            <!-- 求人カード -->
            <div class="space-y-4">
                <Link v-for="job in jobs.data" :key="job.id"
                      :href="`/jobs/${job.id}`"
                      class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5
                             hover:border-blue-300 hover:shadow-md transition group">

                    <div class="flex items-start gap-4">
                        <!-- 企業ロゴ -->
                        <div class="w-12 h-12 min-w-[48px] overflow-hidden rounded-xl border border-gray-100
                                    bg-indigo-50 flex items-center justify-center flex-shrink-0">
                            <img v-if="job.company_logo"
                                 :src="`/storage/${job.company_logo}`"
                                 class="w-12 h-12 object-cover" />
                            <span v-else class="text-xl font-bold text-indigo-500">
                                {{ (job.company_name ?? '?').charAt(0).toUpperCase() }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h2 class="font-bold text-gray-900 group-hover:text-blue-600 transition truncate">
                                {{ job.title }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-0.5">{{ job.company_name ?? '-' }}</p>

                            <div class="flex flex-wrap gap-2 mt-2">
                                <span v-if="job.employment_type"
                                      :class="['text-xs px-2.5 py-1 rounded-full font-medium',
                                               employmentColor(job.employment_type)]">
                                    {{ job.employment_type }}
                                </span>
                                <span v-if="job.location"
                                      class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                                    📍 {{ job.location }}
                                </span>
                                <span v-if="job.category_name"
                                      class="text-xs px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">
                                    {{ job.category_name }}
                                </span>
                                <span class="text-xs px-2.5 py-1 rounded-full bg-green-50 text-green-700">
                                    💰 {{ formatSalary(job.salary_min, job.salary_max) }}
                                </span>
                            </div>
                        </div>

                        <!-- 締切 -->
                        <div class="text-right flex-shrink-0 hidden sm:block">
                            <span class="text-xs" :class="deadlineColor(job.application_deadline)">
                                ⏰ {{ deadlineLabel(job.application_deadline) }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- ページネーション -->
            <div v-if="jobs.last_page > 1" class="flex justify-center gap-2 mt-10">
                <template v-for="link in jobs.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url"
                          :class="['px-4 py-2 rounded-xl text-sm font-medium transition',
                                   link.active
                                       ? 'bg-blue-600 text-white'
                                       : 'bg-white border border-gray-200 text-gray-700 hover:bg-blue-50']"
                          v-html="link.label" />
                    <span v-else class="px-4 py-2 rounded-xl text-sm text-gray-300" v-html="link.label" />
                </template>
            </div>

        </div>
    </div>
</template>