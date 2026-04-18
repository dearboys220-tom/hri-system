<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'
import SharedFooter from '@/Components/SharedFooter.vue'

const { t, tm, locale } = useI18n()

const props = defineProps({
  jobs: {
    type: Object,
    default: () => ({ data: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  categoriesGrouped: {
    type: Array,
    default: () => [],
  },
  totalJobs: {
    type: Number,
    default: 0,
  },
  stats: {
    type: Object,
    default: () => ({
      activeRequests: 649,
      companyMembers: 1200,
      individualMembers: 760000,
      countries: ['Indonesia', 'Jepang', 'Bulgaria', 'Albania'],
    }),
  },
})

const page = usePage()
const auth = computed(() => page.props.auth || {})
const user = computed(() => auth.value.user || null)
const isLoggedIn = computed(() => !!user.value)
const roleType = computed(() => user.value?.role_type || null)

const getDashboardUrl = () => {
  if (roleType.value === 'applicant') return '/applicant/dashboard'
  if (roleType.value === 'company') return '/company/dashboard'
  if (['admin_user', 'investigator_user'].includes(roleType.value)) return '/admin/dashboard'
  return '/dashboard'
}

const scrolled = ref(false)
const mobileOpen = ref(false)

const onScroll = () => {
  scrolled.value = window.scrollY > 24
}

onMounted(() => {
    window.addEventListener('scroll', onScroll)

    // JSON-LD 構造化データを <head> に挿入（SEO用）
    try {
        const schemas = [
            schemaWebsite.value,
            schemaOrganization.value,
            schemaCollectionPage.value,
        ]

        // FAQはアイテムがある場合のみ追加
        if (faqs.value && faqs.value.length > 0) {
            schemas.push(schemaFAQ.value)
        }

        schemas.forEach(schema => {
            if (!schema) return
            const el = document.createElement('script')
            el.type = 'application/ld+json'
            el.text = JSON.stringify(schema)
            document.head.appendChild(el)
        })
    } catch (e) {
        console.warn('JSON-LD挿入エラー:', e)
    }
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})

const searchQuery = ref(props.filters?.q || '')
const searchCategory = ref(props.filters?.category || '')
const searchLocation = ref(props.filters?.location || '')

const locationOptions = [
  'Jakarta',
  'Batam',
  'Cilegon',
  'Tangerang',
  'Surabaya',
  'Bandung',
  'Karawang',
  'Balikpapan',
  'Medan',
  'Makassar',
]

const doSearch = () => {
  router.get('/jobs', {
    q: searchQuery.value || undefined,
    category: searchCategory.value || undefined,
    location: searchLocation.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetSearch = () => {
  searchQuery.value = ''
  searchCategory.value = ''
  searchLocation.value = ''
}

const hasFilter = computed(() => !!searchQuery.value || !!searchCategory.value || !!searchLocation.value)

const whyCards = computed(() => tm('job_why.cards') || [])
const trustPoints = computed(() => tm('job_hero.chips') || [])
const seoIntents = computed(() => tm('seo_block.intents') || [])
const seoPoints = computed(() => tm('seo_block.points') || [])
const faqs = computed(() => tm('job_faq.items') || [])

// カテゴリ名を現在の言語に翻訳する関数
const translateCategory = (name) => {
  const map = tm('category_names')
  if (map && map[name]) return map[name]
  return name // 翻訳がなければ元のインドネシア語をそのまま返す
}

const featuredJobs = computed(() => {
  if (props.jobs?.data?.length) return props.jobs.data.slice(0, 6)
  return [
    {
      id: 1,
      title: 'LNG Pipe Welder',
      company_name: 'Perusahaan Mitra Internasional',
      location: 'Batam',
      salary: 'Rp 12.000.000 - Rp 18.000.000',
      deadline: null,
    },
    {
      id: 2,
      title: 'Perawat / Nurse',
      company_name: 'Mitra Medis Internasional',
      location: 'Jakarta',
      salary: 'Rp 8.000.000 - Rp 14.000.000',
      deadline: null,
    },
    {
      id: 3,
      title: 'Protective Coating Painter',
      company_name: 'Mitra Proyek Energi',
      location: 'Cilegon',
      salary: 'Rp 7.500.000 - Rp 13.000.000',
      deadline: null,
    },
  ]
})

const schemaWebsite = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'WebSite',
  name: 'HRI Job',
  url: 'https://hri-check.com/job',
  inLanguage: locale.value,
  potentialAction: {
    '@type': 'SearchAction',
    target: 'https://hri-check.com/jobs?q={search_term_string}',
    'query-input': 'required name=search_term_string',
  },
}))

const schemaOrganization = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'Organization',
  name: 'PT. NIKI KINDAICHI THERR INDONESIA',
  brand: 'HRI',
  url: 'https://hri-check.com',
  logo: 'https://hri-check.com/images/logo.png',
  description: t('seo_schema.organization_description'),
}))

const schemaCollectionPage = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'CollectionPage',
  name: t('jobs_seo.title'),
  url: 'https://hri-check.com/job',
  inLanguage: locale.value,
  description: t('jobs_seo.description'),
}))

const schemaFAQ = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'FAQPage',
  mainEntity: faqs.value.map((item) => ({
    '@type': 'Question',
    name: item.q,
    acceptedAnswer: {
      '@type': 'Answer',
      text: item.a,
    },
  })),
}))

const applyForJob = (jobId) => {
  if (!isLoggedIn.value) {
    router.visit('/login')
    return
  }
  router.visit(`/jobs/${jobId}`)
}
</script>

<template>
  <Head>
    <title>{{ t('jobs_seo.title') }}</title>
    <meta name="description" :content="t('jobs_seo.description')" />
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />
    <meta name="keywords" :content="t('jobs_seo.keywords')" />
    <meta name="author" content="PT. NIKI KINDAICHI THERR INDONESIA" />
    <meta name="theme-color" content="#0f766e" />

    <link rel="canonical" href="https://hri-check.com/job" />

    <meta property="og:type" content="website" />
    <meta property="og:title" :content="t('jobs_seo.title')" />
    <meta property="og:description" :content="t('jobs_seo.description')" />
    <meta property="og:url" content="https://hri-check.com/job" />
    <meta property="og:site_name" content="HRI Job" />
    <meta property="og:image" content="https://hri-check.com/images/og-hri-job.jpg" />
    <meta property="og:locale" :content="locale === 'id' ? 'id_ID' : 'id_ID'" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" :content="t('jobs_seo.title')" />
    <meta name="twitter:description" :content="t('jobs_seo.description')" />
    <meta name="twitter:image" content="https://hri-check.com/images/og-hri-job.jpg" />

  </Head>

  <a
    href="#konten-utama"
    class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-[100] focus:bg-white focus:text-slate-900 focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg"
  >
    {{ t('a11y.skip_to_content') }}
  </a>

  <div class="min-h-screen bg-slate-50 text-slate-900">
    <nav
      :class="[
        'fixed inset-x-0 top-0 z-50 transition-all duration-300',
        scrolled ? 'bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm' : 'bg-transparent'
      ]"
      aria-label="Navigasi utama"
    >
      <div class="max-w-7xl mx-auto px-4 lg:px-6 h-16 lg:h-20 flex items-center justify-between gap-4">
        <Link href="/" class="flex items-center gap-3 min-w-0" aria-label="HRI Home">
          <img
            src="/images/logo.png"
            alt="HRI Logo"
            class="h-10 w-10 rounded-xl object-contain bg-white/90 p-1 shadow-sm"
          />
          <div class="min-w-0 hidden sm:block">
            <div :class="['text-[11px] lg:text-xs font-black uppercase tracking-[0.18em] truncate', scrolled ? 'text-slate-800' : 'text-white']">
              Human Reliability Intelligence
            </div>
            <div :class="['text-[11px] truncate', scrolled ? 'text-slate-500' : 'text-white/80']">
              {{ t('brand.sub') }}
            </div>
          </div>
        </Link>

        <div class="hidden lg:flex items-center gap-5">
          <Link :class="[scrolled ? 'text-teal-700' : 'text-white', 'text-sm font-bold transition']" href="/job">{{ t('nav.search_job') }}</Link>
          <Link :class="[scrolled ? 'text-slate-700 hover:text-teal-700' : 'text-white hover:text-white/80', 'text-sm font-semibold transition']" href="/">{{ t('nav.for_individual') }}</Link>
          <Link :class="[scrolled ? 'text-slate-700 hover:text-teal-700' : 'text-white hover:text-white/80', 'text-sm font-semibold transition']" href="/company">{{ t('nav.for_company') }}</Link>
          <div :class="scrolled ? '[&_button]:!text-slate-700 [&_button]:!border-slate-300' : ''">
            <LanguageSwitcher />
          </div>

          <template v-if="isLoggedIn">
            <Link :href="getDashboardUrl()" :class="[scrolled ? 'bg-teal-700 text-white hover:bg-teal-800' : 'bg-white text-teal-700 hover:bg-slate-100', 'px-5 py-2.5 rounded-full text-sm font-bold transition']">
              {{ t('nav.my_page') }}
            </Link>
          </template>
          <template v-else>
            <Link :href="'/login'" :class="[scrolled ? 'border-slate-300 text-slate-700 hover:bg-slate-100' : 'border-white/35 text-white hover:bg-white/10', 'px-5 py-2.5 rounded-full text-sm font-bold border transition']">
              {{ t('nav.login') }}
            </Link>
          </template>
        </div>

        <button
          class="lg:hidden inline-flex items-center justify-center rounded-xl p-2"
          :class="scrolled ? 'text-slate-800 bg-slate-100' : 'text-white bg-white/10 border border-white/20'"
          @click="mobileOpen = !mobileOpen"
          :aria-expanded="mobileOpen ? 'true' : 'false'"
          aria-controls="mobile-nav"
          aria-label="Toggle menu"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div v-if="mobileOpen" id="mobile-nav" class="lg:hidden bg-white border-t border-slate-200 shadow-lg">
        <div class="px-4 py-4 space-y-3">
          <Link href="/job" class="block py-2 font-bold text-teal-700">{{ t('nav.search_job') }}</Link>
          <Link href="/" class="block py-2 font-semibold text-slate-700">{{ t('nav.for_individual') }}</Link>
          <Link href="/company" class="block py-2 font-semibold text-slate-700">{{ t('nav.for_company') }}</Link>
          <Link href="/about" class="block py-2 font-semibold text-slate-700">{{ t('nav.about') }}</Link>

          <div class="pt-2">
            <LanguageSwitcher :dark="false" />
          </div>

          <template v-if="isLoggedIn">
            <Link :href="getDashboardUrl()" class="block text-center bg-teal-700 text-white py-3 rounded-full font-bold">{{ t('nav.my_page') }}</Link>
          </template>
          <template v-else>
            <Link href="/login" class="block text-center border border-slate-300 text-slate-700 py-3 rounded-full font-bold">{{ t('nav.login') }}</Link>
            <Link href="/register/company" class="block text-center bg-teal-700 text-white py-3 rounded-full font-bold">{{ t('nav.register_company') }}</Link>
          </template>
        </div>
      </div>
    </nav>

    <section class="relative overflow-hidden pt-28 md:pt-32 lg:pt-36 pb-20 lg:pb-28 bg-gradient-to-br from-teal-900 via-teal-700 to-teal-400">
      <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 32px 32px;"></div>
      <div class="absolute -bottom-1 left-0 right-0 h-20 bg-slate-50" style="clip-path: ellipse(55% 100% at 50% 100%)"></div>

      <div class="relative max-w-7xl mx-auto px-4 lg:px-6 grid lg:grid-cols-[1.25fr_.75fr] gap-10 items-center">
        <div>
          <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-bold text-white/95">
            <span class="w-2.5 h-2.5 rounded-full bg-lime-400 animate-pulse"></span>
            {{ t('job_hero.badge') }}
          </div>

          <h1 class="mt-5 text-4xl md:text-6xl font-black leading-[1.05] text-white max-w-4xl">
            {{ t('job_hero.title1') }}<br>
            <span class="text-amber-300">{{ t('job_hero.title2') }}</span>
          </h1>

          <p class="mt-6 text-lg md:text-xl leading-relaxed text-cyan-50 max-w-3xl">
            {{ t('job_hero.desc') }}
          </p>

          <div class="mt-6 flex flex-wrap gap-2">
            <span
              v-for="chip in trustPoints"
              :key="chip"
              class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs md:text-sm font-semibold text-white"
            >
              {{ chip }}
            </span>
          </div>

          <div class="mt-8 flex flex-wrap gap-3">
            <a href="#konten-utama" class="inline-flex items-center justify-center rounded-full bg-amber-300 px-6 py-3 text-sm md:text-base font-black text-slate-900 shadow-lg transition hover:bg-amber-200">
              {{ t('job_hero.btn_search') }}
            </a>
            <Link href="/company" class="inline-flex items-center justify-center rounded-full border border-white/30 bg-white/10 px-6 py-3 text-sm md:text-base font-bold text-white transition hover:bg-white/15">
              {{ t('job_hero.btn_company') }}
            </Link>
          </div>
        </div>

        <aside class="rounded-3xl border border-white/20 bg-white/10 backdrop-blur-md p-6 md:p-8 shadow-2xl text-white">
          <h2 class="text-2xl font-black leading-tight">{{ t('hero_side.title') }}</h2>
          <p class="mt-4 text-cyan-50 leading-relaxed">{{ t('hero_side.desc') }}</p>
          <ul class="mt-5 space-y-3 text-sm md:text-base">
            <li v-for="item in tm('hero_side.items') || []" :key="item" class="flex gap-3">
              <span class="mt-1 inline-block w-2.5 h-2.5 rounded-full bg-amber-300 flex-none"></span>
              <span>{{ item }}</span>
            </li>
          </ul>
        </aside>
      </div>

      <div id="konten-utama" class="relative max-w-6xl mx-auto px-4 lg:px-6 mt-12">
        <div class="rounded-3xl bg-white p-5 md:p-6 shadow-2xl ring-1 ring-slate-100">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[1.25fr_1fr_1fr_auto] gap-3">
            <label class="relative flex items-center rounded-2xl border border-slate-200 bg-white px-4 min-h-[56px]">
              <svg class="w-5 h-5 text-slate-400 mr-3 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="t('job_hero.search_keyword')"
                class="w-full border-none bg-transparent p-0 text-sm md:text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                @keyup.enter="doSearch"
              >
            </label>

            <label class="relative flex items-center rounded-2xl border border-slate-200 bg-white px-4 min-h-[56px]">
              <svg class="w-5 h-5 text-slate-400 mr-3 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" />
              </svg>
              <select v-model="searchCategory" class="w-full border-none bg-transparent p-0 text-sm md:text-base text-slate-900 focus:outline-none focus:ring-0">
                <option value="">{{ t('job_hero.search_category') }}</option>
                <template v-for="parent in categoriesGrouped" :key="parent.id">
                  <optgroup :label="translateCategory(parent.name)">
                    <option v-for="child in parent.children" :key="child.id" :value="child.id">{{ child.name }}</option>
                  </optgroup>
                </template>
              </select>
            </label>

            <label class="relative flex items-center rounded-2xl border border-slate-200 bg-white px-4 min-h-[56px]">
              <svg class="w-5 h-5 text-slate-400 mr-3 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <select v-model="searchLocation" class="w-full border-none bg-transparent p-0 text-sm md:text-base text-slate-900 focus:outline-none focus:ring-0">
                <option value="">{{ t('job_hero.search_location') }}</option>
                <option v-for="city in locationOptions" :key="city" :value="city">{{ city }}</option>
              </select>
            </label>

            <button @click="doSearch" class="inline-flex items-center justify-center rounded-2xl bg-teal-700 px-6 min-h-[56px] text-sm md:text-base font-black text-white transition hover:bg-teal-800 shadow-md">
              🔍 {{ t('job_hero.btn_search') }}
            </button>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-3 justify-between">
            <p class="text-xs md:text-sm text-slate-500">{{ t('job_hero.note') }}</p>
            <button v-if="hasFilter" @click="resetSearch" class="text-sm font-bold text-slate-600 hover:text-teal-700 transition">
              {{ t('job_hero.btn_reset') }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="relative -mt-8 md:-mt-10 z-10 px-4 lg:px-6">
      <div class="max-w-7xl mx-auto grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <article class="rounded-2xl bg-white border border-slate-100 shadow-lg p-6">
          <div class="text-[11px] font-black tracking-[0.18em] uppercase text-teal-700">{{ t('stats.active_requests') }}</div>
          <div class="mt-2 text-3xl font-black text-slate-900">{{ props.stats.activeRequests }}</div>
          <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ t('stats.active_requests_desc') }}</p>
        </article>
        <article class="rounded-2xl bg-white border border-slate-100 shadow-lg p-6">
          <div class="text-[11px] font-black tracking-[0.18em] uppercase text-teal-700">{{ t('stats.company_members') }}</div>
          <div class="mt-2 text-3xl font-black text-slate-900">{{ props.stats.companyMembers.toLocaleString() }}</div>
          <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ t('stats.company_members_desc') }}</p>
        </article>
        <article class="rounded-2xl bg-white border border-slate-100 shadow-lg p-6">
          <div class="text-[11px] font-black tracking-[0.18em] uppercase text-teal-700">{{ t('stats.individual_members') }}</div>
          <div class="mt-2 text-3xl font-black text-slate-900">{{ props.stats.individualMembers.toLocaleString() }}</div>
          <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ t('stats.individual_members_desc') }}</p>
        </article>
        <article class="rounded-2xl bg-white border border-slate-100 shadow-lg p-6">
          <div class="text-[11px] font-black tracking-[0.18em] uppercase text-teal-700">{{ t('stats.international_context') }}</div>
          <div class="mt-2 text-xl font-black text-slate-900 leading-snug">{{ props.stats.countries.join(' / ') }}</div>
          <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ t('stats.international_context_desc') }}</p>
        </article>
      </div>
    </section>

    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="max-w-4xl mx-auto text-center mb-12">
          <span class="inline-block text-teal-700 font-black text-xs md:text-sm uppercase tracking-[0.22em]">{{ t('job_why.label') }}</span>
          <h2 class="mt-3 text-3xl md:text-5xl font-black text-slate-900 leading-tight">{{ t('job_why.title') }}</h2>
          <p class="mt-4 text-base md:text-lg text-slate-500 leading-relaxed">{{ t('job_why.desc') }}</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
          <article v-for="card in whyCards" :key="card.title" class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:bg-teal-700">
            <div class="text-4xl">{{ card.icon }}</div>
            <h3 class="mt-5 text-xl font-black text-slate-900 group-hover:text-white transition">{{ card.title }}</h3>
            <p class="mt-3 text-slate-500 group-hover:text-cyan-50 leading-relaxed transition">{{ card.desc }}</p>
          </article>
        </div>
      </div>
    </section>

    <section class="pb-20">
      <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="max-w-4xl mx-auto text-center mb-12">
          <span class="inline-block text-teal-700 font-black text-xs md:text-sm uppercase tracking-[0.22em]">{{ t('trust_section.label') }}</span>
          <h2 class="mt-3 text-3xl md:text-5xl font-black text-slate-900 leading-tight">{{ t('trust_section.title') }}</h2>
          <p class="mt-4 text-base md:text-lg text-slate-500 leading-relaxed">{{ t('trust_section.desc') }}</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
          <article class="rounded-3xl border border-slate-200 bg-emerald-50 p-8 shadow-sm">
            <div class="text-4xl">✅</div>
            <h3 class="mt-5 text-2xl font-black text-slate-900">{{ t('trust_section.cards.c1_title') }}</h3>
            <p class="mt-4 text-slate-600 leading-relaxed">{{ t('trust_section.cards.c1_desc') }}</p>
          </article>
          <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="text-4xl">🌍</div>
            <h3 class="mt-5 text-2xl font-black text-slate-900">{{ t('trust_section.cards.c2_title') }}</h3>
            <p class="mt-4 text-slate-600 leading-relaxed">{{ t('trust_section.cards.c2_desc') }}</p>
          </article>
          <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="text-4xl">⚡</div>
            <h3 class="mt-5 text-2xl font-black text-slate-900">{{ t('trust_section.cards.c3_title') }}</h3>
            <p class="mt-4 text-slate-600 leading-relaxed">{{ t('trust_section.cards.c3_desc') }}</p>
          </article>
        </div>
      </div>
    </section>

    <section class="py-20 bg-white border-y border-slate-100">
      <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="max-w-4xl mx-auto text-center mb-12">
          <span class="inline-block text-teal-700 font-black text-xs md:text-sm uppercase tracking-[0.22em]">{{ t('job_list.label') }}</span>
          <h2 class="mt-3 text-3xl md:text-5xl font-black text-slate-900 leading-tight">{{ t('job_list.title') }}</h2>
          <p class="mt-4 text-base md:text-lg text-slate-500 leading-relaxed">{{ t('job_list.desc') }}</p>
        </div>

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
          <article v-for="job in featuredJobs" :key="job.id" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-xl transition">
            <div class="inline-flex items-center rounded-full bg-teal-50 border border-teal-200 px-3 py-1 text-xs font-black text-teal-700">
              {{ t('job_list.verified_badge') }}
            </div>
            <h3 class="mt-4 text-xl font-black text-slate-900 leading-snug">{{ job.title }}</h3>
            <div class="mt-4 space-y-2 text-sm text-slate-600">
              <p><strong>{{ t('job_list.company') }}</strong>{{ job.company_name || '-' }}</p>
              <p><strong>{{ t('job_list.location') }}</strong>{{ job.location || '-' }}</p>
              <p><strong>{{ t('job_list.salary') }}</strong>{{ job.salary || t('job_list.negotiable') }}</p>
            </div>
            <div class="mt-6 flex gap-3">
              <button @click="applyForJob(job.id)" class="inline-flex items-center justify-center rounded-full bg-teal-700 px-5 py-2.5 text-sm font-black text-white transition hover:bg-teal-800">
                {{ t('job_list.btn_detail') }}
              </button>
              <Link :href="`/jobs/${job.id}`" class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                {{ t('job_list.btn_view') }}
              </Link>
            </div>
          </article>
        </div>

        <div class="text-center mt-10">
          <Link href="/jobs" class="inline-flex items-center justify-center rounded-full bg-amber-300 px-7 py-3 text-sm md:text-base font-black text-slate-900 transition hover:bg-amber-200 shadow-md">
            {{ t('job_list.btn_all') }}
          </Link>
        </div>
      </div>
    </section>

    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="max-w-4xl mx-auto text-center mb-12">
          <span class="inline-block text-teal-700 font-black text-xs md:text-sm uppercase tracking-[0.22em]">{{ t('job_category.label') }}</span>
          <h2 class="mt-3 text-3xl md:text-5xl font-black text-slate-900 leading-tight">{{ t('job_category.title') }}</h2>
          <p class="mt-4 text-base md:text-lg text-slate-500 leading-relaxed">{{ t('job_category.desc') }}</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4">
          <button
            v-for="parent in categoriesGrouped"
            :key="parent.id"
            @click="router.get('/jobs', { category: parent.id })"
            class="group rounded-2xl border border-slate-200 bg-white p-4 md:p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:bg-teal-700 hover:shadow-lg"
          >
            <div class="text-sm md:text-base font-black text-slate-900 group-hover:text-white transition leading-snug">
              {{ translateCategory(parent.name) }}
            </div>
            <div class="mt-1 text-xs md:text-sm text-slate-500 group-hover:text-cyan-100 transition">
              {{ parent.children?.length || 0 }} {{ t('job_category.unit') }}
            </div>
          </button>
        </div>
      </div>
    </section>

    <section class="py-16 bg-slate-100 border-y border-slate-200">
      <div class="max-w-6xl mx-auto px-4 lg:px-6 grid lg:grid-cols-2 gap-8 items-center">
        <div>
          <span class="inline-block rounded-full border border-orange-200 bg-orange-100 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-orange-700">
            {{ t('job_company_section.badge') }}
          </span>
          <h2 class="mt-4 text-3xl md:text-4xl font-black text-slate-900 leading-tight">{{ t('job_company_section.title') }}</h2>
          <p class="mt-4 text-slate-600 leading-relaxed text-base md:text-lg">{{ t('job_company_section.desc') }}</p>
          <div class="mt-6 flex flex-wrap gap-3">
            <Link href="/company" class="inline-flex items-center justify-center rounded-full bg-orange-500 px-6 py-3 text-sm md:text-base font-black text-white transition hover:bg-orange-600 shadow-md">
              {{ t('job_company_section.cta') }}
            </Link>
            <Link href="/register/company" class="inline-flex items-center justify-center rounded-full border border-slate-300 px-6 py-3 text-sm md:text-base font-bold text-slate-700 transition hover:bg-white">
              {{ t('job_company_section.cta_secondary') }}
            </Link>
          </div>
        </div>

        <div class="relative rounded-3xl overflow-hidden shadow-xl">
          <img src="/images/company-promo.png"alt=""aria-hidden="true"class="w-full h-full object-cover"/>
          <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-8 bg-white/10">
            <div class="text-5xl md:text-6xl font-black text-slate-900">{{ t('job_company_section.days') }}</div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ t('job_company_section.days_label') }}</div>
            <p class="mt-3 text-slate-800 leading-relaxed">{{ t('job_company_section.days_note') }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-white">
      <div class="max-w-5xl mx-auto px-4 lg:px-6">
        <div class="max-w-4xl mx-auto text-center mb-12">
          <span class="inline-block text-teal-700 font-black text-xs md:text-sm uppercase tracking-[0.22em]">{{ t('seo_block.label') }}</span>
          <h2 class="mt-3 text-3xl md:text-5xl font-black text-slate-900 leading-tight">{{ t('seo_block.title') }}</h2>
          <p class="mt-4 text-base md:text-lg text-slate-500 leading-relaxed">{{ t('seo_block.desc') }}</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
          <article class="rounded-3xl border border-slate-200 bg-slate-50 p-7">
            <h3 class="text-2xl font-black text-slate-900">{{ t('seo_block.intent_title') }}</h3>
            <ul class="mt-5 space-y-3">
              <li v-for="item in seoIntents" :key="item" class="flex gap-3 text-slate-700 leading-relaxed">
                <span class="mt-2 inline-block w-2.5 h-2.5 rounded-full bg-teal-600 flex-none"></span>
                <span>{{ item }}</span>
              </li>
            </ul>
          </article>

          <article class="rounded-3xl border border-slate-200 bg-slate-50 p-7">
            <h3 class="text-2xl font-black text-slate-900">{{ t('seo_block.points_title') }}</h3>
            <ul class="mt-5 space-y-3">
              <li v-for="item in seoPoints" :key="item" class="flex gap-3 text-slate-700 leading-relaxed">
                <span class="mt-2 inline-block w-2.5 h-2.5 rounded-full bg-amber-500 flex-none"></span>
                <span>{{ item }}</span>
              </li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <section class="py-20 bg-slate-50 border-y border-slate-200">
      <div class="max-w-4xl mx-auto px-4 lg:px-6">
        <div class="text-center mb-12">
          <span class="inline-block text-teal-700 font-black text-xs md:text-sm uppercase tracking-[0.22em]">{{ t('job_faq.label') }}</span>
          <h2 class="mt-3 text-3xl md:text-4xl font-black text-slate-900 leading-tight">{{ t('job_faq.title') }}</h2>
        </div>

        <div class="space-y-4">
          <details v-for="item in faqs" :key="item.q" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <summary class="cursor-pointer list-none font-black text-slate-900 flex items-center justify-between gap-4">
              <span>{{ item.q }}</span>
              <span class="text-teal-700">+</span>
            </summary>
            <p class="mt-4 text-slate-600 leading-relaxed">{{ item.a }}</p>
          </details>
        </div>
      </div>
    </section>

    <section class="py-20 bg-gradient-to-br from-teal-900 via-teal-700 to-teal-500">
      <div class="max-w-4xl mx-auto px-4 lg:px-6 text-center">
        <h2 class="text-3xl md:text-5xl font-black text-white leading-tight">{{ t('cta.title') }}</h2>
        <p class="mt-5 text-lg text-cyan-50 leading-relaxed max-w-3xl mx-auto">{{ t('cta.desc') }}</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
          <template v-if="isLoggedIn">
            <Link :href="getDashboardUrl()" class="inline-flex items-center justify-center rounded-full bg-amber-300 px-8 py-4 text-base font-black text-slate-900 transition hover:bg-amber-200 shadow-xl">
              {{ t('cta.btn_mypage') }}
            </Link>
          </template>
          <template v-else>
            <Link href="/login" class="inline-flex items-center justify-center rounded-full bg-amber-300 px-8 py-4 text-base font-black text-slate-900 transition hover:bg-amber-200 shadow-xl">
              {{ t('cta.btn_register') }}
            </Link>
          </template>
          <Link href="/jobs" class="inline-flex items-center justify-center rounded-full border border-white/30 bg-white/10 px-8 py-4 text-base font-black text-white transition hover:bg-white/15">
            {{ t('cta.btn_explore') }}
          </Link>
        </div>
      </div>
    </section>

    <SharedFooter />

    <div class="fixed bottom-4 inset-x-0 px-4 md:hidden z-40">
      <button
        @click="document.getElementById('konten-utama')?.scrollIntoView({ behavior: 'smooth' })"
        class="w-full rounded-full bg-amber-300 py-4 text-base font-black text-slate-900 shadow-2xl"
      >
        🔍 {{ t('job_hero.btn_search') }}
      </button>
    </div>
  </div>
</template>