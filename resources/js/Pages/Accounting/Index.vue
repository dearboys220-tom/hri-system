<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ reports: Object });

const statusLabel = {
  draft:            'Draft',
  ai_organized:     'AI Terorganisir',
  pending_approval: 'Menunggu Persetujuan',
  approved:         'Disetujui',
  sent:             'Terkirim',
  acknowledged:     'Diterima Akuntan',
};
const statusColor = {
  draft:            'bg-gray-100 text-gray-600',
  ai_organized:     'bg-blue-100 text-blue-700',
  pending_approval: 'bg-yellow-100 text-yellow-700',
  approved:         'bg-indigo-100 text-indigo-700',
  sent:             'bg-purple-100 text-purple-700',
  acknowledged:     'bg-green-100 text-green-700',
};
const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
</script>

<template>
  <Head title="Laporan Akuntansi Bulanan" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800">Laporan Akuntansi Bulanan</h2>
        <Link :href="route('accounting.create')"
              class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
          + Buat Laporan
        </Link>
      </div>
    </template>

    <div class="py-6 px-4 max-w-7xl mx-auto">
      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Bulan</th>
              <th class="px-4 py-3 text-right">Pendapatan</th>
              <th class="px-4 py-3 text-right">Belum Lunas</th>
              <th class="px-4 py-3 text-right">Pengeluaran</th>
              <th class="px-4 py-3 text-center">Faktur Lunas</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-center">Dikirim ke</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="r in reports.data" :key="r.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-semibold text-gray-800">{{ r.report_month }}</td>
              <td class="px-4 py-3 text-right font-semibold text-green-700">{{ rupiah(r.total_revenue) }}</td>
              <td class="px-4 py-3 text-right text-orange-600">{{ rupiah(r.total_pending) }}</td>
              <td class="px-4 py-3 text-right text-red-600">{{ rupiah(r.total_expenses) }}</td>
              <td class="px-4 py-3 text-center text-gray-600">{{ r.paid_invoice_count }}件</td>
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusColor[r.status]]">
                  {{ statusLabel[r.status] }}
                </span>
              </td>
              <td class="px-4 py-3 text-center text-xs text-gray-400">
                {{ r.sent_to_email ?? '-' }}
              </td>
              <td class="px-4 py-3 text-center">
                <Link :href="route('accounting.show', r.id)"
                      class="text-blue-600 hover:underline text-xs">Detail</Link>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="px-4 py-3 border-t flex justify-between text-sm text-gray-500">
          <span>{{ reports.total }} laporan</span>
          <div class="flex gap-2">
            <Link v-if="reports.prev_page_url" :href="reports.prev_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">← Prev</Link>
            <Link v-if="reports.next_page_url" :href="reports.next_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">Next →</Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>