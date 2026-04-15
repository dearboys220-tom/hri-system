<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ estimate: Object });

const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
const fmt    = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';

const submit    = () => { if (confirm('Ajukan penawaran untuk persetujuan?')) router.post(route('sales.estimates.submit', props.estimate.id)); };
const approve   = () => { if (confirm('Setujui penawaran ini?'))              router.post(route('sales.estimates.approve', props.estimate.id)); };
const markSent  = () => { if (confirm('Tandai sebagai terkirim?'))            router.post(route('sales.estimates.mark-sent', props.estimate.id)); };
const accept    = () => { if (confirm('Konfirmasi penerimaan dan buat pesanan?')) router.post(route('sales.estimates.accept', props.estimate.id)); };

const statusColor = {
  draft: 'bg-gray-100 text-gray-600', pending_approval: 'bg-yellow-100 text-yellow-700',
  approved: 'bg-blue-100 text-blue-700', sent: 'bg-purple-100 text-purple-700',
  accepted: 'bg-green-100 text-green-700', expired: 'bg-red-100 text-red-500',
};
const statusLabel = {
  draft: 'Draft', pending_approval: 'Menunggu Persetujuan', approved: 'Disetujui',
  sent: 'Terkirim', accepted: 'Diterima', revised: 'Direvisi', expired: 'Kadaluarsa',
};
</script>

<template>
  <Head title="Detail Penawaran" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <Link :href="route('sales.estimates.index')" class="text-gray-400 hover:text-gray-600">← Kembali</Link>
        <h2 class="text-xl font-semibold text-gray-800">Detail Penawaran</h2>
      </div>
    </template>

    <div class="py-6 px-4 max-w-4xl mx-auto space-y-6">

      <!-- ヘッダー -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-start flex-wrap gap-3">
          <div>
            <p class="text-xs font-mono text-gray-400">{{ estimate.estimate_no }}</p>
            <h3 class="text-lg font-bold text-gray-800 mt-1">{{ estimate.title }}</h3>
            <p class="text-sm text-gray-500">{{ estimate.client_name }} · {{ estimate.service_type }}</p>
          </div>
          <span :class="['px-3 py-1 rounded-full text-sm font-semibold', statusColor[estimate.status]]">
            {{ statusLabel[estimate.status] }}
          </span>
        </div>

        <!-- アクションボタン -->
        <div class="mt-4 flex flex-wrap gap-2">
          <button v-if="estimate.status === 'draft'" @click="submit"
                  class="px-4 py-2 bg-yellow-500 text-white text-sm rounded-lg hover:bg-yellow-600">
            Ajukan Persetujuan
          </button>
          <button v-if="estimate.status === 'pending_approval'" @click="approve"
                  class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
            ✅ Setujui
          </button>
          <button v-if="estimate.status === 'approved'" @click="markSent"
                  class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
            📤 Tandai Terkirim
          </button>
          <button v-if="estimate.status === 'sent'" @click="accept"
                  class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
            🎉 Konfirmasi Penerimaan → Buat Pesanan
          </button>
          <Link v-if="estimate.order" :href="route('sales.orders.show', estimate.order.id)"
                class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900">
            → Lihat Pesanan
          </Link>
        </div>
      </div>

      <!-- 金額詳細 -->
      <div class="bg-white rounded-xl shadow p-6">
        <h4 class="font-semibold text-gray-700 mb-3">Rincian Harga</h4>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between text-gray-600">
            <span>Subtotal</span><span>{{ rupiah(estimate.subtotal) }}</span>
          </div>
          <div v-if="estimate.discount_exists" class="flex justify-between text-orange-600">
            <span>Diskon <span class="text-xs text-gray-400">（{{ estimate.discount_reason }}）</span></span>
            <span>- {{ rupiah(estimate.discount_amount) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-800 border-t pt-2 text-base">
            <span>Total</span><span>{{ rupiah(estimate.final_amount) }}</span>
          </div>
          <p v-if="estimate.tax_note" class="text-xs text-gray-400">{{ estimate.tax_note }}</p>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm text-gray-600">
          <div><p class="text-xs text-gray-400">Masa Berlaku</p>{{ estimate.validity_days }} hari (s/d {{ fmt(estimate.valid_until) }})</div>
          <div><p class="text-xs text-gray-400">Syarat Pembayaran</p>{{ estimate.payment_terms ?? '-' }}</div>
        </div>
        <div class="mt-2 flex gap-3 text-xs text-gray-500">
          <span v-if="estimate.contract_required" class="px-2 py-0.5 bg-gray-100 rounded">📄 Perlu Kontrak</span>
          <span v-if="estimate.nda_required" class="px-2 py-0.5 bg-gray-100 rounded">🔒 Perlu NDA</span>
        </div>
      </div>

      <!-- AI生成コンテンツ -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-6" v-if="estimate.ai_estimate_body">
        <h4 class="font-semibold text-blue-700 mb-3">🤖 Konten AI (G-1)</h4>
        <div v-if="estimate.ai_risk_flags?.length" class="flex flex-wrap gap-2 mb-3">
          <span v-for="flag in estimate.ai_risk_flags" :key="flag"
                class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs rounded-full">⚠️ {{ flag }}</span>
        </div>
        <div class="bg-white rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap border border-blue-200 max-h-60 overflow-y-auto">
          {{ estimate.ai_estimate_body }}
        </div>
        <div v-if="estimate.ai_cover_email_draft" class="mt-3 bg-white rounded-lg p-3 text-xs text-gray-600 whitespace-pre-wrap border border-blue-200 max-h-40 overflow-y-auto">
          <p class="font-semibold text-gray-400 mb-1">Draft Email Pengantar</p>
          {{ estimate.ai_cover_email_draft }}
        </div>
      </div>

      <!-- 承認情報 -->
      <div class="bg-white rounded-xl shadow p-4 text-sm text-gray-600" v-if="estimate.approved_at">
        <p class="text-xs text-gray-400">Disetujui oleh</p>
        <p>{{ estimate.approved_by?.name ?? '—' }} · {{ fmt(estimate.approved_at) }}</p>
      </div>

    </div>
  </AuthenticatedLayout>
</template>