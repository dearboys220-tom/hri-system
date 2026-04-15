<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { availableLocales, setLocale } from '@/i18n.js'

const props = defineProps({
    dark: {
        type: Boolean,
        default: true, // true = 暗背景（ナビバー）, false = 白背景（モバイルメニュー）
    },
})

const { locale } = useI18n()
const open = ref(false)

const current = computed(() =>
    availableLocales.find(l => l.code === locale.value) ?? availableLocales[0]
)

function switchLocale(code) {
    setLocale(code)
    open.value = false
}
</script>

<template>
    <div class="relative">
        <!-- トリガーボタン -->
        <button @click="open = !open"
                :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition border',
                         dark
                             ? 'border-white/30 text-white hover:bg-white/10'
                             : 'border-gray-300 text-gray-700 hover:bg-gray-50 bg-white']">
            <span>{{ current.flag }}</span>
            <span class="hidden sm:block">{{ current.label }}</span>
            <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- ドロップダウン -->
        <div v-if="open"
             class="absolute left-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-gray-100
                    py-1 z-50 min-w-[150px]">
            <button v-for="loc in availableLocales" :key="loc.code"
                    @click="switchLocale(loc.code)"
                    :class="['w-full flex items-center gap-2 px-4 py-2.5 text-sm text-left transition',
                             locale === loc.code
                                 ? 'bg-blue-50 text-blue-700 font-semibold'
                                 : 'text-gray-700 hover:bg-gray-50']">
                <span class="text-base">{{ loc.flag }}</span>
                <span>{{ loc.label }}</span>
                <span v-if="locale === loc.code" class="ml-auto text-blue-500 text-xs">✓</span>
            </button>
        </div>

        <!-- 外部クリックで閉じる -->
        <div v-if="open" class="fixed inset-0 z-40" @click="open = false"></div>
    </div>
</template>