<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({ available_months: Array });

const selectedMonth = ref('');
const monthData     = ref(null);
const fetchLoading  = ref(false);

const form = useForm({
  report_month:  '',
  period_from:   '',
  period_to:     '',
  expense_items: [],
  notes:         '',
});

// 月選択時にデータ自動集計
watch(selectedMonth, async (month) => {
  if (!month) return;
  fetchLoading.value = true;
  form.report_month  = month;
  try {
    const res = await axios.post(route('accounting.fetch-month-data'), { month });
    monthData.value  = res.data;
    form.period_from = res.data.period_from;
    form.period_to   = res.data.period_to;
  } catch (e) {
    alert('Gagal mengambil data bulan.');
  } finally {
    fetchLoading.value = false;
  }
});

const addExpense = () => {
  form.expense_items.push({ name: '', amount: 0, receipt_no: '' });
};
const removeExpense = (i) => {
  form.expense_items.splice(i, 1);
};

const totalExpenses  = () => form.expense_items.reduce((s, e) => s + Number(e.amount || 0), 0);
const rupiah         = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');

const submit = () => form.post(route('accounting.store'));
</script>

<template>
  <Head title="Buat Laporan Akuntansi" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Buat Laporan Akuntansi Bulanan</h2>
    </template>

    <div class="py-6 px-4 max-w-3xl mx-auto space-y-6">

      <!-- 月選択 -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-3">Pilih Bulan Laporan</h3>
        <select v-model="selectedMonth"
                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
          <option value="">-- Pilih Bulan --</option>
          <option v-for="m in available_months" :key="m" :value="m">{{ m }}</option>
        </select>
        <p v-if="fetchLoading" class="text-xs text-blue-500 mt-2">Mengambil data...</p>
      </div>

      <!-- 集計データプレビュー -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-6" v-if="monthData">
        <h3 class="font-semibold text-blue-700 mb-3">📊 Data Otomatis dari Faktur</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div class="bg-white rounded-lg p-3">
            <p class="text-xs text-gray-400">Pendapatan (Lunas)</p>
            <p class="font-bold text-green-700 text-base">{{ rupiah(monthData.total_revenue) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ monthData.paid_invoice_count }} faktur</p>
          </div>
          <div class="bg-white rounded-lg p-3">
            <p class="text-xs text-gray-400">Belum Lunas</p>
            <p class="font-bold text-orange-600 text-base">{{ rupiah(monthData.total_pending) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ monthData.pending_invoice_count }} faktur</p>
          </div>
        </div>

        <!-- 集計済み請求書一覧 -->
        <div class="mt-3" v-if="monthData.paid_invoices?.length">
          <p class="text-xs font-semibold text-blue-600 mb-2">Faktur Lunas yang Disertakan</p>
          <div class="space-y-1 max-h-32 overflow-y-auto">
            <div v-for="inv in monthData.paid_invoices" :key="inv.id"
                 class="flex justify-between text-xs bg-white rounded px-2 py-1">
              <span class="font-mono text-gray-500">{{ inv.invoice_no }}</span>
              <span class="text-gray-700">{{ inv.client_name }}</span>
              <span class="font-semibold text-green-700">{{ rupiah(inv.final_amount) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 経費入力 -->
      <div class="bg-white rounded-xl shadow p-6" v-if="monthData">
        <div class="flex justify-between items-center mb-3">
          <h3 class="font-semibold text-gray-700">Pengeluaran / Biaya Operasional</h3>
          <button @click="addExpense" type="button"
                  class="px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200">
            + Tambah
          </button>
        </div>

        <div v-if="form.expense_items.length === 0" class="text-sm text-gray-400 text-center py-4">
          Tidak ada pengeluaran. Klik "+ Tambah" untuk menambahkan.
        </div>

        <div v-for="(exp, i) in form.expense_items" :key="i"
             class="grid grid-cols-12 gap-2 mb-2 items-center">
          <input v-model="exp.name" placeholder="Nama biaya"
                 class="col-span-5 border rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none" />
          <input v-model="exp.amount" type="number" min="0" placeholder="Jumlah (Rp)"
                 class="col-span-3 border rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none" />
          <input v-model="exp.receipt_no" placeholder="No. kwitansi"
                 class="col-span-3 border rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none" />
          <button @click="removeExpense(i)" type="button"
                  class="col-span-1 text-red-400 hover:text-red-600 text-center">✕</button>
        </div>

        <div v-if="form.expense_items.length" class="mt-2 text-right text-sm font-semibold text-red-600">
          Total Pengeluaran: {{ rupiah(totalExpenses()) }}
        </div>
      </div>

      <!-- メモ・送信 -->
      <div class="bg-white rounded-xl shadow p-6" v-if="monthData">
        <div class="mb-4">
          <label class="block text-xs text-gray-500 mb-1">Catatan (opsional)</label>
          <textarea v-model="form.notes" rows="3"
                    placeholder="Catatan khusus untuk bulan ini..."
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none">
          </textarea>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" onclick="history.back()"
                  class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="submit" :disabled="form.processing"
                  class="px-6 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Laporan' }}
          </button>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>