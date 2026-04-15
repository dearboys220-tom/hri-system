<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ report: Object, invoices: Array });

const showSentForm  = ref(false);
const sentForm      = useForm({ sent_to_email: '' });

const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
const fmt    = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';

const organizeAi        = () => { if (confirm('Proses laporan dengan AI (H-1)?')) router.post(route('accounting.organize-ai', props.report.id)); };
const submitForApproval = () => { if (confirm('Ajukan laporan untuk persetujuan?')) router.post(route('accounting.submit', props.report.id)); };
const approve           = () => { if (confirm('Setujui laporan ini?')) router.post(route('accounting.approve', props.report.id)); };
const acknowledge       = () => { if (confirm('Konfirmasi laporan telah diterima oleh akuntan?')) router.post(route('accounting.acknowledge', props.report.id)); };
const markSent          = () => {
  sentForm.post(route('accounting.mark-sent', props.report.id), {
    onSuccess: () => { showSentForm.value = false; sentForm.reset(); },
  });
};

const statusColor = {
  draft: 'bg-gray-100 text-gray-600', ai_organized: 'bg-blue-100 text-blue-700',
  pending_approval: 'bg-yellow-100 text-yellow-700', approved: 'bg-indigo-100 text-indigo-700',
  sent: 'bg-purple-100 text-purple-700', acknowledged: 'bg-green-100 text-green-700',
};
const statusLabel = {
  draft: 'Draft', ai_organized: 'AI Terorganisir',
  pending_approval: 'Menunggu Persetujuan', approved: 'Disetujui',
  sent: 'Terkirim ke Akuntan', acknowledged: 'Diterima Akuntan',
};

// AI サマリーのパース
const aiSummary = () => {
  try { return JSON.parse(props.report.ai_summary); } catch { return null; }
};
const aiChecklist = () => {
  try { return JSON.parse(props.report.ai_checklist); } catch { return []; }
};
</script>

<template>
  <Head title="Detail Laporan Akuntansi" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <Link :href="route('accounting.index')" class="text-gray-400 hover:text-gray-600">← Kembali</Link>
        <h2 class="text-xl font-semibold text-gray-800">Laporan Akuntansi: {{ report.report_month }}</h2>
      </div>
    </template>

    <div class="py-6 px-4 max-w-4xl mx-auto space-y-6">

      <!-- ヘッダー -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-start flex-wrap gap-3">
          <div>
            <h3 class="text-lg font-bold text-gray-800">{{ report.report_month }}</h3>
            <p class="text-sm text-gray-500">{{ report.period_from }} 〜 {{ report.period_to }}</p>
          </div>
          <span :class="['px-3 py-1 rounded-full text-sm font-semibold', statusColor[report.status]]">
            {{ statusLabel[report.status] }}
          </span>
        </div>

        <!-- アクションボタン -->
        <div class="mt-4 flex flex-wrap gap-2">
          <button v-if="['draft', 'ai_organized'].includes(report.status)"
                  @click="organizeAi"
                  class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
            🤖 Proses dengan AI (H-1)
          </button>
          <button v-if="report.status === 'ai_organized'" @click="submitForApproval"
                  class="px-4 py-2 bg-yellow-500 text-white text-sm rounded-lg hover:bg-yellow-600">
            Ajukan Persetujuan
          </button>
          <button v-if="report.status === 'pending_approval'" @click="approve"
                  class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
            ✅ Setujui
          </button>
          <button v-if="report.status === 'approved'" @click="showSentForm = true"
                  class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
            📤 Kirim ke Akuntan
          </button>
          <button v-if="report.status === 'sent'" @click="acknowledge"
                  class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
            ✅ Konfirmasi Diterima
          </button>
        </div>
      </div>

      <!-- 集計サマリー -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
          <p class="text-xs text-green-600">Pendapatan Lunas</p>
          <p class="text-xl font-bold text-green-700 mt-1">{{ rupiah(report.total_revenue) }}</p>
          <p class="text-xs text-green-500 mt-1">{{ report.paid_invoice_count }} faktur</p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center">
          <p class="text-xs text-orange-600">Belum Lunas</p>
          <p class="text-xl font-bold text-orange-600 mt-1">{{ rupiah(report.total_pending) }}</p>
          <p class="text-xs text-orange-500 mt-1">{{ report.pending_invoice_count }} faktur</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
          <p class="text-xs text-red-600">Total Pengeluaran</p>
          <p class="text-xl font-bold text-red-600 mt-1">{{ rupiah(report.total_expenses) }}</p>
          <p class="text-xs text-red-400 mt-1">{{ report.expense_items?.length ?? 0 }} item</p>
        </div>
      </div>

      <!-- 対象請求書 -->
      <div class="bg-white rounded-xl shadow p-5" v-if="invoices?.length">
        <h4 class="font-semibold text-gray-700 mb-3">Faktur yang Disertakan</h4>
        <div class="space-y-1 max-h-48 overflow-y-auto">
          <div v-for="inv in invoices" :key="inv.id"
               class="flex justify-between text-xs bg-gray-50 rounded px-3 py-1.5">
            <span class="font-mono text-gray-400">{{ inv.invoice_no }}</span>
            <span class="text-gray-700">{{ inv.client_name }}</span>
            <span class="font-semibold text-green-700">{{ rupiah(inv.final_amount) }}</span>
            <span class="text-gray-400">{{ fmt(inv.paid_at) }}</span>
          </div>
        </div>
      </div>

      <!-- 経費一覧 -->
      <div class="bg-white rounded-xl shadow p-5" v-if="report.expense_items?.length">
        <h4 class="font-semibold text-gray-700 mb-3">Pengeluaran</h4>
        <div class="space-y-1">
          <div v-for="(exp, i) in report.expense_items" :key="i"
               class="flex justify-between text-sm bg-gray-50 rounded px-3 py-1.5">
            <span class="text-gray-700">{{ exp.name }}</span>
            <span class="text-xs text-gray-400 font-mono">{{ exp.receipt_no || '-' }}</span>
            <span class="font-semibold text-red-600">{{ rupiah(exp.amount) }}</span>
          </div>
        </div>
      </div>

      <!-- AI整理結果（H-1） -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-6" v-if="report.ai_generated_at">
        <h4 class="font-semibold text-blue-700 mb-3">🤖 Hasil AI (H-1) — {{ fmt(report.ai_generated_at) }}</h4>

        <!-- リスクフラグ -->
        <div v-if="report.ai_risk_flags?.length" class="flex flex-wrap gap-2 mb-3">
          <span v-for="flag in report.ai_risk_flags" :key="flag"
                class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs rounded-full">⚠️ {{ flag }}</span>
        </div>

        <!-- 異常注記 -->
        <div v-if="report.ai_anomaly_notes" class="mb-3 p-3 bg-yellow-50 rounded-lg text-sm text-yellow-800">
          <p class="text-xs font-semibold text-yellow-600 mb-1">Catatan Anomali</p>
          {{ report.ai_anomaly_notes }}
        </div>

        <!-- チェックリスト -->
        <div v-if="aiChecklist().length" class="mb-3">
          <p class="text-xs font-semibold text-blue-600 mb-2">Checklist Sebelum Pengiriman</p>
          <div class="space-y-1">
            <div v-for="(item, i) in aiChecklist()" :key="i"
                 class="flex items-center gap-2 text-xs bg-white rounded px-3 py-1.5">
              <span :class="{
                'text-red-500': item.status === 'required',
                'text-yellow-500': item.status === 'recommended',
                'text-gray-400': item.status === 'optional'
              }">●</span>
              <span class="text-gray-700">{{ item.item }}</span>
              <span class="ml-auto text-gray-400">{{ item.status }}</span>
            </div>
          </div>
        </div>

        <!-- 送付文草案 -->
        <div v-if="report.ai_draft_cover_letter" class="bg-white rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap border border-blue-200 max-h-60 overflow-y-auto">
          <p class="text-xs font-semibold text-gray-400 mb-2">Draft Surat Pengantar (AI)</p>
          {{ report.ai_draft_cover_letter }}
        </div>

        <!-- 免責事項 -->
        <p class="text-xs text-gray-400 mt-3 italic">
          ⚠️ AI tidak membuat keputusan pajak. Keputusan akhir adalah wewenang akuntan eksternal.
        </p>
      </div>

      <!-- 送付フォーム -->
      <div class="bg-white rounded-xl shadow p-6" v-if="showSentForm">
        <h4 class="font-semibold text-gray-700 mb-3">Kirim ke Akuntan Eksternal</h4>
        <div class="mb-3">
          <label class="block text-xs text-gray-500 mb-1">Email Akuntan *</label>
          <input v-model="sentForm.sent_to_email" type="email"
                 placeholder="akuntan@example.com"
                 class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 focus:outline-none" />
          <p v-if="sentForm.errors.sent_to_email" class="text-red-500 text-xs mt-1">
            {{ sentForm.errors.sent_to_email }}
          </p>
        </div>
        <div class="flex gap-2 justify-end">
          <button @click="showSentForm = false"
                  class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="markSent" :disabled="sentForm.processing"
                  class="px-6 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 disabled:opacity-50">
            {{ sentForm.processing ? 'Menyimpan...' : '📤 Kirim' }}
          </button>
        </div>
      </div>

      <!-- 承認・送付情報 -->
      <div class="bg-white rounded-xl shadow p-4 text-sm text-gray-600 space-y-2" v-if="report.approved_at || report.sent_at">
        <div v-if="report.approved_at">
          <p class="text-xs text-gray-400">Disetujui oleh</p>
          <p>{{ report.approved_by?.name ?? '—' }} · {{ fmt(report.approved_at) }}</p>
        </div>
        <div v-if="report.sent_at">
          <p class="text-xs text-gray-400">Dikirim ke</p>
          <p>{{ report.sent_to_email }} · {{ fmt(report.sent_at) }}</p>
        </div>
        <div v-if="report.acknowledged_at">
          <p class="text-xs text-gray-400">Dikonfirmasi diterima</p>
          <p>{{ fmt(report.acknowledged_at) }}</p>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>