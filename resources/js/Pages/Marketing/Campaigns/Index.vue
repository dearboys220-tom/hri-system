<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">

      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Divisi Marketing</h1>
          <p class="text-sm text-gray-500 mt-1">Manajemen Kampanye & Riset Pasar</p>
        </div>
        <button @click="showCreateModal = true"
          class="bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-rose-700">
          + Buat Kampanye Baru
        </button>
      </div>

      <!-- 統計 -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div v-for="(val, key) in stats" :key="key" class="bg-white rounded-xl shadow-sm p-4 text-center">
          <div class="text-2xl font-bold text-rose-600">{{ val }}</div>
          <div class="text-xs text-gray-500 mt-1">{{ statLabel(key) }}</div>
        </div>
      </div>

      <!-- フィルター -->
      <div class="flex gap-3 mb-4">
        <input v-model="searchText" @input="doSearch" placeholder="Cari kampanye..."
          class="border rounded-lg px-3 py-2 text-sm w-64" />
        <select v-model="statusFilter" @change="doSearch" class="border rounded-lg px-3 py-2 text-sm">
          <option value="all">Semua Status</option>
          <option value="PLANNING">Perencanaan</option>
          <option value="ACTIVE">Aktif</option>
          <option value="ON_HOLD">Ditangguhkan</option>
          <option value="COMPLETED">Selesai</option>
          <option value="CANCELLED">Dibatalkan</option>
        </select>
      </div>

      <!-- テーブル -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">No. Kampanye</th>
              <th class="px-4 py-3 text-left">Nama</th>
              <th class="px-4 py-3 text-left">Tipe</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Budget (IDR)</th>
              <th class="px-4 py-3 text-left">PIC</th>
              <th class="px-4 py-3 text-left">Periode</th>
              <th class="px-4 py-3 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="c in campaigns.data" :key="c.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs text-rose-600">{{ c.campaign_no }}</td>
              <td class="px-4 py-3 font-medium max-w-xs truncate">{{ c.campaign_name }}</td>
              <td class="px-4 py-3 text-xs">{{ campaignTypeLabel(c.campaign_type) }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusClass(c.campaign_status)">
                  {{ c.campaign_status }}
                </span>
              </td>
              <td class="px-4 py-3 text-xs text-gray-600">{{ c.budget ? Number(c.budget).toLocaleString('id-ID') : '-' }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ c.assigned_name }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ c.started_at ?? '-' }} ~ {{ c.ended_at ?? '-' }}</td>
              <td class="px-4 py-3">
                <button @click="openStatusModal(c)" class="text-xs text-rose-600 hover:underline">Update</button>
              </td>
            </tr>
            <tr v-if="campaigns.data.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada kampanye.</td>
            </tr>
          </tbody>
        </table>
        <div class="px-4 py-3 border-t flex justify-between items-center text-sm text-gray-500">
          <span>Total: {{ campaigns.total }} kampanye</span>
          <div class="flex gap-2">
            <button v-if="campaigns.prev_page_url" @click="goPage(campaigns.current_page - 1)"
              class="px-3 py-1 border rounded hover:bg-gray-50">←</button>
            <span class="px-3 py-1">{{ campaigns.current_page }} / {{ campaigns.last_page }}</span>
            <button v-if="campaigns.next_page_url" @click="goPage(campaigns.current_page + 1)"
              class="px-3 py-1 border rounded hover:bg-gray-50">→</button>
          </div>
        </div>
      </div>

      <!-- 新規作成モーダル -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-xl">
          <h2 class="text-lg font-bold mb-4">Buat Kampanye Baru</h2>
          <form @submit.prevent="submitCreate" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kampanye *</label>
              <input v-model="form.campaign_name" required class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
                <select v-model="form.campaign_type" required class="w-full border rounded-lg px-3 py-2 text-sm">
                  <option value="MARKET_RESEARCH">Riset Pasar</option>
                  <option value="ADVERTISING">Iklan</option>
                  <option value="SALES_MANAGEMENT">Manajemen Penjualan</option>
                  <option value="EVENT">Event</option>
                  <option value="OTHER">Lainnya</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Budget (IDR)</label>
                <input v-model="form.budget" type="number" min="0" class="w-full border rounded-lg px-3 py-2 text-sm" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
              <select v-model="form.assigned_user_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">-- Pilih Staff --</option>
                <option v-for="u in marketingUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Target / Tujuan</label>
              <textarea v-model="form.target_description" rows="2"
                class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input v-model="form.started_at" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <input v-model="form.ended_at" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
              </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="showCreateModal = false"
                class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Batal</button>
              <button type="submit"
                class="px-4 py-2 text-sm bg-rose-600 text-white rounded-lg hover:bg-rose-700">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- ステータス更新モーダル -->
      <div v-if="showStatusModal && selectedCampaign" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
          <h2 class="text-lg font-bold mb-1">Update Status Kampanye</h2>
          <p class="text-sm text-gray-500 mb-4">{{ selectedCampaign.campaign_no }}</p>
          <form @submit.prevent="submitStatus" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="statusForm.campaign_status" required class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="PLANNING">Perencanaan</option>
                <option value="ACTIVE">Aktif</option>
                <option value="ON_HOLD">Ditangguhkan</option>
                <option value="COMPLETED">Selesai</option>
                <option value="CANCELLED">Dibatalkan</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Laporan Hasil</label>
              <textarea v-model="statusForm.result_report" rows="3"
                class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
              <input v-model="statusForm.ended_at" type="date" class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="showStatusModal = false"
                class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Batal</button>
              <button type="submit"
                class="px-4 py-2 text-sm bg-rose-600 text-white rounded-lg hover:bg-rose-700">Update</button>
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
  campaigns: Object, stats: Object,
  marketingUsers: Array, search: String, statusFilter: String,
})

const searchText       = ref(props.search ?? '')
const statusFilter     = ref(props.statusFilter ?? 'all')
const showCreateModal  = ref(false)
const showStatusModal  = ref(false)
const selectedCampaign = ref(null)

const form = reactive({
  campaign_name: '', campaign_type: 'MARKET_RESEARCH',
  target_description: '', assigned_user_id: '',
  budget: '', started_at: '', ended_at: '',
})

const statusForm = reactive({
  campaign_status: 'PLANNING', result_report: '', ended_at: '',
})

const doSearch = () => router.get(route('marketing.dashboard'), { search: searchText.value, status: statusFilter.value }, { preserveState: true })
const goPage   = (page) => router.get(route('marketing.dashboard'), { search: searchText.value, status: statusFilter.value, page }, { preserveState: true })

const submitCreate = () => {
  router.post(route('marketing.campaigns.store'), form, {
    onSuccess: () => { showCreateModal.value = false }
  })
}

const openStatusModal = (c) => {
  selectedCampaign.value = c
  statusForm.campaign_status = c.campaign_status
  statusForm.result_report = ''
  statusForm.ended_at = c.ended_at ?? ''
  showStatusModal.value = true
}

const submitStatus = () => {
  router.post(route('marketing.campaigns.status', selectedCampaign.value.id), statusForm, {
    onSuccess: () => { showStatusModal.value = false }
  })
}

const statLabel = (k) => ({ planning: 'Perencanaan', active: 'Aktif', on_hold: 'Ditangguhkan', completed: 'Selesai' }[k] ?? k)
const statusClass = (s) => ({ PLANNING: 'bg-gray-100 text-gray-600', ACTIVE: 'bg-green-100 text-green-700', ON_HOLD: 'bg-yellow-100 text-yellow-700', COMPLETED: 'bg-blue-100 text-blue-700', CANCELLED: 'bg-red-100 text-red-700' }[s] ?? '')
const campaignTypeLabel = (t) => ({ MARKET_RESEARCH: 'Riset Pasar', ADVERTISING: 'Iklan', SALES_MANAGEMENT: 'Manajemen Penjualan', EVENT: 'Event', OTHER: 'Lainnya' }[t] ?? t)
</script>