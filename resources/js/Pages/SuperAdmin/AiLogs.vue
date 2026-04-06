<!-- ============================================================
     AiLogs.vue
     配置先: resources/js/Pages/SuperAdmin/AiLogs.vue
     ============================================================ -->
<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import LogTable from '@/Components/SuperAdmin/LogTable.vue'

const props = defineProps({
    logs:    Object,
    filters: Object,
})

const form = ref({ ...props.filters })

function applyFilter() {
    router.get(route('super-admin.ai-logs'), form.value, { preserveScroll: true })
}
function clearFilter() {
    form.value = {}
    router.get(route('super-admin.ai-logs'))
}

const headers = ['ID', '実行日時', '種別', '関連ID', 'AIモデル',
                 '合計トークン', 'コスト(IDR)', '応答(ms)', 'ステータス', '判定']

const rows = props.logs.data.map(r => [
    r.id, r.created_at, r.log_type, r.related_id, r.model_name,
    r.tokens_total?.toLocaleString(),
    r.estimated_cost_idr ? `Rp ${Number(r.estimated_cost_idr).toLocaleString()}` : '—',
    r.latency_ms, r.status, r.final_decision,
])
</script>

<template>
    <Head title="スーパー管理者 - AI活動ログ" />
    <div class="min-h-screen bg-gray-50 p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-6">🤖 AI活動ログ</h1>

        <!-- フィルタ -->
        <div class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-500 block mb-1">種別</label>
                <select v-model="form.log_type" class="border rounded px-2 py-1 text-sm">
                    <option value="">すべて</option>
                    <option value="scoring">scoring</option>
                    <option value="priority_analysis">priority_analysis</option>
                    <option value="chat_support">chat_support</option>
                    <option value="em_action">em_action</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">ステータス</label>
                <select v-model="form.status" class="border rounded px-2 py-1 text-sm">
                    <option value="">すべて</option>
                    <option value="success">success</option>
                    <option value="failed">failed</option>
                    <option value="timeout">timeout</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">開始日</label>
                <input v-model="form.date_from" type="date"
                       class="border rounded px-2 py-1 text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">終了日</label>
                <input v-model="form.date_to" type="date"
                       class="border rounded px-2 py-1 text-sm">
            </div>
            <button @click="applyFilter"
                    class="bg-blue-600 text-white px-4 py-1.5 rounded text-sm hover:bg-blue-700">
                絞り込み
            </button>
            <button @click="clearFilter"
                    class="border px-4 py-1.5 rounded text-sm hover:bg-gray-100">
                クリア
            </button>
        </div>

        <LogTable :headers="headers" :rows="rows" :pagination="logs" />
    </div>
</template>

<!-- ============================================================
     SEPARATOR - 以下は別ファイル
     ============================================================ -->

<!-- StaffLogs.vue は AiLogs.vue と同じ構造なので
     AiLogs.vue をコピーして以下だけ変更してください:

     1. ファイル名: StaffLogs.vue
     2. Head title: "スーパー管理者 - スタッフ操作ログ"
     3. h1: "👤 スタッフ操作ログ"
     4. router.get のルート: route('super-admin.staff-logs')
     5. headers: ['ID', '操作日時', 'スタッフID', 'ロール', 'アクション',
                  '対象モデル', '対象ID', '説明', 'IPアドレス', '案件番号']
     6. rows のマッピング: r.id, r.created_at, r.user_id, r.role_type,
                           r.action, r.target_type, r.target_id,
                           r.description, r.ip_address, r.case_no

     DataAccessLogs.vue / AuditLogs.vue / ConsentRecords.vue も
     同様の構造でコピー＆修正してください。
-->
