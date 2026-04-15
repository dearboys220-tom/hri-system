<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  inquiries: Object,
  stats: Object,
});

const filterStatus   = ref('');
const filterUserType = ref('');
const filterPriority = ref('');

const applyFilter = () => {
  router.get(route('admin.inquiry.index'), {
    status:    filterStatus.value,
    user_type: filterUserType.value,
    priority:  filterPriority.value,
  }, { preserveState: true });
};

const statusLabel = {
  received:   'Diterima',
  classified: 'Diklasifikasikan',
  answered:   'Dijawab',
  escalated:  'Dieskalasi',
  closed:     'Ditutup',
};
const statusColor = {
  received:   'bg-yellow-100 text-yellow-700',
  classified: 'bg-blue-100 text-blue-700',
  answered:   'bg-green-100 text-green-700',
  escalated:  'bg-red-100 text-red-700',
  closed:     'bg-gray-100 text-gray-500',
};
const priorityColor = {
  urgent: 'text-red-600 font-bold',
  high:   'text-orange-500 font-semibold',
  normal: 'text-gray-600',
  low:    'text-gray-400',
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
</script>

<template>
  <Head title="Manajemen Pertanyaan" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Manajemen Pertanyaan</h2>
    </template>

    <div class="py-6 px-4 max-w-7xl mx-auto">

      <!-- サマリーカード -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-yellow-700">{{ stats.received }}</p>
          <p class="text-xs text-yellow-600 mt-1">Diterima</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-blue-700">{{ stats.classified }}</p>
          <p class="text-xs text-blue-600 mt-1">Diklasifikasikan AI</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-red-700">{{ stats.escalated }}</p>
          <p class="text-xs text-red-600 mt-1">Eskalasi</p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-orange-700">{{ stats.sla_breach }}</p>
          <p class="text-xs text-orange-600 mt-1">Pelanggaran SLA</p>
        </div>
      </div>

      <!-- フィルター -->
      <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Status</label>
          <select v-model="filterStatus" class="border rounded-lg px-3 py-1.5 text-sm">
            <option value="">Semua</option>
            <option value="received">Diterima</option>
            <option value="classified">Diklasifikasikan</option>
            <option value="answered">Dijawab</option>
            <option value="escalated">Dieskalasi</option>
            <option value="closed">Ditutup</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Jenis Pengguna</label>
          <select v-model="filterUserType" class="border rounded-lg px-3 py-1.5 text-sm">
            <option value="">Semua</option>
            <option value="applicant">Anggota Umum</option>
            <option value="company">Anggota Perusahaan</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Prioritas AI</label>
          <select v-model="filterPriority" class="border rounded-lg px-3 py-1.5 text-sm">
            <option value="">Semua</option>
            <option value="urgent">Urgent</option>
            <option value="high">High</option>
            <option value="normal">Normal</option>
            <option value="low">Low</option>
          </select>
        </div>
        <button @click="applyFilter" class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
          Filter
        </button>
      </div>

      <!-- テーブル -->
      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">No. Pertanyaan</th>
              <th class="px-4 py-3 text-left">Pengirim</th>
              <th class="px-4 py-3 text-left">Subjek</th>
              <th class="px-4 py-3 text-left">Kategori AI</th>
              <th class="px-4 py-3 text-center">Prioritas</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-center">SLA</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="inq in inquiries.data" :key="inq.id"
                :class="['hover:bg-gray-50', inq.sla_breached ? 'bg-red-50' : '']">
              <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ inq.inquiry_no ?? '-' }}</td>
              <td class="px-4 py-3">
                <p class="font-medium text-gray-800">{{ inq.user?.name ?? '-' }}</p>
                <p class="text-xs text-gray-400">{{ inq.user_type === 'company' ? 'Perusahaan' : 'Umum' }}</p>
              </td>
              <td class="px-4 py-3 max-w-xs truncate text-gray-700">{{ inq.subject }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ inq.ai_category ?? '—' }}</td>
              <td class="px-4 py-3 text-center text-xs">
                <span :class="priorityColor[inq.ai_priority] ?? 'text-gray-400'">
                  {{ inq.ai_priority ? inq.ai_priority.toUpperCase() : '—' }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusColor[inq.status]]">
                  {{ statusLabel[inq.status] ?? inq.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-center text-xs">
                <span :class="inq.sla_breached ? 'text-red-600 font-bold' : 'text-gray-500'">
                  {{ formatDate(inq.sla_deadline) }}
                  <span v-if="inq.sla_breached">⚠️</span>
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <Link :href="route('admin.inquiry.show', inq.id)"
                      class="text-blue-600 hover:underline text-xs">Detail</Link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- ページネーション -->
        <div class="px-4 py-3 border-t flex justify-between items-center text-sm text-gray-500">
          <span>{{ inquiries.total }} pertanyaan</span>
          <div class="flex gap-2">
            <Link v-if="inquiries.prev_page_url" :href="inquiries.prev_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">← Prev</Link>
            <Link v-if="inquiries.next_page_url" :href="inquiries.next_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">Next →</Link>
          </div>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>