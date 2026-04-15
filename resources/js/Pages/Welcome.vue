<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t } = useI18n()

// ログイン状態
const auth       = usePage().props.auth
const isLoggedIn = !!auth?.user
const roleType   = auth?.user?.role_type

const getDashboardUrl = () => {
    if (roleType === 'applicant') return '/applicant/dashboard'
    if (roleType === 'company')   return '/company/dashboard'
    if (['admin_user', 'investigator_user'].includes(roleType)) return '/admin/dashboard'
    return '/dashboard'
}

// ナビ スクロール制御
const scrolled     = ref(false)
const mobileOpen   = ref(false)
const handleScroll = () => { scrolled.value = window.scrollY > 50 }
onMounted(()  => window.addEventListener('scroll', handleScroll))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))

const verifiedItems = [
    { label: 'Riwayat Pendidikan', note: 'Ijazah, institusi, periode' },
    { label: 'Pengalaman Kerja',   note: 'Perusahaan, jabatan, masa kerja' },
    { label: 'Sertifikasi',        note: 'Lembaga penerbit, tanggal' },
    { label: 'Informasi Dasar',    note: 'Identitas, dokumen resmi' },
]

const testimonials = [
    { icon: '✈️', name: 'Sarah W.',  role: 'Marketing Specialist',
      text: 'Setelah CV diverifikasi HRI, proses seleksi terasa lebih lancar dan saya berhasil memperoleh pekerjaan di perusahaan luar negeri.' },
    { icon: '👩‍🎓', name: 'Rina S.',  role: 'Fresh Graduate',
      text: 'Profil lebih jelas, peluang interview jadi lebih terarah. Saya percaya diri saat seleksi.' },
    { icon: '👨‍💻', name: 'Andi P.',  role: 'Software Engineer',
      text: 'Kontribusi terverifikasi, dipercaya memimpin tim. Peran dan tanggung jawab saya meningkat.' },
]
</script>

<template>
    <Head title="HRI — CV Terverifikasi. Rekrutmen Lebih Meyakinkan." />

    <!-- ===== NAVBAR ===== -->
    <nav :class="['fixed top-0 inset-x-0 z-50 transition-all duration-300',
                  scrolled ? 'bg-white shadow-md' : 'bg-transparent']">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
            <Link href="/">
                <img src="/images/logo.png" alt="HRI" class="h-10 w-auto" />
            </Link>

            <div class="hidden md:flex items-center gap-5">
                <Link href="/job"
                      :class="['text-sm font-medium transition',
                               scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    {{ t('nav.search_job') }}
                </Link>
                <a href="#features"
                   :class="['text-sm font-medium transition',
                            scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    {{ t('nav.service') }}
                </a>
                <a href="#pricing"
                   :class="['text-sm font-medium transition',
                            scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    {{ t('nav.price') }}
                </a>
                <Link href="/company"
                      :class="['text-sm font-medium transition',
                               scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    {{ t('nav.for_company') }}
                </Link>

                <!-- 言語切替 -->
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
                          class="ml-1 px-4 py-2 rounded-full text-sm font-semibold transition"
                          :class="scrolled ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-white text-blue-700 hover:bg-blue-50'">
                        {{ t('nav.login') }}
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
            <Link href="/job"     class="block text-gray-700 font-medium py-2">{{ t('nav.search_job') }}</Link>
            <a    href="#features" class="block text-gray-700 font-medium py-2" @click="mobileOpen=false">{{ t('nav.service') }}</a>
            <a    href="#pricing"  class="block text-gray-700 font-medium py-2" @click="mobileOpen=false">{{ t('nav.price') }}</a>
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
                      class="block w-full text-center bg-blue-600 text-white rounded-full py-2 font-semibold">
                    {{ t('nav.login') }}
                </Link>
            </template>
        </div>
    </nav>

    <!-- ===== HERO ===== -->
    <section class="relative min-h-screen flex items-center overflow-hidden bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px),
                                      radial-gradient(circle at 80% 20%, white 1px, transparent 1px);
                    background-size: 60px 60px;"></div>
        <div class="absolute -bottom-1 left-0 right-0 h-24 bg-white"
             style="clip-path: ellipse(55% 100% at 50% 100%)"></div>

        <div class="relative max-w-6xl mx-auto px-4 py-32 text-center">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/20
                        rounded-full px-4 py-1.5 text-sm text-blue-100 font-medium mb-6">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                {{ t('hero.badge') }}
            </div>

            <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
                {{ t('hero.title1') }}<br>
                <span class="text-yellow-300">{{ t('hero.title2') }}</span> {{ t('hero.title3') }}
            </h1>

            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto mb-10 leading-relaxed">
                {{ t('hero.desc') }}
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
                <template v-if="isLoggedIn">
                    <Link :href="getDashboardUrl()"
                          class="px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        👤 {{ t('hero.cta_mypage') }}
                    </Link>
                </template>
                <template v-else>
                    <Link href="/login"
                          class="px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        👤 {{ t('hero.cta_register') }}
                    </Link>
                </template>
                <Link href="/job"
                      class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold
                             rounded-full text-lg border border-white/30 backdrop-blur transition-all">
                    🔎 {{ t('hero.cta_jobs') }}
                </Link>
            </div>

            <div class="flex flex-wrap justify-center gap-3">
                <span class="bg-white/10 border border-white/20 text-blue-100 text-sm px-4 py-2 rounded-full backdrop-blur">
                    ✅ {{ t('hero.badge1') }}
                </span>
                <span class="bg-white/10 border border-white/20 text-blue-100 text-sm px-4 py-2 rounded-full backdrop-blur">
                    🏅 {{ t('hero.badge2') }}
                </span>
                <span class="bg-white/10 border border-white/20 text-blue-100 text-sm px-4 py-2 rounded-full backdrop-blur">
                    🆔 {{ t('hero.badge3') }}
                </span>
            </div>
            <p class="text-blue-300 text-xs mt-8">{{ t('hero.note') }}</p>
        </div>
    </section>

    <!-- ===== FITUR ===== -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('features.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">{{ t('features.title') }}</h2>
                <p class="text-gray-500 mt-4 max-w-2xl mx-auto leading-relaxed">{{ t('features.desc') }}</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div v-for="f in [
                    { icon: '🔍', title: t('features.f1_title'), desc: t('features.f1_desc') },
                    { icon: '⭐', title: t('features.f2_title'), desc: t('features.f2_desc') },
                    { icon: '🏆', title: t('features.f3_title'), desc: t('features.f3_desc') },
                ]" :key="f.title"
                     class="group bg-gray-50 hover:bg-blue-600 rounded-2xl p-8 transition-all duration-300
                            hover:shadow-xl hover:-translate-y-1 cursor-default">
                    <div class="text-4xl mb-4">{{ f.icon }}</div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-white mb-3 transition-colors">{{ f.title }}</h3>
                    <p class="text-gray-500 group-hover:text-blue-100 text-sm leading-relaxed transition-colors">{{ f.desc }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('why.label') }}</span>
                    <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2 mb-6">{{ t('why.title') }}</h2>
                    <p class="text-gray-600 leading-relaxed mb-8">{{ t('why.desc') }}</p>
                    <ul class="space-y-4">
                        <li v-for="item in [
                            { icon: '✅', text: t('why.w1') },
                            { icon: '🌏', text: t('why.w2') },
                            { icon: '⚡', text: t('why.w3') },
                        ]" :key="item.text" class="flex gap-3 items-start">
                            <span class="text-xl flex-shrink-0">{{ item.icon }}</span>
                            <span class="text-gray-700 text-sm leading-relaxed">{{ item.text }}</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-2xl">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-bold text-sm">HRI</div>
                        <div>
                            <div class="font-bold text-sm">{{ t('why.card_title') }}</div>
                            <div class="text-blue-200 text-xs">{{ t('why.card_sub') }}</div>
                        </div>
                        <div class="ml-auto bg-green-400 text-green-900 text-xs font-bold px-3 py-1 rounded-full">VERIFIED</div>
                    </div>
                    <div class="space-y-3">
                        <div v-for="item in verifiedItems" :key="item.label"
                             class="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3">
                            <div class="w-5 h-5 rounded-full bg-green-400 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium">{{ item.label }}</div>
                                <div class="text-blue-200 text-xs">{{ item.note }}</div>
                            </div>
                            <span class="ml-auto text-green-300 text-xs font-semibold">✓ Verified</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== STEPS ===== -->
    <section class="py-20 bg-blue-900">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="text-yellow-300 font-semibold text-sm uppercase tracking-widest">{{ t('steps.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-white mt-2">{{ t('steps.title') }}</h2>
            </div>
            <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-6">
                <div v-for="(step, i) in [
                    { num: '01', title: t('steps.s1_title'), desc: t('steps.s1_desc') },
                    { num: '02', title: t('steps.s2_title'), desc: t('steps.s2_desc') },
                    { num: '03', title: t('steps.s3_title'), desc: t('steps.s3_desc') },
                    { num: '04', title: t('steps.s4_title'), desc: t('steps.s4_desc') },
                ]" :key="i"
                     class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 hover:bg-white/20 transition-all">
                    <div class="text-yellow-300 text-4xl font-black mb-3">{{ step.num }}</div>
                    <h3 class="text-white font-bold mb-2">{{ step.title }}</h3>
                    <p class="text-blue-200 text-sm leading-relaxed">{{ step.desc }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TESTIMONIALS ===== -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('testimonials.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">{{ t('testimonials.title') }}</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div v-for="t2 in testimonials" :key="t2.name"
                     class="bg-gray-50 rounded-2xl p-8 border border-gray-100 hover:shadow-lg transition-all">
                    <div class="text-3xl mb-4">{{ t2.icon }}</div>
                    <p class="text-gray-700 text-sm leading-relaxed mb-6 italic">"{{ t2.text }}"</p>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-700 text-sm">
                            {{ t2.name[0] }}
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ t2.name }}</div>
                            <div class="text-gray-400 text-xs">{{ t2.role }}</div>
                        </div>
                    </div>
                    <p class="text-gray-300 text-xs mt-3">{{ t('testimonials.note') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== PRICING ===== -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('pricing.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">{{ t('pricing.title') }}</h2>
                <p class="text-gray-500 mt-4">{{ t('pricing.desc') }}</p>
            </div>
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <div v-for="(item, i) in [
                    { label: t('pricing.p1_label'), price: t('pricing.p1_price'), note: t('pricing.p1_note') },
                    { label: t('pricing.p2_label'), price: t('pricing.p2_price'), note: t('pricing.p2_note') },
                    { label: t('pricing.p3_label'), price: t('pricing.p3_price'), note: t('pricing.p3_note') },
                ]" :key="i"
                     :class="['flex items-center justify-between px-8 py-5',
                              i < 2 ? 'border-b border-gray-100' : '',
                              i === 0 ? 'bg-blue-50' : '']">
                    <div>
                        <div class="font-semibold text-gray-900">{{ item.label }}</div>
                        <div class="text-gray-400 text-xs mt-0.5">{{ item.note }}</div>
                    </div>
                    <div :class="['font-black text-lg',
                                  item.price.includes('Gratis') || item.price.includes('無料') || item.price.includes('무료') || item.price.includes('Free')
                                      ? 'text-green-600' : 'text-blue-700']">
                        {{ item.price }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== 企業向け ===== -->
    <section class="py-20 bg-white border-t border-gray-100">
        <div class="max-w-5xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
            <div>
                <span class="inline-block bg-orange-100 text-orange-700 text-xs font-bold
                             px-3 py-1 rounded-full mb-4 border border-orange-200">
                    {{ t('company_section.badge') }}
                </span>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-4">{{ t('company_section.title') }}</h2>
                <p class="text-gray-500 mb-6 leading-relaxed">{{ t('company_section.desc') }}</p>
                <ul class="space-y-3 mb-8">
                    <li v-for="b in [t('company_section.b1'), t('company_section.b2'), t('company_section.b3')]"
                        :key="b" class="flex items-center gap-3 text-sm text-gray-700">
                        <span class="text-green-500 font-bold flex-shrink-0">✓</span>{{ b }}
                    </li>
                </ul>
                <Link href="/company"
                      class="inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-full transition">
                    {{ t('company_section.cta') }}
                </Link>
            </div>
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl p-8 text-center">
                <p class="text-5xl mb-4">🏢</p>
                <p class="text-4xl font-extrabold text-indigo-600 mb-2">{{ t('company_section.days') }}</p>
                <p class="text-gray-600 font-semibold mb-1">{{ t('company_section.days_label') }}</p>
                <p class="text-gray-400 text-sm">{{ t('company_section.days_note') }}</p>
            </div>
        </div>
    </section>

    <!-- ===== CTA ===== -->
    <section class="py-24 bg-gradient-to-r from-blue-700 to-indigo-700">
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
                        {{ t('cta.btn_register') }}
                    </Link>
                </template>
                <Link href="/company"
                      class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold
                             rounded-full text-lg border border-white/30 transition-all">
                    🏢 {{ t('cta.btn_company') }}
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
                        <div class="space-y-2 text-sm">
                            <Link href="/job"     class="block hover:text-white transition">{{ t('nav.search_job') }}</Link>
                            <Link href="/company" class="block hover:text-white transition">{{ t('nav.for_company') }}</Link>
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
                © {{ new Date().getFullYear() }} HRI Indonesia. {{ t('footer.copyright') }}
            </div>
        </div>
    </footer>
</template>