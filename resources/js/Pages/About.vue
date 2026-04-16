<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t, locale } = useI18n()

const scrolled   = ref(false)
const handleScroll = () => { scrolled.value = window.scrollY > 50 }
onMounted(()  => window.addEventListener('scroll', handleScroll))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))

// 会社沿革データ（4言語対応）
const historyItems = [
    { year: '2005',            plan: false,
      id: { title: 'Berdiri sebagai T-POINT (Tokyo)', desc: 'Memulai layanan investigasi; proyek resort golf Hiroshima.' },
      ja: { title: '株式会社T-POINT 設立（日本・東京）', desc: '調査支援事業を開始。広島県でゴルフリゾート開発に着手。' },
      ko: { title: 'T-POINT 설립 (도쿄)', desc: '조사업무 창업; 히로시마 골프 리조트 착수.' },
      en: { title: 'Incorporated as T-POINT (Tokyo, Japan)', desc: 'Investigation support launched; Hiroshima golf resort project initiated.' },
    },
    { year: '2009–2014',       plan: false,
      id: { title: 'Fondasi Operasi & Integritas Data', desc: 'Standarisasi pemeriksaan latar & penyimpanan bukti; fondasi HRI.' },
      ja: { title: '調査オペレーション／データ整合基盤の確立', desc: '在籍・学歴・資格等の調査を標準化し、証跡保全を整備。HRIの基盤を構築。' },
      ko: { title: '운영 및 데이터 정합 기반', desc: '백그라운드 체크 표준화와 증적 보존; HRI 기반 구축.' },
      en: { title: 'Ops & Data Integrity Foundation', desc: 'Standardized background checks and evidence retention; groundwork for HRI.' },
    },
    { year: '2015',            plan: false,
      id: { title: 'Ganti nama menjadi Community Delivery', desc: 'Pengantaran berorientasi komunitas; interior/fit-out; pengembangan perumahan.' },
      ja: { title: 'コミュティーデリバリー株式会社へ社名変更', desc: '地域密着デリバリー、内装・インテリア工事、住宅開発を開始。' },
      ko: { title: 'Community Delivery로 사명 변경', desc: '커뮤니티 중심 딜리버리; 인테리어/피트아웃; 주택 개발.' },
      en: { title: 'Renamed to Community Delivery Co., Ltd.', desc: 'Community-centric delivery; interior/fit-out; housing development.' },
    },
    { year: '2016',            plan: false,
      id: { title: 'Pengembangan TI in-house', desc: 'Bangun cloud & aplikasi; integrasi digital operasi.' },
      ja: { title: 'IT開発の本格化', desc: 'クラウド基盤と業務アプリを内製化し、運用をデジタル統合。' },
      ko: { title: '사내 IT 개발 본격화', desc: '클라우드 및 업무 앱 내재화; 운영의 디지털 통합.' },
      en: { title: 'In-house IT development', desc: 'Built cloud base & business apps; digital integration for ops.' },
    },
    { year: '2018',            plan: false,
      id: { title: 'Ganti nama menjadi NK3', desc: 'Pilar: Investigasi × Sertifikasi × Teknologi × Properti.' },
      ja: { title: 'NK3株式会社へ社名変更', desc: '調査×認証×テクノロジー×不動産を事業軸に。' },
      ko: { title: 'NK3로 사명 변경', desc: '축: 조사 × 인증 × 테크놀로지 × 부동산.' },
      en: { title: 'Renamed to NK3 Co., Ltd.', desc: 'Pillars: Investigation × Certification × Technology × Real Estate.' },
    },
    { year: '2019',            plan: false,
      id: { title: 'Program TI × Properti; lelang mobil bekas B2B', desc: 'Uji DX properti; lelang B2B lintas negara (JP/KR/BG/MN/AZ).' },
      ja: { title: 'IT×Real Estate プログラム／BtoB中古自動車オークション開始', desc: '不動産DXの実証、越境B2Bオークションを展開（JP/KR/BG/MN/AZ）。' },
      ko: { title: 'IT × Real Estate 프로그램; B2B 중고차 경매', desc: '부동산 DX 실증; 크로스보더 B2B 경매 개시 (JP/KR/BG/MN/AZ).' },
      en: { title: 'IT × Real Estate program; B2B used-car auctions', desc: 'DX pilots in real estate; launched cross-border B2B auctions (JP/KR/BG/MN/AZ).' },
    },
    { year: '2020',            plan: false,
      id: { title: 'Persiapan ekspansi (fokus Indonesia)', desc: 'Analisis niaga, perlindungan data & skema sertifikasi.' },
      ja: { title: '海外展開準備（インドネシア法制調査）', desc: '商取引・個人情報保護・認証制度を分析。' },
      ko: { title: '해외 전개 준비 (인도네시아 중심)', desc: '상거래·개인정보보호·인증 제도 분석.' },
      en: { title: 'Overseas readiness (Indonesia focus)', desc: 'Analyzed commercial, data protection & certification landscape.' },
    },
    { year: '2021',            plan: false,
      id: { title: 'Pembangunan kapabilitas ASEAN (rujukan ISO/IEC 17065)', desc: 'Desain proses evaluasi/keluhan & siap audit.' },
      ja: { title: 'ASEAN展開体制の整備（ISO/IEC 17065参照）', desc: '評価・苦情処理・監査対応の運用設計。' },
      ko: { title: 'ASEAN 역량 구축 (ISO/IEC 17065 참조 운영)', desc: '평가·불만처리·감사 대응 프로세스 설계.' },
      en: { title: 'ASEAN capability build (ISO/IEC 17065 referenced ops)', desc: 'Designed evaluation, complaints & audit-ready processes.' },
    },
    { year: '2022',            plan: false,
      id: { title: 'NK3 KOREA (Seoul) & NK3 BULGARIA (Sofia)', desc: 'Entitas lelang B2B; perluas jaringan lintas negara.' },
      ja: { title: 'NK3 KOREA（ソウル）／NK3 BULGARIA（ソフィア）設立', desc: 'BtoB中古自動車オークション関連法人。' },
      ko: { title: 'NK3 KOREA(서울) & NK3 BULGARIA(소피아)', desc: 'B2B 경매 법인; 크로스보더 네트워크 확대.' },
      en: { title: 'NK3 KOREA (Seoul) & NK3 BULGARIA (Sofia)', desc: 'B2B auction entities; cross-border network expansion.' },
    },
    { year: '2023',            plan: false,
      id: { title: 'PT. NIKI KINDAICHI THREE INDONESIA (Jakarta)', desc: 'Hub ASEAN HRI; rencana resort golf di Indonesia.' },
      ja: { title: 'PT. NIKI KINDAICHI THREE INDONESIA 設立（ジャカルタ）', desc: 'ASEAN中核拠点（HRI認証・調査）、インドネシアでゴルフリゾート構想。' },
      ko: { title: 'PT. NIKI KINDAICHI THREE INDONESIA (자카르타)', desc: 'ASEAN HRI 허브; 인도네시아 골프 리조트 구상.' },
      en: { title: 'PT. NIKI KINDAICHI THREE INDONESIA (Jakarta)', desc: 'ASEAN hub for HRI certification & investigation; golf resort plan.' },
    },
    { year: '2023',            plan: false,
      id: { title: 'Peluncuran proyek HRI; Penunjukan Dukungan Perumahan Tokyo (No.53)', desc: 'Publikasi konsep/kriteria/API HRI; penetapan resmi Tokyo.' },
      ja: { title: 'HRIプロジェクト始動／東京都住宅支援事業者（第53号）', desc: 'HRIの概念・基準・APIを公開し、東京都指定を取得。' },
      ko: { title: 'HRI 프로젝트 시작; 도쿄 주거지원 사업자(53호)', desc: 'HRI 개념·기준·API 공개; 도쿄도 공식 지정.' },
      en: { title: 'HRI project launch; Tokyo Housing Support Provider (No.53)', desc: 'Concept/criteria/API published; official designation by Tokyo Metropolitan Gov.' },
    },
    { year: '2024',            plan: false,
      id: { title: 'Peluncuran modul sertifikasi HRI; layanan B2C estetika', desc: 'Modul cloud-native; masuk domain B2C.' },
      ja: { title: 'HRI認証モジュール展開／BtoC領域開始', desc: 'クラウドネイティブ認証モジュール、BtoCサービスを開始。' },
      ko: { title: 'HRI 인증 모듈 전개; B2C 미용 서비스', desc: '클라우드 네이티브 인증 모듈; B2C 영역 진출.' },
      en: { title: 'HRI certification modules rollout; B2C aesthetics service', desc: 'Cloud-native certification modules; B2C domain launch.' },
    },
    { year: '2025',            plan: false,
      id: { title: 'Integrity Economy; modul HR terhubung HRI', desc: 'Integrasi grup & tata kelola global terkonsolidasi.' },
      ja: { title: 'Integrity Economy構想／HRI連携の社員管理モジュール', desc: 'グループ統合と国際ガバナンスを確立。' },
      ko: { title: 'Integrity Economy; HRI 연계 HR 모듈', desc: '그룹 통합 및 글로벌 거버넌스 정비.' },
      en: { title: 'Integrity Economy; HR module linked to HRI', desc: 'Group integration & global governance consolidated.' },
    },
    { year: 'H1 2026',         plan: true,
      id: { title: 'Marketplace barang bekas "pasar＋" (rilis Indonesia)', desc: 'C2C berbasis skor kepercayaan dengan HRI-ID.' },
      ja: { title: '「pasar＋」中古品マーケット（インドネシア）', desc: 'HRI-IDと連携した信頼評価型C2C。' },
      ko: { title: '"pasar＋" 중고거래 마켓플레이스 (인도네시아)', desc: 'HRI-ID 연계 신뢰 스코어 기반 C2C.' },
      en: { title: '"pasar＋" used-goods marketplace (Indonesia)', desc: 'Trust-scored C2C with HRI-ID.' },
    },
    { year: 'Mid 2026',        plan: true,
      id: { title: 'Platform properti hunian & sewa (rilis Indonesia)', desc: 'Dikembangkan sejak 2023; integrasi data mutu & operasi.' },
      ja: { title: '住宅・賃貸PropTechプラットフォーム（インドネシア）', desc: '2023年から開発。施工品質・管理履歴データを統合。' },
      ko: { title: '주거·임대 PropTech 플랫폼 (인도네시아)', desc: '2023년부터 개발; 품질·운영 이력 데이터 통합.' },
      en: { title: 'Housing & Rental PropTech platform (Indonesia)', desc: 'Since 2023; integrates quality & ops history data.' },
    },
    { year: 'Future Plan',     plan: true,
      id: { title: 'HRI-Global Accreditation Board (sementara)', desc: 'Menuju standardisasi & saling pengakuan internasional.' },
      ja: { title: 'HRI-Global Accreditation Board（仮称）', desc: '国際標準化と相互承認を推進（ISO/IEC 17065参照）。' },
      ko: { title: 'HRI-Global Accreditation Board(가칭)', desc: '국제 표준화와 상호인정을 추진.' },
      en: { title: 'HRI-Global Accreditation Board (provisional)', desc: 'Toward international standardization & mutual recognition.' },
    },
]

const services = [
    { icon: '🔍', key: 's1' },
    { icon: '🤖', key: 's2' },
    { icon: '🛒', key: 's3' },
    { icon: '🌏', key: 's4' },
]
</script>

<template>
    <Head :title="t('about_seo.title')">
        <meta name="description" :content="t('about_seo.description')" />
    </Head>

    <!-- ===== NAVBAR ===== -->
    <nav :class="['fixed top-0 inset-x-0 z-50 transition-all duration-300',
                  scrolled ? 'bg-white shadow-md border-b border-gray-200' : 'bg-white/95 backdrop-blur shadow-sm']">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between h-16">
            <Link href="/"><img src="/images/logo.png" alt="HRI" class="h-10 w-auto" /></Link>

            <div class="hidden md:flex items-center gap-5">
                <Link href="/"       class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">{{ t('nav.for_individual') }}</Link>
                <Link href="/company" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">{{ t('nav.for_company') }}</Link>
                <Link href="/job"    class="text-sm font-medium text-gray-700 hover:text-blue-600 transition">{{ t('nav.search_job') }}</Link>
                <div class="[&_button]:!text-gray-700 [&_button]:!border-gray-300 [&_button:hover]:!bg-gray-50">
                    <LanguageSwitcher />
                </div>
                <Link href="/login"
                      class="ml-1 px-4 py-2 rounded-full text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 transition">
                    {{ t('nav.login') }}
                </Link>
            </div>

            <!-- モバイルメニューボタン -->
            <button class="md:hidden p-2 rounded text-gray-700" @click="scrolled = !scrolled">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </nav>

    <div class="pt-16 bg-white text-slate-900">

        <!-- ===== HERO ===== -->
        <section class="bg-gradient-to-b from-slate-50 to-white border-b border-gray-100 py-14 text-center px-4">
            <div class="max-w-2xl mx-auto">
                <div class="inline-flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-full px-4 py-1.5 text-sm text-blue-700 font-medium mb-5">
                    🏢 About Us
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight mb-3">
                    {{ t('about_hero.title') }}
                </h1>
                <p class="text-slate-500 font-semibold mb-1">
                    <strong>{{ t('about_hero.subtitle') }}</strong> —
                    {{ t('about_hero.brand') }} <strong>{{ t('about_hero.brand_name') }}</strong>
                </p>
                <p class="text-slate-400 text-sm">
                    {{ t('about_hero.updated') }} <strong>{{ t('about_hero.updated_date') }}</strong>
                </p>
            </div>
        </section>

        <div class="max-w-4xl mx-auto px-4 py-10 space-y-8">

            <!-- ===== 基本情報 ===== -->
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-gray-200">
                    <h2 class="text-xl font-black text-slate-900">{{ t('about_basic.title') }}</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="row in [
                        { label: t('about_basic.company_name_label'), value: t('about_basic.company_name_value') },
                        { label: t('about_basic.brand_label'),        value: t('about_basic.brand_value') },
                        { label: t('about_basic.founded_label'),      value: t('about_basic.founded_value') },
                        { label: t('about_basic.director_label'),     value: t('about_basic.director_value') },
                        { label: t('about_basic.address_label'),      value: t('about_basic.address_value') },
                    ]" :key="row.label"
                         class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4 px-6 py-4">
                        <div class="sm:w-44 text-sm font-semibold text-slate-500 flex-shrink-0">{{ row.label }}</div>
                        <div class="text-sm text-slate-800 font-medium">{{ row.value }}</div>
                    </div>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-gray-100">
                    <p class="text-xs text-slate-400">{{ t('about_basic.note') }}</p>
                </div>
            </section>

            <!-- ===== 登録・許認可 ===== -->
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-gray-200">
                    <h2 class="text-xl font-black text-slate-900">{{ t('about_license.title') }}</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="row in [
                        { label: t('about_license.nib_label'),  value: t('about_license.nib_value') },
                        { label: t('about_license.npwp_label'), value: t('about_license.npwp_value') },
                        { label: t('about_license.oss_label'),  value: t('about_license.oss_value') },
                        { label: t('about_license.pse_label'),  value: t('about_license.pse_value') },
                    ]" :key="row.label"
                         class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4 px-6 py-4">
                        <div class="sm:w-44 text-sm font-semibold text-slate-500 flex-shrink-0">{{ row.label }}</div>
                        <div class="text-sm text-slate-800 font-mono font-medium">{{ row.value }}</div>
                    </div>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-gray-100">
                    <p class="text-xs text-slate-400">{{ t('about_license.note') }}</p>
                </div>
            </section>

            <!-- ===== 連絡先・営業時間 ===== -->
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-gray-200">
                    <h2 class="text-xl font-black text-slate-900">{{ t('about_contact.title') }}</h2>
                </div>
                <div class="grid sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                    <div class="px-6 py-5">
                        <h3 class="font-bold text-slate-800 mb-3 text-sm">{{ t('about_contact.window_title') }}</h3>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>
                                <span class="font-semibold text-slate-500">{{ t('about_contact.email_general') }}：</span>
                                <a href="mailto:info@hri-check.com" class="text-blue-600 hover:underline">info@hri-check.com</a>
                            </li>
                            <li>
                                <span class="font-semibold text-slate-500">{{ t('about_contact.email_support') }}：</span>
                                <a href="mailto:support@hri-check.com" class="text-blue-600 hover:underline">support@hri-check.com</a>
                            </li>
                            <li>
                                <span class="font-semibold text-slate-500">{{ t('about_contact.whatsapp') }}：</span>
                                +62-852-8588-7186
                            </li>
                        </ul>
                    </div>
                    <div class="px-6 py-5">
                        <h3 class="font-bold text-slate-800 mb-3 text-sm">{{ t('about_contact.hours_title') }}</h3>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>{{ t('about_contact.hours_weekday') }}</li>
                            <li class="text-slate-400">{{ t('about_contact.hours_closed') }}</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- ===== データポリシー ===== -->
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-gray-200">
                    <h2 class="text-xl font-black text-slate-900">{{ t('about_data.title') }}</h2>
                </div>
                <ul class="px-6 py-5 space-y-3">
                    <li v-for="(p, i) in ['p1','p2','p3']" :key="i"
                        class="flex gap-3 text-sm text-slate-700 leading-7">
                        <span class="text-blue-500 font-bold flex-shrink-0 mt-0.5">•</span>
                        <span>{{ t(`about_data.${p}`) }}</span>
                    </li>
                </ul>
            </section>

            <!-- ===== 主要サービス ===== -->
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-gray-200">
                    <h2 class="text-xl font-black text-slate-900">{{ t('about_services.title') }}</h2>
                </div>
                <div class="grid sm:grid-cols-2 gap-4 p-6">
                    <div v-for="s in services" :key="s.key"
                         class="flex gap-3 bg-slate-50 rounded-xl p-4 border border-gray-100">
                        <div class="text-2xl flex-shrink-0">{{ s.icon }}</div>
                        <div>
                            <div class="font-bold text-slate-800 text-sm mb-1">{{ t(`about_services.${s.key}_title`) }}</div>
                            <div class="text-slate-500 text-xs leading-6">{{ t(`about_services.${s.key}_desc`) }}</div>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-6 text-center">
                    <Link href="/contact"
                          class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-full transition">
                        {{ t('about_services.cta') }}
                    </Link>
                </div>
            </section>

            <!-- ===== 会社沿革 ===== -->
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-gray-200">
                    <h2 class="text-xl font-black text-slate-900">{{ t('about_history.title') }}</h2>
                </div>
                <div class="px-6 py-5">
                    <p class="text-xs text-slate-400 mb-4">{{ t('about_history.lang_note') }}</p>

                    <!-- タイムライン -->
                    <ol class="relative border-l-2 border-gray-200 space-y-6 ml-3">
                        <li v-for="(item, i) in historyItems" :key="i"
                            class="ml-6">
                            <span class="absolute -left-[9px] flex items-center justify-center w-4 h-4 rounded-full bg-blue-600 ring-4 ring-white mt-1.5"></span>

                            <div :class="['text-sm font-black mb-1', item.plan ? 'text-blue-500' : 'text-slate-400']">
                                {{ item.year }}
                                <span v-if="item.plan" class="ml-2 text-xs font-bold text-blue-400">
                                    {{ t('about_history.plan_suffix') }}
                                </span>
                            </div>

                            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                                <div class="font-bold text-slate-900 text-sm mb-1">
                                    {{ item[locale] ? item[locale].title : item.en.title }}
                                </div>
                                <div class="text-slate-500 text-xs leading-6">
                                    {{ item[locale] ? item[locale].desc : item.en.desc }}
                                </div>
                            </div>
                        </li>
                    </ol>

                    <div class="text-center mt-8">
                        <a href="#" class="text-sm text-blue-600 hover:underline">
                            {{ t('about_history.back_to_top') }}
                        </a>
                    </div>
                </div>
            </section>

        </div>

        <!-- ===== FOOTER ===== -->
        <footer class="bg-gray-900 text-gray-400 py-12 mt-10">
            <div class="max-w-6xl mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-start gap-8">
                    <div>
                        <Link href="/"><img src="/images/logo.png" alt="HRI" class="h-8 w-auto mb-3 opacity-80" /></Link>
                        <p class="text-sm max-w-xs leading-relaxed">{{ t('footer.desc') }}</p>
                    </div>
                    <div class="flex gap-12">
                        <div>
                            <div class="text-white font-semibold mb-3 text-sm">{{ t('footer.nav_title') }}</div>
                            <div class="space-y-2 text-sm">
                                <Link href="/"        class="block hover:text-white transition">{{ t('nav.for_individual') }}</Link>
                                <Link href="/company" class="block hover:text-white transition">{{ t('nav.for_company') }}</Link>
                                <Link href="/job"     class="block hover:text-white transition">{{ t('nav.search_job') }}</Link>
                                <Link href="/login"   class="block hover:text-white transition">{{ t('nav.login') }}</Link>
                            </div>
                        </div>
                        <div>
                            <div class="text-white font-semibold mb-3 text-sm">{{ t('footer.legal_title') }}</div>
                            <div class="space-y-2 text-sm">
                                <a href="https://hri-check.com/privacy-applicant/" target="_blank"
                                   class="block hover:text-white transition">{{ t('footer.privacy') }}</a>
                                <a href="https://hri-check.com/important-policies/" target="_blank"
                                   class="block hover:text-white transition">{{ t('footer.policy') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-10 pt-6 border-t border-gray-800 text-xs text-center">
                    © {{ new Date().getFullYear() }} HRI Indonesia. {{ t('footer.copyright') }}
                </div>
            </div>
        </footer>
    </div>
</template>