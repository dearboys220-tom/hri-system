<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ invoices: Object, stats: Object });

const statusLabel = {
  draft: 'Draft', pending_approval: 'Menunggu Persetujuan',
  approved: 'Disetujui', sent: 'Terkirim',
  paid: 'Lunas', overdue: 'Jatuh Tempo', cancelled: 'Dibatalkan',
};
const statusColor = {
  draft:            'bg-gray-100 text-gray-600',
  pending_approval: 'bg-yellow-100 text-yellow-700',
  approved:         'bg-blue-100 text-blue-700',
  sent:             'bg-purple-100 text-purple-700',
  paid:             'bg-green-100 text-green-700',
  overdue:          'bg-red-100 text-red-600',
  cancelled:        'bg-gray-100 text-gray-400',
};
const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
const fmt    = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
</script>

<template>
  <Head title="Manajemen Faktur" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Manajemen Faktur (Invoice)</h2>
    </template>

    <div class="py-6 px-4 max-w-7xl mx-auto">

      <!-- サマリー -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-gray-50 border rounded-xl p-3 text-center">
          <p class="text-xl font-bold text-gray-700">{{ stats.draft }}</p>
          <p class="text-xs text-gray-500 mt-1">Draft</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-center">
          <p class="text-xl font-bold text-yellow-700">{{ stats.pending_approval }}</p>
          <p class="text-xs text-yellow-600 mt-1">Menunggu</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-center">
          <p class="text-xl font-bold text-purple-700">{{ stats.sent }}</p>
          <p class="text-xs text-purple-600 mt-1">Terkirim</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
          <p class="text-xl font-bold text-green-700">{{ stats.paid }}</p>
          <p class="text-xs text-green-600 mt-1">Lunas</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
          <p class="text-xl font-bold text-red-600">{{ stats.overdue }}</p>
          <p class="text-xs text-red-500 mt-1">Jatuh Tempo</p>
        </div>
      </div>

      <!-- テーブル -->
      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">No. Faktur</th>
              <th class="px-4 py-3 text-left">Klien</th>
              <th class="px-4 py-3 text-left">Layanan</th>
              <th class="px-4 py-3 text-right">Jumlah</th>
              <th class="px-4 py-3 text-center">Jatuh Tempo</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="inv in invoices.data" :key="inv.id"
                :class="['hover:bg-gray-50', inv.status === 'overdue' ? 'bg-red-50' : '']">
              <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ inv.invoice_no }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ inv.client_name }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ inv.service_type }}</td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ rupiah(inv.final_amount) }}</td>
              <td class="px-4 py-3 text-center text-xs"
                  :class="inv.status === 'overdue' ? 'text-red-600 font-bold' : 'text-gray-500'">
                {{ fmt(inv.due_date) }}
              </td>
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusColor[inv.status]]">
                  {{ statusLabel[inv.status] }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <Link :href="route('sales.invoices.show', inv.id)"
                      class="text-blue-600 hover:underline text-xs">Detail</Link>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="px-4 py-3 border-t flex justify-between text-sm text-gray-500">
          <span>{{ invoices.total }} faktur</span>
          <div class="flex gap-2">
            <Link v-if="invoices.prev_page_url" :href="invoices.prev_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">← Prev</Link>
            <Link v-if="invoices.next_page_url" :href="invoices.next_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">Next →</Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>