<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ inquiry: Object });

const replyForm = useForm({ human_reply: '' });

const submitReply = () => {
  replyForm.post(route('admin.inquiry.reply', props.inquiry.id), {
    onSuccess: () => replyForm.reset(),
  });
};

const escalate = () => {
  if (confirm('Eskalasi pertanyaan ini ke supervisor?')) {
    router.post(route('admin.inquiry.escalate', props.inquiry.id));
  }
};

const closeInquiry = () => {
  if (confirm('Tutup pertanyaan ini?')) {
    router.post(route('admin.inquiry.close', props.inquiry.id));
  }
};

const formatDate = (d) => d ? new Date(d).toLocaleString('id-ID') : '-';

const priorityBadge = {
  urgent: 'bg-red-100 text-red-700',
  high:   'bg-orange-100 text-orange-700',
  normal: 'bg-blue-100 text-blue-700',
  low:    'bg-gray-100 text-gray-500',
};
</script>

<template>
  <Head title="Detail Pertanyaan" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <Link :href="route('admin.inquiry.index')" class="text-gray-400 hover:text-gray-600">← Kembali</Link>
        <h2 class="text-xl font-semibold text-gray-800">Detail Pertanyaan</h2>
      </div>
    </template>

    <div class="py-6 px-4 max-w-4xl mx-auto space-y-6">

      <!-- ヘッダー情報 -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-start flex-wrap gap-3">
          <div>
            <p class="text-xs text-gray-400 font-mono">{{ inquiry.inquiry_no }}</p>
            <h3 class="text-lg font-bold text-gray-800 mt-1">{{ inquiry.subject }}</h3>
            <p class="text-sm text-gray-500 mt-1">
              {{ inquiry.user?.name }} ·
              {{ inquiry.user_type === 'company' ? 'Anggota Perusahaan' : 'Anggota Umum' }} ·
              {{ formatDate(inquiry.created_at) }}
            </p>
          </div>
          <div class="flex gap-2">
            <button v-if="inquiry.status !== 'closed' && inquiry.status !== 'escalated'"
                    @click="escalate"
                    class="px-3 py-1.5 text-sm border border-orange-400 text-orange-600 rounded-lg hover:bg-orange-50">
              Eskalasi
            </button>
            <button v-if="inquiry.status !== 'closed'"
                    @click="closeInquiry"
                    class="px-3 py-1.5 text-sm border border-gray-400 text-gray-600 rounded-lg hover:bg-gray-50">
              Tutup
            </button>
          </div>
        </div>

        <!-- 本文 -->
        <div class="mt-4 p-4 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-wrap">
          {{ inquiry.body }}
        </div>
      </div>

      <!-- AI分類結果 -->
      <div class="bg-white rounded-xl shadow p-6" v-if="inquiry.ai_classified_at">
        <h4 class="font-semibold text-gray-700 mb-3">🤖 Hasil Klasifikasi AI (D-{{ inquiry.user_type === 'company' ? '3' : '1' }})</h4>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
          <div>
            <p class="text-xs text-gray-400">Kategori</p>
            <p class="font-medium text-gray-700">{{ inquiry.ai_category ?? '—' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-400">Prioritas</p>
            <span :class="['px-2 py-0.5 rounded text-xs font-semibold', priorityBadge[inquiry.ai_priority] ?? 'bg-gray-100']">
              {{ inquiry.ai_priority?.toUpperCase() ?? '—' }}
            </span>
          </div>
          <div>
            <p class="text-xs text-gray-400">Bisa Dijawab Langsung</p>
            <p :class="inquiry.ai_can_answer_immediately ? 'text-green-600' : 'text-red-500'">
              {{ inquiry.ai_can_answer_immediately ? 'Ya' : 'Tidak' }}
            </p>
          </div>
        </div>

        <!-- 警告フラグ -->
        <div v-if="inquiry.ai_requires_legal_review || inquiry.ai_requires_pdp_review || inquiry.ai_answer_prohibited"
             class="mt-3 flex flex-wrap gap-2">
          <span v-if="inquiry.ai_answer_prohibited"
                class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">⛔ Jawaban Dilarang</span>
          <span v-if="inquiry.ai_requires_legal_review"
                class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs rounded-full">⚖️ Perlu Review Legal</span>
          <span v-if="inquiry.ai_requires_pdp_review"
                class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full">🔒 Perlu Review PDP</span>
          <span v-if="inquiry.ai_requires_supervisor_review"
                class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full">👤 Perlu Review Supervisor</span>
        </div>

        <!-- AI推奨アクション -->
        <div class="mt-3 p-3 bg-blue-50 rounded-lg text-sm text-blue-800" v-if="inquiry.ai_recommended_next_action">
          <p class="text-xs font-semibold text-blue-500 mb-1">Rekomendasi Tindakan AI</p>
          {{ inquiry.ai_recommended_next_action }}
        </div>

        <!-- 回答方向性 -->
        <div class="mt-3 p-3 bg-gray-50 rounded-lg text-sm text-gray-700" v-if="inquiry.ai_draft_reply_direction">
          <p class="text-xs font-semibold text-gray-400 mb-1">Arah Jawaban (AI Draft)</p>
          {{ inquiry.ai_draft_reply_direction }}
        </div>
      </div>

      <!-- SLA情報 -->
      <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4 text-sm">
        <div>
          <p class="text-xs text-gray-400">Batas SLA</p>
          <p :class="inquiry.sla_breached ? 'text-red-600 font-bold' : 'text-gray-700'">
            {{ formatDate(inquiry.sla_deadline) }}
            <span v-if="inquiry.sla_breached"> ⚠️ Terlampaui</span>
          </p>
        </div>
        <div v-if="inquiry.replied_at">
          <p class="text-xs text-gray-400">Dijawab oleh</p>
          <p class="text-gray-700">{{ inquiry.replied_by?.name ?? '—' }} · {{ formatDate(inquiry.replied_at) }}</p>
        </div>
      </div>

      <!-- 回答済み表示 -->
      <div class="bg-green-50 border border-green-200 rounded-xl p-5" v-if="inquiry.human_reply">
        <p class="text-xs font-semibold text-green-600 mb-2">✅ Jawaban Admin</p>
        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ inquiry.human_reply }}</p>
      </div>

      <!-- 回答フォーム -->
      <div class="bg-white rounded-xl shadow p-6"
           v-if="!['answered', 'closed'].includes(inquiry.status)">
        <h4 class="font-semibold text-gray-700 mb-3">Kirim Jawaban</h4>
        <textarea
          v-model="replyForm.human_reply"
          rows="6"
          placeholder="Tulis jawaban Anda di sini..."
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
        ></textarea>
        <p v-if="replyForm.errors.human_reply" class="text-red-500 text-xs mt-1">{{ replyForm.errors.human_reply }}</p>
        <div class="mt-3 flex justify-end">
          <button @click="submitReply" :disabled="replyForm.processing"
                  class="px-6 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ replyForm.processing ? 'Mengirim...' : 'Kirim Jawaban' }}
          </button>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>