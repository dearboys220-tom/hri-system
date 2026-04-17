<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'
import SharedFooter from '@/Components/SharedFooter.vue'

const auth       = usePage().props.auth
const isLoggedIn = !!auth?.user
const roleType   = auth?.user?.role_type

const getDashboardUrl = () => {
    if (roleType === 'applicant') return '/applicant/dashboard'
    if (roleType === 'company')   return '/company/dashboard'
    if (['admin_user', 'investigator_user'].includes(roleType)) return '/admin/dashboard'
    return '/dashboard'
}

const { t, locale } = useI18n()
const $t = t

const scrolled   = ref(false)
const mobileOpen = ref(false)
const showFab    = ref(false)
const showPopup  = ref(false)
const popupShown = ref(false)

let popupTimer = null

const handleScroll = () => {
    const y = window.scrollY
    scrolled.value = y > 20
    showFab.value = y > 520
    if (!isLoggedIn && !popupShown.value && y > 780) {
        showPopup.value = true
        popupShown.value = true
    }
}

const openPopup = () => {
    if (isLoggedIn || popupShown.value) return
    showPopup.value = true
    popupShown.value = true
}

const closePopup = () => { showPopup.value = false }

const canonicalUrl = computed(() => {
    if (typeof window === 'undefined') return '/'
    return window.location.origin + '/'
})

// ===== SEO =====
const pageTitle       = computed(() => t('welcome_seo.title'))
const pageDescription = computed(() => t('welcome_seo.description'))
const pageKeywords    = computed(() => t('welcome_seo.keywords'))
const ogImage         = computed(() => '/images/logo.png')

// ===== コンテンツ =====
const reasonCards = computed(() => [
    { icon: '🌏', title: t('reasons.r1_title'), desc: t('reasons.r1_desc') },
    { icon: '🛡️', title: t('reasons.r2_title'), desc: t('reasons.r2_desc') },
    { icon: '📄', title: t('reasons.r3_title'), desc: t('reasons.r3_desc') },
])

const verifiedItems = computed(() => [
    { label: t('verified.v1_label'), note: t('verified.v1_note') },
    { label: t('verified.v2_label'), note: t('verified.v2_note') },
    { label: t('verified.v3_label'), note: t('verified.v3_note') },
    { label: t('verified.v4_label'), note: t('verified.v4_note') },
])

const strengthCards = computed(() => [
    { icon: '🏢', title: t('strength.s1_title'), desc: t('strength.s1_desc') },
    { icon: '🔎', title: t('strength.s2_title'), desc: t('strength.s2_desc') },
    { icon: '🌐', title: t('strength.s3_title'), desc: t('strength.s3_desc') },
])

const trustCards = computed(() => [
    { title: t('trust.t1_title'), desc: t('trust.t1_desc') },
    { title: t('trust.t2_title'), desc: t('trust.t2_desc') },
    { title: t('trust.t3_title'), desc: t('trust.t3_desc') },
    { title: t('trust.t4_title'), desc: t('trust.t4_desc') },
])

const testimonials = computed(() => [
    { icon: '✈️',  name: 'Sarah W.', role: t('testimonials.role1'), text: t('testimonials.text1') },
    { icon: '👩‍🎓', name: 'Rina S.',  role: t('testimonials.role2'), text: t('testimonials.text2') },
    { icon: '👨‍💻', name: 'Andi P.',  role: t('testimonials.role3'), text: t('testimonials.text3') },
])

const faqItems = computed(() => [
    { q: t('welcome_faq.q1'), a: t('welcome_faq.a1') },
    { q: t('welcome_faq.q2'), a: t('welcome_faq.a2') },
    { q: t('welcome_faq.q3'), a: t('welcome_faq.a3') },
    { q: t('welcome_faq.q4'), a: t('welcome_faq.a4') },
    { q: t('welcome_faq.q5'), a: t('welcome_faq.a5') },
])

// ===== 構造化データ（Schema.org） =====
const organizationSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'HRI',
    alternateName: 'Human Reliability Intelligence',
    url: canonicalUrl.value,
    logo: `${typeof window !== 'undefined' ? window.location.origin : ''}/images/logo.png`,
    description: t('welcome_schema.org_description'),
    knowsAbout: [
        t('welcome_schema.k1'),
        t('welcome_schema.k2'),
        t('welcome_schema.k3'),
        t('welcome_schema.k4'),
        t('welcome_schema.k5'),
    ],
}))

const serviceSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Service',
    name: t('welcome_schema.service_name'),
    serviceType: t('welcome_schema.service_type'),
    provider: {
        '@type': 'Organization',
        name: 'HRI',
    },
    description: t('welcome_schema.service_description'),
    areaServed: [
        { '@type': 'Country', name: 'Indonesia' },
        { '@type': 'Country', name: 'Japan' },
    ],
    audience: {
        '@type': 'Audience',
        audienceType: t('welcome_schema.audience'),
    },
}))

const faqSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqItems.value.map((item) => ({
        '@type': 'Question',
        name: item.q,
        acceptedAnswer: {
            '@type': 'Answer',
            text: item.a,
        },
    })),
}))

const websiteSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'HRI',
    url: canonicalUrl.value,
    inLanguage: locale.value || 'id',
    description: t('welcome_schema.website_description'),
}))

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true })
    handleScroll()
    if (!isLoggedIn) {
        popupTimer = window.setTimeout(() => { openPopup() }, 9000)
    } 

    // ===== JSON-LD 構造化データを <head> に挿入 =====
    const schemas = [
        organizationSchema.value,
        serviceSchema.value,
        websiteSchema.value,
        faqSchema.value,
    ]
    schemas.forEach(schema => {
        const el = document.createElement('script')
        el.type = 'application/ld+json'
        el.text = JSON.stringify(schema)
        document.head.appendChild(el)
    })
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
    if (popupTimer) window.clearTimeout(popupTimer)
})
</script>

<template>
    <Head :title="pageTitle">
        <meta name="description" :content="pageDescription" />
        <meta name="keywords" :content="pageKeywords" />
        <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />
        <meta name="theme-color" content="#123e88" />
        <meta name="author" content="HRI" />
        <meta name="format-detection" content="telephone=no" />

        <link rel="canonical" :href="canonicalUrl" />
        <link rel="alternate" :href="canonicalUrl" :hreflang="locale || 'id'" />
        <link rel="alternate" :href="canonicalUrl" hreflang="x-default" />

        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="HRI" />
        <meta property="og:locale" content="id_ID" />
        <meta property="og:title" :content="pageTitle" />
        <meta property="og:description" :content="pageDescription" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="ogImage" />

        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="pageTitle" />
        <meta name="twitter:description" :content="pageDescription" />
        <meta name="twitter:image" :content="ogImage" />
    </Head>

    <div class="bg-white text-slate-900 selection:bg-blue-100 selection:text-blue-900">
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:text-blue-900 focus:shadow-lg"
        >
            {{ $t('a11y.skip') }}
        </a>

        <nav
            :class="[
                'fixed top-0 inset-x-0 z-50 transition-all duration-300',
                scrolled
                    ? 'bg-white/95 backdrop-blur shadow-md border-b border-slate-200'
                    : 'bg-gradient-to-b from-black/30 to-transparent'
            ]"
        >
            <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
                <Link href="/" class="flex items-center gap-3" aria-label="HRI Home">
                    <img src="/images/logo.png" alt="HRI" :class="['h-10 w-auto rounded-lg transition-all', scrolled ? '' : 'bg-white/90 p-1' ]"/>
                    <span
                        class="hidden sm:block text-xs font-semibold tracking-[0.18em] uppercase"
                        :class="scrolled ? 'text-slate-700' : 'text-white/90'"
                    >
                        Human Reliability Intelligence
                    </span>
                </Link>

                <div class="hidden md:flex items-center gap-5">
                    <Link
                        href="/job"
                        :class="[
                            'text-sm font-medium transition',
                            scrolled ? 'text-slate-700 hover:text-blue-700' : 'text-white hover:text-blue-200'
                        ]"
                    >
                        {{ $t('nav.search_job') }}
                    </Link>

                    <a
                        href="#features"
                        :class="[
                            'text-sm font-medium transition',
                            scrolled ? 'text-slate-700 hover:text-blue-700' : 'text-white hover:text-blue-200'
                        ]"
                    >
                        {{ $t('nav.service') }}
                    </a>

                    <a
                        href="#pricing"
                        :class="[
                            'text-sm font-medium transition',
                            scrolled ? 'text-slate-700 hover:text-blue-700' : 'text-white hover:text-blue-200'
                        ]"
                    >
                        {{ $t('nav.price') }}
                    </a>

                    <Link
                        href="/company"
                        :class="[
                            'text-sm font-medium transition',
                            scrolled ? 'text-slate-700 hover:text-blue-700' : 'text-white hover:text-blue-200'
                        ]"
                    >
                        {{ $t('nav.for_company') }}
                    </Link>

                    <div :class="scrolled ? '[&_button]:!text-slate-700 [&_button]:!border-slate-300 [&_button:hover]:!bg-slate-50' : ''">
                        <LanguageSwitcher />
                    </div>

                    <template v-if="isLoggedIn">
                        <Link
                            :href="getDashboardUrl()"
                            class="ml-1 px-4 py-2 rounded-full text-sm font-semibold transition"
                            :class="scrolled ? 'bg-blue-700 text-white hover:bg-blue-800' : 'bg-white text-blue-800 hover:bg-blue-50'"
                        >
                            {{ $t('nav.my_page') }}
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="ml-1 px-4 py-2 rounded-full text-sm font-semibold transition"
                            :class="scrolled ? 'bg-blue-700 text-white hover:bg-blue-800' : 'bg-white text-blue-800 hover:bg-blue-50'"
                        >
                            {{ $t('nav.login') }}
                        </Link>
                    </template>
                </div>

                <button
                    class="md:hidden p-2 rounded-xl transition"
                    :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'"
                    @click="mobileOpen = !mobileOpen"
                    :aria-label="$t('nav.mobile_menu')"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            v-if="!mobileOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="mobileOpen"
                    class="md:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-3 shadow-lg"
                >
                    <Link href="/job" class="block text-slate-700 font-medium py-2">{{ $t('nav.search_job') }}</Link>
                    <a href="#features" class="block text-slate-700 font-medium py-2" @click="mobileOpen = false">{{ $t('nav.service') }}</a>
                    <a href="#pricing" class="block text-slate-700 font-medium py-2" @click="mobileOpen = false">{{ $t('nav.price') }}</a>
                    <Link href="/company" class="block text-slate-700 font-medium py-2">{{ $t('nav.for_company') }}</Link>
                    <div class="py-2">
                        <LanguageSwitcher :dark="false" />
                    </div>

                    <template v-if="isLoggedIn">
                        <Link
                            :href="getDashboardUrl()"
                            class="block w-full text-center bg-blue-700 text-white rounded-2xl py-3 font-semibold"
                        >
                            {{ $t('nav.my_page') }}
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="block w-full text-center bg-blue-700 text-white rounded-2xl py-3 font-semibold"
                        >
                            {{ $t('nav.login') }}
                        </Link>
                    </template>
                </div>
            </transition>
        </nav>

        <main id="main-content">
            <section class="relative overflow-hidden bg-gradient-to-br from-[#0d3276] via-[#123e88] to-[#194d9a]">
                <div
                    class="absolute inset-0 opacity-[0.12]"
                    style="background-image:
                        radial-gradient(circle at 18% 20%, white 1px, transparent 1px),
                        radial-gradient(circle at 82% 12%, white 1px, transparent 1px),
                        linear-gradient(135deg, rgba(255,255,255,.06) 1px, transparent 1px),
                        linear-gradient(45deg, rgba(255,255,255,.04) 1px, transparent 1px);
                        background-size: 72px 72px, 72px 72px, 26px 26px, 26px 26px;"
                />
                <div class="absolute inset-x-0 bottom-0 h-16 sm:h-20 bg-white" style="clip-path: ellipse(58% 100% at 50% 100%)" />

                <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-16 sm:pt-32 sm:pb-20 lg:pt-36 lg:pb-24">
                    <div class="grid lg:grid-cols-[1.05fr_.95fr] gap-8 lg:gap-12 items-center">
                        <div class="text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 backdrop-blur px-4 py-2 text-xs sm:text-sm text-blue-100 font-medium mb-5">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                                {{ $t('hero.badge') }}
                            </div>

                            <h1 class="text-[2rem] sm:text-5xl lg:text-6xl font-black text-white leading-[1.05] tracking-tight">
                                {{ $t('hero.title1') }}
                                <span class="text-[#f3d569]">{{ $t('hero.title2') }}</span><br class="hidden sm:block" />
                                {{ $t('hero.title3') }}
                            </h1>

                            <p class="mt-5 text-base sm:text-lg lg:text-xl text-blue-100 max-w-2xl mx-auto lg:mx-0 leading-8">
                                {{ $t('hero.desc') }}
                            </p>

                            <div class="mt-7 grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl mx-auto lg:mx-0">
                                <template v-if="isLoggedIn">
                                    <Link
                                        :href="getDashboardUrl()"
                                        class="inline-flex items-center justify-center px-6 py-4 bg-[#f3d569] hover:bg-[#f8df84] text-[#103780] font-bold rounded-2xl text-base shadow-xl transition-all hover:scale-[1.02]"
                                    >
                                        {{ $t('hero.cta_mypage') }}
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link
                                        href="/login"
                                        class="inline-flex items-center justify-center px-6 py-4 bg-[#f3d569] hover:bg-[#f8df84] text-[#103780] font-bold rounded-2xl text-base shadow-xl transition-all hover:scale-[1.02]"
                                    >
                                        {{ $t('hero.cta_register') }}
                                    </Link>
                                </template>

                                <Link
                                    href="/job"
                                    class="inline-flex items-center justify-center px-6 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-2xl text-base border border-white/25 backdrop-blur transition-all"
                                >
                                    {{ $t('hero.cta_jobs') }}
                                </Link>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2.5 justify-center lg:justify-start">
                                <span class="bg-white/10 border border-white/20 text-blue-100 text-xs sm:text-sm px-4 py-2 rounded-full backdrop-blur">
                                    {{ $t('hero.point1') }}
                                </span>
                                <span class="bg-white/10 border border-white/20 text-blue-100 text-xs sm:text-sm px-4 py-2 rounded-full backdrop-blur">
                                    {{ $t('hero.point2') }}
                                </span>
                                <span class="bg-white/10 border border-white/20 text-blue-100 text-xs sm:text-sm px-4 py-2 rounded-full backdrop-blur">
                                    {{ $t('hero.point3') }}
                                </span>
                            </div>

                            <p class="text-blue-300 text-xs sm:text-sm mt-5 leading-6 max-w-2xl mx-auto lg:mx-0">
                                {{ $t('hero.note') }}
                            </p>
                        </div>

                        <div class="relative">
                            <div class="rounded-3xl border border-white/15 bg-white/10 backdrop-blur-xl p-5 sm:p-6 text-white shadow-2xl">
                                <div class="flex items-center gap-3 pb-5 border-b border-white/10">
                                    <div class="w-11 h-11 bg-white/15 rounded-2xl flex items-center justify-center font-bold text-sm">HRI</div>
                                    <div>
                                        <div class="font-bold text-sm">{{ $t('hero_card.title') }}</div>
                                        <div class="text-blue-200 text-xs">{{ $t('hero_card.sub') }}</div>
                                    </div>
                                    <div class="ml-auto bg-green-400 text-green-950 text-[11px] font-bold px-3 py-1 rounded-full">
                                        VERIFIED
                                    </div>
                                </div>

                                <div class="space-y-3 mt-5">
                                    <div
                                        v-for="item in verifiedItems"
                                        :key="item.label"
                                        class="flex items-center gap-3 bg-white/10 rounded-2xl px-4 py-3"
                                    >
                                        <div class="w-6 h-6 rounded-full bg-green-400 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">
                                            ✓
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium">{{ item.label }}</div>
                                            <div class="text-blue-200 text-xs">{{ item.note }}</div>
                                        </div>
                                        <span class="ml-auto text-green-300 text-[11px] sm:text-xs font-semibold whitespace-nowrap">
                                            Checked
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-5 rounded-2xl bg-[#f3d569]/15 p-4">
                                    <div class="text-[#f3d569] text-sm font-bold">{{ $t('hero_card.reason_title') }}</div>
                                    <p class="text-yellow-100 text-sm leading-6 mt-1">
                                        {{ $t('hero_card.reason_desc') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-14 sm:py-18 bg-white">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <div class="text-center mb-10 sm:mb-12">
                        <span class="text-[#123e88] font-semibold text-xs sm:text-sm uppercase tracking-[0.22em]">{{ $t('reasons.label') }}</span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-3 leading-tight">
                            {{ $t('reasons.title') }}
                        </h2>
                        <p class="text-slate-500 mt-4 max-w-2xl mx-auto text-sm sm:text-base leading-7">
                            {{ $t('reasons.desc') }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:gap-6 md:grid-cols-3">
                        <article
                            v-for="item in reasonCards"
                            :key="item.title"
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-6 sm:p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                        >
                            <div class="text-4xl">{{ item.icon }}</div>
                            <h3 class="mt-4 text-lg sm:text-xl font-bold text-slate-900">{{ item.title }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ item.desc }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="features" class="py-16 sm:py-20 bg-slate-50">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <div class="text-center mb-10 sm:mb-14">
                        <span class="text-[#123e88] font-semibold text-xs sm:text-sm uppercase tracking-[0.22em]">{{ $t('features.label') }}</span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-3 leading-tight">
                            {{ $t('features.title') }}
                        </h2>
                        <p class="text-slate-500 mt-4 max-w-2xl mx-auto text-sm sm:text-base leading-7">
                            {{ $t('features.desc') }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:gap-6 md:grid-cols-3">
                        <article
                            v-for="item in strengthCards"
                            :key="item.title"
                            class="group bg-white hover:bg-[#123e88] rounded-3xl p-6 sm:p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border border-slate-200"
                        >
                            <div class="text-4xl mb-4">{{ item.icon }}</div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-white mb-3 transition-colors">
                                {{ item.title }}
                            </h3>
                            <p class="text-slate-500 group-hover:text-blue-100 text-sm leading-7 transition-colors">
                                {{ item.desc }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="py-16 sm:py-20 bg-white">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                        <div>
                            <span class="text-[#123e88] font-semibold text-xs sm:text-sm uppercase tracking-[0.22em]">{{ $t('verified.label') }}</span>
                            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-3 mb-5 leading-tight">
                                {{ $t('verified.title') }}
                            </h2>
                            <p class="text-slate-600 leading-8 mb-7 text-sm sm:text-base">
                                {{ $t('verified.desc') }}
                            </p>

                            <ul class="space-y-4">
                                <li
                                    v-for="item in verifiedItems"
                                    :key="item.label"
                                    class="flex gap-3 items-start rounded-2xl bg-slate-50 border border-slate-200 p-4"
                                >
                                    <span class="text-xl flex-shrink-0 text-[#123e88]">✓</span>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ item.label }}</div>
                                        <div class="text-sm text-slate-600 leading-7">{{ item.note }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="grid gap-4 sm:gap-6">
                            <article
                                v-for="item in trustCards"
                                :key="item.title"
                                class="rounded-3xl border border-slate-200 bg-slate-50 p-6 sm:p-7 shadow-sm"
                            >
                                <h3 class="text-lg font-bold text-slate-900">{{ item.title }}</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ item.desc }}</p>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-16 sm:py-20 bg-slate-50">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <div class="text-center mb-10 sm:mb-14">
                        <span class="text-[#123e88] font-semibold text-xs sm:text-sm uppercase tracking-[0.22em]">{{ $t('testimonials.label') }}</span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-3 leading-tight">
                            {{ $t('testimonials.title') }}
                        </h2>
                    </div>

                    <div class="grid gap-4 sm:gap-6 md:grid-cols-3">
                        <article
                            v-for="t2 in testimonials"
                            :key="t2.name"
                            class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 hover:shadow-lg transition-all"
                        >
                            <div class="text-3xl mb-4">{{ t2.icon }}</div>
                            <p class="text-slate-700 text-sm leading-7 mb-6 italic">"{{ t2.text }}"</p>
                            <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-[#123e88] text-sm">
                                    {{ t2.name[0] }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ t2.name }}</div>
                                    <div class="text-slate-400 text-xs">{{ t2.role }}</div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section id="pricing" class="py-16 sm:py-20 bg-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6">
                    <div class="text-center mb-10 sm:mb-14">
                        <span class="text-[#123e88] font-semibold text-xs sm:text-sm uppercase tracking-[0.22em]">{{ $t('pricing.label') }}</span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-3 leading-tight">
                            {{ $t('pricing.title') }}
                        </h2>
                        <p class="text-slate-500 mt-4 text-sm sm:text-base leading-7">
                            {{ $t('pricing.desc') }}
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-[1.75rem] shadow-xl overflow-hidden border border-slate-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-6 sm:px-8 py-5 bg-blue-50 border-b border-slate-100">
                            <div class="pr-3">
                                <div class="font-semibold text-slate-900">{{ $t('pricing.p1_label') }}</div>
                                <div class="text-slate-400 text-xs mt-1 leading-5">{{ $t('pricing.p1_note') }}</div>
                            </div>
                            <div class="font-black text-lg sm:text-xl text-green-600">{{ $t('pricing.p1_price') }}</div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-6 sm:px-8 py-5 border-b border-slate-100 bg-white">
                            <div class="pr-3">
                                <div class="font-semibold text-slate-900">{{ $t('pricing.p2_label') }}</div>
                                <div class="text-slate-400 text-xs mt-1 leading-5">{{ $t('pricing.p2_note') }}</div>
                            </div>
                            <div class="font-black text-lg sm:text-xl text-[#123e88]">{{ $t('pricing.p2_price') }}</div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-6 sm:px-8 py-5 border-b border-slate-100 bg-white">
                            <div class="pr-3">
                                <div class="font-semibold text-slate-900">{{ $t('pricing.p3_label') }}</div>
                                <div class="text-slate-400 text-xs mt-1 leading-5">{{ $t('pricing.p3_note') }}</div>
                            </div>
                            <div class="font-black text-lg sm:text-xl text-slate-900">{{ $t('pricing.p3_price') }}</div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-6 sm:px-8 py-5 bg-white">
                            <div class="pr-3">
                                <div class="font-semibold text-slate-900">{{ $t('pricing.p4_label') }}</div>
                                <div class="text-slate-400 text-xs mt-1 leading-5">{{ $t('pricing.p4_note') }}</div>
                            </div>
                            <div class="font-black text-lg sm:text-xl text-slate-900">{{ $t('pricing.p4_price') }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-16 sm:py-20 bg-slate-50 border-t border-slate-100">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-10 items-center">
                    <div>
                        <span class="inline-block bg-orange-100 text-orange-700 text-xs font-bold px-3 py-1 rounded-full mb-4 border border-orange-200">
                            {{ $t('company.badge') }}
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4 leading-tight">
                            {{ $t('company.title') }}
                        </h2>
                        <p class="text-slate-500 mb-6 leading-8 text-sm sm:text-base">
                            {{ $t('company.desc') }}
                        </p>
                        <Link
                            href="/company"
                            class="inline-flex items-center justify-center bg-orange-500 hover:bg-orange-600 text-white font-bold px-7 py-3 rounded-2xl transition"
                        >
                            {{ $t('company.cta') }}
                        </Link>
                    </div>

                    <div class="relative rounded-3xl overflow-hidden shadow-xl min-h-[260px]">
                      <img src="/images/job-company-bg.png"alt=""aria-hidden="true"class="absolute inset-0 w-full h-full object-cover"/>
                      <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-7 bg-white/15">
                        <p class="text-4xl font-extrabold text-black drop-shadow mb-2">HRI</p>
                        <p class="text-black font-semibold mb-1 drop-shadow">{{ $t('company.card_title') }}</p>
                        <p class="text-black/90 text-sm drop-shadow">{{ $t('company.card_desc') }}</p>
                      </div>
                    </div>
                </div>
            </section>

            <section id="faq" class="py-16 sm:py-20 bg-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6">
                    <div class="text-center mb-10 sm:mb-14">
                        <span class="text-[#123e88] font-semibold text-xs sm:text-sm uppercase tracking-[0.22em]">{{ $t('welcome_faq.label') }}</span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 mt-3 leading-tight">
                            {{ $t('welcome_faq.title') }}
                        </h2>
                    </div>

                    <div class="space-y-4">
                        <details
                            v-for="item in faqItems"
                            :key="item.q"
                            class="group rounded-3xl border border-slate-200 bg-slate-50 p-5 sm:p-6 open:bg-white open:shadow-sm"
                        >
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left">
                                <span class="text-sm sm:text-base font-bold text-slate-900 leading-7">{{ item.q }}</span>
                                <span class="text-[#123e88] text-xl leading-none transition group-open:rotate-45">+</span>
                            </summary>
                            <p class="mt-4 text-sm leading-7 text-slate-600 pr-2 sm:pr-8">
                                {{ item.a }}
                            </p>
                        </details>
                    </div>
                </div>
            </section>

            <section class="py-20 sm:py-24 bg-gradient-to-r from-[#123e88] to-[#103780]">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-white mb-5 leading-tight text-balance">
                        {{ $t('final.title') }}
                    </h2>
                    <p class="text-blue-100 mb-9 leading-8 text-sm sm:text-base">
                        {{ $t('final.desc') }}
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mx-auto">
                        <template v-if="isLoggedIn">
                            <Link
                                :href="getDashboardUrl()"
                                class="px-6 py-4 bg-[#f3d569] hover:bg-[#f8df84] text-[#103780] font-bold rounded-2xl text-base shadow-xl transition-all hover:scale-[1.02]"
                            >
                                {{ $t('final.cta_mypage') }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="px-6 py-4 bg-[#f3d569] hover:bg-[#f8df84] text-[#103780] font-bold rounded-2xl text-base shadow-xl transition-all hover:scale-[1.02]"
                            >
                                {{ $t('final.cta_register') }}
                            </Link>
                        </template>

                        <Link
                            href="/job"
                            class="px-6 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-2xl text-base border border-white/30 transition-all"
                        >
                            {{ $t('final.cta_jobs') }}
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-3"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-3"
        >
            <div
                v-if="showFab && !mobileOpen"
                class="md:hidden fixed bottom-4 inset-x-4 z-40"
            >
                <div class="rounded-2xl bg-white/95 backdrop-blur border border-slate-200 shadow-2xl p-2 grid grid-cols-2 gap-2">
                    <template v-if="isLoggedIn">
                        <Link
                            :href="getDashboardUrl()"
                            class="inline-flex items-center justify-center rounded-xl bg-[#123e88] px-4 py-3 text-sm font-bold text-white"
                        >
                            {{ $t('nav.my_page') }}
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="inline-flex items-center justify-center rounded-xl bg-[#123e88] px-4 py-3 text-sm font-bold text-white"
                        >
                            {{ $t('nav.login') }}
                        </Link>
                    </template>

                    <Link
                        href="/job"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-800"
                    >
                        {{ $t('nav.search_job') }}
                    </Link>
                </div>
            </div>
        </transition>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showPopup && !isLoggedIn"
                class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center bg-slate-950/60 p-3 sm:p-5"
                @click.self="closePopup"
            >
                <div class="w-full max-w-md rounded-[1.75rem] bg-white shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#123e88] to-[#103780] px-5 py-5 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] text-blue-100 font-semibold">{{ $t('popup.label') }}</p>
                                <h3 class="mt-2 text-xl font-black leading-tight">
                                    {{ $t('popup.title') }}
                                </h3>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-full bg-white/10 px-3 py-1.5 text-sm font-semibold hover:bg-white/20"
                                @click="closePopup"
                            >
                                {{ $t('popup.close') }}
                            </button>
                        </div>
                    </div>

                    <div class="p-5">
                        <p class="text-sm leading-7 text-slate-600">
                            {{ $t('popup.desc') }}
                        </p>

                        <div class="mt-4 rounded-2xl bg-slate-50 border border-slate-200 p-4">
                            <ul class="space-y-2 text-sm text-slate-700">
                                <li class="flex gap-2"><span class="text-[#123e88] font-bold">✓</span><span>{{ $t('popup.p1') }}</span></li>
                                <li class="flex gap-2"><span class="text-[#123e88] font-bold">✓</span><span>{{ $t('popup.p2') }}</span></li>
                                <li class="flex gap-2"><span class="text-[#123e88] font-bold">✓</span><span>{{ $t('popup.p3') }}</span></li>
                            </ul>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3">
                            <Link
                                href="/login"
                                class="inline-flex items-center justify-center rounded-2xl bg-[#123e88] px-5 py-3.5 text-sm font-bold text-white hover:bg-[#103780] transition"
                            >
                                {{ $t('popup.cta') }}
                            </Link>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3.5 text-sm font-bold text-slate-800 hover:bg-slate-200 transition"
                                @click="closePopup"
                            >
                                {{ $t('popup.secondary') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <SharedFooter />
    </div>
</template>