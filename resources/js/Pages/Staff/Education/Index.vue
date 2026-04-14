<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    modules:        { type: Array,   default: () => [] },
    completedCount: { type: Number,  default: 0 },
    totalCount:     { type: Number,  default: 0 },
    allCompleted:   { type: Boolean, default: false },
})

const progressPercent = computed(() =>
    props.totalCount > 0
        ? Math.round((props.completedCount / props.totalCount) * 100)
        : 0
)
</script>

<template>
    <Head title="Portal Pelatihan Karyawan" />

    <div class="min-h-screen bg-gray-50">
        <!-- ヘッダー -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Portal Pelatihan Karyawan</h1>
                    <p class="text-xs text-gray-500">HRI System — Wajib diselesaikan sebelum menggunakan sistem</p>
                </div>
            </div>
            <Link :href="route('staff.dashboard')"
                  class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </Link>
        </header>

        <div class="max-w-4xl mx-auto px-4 py-8">

            <!-- 進捗バナー -->
            <div :class="allCompleted
                    ? 'bg-green-50 border-green-200 text-green-800'
                    : 'bg-amber-50 border-amber-200 text-amber-800'"
                 class="border rounded-xl p-5 mb-8 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold">
                        {{ allCompleted
                            ? '✅ Semua modul selesai! Anda memiliki akses penuh ke sistem.'
                            : '⚠️ Selesaikan semua modul untuk menggunakan fitur sistem.' }}
                    </p>
                    <p class="text-xs mt-1 opacity-75">
                        Progress: {{ completedCount }} dari {{ totalCount }} modul selesai
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold">{{ progressPercent }}%</p>
                </div>
            </div>

            <!-- プログレスバー -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-8">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                    <span>Progress Keseluruhan</span>
                    <span>{{ completedCount }}/{{ totalCount }} Modul</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div :style="{ width: progressPercent + '%' }"
                         :class="allCompleted ? 'bg-green-500' : 'bg-blue-500'"
                         class="h-3 rounded-full transition-all duration-500"></div>
                </div>
            </div>

            <!-- モジュール一覧 -->
            <div class="space-y-4">
                <h2 class="text-base font-semibold text-gray-700 mb-4">
                    Modul Pelatihan Wajib
                </h2>

                <div v-for="(mod, idx) in modules" :key="mod.code"
                     class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center p-5 gap-4">
                        <!-- 番号 / 完了アイコン -->
                        <div :class="mod.is_completed
                                ? 'bg-green-100 text-green-700'
                                : 'bg-blue-50 text-blue-600'"
                             class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">
                            <span v-if="mod.is_completed">✓</span>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>

                        <!-- テキスト -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold text-gray-900">{{ mod.title }}</h3>
                                <span v-if="mod.is_completed"
                                      class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Selesai</span>
                                <span v-else
                                      class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Belum selesai</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">{{ mod.description }}</p>

                            <!-- 制限機能 -->
                            <div v-if="!mod.is_completed" class="mt-2">
                                <p class="text-xs text-red-600 font-medium">
                                    🔒 Fitur terkunci:
                                    <span class="font-normal">{{ mod.restricted_features.join(', ') }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- ボタン -->
                        <Link :href="route('staff.education.show', mod.code)"
                              :class="mod.is_completed
                                  ? 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                  : 'bg-blue-600 text-white hover:bg-blue-700'"
                              class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap">
                            {{ mod.is_completed ? 'Lihat Lagi' : 'Mulai Pelajari →' }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 注意事項 -->
            <div class="mt-8 bg-gray-100 rounded-xl p-4 text-xs text-gray-500">
                <p class="font-semibold text-gray-700 mb-1">⚠️ Perhatian</p>
                <p>Akses ke fitur sistem hanya diberikan setelah modul pelatihan yang relevan diselesaikan.
                   Nilai kuis minimum yang diperlukan adalah <strong>80%</strong> untuk setiap modul.
                   Jika gagal, Anda dapat mencoba kembali tanpa batas.</p>
            </div>
        </div>
    </div>
</template>