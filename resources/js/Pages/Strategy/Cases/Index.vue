<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">

      <!-- ヘッダー -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Divisi Strategi Manajemen</h1>
          <p class="text-sm text-gray-500 mt-1">Manajemen Kasus Hukum & Ketenagakerjaan</p>
        </div>
        <button @click="showCreateModal = true"
          class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
          + Buat Kasus Baru
        </button>
      </div>

      <!-- 統計カード -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div v-for="(val, key) in stats" :key="key"
          class="bg-white rounded-xl shadow-sm p-4 text-center">
          <div class="text-2xl font-bold" :class="key === 'high_risk' ? 'text-red-600' : 'text-indigo-600'">
            {{ val }}
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ statLabel(key) }}</div>
        </div>
      </div>

      <!-- フィルター -->
      <div class="flex gap-3 mb-4 flex-wrap">
        <input v-model="searchText" @input="doSearch"
          placeholder="Cari kasus..."
          class="border rounded-lg px-3 py-2 text-sm w-64" />
        <select v-model="statusFilter" @change="doSearch"
          class="border rounded-lg px-3 py-2 text-sm">
          <option value="all">Semua Status</option>
          <option value="OPEN">Open</option>
          <option value="IN_PROGRESS">In Progress</option>
          <option value="PENDING_LAWYER">Menunggu Pengacara</option>
          <option value="RESOLVED">Selesai</option>
          <option value="CLOSED">Ditutup</option>
          <option value="ESCALATED">Eskalasi</option>
        </select>
      </div>

      <!-- テーブル -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">No. Kasus</th>
              <th class="px-4 py-3 text-left">Judul</th>
              <th class="px-4 py-3 text-left">Tipe</th>
              <th class="px-4 py-3 text-left">Risiko</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Klien</th>
              <th class="px-4 py-3 text-left">PIC</th>
              <th class="px-4 py-3 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="c in cases.data" :key="c.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs text-indigo-600">{{ c.case_no }}</td>
              <td class="px-4 py-3 font-medium max-w-xs truncate">{{ c.case_title }}</td>
              <td class="px-4 py-3 text-xs">{{ caseTypeLabel(c.case_type) }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium"
                  :class="riskClass(c.risk_level)">{{ c.risk_level }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium"
                  :class="statusClass(c.case_status)">{{ c.case_status }}</span>
              </td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ c.client_name }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ c.assigned_name }}</td>
              <td class="px-4 py-3">
                <button @click="openStatusModal(c)"
                  class="text-xs text-indigo-600 hover:underline mr-2">Update</button>
                <button @click="generateAiSummary(c)"
                  class="text-xs text-emerald-600 hover:underline">AI Ringkasan</button>
              </td>
            </tr>
            <tr v-if="cases.data.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada kasus.</td>
            </tr>
          </tbody>
        </table>

        <!-- ページネーション -->
        <div class="px-4 py-3 border-t flex justify-between items-center text-sm text-gray-500">
          <span>Total: {{ cases.total }} kasus</span>
          <div class="flex gap-2">
            <button v-if="cases.prev_page_url" @click="goPage(cases.current_page - 1)"
              class="px-3 py-1 border rounded hover:bg-gray-50">←</button>
            <span class="px-3 py-1">{{ cases.current_page }} / {{ cases.last_page }}</span>
            <button v-if="cases.next_page_url" @click="goPage(cases.current_page + 1)"
              class="px-3 py-1 border rounded hover:bg-gray-50">→</button>
          </div>
        </div>
      </div>

      <!-- 新規作成モーダル -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-xl">
          <h2 class="text-lg font-bold mb-4">Buat Kasus Baru</h2>
          <form @submit.prevent="submitCreate" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Judul Kasus *</label>
              <input v-model="form.case_title" required
                class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kasus *</label>
                <select v-model="form.case_type" required class="w-full border rounded-lg px-3 py-2 text-sm">
                  <option value="LABOR_LAW">Hukum Ketenagakerjaan</option>
                  <option value="CONTRACT">Kontrak</option>
                  <option value="COMPLIANCE">Kepatuhan</option>
                  <option value="BANKRUPTCY">Kepailitan</option>
                  <option value="DISPUTE">Sengketa</option>
                  <option value="OTHER">Lainnya</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Level Risiko *</label>
                <select v-model="form.risk_level" required class="w-full border rounded-lg px-3 py-2 text-sm">
                  <option value="LOW">Rendah</option>
                  <option value="MEDIUM">Sedang</option>
                  <option value="HIGH">Tinggi</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Klien</label>
              <select v-model="form.client_company_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">-- Pilih Perusahaan --</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.company_name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
              <select v-model="form.assigned_user_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">-- Pilih Staff --</option>
                <option v-for="u in strategyUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
              <textarea v-model="form.case_description" rows="3"
                class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div>
              <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" v-model="form.requires_registered_lawyer" />
                Memerlukan pengacara terdaftar
              </label>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="showCreateModal = false"
                class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Batal</button>
              <button type="submit"
                class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- ステータス更新モーダル -->
      <div v-if="showStatusModal && selectedCase" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
          <h2 class="text-lg font-bold mb-1">Update Status</h2>
          <p class="text-sm text-gray-500 mb-4">{{ selectedCase.case_no }}</p>
          <form @submit.prevent="submitStatus" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status Kasus</label>
              <select v-model="statusForm.case_status" required class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="OPEN">Open</option>
                <option value="IN_PROGRESS">In Progress</option>
                <option value="PENDING_LAWYER">Menunggu Pengacara</option>
                <option value="RESOLVED">Selesai</option>
                <option value="CLOSED">Ditutup</option>
                <option value="ESCALATED">Eskalasi</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status Penagihan</label>
              <select v-model="statusForm.billing_status" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="UNBILLED">Belum Ditagih</option>
                <option value="BILLED">Sudah Ditagih</option>
                <option value="PAID">Lunas</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ringkasan Penyelesaian</label>
              <textarea v-model="statusForm.resolution_summary" rows="3"
                class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="showStatusModal = false"
                class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Batal</button>
              <button type="submit"
                class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Update</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  cases: Object,
  stats: Object,
  strategyUsers: Array,
  companies: Array,
  search: String,
  statusFilter: String,
})

const searchText   = ref(props.search ?? '')
const statusFilter = ref(props.statusFilter ?? 'all')
const showCreateModal = ref(false)
const showStatusModal = ref(false)
const selectedCase    = ref(null)

const form = reactive({
  case_title: '', case_type: 'OTHER', risk_level: 'LOW',
  case_description: '', client_company_id: '', assigned_user_id: '',
  requires_registered_lawyer: false,
})

const statusForm = reactive({
  case_status: 'OPEN', billing_status: 'UNBILLED', resolution_summary: '',
})

const doSearch = () => {
  router.get(route('strategy.dashboard'), { search: searchText.value, status: statusFilter.value }, { preserveState: true })
}

const goPage = (page) => {
  router.get(route('strategy.dashboard'), { search: searchText.value, status: statusFilter.value, page }, { preserveState: true })
}

const submitCreate = () => {
  router.post(route('strategy.cases.store'), form, {
    onSuccess: () => { showCreateModal.value = false; Object.assign(form, { case_title: '', case_type: 'OTHER', risk_level: 'LOW', case_description: '', client_company_id: '', assigned_user_id: '', requires_registered_lawyer: false }) }
  })
}

const openStatusModal = (c) => {
  selectedCase.value = c
  statusForm.case_status = c.case_status
  statusForm.billing_status = c.billing_status ?? 'UNBILLED'
  statusForm.resolution_summary = ''
  showStatusModal.value = true
}

const submitStatus = () => {
  router.post(route('strategy.cases.status', selectedCase.value.id), statusForm, {
    onSuccess: () => { showStatusModal.value = false }
  })
}

const generateAiSummary = (c) => {
  if (!confirm(`AI ringkasan risiko untuk kasus ${c.case_no}?`)) return
  router.post(route('strategy.cases.ai-summary', c.id), {})
}

const statLabel = (key) => ({ open: 'Open', in_progress: 'In Progress', pending_lawyer: 'Tunggu Pengacara', resolved: 'Selesai', high_risk: 'Risiko Tinggi' }[key] ?? key)
const riskClass = (r) => ({ HIGH: 'bg-red-100 text-red-700', MEDIUM: 'bg-yellow-100 text-yellow-700', LOW: 'bg-green-100 text-green-700' }[r] ?? '')
const statusClass = (s) => ({ OPEN: 'bg-blue-100 text-blue-700', IN_PROGRESS: 'bg-indigo-100 text-indigo-700', PENDING_LAWYER: 'bg-orange-100 text-orange-700', RESOLVED: 'bg-green-100 text-green-700', CLOSED: 'bg-gray-100 text-gray-600', ESCALATED: 'bg-red-100 text-red-700' }[s] ?? '')
const caseTypeLabel = (t) => ({ LABOR_LAW: 'Ketenagakerjaan', CONTRACT: 'Kontrak', COMPLIANCE: 'Kepatuhan', BANKRUPTCY: 'Kepailitan', DISPUTE: 'Sengketa', OTHER: 'Lainnya' }[t] ?? t)
</script>