<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  orders:         Array,
  availableStaff: Array,
})

const page  = usePage()
const flash = computed(() => page.props.flash ?? {})

// ── タブ ──
const activeTab = ref('draft') // 'draft' | 'active' | 'done'

const draftOrders  = computed(() => props.orders.filter(o => o.approval_status === 'DRAFT'))
const activeOrders = computed(() => props.orders.filter(o =>
  ['APPROVED', 'IN_PROGRESS'].includes(o.approval_status)))
const doneOrders   = computed(() => props.orders.filter(o =>
  ['COMPLETED', 'CANCELLED', 'CLOSED'].includes(o.approval_status)))

// ── 承認モーダル ──
const approveModal   = ref(false)
const approveTarget  = ref(null)
const selectedStaff  = ref([])

function openApproveModal(order) {
  approveTarget.value = order
  selectedStaff.value = []
  approveModal.value  = true
}
function closeApproveModal() {
  approveModal.value = false
  approveTarget.value = null
  selectedStaff.value = []
}
function toggleStaff(userId) {
  if (selectedStaff.value.includes(userId)) {
    selectedStaff.value = selectedStaff.value.filter(id => id !== userId)
  } else {
    selectedStaff.value.push(userId)
  }
}
function submitApprove() {
  if (selectedStaff.value.length === 0) {
    alert('担当者を1名以上選択してください。')
    return
  }
  router.post(route('manager.task-orders.approve', approveTarget.value.id), {
    assignee_ids: selectedStaff.value,
  }, {
    onSuccess: closeApproveModal,
  })
}

// ── 新規作成モーダル ──
const createModal = ref(false)
const form = ref({
  title: '', description: '', target_division: 'ADMIN',
  priority: 'NORMAL', due_date: '', assignee_ids: [],
})
function openCreateModal() {
  form.value = {
    title: '', description: '', target_division: 'ADMIN',
    priority: 'NORMAL', due_date: '', assignee_ids: [],
  }
  createModal.value = true
}
function toggleFormStaff(userId) {
  if (form.value.assignee_ids.includes(userId)) {
    form.value.assignee_ids = form.value.assignee_ids.filter(id => id !== userId)
  } else {
    form.value.assignee_ids.push(userId)
  }
}
function submitCreate() {
  if (!form.value.title || !form.value.description) {
    alert('Judul dan deskripsi wajib diisi.')
    return
  }
  if (form.value.assignee_ids.length === 0) {
    alert('Pilih minimal 1 staf.')
    return
  }
  router.post(route('manager.task-orders.store'), form.value, {
    onSuccess: () => { createModal.value = false },
  })
}

// ── キャンセル ──
function cancelOrder(order) {
  if (!confirm('Batalkan instruksi ini?')) return
  router.post(route('manager.task-orders.cancel', order.id))
}

// ── ラベル ──
const divisionLabel = {
  INVESTIGATION: 'Investigasi', ADMIN: 'Admin',
  STRATEGY: 'Strategi', AI_DEV: 'AI Dev', MARKETING: 'Marketing',
}
const priorityColor = {
  URGENT: 'bg-red-100 text-red-700', HIGH: 'bg-orange-100 text-orange-700',
  NORMAL: 'bg-blue-100 text-blue-700', LOW: 'bg-gray-100 text-gray-600',
}
const statusLabel = {
  DRAFT: 'Menunggu Persetujuan', APPROVED: 'Disetujui',
  IN_PROGRESS: 'Berlangsung', COMPLETED: 'Selesai',
  CANCELLED: 'Dibatalkan', CLOSED: 'Ditutup',
}
const statusColor = {
  DRAFT: 'bg-yellow-100 text-yellow-700',
  APPROVED: 'bg-blue-100 text-blue-700',
  IN_PROGRESS: 'bg-indigo-100 text-indigo-700',
  COMPLETED: 'bg-green-100 text-green-700',
  CANCELLED: 'bg-red-100 text-red-700',
  CLOSED: 'bg-gray-100 text-gray-600',
}
const taskStatusLabel = {
  ASSIGNED: 'Ditugaskan', ACKNOWLEDGED: 'Diterima',
  IN_PROGRESS: 'Berlangsung', COMPLETED: 'Selesai',
  DELAYED: 'Terlambat', ESCALATED: 'Dieskalasi', FAILED: 'Gagal',
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Navbar -->
    <div class="bg-white border-b px-6 py-4">
      <div class="max-w-5xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <a href="/manager/dashboard" class="text-gray-400 hover:text-indigo-600 text-sm">← Dashboard</a>
          <span class="text-gray-300">|</span>
          <h1 class="font-bold text-gray-800">Instruksi Tugas</h1>
        </div>
        <button @click="openCreateModal"
          class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
          + Buat Instruksi
        </button>
      </div>
    </div>

    <div class="max-w-5xl mx-auto px-6 py-6">

      <!-- Flash -->
      <div v-if="flash.success"
        class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded text-sm">
        {{ flash.success }}
      </div>
      <div v-if="flash.error"
        class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded text-sm">
        {{ flash.error }}
      </div>

      <!-- タブ -->
      <div class="flex gap-1 bg-gray-100 rounded-lg p-1 mb-6 w-fit">
        <button @click="activeTab = 'draft'"
          :class="['px-4 py-2 rounded-md text-sm font-medium transition',
            activeTab === 'draft' ? 'bg-white shadow text-yellow-700' : 'text-gray-500']">
          ⏳ Menunggu Persetujuan
          <span v-if="draftOrders.length > 0"
            class="ml-1 bg-yellow-400 text-white text-xs px-1.5 py-0.5 rounded-full">
            {{ draftOrders.length }}
          </span>
        </button>
        <button @click="activeTab = 'active'"
          :class="['px-4 py-2 rounded-md text-sm font-medium transition',
            activeTab === 'active' ? 'bg-white shadow text-indigo-700' : 'text-gray-500']">
          🔄 Aktif
          <span v-if="activeOrders.length > 0"
            class="ml-1 bg-indigo-400 text-white text-xs px-1.5 py-0.5 rounded-full">
            {{ activeOrders.length }}
          </span>
        </button>
        <button @click="activeTab = 'done'"
          :class="['px-4 py-2 rounded-md text-sm font-medium transition',
            activeTab === 'done' ? 'bg-white shadow text-green-700' : 'text-gray-500']">
          ✅ Selesai / Dibatalkan
        </button>
      </div>

      <!-- ── DRAFT タブ ── -->
      <div v-if="activeTab === 'draft'">
        <div v-if="draftOrders.length === 0"
          class="bg-white rounded-xl border px-6 py-10 text-center text-gray-400 text-sm">
          Tidak ada instruksi yang menunggu persetujuan
        </div>
        <div v-for="order in draftOrders" :key="order.id"
          class="bg-white rounded-xl border mb-4 overflow-hidden">
          <div class="px-5 py-4 bg-yellow-50 border-b flex items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono text-gray-400">{{ order.order_no }}</span>
                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded font-medium">
                  Dari President
                </span>
                <span :class="['text-xs px-2 py-0.5 rounded font-medium', priorityColor[order.priority]]">
                  {{ order.priority }}
                </span>
              </div>
              <p class="font-semibold text-gray-800">{{ order.title }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ order.description }}</p>
              <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                <span>🏢 {{ divisionLabel[order.target_division] ?? order.target_division }}</span>
                <span v-if="order.due_date">📅 {{ order.due_date }}</span>
              </div>
            </div>
            <button @click="openApproveModal(order)"
              class="shrink-0 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 font-medium">
              ✅ Setujui & Tugaskan
            </button>
          </div>
        </div>
      </div>

      <!-- ── ACTIVE タブ ── -->
      <div v-if="activeTab === 'active'">
        <div v-if="activeOrders.length === 0"
          class="bg-white rounded-xl border px-6 py-10 text-center text-gray-400 text-sm">
          Tidak ada instruksi aktif
        </div>
        <div v-for="order in activeOrders" :key="order.id"
          class="bg-white rounded-xl border mb-4 overflow-hidden">
          <div class="px-5 py-4 border-b">
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-mono text-gray-400">{{ order.order_no }}</span>
                  <span :class="['text-xs px-2 py-0.5 rounded font-medium', statusColor[order.approval_status]]">
                    {{ statusLabel[order.approval_status] ?? order.approval_status }}
                  </span>
                  <span :class="['text-xs px-2 py-0.5 rounded font-medium', priorityColor[order.priority]]">
                    {{ order.priority }}
                  </span>
                </div>
                <p class="font-semibold text-gray-800">{{ order.title }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ order.description }}</p>
                <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                  <span>🏢 {{ divisionLabel[order.target_division] ?? order.target_division }}</span>
                  <span v-if="order.due_date">📅 {{ order.due_date }}</span>
                  <span>🕐 {{ order.created_at }}</span>
                </div>
              </div>
              <button @click="cancelOrder(order)"
                class="shrink-0 text-xs text-red-400 hover:text-red-600 border border-red-200 px-3 py-1.5 rounded">
                Batalkan
              </button>
            </div>
          </div>
          <!-- 担当者一覧 -->
          <div v-if="order.assignments?.length > 0" class="px-5 py-3 bg-gray-50">
            <p class="text-xs text-gray-400 mb-2">Staf yang ditugaskan:</p>
            <div class="flex flex-wrap gap-2">
              <div v-for="a in order.assignments" :key="a.id"
                class="flex items-center gap-1.5 bg-white border rounded-lg px-2 py-1">
                <span class="text-xs font-medium text-gray-700">{{ a.staff_name }}</span>
                <span class="text-xs text-gray-400">{{ a.role_type }}</span>
                <span class="text-xs bg-blue-50 text-blue-600 px-1.5 rounded">
                  {{ taskStatusLabel[a.task_status] ?? a.task_status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── DONE タブ ── -->
      <div v-if="activeTab === 'done'">
        <div v-if="doneOrders.length === 0"
          class="bg-white rounded-xl border px-6 py-10 text-center text-gray-400 text-sm">
          Belum ada instruksi yang selesai
        </div>
        <div v-for="order in doneOrders" :key="order.id"
          class="bg-white rounded-xl border mb-3 px-5 py-4">
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono text-gray-400">{{ order.order_no }}</span>
                <span :class="['text-xs px-2 py-0.5 rounded font-medium', statusColor[order.approval_status]]">
                  {{ statusLabel[order.approval_status] ?? order.approval_status }}
                </span>
              </div>
              <p class="font-semibold text-gray-800 text-sm">{{ order.title }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ order.created_at }}</p>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ── 承認モーダル ── -->
    <div v-if="approveModal"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="px-6 py-5 border-b">
          <h3 class="font-bold text-gray-800 text-lg">✅ Setujui & Tugaskan Instruksi</h3>
          <p class="text-sm text-gray-500 mt-1">{{ approveTarget?.title }}</p>
          <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ approveTarget?.order_no }}</p>
        </div>
        <div class="px-6 py-5">
          <p class="text-sm font-medium text-gray-700 mb-3">
            Pilih staf yang akan ditugaskan:
          </p>
          <div v-if="availableStaff.length === 0"
            class="text-sm text-gray-400 text-center py-4">
            Tidak ada staf yang tersedia saat ini
          </div>
          <div v-else class="space-y-2 max-h-60 overflow-y-auto">
            <label v-for="s in availableStaff" :key="s.user_id"
              class="flex items-center gap-3 border rounded-lg px-3 py-2 cursor-pointer hover:bg-indigo-50"
              :class="selectedStaff.includes(s.user_id) ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200'">
              <input type="checkbox"
                :value="s.user_id"
                :checked="selectedStaff.includes(s.user_id)"
                @change="toggleStaff(s.user_id)"
                class="accent-indigo-600">
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">{{ s.name }}</p>
                <p class="text-xs text-gray-400">
                  {{ s.department_code }} · タスク数: {{ s.active_task_count }}
                </p>
              </div>
            </label>
          </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-end gap-3">
          <button @click="closeApproveModal"
            class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">
            Batal
          </button>
          <button @click="submitApprove"
            :disabled="selectedStaff.length === 0"
            :class="['px-5 py-2 text-sm font-medium rounded-lg transition',
              selectedStaff.length === 0
                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                : 'bg-indigo-600 text-white hover:bg-indigo-700']">
            ✅ Setujui & Tugaskan
          </button>
        </div>
      </div>
    </div>

    <!-- ── 新規作成モーダル ── -->
    <div v-if="createModal"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-y-auto max-h-screen">
        <div class="px-6 py-5 border-b">
          <h3 class="font-bold text-gray-800 text-lg">+ Buat Instruksi Baru</h3>
        </div>
        <div class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Instruksi *</label>
            <input v-model="form.title" type="text" placeholder="Judul singkat instruksi"
              class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi *</label>
            <textarea v-model="form.description" rows="4" placeholder="Detail instruksi yang harus dilaksanakan..."
              class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none">
            </textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Divisi Target</label>
              <select v-model="form.target_division"
                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none">
                <option value="INVESTIGATION">Investigasi</option>
                <option value="ADMIN">Admin</option>
                <option value="STRATEGY">Strategi</option>
                <option value="AI_DEV">AI Dev</option>
                <option value="MARKETING">Marketing</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
              <select v-model="form.priority"
                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none">
                <option value="LOW">Rendah</option>
                <option value="NORMAL">Normal</option>
                <option value="HIGH">Tinggi</option>
                <option value="URGENT">Mendesak</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Batas Waktu</label>
            <input v-model="form.due_date" type="date"
              class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Pilih Staf *
              <span class="text-xs text-gray-400 font-normal ml-1">（Hanya staf yang tersedia ditampilkan）</span>
            </label>
            <div v-if="availableStaff.length === 0"
              class="text-sm text-gray-400 text-center py-4 border rounded-lg">
              Tidak ada staf yang tersedia
            </div>
            <div v-else class="space-y-2 max-h-48 overflow-y-auto border rounded-lg p-2">
              <label v-for="s in availableStaff" :key="s.user_id"
                class="flex items-center gap-3 rounded-lg px-3 py-2 cursor-pointer hover:bg-indigo-50"
                :class="form.assignee_ids.includes(s.user_id) ? 'bg-indigo-50' : ''">
                <input type="checkbox"
                  :value="s.user_id"
                  :checked="form.assignee_ids.includes(s.user_id)"
                  @change="toggleFormStaff(s.user_id)"
                  class="accent-indigo-600">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-800">{{ s.name }}</p>
                  <p class="text-xs text-gray-400">{{ s.department_code }} · タスク数: {{ s.active_task_count }}</p>
                </div>
              </label>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-end gap-3">
          <button @click="createModal = false"
            class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">
            Batal
          </button>
          <button @click="submitCreate"
            class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            Buat Instruksi
          </button>
        </div>
      </div>
    </div>

  </div>
</template>