<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'

const props = defineProps({
    memberId:      { type: String, required: true },
    applicantName: { type: String, required: true },
    amount:        { type: Number, default: 50000 },
})

const loading = ref(false)
const error   = ref('')

function loadMidtrans() {
    return new Promise((resolve) => {
        if (window.snap) { resolve(); return; }
        const script = document.createElement('script')
        script.src = 'https://app.sandbox.midtrans.com/snap/snap.js'
        script.setAttribute('data-client-key', import.meta.env.VITE_MIDTRANS_CLIENT_KEY || '')
        script.onload = resolve
        document.head.appendChild(script)
    })
}

async function pay() {
    loading.value = true
    error.value   = ''

    try {
        await loadMidtrans()

        // axiosで CSRF トークンを自動付与してPOST
        let data
        try {
            const res = await axios.post('/company/score/snap', {
                member_id: props.memberId,
            })
            data = res.data
        } catch (e) {
            error.value   = e.response?.data?.error || 'Terjadi kesalahan.'
            loading.value = false
            return
        }

        window.snap.pay(data.snap_token, {
            onSuccess: function () {
                router.get('/company/score/finish?order_id=' + data.order_id)
            },
            onPending: function () {
                router.get('/company/score/finish?order_id=' + data.order_id)
            },
            onError: function () {
                error.value   = 'Pembayaran gagal. Silakan coba lagi.'
                loading.value = false
            },
            onClose: function () {
                loading.value = false
            },
        })
    } catch (e) {
        error.value   = 'Terjadi kesalahan koneksi.'
        loading.value = false
    }
}
</script>

<template>
    <Head title="Pembelian Detail Skor" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
            <div class="max-w-md w-full">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

                    <!-- ヘッダー -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white text-xl">
                                📊
                            </div>
                            <div>
                                <h1 class="text-white font-bold text-lg">Detail Skor HRI</h1>
                                <p class="text-blue-100 text-sm">Pembelian akses laporan lengkap</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">

                        <!-- 対象者 -->
                        <div class="bg-blue-50 rounded-xl p-4 flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-sm">
                                {{ applicantName?.charAt(0) }}
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Kandidat</p>
                                <p class="font-semibold text-gray-800">{{ applicantName }}</p>
                                <p class="text-xs text-gray-400">{{ memberId }}</p>
                            </div>
                        </div>

                        <!-- 内容説明 -->
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-700">Yang Anda dapatkan:</p>
                            <ul class="space-y-1.5">
                                <li v-for="item in [
                                    '✅ Rincian penilaian per kategori (pekerjaan, pendidikan, sertifikat)',
                                    '✅ Hasil investigasi setiap item',
                                    '✅ Catatan dari tim reviewer',
                                    '✅ Akses permanen — beli sekali, lihat selamanya',
                                ]" :key="item" class="text-sm text-gray-600">
                                    {{ item }}
                                </li>
                            </ul>
                        </div>

                        <!-- 金額 -->
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Biaya akses</span>
                                <span class="text-2xl font-bold text-gray-800">
                                    Rp {{ amount.toLocaleString('id-ID') }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Pembayaran melalui QRIS / GoPay / Transfer Bank</p>
                        </div>

                        <!-- エラー -->
                        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-600">
                            {{ error }}
                        </div>

                        <!-- 支払いボタン -->
                        <button
                            @click="pay"
                            :disabled="loading"
                            class="w-full py-3.5 rounded-xl font-semibold text-white transition-all"
                            :class="loading
                                ? 'bg-gray-400 cursor-not-allowed'
                                : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg'"
                        >
                            <span v-if="loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                Memproses...
                            </span>
                            <span v-else>💳 Bayar Sekarang — Rp {{ amount.toLocaleString('id-ID') }}</span>
                        </button>

                        <button
                            @click="router.visit('/company/dashboard')"
                            class="w-full py-2.5 rounded-xl text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition"
                        >
                            ← Kembali ke Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>