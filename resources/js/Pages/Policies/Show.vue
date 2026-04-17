<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const props = defineProps({
  slug: String,
})

const { locale } = useI18n()
const userType = ref('individual')
const policyContent = ref({})
const loading = ref(true)

const policyMeta = {
  'terms':               { title: { id: 'Ketentuan Penggunaan HRI', ja: '利用規約', ko: '이용약관', en: 'Terms of Use' }, both: true },
  'privacy':             { title: { id: 'Kebijakan Privasi', ja: 'プライバシーポリシー', ko: '개인정보처리방침', en: 'Privacy Policy' }, both: true },
  'cookie':              { title: { id: 'Kebijakan Cookie & Analitik', ja: 'Cookie・アナリティクスポリシー', ko: 'Cookie 정책', en: 'Cookie & Analytics Policy' }, both: true },
  'resume-restrictions': { title: { id: 'Kebijakan Verified Resume', ja: 'Verified Resume利用制限', ko: 'Verified Resume 정책', en: 'Verified Resume Policy' }, both: true },
  'investigation':       { title: { id: 'Kebijakan Persetujuan Investigasi', ja: '調査・検証同意ポリシー', ko: '조사·검증 동의 정책', en: 'Investigation Consent Policy' }, both: false },
  'acceptable-use':      { title: { id: 'Kebijakan Penggunaan yang Diperbolehkan', ja: '許可される利用ポリシー', ko: '허용 이용 정책', en: 'Acceptable Use Policy' }, both: true },
  'data-responsibility': { title: { id: 'Kebijakan Tanggung Jawab Informasi', ja: '情報提供責任ポリシー', ko: '정보 제공 책임 정책', en: 'Data Responsibility Policy' }, both: true },
  'liability':           { title: { id: 'Kebijakan Pembatasan Tanggung Jawab', ja: '免責・責任制限ポリシー', ko: '면책 및 책임 제한 정책', en: 'Liability Limitation Policy' }, both: true },
  'dispute-resolution':  { title: { id: 'Kebijakan Penanganan Keluhan', ja: '苦情・紛争処理ポリシー', ko: '불만 및 분쟁 처리 정책', en: 'Dispute Resolution Policy' }, both: true },
  'anti-discrimination': { title: { id: 'Kebijakan Anti-Diskriminasi', ja: '差別防止・ハラスメント防止ポリシー', ko: '차별 방지 정책', en: 'Anti-Discrimination Policy' }, both: true },
  'language':            { title: { id: 'Kebijakan Bahasa', ja: '言語優先順位ポリシー', ko: '언어 우선순위 정책', en: 'Language Policy' }, both: true },
}

const meta = computed(() => policyMeta[props.slug] ?? { title: { id: 'Kebijakan', ja: 'ポリシー', ko: '정책', en: 'Policy' }, both: true })
const currentTitle = computed(() => meta.value.title[locale.value] ?? meta.value.title['id'])

// ロケール＋ユーザー種別でコンテンツキーを決定
// 例: 'ja_individual', 'id_company'
const contentKey = computed(() => {
  const type = (!meta.value.both) ? 'individual' : userType.value
  return `${locale.value}_${type}`
})

const currentContent = computed(() => {
  // 該当言語がなければ id（インドネシア語）にフォールバック
  return policyContent.value[contentKey.value]
    ?? policyContent.value[`id_${userType.value}`]
    ?? policyContent.value['id_individual']
    ?? '<p>Konten tidak ditemukan.</p>'
})

onMounted(async () => {
  // URLパラメータからユーザー種別を取得
  const params = new URLSearchParams(window.location.search)
  if (params.get('type') === 'company') userType.value = 'company'

  try {
    const mod = await import(`../../policies/${props.slug}.js`)
    policyContent.value = mod.default
  } catch (e) {
    console.error('Policy not found:', e)
  } finally {
    loading.value = false
  }
})

// ラベル（言語別）
const labels = computed(() => ({
  back:       { id: 'Daftar Kebijakan', ja: '規約一覧', ko: '약관 목록', en: 'Policy List' }[locale.value] ?? 'Daftar Kebijakan',
  individual: { id: 'Individu', ja: '個人会員', ko: '개인 회원', en: 'Individual' }[locale.value] ?? 'Individu',
  company:    { id: 'Perusahaan', ja: '企業会員', ko: '기업 회원', en: 'Company' }[locale.value] ?? 'Perusahaan',
  onlyIndiv:  { id: 'Khusus Individu', ja: '個人会員専用', ko: '개인 회원 전용', en: 'Individual Only' }[locale.value] ?? 'Khusus Individu',
  fallback:   { id: '', ja: '※ この言語版は準備中です。インドネシア語版を表示しています。', ko: '※ 이 언어 버전은 준비 중입니다. 인도네시아어 버전을 표시합니다.', en: '※ This language version is being prepared. Showing Indonesian version.' }[locale.value] ?? '',
}))

// フォールバック表示判定
const isFallback = computed(() => {
  const key = contentKey.value
  return locale.value !== 'id' && !policyContent.value[key]
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">

    <!-- ナビゲーションバー -->
    <div class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
      <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between gap-3">

        <!-- 戻るリンク -->
        <Link href="/policies" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          <span class="hidden sm:inline">{{ labels.back }}</span>
        </Link>

        <!-- 個人/企業タブ -->
        <div v-if="meta.both" class="inline-flex bg-gray-100 rounded-full p-1 gap-1">
          <button
            @click="userType = 'individual'"
            :class="[
              'px-4 py-1.5 rounded-full text-xs font-medium transition',
              userType === 'individual' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-700'
            ]"
          >
            👤 {{ labels.individual }}
          </button>
          <button
            @click="userType = 'company'"
            :class="[
              'px-4 py-1.5 rounded-full text-xs font-medium transition',
              userType === 'company' ? 'bg-white text-gray-900 shadow' : 'text-gray-500 hover:text-gray-700'
            ]"
          >
            🏢 {{ labels.company }}
          </button>
        </div>
        <span v-else class="text-xs text-blue-600 bg-blue-50 border border-blue-200 px-3 py-1 rounded-full shrink-0">
          {{ labels.onlyIndiv }}
        </span>

        <!-- 言語切替 -->
        <LanguageSwitcher :dark="false" />
      </div>
    </div>

    <!-- フォールバック注記 -->
    <div v-if="!loading && isFallback" class="bg-amber-50 border-b border-amber-200">
      <div class="max-w-4xl mx-auto px-4 py-2 text-xs text-amber-700">
        {{ labels.fallback }}
      </div>
    </div>

    <!-- コンテンツエリア -->
    <div class="max-w-4xl mx-auto px-4 py-8">

      <!-- ローディング -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="w-8 h-8 border-4 border-gray-200 border-t-[#0A1A3A] rounded-full animate-spin"></div>
      </div>

      <!-- HTMLコンテンツ -->
      <div v-else v-html="currentContent"></div>
    </div>
  </div>
</template>