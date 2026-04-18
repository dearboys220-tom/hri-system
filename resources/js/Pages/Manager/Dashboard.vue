<script setup>
import { computed, ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const user = computed(() => page.props.auth?.user)

// ================================================================
// メニュー
// ================================================================
const menuItems = [
    {
        label: 'Manajemen Staf',
        href:  '/manager/staff',
        icon:  '👥',
        desc:  'Daftar & pendaftaran staf internal',
    },
    {
        label: 'Instruksi Tugas',
        href:  '/manager/task-orders',
        icon:  '📋',
        desc:  'Buat dan kelola instruksi kerja – termasuk persetujuan instruksi dari President',
    },
    {
        label: 'Laporan Tugas',
        href:  '/manager/reports',
        icon:  '📊',
        desc:  'Daftar laporan yang dikirim oleh staf',
    },
    {
        label: 'Pengajuan Izin',
        href:  '/manager/absence-requests',
        icon:  '🗓️',
        desc:  'Setujui atau tolak pengajuan izin staf',
    },
    {
        label: 'Penilaian AI',
        href:  '/manager/evaluations',
        icon:  '📊',
        desc:  'Buat dan konfirmasi penilaian kinerja staf',
    },
    {
        label: 'Manajemen Gaji',
        href:  '/manager/salary',
        icon:  '💰',
        desc:  'Perhitungan dan persetujuan gaji staf',
    },
    {
        label: 'Catatan Pembayaran',
        href:  '/manager/payroll',
        icon:  '🏦',
        desc:  'Proses dan konfirmasi pembayaran gaji staf',
    },
]

// ================================================================
// タブ管理
// ================================================================
const activeTab = ref('menu')

// ================================================================
// スタッフ一覧（チャット送信先用）※ props から受け取る想定
// ================================================================
const props = defineProps({
    allStaff: { type: Array, default: () => [] },
})

// ================================================================
// AIチャット
// ================================================================
const chatMessages     = ref([])
const chatInput        = ref('')
const chatLoading      = ref(false)
const sessionId        = ref('manager-' + Date.now())
const selectedStaffIds = ref([])

const roleLabel = {
    investigator_user: 'Investigasi',
    admin_user:        'Admin',
    em_staff:          'Staf Umum',
    strategy_user:     'Strategi',
    ai_dev_user:       'AI Dev',
    marketing_user:    'Marketing',
}

function toggleStaff(id) {
    if (selectedStaffIds.value.includes(id)) {
        selectedStaffIds.value = selectedStaffIds.value.filter(i => i !== id)
    } else {
        selectedStaffIds.value.push(id)
    }
}
function selectAll() {
    selectedStaffIds.value = (props.allStaff ?? []).map(s => s.id)
}
function clearSelection() {
    selectedStaffIds.value = []
}

async function sendChat() {
    if (!chatInput.value.trim() || chatLoading.value) return

    const userMessage = chatInput.value.trim()
    const targets     = [...selectedStaffIds.value]

    const targetNames = targets.length > 0
        ? (props.allStaff ?? []).filter(s => targets.includes(s.id)).map(s => s.name).join(', ')
        : '（Tanpa penerima）'

    chatMessages.value.push({
        role:     'user',
        content:  userMessage,
        targets:  targetNames,
    })
    chatInput.value   = ''
    chatLoading.value = true

    try {
        const history = chatMessages.value.slice(-6).map(m => ({
            role:    m.role === 'user' ? 'user' : 'assistant',
            content: m.content,
        }))

        const res = await axios.post(route('manager.chat.send'), {
            message:      userMessage,
            session_id:   sessionId.value,
            history:      history,
            assignee_ids: targets,
        })

        const data = res.data
        chatMessages.value.push({
            role:               'assistant',
            content:            data.message,
            is_task_instruction: data.is_task_instruction,
            task_order_created: data.task_order_created,
            task_assigned:      data.task_assigned,
            assignee_count:     data.assignee_count,
        })

        selectedStaffIds.value = []

    } catch (e) {
        chatMessages.value.push({
            role:    'assistant',
            content: 'Terjadi kesalahan. Silakan coba lagi.',
        })
    } finally {
        chatLoading.value = false
        setTimeout(() => {
            document.getElementById('chat-bottom-mgr')?.scrollIntoView({ behavior: 'smooth' })
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
        const res = await axios.get(route('manager.chat.history'))
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
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        HRI
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">HRI System</p>
                        <p class="text-xs text-gray-400">Manager Panel</p>
                    </div>
                </div>

                <!-- タブ切り替え -->
                <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                    <button @click="activeTab = 'menu'"
                        :class="['px-4 py-1.5 rounded-md text-sm font-medium transition',
                            activeTab === 'menu'
                                ? 'bg-white shadow text-indigo-700'
                                : 'text-gray-500 hover:text-gray-700']">
                        🏠 Menu
                    </button>
                    <button @click="activeTab = 'chat'"
                        :class="['px-4 py-1.5 rounded-md text-sm font-medium transition',
                            activeTab === 'chat'
                                ? 'bg-white shadow text-indigo-700'
                                : 'text-gray-500 hover:text-gray-700']">
                        💬 AI Chat
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ user?.name }}</span>
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded font-semibold">Local Manager</span>
                    <button @click="logout" class="text-xs text-gray-500 hover:text-red-500 transition">Logout</button>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-6 py-8">

            <!-- ウェルカムバナー -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-2xl px-6 py-5 text-white mb-6">
                <p class="text-lg font-bold">Selamat datang, {{ user?.name }}</p>
                <p class="text-indigo-200 text-sm mt-1">Panel Manager – Kelola staf dan operasional internal HRI</p>
            </div>

            <!-- ════════════════════════════════════ -->
            <!-- タブ: メニュー                       -->
            <!-- ════════════════════════════════════ -->
            <div v-if="activeTab === 'menu'">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a
                        v-for="item in menuItems"
                        :key="item.label"
                        :href="item.href"
                        class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-md transition group"
                    >
                        <div class="text-3xl mb-3">{{ item.icon }}</div>
                        <p class="font-semibold text-gray-800 group-hover:text-indigo-600 transition">{{ item.label }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ item.desc }}</p>
                    </a>
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
                                ? 'border-indigo-600 text-indigo-700'
                                : 'border-transparent text-gray-500 hover:text-gray-700']">
                        💬 Chat Baru
                    </button>
                    <button
                        @click="chatSubTab = 'history'"
                        :class="['px-5 py-2.5 text-sm font-medium border-b-2 transition -mb-px',
                            chatSubTab === 'history'
                                ? 'border-indigo-600 text-indigo-700'
                                : 'border-transparent text-gray-500 hover:text-gray-700']">
                        📋 Riwayat
                    </button>
                </div>

                <!-- ===== 新規チャット ===== -->
                <div v-if="chatSubTab === 'new'" class="space-y-4">

                    <!-- 送信先選択 -->
                    <div v-if="allStaff && allStaff.length > 0"
                        class="bg-white rounded-xl border overflow-hidden">
                        <div class="px-5 py-3 bg-indigo-50 border-b flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-3">
                                <h3 class="font-semibold text-indigo-800 text-sm">👥 Pilih Penerima</h3>
                                <span class="text-xs text-indigo-500">
                                    {{ selectedStaffIds.length > 0
                                        ? selectedStaffIds.length + ' staf dipilih'
                                        : 'Belum dipilih（instruksi saja）' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="selectAll"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 px-2 py-1 rounded">
                                    Pilih Semua
                                </button>
                                <button @click="clearSelection"
                                    class="text-xs text-gray-500 hover:text-gray-700 border px-2 py-1 rounded">
                                    Hapus
                                </button>
                            </div>
                        </div>
                        <div class="px-4 py-3 flex flex-wrap gap-2 min-h-12">
                            <button
                                v-for="s in allStaff" :key="s.id"
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
                        <div class="px-5 py-4 border-b bg-indigo-50">
                            <h2 class="font-semibold text-indigo-800">🤖 AI Asisten Manager</h2>
                            <p class="text-xs text-indigo-600 mt-0.5">
                                Ketik instruksi atau pertanyaan – AI akan membantu membuat instruksi tugas
                            </p>
                        </div>

                        <!-- チャット本文 -->
                        <div class="h-96 overflow-y-auto p-5 space-y-4 bg-gray-50">

                            <!-- 初期メッセージ -->
                            <div v-if="chatMessages.length === 0"
                                class="flex justify-center items-center h-full">
                                <div class="text-center text-gray-400">
                                    <p class="text-4xl mb-3">🤖</p>
                                    <p class="text-sm font-medium">Pilih penerima lalu ketik instruksi Anda</p>
                                    <p class="text-xs mt-1 text-gray-400">Tanpa penerima, instruksi tetap dibuat dan dapat dialokasikan nanti</p>
                                    <div class="mt-4 space-y-2">
                                        <button @click="chatInput = 'Tolong buat laporan progres minggu ini untuk semua staf investigasi'"
                                            class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-indigo-50 text-gray-600">
                                            💡 「Tolong buat laporan progres minggu ini untuk semua staf investigasi」
                                        </button>
                                        <button @click="chatInput = 'Harap kirimkan ringkasan tugas yang belum selesai kepada tim'"
                                            class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-indigo-50 text-gray-600">
                                            💡 「Harap kirimkan ringkasan tugas yang belum selesai kepada tim」
                                        </button>
                                        <button @click="chatInput = 'Mohon konfirmasi kehadiran staf untuk minggu depan'"
                                            class="block w-full text-left text-xs bg-white border rounded-lg px-3 py-2 hover:bg-indigo-50 text-gray-600">
                                            💡 「Mohon konfirmasi kehadiran staf untuk minggu depan」
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- メッセージ一覧 -->
                            <div v-for="(msg, idx) in chatMessages" :key="idx"
                                :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
                                <div :class="['max-w-lg rounded-xl px-4 py-3 text-sm',
                                    msg.role === 'user'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-white border text-gray-800 shadow-sm']">

                                    <!-- AI返答 -->
                                    <div v-if="msg.role === 'assistant'">
                                        <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                                        <div v-if="msg.task_order_created"
                                            :class="['mt-2 rounded px-3 py-2 border',
                                                msg.task_assigned
                                                    ? 'bg-green-50 border-green-200'
                                                    : 'bg-yellow-50 border-yellow-200']">
                                            <p :class="['text-xs font-medium',
                                                msg.task_assigned ? 'text-green-700' : 'text-yellow-700']">
                                                ✅ Instruksi tugas berhasil dibuat
                                            </p>
                                            <p v-if="msg.task_assigned"
                                                class="text-xs text-green-600 mt-0.5">
                                                Ditetapkan ke {{ msg.assignee_count }} staf
                                            </p>
                                            <p v-else class="text-xs text-yellow-600 mt-0.5">
                                                Belum ada penerima. Dapat dialokasikan dari menu Instruksi Tugas.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- ユーザーメッセージ -->
                                    <div v-else>
                                        <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                                        <p v-if="msg.targets" class="text-xs text-indigo-200 mt-1">
                                            📤 Penerima: {{ msg.targets }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ローディング -->
                            <div v-if="chatLoading" class="flex justify-start">
                                <div class="bg-white border rounded-xl px-4 py-3 shadow-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                                        <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                                        <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
                                    </div>
                                </div>
                            </div>

                            <div id="chat-bottom-mgr"></div>
                        </div>

                        <!-- 入力エリア -->
                        <div class="px-5 py-4 border-t bg-white">
                            <div v-if="selectedStaffIds.length > 0" class="mb-2 flex flex-wrap gap-1 items-center">
                                <span class="text-xs text-gray-500">Penerima:</span>
                                <span v-for="id in selectedStaffIds" :key="id"
                                    class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                                    {{ (allStaff ?? []).find(s => s.id === id)?.name ?? id }}
                                </span>
                            </div>
                            <div class="flex gap-3">
                                <textarea
                                    v-model="chatInput"
                                    @keydown="handleEnter"
                                    placeholder="Ketik instruksi atau pertanyaan... (Enter untuk kirim, Shift+Enter untuk baris baru)"
                                    rows="2"
                                    class="flex-1 border rounded-xl px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                ></textarea>
                                <button
                                    @click="sendChat"
                                    :disabled="chatLoading || !chatInput.trim()"
                                    :class="['px-5 py-3 rounded-xl text-sm font-medium transition',
                                        chatLoading || !chatInput.trim()
                                            ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                            : 'bg-indigo-600 text-white hover:bg-indigo-700']">
                                    {{ chatLoading ? 'Mengirim...' : 'Kirim' }}
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">
                                💡 Pilih penerima untuk langsung menetapkan instruksi
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ===== 送信履歴 ===== -->
                <div v-if="chatSubTab === 'history'" class="space-y-3">
                    <div class="bg-white rounded-xl border overflow-hidden">
                        <div class="px-5 py-4 border-b bg-indigo-50 flex items-center justify-between">
                            <h2 class="font-semibold text-indigo-800">📋 Riwayat Chat AI</h2>
                            <button @click="loadHistory"
                                class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 px-3 py-1 rounded-lg transition">
                                🔄 Refresh
                            </button>
                        </div>

                        <!-- ローディング -->
                        <div v-if="historyLoading" class="px-5 py-10 text-center text-gray-400 text-sm">
                            <div class="flex justify-center gap-1 mb-3">
                                <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                                <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                                <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
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

                                <!-- セッションヘッダー -->
                                <button
                                    @click="toggleSession(session.session_id)"
                                    class="w-full px-5 py-4 flex items-start justify-between gap-3 hover:bg-gray-50 transition text-left">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <span :class="['shrink-0 text-xs font-medium px-2 py-0.5 rounded-full mt-0.5',
                                            session.hasTaskInstruction
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-gray-100 text-gray-500']">
                                            {{ session.hasTaskInstruction ? '✅ Ada instruksi' : '💬 Hanya chat' }}
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
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-white border text-gray-800 shadow-sm']">
                                            <p class="whitespace-pre-wrap text-xs leading-relaxed">
                                                {{ msg.message_content_ja ?? msg.message_content }}
                                            </p>
                                            <div v-if="msg.is_task_instruction && msg.message_role === 'user'"
                                                class="mt-1.5 text-xs text-indigo-200 font-medium">
                                                📋 Dikirim sebagai instruksi tugas
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

        </div>
    </div>
</template>