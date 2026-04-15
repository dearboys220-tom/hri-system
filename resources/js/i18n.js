import { createI18n } from 'vue-i18n'
import id from './locales/id.js'
import ja from './locales/ja.js'
import ko from './locales/ko.js'
import en from './locales/en.js'

const savedLocale = localStorage.getItem('hri_locale') ?? 'id'

export const i18n = createI18n({
    legacy: false,
    locale: savedLocale,
    fallbackLocale: 'id',
    messages: { id, ja, ko, en },
})

export const availableLocales = [
    { code: 'id', label: 'Indonesia', flag: '🇮🇩' },
    { code: 'ja', label: '日本語',    flag: '🇯🇵' },
    { code: 'ko', label: '한국어',    flag: '🇰🇷' },
    { code: 'en', label: 'English',   flag: '🇬🇧' },
]

export function setLocale(code) {
    i18n.global.locale.value = code
    localStorage.setItem('hri_locale', code)
}