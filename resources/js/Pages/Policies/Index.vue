<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { locale } = useI18n()
const userType = ref('individual')

const policies = [
  { slug: 'terms',               both: true,  individualOnly: false },
  { slug: 'privacy',             both: true,  individualOnly: false },
  { slug: 'cookie',              both: true,  individualOnly: false },
  { slug: 'resume-restrictions', both: true,  individualOnly: false },
  { slug: 'investigation',       both: false, individualOnly: true  },
  { slug: 'acceptable-use',      both: true,  individualOnly: false },
  { slug: 'data-responsibility',  both: false, individualOnly: true  },
  { slug: 'liability',           both: true,  individualOnly: false },
  { slug: 'dispute-resolution',  both: true,  individualOnly: false },
  { slug: 'anti-discrimination',  both: false, individualOnly: true  },
  { slug: 'language',            both: true,  individualOnly: false },
]

const meta = {
  terms:               { id: { title: 'Ketentuan Penggunaan HRI',                          desc: 'Syarat dan ketentuan penggunaan layanan HRI.' },                                ja: { title: '利用規約',                       desc: 'HRIサービスの利用規約。' },                              ko: { title: '이용약관',           desc: 'HRI 서비스 이용약관.' },                    en: { title: 'Terms of Use',                  desc: 'Terms and conditions for using HRI services.' } },
  privacy:             { id: { title: 'Kebijakan Privasi Anggota HRI',                     desc: 'Pengelolaan dan perlindungan data pribadi.' },                                  ja: { title: 'プライバシーポリシー',            desc: '個人データの管理と保護。' },                              ko: { title: '개인정보처리방침',   desc: '개인 데이터의 관리 및 보호.' },              en: { title: 'Privacy Policy',                desc: 'Management and protection of personal data.' } },
  cookie:              { id: { title: 'Kebijakan Cookie & Analitik',                       desc: 'Penggunaan cookie dan analitik.' },                                             ja: { title: 'Cookie・アナリティクスポリシー', desc: 'Cookieとアナリティクスの利用。' },                        ko: { title: 'Cookie 정책',        desc: 'Cookie 및 분석 이용.' },                    en: { title: 'Cookie & Analytics Policy',     desc: 'Use of cookies and analytics.' } },
  'resume-restrictions': { id: { title: 'Kebijakan Penggunaan & Pembatasan Verified Resume', desc: 'Aturan penggunaan dan visibilitas Verified Resume.' },                       ja: { title: 'Verified Resume利用制限',        desc: 'Verified Resumeの利用制限。' },                           ko: { title: 'Verified Resume 정책', desc: 'Verified Resume 이용 제한.' },             en: { title: 'Verified Resume Policy',        desc: 'Rules for using and displaying Verified Resume.' } },
  investigation:       { id: { title: 'Kebijakan Persetujuan Investigasi & Verifikasi',    desc: 'Persetujuan eksplisit terhadap proses investigasi dan verifikasi.' },          ja: { title: '調査・検証同意ポリシー',          desc: '調査・検証プロセスへの同意。' },                          ko: { title: '조사·검증 동의 정책', desc: '조사 및 검증 프로세스에 대한 동의.' },        en: { title: 'Investigation Consent Policy',  desc: 'Consent to the investigation and verification process.' } },
  'acceptable-use':    { id: { title: 'Kebijakan Penggunaan yang Diperbolehkan (AUP)',     desc: 'Aturan perilaku yang diperbolehkan.' },                                        ja: { title: '許可される利用ポリシー',          desc: '許可される行動ルール。' },                                ko: { title: '허용 이용 정책',      desc: '허용되는 행동 규칙.' },                     en: { title: 'Acceptable Use Policy (AUP)',   desc: 'Rules for permitted conduct.' } },
  'data-responsibility': { id: { title: 'Kebijakan Tanggung Jawab Penyediaan Informasi',  desc: 'Tanggung jawab pengguna atas keakuratan data.' },                              ja: { title: '情報提供責任ポリシー',            desc: 'データの正確性に関するユーザーの責任。' },                ko: { title: '정보 제공 책임 정책', desc: '데이터 정확성에 대한 사용자 책임.' },         en: { title: 'Data Responsibility Policy',    desc: 'User responsibility for data accuracy.' } },
  liability:           { id: { title: 'Kebijakan Penyangkalan & Pembatasan Tanggung Jawab', desc: 'Batasan tanggung jawab dan disclaimer.' },                                   ja: { title: '免責・責任制限ポリシー',          desc: '免責および責任の制限。' },                               ko: { title: '면책 및 책임 제한 정책', desc: '면책 및 책임 제한.' },                     en: { title: 'Disclaimer & Limitation of Liability', desc: 'Limitations of liability and disclaimers.' } },
  'dispute-resolution': { id: { title: 'Kebijakan Penanganan Keluhan & Pertanyaan',       desc: 'Prosedur pengaduan dan penyelesaian sengketa.' },                              ja: { title: '苦情・紛争処理ポリシー',          desc: '苦情申し立てと紛争解決の手順。' },                       ko: { title: '불만 및 분쟁 처리 정책', desc: '불만 제기 및 분쟁 해결 절차.' },           en: { title: 'Dispute Resolution Policy',     desc: 'Procedures for complaints and dispute resolution.' } },
  'anti-discrimination': { id: { title: 'Kebijakan Anti-Diskriminasi & Pencegahan Pelecehan', desc: 'Kebijakan anti-diskriminasi dan pencegahan pelecehan.' },                  ja: { title: '差別防止・ハラスメント防止ポリシー', desc: '差別防止とハラスメント防止の方針。' },                  ko: { title: '차별 방지 정책',      desc: '차별 방지 및 괴롭힘 방지 정책.' },          en: { title: 'Anti-Discrimination Policy',    desc: 'Policy on anti-discrimination and harassment prevention.' } },
  language:            { id: { title: 'Kebijakan Bahasa & Urutan Prioritas',               desc: 'Aturan prioritas versi bahasa.' },                                             ja: { title: '言語優先順位ポリシー',            desc: '言語バージョンの優先順位ルール。' },                      ko: { title: '언어 우선순위 정책',  desc: '언어 버전 우선순위 규칙.' },                en: { title: 'Language & Priority Policy',    desc: 'Rules for language version priority.' } },
}

const labels = computed(() => {
  const l = locale.value
  return {
    back:       { id: 'Kembali ke Beranda', ja: 'トップへ戻る',   ko: '홈으로',      en: 'Back to Home' }[l] ?? 'Kembali ke Beranda',
    pageTitle:  { id: 'Kebijakan Penting Pengguna', ja: '約款一覧', ko: '약관 목록',  en: 'Policy List' }[l] ?? 'Kebijakan Penting Pengguna',
    pageDesc:   { id: 'Dokumen resmi yang mengatur hak, kewajiban, dan perlindungan pengguna HRI', ja: 'HRIユーザーの権利・義務・保護を規定する公式文書', ko: 'HRI 사용자의 권리, 의무 및 보호를 규정하는 공식 문서', en: 'Official documents governing the rights, obligations, and protection of HRI users' }[l] ?? '',
    individual: { id: 'Individu',   ja: '個人会員', ko: '개인 회원', en: 'Individual' }[l] ?? 'Individu',
    company:    { id: 'Perusahaan', ja: '企業会員', ko: '기업 회원', en: 'Company'    }[l] ?? 'Perusahaan',
    onlyIndiv:  { id: 'Khusus Individu', ja: '個人会員専用', ko: '개인 회원 전용', en: 'Individual Only' }[l] ?? 'Khusus Individu',
    open:       { id: 'Buka',  ja: '開く', ko: '열기', en: 'Open' }[l] ?? 'Buka',
    copyright:  'PT. NIKI KINDAICHI THREE INDONESIA — © 2025',
  }
})

import { computed } from 'vue'

const getTitle = (slug) => meta[slug]?.[locale.value]?.title ?? meta[slug]?.['id']?.title ?? slug
const getDesc  = (slug) => meta[slug]?.[locale.value]?.desc  ?? meta[slug]?.['id']?.desc  ?? ''

const visiblePolicies = (p) => {
  if (p.individualOnly && userType.value === 'company') return false
  return true
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- ヘッダー -->
    <div class="bg-[#0A1A3A] text-white">
      <div class="max-w-4xl mx-auto px-4 py-10">
        <Link href="/" class="text-sm text-white/70 hover:text-white mb-4 inline-flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          {{ labels.back }}
        </Link>
        <h1 class="text-2xl font-bold mt-2">{{ labels.pageTitle }}</h1>
        <p class="text-white/80 mt-2 text-sm">{{ labels.pageDesc }}</p>

        <!-- 個人/企業切替 + 言語切替 -->
        <div class="mt-6 flex flex-wrap items-center gap-4">
          <div class="inline-flex bg-white/10 rounded-full p-1 gap-1">
            <button
              @click="userType = 'individual'"
              :class="['px-5 py-2 rounded-full text-sm font-medium transition', userType === 'individual' ? 'bg-white text-[#0A1A3A]' : 'text-white hover:bg-white/10']"
            >
              👤 {{ labels.individual }}
            </button>
            <button
              @click="userType = 'company'"
              :class="['px-5 py-2 rounded-full text-sm font-medium transition', userType === 'company' ? 'bg-white text-[#0A1A3A]' : 'text-white hover:bg-white/10']"
            >
              🏢 {{ labels.company }}
            </button>
          </div>

          <!-- 言語切替 -->
          <LanguageSwitcher :dark="true" />
        </div>
      </div>
    </div>

    <!-- 規約一覧 -->
    <div class="max-w-4xl mx-auto px-4 py-10">
      <div class="grid gap-4">
        <template v-for="p in policies" :key="p.slug">
          <div v-if="visiblePolicies(p)" class="bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h2 class="font-semibold text-gray-900 text-base">{{ getTitle(p.slug) }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ getDesc(p.slug) }}</p>
                <span v-if="p.individualOnly" class="mt-2 inline-block text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full">
                  {{ labels.onlyIndiv }}
                </span>
              </div>
              <Link
                :href="`/policies/${p.slug}?type=${userType}`"
                class="shrink-0 text-sm font-medium text-[#0A1A3A] hover:underline whitespace-nowrap"
              >
                {{ labels.open }} →
              </Link>
            </div>
          </div>
        </template>
      </div>

      <div class="mt-10 text-center text-xs text-gray-400">
        {{ labels.copyright }}
      </div>
    </div>
  </div>
</template>