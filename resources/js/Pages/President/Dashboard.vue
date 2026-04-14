<script setup>
import { computed, ref, reactive, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  totalStaff:        Number,
  availabilityStats: Object,
  evaluationSummary: Object,
  draftSalaries:     Array,
  pendingSalaries:   Array,
  activePayrolls:    Array,
  staffByDept:       Object,
  recentLogs:        Array,
})

const page  = usePage()
const user  = computed(() => page.props.auth?.user)
const flash = computed(() => page.props.flash ?? {})

// ================================================================
// AIチャット
// ================================================================
const chatMessages  = ref([])
const chatInput     = ref('')
const chatLoading   = ref(false)
const sessionId     = ref('president-' + Date.now())
const activeTab     = ref('dashboard') // 'dashboard' | 'chat' | 'tasks'

// 表示言語（Presidentは日本語固定）
const displayLang = ref('ja')

function getChatContent(msg) {
  if (msg.role === 'user') return msg.content_ja ?? msg.content
  switch (displayLang.value) {
    case 'ko': return msg.content_ko ?? msg.content_ja ?? msg.content
    case 'id': return msg.content_id ?? msg.content_ja ?? msg.content
    default:   return msg.content_ja ?? msg.content
  }
}

async function sendChat() {
  if (!chatInput.value.trim() || chatLoading.value) return

  const userMessage = chatInput.value.trim()
  chatMessages.value.push({
    role: 'user', content: userMessage, content_ja: userMessage,
  })
  chatInput.value = ''
  chatLoading.value = true

  try {
    const history = chatMessages.value.slice(-6).map(m => ({
      role: m.role === 'user' ? 'user' : 'assistant',
      content: m.content_ja ?? m.content,
    }))

    const res = await axios.post(route('president.chat.send'), {
      message:    userMessage,
      session_id: sessionId.value,
      history:    history,
    })

    const data = res.data
    chatMessages.value.push({
      role:       'assistant',
      content:    data.message_ja,
      content_ja: data.message_ja,
      content_ko: data.message_ko,
      content_id: data.message_id,
      is_task_instruction: data.is_task_instruction,
      task_order_id:       data.task_order_id,
      task_order_created:  data.task_order_created,
    })

    // 業務指示が生成された場合は通知
    if (data.task_order_created) {
      setTimeout(() => router.reload(), 2000)
    }

  } catch (e) {
    chatMessages.value.push({
      role: 'assistant',
      content: 'エラーが発生しました。もう一度お試しください。',
      content_ja: 'エラーが発生しました。もう一度お試しください。',
    })
  } finally {
    chatLoading.value = false
    setTimeout(() => {
      const el = document.getElementById('chat-bottom')
      el?.scrollIntoView({ behavior: 'smooth' })
    }, 100)
  }
}

function handleEnter(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    sendChat()
  }
}

// ================================================================
// 給与承認
// ================================================================
function approveSalary(calc) {
  if (!confirm('Setujui perhitungan gaji untuk ' + calc.staff?.name + '?')) return
  router.post(route('president.salary.approve', calc.id))
}

function logout() {
  router.post('/staff/logout')
}

// ================================================================
// ラベル・スタイル
// ================================================================
const deptLabel = {
  investigator_user: 'Investigasi', admin_user: 'Admin',
  em_staff: 'Staf Umum', strategy_user: 'Strategi',
  ai_dev_user: 'AI Dev', marketing_user: 'Marketing',
  local_manager: 'Manajer Lokal',
}
const availLabel = { AVAILABLE: 'Tersedia', BUSY: 'Sibuk', ON_LEAVE: 'Cuti', SUSPENDED: 'Ditangguhkan' }
const availColor = {
  AVAILABLE: 'bg-green-100 text-green-700', BUSY: 'bg-blue-100 text-blue-700',
  ON_LEAVE: 'bg-yellow-100 text-yellow-700', SUSPENDED: 'bg-red-100 text-red-700',
}
const bandColor = { GOOD: 'text-green-600', FAIR: 'text-yellow-600', WARNING: 'text-red-600' }
const bandLabel = { GOOD: 'Baik', FAIR: 'Cukup', WARNING: 'Peringatan' }
const payStatusLabel = { SCHEDULED: 'Dijadwalkan', PROCESSED: 'Diproses' }
const payStatusColor = {
  SCHEDULED: 'bg-gray-100 text-gray-600', PROCESSED: 'bg-blue-100 text-blue-700',
}

function rupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}
function formatDate(val) {
  if (!val) return '-'
  return new Date(val).toLocaleDateString('id-ID', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Navbar -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-10">
      <div class="max-w-6xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-purple-700 rounded-lg flex items-center justify-center text-white font-bold text-sm">
            HRI
          </div>
          <div>
            <p class="font-bold text-gray-800 text-sm">HRI System</p>
            <p class="text-xs text-gray-400">President Panel</p>
          </div>
        </div>

        <!-- タブ切り替え -->
        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
          <button @click="activeTab = 'dashboard'"
            :class="['px-4 py-1.5 rounded-md text-sm font-medium transition',
              activeTab === 'dashboard' ? 'bg-white shadow text-purple-700' : 'text-gray-500 hover:text-gray-700']">
            📊 ダッシュボード
          </button>
          <button @click="activeTab = 'chat'"
            :class="['px-4 py-1.5 rounded-md text-sm font-medium transition',
              activeTab === 'chat' ? 'bg-white shadow text-purple-700' : 'text-gray-500 hover:text-gray-700']">
            💬 AI指示チャット
          </button>
          <button @click="activeTab = 'salary'"
            :class="['px-4 py-1.5 rounded-md text-sm font-medium transition',
              activeTab === 'salary' ? 'bg-white shadow text-purple-700' : 'text-gray-500 hover:text-gray-700']">
            💰 給与承認
            <span v-if="draftSalaries.length > 0"
              class="ml-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
              {{ draftSalaries.length }}
            </span>
          </button>
        </div>

        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-600">{{ user?.name }}</span>
          <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded font-semibold">President</span>
          <button @click="logout" class="text-xs text-gray-500 hover:text-red-500">Logout</button>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">

      <!-- Flash -->
      <div v-if="flash.success"
        class="mb-4 rounded bg-green-100 border border-green-300 px-4 py-3 text-green-800 text-sm">
        {{ flash.success }}
      </div>
      <div v-if="flash.error"
        class="mb-4 rounded bg-red-100 border border-red-300 px-4 py-3 text-red-800 text-sm">
        {{ flash.error }}
      </div>

      <!-- ウェルカム -->
      <div class="bg-gradient-to-r from-purple-700 to-purple-800 rounded-2xl px-6 py-5 text-white mb-6">
        <p class="text-lg font-bold">{{ user?.name }} 様</p>
        <p class="text-purple-200 text-sm mt-1">
          AIチャットで業務指示を送信できます。日本語で入力すると、担当者に自動翻訳して伝達します。
        </p>
      </div>

      <!-- 統計カード（常時表示） -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-purple-700">{{ totalStaff }}</p>
          <p class="text-xs text-gray-500 mt-1">総スタッフ数</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-green-600">{{ availabilityStats['AVAILABLE'] ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-1">稼働中スタッフ</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center cursor-pointer" @click="activeTab = 'salary'">
          <p class="text-3xl font-bold text-yellow-500">{{ draftSalaries.length }}</p>
          <p class="text-xs text-gray-500 mt-1">給与承認待ち</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-blue-600">{{ activePayrolls.length }}</p>
          <p class="text-xs text-gray-500 mt-1">支払い進行中</p>
        </div>
      </div>

      <!-- ══════════════════════════════════════════ -->
      <!-- タブ: ダッシュボード -->
      <!-- ══════════════════════════════════════════ -->
      <div v-if="activeTab === 'dashboard'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

          <!-- 部署別スタッフ数 -->
          <div class="bg-white rounded-xl border p-5">
            <h2 class="font-semibold text-gray-700 mb-4">👥 部署別スタッフ数</h2>
            <div class="space-y-2">
              <div v-for="(count, role) in staffByDept" :key="role"
                class="flex items-center justify-between">
                <span class="text-sm text-gray-600">{{ deptLabel[role] ?? role }}</span>
                <span class="text-sm font-semibold text-gray-800 bg-gray-100 px-3 py-0.5 rounded-full">
                  {{ count }} 名
                </span>
              </div>
              <p v-if="Object.keys(staffByDept).length === 0"
                class="text-sm text-gray-400 text-center py-2">データなし</p>
            </div>
          </div>

          <!-- 稼働状況 -->
          <div class="bg-white rounded-xl border p-5">
            <h2 class="font-semibold text-gray-700 mb-4">📊 スタッフ稼働状況</h2>
            <div class="space-y-2">
              <div v-for="(count, status) in availabilityStats" :key="status"
                class="flex items-center justify-between">
                <span :class="['text-xs font-medium px-2 py-1 rounded-full', availColor[status]]">
                  {{ availLabel[status] ?? status }}
                </span>
                <span class="text-sm font-semibold text-gray-800">{{ count }} 名</span>
              </div>
              <p v-if="Object.keys(availabilityStats).length === 0"
                class="text-sm text-gray-400 text-center py-2">データなし</p>
            </div>
          </div>
        </div>

        <!-- 直近の監査ログ -->
        <div class="bg-white rounded-xl border overflow-hidden">
          <div class="px-5 py-4 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-700">🔍 直近の操作履歴</h2>
          </div>
          <div class="divide-y divide-gray-100">
            <div v-if="recentLogs.length === 0"
              class="px-5 py-6 text-center text-gray-400 text-sm">履歴なし</div>
            <div v-for="log in recentLogs" :key="log.id"
              class="px-5 py-3 flex items-start justify-between gap-4">
              <div>
                <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                  {{ log.action_type }}
                </span>
                <p class="text-xs text-gray-500 mt-1">
                  {{ log.user?.name ?? (log.actor_type === 'ai' ? 'AI System' : 'System') }}
                </p>
              </div>
              <p class="text-xs text-gray-400 whitespace-nowrap">{{ formatDate(log.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════ -->
      <!-- タブ: AI指示チャット -->
      <!-- ══════════════════════════════════════════ -->
      <div v-if="activeTab === 'chat'" class="bg-white rounded-xl border overflow-hidden">

        <!-- チャットヘッダー -->
        <div class="px-5 py-4 border-b bg-purple-50 flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-purple-800">💬 AI業務指示チャット</h2>
            <p class="text-xs text-purple-600 mt-0.5">
              日本語で入力 → 担当者には自動翻訳（韓国語・インドネシア語）で伝達
            </p>
          </div>
          <!-- 表示言語切替（確認用） -->
          <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">表示言語：</span>
            <select v-model="displayLang" class="border rounded px-2 py-1 text-xs">
              <option value="ja">日本語</option>
              <option value="ko">한국어</option>
              <option value="id">Bahasa Indonesia</option>
            </select>
          </div>
        </div>

        <!-- チャット本文 -->
        <div class="h-96 overflow-y-auto p-5 space-y-4 bg-gray-50">
          <!-- 初期メッセージ -->
          <div v-if="chatMessages.length === 0"
            class="flex justify-center items-center h-full">
            <div class="text-center text-gray-400">
              <p class="text-4xl mb-3">🤖</p>
              <p class="text-sm font-medium">AIアシスタントへようこそ</p>
              <p class="text-xs mt-1">業務指示や質問を日本語で入力してください</p>
              <div class="mt-4 space-y-2">
                <button @click="chatInput = 'インドネシア市場の最新調査をしてください'"
                  class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-purple-50 text-gray-600">
                  💡 「インドネシア市場の最新調査をしてください」
                </button>
                <button @click="chatInput = '調査部に新規申請者Aの学歴調査を依頼してください'"
                  class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-purple-50 text-gray-600">
                  💡 「調査部に新規申請者Aの学歴調査を依頼してください」
                </button>
                <button @click="chatInput = '現在の稼働状況を教えてください'"
                  class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-purple-50 text-gray-600">
                  💡 「現在の稼働状況を教えてください」
                </button>
              </div>
            </div>
          </div>

          <!-- メッセージ一覧 -->
          <div v-for="(msg, idx) in chatMessages" :key="idx"
            :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
            <div :class="['max-w-lg rounded-xl px-4 py-3 text-sm',
              msg.role === 'user'
                ? 'bg-purple-600 text-white'
                : 'bg-white border text-gray-800 shadow-sm']">

              <!-- AI返答の場合：翻訳表示 -->
              <div v-if="msg.role === 'assistant'">
                <p class="whitespace-pre-wrap">{{ getChatContent(msg) }}</p>

                <!-- 言語切替バッジ -->
                <div class="flex gap-1 mt-2">
                  <button @click="displayLang = 'ja'"
                    :class="['text-xs px-2 py-0.5 rounded', displayLang === 'ja' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500']">
                    日本語
                  </button>
                  <button @click="displayLang = 'ko'"
                    :class="['text-xs px-2 py-0.5 rounded', displayLang === 'ko' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500']">
                    한국어
                  </button>
                  <button @click="displayLang = 'id'"
                    :class="['text-xs px-2 py-0.5 rounded', displayLang === 'id' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                    Bahasa
                  </button>
                </div>

                <!-- 業務指示生成通知 -->
                <div v-if="msg.task_order_created"
                  class="mt-2 bg-yellow-50 border border-yellow-200 rounded px-3 py-2">
                  <p class="text-xs text-yellow-700 font-medium">
                    ✅ 業務指示を生成しました（DRAFT状態）
                  </p>
                  <p class="text-xs text-yellow-600 mt-0.5">
                    マネージャーの承認後に担当者へ割り当てられます
                  </p>
                </div>
              </div>

              <!-- ユーザーメッセージ -->
              <p v-else class="whitespace-pre-wrap">{{ msg.content_ja ?? msg.content }}</p>
            </div>
          </div>

          <!-- ローディング -->
          <div v-if="chatLoading" class="flex justify-start">
            <div class="bg-white border rounded-xl px-4 py-3 shadow-sm">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
              </div>
            </div>
          </div>

          <div id="chat-bottom"></div>
        </div>

        <!-- 入力エリア -->
        <div class="px-5 py-4 border-t bg-white">
          <div class="flex gap-3">
            <textarea
              v-model="chatInput"
              @keydown="handleEnter"
              placeholder="業務指示や質問を日本語で入力してください... (Enterで送信、Shift+Enterで改行)"
              rows="2"
              class="flex-1 border rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-purple-300"
            ></textarea>
            <button
              @click="sendChat"
              :disabled="chatLoading || !chatInput.trim()"
              :class="['px-5 py-3 rounded-xl text-sm font-medium transition',
                chatLoading || !chatInput.trim()
                  ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                  : 'bg-purple-600 text-white hover:bg-purple-700']">
              {{ chatLoading ? '送信中...' : '送信' }}
            </button>
          </div>
          <p class="text-xs text-gray-400 mt-2">
            💡 業務指示は自動で韓国語・インドネシア語に翻訳され、担当者に伝達されます
          </p>
        </div>
      </div>

      <!-- ══════════════════════════════════════════ -->
      <!-- タブ: 給与承認 -->
      <!-- ══════════════════════════════════════════ -->
      <div v-if="activeTab === 'salary'">

        <!-- 給与承認待ち -->
        <div class="bg-white rounded-xl border mb-6 overflow-hidden">
          <div class="px-5 py-4 border-b bg-yellow-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">
              ⏳ 給与承認待ち
              <span v-if="draftSalaries.length > 0"
                class="ml-2 bg-yellow-400 text-white text-xs px-2 py-0.5 rounded-full">
                {{ draftSalaries.length }}
              </span>
            </h2>
          </div>
          <div v-if="draftSalaries.length === 0"
            class="px-5 py-6 text-center text-gray-400 text-sm">承認待ちの給与計算はありません</div>
          <table v-else class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
              <tr>
                <th class="px-4 py-3 text-left">スタッフ</th>
                <th class="px-4 py-3 text-left">対象月</th>
                <th class="px-4 py-3 text-center">評価</th>
                <th class="px-4 py-3 text-right">基本給</th>
                <th class="px-4 py-3 text-right">手取り</th>
                <th class="px-4 py-3 text-left">AIメモ</th>
                <th class="px-4 py-3 text-center">操作</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="calc in draftSalaries" :key="calc.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">
                  {{ calc.staff?.name ?? '-' }}
                  <div class="text-xs text-gray-400">{{ deptLabel[calc.staff?.role_type] ?? calc.staff?.role_type }}</div>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ calc.calculation_month }}</td>
                <td class="px-4 py-3 text-center">
                  <span v-if="calc.performance_band"
                    :class="['text-xs font-bold', bandColor[calc.performance_band]]">
                    {{ bandLabel[calc.performance_band] ?? calc.performance_band }}
                  </span>
                  <span v-else class="text-xs text-gray-400">-</span>
                </td>
                <td class="px-4 py-3 text-right text-gray-700">{{ rupiah(calc.base_salary) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ rupiah(calc.net_salary) }}</td>
                <td class="px-4 py-3 text-gray-500 max-w-xs">
                  <p class="line-clamp-2 text-xs">{{ calc.ai_calculation_notes ?? '-' }}</p>
                </td>
                <td class="px-4 py-3 text-center">
                  <button @click="approveSalary(calc)"
                    class="px-3 py-1 bg-purple-600 text-white rounded text-xs hover:bg-purple-700">
                    承認
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 支払い進行中 -->
        <div class="bg-white rounded-xl border overflow-hidden">
          <div class="px-5 py-4 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-700">🏦 支払い進行中</h2>
          </div>
          <div v-if="activePayrolls.length === 0"
            class="px-5 py-6 text-center text-gray-400 text-sm">進行中の支払いはありません</div>
          <table v-else class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
              <tr>
                <th class="px-4 py-3 text-left">スタッフ</th>
                <th class="px-4 py-3 text-left">対象月</th>
                <th class="px-4 py-3 text-right">金額</th>
                <th class="px-4 py-3 text-left">支払方法</th>
                <th class="px-4 py-3 text-center">ステータス</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in activePayrolls" :key="p.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ p.staff?.name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ p.payment_month }}</td>
                <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ rupiah(p.paid_amount) }}</td>
                <td class="px-4 py-3 text-gray-600 text-xs">{{ p.payment_method }}</td>
                <td class="px-4 py-3 text-center">
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', payStatusColor[p.payment_status]]">
                    {{ payStatusLabel[p.payment_status] ?? p.payment_status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>