<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  calculations: Object,
  staffList: Array,
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})

// 生成フォーム
const form = ref({
  staff_user_id:     '',
  calculation_month: '',
  base_salary:       '',
})
const generating = ref(false)

function generateSalary() {
  generating.value = true
  router.post(route('manager.salary.generate'), form.value, {
    onFinish: () => (generating.value = false),
  })
}

// 承認モーダル
const approveModal  = ref(false)
const selectedCalc  = ref(null)
const approveForm   = ref({
  performance_adjustment: 0,
  deductions:   0,
  overtime_pay: 0,
  allowances:   0,
})

function openApprove(calc) {
  selectedCalc.value = calc
  approveForm.value = {
    performance_adjustment: calc.performance_adjustment ?? 0,
    deductions:   calc.deductions   ?? 0,
    overtime_pay: calc.overtime_pay ?? 0,
    allowances:   calc.allowances   ?? 0,
  }
  approveModal.value = true
}

function submitApprove() {
  router.post(
    route('manager.salary.approve', selectedCalc.value.id),
    approveForm.value,
    { onSuccess: () => (approveModal.value = false) }
  )
}

// プレビュー計算（モーダル内リアルタイム）
const previewGross = computed(() => {
  if (!selectedCalc.value) return 0
  return (
    Number(selectedCalc.value.base_salary) +
    Number(approveForm.value.performance_adjustment) +
    Number(approveForm.value.overtime_pay) +
    Number(approveForm.value.allowances)
  )
})
const previewNet = computed(() => previewGross.value - Number(approveForm.value.deductions))

// ステータスカラー
const statusColor = {
  DRAFT:            'bg-gray-100 text-gray-600',
  PENDING_APPROVAL: 'bg-yellow-100 text-yellow-700',
  APPROVED:         'bg-green-100 text-green-700',
  REJECTED:         'bg-red-100 text-red-700',
}
const statusLabel = {
  DRAFT:            'Draft',
  PENDING_APPROVAL: 'Menunggu Persetujuan',
  APPROVED:         'Disetujui',
  REJECTED:         'Ditolak',
}

// バンドカラー
const bandColor = {
  GOOD:    'text-green-600',
  FAIR:    'text-yellow-600',
  WARNING: 'text-red-600',
}
const bandLabel = { GOOD: 'Baik', FAIR: 'Cukup', WARNING: 'Peringatan' }

// ロール表示
const roleLabel = {
  investigator_user: 'Investigasi',
  admin_user:        'Admin',
  em_staff:          'Staf',
  strategy_user:     'Strategi',
  ai_dev_user:       'AI Dev',
  marketing_user:    'Marketing',
}

// 金額フォーマット
function rupiah(val) {
  return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">

    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
      <a :href="route('manager.dashboard')" class="text-sm text-blue-600 hover:underline">
        ← Dashboard
      </a>
      <h1 class="text-2xl font-bold text-gray-800">💰 Manajemen Gaji</h1>
    </div>

    <!-- Flash messages -->
    <div v-if="flash.success"
      class="mb-4 rounded bg-green-100 border border-green-300 px-4 py-3 text-green-800">
      {{ flash.success }}
    </div>
    <div v-if="flash.error"
      class="mb-4 rounded bg-red-100 border border-red-300 px-4 py-3 text-red-800">
      {{ flash.error }}
    </div>

    <!-- 生成フォーム -->
    <div class="mb-6 rounded-lg bg-white shadow p-6">
      <h2 class="text-lg font-semibold text-gray-700 mb-4">
        🤖 Buat Draft Perhitungan Gaji
      </h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <!-- スタッフ選択 -->
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Staf</label>
          <select v-model="form.staff_user_id"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
            <option value="">-- Pilih Staf --</option>
            <option v-for="s in staffList" :key="s.id" :value="s.id">
              {{ s.name }} ({{ roleLabel[s.role_type] ?? s.role_type }})
            </option>
          </select>
        </div>

        <!-- 計算月 -->
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">
            Bulan Perhitungan
            <span class="text-gray-400 font-normal">(format: 2026-04)</span>
          </label>
          <input type="month" v-model="form.calculation_month"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
        </div>

        <!-- 基本給 -->
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">
            Gaji Pokok (IDR)
          </label>
          <input type="number" v-model="form.base_salary" min="0" step="1000"
            placeholder="contoh: 5000000"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
        </div>
      </div>

      <div class="mt-4">
        <button @click="generateSalary"
          :disabled="generating || !form.staff_user_id || !form.calculation_month || !form.base_salary"
          class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 text-sm font-medium">
          {{ generating ? '⏳ Sedang menghitung...' : '🤖 Buat Draft Gaji' }}
        </button>
        <p class="mt-2 text-xs text-gray-400">
          ※ Potongan absen dan komponen gaji dihitung otomatis oleh AI.
          Penyesuaian kinerja dan konfirmasi akhir dilakukan oleh manajer.
        </p>
      </div>
    </div>

    <!-- 給与計算一覧 -->
    <div class="rounded-lg bg-white shadow overflow-hidden">
      <div class="px-6 py-4 border-b bg-gray-50">
        <h2 class="text-base font-semibold text-gray-700">Daftar Perhitungan Gaji</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-xs text-gray-500 uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Staf</th>
              <th class="px-4 py-3 text-left">Bulan</th>
              <th class="px-4 py-3 text-right">Gaji Pokok</th>
              <th class="px-4 py-3 text-center">Hadir</th>
              <th class="px-4 py-3 text-center">Absen</th>
              <th class="px-4 py-3 text-center">Kinerja</th>
              <th class="px-4 py-3 text-right">Gaji Bersih</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-left">Catatan AI</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="calculations.data.length === 0">
              <td colspan="10" class="px-4 py-8 text-center text-gray-400">
                Belum ada data perhitungan gaji
              </td>
            </tr>
            <tr v-for="calc in calculations.data" :key="calc.id" class="hover:bg-gray-50">

              <!-- スタッフ -->
              <td class="px-4 py-3 font-medium text-gray-800">
                {{ calc.staff?.name ?? '-' }}
                <div class="text-xs text-gray-400">
                  {{ roleLabel[calc.staff?.role_type] ?? calc.staff?.role_type }}
                </div>
              </td>

              <!-- 計算月 -->
              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                {{ calc.calculation_month }}
              </td>

              <!-- 基本給 -->
              <td class="px-4 py-3 text-right text-gray-700 whitespace-nowrap">
                {{ rupiah(calc.base_salary) }}
              </td>

              <!-- 出勤日数 -->
              <td class="px-4 py-3 text-center text-gray-600">
                {{ calc.attendance_days ?? '-' }} hari
              </td>

              <!-- 欠勤日数 -->
              <td class="px-4 py-3 text-center text-gray-600">
                {{ calc.absent_days ?? 0 }} hari
              </td>

              <!-- 査定バンド -->
              <td class="px-4 py-3 text-center">
                <span v-if="calc.performance_band"
                  :class="['text-xs font-bold', bandColor[calc.performance_band]]">
                  {{ bandLabel[calc.performance_band] ?? calc.performance_band }}
                </span>
                <span v-else class="text-xs text-gray-400">-</span>
              </td>

              <!-- 手取り -->
              <td class="px-4 py-3 text-right font-semibold text-gray-800 whitespace-nowrap">
                {{ rupiah(calc.net_salary) }}
              </td>

              <!-- ステータス -->
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-1 rounded-full text-xs font-medium', statusColor[calc.calculation_status]]">
                  {{ statusLabel[calc.calculation_status] ?? calc.calculation_status }}
                </span>
              </td>

              <!-- AI注記 -->
              <td class="px-4 py-3 text-gray-500 max-w-xs">
                <p class="line-clamp-2 text-xs">{{ calc.ai_calculation_notes ?? '-' }}</p>
              </td>

              <!-- 操作 -->
              <td class="px-4 py-3 text-center">
                <button v-if="calc.calculation_status === 'DRAFT'"
                  @click="openApprove(calc)"
                  class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">
                  Setujui
                </button>
                <span v-else-if="calc.calculation_status === 'APPROVED'"
                  class="text-xs text-gray-400">✅ Disetujui</span>
                <span v-else class="text-xs text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 承認モーダル -->
    <div v-if="approveModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-1">Persetujuan Gaji</h3>
        <p class="text-xs text-gray-400 mb-4">
          Sesuaikan komponen gaji jika diperlukan, lalu klik Setujui.
        </p>

        <!-- スタッフ情報 -->
        <div v-if="selectedCalc" class="mb-4 p-3 bg-gray-50 rounded text-sm text-gray-600 space-y-1">
          <p><strong>Staf:</strong> {{ selectedCalc.staff?.name }}</p>
          <p><strong>Bulan:</strong> {{ selectedCalc.calculation_month }}</p>
          <p><strong>Gaji Pokok:</strong> {{ rupiah(selectedCalc.base_salary) }}</p>
          <p v-if="selectedCalc.performance_band">
            <strong>Kinerja:</strong>
            <span :class="['font-bold ml-1', bandColor[selectedCalc.performance_band]]">
              {{ bandLabel[selectedCalc.performance_band] }}
            </span>
          </p>
          <p v-if="selectedCalc.ai_calculation_notes" class="text-xs text-orange-600 mt-1">
            ⚠️ {{ selectedCalc.ai_calculation_notes }}
          </p>
        </div>

        <!-- 調整項目 -->
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
              Penyesuaian Kinerja (IDR)
              <span class="text-gray-400">（+ / -）</span>
            </label>
            <input type="number" v-model="approveForm.performance_adjustment" step="1000"
              class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Potongan (IDR)</label>
            <input type="number" v-model="approveForm.deductions" min="0" step="1000"
              class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Lembur (IDR)</label>
            <input type="number" v-model="approveForm.overtime_pay" min="0" step="1000"
              class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tunjangan (IDR)</label>
            <input type="number" v-model="approveForm.allowances" min="0" step="1000"
              class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
          </div>
        </div>

        <!-- リアルタイムプレビュー -->
        <div class="mb-4 p-3 bg-blue-50 rounded text-sm space-y-1">
          <div class="flex justify-between text-gray-600">
            <span>Total Bruto:</span>
            <span class="font-medium">{{ rupiah(previewGross) }}</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Potongan:</span>
            <span class="font-medium text-red-600">- {{ rupiah(approveForm.deductions) }}</span>
          </div>
          <div class="flex justify-between text-gray-800 font-bold border-t pt-1 mt-1">
            <span>Gaji Bersih:</span>
            <span class="text-green-700">{{ rupiah(previewNet) }}</span>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="approveModal = false"
            class="px-4 py-2 border rounded text-sm text-gray-600 hover:bg-gray-50">
            Batal
          </button>
          <button @click="submitApprove"
            class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">
            Setujui & Konfirmasi
          </button>
        </div>
      </div>
    </div>

  </div>
</template>