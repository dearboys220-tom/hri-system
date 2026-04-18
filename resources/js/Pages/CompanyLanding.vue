<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'
import SharedFooter from '@/Components/SharedFooter.vue'

const { t } = useI18n()

const props = defineProps({
    publishedJobCount: Number,
})

const auth       = usePage().props.auth
const isLoggedIn = !!auth?.user
const roleType   = auth?.user?.role_type

const getDashboardUrl = () => {
    if (roleType === 'company') return '/company/dashboard'
    if (['admin_user', 'investigator_user'].includes(roleType)) return '/admin/dashboard'
    if (roleType === 'applicant') return '/applicant/dashboard'
    return '/dashboard'
}

const scrolled   = ref(false)
const mobileOpen = ref(false)

const handleScroll = () => {
    scrolled.value = window.scrollY > 50
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)

    // JSON-LD 構造化データを <head> に挿入（SEO用）
    ;[orgSchema.value, faqSchema.value].forEach(schema => {
        const el = document.createElement('script')
        el.type = 'application/ld+json'
        el.text = JSON.stringify(schema)
        document.head.appendChild(el)
    })
})
onUnmounted(() => window.removeEventListener('scroll', handleScroll))

const investigationItems = computed(() => [
    { icon: '🎓', title: t('investigation.i1_title'), desc: t('investigation.i1_desc') },
    { icon: '💼', title: t('investigation.i2_title'), desc: t('investigation.i2_desc') },
    { icon: '🏅', title: t('investigation.i3_title'), desc: t('investigation.i3_desc') },
    { icon: '🛡️', title: t('investigation.i4_title'), desc: t('investigation.i4_desc') },
])

const benefits = computed(() => [
    { value: '90 hari', label: 'masa berlaku default resume terverifikasi' },
    { value: '5 akses', label: 'batas akses referensi default' },
    { value: 'HR', label: 'screening lebih cepat dan lebih terarah' },
])

const testimonials = computed(() => [
    { icon: '📊', name: 'PT Maju Bersama',     role: t('company_testimonials.role1'), text: t('company_testimonials.text1') },
    { icon: '📈', name: 'CV Karya Nusantara',  role: t('company_testimonials.role2'), text: t('company_testimonials.text2') },
    { icon: '🏢', name: 'PT Retail Indonesia', role: t('company_testimonials.role3'), text: t('company_testimonials.text3') },
])

const pricingItems = computed(() => [
    { label: t('company_pricing.p1_label'), price: t('company_pricing.p1_price'), note: t('company_pricing.p1_note') },
    { label: t('company_pricing.p2_label'), price: t('company_pricing.p2_price'), note: t('company_pricing.p2_note') },
    { label: t('company_pricing.p3_label'), price: t('company_pricing.p3_price'), note: t('company_pricing.p3_note') },
    { label: t('company_pricing.p4_label'), price: t('company_pricing.p4_price'), note: t('company_pricing.p4_note') },
])

const featureItems = computed(() => [
    { icon: '🔍', title: t('features.f1_title'), desc: t('features.f1_desc') },
    { icon: '📊', title: t('features.f2_title'), desc: t('features.f2_desc') },
    { icon: '🧾', title: t('features.f3_title'), desc: t('features.f3_desc') },
    { icon: '💼', title: t('features.f4_title'), desc: t('features.f4_desc') },
    { icon: '⚡', title: t('features.f5_title'), desc: t('features.f5_desc') },
    { icon: '🔒', title: t('features.f6_title'), desc: t('features.f6_desc') },
])

const stepItems = computed(() => [
    { num: '01', title: t('company_steps.s1_title'), desc: t('company_steps.s1_desc') },
    { num: '02', title: t('company_steps.s2_title'), desc: t('company_steps.s2_desc') },
    { num: '03', title: t('company_steps.s3_title'), desc: t('company_steps.s3_desc') },
    { num: '04', title: t('company_steps.s4_title'), desc: t('company_steps.s4_desc') },
])

const orgSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'HRI — Human Reliability Intelligence',
    legalName: 'PT. NIKI KINDAICHI THERR INDONESIA',
    url: 'https://hri-check.com/company',
    logo: 'https://hri-check.com/images/logo.png',
    description: t('company_seo.description'),
}))

const faqSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: [
        {
            '@type': 'Question',
            name: 'Apa manfaat utama HRI untuk perusahaan?',
            acceptedAnswer: {
                '@type': 'Answer',
                text: 'HRI membantu perusahaan membaca kandidat dengan dasar verifikasi yang lebih jelas, lebih ringkas, dan lebih mudah dipahami.',
            },
        },
        {
            '@type': 'Question',
            name: 'Apakah perusahaan dapat melihat semua data mentah kandidat?',
            acceptedAnswer: {
                '@type': 'Answer',
                text: 'Tidak. HRI menempatkan resume terverifikasi sebagai reference only dengan kontrol akses yang terbatas.',
            },
        },
        {
            '@type': 'Question',
            name: 'Apakah halaman ini dirancang untuk SEO dan AI search?',
            acceptedAnswer: {
                '@type': 'Answer',
                text: 'Ya. Struktur heading, meta, schema, dan isi konten dibuat agar mudah dipahami pengguna, mesin pencari, dan AI search.',
            },
        },
    ],
}))
</script>

<template>
    <Head>
        <title>{{ t('company_seo.title') }}</title>
        <meta name="description" :content="t('company_seo.description')" />
        <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />
        <link rel="canonical" href="https://hri-check.com/company" />

        <meta property="og:type" content="website" />
        <meta property="og:title" :content="t('company_seo.title')" />
        <meta property="og:description" :content="t('company_seo.description')" />
        <meta property="og:url" content="https://hri-check.com/company" />
        <meta property="og:site_name" content="HRI" />
        <meta property="og:image" content="https://hri-check.com/images/logo.png" />

        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="t('company_seo.title')" />
        <meta name="twitter:description" :content="t('company_seo.description')" />
        <meta name="twitter:image" content="https://hri-check.com/images/logo.png" />
    </Head>

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:bg-teal-600 focus:text-white focus:px-4 focus:py-2 focus:rounded-lg z-[999]">
        Lewati ke konten utama
    </a>

    <!-- NAVBAR -->
    <nav :class="[
        'fixed top-0 inset-x-0 z-50 transition-all duration-300',
        scrolled ? 'bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm' : 'bg-transparent'
    ]">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
            <Link href="/" class="flex items-center gap-3" aria-label="HRI Home">
                <img
                    src="/images/logo.png"
                    alt="HRI"
                    :class="[
                        'h-10 w-auto rounded-xl transition-all',
                        scrolled ? '' : 'bg-white/90 p-1'
                    ]"
                />
                <span
                    class="hidden sm:block text-xs font-semibold tracking-[0.18em] uppercase transition"
                    :class="scrolled ? 'text-slate-700' : 'text-white/90'"
                >
                    Human Reliability Intelligence
                </span>
            </Link>

            <div class="hidden md:flex items-center gap-5">
                <Link href="/job"
                    :class="[
                        'text-sm font-medium transition',
                        scrolled ? 'text-slate-700 hover:text-teal-600' : 'text-white hover:text-amber-200'
                    ]">
                    {{ t('nav.search_job') }}
                </Link>

                <a href="#features"
                    :class="[
                        'text-sm font-medium transition',
                        scrolled ? 'text-slate-700 hover:text-teal-600' : 'text-white hover:text-amber-200'
                    ]">
                    {{ t('nav.service') }}
                </a>

                <a href="#pricing"
                    :class="[
                        'text-sm font-medium transition',
                        scrolled ? 'text-slate-700 hover:text-teal-600' : 'text-white hover:text-amber-200'
                    ]">
                    {{ t('nav.price') }}
                </a>

                <Link href="/"
                    :class="[
                        'text-sm font-medium transition',
                        scrolled ? 'text-slate-700 hover:text-teal-600' : 'text-white hover:text-amber-200'
                    ]">
                    {{ t('nav.for_individual') }}
                </Link>

                <div :class="scrolled ? '[&_button]:!text-slate-700 [&_button]:!border-slate-300 [&_button:hover]:!bg-slate-50' : ''">
                    <LanguageSwitcher />
                </div>

                <template v-if="isLoggedIn">
                    <Link
                        :href="getDashboardUrl()"
                        class="ml-1 px-4 py-2 rounded-full text-sm font-semibold transition border"
                        :class="scrolled ? 'bg-teal-600 text-white hover:bg-teal-700 border-teal-600' : 'bg-white text-teal-700 hover:bg-slate-50 border-white'"
                    >
                        {{ t('company_hero.cta_dashboard') }}
                    </Link>
                </template>

                <template v-else>
                    <Link
                        href="/login"
                        class="ml-1 px-4 py-2 rounded-full text-sm font-semibold transition border"
                        :class="scrolled ? 'border-slate-300 text-slate-700 hover:bg-slate-50' : 'border-white/40 text-white hover:bg-white/10'"
                    >
                        {{ t('nav.login') }}
                    </Link>
                </template>
            </div>

            <button
                class="md:hidden p-2 rounded"
                :class="scrolled ? 'text-slate-700' : 'text-white'"
                @click="mobileOpen = !mobileOpen"
                aria-label="Buka menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div v-if="mobileOpen" class="md:hidden bg-white border-t px-4 py-4 space-y-3 shadow-lg">
            <Link href="/job" class="block text-slate-700 font-medium py-2">{{ t('nav.search_job') }}</Link>
            <a href="#features" class="block text-slate-700 font-medium py-2" @click="mobileOpen = false">{{ t('nav.service') }}</a>
            <a href="#pricing" class="block text-slate-700 font-medium py-2" @click="mobileOpen = false">{{ t('nav.price') }}</a>
            <Link href="/" class="block text-slate-700 font-medium py-2">{{ t('nav.for_individual') }}</Link>
            <div class="py-2"><LanguageSwitcher :dark="false" /></div>

            <template v-if="isLoggedIn">
                <Link :href="getDashboardUrl()" class="block w-full text-center bg-teal-600 text-white rounded-full py-2 font-semibold">
                    {{ t('company_hero.cta_dashboard') }}
                </Link>
            </template>

            <template v-else>
                <Link href="/login" class="block w-full text-center border border-slate-300 text-slate-700 rounded-full py-2 font-semibold">
                    {{ t('nav.login') }}
                </Link>
                <Link href="/register/company" class="block w-full text-center bg-teal-600 text-white rounded-full py-2 font-semibold">
                    {{ t('nav.register_company') }}
                </Link>
            </template>
        </div>
    </nav>

    <main id="main-content">
        <!-- HERO -->
        <section class="relative min-h-screen flex items-center overflow-hidden"
            style="background: linear-gradient(135deg, #0b1220 0%, #17345f 55%, #0d9488 100%);">
            <div class="absolute inset-0 opacity-10"
                style="background-image:
                    radial-gradient(circle at 20% 50%, white 1px, transparent 1px),
                    radial-gradient(circle at 80% 20%, white 1px, transparent 1px);
                    background-size: 60px 60px;">
            </div>

            <div class="absolute -bottom-1 left-0 right-0 h-24 bg-white"
                style="clip-path: ellipse(55% 100% at 50% 100%)"></div>

            <div class="relative max-w-6xl mx-auto px-4 py-32 text-center">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/20 rounded-full px-4 py-1.5 text-sm text-blue-100 font-medium mb-6">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    {{ t('company_hero.badge') }}
                </div>

                <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
                    {{ t('company_hero.title1') }}<br>
                    <span class="text-amber-300">{{ t('company_hero.title2') }}</span><br>
                    {{ t('company_hero.title3') }}
                </h1>

                <p class="text-lg md:text-xl text-slate-100 max-w-3xl mx-auto mb-10 leading-relaxed">
                    {{ t('company_hero.desc') }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-14">
                    <template v-if="isLoggedIn && roleType === 'company'">
                        <Link
                            :href="getDashboardUrl()"
                            class="px-8 py-4 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold rounded-full text-lg shadow-xl transition-all hover:scale-105"
                        >
                            🏢 {{ t('company_hero.cta_dashboard') }}
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            href="/register/company"
                            class="px-8 py-4 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold rounded-full text-lg shadow-xl transition-all hover:scale-105"
                        >
                            🏢 {{ t('company_hero.cta_register') }}
                        </Link>
                    </template>

                    <a
                        href="#features"
                        class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-full text-lg border border-white/30 backdrop-blur transition-all"
                    >
                        {{ t('company_hero.cta_service') }} ↓
                    </a>
                </div>

                <div class="flex flex-wrap justify-center gap-6">
                    <div
                        v-for="b in benefits"
                        :key="b.label"
                        class="bg-white/10 border border-white/20 backdrop-blur rounded-2xl px-6 py-3 text-center"
                    >
                        <div class="text-2xl font-black text-amber-300">{{ b.value }}</div>
                        <div class="text-slate-100 text-xs mt-0.5">{{ b.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section id="features" class="py-20 bg-white">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-14 max-w-3xl mx-auto">
                    <span class="text-teal-600 font-semibold text-sm uppercase tracking-widest">{{ t('features.label') }}</span>
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mt-2 mb-4">{{ t('features.title') }}</h2>
                    <p class="text-slate-600 leading-relaxed">{{ t('features.desc') }}</p>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div
                        v-for="f in featureItems"
                        :key="f.title"
                        class="group bg-slate-50 hover:bg-teal-600 rounded-2xl p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-default border border-slate-100"
                    >
                        <div class="text-4xl mb-4">{{ f.icon }}</div>
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-white mb-3 transition-colors">
                            {{ f.title }}
                        </h3>
                        <p class="text-slate-500 group-hover:text-teal-50 text-sm leading-relaxed transition-colors">
                            {{ f.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- WHY -->
        <section class="py-20 bg-gradient-to-b from-slate-50 to-white">
            <div class="max-w-6xl mx-auto px-4">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div>
                        <span class="text-teal-600 font-semibold text-sm uppercase tracking-widest">{{ t('why.label') }}</span>
                        <h2 class="text-3xl md:text-4xl font-black text-slate-900 mt-2 mb-6">{{ t('why.title') }}</h2>
                        <p class="text-slate-600 leading-relaxed mb-8">{{ t('why.desc') }}</p>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <span class="mt-1 text-teal-600 font-black">✓</span>
                                <p class="text-slate-700 leading-relaxed">{{ t('why.w1') }}</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-1 text-teal-600 font-black">✓</span>
                                <p class="text-slate-700 leading-relaxed">{{ t('why.w2') }}</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-1 text-teal-600 font-black">✓</span>
                                <p class="text-slate-700 leading-relaxed">{{ t('why.w3') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl p-8 shadow-xl border border-slate-100 bg-white">
                        <div class="inline-flex items-center gap-2 bg-teal-50 text-teal-700 rounded-full px-3 py-1 text-sm font-semibold mb-5">
                            {{ t('why.card_sub') }}
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-4">{{ t('why.card_title') }}</h3>
                        <p class="text-slate-600 leading-relaxed">
                            {{ t('why.card_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- INVESTIGATION -->
        <section class="py-20 bg-white">
            <div class="max-w-6xl mx-auto px-4">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div>
                        <span class="text-teal-600 font-semibold text-sm uppercase tracking-widest">{{ t('investigation.label') }}</span>
                        <h2 class="text-3xl md:text-4xl font-black text-slate-900 mt-2 mb-6">
                            {{ t('investigation.title') }}
                        </h2>
                        <p class="text-slate-600 leading-relaxed mb-8">
                            {{ t('investigation.desc') }}
                        </p>

                        <Link
                            href="/register/company"
                            class="inline-block bg-teal-600 hover:bg-teal-700 text-white font-bold px-8 py-3 rounded-full transition"
                        >
                            {{ t('company_hero.cta_register') }} →
                        </Link>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="item in investigationItems"
                            :key="item.title"
                            class="flex items-start gap-4 bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md hover:border-teal-200 transition-all"
                        >
                            <div class="text-3xl flex-shrink-0">{{ item.icon }}</div>
                            <div>
                                <h3 class="font-bold text-slate-900 mb-1">{{ item.title }}</h3>
                                <p class="text-slate-500 text-sm leading-relaxed">{{ item.desc }}</p>
                            </div>
                            <div class="ml-auto flex-shrink-0">
                                <span class="bg-teal-50 text-teal-700 text-xs font-bold px-2 py-1 rounded-full">
                                    Reference
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- COMPANY SECTION -->
        <section class="py-20 bg-slate-50">
            <div class="max-w-6xl mx-auto px-4">
                <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-8 items-center">
                    <div>
                        <span class="text-teal-600 font-semibold text-sm uppercase tracking-widest">{{ t('company_section.badge') }}</span>
                        <h2 class="text-3xl md:text-4xl font-black text-slate-900 mt-2 mb-5">{{ t('company_section.title') }}</h2>
                        <p class="text-slate-600 leading-relaxed mb-8">{{ t('company_section.desc') }}</p>

                        <div class="space-y-4 mb-8">
                            <div class="flex items-start gap-3">
                                <span class="mt-1 text-teal-600 font-black">✓</span>
                                <p class="text-slate-700">{{ t('company_section.b1') }}</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-1 text-teal-600 font-black">✓</span>
                                <p class="text-slate-700">{{ t('company_section.b2') }}</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-1 text-teal-600 font-black">✓</span>
                                <p class="text-slate-700">{{ t('company_section.b3') }}</p>
                            </div>
                        </div>

                        <Link
                            href="/register/company"
                            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white font-bold px-8 py-3 rounded-full transition"
                        >
                            {{ t('company_section.cta') }}
                        </Link>
                    </div>

                    <div class="relative rounded-3xl overflow-hidden shadow-xl">
                        <img src="/images/company-promo.png"alt=""aria-hidden="true"class="w-full h-full object-cover"/>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-8 bg-white/10">
                            <div class="text-5xl md:text-6xl font-black text-slate-900">{{ t('company_section.days') }}</div>
                            <div class="mt-2 text-2xl font-black text-slate-900">{{ t('company_section.days_label') }}</div>
                            <p class="mt-3 text-slate-800 leading-relaxed">{{ t('company_section.days_note') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- STEPS -->
        <section class="py-20 bg-slate-900">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-14">
                    <span class="text-amber-300 font-semibold text-sm uppercase tracking-widest">{{ t('steps.label') }}</span>
                    <h2 class="text-3xl md:text-4xl font-black text-white mt-2">{{ t('steps.title') }}</h2>
                </div>

                <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-6">
                    <div
                        v-for="step in stepItems"
                        :key="step.num"
                        class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 hover:bg-white/15 transition-all"
                    >
                        <div class="text-amber-300 text-4xl font-black mb-3">{{ step.num }}</div>
                        <h3 class="text-white font-bold mb-2">{{ step.title }}</h3>
                        <p class="text-slate-200 text-sm leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TESTIMONIALS -->
        <section class="py-20 bg-white">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-14">
                    <span class="text-teal-600 font-semibold text-sm uppercase tracking-widest">{{ t('company_testimonials.label') }}</span>
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mt-2">{{ t('company_testimonials.title') }}</h2>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div
                        v-for="t2 in testimonials"
                        :key="t2.name"
                        class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:shadow-lg transition-all"
                    >
                        <div class="text-3xl mb-4">{{ t2.icon }}</div>
                        <p class="text-slate-700 text-sm leading-relaxed mb-6 italic">"{{ t2.text }}"</p>

                        <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                            <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center font-bold text-teal-700 text-sm">
                                {{ t2.name[0] }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-sm">{{ t2.name }}</div>
                                <div class="text-slate-400 text-xs">{{ t2.role }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRICING -->
        <section id="pricing" class="py-20 bg-slate-50">
            <div class="max-w-4xl mx-auto px-4">
                <div class="text-center mb-14">
                    <span class="text-teal-600 font-semibold text-sm uppercase tracking-widest">
                        {{ t('company_pricing_meta.label') }}
                    </span>
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mt-2">{{ t('company_pricing_meta.title') }}</h2>
                    <p class="text-slate-500 mt-4">{{ t('company_pricing_meta.desc') }}</p>
                </div>

                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
                    <div
                        v-for="(item, i) in pricingItems"
                        :key="i"
                        :class="[
                            'flex items-center justify-between px-8 py-5 gap-6',
                            i < pricingItems.length - 1 ? 'border-b border-slate-100' : '',
                            i === 0 ? 'bg-amber-50' : ''
                        ]"
                    >
                        <div>
                            <div class="font-semibold text-slate-900">{{ item.label }}</div>
                            <div class="text-slate-400 text-xs mt-0.5">{{ item.note }}</div>
                        </div>

                        <div
                            :class="[
                                'font-black text-lg text-right',
                                item.price.includes('Gratis') ? 'text-teal-600' : 'text-slate-900'
                            ]"
                        >
                            {{ item.price }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-24 bg-gradient-to-r from-slate-900 via-slate-800 to-teal-700">
            <div class="max-w-3xl mx-auto px-4 text-center">
                <h2 class="text-3xl md:text-4xl font-black text-white mb-6">
                    {{ t('company_cta.title') }}
                </h2>

                <p class="text-slate-100 mb-10 leading-relaxed">
                    {{ t('company_cta.desc') }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <template v-if="isLoggedIn && roleType === 'company'">
                        <Link
                            :href="getDashboardUrl()"
                            class="px-8 py-4 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold rounded-full text-lg shadow-xl transition-all hover:scale-105"
                        >
                            {{ t('company_hero.cta_dashboard') }} →
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            href="/register/company"
                            class="px-8 py-4 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold rounded-full text-lg shadow-xl transition-all hover:scale-105"
                        >
                            {{ t('company_hero.cta_register') }}
                        </Link>
                    </template>

                    <Link
                        href="/"
                        class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-full text-lg border border-white/30 transition-all"
                    >
                        {{ t('nav.for_individual') }}
                    </Link>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <SharedFooter />
</template>