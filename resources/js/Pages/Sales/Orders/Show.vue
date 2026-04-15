<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ order: Object });

const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
const fmt    = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';

const createInvoice = () => {
  if (confirm('Buat faktur dari pesanan ini?')) {
    router.post(route('sales.orders.create-invoice', props.order.id));
  }
};
const complete = () => {
  if (confirm('Tandai pesanan ini sebagai selesai?')) {
    router.post(route('sales.orders.complete', props.order.id));
  }
};

const statusColor = {
  confirmed:   'bg-blue-100 text-blue-700',
  in_progress: 'bg-yellow-100 text-yellow-700',
  completed:   'bg-green-100 text-green-700',
  cancelled:   'bg-red-100 text-red-500',
};
const statusLabel = {
  confirmed: 'Dikonfirmasi', in_progress: 'Sedang Berjalan',
  completed: 'Selesai', cancelled: 'Dibatalkan',
};
</script>

<template>
  <Head title="Detail Pesanan" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <Link :href="route('sales.orders.index')" class="text-gray-400 hover:text-gray-600">← Kembali</Link>
        <h2 class="text-xl font-semibold text-gray-800">Detail Pesanan</h2>
      </div>
    </template>

    <div class="py-6 px-4 max-w-4xl mx-auto space-y-6">

      <!-- ヘッダー -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-start flex-wrap gap-3">
          <div>
            <p class="text-xs font-mono text-gray-400">{{ order.order_no }}</p>
            <h3 class="text-lg font-bold text-gray-800 mt-1">{{ order.client_name }}</h3>
            <p class="text-sm text-gray-500">{{ order.service_type }} · {{ fmt(order.created_at) }}</p>
          </div>
          <span :class="['px-3 py-1 rounded-full text-sm font-semibold', statusColor[order.status]]">
            {{ statusLabel[order.status] }}
          </span>
        </div>

        <!-- アクション -->
        <div class="mt-4 flex flex-wrap gap-2">
          <!-- 請求書作成（受注確定後のみ・Section 30.4） -->
          <button v-if="!order.invoice && order.status !== 'cancelled'"
                  @click="createInvoice"
                  class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
            📄 Buat Faktur
          </button>
          <Link v-if="order.invoice"
                :href="route('sales.invoices.show', order.invoice.id)"
                class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
            → Lihat Faktur
          </Link>
          <button v-if="order.status === 'in_progress'" @click="complete"
                  class="px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800">
            ✅ Tandai Selesai
          </button>
          <Link :href="route('sales.estimates.show', order.estimate_id)"
                class="px-4 py-2 border text-gray-600 text-sm rounded-lg hover:bg-gray-50">
            ← Lihat Penawaran
          </Link>
        </div>
      </div>

      <!-- 詳細情報 -->
      <div class="bg-white rounded-xl shadow p-6">
        <h4 class="font-semibold text-gray-700 mb-4">Rincian Pesanan</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-xs text-gray-400">No. Penawaran Asal</p>
            <p class="font-medium text-gray-700">{{ order.estimate?.estimate_no ?? '-' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400">Email Klien</p>
            <p class="text-gray-700">{{ order.client_email ?? '-' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400">Jumlah</p>
            <p class="font-bold text-gray-800 text-base">{{ rupiah(order.final_amount) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400">Syarat Pembayaran</p>
            <p class="text-gray-700">{{ order.payment_terms ?? '-' }}</p>
          </div>
          <div v-if="order.completed_at">
            <p class="text-xs text-gray-400">Tanggal Selesai</p>
            <p class="text-gray-700">{{ fmt(order.completed_at) }}</p>
          </div>
        </div>
        <div v-if="order.notes" class="mt-3 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
          {{ order.notes }}
        </div>
      </div>

      <!-- 請求書ステータス -->
      <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-sm" v-if="order.invoice">
        <p class="font-semibold text-indigo-700 mb-1">📄 Faktur Terkait</p>
        <p class="text-indigo-600 font-mono text-xs">{{ order.invoice.invoice_no }}</p>
        <p class="text-indigo-500 text-xs mt-1">Status: {{ order.invoice.status }}</p>
      </div>

      <!-- ⚠️ 受注前請求防止ガイド（Section 30.4） -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-xs text-yellow-700"
           v-if="!order.invoice">
        <p class="font-semibold mb-1">⚠️ Catatan (Section 30.4)</p>
        <p>Faktur hanya boleh dibuat setelah pesanan dikonfirmasi. Jangan memulai penagihan sebelum pesanan diterima secara resmi.</p>
      </div>

    </div>
  </AuthenticatedLayout>
</template>