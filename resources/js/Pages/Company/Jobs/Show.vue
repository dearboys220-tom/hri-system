<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    job: Object,
    categoryName: String,
    subcategoryName: String,
    needsPayment: Boolean,
});

const processing    = ref(false);
const errorMsg      = ref('');

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d     = new Date(dateStr);
    const day   = String(d.getUTCDate()).padStart(2, '0');
    const month = String(d.getUTCMonth() + 1).padStart(2, '0');
    const year  = d.getUTCFullYear();
    return `${day}-${month}-${year}`;
}

function formatSalary(min, max) {
    if (!min && !max) return 'Tidak disebutkan';
    const fmt = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
    if (min && max) return `${fmt(min)} – ${fmt(max)}`;
    if (min) return `${fmt(min)}+`;
    return `s/d ${fmt(max)}`;
}

function confirmDelete() {
    if (confirm('Yakin ingin menghapus lowongan ini?')) {
        router.delete(`/company/jobs/${props.job.id}`);
    }
}

// Snap.js動的ロード
const loadSnapScript = (url) => {
    return new Promise((resolve) => {
        if (window.snap) { resolve(); return; }
        const script    = document.createElement('script');
        script.src      = url;
        script.onload   = resolve;
        document.head.appendChild(script);
    });
};

// 支払い処理
async function payNow() {
    if (processing.value) return;
    processing.value = true;
    errorMsg.value   = '';

    try {
        const res = await axios.post(`/company/jobs/${props.job.id}/payment`);
        const { snap_token, snap_url } = res.data;

        await loadSnapScript(snap_url);

        window.snap.pay(snap_token, {
            onSuccess: () => {
                window.location.href = '/company/jobs?payment=success';
            },
            onPending: () => {
                window.location.href = '/company/jobs?payment=pending';
            },
            onError: () => {
                errorMsg.value   = 'Pembayaran gagal. Silakan coba lagi.';
                processing.value = false;
            },
            onClose: () => {
                errorMsg.value   = 'Pembayaran dibatalkan.';
                processing.value = false;
            },
        });
    } catch (e) {
        errorMsg.value   = 'Terjadi kesalahan. Silakan coba lagi.';
        processing.value = false;
    }
}

const statusConfig = {
    active:  { label: 'Aktif',            color: 'bg-green-100 text-green-700' },
    closed:  { label: 'Ditutup',          color: 'bg-red-100 text-red-600' },
    draft:   { label: 'Menunggu Bayar',   color: 'bg-yellow-100 text-yellow-700' },
    deleted: { label: 'Dihapus',          color: 'bg-red-200 text-red-800' },
};
const st = statusConfig[props.job.status] ?? statusConfig.draft;
</script>

<template>
    <Head :title="job.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Detail Lowongan</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-5">

                <!-- 支払いバナー（draft状態） -->
                <div v-if="job.status === 'draft'"
                     class="bg-yellow-50 border border-yellow-300 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-2xl">💳</span>
                        <div>
                            <p class="font-bold text-yellow-800">Lowongan Belum Aktif</p>
                            <p class="text-sm text-yellow-700">Selesaikan pembayaran untuk mengaktifkan lowongan ini.</p>
                        </div>
                        <span class="ml-auto font-bold text-yellow-800 text-lg">Rp 250.000</span>
                    </div>
                    <p v-if="errorMsg" class="text-red-600 text-sm mb-2 text-center">{{ errorMsg }}</p>
                    <button
                        @click="payNow"
                        :disabled="processing"
                        class="w-full bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-300 text-white font-bold py-3 rounded-xl transition text-sm">
                        {{ processing ? 'Memproses...' : '💳 Bayar Sekarang Rp 250.000' }}
                    </button>
                </div>

                <!-- ヘッダーカード -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex flex-col sm:flex-row gap-4 items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-xl font-bold text-gray-800">{{ job.title }}</h1>
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', st.color]">{{ st.label }}</span>
                                <span v-if="job.is_free_post"
                                      class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    🎁 GRATIS
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-500">
                                <span>📍 {{ job.location }}</span>
                                <span>💼 {{ job.employment_type }}</span>
                                <span>💰 {{ formatSalary(job.salary_min, job.salary_max) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- アクションボタン -->
                    <div class="flex gap-3 mt-5 pt-4 border-t border-gray-100">
                        <Link :href="`/company/jobs/${job.id}/edit`"
                              class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-medium transition">
                            ✏️ Edit Lowongan
                        </Link>
                        <Link :href="`/company/jobs/${job.id}/applications`"
                              class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-xl text-sm font-medium transition">
                            👥 Lihat Pelamar
                        </Link>
                        <button @click="confirmDelete"
                                class="bg-red-50 hover:bg-red-100 text-red-600 px-5 py-2 rounded-xl text-sm font-medium transition border border-red-200">
                            🗑️ Hapus
                        </button>
                    </div>
                </div>

                <!-- 詳細情報 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="font-semibold text-gray-700 border-b pb-2">📋 Informasi Lowongan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-400 block">Bidang</span><span class="font-medium">{{ categoryName }}</span></div>
                        <div><span class="text-gray-400 block">Spesialisasi</span><span class="font-medium">{{ subcategoryName }}</span></div>
                        <div><span class="text-gray-400 block">Pendidikan Minimal</span><span class="font-medium">{{ job.education_requirement ?? '-' }}</span></div>
                        <div><span class="text-gray-400 block">Pengalaman</span><span class="font-medium">{{ job.experience_level ?? '-' }}</span></div>
                        <div><span class="text-gray-400 block">Hari Kerja</span><span class="font-medium">{{ job.working_days?.join(', ') ?? '-' }}</span></div>
                        <div><span class="text-gray-400 block">Jam Kerja</span><span class="font-medium">{{ job.working_hours ?? '-' }}</span></div>
                        <div><span class="text-gray-400 block">Jenis Kelamin</span><span class="font-medium">{{ job.gender ?? '-' }}</span></div>
                        <div><span class="text-gray-400 block">Usia</span>
                            <span class="font-medium">
                                {{ job.age_min && job.age_max ? `${job.age_min} – ${job.age_max} tahun` : '-' }}
                            </span>
                        </div>
                        <div><span class="text-gray-400 block">Status Pernikahan</span><span class="font-medium">{{ job.marital_status ?? '-' }}</span></div>
                        <div><span class="text-gray-400 block">Bahasa</span><span class="font-medium">{{ job.language_requirements?.join(', ') ?? '-' }}</span></div>
                        <div><span class="text-gray-400 block">Batas Lamaran</span><span class="font-medium">{{ formatDate(job.application_deadline) }}</span></div>
                        <div><span class="text-gray-400 block">Tanggal Mulai</span><span class="font-medium">{{ formatDate(job.start_date) }}</span></div>
                    </div>
                </div>

                <!-- 詳細テキスト -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="font-semibold text-gray-700 border-b pb-2">📝 Detail Pekerjaan</h3>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Deskripsi Pekerjaan</p>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ job.job_description }}</p>
                    </div>
                    <div v-if="job.required_skills">
                        <p class="text-sm font-medium text-gray-600 mb-1">Keterampilan yang Dibutuhkan</p>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ job.required_skills }}</p>
                    </div>
                    <div v-if="job.preferred_skills">
                        <p class="text-sm font-medium text-gray-600 mb-1">Keahlian Tambahan</p>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ job.preferred_skills }}</p>
                    </div>
                    <div v-if="job.special_requirements">
                        <p class="text-sm font-medium text-gray-600 mb-1">Persyaratan Khusus</p>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ job.special_requirements }}</p>
                    </div>
                </div>

                <!-- 職場写真 -->
                <div v-if="job.workplace_photo"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-700">📸 Foto Tempat Kerja</h3>
                    </div>
                    <img :src="`/storage/${job.workplace_photo}`" class="w-full object-cover max-h-96" />
                </div>

                <div class="pb-8">
                    <Link href="/company/jobs" class="text-sm text-indigo-600 hover:underline">
                        ← Kembali ke Daftar Lowongan
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>