<script setup>
/**
 * 共通ログテーブルコンポーネント
 * 全スーパー管理者ログ画面で使い回す。
 *
 * 配置先: resources/js/Components/SuperAdmin/LogTable.vue
 */
import { Link } from '@inertiajs/vue3'

defineProps({
    headers:    { type: Array,  required: true },
    rows:       { type: Array,  required: true },
    pagination: { type: Object, default: null },
})
</script>

<template>
    <div>
        <!-- テーブル -->
        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th v-for="header in headers" :key="header"
                            class="px-4 py-3 whitespace-nowrap">
                            {{ header }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="(row, i) in rows" :key="i"
                        class="hover:bg-gray-50 transition">
                        <td v-for="(cell, j) in row" :key="j"
                            class="px-4 py-3 text-gray-700 whitespace-nowrap max-w-xs truncate">
                            {{ cell ?? '—' }}
                        </td>
                    </tr>
                    <tr v-if="!rows.length">
                        <td :colspan="headers.length"
                            class="text-center py-10 text-gray-400">
                            データがありません
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ページネーション -->
        <div v-if="pagination" class="mt-4 flex items-center justify-between text-sm text-gray-600">
            <span>
                {{ pagination.from }}〜{{ pagination.to }} 件 /
                全 {{ pagination.total }} 件
            </span>
            <div class="flex gap-2">
                <Link v-if="pagination.prev_page_url"
                      :href="pagination.prev_page_url"
                      class="px-3 py-1 border rounded hover:bg-gray-100">
                    ← 前へ
                </Link>
                <Link v-if="pagination.next_page_url"
                      :href="pagination.next_page_url"
                      class="px-3 py-1 border rounded hover:bg-gray-100">
                    次へ →
                </Link>
            </div>
        </div>
    </div>
</template>
