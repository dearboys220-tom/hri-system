<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  evaluations: Object,
  staffList: Array,
})

const page = usePage()
const flash = computed(() => page.props.flash ?? {})

const form = ref({
  staff_user_id: '',
  evaluation_period_from: '',
  evaluation_period_to: '',
  evaluation_type: 'MONTHLY',
})
const generating = ref(false)

function generateEvaluation() {
  generating.value = true
  router.post(route('manager.evaluations.generate'), form.value, {
    onFinish: () => (generating.value = false),
  })
}

const approveModal = ref(false)
const selectedEval = ref(null)
const approveForm = ref({ human_final_band: '', human_override_reason: '' })

function openApprove(ev) {
  selectedEval.value = ev
  approveForm.value = {
    human_final_band: ev.ai_performance_band,
    human_override_reason: '',
  }
  approveModal.value = true
}

function submitApprove() {
  router.post(
    route('manager.evaluations.approve', selectedEval.value.id),
    approveForm.value,
    { onSuccess: () => (approveModal.value = false) }
  )
}

const bandColor = {
  GOOD:    'bg-green-100 text-green-800',
  FAIR:    'bg-yellow-100 text-yellow-800',
  WARNING: 'bg-red-100 text-red-800',
}
const bandLabel = { GOOD: 'Baik', FAIR: 'Cukup', WARNING: 'Peringatan' }

const roleLabel = {
  investigator_user: 'Investigasi',
  admin_user: 'Admin',
  em_staff: 'Staf',
  strategy_user: 'Strategi',
  ai_dev_user: 'AI Dev',
  marketing_user: 'Marketing',
}

const evalTypeLabel = {
  TASK_BASED: 'Per Tugas',
  MONTHLY: 'Bulanan',
  QUARTERLY: 'Triwulan',
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">

    <!-- Header -->
    <div class="mb-6 flex items-center gap-4">
      <a :href="route('manager.dashboard')" class="text-sm text-blue-600 hover:underline">
        ← Dashboard
      </a>
      <h1 class="text-2xl font-bold text-gray-800">📊 Manajemen Penilaian AI</h1>
    </div>

    <!-- Flash messages -->
    <div v-if="flash.success"
      class="mb-4 rounded bg-green-100 border border-green-300 px-4 py-3 text-green-800">
      {{ flash.success }}
    </div>
    <div v-if="flash.error"
      class="mb-4 rounded bg-red-100 border border-red-300 px-4 py-3 text-red-800">
      {{ flash.error }}
    </div>

    <!-- Form: generate evaluation -->
    <div class="mb-6 rounded-lg bg-white shadow p-6">
      <h2 class="text-lg font-semibold text-gray-700 mb-4">
        🤖 Buat Draft Penilaian AI
      </h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <!-- Staff selection -->
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Staf yang Dinilai</label>
          <select v-model="form.staff_user_id"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
            <option value="">-- Pilih Staf --</option>
            <option v-for="s in staffList" :key="s.id" :value="s.id">
              {{ s.name }} ({{ roleLabel[s.role_type] ?? s.role_type }})
            </option>
          </select>
        </div>

        <!-- Period from -->
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Periode Mulai</label>
          <input type="date" v-model="form.evaluation_period_from"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
        </div>

        <!-- Period to -->
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Periode Selesai</label>
          <input type="date" v-model="form.evaluation_period_to"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400" />
        </div>

        <!-- Evaluation type -->
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Jenis Penilaian</label>
          <select v-model="form.evaluation_type"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
            <option value="TASK_BASED">Per Tugas</option>
            <option value="MONTHLY">Bulanan</option>
            <option value="QUARTERLY">Triwulan</option>
          </select>
        </div>
      </div>

      <div class="mt-4">
        <button @click="generateEvaluation"
          :disabled="generating || !form.staff_user_id || !form.evaluation_period_from || !form.evaluation_period_to"
          class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 text-sm font-medium">
          {{ generating ? '⏳ Sedang membuat penilaian...' : '🤖 Buat Draft Penilaian AI' }}
        </button>
        <p class="mt-2 text-xs text-gray-400">
          ※ Penilaian dibuat berdasarkan tingkat penyelesaian tugas, bukti laporan, dan kehadiran.
          Konfirmasi akhir dilakukan oleh manajer.
        </p>
      </div>
    </div>

    <!-- Evaluation list -->
    <div class="rounded-lg bg-white shadow overflow-hidden">
      <div class="px-6 py-4 border-b bg-gray-50">
        <h2 class="text-base font-semibold text-gray-700">Daftar Penilaian</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-xs text-gray-500 uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Staf</th>
              <th class="px-4 py-3 text-left">Periode</th>
              <th class="px-4 py-3 text-left">Jenis</th>
              <th class="px-4 py-3 text-center">Hasil AI</th>
              <th class="px-4 py-3 text-center">Skor AI</th>
              <th class="px-4 py-3 text-center">Hasil Final</th>
              <th class="px-4 py-3 text-left">Ringkasan AI</th>
              <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="evaluations.data.length === 0">
              <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                Belum ada data penilaian
              </td>
            </tr>
            <tr v-for="ev in evaluations.data" :key="ev.id" class="hover:bg-gray-50">

              <!-- Staff name -->
              <td class="px-4 py-3 font-medium text-gray-800">
                {{ ev.staff?.name ?? '-' }}
                <div class="text-xs text-gray-400">
                  {{ roleLabel[ev.staff?.role_type] ?? ev.staff?.role_type }}
                </div>
              </td>

              <!-- Period -->
              <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-xs">
                {{ ev.evaluation_period_from }}<br/>s/d {{ ev.evaluation_period_to }}
              </td>

              <!-- Type -->
              <td class="px-4 py-3 text-gray-600 text-xs">
                {{ evalTypeLabel[ev.evaluation_type] ?? ev.evaluation_type }}
              </td>

              <!-- AI band -->
              <td class="px-4 py-3 text-center">
                <span :class="['px-2 py-1 rounded-full text-xs font-bold', bandColor[ev.ai_performance_band]]">
                  {{ bandLabel[ev.ai_performance_band] ?? ev.ai_performance_band }}
                </span>
              </td>

              <!-- AI score -->
              <td class="px-4 py-3 text-center font-mono text-gray-700">
                {{ ev.ai_score ?? '-' }}
              </td>

              <!-- Human final band -->
              <td class="px-4 py-3 text-center">
                <span v-if="ev.human_final_band"
                  :class="['px-2 py-1 rounded-full text-xs font-bold', bandColor[ev.human_final_band]]">
                  {{ bandLabel[ev.human_final_band] }}
                </span>
                <span v-else class="text-xs text-gray-400">Belum dikonfirmasi</span>
              </td>

              <!-- Summary -->
              <td class="px-4 py-3 text-gray-600 max-w-xs">
                <p class="line-clamp-2 text-xs">{{ ev.ai_evaluation_summary }}</p>
              </td>

              <!-- Action -->
              <td class="px-4 py-3 text-center">
                <button v-if="!ev.human_final_band"
                  @click="openApprove(ev)"
                  class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">
                  Konfirmasi
                </button>
                <span v-else class="text-xs text-gray-400">✅ Selesai</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Approve modal -->
    <div v-if="approveModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Konfirmasi Penilaian</h3>

        <div v-if="selectedEval" class="mb-4 text-sm text-gray-600 space-y-1">
          <p><strong>Staf:</strong> {{ selectedEval.staff?.name }}</p>
          <p>
            <strong>Hasil AI:</strong>
            {{ bandLabel[selectedEval.ai_performance_band] }}
            (Skor: {{ selectedEval.ai_score }})
          </p>
          <p class="text-xs text-gray-400 mt-2">{{ selectedEval.ai_evaluation_summary }}</p>
        </div>

        <!-- Band selection -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Hasil Final</label>
          <div class="flex gap-3">
            <label v-for="b in ['GOOD','FAIR','WARNING']" :key="b"
              class="flex items-center gap-1 cursor-pointer">
              <input type="radio" v-model="approveForm.human_final_band" :value="b" />
              <span :class="['px-2 py-0.5 rounded text-xs font-bold', bandColor[b]]">
                {{ bandLabel[b] }}
              </span>
            </label>
          </div>
        </div>

        <!-- Override reason -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Alasan Perubahan
            <span class="text-gray-400 font-normal">
              (wajib diisi jika berbeda dari hasil AI)
            </span>
          </label>
          <textarea v-model="approveForm.human_override_reason" rows="3"
            class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400"
            placeholder="Tuliskan alasan jika Anda mengubah hasil penilaian AI"></textarea>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="approveModal = false"
            class="px-4 py-2 border rounded text-sm text-gray-600 hover:bg-gray-50">
            Batal
          </button>
          <button @click="submitApprove"
            :disabled="!approveForm.human_final_band"
            class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:opacity-50">
            Konfirmasi
          </button>
        </div>
      </div>
    </div>

  </div>
</template>