<script setup>
/**
 * スーパー管理者 エクスポート画面
 * 配置先: resources/js/Pages/SuperAdmin/Export.vue
 */
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios'

const form = ref({
    export_type: 'ai_logs',
    date_from:   '',
    date_to:     '',
})

const downloading = ref(false)
const errorMsg    = ref('')

const exportTypes = [
    { value: 'ai_logs',          label: '🤖 AI活動ログ' },
    { value: 'ai_chat_logs',     label: '💬 AIチャットログ' },
    { value: 'staff_logs',       label: '👤 スタッフ操作ログ' },
    { value: 'data_access_logs', label: '🔍 個人データアクセスログ' },
    { value: 'ai_transfer_logs', label: '🌐 AIデータ送信ログ（越境移転）' },
    { value: 'audit_logs',       label: '📋 監査ログ（全業務）' },
    { value: 'consent_records',  label: '✅ 同意記録' },
]

async function download() {
    downloading.value = true
    errorMsg.value    = ''

    try {
        const response = await axios.post(
            route('super-admin.export.download'),
            form.value,
            { responseType: 'blob' }
        )

        // ブラウザでダウンロード
        const url      = window.URL.createObjectURL(new Blob([response.data]))
        const link     = document.createElement('a')
        const filename = `${form.value.export_type}_${new Date().toISOString().slice(0,10)}.csv`
        link.href      = url
        link.setAttribute('download', filename)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)

    } catch (e) {
        errorMsg.value = 'ダウンロードに失敗しました。もう一度試してください。'
    } finally {
        downloading.value = false
    }
}
</script>

<template>
    <Head title="スーパー管理者 - エクスポート" />

    <div class="min-h-screen bg-gray-50 p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-2">📥 ログエクスポート</h1>
        <p class="text-sm text-gray-500 mb-6">
            エクスポートファイルには個人識別情報（NIK・電話番号等）は含まれません。
            エクスポート操作は監査ログに記録されます。
        </p>

        <div class="bg-white rounded-xl shadow p-6 max-w-lg">

            <!-- エクスポート種別 -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    エクスポート対象
                </label>
                <div class="space-y-2">
                    <label v-for="type in exportTypes" :key="type.value"
                           class="flex items-center gap-2 cursor-pointer">
                        <input type="radio"
                               v-model="form.export_type"
                               :value="type.value"
                               class="accent-blue-600">
                        <span class="text-sm text-gray-700">{{ type.label }}</span>
                    </label>
                </div>
            </div>

            <!-- 日付範囲 -->
            <div class="mb-5 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">開始日</label>
                    <input v-model="form.date_from" type="date"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">終了日</label>
                    <input v-model="form.date_to" type="date"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>

            <!-- エラー -->
            <p v-if="errorMsg" class="text-red-600 text-sm mb-3">{{ errorMsg }}</p>

            <!-- ダウンロードボタン -->
            <button @click="download"
                    :disabled="downloading"
                    class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium
                           hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                {{ downloading ? 'ダウンロード中...' : '📥 CSVをダウンロード' }}
            </button>
        </div>
    </div>
</template>
