import { createI18n } from 'vue-i18n'

// ===== id =====
import id_common  from './locales/id/common.js'
import id_welcome from './locales/id/welcome.js'
import id_company from './locales/id/company.js'
import id_jobs    from './locales/id/jobs.js'

// ===== ja =====
import ja_common  from './locales/ja/common.js'
import ja_welcome from './locales/ja/welcome.js'
import ja_company from './locales/ja/company.js'
import ja_jobs    from './locales/ja/jobs.js'

// ===== ko =====
import ko_common  from './locales/ko/common.js'
import ko_welcome from './locales/ko/welcome.js'
import ko_company from './locales/ko/company.js'
import ko_jobs    from './locales/ko/jobs.js'

// ===== en =====
import en_common  from './locales/en/common.js'
import en_welcome from './locales/en/welcome.js'
import en_company from './locales/en/company.js'
import en_jobs    from './locales/en/jobs.js'

// 利用可能な言語一覧（LanguageSwitcher.vue で使用）
export const availableLocales = [
    { code: 'id', label: 'Indonesia',  flag: '🇮🇩' },
    { code: 'ja', label: '日本語',      flag: '🇯🇵' },
    { code: 'ko', label: '한국어',      flag: '🇰🇷' },
    { code: 'en', label: 'English',    flag: '🇬🇧' },
]

export const i18n = createI18n({
    legacy: false,
    locale: localStorage.getItem('hri_locale') || 'id',
    fallbackLocale: 'id',
    messages: {
        id: { ...id_common, ...id_welcome, ...id_company, ...id_jobs },
        ja: { ...ja_common, ...ja_welcome, ...ja_company, ...ja_jobs },
        ko: { ...ko_common, ...ko_welcome, ...ko_company, ...ko_jobs },
        en: { ...en_common, ...en_welcome, ...en_company, ...en_jobs },
    },
})

// 言語切替関数（LanguageSwitcher.vue で使用）
export const setLocale = (code) => {
    i18n.global.locale.value = code
    localStorage.setItem('hri_locale', code)
    document.documentElement.lang = code
}

export default i18n