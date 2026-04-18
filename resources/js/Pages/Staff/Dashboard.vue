<script setup>
import { computed, ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import { v4 as uuidv4 } from 'uuid'

const props = defineProps({
  user:          Object,
  myTasks:       Array,
  myAbsences:    Array,
  availability:  Object,
  myEvaluations: Array,
})

const page  = usePage()
const flash = computed(() => page.props.flash ?? {})

// ================================================================
// タブ管理
// ================================================================
const activeTab = ref('dashboard')

// ================================================================
// メニュー
// ================================================================
const menuItems = [
  {
    label: 'Tugas Saya',
    href:  '/staff/tasks',
    icon:  '📋',
    desc:  'Lihat dan kerjakan instruksi yang ditugaskan',
    badge: props.myTasks?.filter(t => t.task_status === 'ASSIGNED').length ?? 0,
  },
  {
    label: 'Pengajuan Izin',
    href:  '/staff/absence/create',
    icon:  '🗓️',
    desc:  'Ajukan izin atau cuti',
    badge: 0,
  },
  {
    label: 'Modul Pendidikan',
    href:  '/staff/education',
    icon:  '📚',
    desc:  'Selesaikan modul pendidikan wajib',
    badge: 0,
  },
]

// ================================================================
// AIチャット
// ================================================================
const chatMessages = ref([])
const chatInput    = ref('')
const chatLoading  = ref(false)
const sessionId    = ref('staff-' + uuidv4())
const errorMsg     = ref('')

async function sendChat() {
  if (!chatInput.value.trim() || chatLoading.value) return

  const userMessage = chatInput.value.trim()
  chatMessages.value.push({ role: 'user', content: userMessage })
  chatInput.value  = ''
  chatLoading.value = true
  errorMsg.value   = ''

  try {
    const history = chatMessages.value.slice(-6).map(m => ({
      role:    m.role,
      content: m.content,
    }))

    const res = await axios.post(route('staff.chat.send'), {
      message:    userMessage,
      session_id: sessionId.value,
      history:    history.slice(0, -1),
    })

    chatMessages.value.push({
      role:    'assistant',
      content: res.data.message,
    })
  } catch (e) {
    errorMsg.value = e.response?.data?.error || 'Terjadi kesalahan. Coba lagi.'
  } finally {
    chatLoading.value = false
    setTimeout(() => {
      document.getElementById('chat-bottom-staff')?.scrollIntoView({ behavior: 'smooth' })
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
        session_id:   log.session_id,
        messages:     [],
        firstUserMsg: null,
        createdAt:    log.created_at,
      }
    }
    groups[log.session_id].messages.push(log)
    if (log.message_role === 'user' && !groups[log.session_id].firstUserMsg) {
      groups[log.session_id].firstUserMsg = log.message_content
    }
  }
  return Object.values(groups).sort(
    (a, b) => new Date(b.createdAt) - new Date(a.createdAt)
  )
})

async function loadHistory() {
  historyLoading.value = true
  try {
    const res = await axios.get(route('staff.chat.history'))
    chatHistory.value = res.data.history ?? []
  } catch (e) {
    console.error('Gagal memuat riwayat:', e)
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
// ラベル・スタイル
// ================================================================
const taskStatusLabel = {
  ASSIGNED:    'Ditugaskan',
  IN_PROGRESS: 'Berlangsung',
  COMPLETED:   'Selesai',
  DELAYED:     'Terlambat',
  ESCALATED:   'Dieskalasi',
}
const taskStatusColor = {
  ASSIGNED:    'bg-blue-100 text-blue-700',
  IN_PROGRESS: 'bg-indigo-100 text-indigo-700',
  COMPLETED:   'bg-green-100 text-green-700',
  DELAYED:     'bg-orange-100 text-orange-700',
  ESCALATED:   'bg-red-100 text-red-700',
}
const priorityColor = {
  URGENT: 'bg-red-100 text-red-700',
  HIGH:   'bg-orange-100 text-orange-700',
  NORMAL: 'bg-blue-100 text-blue-700',
  LOW:    'bg-gray-100 text-gray-500',
}
const absenceStatusLabel = {
  PENDING:  'Menunggu',
  APPROVED: 'Disetujui',
  REJECTED: 'Ditolak',
}
const absenceStatusColor = {
  PENDING:  'bg-yellow-100 text-yellow-700',
  APPROVED: 'bg-green-100 text-green-700',
  REJECTED: 'bg-red-100 text-red-700',
}
const availLabel = {
  AVAILABLE: 'Tersedia', BUSY: 'Sibuk',
  ON_LEAVE: 'Cuti', SUSPENDED: 'Ditangguhkan',
}
const availColor = {
  AVAILABLE: 'bg-green-100 text-green-700',
  BUSY:      'bg-blue-100 text-blue-700',
  ON_LEAVE:  'bg-yellow-100 text-yellow-700',
  SUSPENDED: 'bg-red-100 text-red-700',
}
const bandLabel = { GOOD: 'Baik', FAIR: 'Cukup', WARNING: 'Peringatan' }
const bandColor = {
  GOOD: 'text-green-600', FAIR: 'text-yellow-600', WARNING: 'text-red-600',
}

const activeTasks = computed(() =>
  (props.myTasks ?? []).filter(t =>
    ['ASSIGNED', 'IN_PROGRESS', 'DELAYED', 'ESCALATED'].includes(t.task_status)
  )
)
const completedTasks = computed(() =>
  (props.myTasks ?? []).filter(t => t.task_status === 'COMPLETED')
)

function formatDate(val) {
  if (!val) return '-'
  return new Date(val).toLocaleDateString('id-ID', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function logout() {
  router.post('/staff/logout')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Navbar -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-10">
      <div class="max-w-4xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-teal-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
            HRI
          </div>
          <div>
            <p class="font-bold text-gray-800 text-sm">HRI System</p>
            <p class="text-xs text-gray-400">Halaman Saya</p>
          </div>
        </div>

        <!-- タブ切り替え -->
        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
          <button @click="activeTab = 'dashboard'"
            :class="['px-4 py-1.5 rounded-md text-sm font-medium transition',
              activeTab === 'dashboard'
                ? 'bg-white shadow text-teal-700'
                : 'text-gray-500 hover:text-gray-700']">
            🏠 Dashboard
          </button>
          <button @click="activeTab = 'chat'"
            :class="['px-4 py-1.5 rounded-md text-sm font-medium transition',
              activeTab === 'chat'
                ? 'bg-white shadow text-teal-700'
                : 'text-gray-500 hover:text-gray-700']">
            💬 AI Chat
          </button>
        </div>

        <div class="flex items-center gap-3">
          <!-- 部署ページへ戻る -->
          <a v-if="user.role_type === 'investigator_user'" href="/admin/investigator"
            class="text-sm text-teal-600 hover:text-teal-800 font-medium">← Investigasi</a>
          <a v-else-if="user.role_type === 'admin_user'" href="/admin/admin"
            class="text-sm text-teal-600 hover:text-teal-800 font-medium">← Admin</a>
          <a v-else-if="user.role_type === 'local_manager'" href="/manager/dashboard"
            class="text-sm text-teal-600 hover:text-teal-800 font-medium">← Manager</a>
          <a v-else-if="user.role_type === 'strategy_user'" href="/strategy/dashboard"
            class="text-sm text-teal-600 hover:text-teal-800 font-medium">← Strategi</a>
          <a v-else-if="user.role_type === 'ai_dev_user'" href="/ai-dev/dashboard"
            class="text-sm text-teal-600 hover:text-teal-800 font-medium">← AI Dev</a>
          <a v-else-if="user.role_type === 'marketing_user'" href="/marketing/dashboard"
            class="text-sm text-teal-600 hover:text-teal-800 font-medium">← Marketing</a>

          <span class="text-sm text-gray-600">{{ user?.name }}</span>
          <span v-if="availability"
            :class="['text-xs px-2 py-0.5 rounded-full font-semibold',
              availColor[availability.availability_status]]">
            {{ availLabel[availability.availability_status] ?? availability.availability_status }}
          </span>
          <button @click="logout"
            class="text-xs text-gray-500 hover:text-red-500 transition">Logout</button>
        </div>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-8">

      <!-- Flash -->
      <div v-if="flash.success"
        class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded text-sm">
        {{ flash.success }}
      </div>
      <div v-if="flash.error"
        class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded text-sm">
        {{ flash.error }}
      </div>

      <!-- ウェルカムバナー -->
      <div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-2xl px-6 py-5 text-white mb-6">
        <p class="text-lg font-bold">Selamat datang, {{ user?.name }}</p>
        <p class="text-teal-200 text-sm mt-1">
          Halaman pribadi Anda — lihat tugas, pengajuan izin, dan penilaian kinerja
        </p>
      </div>

      <!-- ════════════════════════════════════ -->
      <!-- タブ: ダッシュボード                 -->
      <!-- ════════════════════════════════════ -->
      <div v-if="activeTab === 'dashboard'">

        <!-- 統計カード -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-3xl font-bold text-teal-600">{{ activeTasks.length }}</p>
            <p class="text-xs text-gray-500 mt-1">Tugas Aktif</p>
          </div>
          <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-3xl font-bold text-green-600">{{ completedTasks.length }}</p>
            <p class="text-xs text-gray-500 mt-1">Tugas Selesai</p>
          </div>
          <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-3xl font-bold text-yellow-500">
              {{ (myAbsences ?? []).filter(a => a.approval_status === 'PENDING').length }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Izin Menunggu</p>
          </div>
          <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-3xl font-bold text-indigo-600">
              {{ availability?.active_task_count ?? 0 }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Tugas Berjalan</p>
          </div>
        </div>

        <!-- メニューカード -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <a v-for="item in menuItems" :key="item.label"
            :href="item.href"
            class="bg-white rounded-xl border border-gray-200 p-5 hover:border-teal-300 hover:shadow-md transition group relative">
            <div class="text-3xl mb-3">{{ item.icon }}</div>
            <p class="font-semibold text-gray-800 group-hover:text-teal-600 transition">
              {{ item.label }}
            </p>
            <p class="text-xs text-gray-500 mt-1">{{ item.desc }}</p>
            <span v-if="item.badge > 0"
              class="absolute top-3 right-3 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
              {{ item.badge }}
            </span>
          </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- アクティブタスク一覧 -->
          <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-5 py-4 border-b bg-teal-50">
              <h2 class="font-semibold text-teal-800">📋 Tugas Aktif</h2>
            </div>
            <div class="divide-y divide-gray-100">
              <div v-if="activeTasks.length === 0"
                class="px-5 py-6 text-center text-gray-400 text-sm">
                Tidak ada tugas aktif saat ini
              </div>
              <div v-for="task in activeTasks" :key="task.id" class="px-5 py-3">
                <div class="flex items-start justify-between gap-2">
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">
                      {{ task.title ?? '-' }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ task.due_date ? '🗓️ ' + task.due_date : '期限なし' }}
                    </p>
                  </div>
                  <div class="flex flex-col items-end gap-1 shrink-0">
                    <span :class="['text-xs px-2 py-0.5 rounded-full font-medium',
                      taskStatusColor[task.task_status]]">
                      {{ taskStatusLabel[task.task_status] ?? task.task_status }}
                    </span>
                    <span :class="['text-xs px-1.5 py-0.5 rounded font-medium',
                      priorityColor[task.priority]]">
                      {{ task.priority }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="px-5 py-3 border-t bg-gray-50">
              <a href="/staff/tasks"
                class="text-xs text-teal-600 hover:text-teal-800 font-medium">
                すべてのタスクを見る →
              </a>
            </div>
          </div>

          <!-- 右カラム -->
          <div class="space-y-4">

            <!-- 欠勤申請履歴 -->
            <div class="bg-white rounded-xl border overflow-hidden">
              <div class="px-5 py-4 border-b bg-gray-50 flex items-center justify-between">
                <h2 class="font-semibold text-gray-700">🗓️ Riwayat Izin</h2>
                <a href="/staff/absence/create"
                  class="text-xs text-teal-600 hover:text-teal-800 font-medium">
                  + Ajukan Izin
                </a>
              </div>
              <div class="divide-y divide-gray-100">
                <div v-if="!myAbsences || myAbsences.length === 0"
                  class="px-5 py-4 text-center text-gray-400 text-sm">
                  Belum ada pengajuan izin
                </div>
                <div v-for="abs in (myAbsences ?? []).slice(0, 3)" :key="abs.id"
                  class="px-5 py-3 flex items-center justify-between">
                  <div>
                    <p class="text-sm text-gray-700">{{ abs.absence_type }}</p>
                    <p class="text-xs text-gray-400">
                      {{ abs.absence_date_from }} 〜 {{ abs.absence_date_to }}
                    </p>
                  </div>
                  <span :class="['text-xs px-2 py-0.5 rounded-full font-medium',
                    absenceStatusColor[abs.approval_status]]">
                    {{ absenceStatusLabel[abs.approval_status] ?? abs.approval_status }}
                  </span>
                </div>
              </div>
            </div>

            <!-- 自分の評価履歴 -->
            <div class="bg-white rounded-xl border overflow-hidden">
              <div class="px-5 py-4 border-b bg-gray-50">
                <h2 class="font-semibold text-gray-700">📊 Penilaian Kinerja</h2>
              </div>
              <div class="divide-y divide-gray-100">
                <div v-if="!myEvaluations || myEvaluations.length === 0"
                  class="px-5 py-4 text-center text-gray-400 text-sm">
                  Belum ada penilaian
                </div>
                <div v-for="ev in (myEvaluations ?? []).slice(0, 3)" :key="ev.id"
                  class="px-5 py-3 flex items-center justify-between">
                  <div>
                    <p class="text-xs text-gray-400">
                      {{ ev.evaluation_period_from }} 〜 {{ ev.evaluation_period_to }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ ev.evaluation_type }}</p>
                  </div>
                  <div class="text-right">
                    <p v-if="ev.human_final_band"
                      :class="['text-sm font-bold', bandColor[ev.human_final_band]]">
                      {{ bandLabel[ev.human_final_band] ?? ev.human_final_band }}
                    </p>
                    <p v-else class="text-xs text-gray-400">確定待ち</p>
                    <p v-if="ev.ai_score" class="text-xs text-gray-400">
                      スコア: {{ ev.ai_score }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- ════════════════════════════════════ -->
      <!-- タブ: AI チャット                   -->
      <!-- ════════════════════════════════════ -->
      <div v-if="activeTab === 'chat'" class="space-y-4">

        <!-- サブタブ -->
        <div class="flex gap-0 border-b border-gray-200">
          <button
            @click="chatSubTab = 'new'"
            :class="['px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px',
              chatSubTab === 'new'
                ? 'border-teal-600 text-teal-700'
                : 'border-transparent text-gray-500 hover:text-gray-700']">
            💬 Chat Baru
          </button>
          <button
            @click="chatSubTab = 'history'"
            :class="['px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px',
              chatSubTab === 'history'
                ? 'border-teal-600 text-teal-700'
                : 'border-transparent text-gray-500 hover:text-gray-700']">
            📋 Riwayat
          </button>
        </div>

        <!-- ===== 新規チャット ===== -->
        <div v-if="chatSubTab === 'new'">
          <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-5 py-4 border-b bg-teal-50">
              <h2 class="font-semibold text-teal-800">🤖 AI Asisten Staf</h2>
              <p class="text-xs text-teal-600 mt-0.5">
                Tanyakan tentang prosedur kerja, cara mengisi laporan, atau pengajuan izin
              </p>
            </div>

            <!-- チャット本文 -->
            <div class="h-96 overflow-y-auto p-5 space-y-4 bg-gray-50">

              <!-- 初期メッセージ -->
              <div v-if="chatMessages.length === 0"
                class="flex justify-center items-center h-full">
                <div class="text-center text-gray-400">
                  <p class="text-4xl mb-3">🤖</p>
                  <p class="text-sm font-medium">Halo! Ada yang bisa saya bantu?</p>
                  <p class="text-xs mt-1 text-gray-400">
                    Tanyakan tentang tugas, laporan, izin, atau prosedur kerja
                  </p>
                  <div class="mt-4 space-y-2">
                    <button @click="chatInput = 'Bagaimana cara mengisi laporan tugas dengan benar?'"
                      class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-teal-50 text-gray-600">
                      💡 「Bagaimana cara mengisi laporan tugas dengan benar?」
                    </button>
                    <button @click="chatInput = 'Apa saja dokumen yang diperlukan untuk pengajuan izin sakit?'"
                      class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-teal-50 text-gray-600">
                      💡 「Apa saja dokumen yang diperlukan untuk pengajuan izin sakit?」
                    </button>
                    <button @click="chatInput = 'Apa yang harus saya lakukan jika tugas saya terlambat?'"
                      class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-teal-50 text-gray-600">
                      💡 「Apa yang harus saya lakukan jika tugas saya terlambat?」
                    </button>
                  </div>
                </div>
              </div>

              <!-- メッセージ一覧 -->
              <div v-for="(msg, idx) in chatMessages" :key="idx"
                :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
                <div :class="['max-w-lg rounded-xl px-4 py-3 text-sm',
                  msg.role === 'user'
                    ? 'bg-teal-600 text-white'
                    : 'bg-white border text-gray-800 shadow-sm']">
                  <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                </div>
              </div>

              <!-- ローディング -->
              <div v-if="chatLoading" class="flex justify-start">
                <div class="bg-white border rounded-xl px-4 py-3 shadow-sm">
                  <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                    <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                    <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
                  </div>
                </div>
              </div>

              <div id="chat-bottom-staff"></div>
            </div>

            <!-- エラー表示 -->
            <div v-if="errorMsg"
              class="px-5 py-2 bg-red-50 border-t border-red-200 text-red-600 text-xs">
              ⚠ {{ errorMsg }}
            </div>

            <!-- 入力エリア -->
            <div class="px-5 py-4 border-t bg-white">
              <div class="flex gap-3">
                <textarea
                  v-model="chatInput"
                  @keydown="handleEnter"
                  placeholder="Ketik pertanyaan Anda... (Enter untuk kirim, Shift+Enter untuk baris baru)"
                  rows="2"
                  class="flex-1 border rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-teal-300"
                ></textarea>
                <button
                  @click="sendChat"
                  :disabled="chatLoading || !chatInput.trim()"
                  :class="['px-5 py-3 rounded-xl text-sm font-medium transition',
                    chatLoading || !chatInput.trim()
                      ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                      : 'bg-teal-600 text-white hover:bg-teal-700']">
                  {{ chatLoading ? 'Mengirim...' : 'Kirim' }}
                </button>
              </div>
              <p class="text-xs text-gray-400 mt-2">
                💡 AI ini hanya untuk tanya jawab – tidak dapat membuat instruksi tugas untuk orang lain
              </p>
            </div>
          </div>
        </div>

        <!-- ===== 履歴 ===== -->
        <div v-if="chatSubTab === 'history'">
          <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-5 py-4 border-b bg-teal-50 flex items-center justify-between">
              <h2 class="font-semibold text-teal-800">📋 Riwayat Chat AI</h2>
              <button @click="loadHistory"
                class="text-xs text-teal-600 hover:text-teal-800 border border-teal-200 px-3 py-1 rounded-lg transition">
                🔄 Refresh
              </button>
            </div>

            <!-- ローディング -->
            <div v-if="historyLoading" class="px-5 py-10 text-center text-gray-400 text-sm">
              <div class="flex justify-center gap-1 mb-3">
                <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                <div class="w-2 h-2 bg-teal-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
              </div>
              Memuat riwayat...
            </div>

            <!-- 履歴なし -->
            <div v-else-if="groupedHistory.length === 0"
              class="px-5 py-12 text-center text-gray-400 text-sm">
              <p class="text-3xl mb-2">📭</p>
              <p>Belum ada riwayat chat</p>
            </div>

            <!-- セッション別履歴 -->
            <div v-else class="divide-y divide-gray-100">
              <div v-for="session in groupedHistory" :key="session.session_id">

                <button
                  @click="toggleSession(session.session_id)"
                  class="w-full px-5 py-4 flex items-start justify-between gap-3 hover:bg-gray-50 transition text-left">
                  <div class="flex items-start gap-3 min-w-0">
                    <span class="shrink-0 text-xs bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full mt-0.5">
                      💬 Chat
                    </span>
                    <p class="text-sm text-gray-700 truncate">
                      {{ session.firstUserMsg ?? '（Tidak ada isi）' }}
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
                        ? 'bg-teal-600 text-white'
                        : 'bg-white border text-gray-800 shadow-sm']">
                      <p class="whitespace-pre-wrap text-xs leading-relaxed">
                        {{ msg.message_content }}
                      </p>
                      <p class="text-xs mt-1.5 opacity-50">{{ formatDate(msg.created_at) }}</p>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>