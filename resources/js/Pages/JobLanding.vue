<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    jobs:              Object,
    filters:           Object,
    categoriesGrouped: Array,
    totalJobs:         Number,
})

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

// 検索フォーム → /jobs へリダイレクト
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

            <Link href="/">
                <img src="/images/logo.png" alt="HRI" class="h-10 w-auto" />
            </Link>

            <!-- デスクトップメニュー -->
            <div class="hidden md:flex items-center gap-6">
                <Link href="/job"
                      :class="['text-sm font-semibold transition',
                               scrolled ? 'text-blue-600' : 'text-white']">
                    Cari Lowongan
                </Link>
                <Link href="/"
                      :class="['text-sm font-medium transition',
                               scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    Untuk Individu
                </Link>
                <Link href="/company"
                      :class="['text-sm font-medium transition',
                               scrolled ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-blue-200']">
                    Untuk Perusahaan
                </Link>

                <template v-if="isLoggedIn">
                    <Link :href="getDashboardUrl()"
                          class="ml-2 px-4 py-2 rounded-full text-sm font-semibold transition"
                          :class="scrolled
                              ? 'bg-blue-600 text-white hover:bg-blue-700'
                              : 'bg-white text-blue-700 hover:bg-blue-50'">
                        Halaman Saya
                    </Link>
                </template>
                <template v-else>
                    <Link href="/login"
                          class="ml-2 px-4 py-2 rounded-full text-sm font-semibold transition border"
                          :class="scrolled
                              ? 'border-gray-300 text-gray-700 hover:bg-gray-50'
                              : 'border-white/40 text-white hover:bg-white/10'">
                        Masuk
                    </Link>
                    <Link href="/register/company"
                          class="px-4 py-2 rounded-full text-sm font-semibold transition"
                          :class="scrolled
                              ? 'bg-blue-600 text-white hover:bg-blue-700'
                              : 'bg-white text-blue-700 hover:bg-blue-50'">
                        Daftar Perusahaan
                    </Link>
                </template>
            </div>

            <!-- モバイルボタン -->
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

        <!-- モバイルメニュー -->
        <div v-if="mobileOpen" class="md:hidden bg-white border-t px-4 py-4 space-y-3 shadow-lg">
            <Link href="/job"      class="block text-blue-600 font-semibold py-2">Cari Lowongan</Link>
            <Link href="/"         class="block text-gray-700 font-medium py-2 hover:text-blue-600">Untuk Individu</Link>
            <Link href="/company"  class="block text-gray-700 font-medium py-2 hover:text-blue-600">Untuk Perusahaan</Link>
            <template v-if="isLoggedIn">
                <Link :href="getDashboardUrl()"
                      class="block w-full text-center bg-blue-600 text-white rounded-full py-2 font-semibold">
                    Halaman Saya
                </Link>
            </template>
            <template v-else>
                <Link href="/login"
                      class="block w-full text-center border border-gray-300 text-gray-700 rounded-full py-2 font-semibold">
                    Masuk
                </Link>
                <Link href="/register/company"
                      class="block w-full text-center bg-blue-600 text-white rounded-full py-2 font-semibold">
                    Daftar Perusahaan
                </Link>
            </template>
        </div>
    </nav>

    <!-- ===== HERO + 検索 ===== -->
    <section class="relative pt-32 pb-20 px-4 overflow-hidden"
             style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #0ea5e9 100%);">
        <!-- 背景ドット -->
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 30% 50%, white 1px, transparent 1px);
                    background-size: 50px 50px;"></div>
        <!-- 下部カーブ -->
        <div class="absolute -bottom-1 left-0 right-0 h-20 bg-gray-50"
             style="clip-path: ellipse(55% 100% at 50% 100%)"></div>

        <div class="relative max-w-4xl mx-auto text-center mb-10">
            <!-- バッジ -->
            <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20
                        rounded-full px-4 py-1.5 text-sm text-blue-100 font-medium mb-5">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Platform Lowongan Kerja dengan CV Terverifikasi
            </div>

            <h1 class="text-4xl md:text-5xl font-black text-white leading-tight mb-4">
                Cari Kerja Jadi<br>
                <span class="text-yellow-300">Lebih Jelas & Cepat</span>
            </h1>
            <p class="text-blue-100 text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
                Temukan lowongan dari perusahaan terverifikasi HRI.
                Lamar dengan CV yang sudah diverifikasi dan tingkatkan peluang diterima.
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
                        <input v-model="searchQuery"
                               type="text"
                               placeholder="Posisi, kata kunci..."
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm
                                      focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                               @keyup.enter="doSearch" />
                    </div>

                    <!-- カテゴリ（階層） -->
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
                            <option value="">Semua Kategori</option>
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
                            <option value="">Semua Lokasi</option>
                            <option v-for="city in indonesianCities" :key="city" :value="city">
                                {{ city }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button @click="doSearch"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold
                                   py-3 rounded-xl transition-all text-sm">
                        🔍 Cari Lowongan
                    </button>
                    <button v-if="hasFilter" @click="searchQuery=''; searchCategory=''; searchLocation='';"
                            class="px-4 py-3 border border-gray-200 text-gray-500
                                   hover:bg-gray-50 rounded-xl text-sm transition">
                        Reset
                    </button>
                </div>

                <p class="text-center text-gray-400 text-xs mt-3">
                    Pencarian akan diarahkan ke halaman daftar lowongan lengkap
                </p>
            </div>
        </div>
    </section>

    <!-- ===== 特徴セクション ===== -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Kenapa HRI Job?</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">
                    Platform Lowongan yang Berbeda
                </h2>
                <p class="text-gray-500 mt-4 max-w-xl mx-auto">
                    HRI menghubungkan kandidat dengan CV terverifikasi dan perusahaan yang membutuhkan rekrutmen berkualitas.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div v-for="f in [
                    { icon: '✅', title: 'Lowongan dari Perusahaan Terverifikasi', desc: 'Semua perusahaan yang memasang lowongan telah melalui proses verifikasi HRI.' },
                    { icon: '🏅', title: 'Lamar dengan CV Terverifikasi', desc: 'CV yang sudah diverifikasi meningkatkan kepercayaan perekrut sejak tahap pertama.' },
                    { icon: '⚡', title: 'Proses Seleksi Lebih Cepat', desc: 'Perusahaan dapat langsung melihat HRI Score dan fokus pada kandidat berkualitas.' },
                ]" :key="f.title"
                     class="group bg-white hover:bg-blue-600 rounded-2xl p-8
                            transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-default
                            border border-gray-100">
                    <div class="text-4xl mb-4">{{ f.icon }}</div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-white mb-3 transition-colors">
                        {{ f.title }}
                    </h3>
                    <p class="text-gray-500 group-hover:text-blue-100 text-sm leading-relaxed transition-colors">
                        {{ f.desc }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== カテゴリ一覧 ===== -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <span class="text-blue-600 font-semibold text-sm uppercase tracking-widest">Kategori</span>
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-2">
                    Jelajahi Berdasarkan Bidang
                </h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <button v-for="parent in categoriesGrouped" :key="parent.id"
                        @click="router.get('/jobs', { category: parent.id })"
                        class="bg-gray-50 hover:bg-blue-600 border border-gray-100
                               rounded-xl p-4 text-center transition-all hover:shadow-md
                               hover:-translate-y-0.5 group cursor-pointer">
                    <div class="text-sm font-semibold text-gray-700 group-hover:text-white transition-colors leading-snug">
                        {{ parent.name }}
                    </div>
                    <div class="text-xs text-gray-400 group-hover:text-blue-100 mt-1 transition-colors">
                        {{ parent.children?.length ?? 0 }} bidang
                    </div>
                </button>
            </div>

            <div class="text-center mt-10">
                <Link href="/jobs"
                      class="inline-block bg-blue-600 hover:bg-blue-700 text-white
                             font-bold px-10 py-4 rounded-full transition shadow-lg">
                    Lihat Semua Lowongan →
                </Link>
            </div>
        </div>
    </section>

    <!-- ===== 求人登録企業向けCTA ===== -->
    <section class="py-16 bg-gray-50 border-t border-gray-100">
        <div class="max-w-5xl mx-auto px-4 grid md:grid-cols-2 gap-10 items-center">
            <div>
                <span class="inline-block bg-orange-100 text-orange-700 text-xs font-bold
                             px-3 py-1 rounded-full mb-4 border border-orange-200">
                    Untuk Perusahaan
                </span>
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 mb-4">
                    Pasang Lowongan &<br>Temukan Kandidat Terbaik
                </h2>
                <p class="text-gray-500 mb-6 text-sm leading-relaxed">
                    Posting lowongan gratis 30 hari pertama. Akses ribuan kandidat
                    dengan CV yang sudah diverifikasi HRI secara profesional.
                </p>
                <Link href="/company"
                      class="inline-block bg-orange-500 hover:bg-orange-600 text-white
                             font-bold px-8 py-3 rounded-full transition">
                    Daftar Perusahaan →
                </Link>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-8 text-center">
                <p class="text-5xl mb-4">🏢</p>
                <p class="text-4xl font-extrabold text-indigo-600 mb-2">30 Hari</p>
                <p class="text-gray-600 font-semibold mb-1">Posting Lowongan Gratis</p>
                <p class="text-gray-400 text-sm">untuk perusahaan yang baru bergabung</p>
            </div>
        </div>
    </section>

    <!-- ===== 個人向けCTA ===== -->
    <section class="py-20" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #0ea5e9 100%);">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-6">
                Belum punya CV Terverifikasi?
            </h2>
            <p class="text-blue-100 mb-10 leading-relaxed">
                Daftar gratis sekarang, lengkapi data CV, dan ajukan verifikasi HRI
                untuk meningkatkan peluang diterima kerja.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <template v-if="isLoggedIn">
                    <Link :href="getDashboardUrl()"
                          class="px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        Halaman Saya →
                    </Link>
                </template>
                <template v-else>
                    <Link href="/login"
                          class="px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold
                                 rounded-full text-lg shadow-xl transition-all hover:scale-105">
                        👤 Registrasi Gratis
                    </Link>
                </template>
                <Link href="/jobs"
                      class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold
                             rounded-full text-lg border border-white/30 transition-all">
                    🔎 Lihat Semua Lowongan
                </Link>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-start gap-8">
                <div>
                    <Link href="/">
                        <img src="/images/logo.png" alt="HRI" class="h-8 w-auto mb-3 opacity-80" />
                    </Link>
                    <p class="text-sm max-w-xs leading-relaxed">
                        Human Resource Integration — Sistem verifikasi CV pihak ketiga
                        untuk rekrutmen yang lebih terpercaya.
                    </p>
                </div>
                <div class="flex gap-12">
                    <div>
                        <div class="text-white font-semibold mb-3 text-sm">Navigasi</div>
                        <div class="space-y-2 text-sm">
                            <Link href="/"        class="block hover:text-white transition">Untuk Individu</Link>
                            <Link href="/company" class="block hover:text-white transition">Untuk Perusahaan</Link>
                            <Link href="/jobs"    class="block hover:text-white transition">Cari Lowongan</Link>
                            <Link href="/login"   class="block hover:text-white transition">Login</Link>
                        </div>
                    </div>
                    <div>
                        <div class="text-white font-semibold mb-3 text-sm">Legal</div>
                        <div class="space-y-2 text-sm">
                            <a href="https://hri-check.com/privacy-applicant/" target="_blank"
                               class="block hover:text-white transition">Kebijakan Privasi</a>
                            <a href="https://hri-check.com/important-policies/" target="_blank"
                               class="block hover:text-white transition">Kebijakan Layanan</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-10 pt-6 border-t border-gray-800 text-xs text-center">
                © {{ new Date().getFullYear() }} HRI Indonesia. All rights reserved.
                Data diproses sesuai UU PDP Indonesia.
            </div>
        </div>
    </footer>
</template>