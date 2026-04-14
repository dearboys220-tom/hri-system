<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

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

function approveSalary(calc) {
  if (!confirm('Setujui perhitungan gaji untuk ' + calc.staff?.name + '?')) return
  router.post(route('president.salary.approve', calc.id))
}

function logout() {
  router.post('/staff/logout')
}

const deptLabel = {
  investigator_user: 'Investigasi',
  admin_user:        'Admin',
  em_staff:          'Staf Umum',
  strategy_user:     'Strategi',
  ai_dev_user:       'AI Dev',
  marketing_user:    'Marketing',
  local_manager:     'Manajer Lokal',
}

const availLabel = {
  AVAILABLE: 'Tersedia',
  BUSY:      'Sibuk',
  ON_LEAVE:  'Cuti',
  SUSPENDED: 'Ditangguhkan',
}
const availColor = {
  AVAILABLE: 'bg-green-100 text-green-700',
  BUSY:      'bg-blue-100 text-blue-700',
  ON_LEAVE:  'bg-yellow-100 text-yellow-700',
  SUSPENDED: 'bg-red-100 text-red-700',
}

const bandColor = {
  GOOD:    'text-green-600',
  FAIR:    'text-yellow-600',
  WARNING: 'text-red-600',
}
const bandLabel = { GOOD: 'Baik', FAIR: 'Cukup', WARNING: 'Peringatan' }

const payStatusLabel = {
  SCHEDULED: 'Dijadwalkan',
  PROCESSED: 'Diproses',
}
const payStatusColor = {
  SCHEDULED: 'bg-gray-100 text-gray-600',
  PROCESSED: 'bg-blue-100 text-blue-700',
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
    <div class="bg-white border-b border-gray-200 px-6 py-4">
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
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-600">{{ user?.name }}</span>
          <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded font-semibold">
            President
          </span>
          <button @click="logout"
            class="text-xs text-gray-500 hover:text-red-500 transition">
            Logout
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">

      <!-- ウェルカム -->
      <div class="bg-gradient-to-r from-purple-700 to-purple-800 rounded-2xl px-6 py-5 text-white mb-6">
        <p class="text-lg font-bold">Selamat datang, {{ user?.name }}</p>
        <p class="text-purple-200 text-sm mt-1">
          Panel President — Pantau seluruh operasional dan setujui penggajian
        </p>
      </div>

      <!-- Flash -->
      <div v-if="flash.success"
        class="mb-4 rounded bg-green-100 border border-green-300 px-4 py-3 text-green-800 text-sm">
        {{ flash.success }}
      </div>
      <div v-if="flash.error"
        class="mb-4 rounded bg-red-100 border border-red-300 px-4 py-3 text-red-800 text-sm">
        {{ flash.error }}
      </div>

      <!-- 統計カード -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-purple-700">{{ totalStaff }}</p>
          <p class="text-xs text-gray-500 mt-1">Total Staf Aktif</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-green-600">
            {{ availabilityStats['AVAILABLE'] ?? 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">Staf Tersedia</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-yellow-500">{{ draftSalaries.length }}</p>
          <p class="text-xs text-gray-500 mt-1">Gaji Menunggu Persetujuan</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
          <p class="text-3xl font-bold text-blue-600">{{ activePayrolls.length }}</p>
          <p class="text-xs text-gray-500 mt-1">Pembayaran Aktif</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <!-- 部署別スタッフ数 -->
        <div class="bg-white rounded-xl border p-5">
          <h2 class="font-semibold text-gray-700 mb-4">👥 Staf per Departemen</h2>
          <div class="space-y-2">
            <div v-for="(count, role) in staffByDept" :key="role"
              class="flex items-center justify-between">
              <span class="text-sm text-gray-600">{{ deptLabel[role] ?? role }}</span>
              <span class="text-sm font-semibold text-gray-800 bg-gray-100 px-3 py-0.5 rounded-full">
                {{ count }} orang
              </span>
            </div>
            <p v-if="Object.keys(staffByDept).length === 0"
              class="text-sm text-gray-400 text-center py-2">
              Belum ada data
            </p>
          </div>
        </div>

        <!-- 稼働状況 -->
        <div class="bg-white rounded-xl border p-5">
          <h2 class="font-semibold text-gray-700 mb-4">📊 Status Ketersediaan Staf</h2>
          <div class="space-y-2">
            <div v-for="(count, status) in availabilityStats" :key="status"
              class="flex items-center justify-between">
              <span :class="['text-xs font-medium px-2 py-1 rounded-full', availColor[status]]">
                {{ availLabel[status] ?? status }}
              </span>
              <span class="text-sm font-semibold text-gray-800">{{ count }} orang</span>
            </div>
            <p v-if="Object.keys(availabilityStats).length === 0"
              class="text-sm text-gray-400 text-center py-2">
              Belum ada data
            </p>
          </div>
        </div>
      </div>

      <!-- 給与承認待ち（DRAFT） -->
      <div class="bg-white rounded-xl border mb-6 overflow-hidden">
        <div class="px-5 py-4 border-b bg-yellow-50 flex items-center justify-between">
          <h2 class="font-semibold text-gray-700">
            ⏳ Gaji Menunggu Persetujuan President
            <span v-if="draftSalaries.length > 0"
              class="ml-2 bg-yellow-400 text-white text-xs px-2 py-0.5 rounded-full">
              {{ draftSalaries.length }}
            </span>
          </h2>
        </div>
        <div v-if="draftSalaries.length === 0"
          class="px-5 py-6 text-center text-gray-400 text-sm">
          Tidak ada gaji yang menunggu persetujuan
        </div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Staf</th>
              <th class="px-4 py-3 text-left">Bulan</th>
              <th class="px-4 py-3 text-center">Kinerja</th>
              <th class="px-4 py-3 text-right">Gaji Pokok</th>
              <th class="px-4 py-3 text-right">Gaji Bersih</th>
              <th class="px-4 py-3 text-left">Catatan AI</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="calc in draftSalaries" :key="calc.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-800">
                {{ calc.staff?.name ?? '-' }}
                <div class="text-xs text-gray-400">
                  {{ deptLabel[calc.staff?.role_type] ?? calc.staff?.role_type }}
                </div>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ calc.calculation_month }}</td>
              <td class="px-4 py-3 text-center">
                <span v-if="calc.performance_band"
                  :class="['text-xs font-bold', bandColor[calc.performance_band]]">
                  {{ bandLabel[calc.performance_band] ?? calc.performance_band }}
                </span>
                <span v-else class="text-xs text-gray-400">-</span>
              </td>
              <td class="px-4 py-3 text-right text-gray-700">
                {{ rupiah(calc.base_salary) }}
              </td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800">
                {{ rupiah(calc.net_salary) }}
              </td>
              <td class="px-4 py-3 text-gray-500 max-w-xs">
                <p class="line-clamp-2 text-xs">{{ calc.ai_calculation_notes ?? '-' }}</p>
              </td>
              <td class="px-4 py-3 text-center">
                <button @click="approveSalary(calc)"
                  class="px-3 py-1 bg-purple-600 text-white rounded text-xs hover:bg-purple-700">
                  Setujui
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 支払い進行中 -->
      <div class="bg-white rounded-xl border mb-6 overflow-hidden">
        <div class="px-5 py-4 border-b bg-gray-50">
          <h2 class="font-semibold text-gray-700">🏦 Pembayaran Sedang Berjalan</h2>
        </div>
        <div v-if="activePayrolls.length === 0"
          class="px-5 py-6 text-center text-gray-400 text-sm">
          Tidak ada pembayaran yang sedang berjalan
        </div>
        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Staf</th>
              <th class="px-4 py-3 text-left">Bulan</th>
              <th class="px-4 py-3 text-right">Jumlah</th>
              <th class="px-4 py-3 text-left">Metode</th>
              <th class="px-4 py-3 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="p in activePayrolls" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-800">
                {{ p.staff?.name ?? '-' }}
              </td>
              <td class="px-4 py-3 text-gray-600">{{ p.payment_month }}</td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800">
                {{ rupiah(p.paid_amount) }}
              </td>
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

      <!-- 直近の監査ログ -->
      <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-5 py-4 border-b bg-gray-50">
          <h2 class="font-semibold text-gray-700">🔍 Aktivitas Terbaru</h2>
        </div>
        <div class="divide-y divide-gray-100">
          <div v-if="recentLogs.length === 0"
            class="px-5 py-6 text-center text-gray-400 text-sm">
            Belum ada aktivitas
          </div>
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
            <p class="text-xs text-gray-400 whitespace-nowrap">
              {{ formatDate(log.created_at) }}
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>