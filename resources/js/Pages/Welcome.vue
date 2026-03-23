<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';

const auth = usePage().props.auth;
const isLoggedIn = !!auth?.user;
const roleType = auth?.user?.role_type;

const getDashboardUrl = () => {
    if (roleType === 'applicant') return '/applicant/dashboard';
    if (roleType === 'company') return '/company/dashboard';
    if (['admin_user', 'investigator_user', 'reviewer_user'].includes(roleType)) return '/admin/dashboard';
    return '/dashboard';
};

const steps = [
    { icon: '📝', title: 'Registrasi & Buat Akun', desc: 'Daftar gratis dengan Google. Tanpa kartu kredit.' },
    { icon: '📄', title: 'Isi CV & Verifikasi Identitas', desc: 'Lengkapi data diri, pendidikan, pengalaman kerja.' },
    { icon: '🔍', title: 'Verifikasi 3 Tahap', desc: 'Tim HRI memeriksa fakta secara independen.' },
    { icon: '✅', title: 'Dapatkan HRI-ID', desc: 'CV tersertifikasi siap digunakan untuk melamar.' },
];
</script>

<template>
    <Head title="HRICheck – CV Terverifikasi" />
    <div class="min-h-screen bg-white text-gray-900">

        <!-- ナビバー -->
        <nav class="border-b border-gray-100 px-6 py-4 sticky top-0 bg-white z-30">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <Link href="/">
                    <img src="/images/logo.png" alt="HRI" class="h-10 w-auto" />
                </Link>
                <div class="flex items-center gap-3">
                    <!-- 求人一覧リンク -->
                    <Link href="/jobs" class="text-sm text-gray-600 hover:text-indigo-600 font-medium transition">
                        Lowongan Kerja
                    </Link>
                    <template v-if="isLoggedIn">
                        <Link :href="getDashboardUrl()"
                              class="bg-indigo-600 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-indigo-700 transition">
                            Halaman Saya
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login"
                              class="border border-gray-300 text-gray-700 px-5 py-2 rounded-full text-sm font-semibold hover:bg-gray-50 transition">
                            Masuk
                        </Link>
                        <Link href="/register/company"
                              class="bg-indigo-600 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-indigo-700 transition">
                            Daftar Perusahaan
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- ヒーロー -->
        <section class="py-20 px-6 text-center bg-gradient-to-b from-indigo-50 to-white">
            <div class="max-w-3xl mx-auto">
                <span class="inline-block bg-indigo-100 text-indigo-700 text-sm font-bold px-4 py-1.5 rounded-full mb-6 border border-indigo-200">
                    CV Terverifikasi oleh Pihak Ketiga
                </span>
                <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight text-gray-900 mb-6">
                    CV Terverifikasi.<br>
                    <span class="text-indigo-600">Rekrutmen Lebih Pasti.</span>
                </h1>
                <p class="text-lg text-gray-500 mb-10 max-w-2xl mx-auto leading-relaxed">
                    HRI memeriksa fakta pendidikan, pekerjaan, dan sertifikasi secara independen.
                    Tingkatkan peluang lolos seleksi dengan CV yang kredibel & siap pakai.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link href="/login"
                          class="bg-indigo-600 text-white px-8 py-4 rounded-full font-bold text-base hover:bg-indigo-700 transition shadow-lg">
                        👤 Registrasi Individu (Gratis)
                    </Link>
                    <Link href="/jobs"
                          class="border border-indigo-300 text-indigo-600 px-8 py-4 rounded-full font-bold text-base hover:bg-indigo-50 transition">
                        🔎 Lihat Lowongan Kerja
                    </Link>
                </div>
                <p class="text-sm text-gray-400 mt-4">Tidak ada biaya pendaftaran. Data diproses sesuai UU PDP.</p>
                <div class="flex flex-wrap justify-center gap-3 mt-8">
                    <span class="bg-white border border-gray-200 text-gray-700 text-sm px-4 py-2 rounded-xl shadow-sm">✅ Verifikasi 3 tahap</span>
                    <span class="bg-white border border-gray-200 text-gray-700 text-sm px-4 py-2 rounded-xl shadow-sm">🏅 CV dengan tanda resmi HRI</span>
                    <span class="bg-white border border-gray-200 text-gray-700 text-sm px-4 py-2 rounded-xl shadow-sm">🆔 HRI-ID siap pakai</span>
                </div>
            </div>
        </section>

        <!-- 求人セクション -->
        <section class="py-16 px-6 bg-white border-t border-gray-100">
            <div class="max-w-5xl mx-auto text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Lowongan Kerja Terbaru</h2>
                <p class="text-gray-400 mb-8">Temukan pekerjaan yang sesuai dengan keahlian dan pengalamanmu</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
                    <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 text-left">
                        <p class="text-3xl mb-3">💼</p>
                        <h3 class="font-bold text-gray-800 mb-1">Berbagai Bidang</h3>
                        <p class="text-sm text-gray-500">Akuntansi, IT, Marketing, HR, dan banyak lagi.</p>
                    </div>
                    <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 text-left">
                        <p class="text-3xl mb-3">🏢</p>
                        <h3 class="font-bold text-gray-800 mb-1">Perusahaan Terverifikasi</h3>
                        <p class="text-sm text-gray-500">Semua perusahaan telah melalui proses verifikasi HRI.</p>
                    </div>
                    <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 text-left">
                        <p class="text-3xl mb-3">🚀</p>
                        <h3 class="font-bold text-gray-800 mb-1">Lamar Lebih Mudah</h3>
                        <p class="text-sm text-gray-500">CV tersertifikasi meningkatkan peluang diterima.</p>
                    </div>
                </div>
                <Link href="/jobs"
                      class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-10 py-4 rounded-full transition shadow-lg">
                    Lihat Semua Lowongan →
                </Link>
            </div>
        </section>

        <!-- 特徴 -->
        <section class="py-20 px-6 bg-gray-50">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-3">Fitur HRI</h2>
                <p class="text-center text-gray-400 mb-12">Sistem verifikasi CV pihak ketiga untuk meningkatkan kredibilitas</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm">
                        <div class="w-14 h-14 bg-indigo-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">🔍</div>
                        <h3 class="font-bold text-gray-800 mb-2">Investigasi Profesional</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Validasi 3 tahap oleh Tim Investigasi, Tim Penilai, dan Tim Administrasi.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm">
                        <div class="w-14 h-14 bg-indigo-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">⭐</div>
                        <h3 class="font-bold text-gray-800 mb-2">Penilaian 100 Poin</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Evaluasi objektif dan transparan sehingga perekrut mudah memahami kualitas kandidat.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm">
                        <div class="w-14 h-14 bg-indigo-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">🏅</div>
                        <h3 class="font-bold text-gray-800 mb-2">CV Terverifikasi Resmi</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">CV yang lulus pemeriksaan ditandai resmi agar kredibilitas mudah dikenali perusahaan.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 認証フロー -->
        <section class="py-20 px-6 bg-indigo-50">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-3">Cara Kerja HRI</h2>
                <p class="text-center text-gray-400 mb-12">4 langkah sederhana menuju CV tersertifikasi</p>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                    <div v-for="(step, i) in steps" :key="i"
                         class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <div class="text-3xl mb-3">{{ step.icon }}</div>
                        <div class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-xs mb-3">
                            {{ i + 1 }}
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2 text-sm">{{ step.title }}</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 企業向けセクション -->
        <section class="py-20 px-6 bg-white border-t border-gray-100">
            <div class="max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="inline-block bg-orange-100 text-orange-700 text-xs font-bold px-3 py-1 rounded-full mb-4 border border-orange-200">
                        Untuk Perusahaan
                    </span>
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-4">
                        Rekrut Kandidat<br>yang Sudah Terverifikasi
                    </h2>
                    <p class="text-gray-500 mb-6 leading-relaxed">
                        Pasang lowongan kerja dan temukan kandidat dengan CV yang sudah diverifikasi secara independen.
                        Hemat waktu rekrutmen dengan data yang akurat.
                    </p>
                    <div class="space-y-3 mb-8">
                        <div class="flex items-center gap-3 text-sm text-gray-700">
                            <span class="text-green-500 font-bold">✓</span> Pasang lowongan gratis untuk 30 hari pertama
                        </div>
                        <div class="flex items-center gap-3 text-sm text-gray-700">
                            <span class="text-green-500 font-bold">✓</span> Akses CV tersertifikasi dengan HRI Score
                        </div>
                        <div class="flex items-center gap-3 text-sm text-gray-700">
                            <span class="text-green-500 font-bold">✓</span> Kelola lamaran masuk dengan mudah
                        </div>
                    </div>
                    <Link href="/register/company"
                          class="inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-full transition">
                        Daftar Perusahaan →
                    </Link>
                </div>
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl p-8 text-center">
                    <p class="text-5xl mb-4">🏢</p>
                    <p class="text-4xl font-extrabold text-indigo-600 mb-2">30 Hari</p>
                    <p class="text-gray-600 font-semibold mb-1">Posting Lowongan Gratis</p>
                    <p class="text-gray-400 text-sm">untuk perusahaan yang baru bergabung</p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-20 px-6 text-centers bg-indigo-600">
            <h2 class="text-3xl font-extrabold text-white mb-4">Siap Memulai?</h2>
            <p class="text-indigo-200 mb-8">Daftar gratis sekarang dan dapatkan CV tersertifikasi HRI.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <Link href="/login"
                      class="bg-white text-indigo-600 px-10 py-4 rounded-full font-extrabold text-base hover:bg-indigo-50 transition shadow-lg">
                    Mulai Gratis Sekarang →
                </Link>
                <Link href="/jobs"
                      class="border border-white text-white px-10 py-4 rounded-full font-extrabold text-base hover:bg-indigo-700 transition">
                    Lihat Lowongan Kerja
                </Link>
            </div>
        </section>

        <!-- フッター -->
        <footer class="py-8 px-6 text-center text-sm text-gray-400 border-t border-gray-100">
            © 2026 HRICheck. All rights reserved.
        </footer>

    </div>
</template>