<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    module: { type: Object, required: true }
})

const page       = usePage()
const quizResult = computed(() => page.props.flash?.quiz_result ?? null)

// クイズ状態
const showQuiz  = ref(props.module.is_completed === false) // 未完了なら即クイズ表示
const answers   = ref({})

const form = useForm({})

function submitQuiz() {
    form.transform(() => ({ answers: answers.value }))
        .post(route('staff.education.complete', props.module.code), {
            preserveScroll: true,
            onSuccess: () => {
                answers.value = {}
            },
        })
}
</script>

<template>
    <Head :title="`Pelatihan: ${module.title}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- ヘッダー -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4">
            <Link :href="route('staff.education.index')"
                  class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </Link>
            <div>
                <h1 class="text-base font-bold text-gray-900">{{ module.title }}</h1>
                <p class="text-xs text-gray-500">Portal Pelatihan Karyawan</p>
            </div>
            <div class="ml-auto">
                <span v-if="module.is_completed"
                      class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">
                    ✓ Sudah Selesai — Skor: {{ module.quiz_score }}%
                </span>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 py-8 space-y-6">

            <!-- クイズ結果バナー -->
            <div v-if="quizResult"
                 :class="quizResult.is_passed
                     ? 'bg-green-50 border-green-300 text-green-800'
                     : 'bg-red-50 border-red-300 text-red-800'"
                 class="border rounded-xl p-5">
                <p class="font-bold text-base">
                    {{ quizResult.is_passed ? '🎉 Selamat! Kuis Berhasil!' : '❌ Belum Lulus' }}
                </p>
                <p class="text-sm mt-1">
                    Skor Anda: <strong>{{ quizResult.score }}%</strong>
                    ({{ quizResult.correct }} dari {{ quizResult.total }} jawaban benar)
                    — Nilai minimum: {{ quizResult.required }}%
                </p>
                <p v-if="!quizResult.is_passed" class="text-sm mt-2">
                    Silakan pelajari kembali materi di bawah ini dan coba lagi.
                </p>
                <p v-if="quizResult.is_passed" class="text-sm mt-2">
                    Modul ini telah tercatat sebagai selesai. Kembali ke portal untuk melanjutkan modul berikutnya.
                </p>
            </div>

            <!-- 学習コンテンツ -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h2 class="text-white font-semibold">📖 Materi Pelatihan</h2>
                    <p class="text-blue-100 text-xs mt-1">{{ module.description }}</p>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="(section, i) in module.content" :key="i" class="p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-2">{{ section.heading }}</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ section.body }}</p>
                    </div>
                </div>
            </div>

            <!-- クイズセクション -->
            <div v-if="!module.is_completed || quizResult?.is_passed === false"
                 class="bg-white rounded-xl border border-blue-200 overflow-hidden">
                <div class="bg-blue-50 px-6 py-4 border-b border-blue-200">
                    <h2 class="text-sm font-bold text-blue-900">📝 Kuis Pemahaman</h2>
                    <p class="text-xs text-blue-700 mt-1">
                        Jawab semua pertanyaan. Nilai minimum <strong>{{ module.required_score }}%</strong> untuk lulus.
                        Percobaan ke-{{ (module.attempt_count || 0) + 1 }}.
                    </p>
                </div>
                <form @submit.prevent="submitQuiz" class="divide-y divide-gray-100">
                    <div v-for="(q, qi) in module.quiz" :key="qi" class="p-6">
                        <p class="text-sm font-semibold text-gray-900 mb-3">
                            {{ qi + 1 }}. {{ q.question }}
                        </p>
                        <div class="space-y-2">
                            <label v-for="(opt, oi) in q.options" :key="oi"
                                   :class="answers[qi] === oi
                                       ? 'border-blue-500 bg-blue-50'
                                       : 'border-gray-200 hover:border-gray-300'"
                                   class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition">
                                <input type="radio"
                                       :name="`q_${qi}`"
                                       :value="oi"
                                       v-model="answers[qi]"
                                       class="accent-blue-600" />
                                <span class="text-sm text-gray-700">{{ opt }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-6">
                        <button type="submit"
                                :disabled="Object.keys(answers).length < module.quiz.length || form.processing"
                                class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300
                                       text-white font-semibold py-3 rounded-lg transition text-sm">
                            {{ form.processing ? 'Sedang menilai...' : 'Kirim Jawaban' }}
                        </button>
                        <p v-if="Object.keys(answers).length < module.quiz.length"
                           class="text-xs text-center text-gray-400 mt-2">
                            Jawab semua pertanyaan terlebih dahulu
                            ({{ Object.keys(answers).length }}/{{ module.quiz.length }})
                        </p>
                    </div>
                </form>
            </div>

            <!-- 完了済みの場合 -->
            <div v-if="module.is_completed && !quizResult"
                 class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                <p class="text-green-800 font-semibold">✅ Modul ini sudah Anda selesaikan</p>
                <p class="text-sm text-green-700 mt-1">
                    Skor terakhir: <strong>{{ module.quiz_score }}%</strong> —
                    Selesai pada: {{ module.completed_at ?? '-' }}
                </p>
                <Link :href="route('staff.education.index')"
                      class="mt-4 inline-block bg-green-600 hover:bg-green-700 text-white
                             text-sm font-semibold px-6 py-2 rounded-lg transition">
                    Kembali ke Portal Pelatihan
                </Link>
            </div>
        </div>
    </div>
</template>