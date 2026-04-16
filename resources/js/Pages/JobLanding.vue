<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t } = useI18n()

const props = defineProps({
    jobs:              Object,
    filters:           Object,
    categoriesGrouped: Array,
    totalJobs:         Number,
})

const auth       = usePage().props.auth
const isLoggedIn = !!auth?.user
const roleType   = auth?.user?.role_type

const getDashboardUrl = () => {
    if (roleType === 'applicant') return '/applicant/dashboard'
    if (roleType === 'company')   return '/company/dashboard'
    if (['admin_user', 'investigator_user'].includes(roleType)) return '/admin/dashboard'
    return '/dashboard'
}

const scrolled     = ref(false)
const mobileOpen   = ref(false)
const handleScroll = () => { scrolled.value = window.scrollY > 50 }
onMounted(()  => window.addEventListener('scroll', handleScroll))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))

const searchQuery    = ref('')
const searchCategory = ref('')
const searchLocation = ref('')

const indonesianCities = [
    'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Bekasi',
    'Tangerang', 'Depok', 'Semarang', 'Palembang', 'Makassar',
    'Bogor', 'Batam', 'Pekanbaru', 'Bali / Denpasar', 'Yogyakarta',
]

const doSearch = () => {
    router.get('/jobs', {
        q:        searchQuery.value,
        category: searchCategory.value,
        location: searchLocation.value,
    })
}

const hasFilter = computed(() =>
    searchQuery.value || searchCategory.value || searchLocation.value
)
</script>

<template>
    <Head title="HRI Job — Cari Lowongan Kerja Terverifikasi" />

    <!-- ===== NAVBAR ===== -->
    <nav :class="['fixed top-0 inset-x-0 z-50 transition-all duration-300',
                  scrolled ? 'bg-white shadow-md' : 'bg-transparent']">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
            <Link href="/" class="flex items-center gap-3" aria-label="HRI Home">
                <img src="/images/logo.png" alt="HRI" :class="['h-10 w-auto rounded-lg transition-all', scrolled ? '' : 'bg-white/90 p-1' ]"/>
                <span class="hidden sm:block text-xs font-semibold tracking-[0.18em] uppercase transition" :class="scrolled ? 'text-slate-700' : 'text-white/90'" >
                    Human Reliability Intelligence
                </span>
            </Link>

            <div class="hidden md:flex items-center gap-5">
                <Link href="/job"
                      :class="['text-sm font-semibold transition',
                               scrolled ? 'text-blue-600' : 'text-white']">
                    {{ t('nav.search_job') }}
                </Link>
                <Link href="/"
                      :class="['text-sm font-medium transition',
                               scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    {{ t('nav.for_individual') }}
                </Link>
                <Link href="/company"
                      :class="['text-sm font-medium transition',
                               scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    {{ t('nav.for_company') }}
                </Link>

                <div :class="scrolled ? '[&_button]:!text-gray-700 [&_button]:!border-gray-300 [&_button:hover]:!bg-gray-50' : ''">
                    <LanguageSwitcher />
                </div>

                <template v-if="isLoggedIn">
                    <Link :href="getDashboardUrl()"
                          class="ml-1 px-4 py-2 rounded-full text-sm font-semibold transition"
                          :class="scrolled ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-white text-blue-700 hover:bg-blue-50'">
                        {{ t('nav.my_page') }}
                    </Link>
                </template>
                <template v-else>
                    <Link href="/login"
                          class="ml-1 px-4 py-2 rounded-full text-sm font-semibold transition border"
                          :class="scrolled ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-white/40 text-white hover:bg-white/10'">
                        {{ t('nav.login') }}
                    </Link>
                    <Link href="/register/company"
                          class="px-4 py-2 rounded-full text-sm font-semibold transition"
                          :class="scrolled ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-white text-blue-700 hover:bg-blue-50'">
                        {{ t('nav.register_company') }}
                    </Link>
                </template>
            </div>

            <button class="md:hidden p-2 rounded"
                    :class="scrolled ? 'text-gray-700' : 'text-white'"
                    @click="mobileOpen = !mobileOpen">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div v-if="mobileOpen" class="md:hidden bg-white border-t px-4 py-4 space-y-3 shadow-lg">
            <Link href="/job"      class="block text-blue-600 font-semibold py-2">{{ t('nav.search_job') }}</Link>
            <Link href="/"         class="block text-gray-700 font-medium py-2">{{ t('nav.for_individual') }}</Link>
            <Link href="/company"  class="block text-gray-700 font-medium py-2">{{ t('nav.for_company') }}</Link>
            <div class="py-2"><LanguageSwitcher :dark="false" /></div>
            <template v-if="isLoggedIn">
                <Link :href="getDashboardUrl()"
                      class="block w-full text-center bg-blue-600 text-white rounded-full py-2 font-semibold">
                    {{ t('nav.my_page') }}
                </Link>
            </template>
            <template v-else>
                <Link href="/login"
                      class="block w-full text-center border border-gray-300 text-gray-700 rounded-full py-2 font-semibold">
                    {{ t('nav.login') }}
                </Link>
                <Link href="/register/company"
                      class="block w-full text-center bg-blue-600 text-white rounded-full py-2 font-semibold">
                    {{ t('nav.register_company') }}
                </Link>
            </template>
        </div>
    </nav>

    <!-- ===== HERO + 検索 ===== -->
    <section class="relative pt-32 pb-20 px-4 overflow-hidden"
             style="background: linear-gradient(135deg, #065f52 0%, #0f766e 60%, #14b8a6 100%);">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 30% 50%, white 1px, transparent 1px);
                    background-size: 50px 50px;"></div>
        <div class="absolute -bottom-1 left-0 right-0 h-20 bg-gray-50"
             style="clip-path: ellipse(55% 100% at 50% 100%)"></div>

        <div class="relative max-w-4xl mx-auto text-center mb-10">
            <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20
                        rounded-full px-4 py-1.5 text-sm text-blue-100 font-medium mb-5">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                {{ t('job_hero.badge') }}
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-white leading-tight mb-4">
                {{ t('job_hero.title1') }}<br>
                <span class="text-yellow-300">{{ t('job_hero.title2') }}</span>
            </h1>
            <p class="text-blue-100 text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
                {{ t('job_hero.desc') }}
            </p>
        </div>

        <!-- 検索ボックス -->
        <div class="relative max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl p-5 md:p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">

                    <!-- キーワード -->
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input v-model="searchQuery" type="text"
                               :placeholder="t('job_hero.search_keyword')"
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm
                                      focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                               @keyup.enter="doSearch" />
                    </div>

                    <!-- カテゴリ -->
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6h16M4 12h8m-8 6h16"/>
                            </svg>
                        </div>
                        <select v-model="searchCategory"
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm
                                       focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100
                                       appearance-none bg-white">
                            <option value="">{{ t('job_hero.search_category') }}</option>
                            <template v-for="parent in categoriesGrouped" :key="parent.id">
                                <optgroup :label="parent.name">
                                    <option v-for="child in parent.children" :key="child.id" :value="child.id">
                                        {{ child.name }}
                                    </option>
                                </optgroup>
                            </template>
                        </select>
                    </div>

                    <!-- 地域 -->
                    <div class="relative">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <select v-model="searchLocation"
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm
                                       focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100
                                       appearance-none bg-white">
                            <option value="">{{ t('job_hero.search_location') }}</option>
                            <option v-for="city in indonesianCities" :key="city" :value="city">{{ city }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button @click="doSearch"
                            class="flex-1 text-white font-bold py-3 rounded-xl transition-all text-sm"
                            style="background-color: #0F766E;"
                            @mouseover="$event.target.style.backgroundColor='#0d6b63'"
                            @mouseleave="$event.target.style.backgroundColor='#0F766E'">
                        🔍 {{ t('job_hero.btn_search') }}
                    </button>
                    <button v-if="hasFilter"
                            @click="searchQuery=''; searchCategory=''; searchLocation='';"
                            class="px-4 py-3 border border-gray-200 text-gray-500
                                   hover:bg-gray-50 rounded-xl text-sm transition">
                        {{ t('job_hero.btn_reset') }}
                    </button>
                </div>
                <p class="text-center text-gray-400 text-xs mt-3">{{ t('job_hero.note') }}</p>
            </div>
        </div>
    </section>

    <!-- ===== 特徴 ===== -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('job_why.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">{{ t('job_why.title') }}</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div v-for="f in [
                    { icon: '✅', title: t('features.f1_title'), desc: t('features.f1_desc') },
                    { icon: '🏅', title: t('features.f2_title'), desc: t('features.f2_desc') },
                    { icon: '⚡', title: t('features.f3_title'), desc: t('features.f3_desc') },
                ]" :key="f.title"
                     class="hri-feature-card group bg-white rounded-2xl p-8 transition-all duration-300
                            hover:shadow-xl hover:-translate-y-1 cursor-default border border-gray-100">
                    <div class="text-4xl mb-4">{{ f.icon }}</div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-white mb-3 transition-colors">{{ f.title }}</h3>
                    <p class="text-gray-500 group-hover:text-blue-100 text-sm leading-relaxed transition-colors">{{ f.desc }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== カテゴリ一覧 ===== -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('job_category.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">{{ t('job_category.title') }}</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <button v-for="parent in categoriesGrouped" :key="parent.id"
                        @click="router.get('/jobs', { category: parent.id })"
                        class="hri-cat-btn bg-gray-50 border border-gray-100 rounded-xl p-4 text-center
                               transition-all hover:shadow-md hover:-translate-y-0.5 group cursor-pointer">
                    <div class="cat-name text-sm font-semibold leading-snug">
                        {{ parent.name }}
                    </div>
                    <div class="cat-count text-xs mt-1">
                        {{ parent.children?.length ?? 0 }} {{ t('job_category.unit') }}
                    </div>
                </button>
            </div>
            <div class="text-center mt-10">
                <Link href="/jobs"
                      class="inline-block text-white font-bold px-10 py-4 rounded-full transition shadow-lg"
                      style="background-color: #0F766E;"
                      @mouseover="$event.target.style.backgroundColor='#0d6b63'"
                      @mouseleave="$event.target.style.backgroundColor='#0F766E'">
                    {{ t('job_list.btn_all') }} →
                </Link>
            </div>
        </div>
    </section>

    <!-- ===== 企業向けCTA ===== -->
    <section class="py-16 bg-gray-50 border-t border-gray-100">
        <div class="max-w-5xl mx-auto px-4 grid md:grid-cols-2 gap-10 items-center">
            <div>
                <span class="inline-block bg-orange-100 text-orange-700 text-xs font-bold
                             px-3 py-1 rounded-full mb-4 border border-orange-200">
                    {{ t('company_section.badge') }}
                </span>
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 mb-4">{{ t('company_section.title') }}</h2>
                <p class="text-gray-500 mb-6 text-sm leading-relaxed">{{ t('company_section.desc') }}</p>
                <Link href="/company"
                      class="inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-full transition">
                    {{ t('company_section.cta') }}
                </Link>
            </div>
            <div class="relative rounded-2xl p-8 text-center overflow-hidden min-h-[200px] flex flex-col items-center justify-center"
                 style="background-image: url('/images/job-company-bg.png'); background-size: cover; background-position: center;">
                <div class="absolute inset-0 bg-black/10 rounded-2xl"></div>
                <div class="relative z-10 bg-black/60 rounded-2xl px-8 py-6 text-center m-6">
                    <p class="text-4xl font-extrabold text-white mb-2">{{ t('company_section.days') }}</p>
                    <p class="text-white font-semibold mb-1">{{ t('company_section.days_label') }}</p>
                    <p class="text-white/80 text-sm">{{ t('company_section.days_note') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== 個人向けCTA ===== -->
    <section class="py-20" style="background: linear-gradient(135deg, #065f52 0%, #0f766e 60%, #14b8a6 100%);">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-6">{{ t('cta.title') }}</h2>
            <p class="text-blue-100 mb-10 leading-relaxed">{{ t('cta.desc') }}</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <template v-if="isLoggedIn">
                    <Link :href="getDashboardUrl()"
                          class="px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        {{ t('cta.btn_mypage') }}
                    </Link>
                </template>
                <template v-else>
                    <Link href="/login"
                          class="px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        👤 {{ t('cta.btn_register') }}
                    </Link>
                </template>
                <Link href="/jobs"
                      class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold
                             rounded-full text-lg border border-white/30 transition-all">
                    🔎 {{ t('job_list.btn_all') }}
                </Link>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-start gap-8">
                <div>
                    <Link href="/"><img src="/images/logo.png" alt="HRI" class="h-8 w-auto mb-3 opacity-80" /></Link>
                    <p class="text-sm max-w-xs leading-relaxed">{{ t('footer.desc') }}</p>
                </div>
                <div class="flex gap-12">
                    <div>
                        <div class="text-white font-semibold mb-3 text-sm">{{ t('footer.nav_title') }}</div>
                        <Link href="/about" class="block hover:text-white transition">{{ t('footer.about') }}</Link>
                        <div class="space-y-2 text-sm">
                            <Link href="/"        class="block hover:text-white transition">{{ t('nav.for_individual') }}</Link>
                            <Link href="/company" class="block hover:text-white transition">{{ t('nav.for_company') }}</Link>
                            <Link href="/jobs"    class="block hover:text-white transition">{{ t('nav.search_job') }}</Link>
                            <Link href="/login"   class="block hover:text-white transition">{{ t('nav.login') }}</Link>
                        </div>
                    </div>
                    <div>
                        <div class="text-white font-semibold mb-3 text-sm">{{ t('footer.legal_title') }}</div>
                        <div class="space-y-2 text-sm">
                            <a href="https://hri-check.com/privacy-applicant/" target="_blank"
                               class="block hover:text-white transition">{{ t('footer.privacy') }}</a>
                            <a href="https://hri-check.com/important-policies/" target="_blank"
                               class="block hover:text-white transition">{{ t('footer.policy') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-10 pt-6 border-t border-gray-800 text-xs text-center">
                © 2026 PT. NIKI KINDAICHI THERR INDONESIA. HRI (Human Reliability Intelligence). Privacy, consent, and audit-log controls applied. All rights reserved.
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* カテゴリボタン */
.hri-cat-btn .cat-name  { color: #374151; } /* gray-700 */
.hri-cat-btn .cat-count { color: #9ca3af; } /* gray-400 */

.hri-cat-btn:hover {
    background-color: #0F766E;
}
.hri-cat-btn:hover .cat-name  { color: #ffffff; }
.hri-cat-btn:hover .cat-count { color: #ccfbf1; } /* teal-100 */

/* フィーチャーカード */
.hri-feature-card:hover {
    background-color: #0F766E;
}
</style>