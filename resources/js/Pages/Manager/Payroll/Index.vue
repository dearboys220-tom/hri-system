<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  payrolls:            Object,
  pendingCalculations: Array,
})

const page  = usePage()
const flash = computed(() => page.props.flash ?? {})

// ── 支払い記録作成モーダル ──
const createModal = ref(false)
const createForm  = ref({
  salary_calculation_id: '',
  bank_account_name:     '',
  bank_name:             '',
  bank_account_no:       '',
  payment_method:        'BANK_TRANSFER',
})

function openCreate() {
  createForm.value = {
    salary_calculation_id: '',
    bank_account_name:     '',
    bank_name:             '',
    bank_account_no:       '',
    payment_method:        'BANK_TRANSFER',
  }
  createModal.value = true
}

function submitCreate() {
  router.post(route('manager.payroll.store'), createForm.value, {
    onSuccess: () => (createModal.value = false),
  })
}

// 選択中の給与計算情報（プレビュー表示用）
const selectedCalc = computed(() =>
  props.pendingCalculations.find(c => c.id == createForm.value.salary_calculation_id) ?? null
)

// ── 送金処理モーダル ──
const processModal  = ref(false)
const selectedPayroll = ref(null)
const processForm   = ref({ payment_reference_no: '' })

function openProcess(p) {
  selectedPayroll.value = p
  processForm.value = { payment_reference_no: '' }
  processModal.value = true
}

function submitProcess() {
  router.post(
    route('manager.payroll.processed', selectedPayroll.value.id),
    processForm.value,
    { onSuccess: () => (processModal.value = false) }
  )
}

// ── ステータス変更（確認済み・失敗） ──
function markConfirmed(p) {
  if (!confirm('Konfirmasi pembayaran telah diterima?')) return
  router.post(route('manager.payroll.confirmed', p.id))
}

function markFailed(p) {
  if (!confirm('Tandai pembayaran ini sebagai gagal?')) return
  router.post(route('manager.payroll.failed', p.id))
}

// ── 表示用ヘルパー ──
const statusColor = {
  SCHEDULED: 'bg-gray-100 text-gray-600',
  PROCESSED: 'bg-blue-100 text-blue-700',
  CONFIRMED: 'bg-green-100 text-green-700',
  FAILED:    'bg-red-100 text-red-700',
}
const statusLabel = {
  SCHEDULED: 'Dijadwalkan',
  PROCESSED: 'Diproses',
  CONFIRMED: 'Dikonfirmasi',
  FAILED:    'Gagal',
}
const methodLabel = {
  BANK_TRANSFER: 'Transfer Bank',
  CASH:          'Tunai',
  QRIS:          'QRIS',
  OTHER:         'Lainnya',
}
const roleLabel = {
  investigator_user: 'Investigasi',
  admin_user:        'Admin',
  em_staff:          'Staf',
  strategy_user:     'Strategi',
  ai_dev_user:       'AI Dev',
  marketing_user:    'Marketing',
}

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
      <h1 class="text-2xl font-bold text-gray-800">🏦 Catatan Pembayaran Gaji</h1>
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

    <!-- 未払い件数バナー -->
    <div v-if="pendingCalculations.length > 0"
      class="mb-6 p-4 bg-yellow-50 border border-yellow-300 rounded-lg flex items-center justify-between">
      <div>
        <p class="text-sm font-semibold text-yellow-800">
          ⚠️ Ada {{ pendingCalculations.length }} perhitungan gaji yang sudah disetujui namun belum dibuat catatan pembayaran.
        </p>
        <p class="text-xs text-yellow-600 mt-1">Klik tombol di bawah untuk membuat catatan pembayaran.</p>
      </div>
      <button @click="openCreate"
        class="ml-4 px-4 py-2 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600 whitespace-nowrap">
        + Buat Catatan Pembayaran
      </button>
    </div>

    <div v-else class="mb-6 flex justify-end">
      <button @click="openCreate"
        :disabled="pendingCalculations.length === 0"
        class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-40">
        + Buat Catatan Pembayaran
      </button>
    </div>

    <!-- 支払い記録一覧 -->
    <div class="rounded-lg bg-white shadow overflow-hidden">
      <div class="px-6 py-4 border-b bg-gray-50">
        <h2 class="text-base font-semibold text-gray-700">Daftar Catatan Pembayaran</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-xs text-gray-500 uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Staf</th>
              <th class="px-4 py-3 text-left">Bulan</th>
              <th class="px-4 py-3 text-right">Jumlah</th>
              <th class="px-4 py-3 text-left">Metode</th>
              <th class="px-4 py-3 text-left">Bank / Akun</th>
              <th class="px-4 py-3 text-left">No. Referensi</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-left">Diproses Oleh</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="payrolls.data.length === 0">
              <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                Belum ada catatan pembayaran
              </td>
            </tr>
            <tr v-for="p in payrolls.data" :key="p.id" class="hover:bg-gray-50">

              <!-- スタッフ -->
              <td class="px-4 py-3 font-medium text-gray-800">
                {{ p.staff?.name ?? '-' }}
                <div class="text-xs text-gray-400">
                  {{ roleLabel[p.staff?.role_type] ?? p.staff?.role_type }}
                </div>
              </td>

              <!-- 支払い月 -->
              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                {{ p.payment_month }}
              </td>

              <!-- 金額 -->
              <td class="px-4 py-3 text-right font-semibold text-gray-800 whitespace-nowrap">
                {{ rupiah(p.paid_amount) }}
              </td>

              <!-- 支払い方法 -->
              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                {{ methodLabel[p.payment_method] ?? p.payment_method }}
              </td>

              <!-- 銀行・口座 -->
              <td class="px-4 py-3 text-gray-600 text-xs">
                <div>{{ p.bank_name }}</div>
                <div class="text-gray-400">{{ p.bank_account_name }}</div>
                <div class="font-mono">{{ p.bank_account_no }}</div>
              </td>

              <!-- 参照番号 -->
              <td class="px-4 py-3 text-gray-500 font-mono text-xs">
                {{ p.payment_reference_no ?? '-' }}
              </td>

              <!-- ステータス -->
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-1 rounded-full text-xs font-medium', statusColor[p.payment_status]]">
                  {{ statusLabel[p.payment_status] ?? p.payment_status }}
                </span>
              </td>

              <!-- 処理者 -->
              <td class="px-4 py-3 text-gray-500 text-xs">
                {{ p.processed_by?.name ?? '-' }}
              </td>

              <!-- 操作 -->
              <td class="px-4 py-3 text-center">
                <div class="flex flex-col gap-1 items-center">
                  <!-- SCHEDULED → 送金処理 -->
                  <button v-if="p.payment_status === 'SCHEDULED'"
                    @click="openProcess(p)"
                    class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 whitespace-nowrap">
                    Proses
                  </button>
                  <!-- PROCESSED → 受取確認 / 失敗 -->
                  <template v-if="p.payment_status === 'PROCESSED'">
                    <button @click="markConfirmed(p)"
                      class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700 whitespace-nowrap">
                      Konfirmasi
                    </button>
                    <button @click="markFailed(p)"
                      class="px-3 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 whitespace-nowrap">
                      Gagal
                    </button>
                  </template>
                  <!-- CONFIRMED / FAILED -->
                  <span v-if="p.payment_status === 'CONFIRMED'" class="text-xs text-gray-400">✅ Selesai</span>
                  <span v-if="p.payment_status === 'FAILED'" class="text-xs text-red-400">❌ Gagal</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 支払い記録作成モーダル -->
    <div v-if="createModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Buat Catatan Pembayaran</h3>

        <!-- 給与計算選択 -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Data Gaji</label>
          <select v-model="createForm.salary_calculation_id"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
            <option value="">-- Pilih --</option>
            <option v-for="c in pendingCalculations" :key="c.id" :value="c.id">
              {{ c.staff?.name }} — {{ c.calculation_month }} — {{ rupiah(c.net_salary) }}
            </option>
          </select>
        </div>

        <!-- 選択中の給与情報プレビュー -->
        <div v-if="selectedCalc"
          class="mb-4 p-3 bg-blue-50 rounded text-xs text-gray-600 space-y-1">
          <p><strong>Staf:</strong> {{ selectedCalc.staff?.name }}</p>
          <p><strong>Bulan:</strong> {{ selectedCalc.calculation_month }}</p>
          <p><strong>Gaji Bersih:</strong>
            <span class="font-bold text-green-700">{{ rupiah(selectedCalc.net_salary) }}</span>
          </p>
        </div>

        <!-- 銀行情報 -->
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nama Penerima</label>
            <input type="text" v-model="createForm.bank_account_name"
              placeholder="Nama sesuai rekening"
              class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bank</label>
            <input type="text" v-model="createForm.bank_name"
              placeholder="BCA / BRI / Mandiri ..."
              class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Rekening</label>
            <input type="text" v-model="createForm.bank_account_no"
              placeholder="0123456789"
              class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Metode Pembayaran</label>
            <select v-model="createForm.payment_method"
              class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
              <option value="BANK_TRANSFER">Transfer Bank</option>
              <option value="CASH">Tunai</option>
              <option value="QRIS">QRIS</option>
              <option value="OTHER">Lainnya</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="createModal = false"
            class="px-4 py-2 border rounded text-sm text-gray-600 hover:bg-gray-50">
            Batal
          </button>
          <button @click="submitCreate"
            :disabled="!createForm.salary_calculation_id || !createForm.bank_account_name || !createForm.bank_account_no"
            class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50">
            Buat Catatan
          </button>
        </div>
      </div>
    </div>

    <!-- 送金処理モーダル -->
    <div v-if="processModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Proses Pembayaran</h3>

        <div v-if="selectedPayroll" class="mb-4 p-3 bg-gray-50 rounded text-sm text-gray-600 space-y-1">
          <p><strong>Staf:</strong> {{ selectedPayroll.staff?.name }}</p>
          <p><strong>Bulan:</strong> {{ selectedPayroll.payment_month }}</p>
          <p><strong>Jumlah:</strong>
            <span class="font-bold text-green-700">{{ rupiah(selectedPayroll.paid_amount) }}</span>
          </p>
          <p><strong>Metode:</strong> {{ methodLabel[selectedPayroll.payment_method] }}</p>
          <p><strong>Bank:</strong> {{ selectedPayroll.bank_name }} — {{ selectedPayroll.bank_account_no }}</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nomor Referensi Transfer
            <span class="text-gray-400 font-normal">(opsional)</span>
          </label>
          <input type="text" v-model="processForm.payment_reference_no"
            placeholder="Nomor bukti transfer / referensi"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
        </div>

        <div class="flex justify-end gap-3">
          <button @click="processModal = false"
            class="px-4 py-2 border rounded text-sm text-gray-600 hover:bg-gray-50">
            Batal
          </button>
          <button @click="submitProcess"
            class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
            Tandai Sudah Diproses
          </button>
        </div>
      </div>
    </div>

  </div>
</template>