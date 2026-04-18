<template>
  <div class="flex flex-col h-full">

    <!-- ===== サブタブ（新規 / 履歴） ===== -->
    <div class="flex border-b border-gray-700 flex-shrink-0">
      <button
        @click="subTab = 'chat'"
        :class="['px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px',
          subTab === 'chat'
            ? 'border-blue-500 text-blue-400'
            : 'border-transparent text-gray-400 hover:text-gray-200']">
        💬 Chat
      </button>
      <button
        @click="subTab = 'history'"
        :class="['px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px',
          subTab === 'history'
            ? 'border-blue-500 text-blue-400'
            : 'border-transparent text-gray-400 hover:text-gray-200']">
        📋 Riwayat
      </button>
    </div>

    <!-- ===== 新規チャット ===== -->
    <div v-if="subTab === 'chat'" class="flex flex-col flex-1 overflow-hidden">

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
      <div v-if="errorMsg"
        class="px-4 py-2 bg-red-900/50 border-t border-red-700 text-red-300 text-xs flex-shrink-0">
        ⚠ {{ errorMsg }}
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

    <!-- ===== 送信履歴 ===== -->
    <div v-if="subTab === 'history'" class="flex flex-col flex-1 overflow-hidden">

      <!-- ヘッダー -->
      <div class="px-4 py-3 border-b border-gray-700 flex items-center justify-between flex-shrink-0">
        <span class="text-sm text-gray-300 font-medium">📋 Riwayat Chat AI</span>
        <button
          @click="loadHistory"
          class="text-xs text-blue-400 hover:text-blue-300 border border-gray-600 px-3 py-1 rounded transition">
          🔄 Refresh
        </button>
      </div>

      <!-- ローディング -->
      <div v-if="historyLoading" class="flex-1 flex items-center justify-center">
        <div class="text-center text-gray-500 text-sm">
          <div class="flex justify-center gap-1 mb-2">
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
          </div>
          Memuat riwayat...
        </div>
      </div>

      <!-- 履歴なし -->
      <div v-else-if="groupedHistory.length === 0"
        class="flex-1 flex items-center justify-center">
        <div class="text-center text-gray-500 text-sm">
          <p class="text-3xl mb-2">📭</p>
          <p>Belum ada riwayat chat</p>
        </div>
      </div>

      <!-- セッション別一覧 -->
      <div v-else class="flex-1 overflow-y-auto divide-y divide-gray-700">
        <div v-for="session in groupedHistory" :key="session.session_id">

          <!-- セッションヘッダー -->
          <button
            @click="toggleSession(session.session_id)"
            class="w-full px-4 py-3 flex items-start justify-between gap-3 hover:bg-gray-700/50 transition text-left">
            <div class="flex items-start gap-2 min-w-0">
              <!-- ケース番号バッジ -->
              <span v-if="session.caseNo"
                class="shrink-0 text-xs bg-yellow-500/20 text-yellow-400 px-2 py-0.5 rounded-full mt-0.5">
                {{ session.caseNo }}
              </span>
              <span v-else
                class="shrink-0 text-xs bg-gray-600 text-gray-400 px-2 py-0.5 rounded-full mt-0.5">
                Tanpa kasus
              </span>
              <!-- メッセージプレビュー -->
              <p class="text-sm text-gray-300 truncate">
                {{ session.firstUserMsg ?? '（内容なし）' }}
              </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <span class="text-xs text-gray-500">{{ formatDate(session.createdAt) }}</span>
              <span class="text-gray-500 text-xs">
                {{ expandedSession === session.session_id ? '▲' : '▼' }}
              </span>
            </div>
          </button>

          <!-- 展開：会話内容 -->
          <div v-if="expandedSession === session.session_id"
            class="bg-gray-900/50 px-4 py-3 space-y-3 border-t border-gray-700">
            <div v-for="(msg, idx) in session.messages" :key="idx"
              :class="['flex', msg.message_role === 'user' ? 'justify-end' : 'justify-start']">
              <div :class="[
                'max-w-[80%] rounded-xl px-4 py-3 text-xs leading-relaxed whitespace-pre-wrap',
                msg.message_role === 'user'
                  ? 'bg-blue-600 text-white rounded-br-none'
                  : 'bg-gray-700 text-gray-200 rounded-bl-none'
              ]">
                {{ msg.message_content }}
                <p class="text-xs mt-1.5 opacity-40">{{ formatDate(msg.created_at) }}</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch, onMounted } from 'vue'
import { v4 as uuidv4 } from 'uuid'
import axios from 'axios'

const props = defineProps({
  requests: { type: Array,  default: () => [] },
  autoCase: { type: String, default: '' },
})

// ================================================================
// チャット
// ================================================================
const subTab         = ref('chat')
const selectedCaseNo = ref('')
const inputText      = ref('')
const history        = ref([])
const loading        = ref(false)
const errorMsg       = ref('')
const messagesEl     = ref(null)
const sessionId      = ref(uuidv4())

onMounted(() => {
  if (props.autoCase) {
    selectedCaseNo.value = props.autoCase
  }
})

watch(() => props.autoCase, (val) => {
  if (val && val !== selectedCaseNo.value) {
    selectedCaseNo.value = val
    clearHistory()
  }
})

function clearHistory() {
  history.value   = []
  errorMsg.value  = ''
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
  loading.value   = true
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

// ================================================================
// 履歴
// ================================================================
const chatHistory     = ref([])
const historyLoading  = ref(false)
const expandedSession = ref(null)

const groupedHistory = computed(() => {
  const groups = {}
  for (const log of chatHistory.value) {
    if (!groups[log.session_id]) {
      groups[log.session_id] = {
        session_id:   log.session_id,
        messages:     [],
        firstUserMsg: null,
        caseNo:       log.case_no ?? null,
        createdAt:    log.created_at,
      }
    }
    groups[log.session_id].messages.push(log)
    if (log.message_role === 'user' && !groups[log.session_id].firstUserMsg) {
      groups[log.session_id].firstUserMsg = log.message_content
    }
    // より古い created_at を先頭セッション日時として使う
    if (log.created_at < groups[log.session_id].createdAt) {
      groups[log.session_id].createdAt = log.created_at
    }
  }
  return Object.values(groups).sort(
    (a, b) => new Date(b.createdAt) - new Date(a.createdAt)
  )
})

async function loadHistory() {
  historyLoading.value = true
  try {
    const res = await axios.get('/admin/ai-chat/history')
    chatHistory.value = res.data.history ?? []
  } catch (e) {
    console.error('履歴取得エラー:', e)
  } finally {
    historyLoading.value = false
  }
}

function toggleSession(sid) {
  expandedSession.value = expandedSession.value === sid ? null : sid
}

watch(subTab, (val) => {
  if (val === 'history') loadHistory()
})

function formatDate(val) {
  if (!val) return '-'
  return new Date(val).toLocaleDateString('id-ID', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}
</script>