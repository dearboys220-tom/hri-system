<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t } = useI18n()

const props = defineProps({
    publishedJobCount: Number,
})

const auth       = usePage().props.auth
const isLoggedIn = !!auth?.user
const roleType   = auth?.user?.role_type

const getDashboardUrl = () => {
    if (roleType === 'company')   return '/company/dashboard'
    if (['admin_user', 'investigator_user'].includes(roleType)) return '/admin/dashboard'
    if (roleType === 'applicant') return '/applicant/dashboard'
    return '/dashboard'
}

const scrolled     = ref(false)
const mobileOpen   = ref(false)
const handleScroll = () => { scrolled.value = window.scrollY > 50 }
onMounted(()  => window.addEventListener('scroll', handleScroll))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))

// ===== i18n対応コンテンツ =====
const investigationItems = computed(() => [
    { icon: '🎓', title: t('investigation.i1_title'), desc: t('investigation.i1_desc') },
    { icon: '💼', title: t('investigation.i2_title'), desc: t('investigation.i2_desc') },
    { icon: '🏆', title: t('investigation.i3_title'), desc: t('investigation.i3_desc') },
    { icon: '📱', title: t('investigation.i4_title'), desc: t('investigation.i4_desc') },
])

const benefits = computed(() => [
    { value: '80%', label: t('benefits.b1') },
    { value: '3x',  label: t('benefits.b2') },
    { value: '100', label: t('benefits.b3') },
])

const testimonials = computed(() => [
    { icon: '📊', name: 'PT Maju Bersama',     role: t('company_testimonials.role1'), text: t('company_testimonials.text1') },
    { icon: '📈', name: 'CV Karya Nusantara',  role: t('company_testimonials.role2'), text: t('company_testimonials.text2') },
    { icon: '🏪', name: 'PT Retail Indonesia', role: t('company_testimonials.role3'), text: t('company_testimonials.text3') },
])

const pricingItems = computed(() => [
    { label: t('company_pricing.p1_label'), price: t('company_pricing.p1_price'), note: t('company_pricing.p1_note') },
    { label: t('company_pricing.p2_label'), price: t('company_pricing.p2_price'), note: t('company_pricing.p2_note') },
    { label: t('company_pricing.p3_label'), price: t('company_pricing.p3_price'), note: t('company_pricing.p3_note') },
    { label: t('company_pricing.p4_label'), price: t('company_pricing.p4_price'), note: t('company_pricing.p4_note') },
])

const featureItems = computed(() => [
    { icon: '🔍', title: t('features.f1_title'), desc: t('features.f1_desc') },
    { icon: '⭐', title: t('features.f2_title'), desc: t('features.f2_desc') },
    { icon: '🏆', title: t('features.f3_title'), desc: t('features.f3_desc') },
    { icon: '📋', title: t('features.f4_title'), desc: t('features.f4_desc') },
    { icon: '⚡', title: t('features.f5_title'), desc: t('features.f5_desc') },
    { icon: '🔒', title: t('features.f6_title'), desc: t('features.f6_desc') },
])

const stepItems = computed(() => [
    { num: '01', title: t('company_steps.s1_title'), desc: t('company_steps.s1_desc') },
    { num: '02', title: t('company_steps.s2_title'), desc: t('company_steps.s2_desc') },
    { num: '03', title: t('company_steps.s3_title'), desc: t('company_steps.s3_desc') },
    { num: '04', title: t('company_steps.s4_title'), desc: t('company_steps.s4_desc') },
])
</script>

<template>
    <Head :title="t('company_seo.title')" />

    <!-- ===== NAVBAR ===== -->
    <nav :class="['fixed top-0 inset-x-0 z-50 transition-all duration-300',
                  scrolled ? 'bg-white shadow-md' : 'bg-transparent']">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
            <Link href="/"><img src="/images/logo.png" alt="HRI" class="h-10 w-auto" /></Link>

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
                <Link href="/"
                      :class="['text-sm font-medium transition',
                               scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    {{ t('nav.for_individual') }}
                </Link>

                <!-- ↓ スクロール時の言語切替ボタン色修正 -->
                <div :class="scrolled ? '[&_button]:!text-gray-700 [&_button]:!border-gray-300 [&_button:hover]:!bg-gray-50' : ''">
                    <LanguageSwitcher />
                </div>

                <template v-if="isLoggedIn">
                    <Link :href="getDashboardUrl()"
                          class="ml-1 px-4 py-2 rounded-full text-sm font-semibold transition border"
                          :class="scrolled ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-white text-blue-700 hover:bg-blue-50'">
                        Dashboard
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
            <Link href="/job"      class="block text-gray-700 font-medium py-2">{{ t('nav.search_job') }}</Link>
            <a    href="#features" class="block text-gray-700 font-medium py-2" @click="mobileOpen=false">{{ t('nav.service') }}</a>
            <a    href="#pricing"  class="block text-gray-700 font-medium py-2" @click="mobileOpen=false">{{ t('nav.price') }}</a>
            <Link href="/"         class="block text-gray-700 font-medium py-2">{{ t('nav.for_individual') }}</Link>
            <div class="py-2"><LanguageSwitcher :dark="false" /></div>
            <template v-if="isLoggedIn">
                <Link :href="getDashboardUrl()"
                      class="block w-full text-center bg-blue-600 text-white rounded-full py-2 font-semibold">
                    Dashboard
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

    <!-- ===== HERO ===== -->
    <section class="relative min-h-screen flex items-center overflow-hidden"
             style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #312e81 100%);">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px),
                                      radial-gradient(circle at 80% 20%, white 1px, transparent 1px);
                    background-size: 60px 60px;"></div>
        <div class="absolute -bottom-1 left-0 right-0 h-24 bg-white"
             style="clip-path: ellipse(55% 100% at 50% 100%)"></div>

        <div class="relative max-w-6xl mx-auto px-4 py-32 text-center">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/20
                        rounded-full px-4 py-1.5 text-sm text-blue-100 font-medium mb-6">
                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                {{ t('company_hero.badge') }}
            </div>

            <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
                {{ t('company_hero.title1') }}<br>
                <span class="text-orange-300">{{ t('company_hero.title2') }}</span><br>
                {{ t('company_hero.title3') }}
            </h1>

            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto mb-10 leading-relaxed">
                {{ t('company_hero.desc') }}
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-14">
                <template v-if="isLoggedIn && roleType === 'company'">
                    <Link :href="getDashboardUrl()"
                          class="px-8 py-4 bg-orange-400 hover:bg-orange-300 text-white font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        🏢 {{ t('company_hero.cta_dashboard') }}
                    </Link>
                </template>
                <template v-else>
                    <Link href="/register/company"
                          class="px-8 py-4 bg-orange-400 hover:bg-orange-300 text-white font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        🏢 {{ t('company_hero.cta_register') }}
                    </Link>
                </template>
                <a href="#features"
                   class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold
                          rounded-full text-lg border border-white/30 backdrop-blur transition-all">
                    {{ t('nav.service') }} ↓
                </a>
            </div>

            <div class="flex flex-wrap justify-center gap-6">
                <div v-for="b in benefits" :key="b.label"
                     class="bg-white/10 border border-white/20 backdrop-blur rounded-2xl px-6 py-3 text-center">
                    <div class="text-2xl font-black text-orange-300">{{ b.value }}</div>
                    <div class="text-blue-200 text-xs mt-0.5">{{ b.label }}</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FITUR ===== -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('features.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">{{ t('features.title') }}</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div v-for="f in featureItems" :key="f.title"
                     class="group bg-gray-50 hover:bg-blue-600 rounded-2xl p-8 transition-all duration-300
                            hover:shadow-xl hover:-translate-y-1 cursor-default">
                    <div class="text-4xl mb-4">{{ f.icon }}</div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-white mb-3 transition-colors">{{ f.title }}</h3>
                    <p class="text-gray-500 group-hover:text-blue-100 text-sm leading-relaxed transition-colors">{{ f.desc }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== 調査項目 ===== -->
    <section class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('investigation.label') }}</span>
                    <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2 mb-6">
                        {{ t('investigation.title') }}
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-8">
                        {{ t('investigation.desc') }}
                    </p>
                    <Link href="/register/company"
                          class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-full transition">
                        {{ t('company_hero.cta_register') }} →
                    </Link>
                </div>
                <div class="space-y-4">
                    <div v-for="item in investigationItems" :key="item.title"
                         class="flex items-start gap-4 bg-white rounded-2xl p-5 shadow-sm border border-gray-100
                                hover:shadow-md hover:border-blue-200 transition-all">
                        <div class="text-3xl flex-shrink-0">{{ item.icon }}</div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">{{ item.title }}</h3>
                            <p class="text-gray-500 text-sm leading-relaxed">{{ item.desc }}</p>
                        </div>
                        <div class="ml-auto flex-shrink-0">
                            <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">✓ Verified</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== STEPS ===== -->
    <section class="py-20" style="background-color: #0f172a;">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="text-orange-300 font-semibold text-sm uppercase tracking-widest">{{ t('steps.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-white mt-2">{{ t('steps.title') }}</h2>
            </div>
            <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-6">
                <div v-for="step in stepItems" :key="step.num"
                     class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 hover:bg-white/20 transition-all">
                    <div class="text-orange-300 text-4xl font-black mb-3">{{ step.num }}</div>
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
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">{{ t('company_testimonials.label') }}</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">{{ t('company_testimonials.title') }}</h2>
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
                <div v-for="(item, i) in pricingItems" :key="i"
                     :class="['flex items-center justify-between px-8 py-5',
                              i < pricingItems.length - 1 ? 'border-b border-gray-100' : '',
                              i === 0 ? 'bg-orange-50' : '']">
                    <div>
                        <div class="font-semibold text-gray-900">{{ item.label }}</div>
                        <div class="text-gray-400 text-xs mt-0.5">{{ item.note }}</div>
                    </div>
                    <div :class="['font-black text-lg',
                                  item.price.includes('Gratis') || item.price.includes('無料') || item.price.includes('무료') || item.price.includes('Free') ? 'text-green-600' : 'text-blue-700']">
                        {{ item.price }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA ===== -->
    <section class="py-24" style="background: linear-gradient(to right, #1e293b, #1e3a8a);">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-6">
                {{ t('company_cta.title') }}
            </h2>
            <p class="text-blue-100 mb-10 leading-relaxed">
                {{ t('company_cta.desc') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <template v-if="isLoggedIn && roleType === 'company'">
                    <Link :href="getDashboardUrl()"
                          class="px-8 py-4 bg-orange-400 hover:bg-orange-300 text-white font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        {{ t('company_hero.cta_dashboard') }} →
                    </Link>
                </template>
                <template v-else>
                    <Link href="/register/company"
                          class="px-8 py-4 bg-orange-400 hover:bg-orange-300 text-white font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        {{ t('company_hero.cta_register') }}
                    </Link>
                </template>
                <Link href="/"
                      class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold
                             rounded-full text-lg border border-white/30 transition-all">
                    👤 {{ t('nav.for_individual') }}
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
                            <Link href="/"                 class="block hover:text-white transition">{{ t('nav.for_individual') }}</Link>
                            <Link href="/job"              class="block hover:text-white transition">{{ t('nav.search_job') }}</Link>
                            <Link href="/register/company" class="block hover:text-white transition">{{ t('nav.register_company') }}</Link>
                            <Link href="/login"            class="block hover:text-white transition">{{ t('nav.login') }}</Link>
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