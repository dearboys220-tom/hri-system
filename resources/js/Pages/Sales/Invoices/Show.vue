<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ invoice: Object });

const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
const fmt    = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';

const showPaidForm = ref(false);
const paidForm = useForm({ payment_method: '', payment_notes: '' });

const submitForApproval = () => {
  if (confirm('Ajukan faktur untuk persetujuan?'))
    router.post(route('sales.invoices.submit', props.invoice.id));
};
const approve = () => {
  if (confirm('Setujui faktur ini?'))
    router.post(route('sales.invoices.approve', props.invoice.id));
};
const markSent = () => {
  if (confirm('Tandai faktur sebagai terkirim ke klien?'))
    router.post(route('sales.invoices.mark-sent', props.invoice.id));
};
const markPaid = () => {
  paidForm.post(route('sales.invoices.mark-paid', props.invoice.id), {
    onSuccess: () => { showPaidForm.value = false; paidForm.reset(); },
  });
};

const statusColor = {
  draft: 'bg-gray-100 text-gray-600', pending_approval: 'bg-yellow-100 text-yellow-700',
  approved: 'bg-blue-100 text-blue-700', sent: 'bg-purple-100 text-purple-700',
  paid: 'bg-green-100 text-green-700', overdue: 'bg-red-100 text-red-600',
  cancelled: 'bg-gray-100 text-gray-400',
};
const statusLabel = {
  draft: 'Draft', pending_approval: 'Menunggu Persetujuan', approved: 'Disetujui',
  sent: 'Terkirim', paid: 'Lunas', overdue: 'Jatuh Tempo', cancelled: 'Dibatalkan',
};
const paymentMethods = ['Transfer Bank', 'QRIS', 'GoPay', 'OVO', 'Tunai', 'Lainnya'];
</script>

<template>
  <Head title="Detail Faktur" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <Link :href="route('sales.invoices.index')" class="text-gray-400 hover:text-gray-600">← Kembali</Link>
        <h2 class="text-xl font-semibold text-gray-800">Detail Faktur</h2>
      </div>
    </template>

    <div class="py-6 px-4 max-w-4xl mx-auto space-y-6">

      <!-- ヘッダー -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-start flex-wrap gap-3">
          <div>
            <p class="text-xs font-mono text-gray-400">{{ invoice.invoice_no }}</p>
            <h3 class="text-lg font-bold text-gray-800 mt-1">{{ invoice.client_name }}</h3>
            <p class="text-sm text-gray-500">{{ invoice.service_type }}</p>
          </div>
          <span :class="['px-3 py-1 rounded-full text-sm font-semibold', statusColor[invoice.status]]">
            {{ statusLabel[invoice.status] }}
          </span>
        </div>

        <!-- アクション -->
        <div class="mt-4 flex flex-wrap gap-2">
          <button v-if="invoice.status === 'draft'" @click="submitForApproval"
                  class="px-4 py-2 bg-yellow-500 text-white text-sm rounded-lg hover:bg-yellow-600">
            Ajukan Persetujuan
          </button>
          <button v-if="invoice.status === 'pending_approval'" @click="approve"
                  class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
            ✅ Setujui
          </button>
          <button v-if="invoice.status === 'approved'" @click="markSent"
                  class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
            📤 Kirim ke Klien
          </button>
          <button v-if="invoice.status === 'sent' || invoice.status === 'overdue'"
                  @click="showPaidForm = true"
                  class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
            💰 Konfirmasi Pembayaran
          </button>
          <Link :href="route('sales.orders.show', invoice.order_id)"
                class="px-4 py-2 border text-gray-600 text-sm rounded-lg hover:bg-gray-50">
            ← Lihat Pesanan
          </Link>
        </div>
      </div>

      <!-- 金額詳細 -->
      <div class="bg-white rounded-xl shadow p-6">
        <h4 class="font-semibold text-gray-700 mb-4">Rincian Tagihan</h4>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between text-gray-600">
            <span>Subtotal</span><span>{{ rupiah(invoice.subtotal) }}</span>
          </div>
          <div v-if="invoice.discount_amount > 0" class="flex justify-between text-orange-600">
            <span>Diskon</span><span>- {{ rupiah(invoice.discount_amount) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-800 border-t pt-2 text-base">
            <span>Total Tagihan</span><span>{{ rupiah(invoice.final_amount) }}</span>
          </div>
          <p v-if="invoice.tax_note" class="text-xs text-gray-400">{{ invoice.tax_note }}</p>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-gray-600">
          <div>
            <p class="text-xs text-gray-400">Syarat Pembayaran</p>
            <p>{{ invoice.payment_terms ?? '-' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400">Jatuh Tempo</p>
            <p :class="invoice.status === 'overdue' ? 'text-red-600 font-bold' : ''">
              {{ fmt(invoice.due_date) }}
            </p>
          </div>
        </div>
      </div>

      <!-- 入金済み情報 -->
      <div class="bg-green-50 border border-green-200 rounded-xl p-5" v-if="invoice.paid_at">
        <p class="text-xs font-semibold text-green-600 mb-2">✅ Pembayaran Diterima</p>
        <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
          <div><p class="text-xs text-gray-400">Tanggal Bayar</p>{{ fmt(invoice.paid_at) }}</div>
          <div><p class="text-xs text-gray-400">Metode</p>{{ invoice.payment_method }}</div>
        </div>
        <p v-if="invoice.payment_notes" class="text-xs text-gray-500 mt-2">{{ invoice.payment_notes }}</p>
      </div>

      <!-- 入金確認フォーム -->
      <div class="bg-white rounded-xl shadow p-6" v-if="showPaidForm">
        <h4 class="font-semibold text-gray-700 mb-4">Konfirmasi Pembayaran</h4>
        <div class="space-y-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Metode Pembayaran *</label>
            <select v-model="paidForm.payment_method"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400 focus:outline-none">
              <option value="">-- Pilih --</option>
              <option v-for="m in paymentMethods" :key="m" :value="m">{{ m }}</option>
            </select>
            <p v-if="paidForm.errors.payment_method" class="text-red-500 text-xs mt-1">
              {{ paidForm.errors.payment_method }}
            </p>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Catatan (opsional)</label>
            <textarea v-model="paidForm.payment_notes" rows="3"
                      class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400 focus:outline-none resize-none">
            </textarea>
          </div>
          <div class="flex gap-2 justify-end">
            <button @click="showPaidForm = false"
                    class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Batal</button>
            <button @click="markPaid" :disabled="paidForm.processing"
                    class="px-6 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50">
              {{ paidForm.processing ? 'Menyimpan...' : '💰 Konfirmasi Lunas' }}
            </button>
          </div>
        </div>
      </div>

      <!-- 承認情報 -->
      <div class="bg-white rounded-xl shadow p-4 text-sm text-gray-600" v-if="invoice.approved_at">
        <p class="text-xs text-gray-400">Disetujui oleh</p>
        <p>{{ invoice.approved_by?.name ?? '—' }} · {{ fmt(invoice.approved_at) }}</p>
      </div>

    </div>
  </AuthenticatedLayout>
</template>