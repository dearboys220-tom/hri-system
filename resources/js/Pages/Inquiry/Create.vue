<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
  subject: '',
  body: '',
});

const submit = () => {
  form.post(route('inquiry.store'));
};
</script>

<template>
  <Head title="Kirim Pertanyaan" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Kirim Pertanyaan</h2>
    </template>

    <div class="py-8 max-w-2xl mx-auto px-4">
      <!-- SLA案内 -->
      <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
        <p class="font-semibold mb-1">📋 Waktu Respons</p>
        <p>Anggota Umum: Respons pertama dalam 2 hari kerja</p>
        <p>Anggota Perusahaan: Respons pertama dalam 1 hari kerja</p>
        <p>Keluhan / Keberatan: Konfirmasi pada hari yang sama</p>
      </div>

      <div class="bg-white rounded-xl shadow p-6">
        <div class="mb-5">
          <label class="block text-sm font-medium text-gray-700 mb-1">Subjek <span class="text-red-500">*</span></label>
          <input
            v-model="form.subject"
            type="text"
            maxlength="255"
            placeholder="Contoh: Pertanyaan tentang hasil sertifikasi"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <p v-if="form.errors.subject" class="text-red-500 text-xs mt-1">{{ form.errors.subject }}</p>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">Isi Pertanyaan <span class="text-red-500">*</span></label>
          <textarea
            v-model="form.body"
            rows="8"
            maxlength="5000"
            placeholder="Tuliskan pertanyaan Anda secara lengkap dan jelas..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
          ></textarea>
          <p class="text-xs text-gray-400 mt-1 text-right">{{ form.body.length }} / 5000</p>
          <p v-if="form.errors.body" class="text-red-500 text-xs mt-1">{{ form.errors.body }}</p>
        </div>

        <div class="flex justify-end gap-3">
          <Link :href="route('dashboard')" class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Batal</Link>
          <button
            @click="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Mengirim...' : 'Kirim Pertanyaan' }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>