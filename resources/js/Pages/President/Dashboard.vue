<script setup>
import { computed, ref, watch } from 'vue'
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
  allStaff:          Array,
})

const page  = usePage()
const user  = computed(() => page.props.auth?.user)
const flash = computed(() => page.props.flash ?? {})

// ================================================================
// タブ管理
// ================================================================
const activeTab = ref('dashboard')

// ================================================================
// AIチャット
// ================================================================
const chatMessages = ref([])
const chatInput    = ref('')
const chatLoading  = ref(false)
const sessionId    = ref('president-' + Date.now())
const displayLang  = ref('ja')

// ================================================================
// チャット履歴
// ================================================================
const chatSubTab      = ref('new')
const chatHistory     = ref([])
const historyLoading  = ref(false)
const expandedSession = ref(null)

const groupedHistory = computed(() => {
  const groups = {}
  for (const log of chatHistory.value) {
    if (!groups[log.session_id]) {
      groups[log.session_id] = {
        session_id:         log.session_id,
        messages:           [],
        firstUserMsg:       null,
        hasTaskInstruction: false,
        createdAt:          log.created_at,
      }
    }
    groups[log.session_id].messages.push(log)
    if (log.message_role === 'user' && !groups[log.session_id].firstUserMsg) {
      groups[log.session_id].firstUserMsg = log.message_content_ja ?? log.message_content
    }
    if (log.is_task_instruction) {
      groups[log.session_id].hasTaskInstruction = true
    }
  }
  return Object.values(groups).sort(
    (a, b) => new Date(b.createdAt) - new Date(a.createdAt)
  )
})

async function loadHistory() {
  historyLoading.value = true
  try {
    const res = await axios.get(route('president.chat.history'))
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

watch(chatSubTab, (val) => {
  if (val === 'history') loadHistory()
})

// ================================================================
// 送信先スタッフ選択
// ================================================================
const selectedStaffIds = ref([])
const staffFilter      = ref('ALL')

const roleLabel = {
  investigator_user: 'Investigasi', admin_user: 'Admin',
  em_staff: 'Staf Umum', strategy_user: 'Strategi',
  ai_dev_user: 'AI Dev', marketing_user: 'Marketing',
  local_manager: 'Manajer Lokal',
}
const roleToDivision = {
  investigator_user: 'INVESTIGATION', admin_user: 'ADMIN',
  strategy_user: 'STRATEGY', ai_dev_user: 'AI_DEV',
  marketing_user: 'MARKETING', local_manager: 'ADMIN', em_staff: 'ADMIN',
}
const divisionOptions = [
  { value: 'ALL',           label: 'Semua Staf' },
  { value: 'INVESTIGATION', label: 'Investigasi' },
  { value: 'ADMIN',         label: 'Admin' },
  { value: 'STRATEGY',      label: 'Strategi' },
  { value: 'AI_DEV',        label: 'AI Dev' },
  { value: 'MARKETING',     label: 'Marketing' },
]

const filteredStaff = computed(() => {
  if (!props.allStaff) return []
  if (staffFilter.value === 'ALL') return props.allStaff
  return props.allStaff.filter(s => roleToDivision[s.role_type] === staffFilter.value)
})

function toggleStaff(id) {
  if (selectedStaffIds.value.includes(id)) {
    selectedStaffIds.value = selectedStaffIds.value.filter(i => i !== id)
  } else {
    selectedStaffIds.value.push(id)
  }
}
function selectAll() {
  selectedStaffIds.value = filteredStaff.value.map(s => s.id)
}
function clearSelection() {
  selectedStaffIds.value = []
}

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
  const targets     = [...selectedStaffIds.value]

  const targetNames = targets.length > 0
    ? (props.allStaff ?? []).filter(s => targets.includes(s.id)).map(s => s.name).join('、')
    : '（送信先未指定）'

  chatMessages.value.push({
    role: 'user', content: userMessage, content_ja: userMessage, targets: targetNames,
  })
  chatInput.value   = ''
  chatLoading.value = true

  try {
    const history = chatMessages.value.slice(-6).map(m => ({
      role:    m.role === 'user' ? 'user' : 'assistant',
      content: m.content_ja ?? m.content,
    }))

    const res = await axios.post(route('president.chat.send'), {
      message:      userMessage,
      session_id:   sessionId.value,
      history:      history,
      assignee_ids: targets,
    })

    const data = res.data
    chatMessages.value.push({
      role:                'assistant',
      content:             data.message_ja,
      content_ja:          data.message_ja,
      content_ko:          data.message_ko,
      content_id:          data.message_id,
      is_task_instruction: data.is_task_instruction,
      task_order_id:       data.task_order_id,
      task_order_created:  data.task_order_created,
      task_assigned:       data.task_assigned,
      assignee_count:      data.assignee_count,
    })

    selectedStaffIds.value = []

  } catch (e) {
    chatMessages.value.push({
      role:       'assistant',
      content:    'エラーが発生しました。もう一度お試しください。',
      content_ja: 'エラーが発生しました。もう一度お試しください。',
    })
  } finally {
    chatLoading.value = false
    setTimeout(() => {
      document.getElementById('chat-bottom')?.scrollIntoView({ behavior: 'smooth' })
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
const availLabel = {
  AVAILABLE: 'Tersedia', BUSY: 'Sibuk', ON_LEAVE: 'Cuti', SUSPENDED: 'Ditangguhkan',
}
const availColor = {
  AVAILABLE:  'bg-green-100 text-green-700',
  BUSY:       'bg-blue-100 text-blue-700',
  ON_LEAVE:   'bg-yellow-100 text-yellow-700',
  SUSPENDED:  'bg-red-100 text-red-700',
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

    <!-- ── Navbar ── -->
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

      <!-- ウェルカムバナー -->
      <div class="bg-gradient-to-r from-purple-700 to-purple-800 rounded-2xl px-6 py-5 text-white mb-6">
        <p class="text-lg font-bold">{{ user?.name }} 様</p>
        <p class="text-purple-200 text-sm mt-1">
          AIチャットでスタッフを選択して業務指示を直接送信できます。日本語で入力すると自動翻訳して即時割当します。
        </p>
      </div>

      <!-- 統計カード（常時表示） -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-purple-700">{{ totalStaff }}</p>
          <p class="text-xs text-gray-500 mt-1">総スタッフ数</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-green-600">{{ availabilityStats?.['AVAILABLE'] ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-1">稼働中スタッフ</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center cursor-pointer" @click="activeTab = 'salary'">
          <p class="text-3xl font-bold text-yellow-500">{{ draftSalaries?.length ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-1">給与承認待ち</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-blue-600">{{ activePayrolls?.length ?? 0 }}</p>
          <p class="text-xs text-gray-500 mt-1">支払い進行中</p>
        </div>
      </div>

      <!-- ════════════════════════════════════ -->
      <!-- タブ: ダッシュボード                 -->
      <!-- ════════════════════════════════════ -->
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
              <p v-if="!staffByDept || Object.keys(staffByDept).length === 0"
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
              <p v-if="!availabilityStats || Object.keys(availabilityStats).length === 0"
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
            <div v-if="!recentLogs || recentLogs.length === 0"
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

      <!-- ════════════════════════════════════ -->
      <!-- タブ: AI指示チャット                 -->
      <!-- ════════════════════════════════════ -->
      <div v-if="activeTab === 'chat'" class="space-y-4">

        <!-- サブタブ（新規チャット / 送信履歴） -->
        <div class="flex gap-0 border-b border-gray-200">
          <button
            @click="chatSubTab = 'new'"
            :class="['px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px',
              chatSubTab === 'new'
                ? 'border-purple-600 text-purple-700'
                : 'border-transparent text-gray-500 hover:text-gray-700']">
            💬 新規チャット
          </button>
          <button
            @click="chatSubTab = 'history'"
            :class="['px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px',
              chatSubTab === 'history'
                ? 'border-purple-600 text-purple-700'
                : 'border-transparent text-gray-500 hover:text-gray-700']">
            📋 送信履歴
          </button>
        </div>

        <!-- ===== 新規チャット ===== -->
        <div v-if="chatSubTab === 'new'" class="space-y-4">

          <!-- 送信先スタッフ選択パネル -->
          <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-5 py-3 bg-indigo-50 border-b flex items-center justify-between flex-wrap gap-2">
              <div class="flex items-center gap-3">
                <h3 class="font-semibold text-indigo-800 text-sm">👥 送信先を選択</h3>
                <span class="text-xs text-indigo-500">
                  {{ selectedStaffIds.length > 0 ? selectedStaffIds.length + '名選択中' : '未選択（指示のみ生成）' }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                <select v-model="staffFilter"
                  class="border border-indigo-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-300">
                  <option v-for="d in divisionOptions" :key="d.value" :value="d.value">
                    {{ d.label }}
                  </option>
                </select>
                <button @click="selectAll"
                  class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 px-2 py-1 rounded">
                  全選択
                </button>
                <button @click="clearSelection"
                  class="text-xs text-gray-500 hover:text-gray-700 border px-2 py-1 rounded">
                  解除
                </button>
              </div>
            </div>
            <div class="px-4 py-3 flex flex-wrap gap-2 min-h-12">
              <p v-if="filteredStaff.length === 0" class="text-xs text-gray-400 py-1">対象スタッフなし</p>
              <button
                v-for="s in filteredStaff" :key="s.id"
                @click="toggleStaff(s.id)"
                :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition',
                  selectedStaffIds.includes(s.id)
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300']">
                <span>{{ s.name }}</span>
                <span :class="selectedStaffIds.includes(s.id) ? 'text-indigo-200' : 'text-gray-400'">
                  {{ roleLabel[s.role_type] ?? s.role_type }}
                </span>
              </button>
            </div>
          </div>

          <!-- チャット本体 -->
          <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-5 py-4 border-b bg-purple-50 flex items-center justify-between flex-wrap gap-2">
              <div>
                <h2 class="font-semibold text-purple-800">🤖 AI業務指示チャット</h2>
                <p class="text-xs text-purple-600 mt-0.5">
                  日本語で入力 → 自動翻訳（韓国語・インドネシア語）して担当者に即時割当
                </p>
              </div>
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
              <div v-if="chatMessages.length === 0" class="flex justify-center items-center h-full">
                <div class="text-center text-gray-400">
                  <p class="text-4xl mb-3">🤖</p>
                  <p class="text-sm font-medium">上のパネルで送信先を選択してから指示を入力してください</p>
                  <p class="text-xs mt-1 text-gray-400">送信先未指定でも指示を生成できます（Managerが後で割当）</p>
                  <div class="mt-4 space-y-2">
                    <button @click="chatInput = '調査部に新規申請者の学歴調査を依頼してください'"
                      class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-purple-50 text-gray-600">
                      💡 「調査部に新規申請者の学歴調査を依頼してください」
                    </button>
                    <button @click="chatInput = 'マーケティング部にインドネシア市場調査レポートを作成してください'"
                      class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-purple-50 text-gray-600">
                      💡 「マーケティング部にインドネシア市場調査レポートを作成してください」
                    </button>
                    <button @click="chatInput = '全スタッフの今週の業務進捗を確認してください'"
                      class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-purple-50 text-gray-600">
                      💡 「全スタッフの今週の業務進捗を確認してください」
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

                  <!-- AI返答 -->
                  <div v-if="msg.role === 'assistant'">
                    <p class="whitespace-pre-wrap">{{ getChatContent(msg) }}</p>
                    <div class="flex gap-1 mt-2">
                      <button @click="displayLang = 'ja'"
                        :class="['text-xs px-2 py-0.5 rounded',
                          displayLang === 'ja' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500']">
                        日本語
                      </button>
                      <button @click="displayLang = 'ko'"
                        :class="['text-xs px-2 py-0.5 rounded',
                          displayLang === 'ko' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500']">
                        한국어
                      </button>
                      <button @click="displayLang = 'id'"
                        :class="['text-xs px-2 py-0.5 rounded',
                          displayLang === 'id' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                        Bahasa
                      </button>
                    </div>
                    <div v-if="msg.task_order_created"
                      :class="['mt-2 rounded px-3 py-2 border',
                        msg.task_assigned ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200']">
                      <p :class="['text-xs font-medium',
                        msg.task_assigned ? 'text-green-700' : 'text-yellow-700']">
                        ✅ 業務指示を即時発令しました
                      </p>
                      <p v-if="msg.task_assigned" class="text-xs text-green-600 mt-0.5">
                        {{ msg.assignee_count }}名に割当済み（Manager承認不要）
                      </p>
                      <p v-else class="text-xs text-yellow-600 mt-0.5">
                        送信先未指定のため割当なし。Managerが割当できます。
                      </p>
                    </div>
                  </div>

                  <!-- ユーザーメッセージ -->
                  <div v-else>
                    <p class="whitespace-pre-wrap">{{ msg.content_ja ?? msg.content }}</p>
                    <p v-if="msg.targets" class="text-xs text-purple-200 mt-1">
                      📤 送信先: {{ msg.targets }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- ローディング -->
              <div v-if="chatLoading" class="flex justify-start">
                <div class="bg-white border rounded-xl px-4 py-3 shadow-sm">
                  <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
                  </div>
                </div>
              </div>

              <div id="chat-bottom"></div>
            </div>

            <!-- 入力エリア -->
            <div class="px-5 py-4 border-t bg-white">
              <div v-if="selectedStaffIds.length > 0" class="mb-2 flex flex-wrap gap-1 items-center">
                <span class="text-xs text-gray-500">送信先:</span>
                <span v-for="id in selectedStaffIds" :key="id"
                  class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                  {{ (allStaff ?? []).find(s => s.id === id)?.name ?? id }}
                </span>
              </div>
              <div class="flex gap-3">
                <textarea
                  v-model="chatInput"
                  @keydown="handleEnter"
                  placeholder="業務指示や質問を日本語で入力... (Enterで送信、Shift+Enterで改行)"
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
                💡 送信先を選択すると即時割当 / 未選択の場合はManagerが割当できます
              </p>
            </div>
          </div>
        </div>

        <!-- ===== 送信履歴 ===== -->
        <div v-if="chatSubTab === 'history'" class="space-y-3">
          <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-5 py-4 border-b bg-purple-50 flex items-center justify-between">
              <h2 class="font-semibold text-purple-800">📋 AI指示チャット履歴</h2>
              <button @click="loadHistory"
                class="text-xs text-purple-600 hover:text-purple-800 border border-purple-200 px-3 py-1 rounded-lg transition">
                🔄 更新
              </button>
            </div>

            <!-- ローディング -->
            <div v-if="historyLoading" class="px-5 py-10 text-center text-gray-400 text-sm">
              <div class="flex justify-center gap-1 mb-3">
                <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
              </div>
              履歴を読み込み中...
            </div>

            <!-- 履歴なし -->
            <div v-else-if="groupedHistory.length === 0"
              class="px-5 py-12 text-center text-gray-400 text-sm">
              <p class="text-3xl mb-2">📭</p>
              <p>チャット履歴がまだありません</p>
            </div>

            <!-- セッション別履歴一覧 -->
            <div v-else class="divide-y divide-gray-100">
              <div v-for="session in groupedHistory" :key="session.session_id">

                <!-- セッションヘッダー（クリックで展開） -->
                <button
                  @click="toggleSession(session.session_id)"
                  class="w-full px-5 py-4 flex items-start justify-between gap-3 hover:bg-gray-50 transition text-left">
                  <div class="flex items-start gap-3 min-w-0">
                    <span :class="['shrink-0 text-xs font-medium px-2 py-0.5 rounded-full mt-0.5',
                      session.hasTaskInstruction
                        ? 'bg-green-100 text-green-700'
                        : 'bg-gray-100 text-gray-500']">
                      {{ session.hasTaskInstruction ? '✅ 指示あり' : '💬 会話のみ' }}
                    </span>
                    <p class="text-sm text-gray-700 truncate">
                      {{ session.firstUserMsg ?? '（内容なし）' }}
                    </p>
                  </div>
                  <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs text-gray-400">{{ formatDate(session.createdAt) }}</span>
                    <span class="text-gray-400 text-xs">
                      {{ expandedSession === session.session_id ? '▲' : '▼' }}
                    </span>
                  </div>
                </button>

                <!-- 展開：会話内容 -->
                <div v-if="expandedSession === session.session_id"
                  class="bg-gray-50 px-5 py-4 space-y-3 border-t border-gray-100">
                  <div v-for="(msg, idx) in session.messages" :key="idx"
                    :class="['flex', msg.message_role === 'user' ? 'justify-end' : 'justify-start']">
                    <div :class="['max-w-lg rounded-xl px-4 py-3',
                      msg.message_role === 'user'
                        ? 'bg-purple-600 text-white'
                        : 'bg-white border text-gray-800 shadow-sm']">
                      <p class="whitespace-pre-wrap text-xs leading-relaxed">
                        {{ msg.message_content_ja ?? msg.message_content }}
                      </p>
                      <div v-if="msg.is_task_instruction && msg.message_role === 'user'"
                        class="mt-1.5 text-xs text-purple-200 font-medium">
                        📋 業務指示として送信
                      </div>
                      <p class="text-xs mt-1.5 opacity-50">{{ formatDate(msg.created_at) }}</p>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ════════════════════════════════════ -->
      <!-- タブ: 給与承認                       -->
      <!-- ════════════════════════════════════ -->
      <div v-if="activeTab === 'salary'">

        <!-- 給与承認待ち -->
        <div class="bg-white rounded-xl border mb-6 overflow-hidden">
          <div class="px-5 py-4 border-b bg-yellow-50">
            <h2 class="font-semibold text-gray-700">
              ⏳ 給与承認待ち
              <span v-if="draftSalaries?.length > 0"
                class="ml-2 bg-yellow-400 text-white text-xs px-2 py-0.5 rounded-full">
                {{ draftSalaries.length }}
              </span>
            </h2>
          </div>
          <div v-if="!draftSalaries || draftSalaries.length === 0"
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
          <div v-if="!activePayrolls || activePayrolls.length === 0"
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