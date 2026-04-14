<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const props = defineProps({
  user:            Object,
  myTasks:         Array,
  myAbsences:      Array,
  availability:    Object,
  myEvaluations:   Array,
})

const page  = usePage()
const flash = computed(() => page.props.flash ?? {})

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
    icon:  '📅',
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
  GOOD:    'text-green-600', FAIR: 'text-yellow-600', WARNING: 'text-red-600',
}

const activeTasks = computed(() =>
  (props.myTasks ?? []).filter(t =>
    ['ASSIGNED', 'IN_PROGRESS', 'DELAYED', 'ESCALATED'].includes(t.task_status)
  )
)
const completedTasks = computed(() =>
  (props.myTasks ?? []).filter(t => t.task_status === 'COMPLETED')
)

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
        <!-- 部署ページへ戻る -->
        <a v-if="user.role_type === 'investigator_user'" href="/admin/investigator"
          class="text-sm text-teal-600 hover:text-teal-800 font-medium flex items-center gap-1">
          ← Tim Investigasi
        </a>
        <a v-else-if="user.role_type === 'admin_user'" href="/admin/admin"
          class="text-sm text-teal-600 hover:text-teal-800 font-medium flex items-center gap-1">
          ← Tim Admin
        </a>
        <a v-else-if="user.role_type === 'local_manager'" href="/manager/dashboard"
          class="text-sm text-teal-600 hover:text-teal-800 font-medium flex items-center gap-1">
          ← Manager Panel
        </a>
        <a v-else-if="user.role_type === 'strategy_user'" href="/strategy/dashboard"
          class="text-sm text-teal-600 hover:text-teal-800 font-medium flex items-center gap-1">
          ← Tim Strategi
        </a>
        <a v-else-if="user.role_type === 'ai_dev_user'" href="/ai-dev/dashboard"
          class="text-sm text-teal-600 hover:text-teal-800 font-medium flex items-center gap-1">
          ← Tim AI Dev
        </a>
        <a v-else-if="user.role_type === 'marketing_user'" href="/marketing/dashboard"
          class="text-sm text-teal-600 hover:text-teal-800 font-medium flex items-center gap-1">
          ← Tim Marketing
        </a>
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-600">{{ user?.name }}</span>
          <!-- 稼働状況バッジ -->
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
          <!-- バッジ -->
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
            <div v-for="task in activeTasks" :key="task.id"
              class="px-5 py-3">
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-800 truncate">
                    {{ task.title ?? '—' }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    {{ task.due_date ? '📅 ' + task.due_date : '期限なし' }}
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

        <!-- 右カラム：欠勤申請・査定 -->
        <div class="space-y-4">

          <!-- 欠勤申請履歴 -->
          <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-5 py-4 border-b bg-gray-50 flex items-center justify-between">
              <h2 class="font-semibold text-gray-700">📅 Riwayat Izin</h2>
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

          <!-- 自分の査定履歴 -->
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
  </div>
</template>