<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ orders: Object });

const statusLabel = {
  confirmed: 'Dikonfirmasi', in_progress: 'Sedang Berjalan',
  completed: 'Selesai', cancelled: 'Dibatalkan',
};
const statusColor = {
  confirmed:   'bg-blue-100 text-blue-700',
  in_progress: 'bg-yellow-100 text-yellow-700',
  completed:   'bg-green-100 text-green-700',
  cancelled:   'bg-red-100 text-red-500',
};
const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
const fmt    = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
</script>

<template>
  <Head title="Manajemen Pesanan" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Manajemen Pesanan (Order)</h2>
    </template>

    <div class="py-6 px-4 max-w-7xl mx-auto">
      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">No. Pesanan</th>
              <th class="px-4 py-3 text-left">No. Penawaran</th>
              <th class="px-4 py-3 text-left">Klien</th>
              <th class="px-4 py-3 text-left">Layanan</th>
              <th class="px-4 py-3 text-right">Jumlah</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-center">Faktur</th>
              <th class="px-4 py-3 text-center">Tgl. Dibuat</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="ord in orders.data" :key="ord.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ ord.order_no }}</td>
              <td class="px-4 py-3 font-mono text-xs text-gray-400">
                <Link :href="route('sales.estimates.show', ord.estimate_id)"
                      class="hover:underline text-blue-500">
                  {{ ord.estimate?.estimate_no ?? '-' }}
                </Link>
              </td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ ord.client_name }}</td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ ord.service_type }}</td>
              <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ rupiah(ord.final_amount) }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusColor[ord.status]]">
                  {{ statusLabel[ord.status] }}
                </span>
              </td>
              <td class="px-4 py-3 text-center text-xs">
                <Link v-if="ord.invoice" :href="route('sales.invoices.show', ord.invoice.id)"
                      class="text-green-600 hover:underline">Ada ✓</Link>
                <span v-else class="text-gray-400">Belum</span>
              </td>
              <td class="px-4 py-3 text-center text-xs text-gray-500">{{ fmt(ord.created_at) }}</td>
              <td class="px-4 py-3 text-center">
                <Link :href="route('sales.orders.show', ord.id)"
                      class="text-blue-600 hover:underline text-xs">Detail</Link>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="px-4 py-3 border-t flex justify-between text-sm text-gray-500">
          <span>{{ orders.total }} pesanan</span>
          <div class="flex gap-2">
            <Link v-if="orders.prev_page_url" :href="orders.prev_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">← Prev</Link>
            <Link v-if="orders.next_page_url" :href="orders.next_page_url"
                  class="px-3 py-1 border rounded hover:bg-gray-50">Next →</Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>