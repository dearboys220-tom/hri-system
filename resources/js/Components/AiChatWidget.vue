<template>
  <div class="flex flex-col h-full">

    <!-- 案件選択 -->
    <div v-if="requests.length > 0" class="p-4 border-b border-gray-700 flex-shrink-0">
      <label class="block text-xs text-gray-400 mb-1">Pilih Kasus</label>
      <select
        v-model="selectedCaseNo"
        class="w-full bg-gray-700 text-white text-sm rounded px-3 py-2 border border-gray-600"
        @change="clearHistory"
      >
        <option value="">-- Tanpa konteks kasus --</option>
        <option v-for="r in requests" :key="r.case_no" :value="r.case_no">
          {{ r.label }}
        </option>
      </select>
    </div>

    <!-- メッセージエリア -->
    <div ref="messagesEl" class="flex-1 overflow-y-auto p-4 space-y-4">
      <div v-if="!history.length" class="text-center text-gray-500 text-sm mt-8">
        <div class="text-3xl mb-2">🤖</div>
        <p>Halo! Saya siap membantu Anda.</p>
        <p v-if="!autoCase" class="text-xs mt-1">Pilih kasus di atas untuk memberikan konteks.</p>
        <p v-else class="text-xs mt-1 text-yellow-400">Konteks kasus: {{ autoCase }}</p>
      </div>

      <div
        v-for="(msg, i) in history"
        :key="i"
        :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'"
      >
        <div
          :class="[
            'max-w-[80%] rounded-xl px-4 py-3 text-sm leading-relaxed whitespace-pre-wrap',
            msg.role === 'user'
              ? 'bg-blue-600 text-white rounded-br-none'
              : 'bg-gray-700 text-gray-100 rounded-bl-none'
          ]"
        >
          {{ msg.content }}
        </div>
      </div>

      <div v-if="loading" class="flex justify-start">
        <div class="bg-gray-700 rounded-xl rounded-bl-none px-4 py-3 flex gap-1">
          <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
          <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
          <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
        </div>
      </div>
    </div>

    <!-- エラー表示 -->
    <div v-if="errorMsg" class="px-4 py-2 bg-red-900/50 border-t border-red-700 text-red-300 text-xs flex-shrink-0">
      ⚠️ {{ errorMsg }}
    </div>

    <!-- 入力エリア -->
    <div class="p-4 border-t border-gray-700 flex-shrink-0">
      <div class="flex gap-2">
        <textarea
          v-model="inputText"
          @keydown.enter.exact.prevent="send"
          rows="3"
          :disabled="loading"
          placeholder="Ketik pertanyaan Anda... (Enter untuk kirim)"
          class="flex-1 bg-gray-700 text-white text-sm rounded-lg px-3 py-2 resize-none border border-gray-600 focus:outline-none focus:border-blue-500 disabled:opacity-50"
        ></textarea>
        <button
          @click="send"
          :disabled="loading || !inputText.trim()"
          class="px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-40 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
          </svg>
        </button>
      </div>
      <p class="text-xs text-gray-500 mt-1">
        Jangan masukkan NIK, nomor telepon, atau nama lengkap.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, watch, onMounted } from 'vue'
import { v4 as uuidv4 } from 'uuid'
import axios from 'axios'

const props = defineProps({
  requests:  { type: Array,  default: () => [] },
  // フローティングチャット用：案件を自動セット・ドロップダウン非表示
  autoCase:  { type: String, default: '' },
})

const selectedCaseNo = ref('')
const inputText      = ref('')
const history        = ref([])
const loading        = ref(false)
const errorMsg       = ref('')
const messagesEl     = ref(null)
const sessionId      = ref(uuidv4())

// autoCase が指定された場合は自動セット
onMounted(() => {
  if (props.autoCase) {
    selectedCaseNo.value = props.autoCase
  }
})

// autoCase が変わった場合も追従（案件切り替え時）
watch(() => props.autoCase, (val) => {
  if (val && val !== selectedCaseNo.value) {
    selectedCaseNo.value = val
    clearHistory()
  }
})

function clearHistory() {
  history.value  = []
  errorMsg.value = ''
  sessionId.value = uuidv4()
}

async function scrollBottom() {
  await nextTick()
  if (messagesEl.value) {
    messagesEl.value.scrollTop = messagesEl.value.scrollHeight
  }
}

async function send() {
  const text = inputText.value.trim()
  if (!text || loading.value) return

  errorMsg.value = ''
  history.value.push({ role: 'user', content: text })
  inputText.value = ''
  loading.value = true
  await scrollBottom()

  try {
    const { data } = await axios.post('/admin/ai-chat/send', {
      message:    text,
      session_id: sessionId.value,
      case_no:    selectedCaseNo.value || null,
      history:    history.value.slice(0, -1),
    })
    history.value.push({ role: 'assistant', content: data.message })
  } catch (err) {
    errorMsg.value = err.response?.data?.error || 'Terjadi kesalahan. Coba lagi.'
  } finally {
    loading.value = false
    await scrollBottom()
  }
}
</script>