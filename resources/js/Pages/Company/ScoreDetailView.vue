<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    memberId:           { type: String,  required: true },
    applicant:          { type: Object,  required: true },
    reviewItems:        { type: Array,   default: () => [] },
    investigationItems: { type: Array,   default: () => [] },
    purchasedAt:        { type: String,  default: null },
})

// カテゴリ表示名
const CATEGORY_LABEL = {
    basic_info:    'Informasi Dasar',
    education:     'Pendidikan',
    work:          'Riwayat Kerja',
    certification: 'Sertifikat',
    other:         'Lainnya',
}

const CATEGORY_COLOR = {
    basic_info:    'bg-gray-100 text-gray-700',
    education:     'bg-blue-100 text-blue-700',
    work:          'bg-green-100 text-green-700',
    certification: 'bg-purple-100 text-purple-700',
    other:         'bg-orange-100 text-orange-700',
}

// カテゴリ別にグループ化
const groupedReview = computed(() => {
    const groups = {}
    for (const item of props.reviewItems) {
        if (!groups[item.category]) groups[item.category] = []
        groups[item.category].push(item)
    }
    return groups
})

const groupedInvestigation = computed(() => {
    const groups = {}
    for (const item of props.investigationItems) {
        if (!groups[item.category]) groups[item.category] = []
        groups[item.category].push(item)
    }
    return groups
})

// スコアの色
function scoreColor(score) {
    if (score >= 80) return 'text-green-600'
    if (score >= 60) return 'text-yellow-600'
    return 'text-red-600'
}

function scoreBg(score) {
    if (score >= 80) return 'bg-green-50 border-green-200'
    if (score >= 60) return 'bg-yellow-50 border-yellow-200'
    return 'bg-red-50 border-red-200'
}

function formatDate(d) {
    if (!d) return '-'
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>

<template>
    <Head :title="'Detail Skor — ' + memberId" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <button @click="router.visit('/company/dashboard')"
                    class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    ← Kembali
                </button>
                <div v-if="purchasedAt" class="text-xs text-gray-400">
                    Dibeli: {{ formatDate(purchasedAt) }}
                </div>
            </div>

            <!-- 申請者カード -->
            <div :class="['rounded-2xl border-2 p-6', scoreBg(applicant.hri_score ?? 0)]">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Laporan Detail Skor HRI</p>
                        <h1 class="text-xl font-bold text-gray-800">{{ applicant.full_name }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ applicant.member_id }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                :class="applicant.cert_status === 'Terverifikasi'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-600'">
                                {{ applicant.cert_status === 'Terverifikasi' ? '✅ Terverifikasi' : applicant.cert_status }}
                            </span>
                            <span v-if="applicant.cert_expiry" class="text-xs text-gray-500">
                                Berlaku s/d {{ formatDate(applicant.cert_expiry) }}
                            </span>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500 mb-1">Skor HRI</p>
                        <p :class="['text-5xl font-black', scoreColor(applicant.hri_score ?? 0)]">
                            {{ applicant.hri_score ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-400">/ 100</p>
                    </div>
                </div>
            </div>

            <!-- レビュー結果（カテゴリ別） -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">📊 Rincian Penilaian (Review)</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Hasil evaluasi oleh tim reviewer</p>
                </div>

                <div v-if="reviewItems.length === 0" class="px-6 py-8 text-center text-gray-400 text-sm">
                    Belum ada data penilaian.
                </div>

                <div v-else class="divide-y divide-gray-50">
                    <div v-for="(items, category) in groupedReview" :key="category">
                        <!-- カテゴリヘッダー -->
                        <div class="px-6 py-3 bg-gray-50 flex items-center gap-2">
                            <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', CATEGORY_COLOR[category] ?? 'bg-gray-100 text-gray-600']">
                                {{ CATEGORY_LABEL[category] ?? category }}
                            </span>
                            <span class="text-xs text-gray-400">
                                Bobot: {{ (items[0]?.weight * 100).toFixed(0) }}%
                            </span>
                        </div>

                        <!-- アイテム一覧 -->
                        <div v-for="item in items" :key="item.item_name"
                            class="px-6 py-3 flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700">{{ item.item_name }}</p>
                                <p v-if="item.notes" class="text-xs text-gray-400 mt-0.5 truncate">{{ item.notes }}</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">Pengurangan</p>
                                    <p :class="['text-sm font-semibold', item.actual_deduction > 0 ? 'text-red-600' : 'text-green-600']">
                                        {{ item.actual_deduction > 0 ? '-' + item.actual_deduction : '0' }}
                                        <span class="text-gray-400 font-normal"> / {{ item.max_deduction }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 調査結果（カテゴリ別） -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800">🔍 Hasil Investigasi</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Verifikasi lapangan oleh tim investigator</p>
                </div>

                <div v-if="investigationItems.length === 0" class="px-6 py-8 text-center text-gray-400 text-sm">
                    Belum ada data investigasi.
                </div>

                <div v-else class="divide-y divide-gray-50">
                    <div v-for="(items, category) in groupedInvestigation" :key="category">
                        <div class="px-6 py-3 bg-gray-50">
                            <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', CATEGORY_COLOR[category] ?? 'bg-gray-100 text-gray-600']">
                                {{ CATEGORY_LABEL[category] ?? category }}
                            </span>
                        </div>

                        <div v-for="item in items" :key="item.item_name"
                            class="px-6 py-3 flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700">{{ item.item_name }}</p>
                                <p v-if="item.notes_id || item.notes"
                                    class="text-xs text-gray-400 mt-0.5">
                                    {{ item.notes_id || item.notes }}
                                </p>
                            </div>
                            <span :class="[
                                'text-xs font-bold px-3 py-1 rounded-full shrink-0',
                                item.validity === 'VALID'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'
                            ]">
                                {{ item.validity }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- フッター -->
            <p class="text-center text-xs text-gray-400 pb-4">
                Laporan ini diterbitkan oleh HRI System. Data bersifat rahasia dan hanya untuk keperluan rekrutmen.
            </p>

        </div>
    </AuthenticatedLayout>
</template>