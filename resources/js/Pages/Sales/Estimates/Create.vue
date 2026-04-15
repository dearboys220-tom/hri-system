<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const form = useForm({
  title: '', client_name: '', client_email: '', service_type: '',
  scope_included: '', scope_excluded: '', special_notes: '',
  subtotal: 0, discount_exists: false, discount_amount: 0,
  discount_reason: '', final_amount: 0, tax_note: '',
  payment_terms: '', validity_days: 14,
  contract_required: false, nda_required: false,
  ai_estimate_body: '', ai_cover_email_draft: '',
  ai_approval_note: '', ai_risk_flags: [], ai_missing_items: [],
});

const aiLoading  = ref(false);
const aiResult   = ref(null);
const scopeNotes = ref('');
const budgetHint = ref('');

const serviceTypes = [
  'hri_verification', 'verified_resume', 'corporate_membership',
  'job_posting', 'paid_viewing', 'investigation_service',
  'risk_management', 'system_development', 'employee_management_system',
  'maintenance_operation', 'consulting', 'other',
];

const generateAi = async () => {
  if (!form.title || !form.client_name || !form.service_type) {
    alert('Mohon isi Judul, Nama Klien, dan Jenis Layanan terlebih dahulu.');
    return;
  }
  aiLoading.value = true;
  try {
    const res = await axios.post(route('sales.estimates.generate-ai'), {
      title: form.title, client_name: form.client_name,
      service_type: form.service_type,
      scope_notes: scopeNotes.value, budget_hint: budgetHint.value,
    });
    aiResult.value = res.data;
    // AIの結果をフォームに反映
    form.ai_estimate_body     = res.data.estimate_body ?? '';
    form.ai_cover_email_draft = res.data.cover_email_draft ?? '';
    form.ai_approval_note     = res.data.approval_note ?? '';
    form.ai_risk_flags        = res.data.risk_flags ?? [];
    form.ai_missing_items     = res.data.missing_items ?? [];
    // 金額のヒント反映
    if (res.data.price_summary?.final_amount) {
      const amt = parseInt(String(res.data.price_summary.final_amount).replace(/\D/g, ''));
      if (!isNaN(amt)) { form.final_amount = amt; form.subtotal = amt; }
    }
  } catch (e) {
    alert('Gagal menghasilkan penawaran AI. Silakan coba lagi.');
  } finally {
    aiLoading.value = false;
  }
};

const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');

const submit = () => form.post(route('sales.estimates.store'));
</script>

<template>
  <Head title="Buat Penawaran" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Buat Penawaran Baru</h2>
    </template>

    <div class="py-6 px-4 max-w-4xl mx-auto space-y-6">

      <!-- 基本情報 -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Informasi Dasar</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Judul Penawaran *</label>
            <input v-model="form.title" type="text" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Nama Klien *</label>
            <input v-model="form.client_name" type="text" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Email Klien</label>
            <input v-model="form.client_email" type="email" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Jenis Layanan *</label>
            <select v-model="form.service_type" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
              <option value="">-- Pilih --</option>
              <option v-for="st in serviceTypes" :key="st" :value="st">{{ st }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- AI生成パネル -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <h3 class="font-semibold text-blue-700 mb-3">🤖 Generate Penawaran dengan AI (G-1)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
          <div>
            <label class="block text-xs text-blue-600 mb-1">Catatan Ruang Lingkup</label>
            <textarea v-model="scopeNotes" rows="3" placeholder="Deskripsikan ruang lingkup pekerjaan..." class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none bg-white"></textarea>
          </div>
          <div>
            <label class="block text-xs text-blue-600 mb-1">Petunjuk Anggaran (opsional)</label>
            <input v-model="budgetHint" type="text" placeholder="Contoh: Rp 5.000.000 - Rp 10.000.000" class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm focus:outline-none bg-white" />
          </div>
        </div>
        <button @click="generateAi" :disabled="aiLoading"
                class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50">
          {{ aiLoading ? 'Menghasilkan...' : '✨ Generate dengan AI' }}
        </button>

        <!-- AI結果プレビュー -->
        <div v-if="aiResult" class="mt-4 space-y-3">
          <div v-if="aiResult.risk_flags?.length" class="flex flex-wrap gap-2">
            <span v-for="flag in aiResult.risk_flags" :key="flag"
                  class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs rounded-full">⚠️ {{ flag }}</span>
          </div>
          <div v-if="aiResult.missing_items?.length" class="flex flex-wrap gap-2">
            <span v-for="item in aiResult.missing_items" :key="item"
                  class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">❓ {{ item }}</span>
          </div>
          <div class="bg-white rounded-lg p-3 text-xs text-gray-700 whitespace-pre-wrap border border-blue-200 max-h-48 overflow-y-auto">
            <p class="font-semibold text-gray-500 mb-1">Isi Penawaran (AI Draft)</p>
            {{ form.ai_estimate_body }}
          </div>
        </div>
      </div>

      <!-- 金額 -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Rincian Harga (IDR)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Subtotal (Rp) *</label>
            <input v-model="form.subtotal" type="number" min="0" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Jumlah Akhir (Rp) *</label>
            <input v-model="form.final_amount" type="number" min="0" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
          </div>
          <div class="md:col-span-2">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input v-model="form.discount_exists" type="checkbox" class="rounded" />
              Ada Diskon
            </label>
          </div>
          <template v-if="form.discount_exists">
            <div>
              <label class="block text-xs text-gray-500 mb-1">Jumlah Diskon (Rp) *</label>
              <input v-model="form.discount_amount" type="number" min="0" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Alasan Diskon * <span class="text-red-500">（wajib diisi）</span></label>
              <input v-model="form.discount_reason" type="text" placeholder="Contoh: Diskon loyalitas klien lama" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
              <p v-if="form.errors.discount_reason" class="text-red-500 text-xs mt-1">{{ form.errors.discount_reason }}</p>
            </div>
          </template>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Masa Berlaku (hari) *</label>
            <select v-model="form.validity_days" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
              <option :value="7">7 hari</option>
              <option :value="14">14 hari</option>
              <option :value="30">30 hari</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Syarat Pembayaran</label>
            <input v-model="form.payment_terms" type="text" placeholder="Contoh: Pembayaran 14 hari setelah faktur" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
          </div>
        </div>

        <div class="mt-3 flex gap-4">
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input v-model="form.contract_required" type="checkbox" class="rounded" />
            Perlu Kontrak
          </label>
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input v-model="form.nda_required" type="checkbox" class="rounded" />
            Perlu NDA
          </label>
        </div>
      </div>

      <!-- 送信ボタン -->
      <div class="flex justify-end gap-3">
        <button type="button" onclick="history.back()"
                class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Batal</button>
        <button @click="submit" :disabled="form.processing"
                class="px-6 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50">
          {{ form.processing ? 'Menyimpan...' : 'Simpan sebagai Draft' }}
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>