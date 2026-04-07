<template>
  <div class="min-h-screen bg-gray-900 text-white flex flex-col">

    <header class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex items-center justify-between flex-shrink-0">
      <div class="flex items-center gap-3">
        <span class="text-yellow-400 font-bold text-lg">HRI</span>
        <span class="text-gray-400">|</span>
        <span class="text-sm text-gray-300">AI Asisten — Tim Investigasi</span>
      </div>
      <a href="/admin/investigator" class="text-sm text-gray-400 hover:text-white transition-colors">
        ← Kembali ke Dashboard
      </a>
    </header>

    <div class="flex-1 flex flex-col max-w-4xl w-full mx-auto px-4 py-6 overflow-hidden">
      <div class="bg-gray-800 rounded-xl border border-gray-700 flex-1 flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700 flex items-center gap-3 flex-shrink-0">
          <div class="w-9 h-9 bg-yellow-500/20 rounded-full flex items-center justify-center text-xl">🤖</div>
          <div>
            <h1 class="font-semibold text-white text-sm">AI Asisten Investigasi</h1>
            <p class="text-xs text-gray-400">Konsultasi verifikasi data, panduan kategori, dan analisis kasus</p>
          </div>
          <div class="ml-auto">
            <span class="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded-full">Investigator</span>
          </div>
        </div>
        <AiChatWidget :requests="requests" />
      </div>
    </div>

  </div>
</template>

<script setup>
import AiChatWidget from '@/Components/AiChatWidget.vue'

defineProps({
  requests: {
    type: Array,
    default: () => [],
  },
})
</script>