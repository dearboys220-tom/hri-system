<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ estimates: Object, stats: Object });

const statusLabel = {
  draft: 'Draft', pending_approval: 'Menunggu Persetujuan',
  approved: 'Disetujui', sent: 'Terkirim',
  accepted: 'Diterima', revised: 'Direvisi', expired: 'Kadaluarsa',
};
const statusColor = {
  draft: 'bg-gray-100 text-gray-600',
  pending_approval: 'bg-yellow-100 text-yellow-700',
  approved: 'bg-blue-100 text-blue-700',
  sent: 'bg-purple-100 text-purple-700',
  accepted: 'bg-green-100 text-green-700',
  revised: 'bg-orange-100 text-orange-700',
  expired: 'bg-red-100 text-red-500',
};
const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
</script>

<template>
  <Head title="Manajemen Penawaran" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800">Manajemen Penawaran (Estimasi)</h2>
        <Link :href="route('sales.estimates.create')"
              class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
          + Buat Penawaran
        </Link>
      </div>
    </template>

    <div class="py-6 px-4 max-w-7xl mx-auto">
      <!-- サマリー -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-50 border rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-gray-700">{{ stats.draft }}</p>
          <p class="text-xs text-gray-500 mt-1">Draft</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-yellow-700">{{ stats.pending_approval }}</p>
          <p class="text-xs text-yellow-600 mt-1">Menunggu Persetujuan</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-purple-700">{{ stats.sent }}</p>
          <p class="text-xs text-purple-600 mt-1">Terkirim</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-green-700">{{ stats.accepted }}</p>
          <p class="text-xs text-green-600 mt-1">Diterima / Pesanan</p>
        </div>
      </div>

      <!-- テーブル -->
      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">No. Penawaran</th>
              <th class="px-4 py-3 text-left">Klien</th>
              <th class="px-4 py-3 text-left">Layanan</th>
              <th class="px-4 py-3 text-right">Jumlah</th>
              <th class="px-4 py-3 text-center">Berlaku s/d</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="est in estimates.data" :key="est.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ est.estimate_no }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ est.client_name }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ est.service_type }}</td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ rupiah(est.final_amount) }}</td>
              <td class="px-4 py-3 text-center text-xs text-gray-500">{{ est.valid_until }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusColor[est.status]]">
                  {{ statusLabel[est.status] }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <Link :href="route('sales.estimates.show', est.id)"
                      class="text-blue-600 hover:underline text-xs">Detail</Link>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="px-4 py-3 border-t flex justify-between text-sm text-gray-500">
          <span>{{ estimates.total }} penawaran</span>
          <div class="flex gap-2">
            <Link v-if="estimates.prev_page_url" :href="estimates.prev_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">← Prev</Link>
            <Link v-if="estimates.next_page_url" :href="estimates.next_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">Next →</Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>