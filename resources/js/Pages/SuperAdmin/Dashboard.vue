<script setup>
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    aiStats:    Object,
    caseStats:  Object,
    staffStats: Object,
    pdpAlerts:  Object,
    costTrend:  Array,
})

// case_statusの日本語ラベル
const statusLabels = {
    draft:                  '下書き',
    under_investigation:    '調査中',
    ai_review_pending:      'AI審査中',
    returned_internal:      '差し戻し中',
    human_review_required:  '人間確認中',
    conditionally_verified: '条件付き認証済み',
    verified:               '認証済み',
    rejected:               '却下',
}
</script>

<template>
    <Head title="スーパー管理者 - ダッシュボード" />

    <div class="min-h-screen bg-gray-50">

        <!-- ヘッダー -->
        <header class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-red-400 font-bold text-sm bg-red-900 px-2 py-0.5 rounded">
                    SUPER ADMIN
                </span>
                <h1 class="text-lg font-semibold">HRI System 管理画面</h1>
            </div>
            <nav class="flex gap-4 text-sm">
                <Link :href="route('super-admin.dashboard')"       class="hover:text-white text-gray-300">ダッシュボード</Link>
                <Link :href="route('super-admin.ai-logs')"         class="hover:text-white text-gray-300">AIログ</Link>
                <Link :href="route('super-admin.staff-logs')"      class="hover:text-white text-gray-300">スタッフログ</Link>
                <Link :href="route('super-admin.data-access-logs')" class="hover:text-white text-gray-300">アクセスログ</Link>
                <Link :href="route('super-admin.consent-records')" class="hover:text-white text-gray-300">同意管理</Link>
                <Link :href="route('super-admin.deletion-requests')" class="hover:text-white text-gray-300">削除申請</Link>
                <Link :href="route('super-admin.users-all')"       class="hover:text-white text-gray-300">ユーザー管理</Link>
                <Link :href="route('super-admin.export')"          class="hover:text-white text-gray-300">エクスポート</Link>
            </nav>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-8">

            <!-- PDP法アラート -->
            <div v-if="pdpAlerts.unresolved_deletions > 0"
                 class="mb-6 bg-yellow-50 border border-yellow-300 rounded-lg p-4 flex items-center gap-3">
                <span class="text-yellow-600 text-xl">⚠️</span>
                <span class="text-yellow-800 font-medium">
                    未対応のデータ削除申請が {{ pdpAlerts.unresolved_deletions }} 件あります。
                </span>
                <Link :href="route('super-admin.deletion-requests')"
                      class="ml-auto text-sm text-yellow-700 underline">
                    確認する →
                </Link>
            </div>

            <!-- KPIカード -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-xs text-gray-500 mb-1">今月のAI使用コスト</p>
                    <p class="text-2xl font-bold text-gray-800">
                        Rp {{ aiStats.totalCostIdr }}
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-xs text-gray-500 mb-1">今月のAI処理件数</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ aiStats.totalCount.toLocaleString() }} 件
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-xs text-gray-500 mb-1">平均応答時間</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ aiStats.avgLatencyMs }} ms
                    </p>
                </div>
                <div class="bg-white rounded-xl shadow p-5">
                    <p class="text-xs text-gray-500 mb-1">AI成功率</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ aiStats.successRate }} %
                    </p>
                </div>
            </div>

            <!-- メインコンテンツ -->
            <div class="grid grid-cols-3 gap-6">

                <!-- 案件ステータス -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        📋 案件ステータス
                    </h2>
                    <ul class="space-y-2">
                        <li v-for="(count, status) in caseStats" :key="status"
                            class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ statusLabels[status] ?? status }}</span>
                            <span class="font-bold text-gray-800">{{ count }} 件</span>
                        </li>
                    </ul>
                </div>

                <!-- スタッフ活動（本日） -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="font-semibold text-gray-700 mb-4">📊 スタッフ活動（本日）</h2>
                    <ul class="space-y-3 text-sm">
                        <li class="flex justify-between">
                            <span class="text-gray-600">調査部 操作件数</span>
                            <span class="font-bold">{{ staffStats.investigator }} 件</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-gray-600">審査管理部 操作件数</span>
                            <span class="font-bold">{{ staffStats.admin }} 件</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-gray-600">AIチャット利用回数</span>
                            <span class="font-bold">{{ staffStats.chatCount }} 回</span>
                        </li>
                    </ul>
                </div>

                <!-- コスト推移グラフ（シンプル版） -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="font-semibold text-gray-700 mb-4">📈 直近7日間のコスト</h2>
                    <div class="space-y-2">
                        <div v-for="item in costTrend" :key="item.date" class="flex items-center gap-2 text-xs">
                            <span class="text-gray-500 w-20 shrink-0">{{ item.date }}</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full"
                                     :style="{ width: `${Math.min(item.daily_cost / 50000 * 100, 100)}%` }">
                                </div>
                            </div>
                            <span class="text-gray-700 w-20 text-right">
                                Rp {{ Number(item.daily_cost ?? 0).toLocaleString() }}
                            </span>
                        </div>
                        <p v-if="!costTrend?.length" class="text-gray-400 text-sm text-center py-4">
                            データなし
                        </p>
                    </div>
                </div>
            </div>

            <!-- クイックリンク -->
            <div class="mt-8 grid grid-cols-4 gap-4">
                <Link v-for="link in [
                    { label: 'AIログ',         icon: '🤖', route: 'super-admin.ai-logs' },
                    { label: 'スタッフログ',   icon: '👤', route: 'super-admin.staff-logs' },
                    { label: 'アクセスログ',   icon: '🔍', route: 'super-admin.data-access-logs' },
                    { label: 'エクスポート',   icon: '📥', route: 'super-admin.export' },
                ]"
                :key="link.route"
                :href="route(link.route)"
                class="bg-white rounded-xl shadow p-4 text-center hover:bg-blue-50 transition">
                    <div class="text-2xl mb-1">{{ link.icon }}</div>
                    <div class="text-sm font-medium text-gray-700">{{ link.label }}</div>
                </Link>
            </div>

        </main>
    </div>
</template>
