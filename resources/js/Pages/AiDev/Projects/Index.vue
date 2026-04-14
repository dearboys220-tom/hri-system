<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">

      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Divisi Pengembangan AI</h1>
          <p class="text-sm text-gray-500 mt-1">Manajemen Proyek AI untuk Perusahaan</p>
        </div>
        <button @click="showCreateModal = true"
          class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
          + Buat Proyek Baru
        </button>
      </div>

      <!-- 統計 -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div v-for="(val, key) in stats" :key="key" class="bg-white rounded-xl shadow-sm p-4 text-center">
          <div class="text-2xl font-bold" :class="key === 'overdue' ? 'text-red-600' : 'text-violet-600'">{{ val }}</div>
          <div class="text-xs text-gray-500 mt-1">{{ statLabel(key) }}</div>
        </div>
      </div>

      <!-- フィルター -->
      <div class="flex gap-3 mb-4">
        <input v-model="searchText" @input="doSearch" placeholder="Cari proyek..."
          class="border rounded-lg px-3 py-2 text-sm w-64" />
        <select v-model="statusFilter" @change="doSearch" class="border rounded-lg px-3 py-2 text-sm">
          <option value="all">Semua Status</option>
          <option value="PROPOSAL">Proposal</option>
          <option value="CONTRACTED">Dikontrak</option>
          <option value="IN_PROGRESS">In Progress</option>
          <option value="TESTING">Testing</option>
          <option value="DELIVERED">Terkirim</option>
          <option value="MAINTENANCE">Pemeliharaan</option>
          <option value="COMPLETED">Selesai</option>
          <option value="CANCELLED">Dibatalkan</option>
        </select>
      </div>

      <!-- テーブル -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">No. Proyek</th>
              <th class="px-4 py-3 text-left">Nama Proyek</th>
              <th class="px-4 py-3 text-left">Tipe</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Klien</th>
              <th class="px-4 py-3 text-left">Lead</th>
              <th class="px-4 py-3 text-left">Tenggat</th>
              <th class="px-4 py-3 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="p in projects.data" :key="p.id"
              :class="p.is_overdue ? 'bg-red-50' : 'hover:bg-gray-50'">
              <td class="px-4 py-3 font-mono text-xs text-violet-600">{{ p.project_no }}</td>
              <td class="px-4 py-3 font-medium max-w-xs truncate">
                {{ p.project_name }}
                <span v-if="p.is_overdue" class="ml-1 text-xs text-red-600">⚠ Terlambat</span>
              </td>
              <td class="px-4 py-3 text-xs">{{ projectTypeLabel(p.project_type) }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusClass(p.project_status)">
                  {{ p.project_status }}
                </span>
              </td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ p.client_name }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ p.lead_name }}</td>
              <td class="px-4 py-3 text-xs" :class="p.is_overdue ? 'text-red-600 font-medium' : 'text-gray-500'">
                {{ p.delivery_due_at ?? '-' }}
              </td>
              <td class="px-4 py-3">
                <button @click="openStatusModal(p)" class="text-xs text-violet-600 hover:underline">Update</button>
              </td>
            </tr>
            <tr v-if="projects.data.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada proyek.</td>
            </tr>
          </tbody>
        </table>
        <div class="px-4 py-3 border-t flex justify-between items-center text-sm text-gray-500">
          <span>Total: {{ projects.total }} proyek</span>
          <div class="flex gap-2">
            <button v-if="projects.prev_page_url" @click="goPage(projects.current_page - 1)"
              class="px-3 py-1 border rounded hover:bg-gray-50">←</button>
            <span class="px-3 py-1">{{ projects.current_page }} / {{ projects.last_page }}</span>
            <button v-if="projects.next_page_url" @click="goPage(projects.current_page + 1)"
              class="px-3 py-1 border rounded hover:bg-gray-50">→</button>
          </div>
        </div>
      </div>

      <!-- 新規作成モーダル -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-xl">
          <h2 class="text-lg font-bold mb-4">Buat Proyek Baru</h2>
          <form @submit.prevent="submitCreate" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Proyek *</label>
              <input v-model="form.project_name" required class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
                <select v-model="form.project_type" required class="w-full border rounded-lg px-3 py-2 text-sm">
                  <option value="CONSULTATION">Konsultasi</option>
                  <option value="DESIGN">Desain</option>
                  <option value="DEVELOPMENT">Pengembangan</option>
                  <option value="DELIVERY">Pengiriman</option>
                  <option value="MAINTENANCE">Pemeliharaan</option>
                  <option value="OTHER">Lainnya</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Kontrak (IDR)</label>
                <input v-model="form.contract_amount" type="number" min="0"
                  class="w-full border rounded-lg px-3 py-2 text-sm" />
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Lead Developer</label>
              <select v-model="form.lead_user_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">-- Pilih Staff --</option>
                <option v-for="u in aiDevUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input v-model="form.started_at" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tenggat Pengiriman</label>
                <input v-model="form.delivery_due_at" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
              </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="showCreateModal = false"
                class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Batal</button>
              <button type="submit"
                class="px-4 py-2 text-sm bg-violet-600 text-white rounded-lg hover:bg-violet-700">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- ステータス更新モーダル -->
      <div v-if="showStatusModal && selectedProject" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
          <h2 class="text-lg font-bold mb-1">Update Status Proyek</h2>
          <p class="text-sm text-gray-500 mb-4">{{ selectedProject.project_no }}</p>
          <form @submit.prevent="submitStatus" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status Proyek</label>
              <select v-model="statusForm.project_status" required class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="PROPOSAL">Proposal</option>
                <option value="CONTRACTED">Dikontrak</option>
                <option value="IN_PROGRESS">In Progress</option>
                <option value="TESTING">Testing</option>
                <option value="DELIVERED">Terkirim</option>
                <option value="MAINTENANCE">Pemeliharaan</option>
                <option value="COMPLETED">Selesai</option>
                <option value="CANCELLED">Dibatalkan</option>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengiriman Aktual</label>
              <input v-model="statusForm.delivered_at" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="showStatusModal = false"
                class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Batal</button>
              <button type="submit"
                class="px-4 py-2 text-sm bg-violet-600 text-white rounded-lg hover:bg-violet-700">Update</button>
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
  projects: Object, stats: Object,
  aiDevUsers: Array, companies: Array,
  search: String, statusFilter: String,
})

const searchText      = ref(props.search ?? '')
const statusFilter    = ref(props.statusFilter ?? 'all')
const showCreateModal = ref(false)
const showStatusModal = ref(false)
const selectedProject = ref(null)

const form = reactive({
  project_name: '', project_type: 'DEVELOPMENT', contract_amount: '',
  client_company_id: '', lead_user_id: '', started_at: '', delivery_due_at: '',
})

const statusForm = reactive({
  project_status: 'PROPOSAL', billing_status: 'UNBILLED', delivered_at: '',
})

const doSearch = () => router.get(route('ai-dev.dashboard'), { search: searchText.value, status: statusFilter.value }, { preserveState: true })
const goPage   = (page) => router.get(route('ai-dev.dashboard'), { search: searchText.value, status: statusFilter.value, page }, { preserveState: true })

const submitCreate = () => {
  router.post(route('ai-dev.projects.store'), form, {
    onSuccess: () => { showCreateModal.value = false }
  })
}

const openStatusModal = (p) => {
  selectedProject.value = p
  statusForm.project_status = p.project_status
  statusForm.billing_status = p.billing_status ?? 'UNBILLED'
  statusForm.delivered_at = ''
  showStatusModal.value = true
}

const submitStatus = () => {
  router.post(route('ai-dev.projects.status', selectedProject.value.id), statusForm, {
    onSuccess: () => { showStatusModal.value = false }
  })
}

const statLabel = (k) => ({ proposal: 'Proposal', in_progress: 'In Progress', testing: 'Testing', delivered: 'Terkirim', overdue: 'Terlambat' }[k] ?? k)
const statusClass = (s) => ({ PROPOSAL: 'bg-gray-100 text-gray-600', CONTRACTED: 'bg-blue-100 text-blue-700', IN_PROGRESS: 'bg-indigo-100 text-indigo-700', TESTING: 'bg-yellow-100 text-yellow-700', DELIVERED: 'bg-green-100 text-green-700', MAINTENANCE: 'bg-teal-100 text-teal-700', COMPLETED: 'bg-emerald-100 text-emerald-700', CANCELLED: 'bg-red-100 text-red-700' }[s] ?? '')
const projectTypeLabel = (t) => ({ CONSULTATION: 'Konsultasi', DESIGN: 'Desain', DEVELOPMENT: 'Pengembangan', DELIVERY: 'Pengiriman', MAINTENANCE: 'Pemeliharaan', OTHER: 'Lainnya' }[t] ?? t)
</script>