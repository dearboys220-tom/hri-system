<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t } = useI18n()

const form = ref({
    name: '',
    email: '',
    phone: '',
    category: '',
    subject: '',
    message: '',
})

const errors = ref({})
const submitting = ref(false)
const success = ref(false)
const inquiry_no = ref('')
const serverError = ref(false)

const categories = computed(() => [
    { value: 'general',      label: t('contact.categories.general') },
    { value: 'account',      label: t('contact.categories.account') },
    { value: 'verification', label: t('contact.categories.verification') },
    { value: 'payment',      label: t('contact.categories.payment') },
    { value: 'job',          label: t('contact.categories.job') },
    { value: 'company',      label: t('contact.categories.company') },
    { value: 'privacy',      label: t('contact.categories.privacy') },
    { value: 'other',        label: t('contact.categories.other') },
])

function validate() {
    errors.value = {}
    if (!form.value.name)     errors.value.name    = t('contact.required')
    if (!form.value.email)    errors.value.email   = t('contact.required')
    if (!form.value.category) errors.value.category = t('contact.required')
    if (!form.value.subject)  errors.value.subject = t('contact.required')
    if (!form.value.message)  errors.value.message = t('contact.required')
    return Object.keys(errors.value).length === 0
}

async function submit() {
    if (!validate()) return
    submitting.value = true
    serverError.value = false

    router.post('/contact', form.value, {
        onSuccess: (page) => {
            success.value = true
            inquiry_no.value = page.props.flash?.inquiry_no ?? ''
        },
        onError: (errs) => {
            errors.value = errs
            serverError.value = true
        },
        onFinish: () => { submitting.value = false },
    })
}
</script>

<template>
    <Head>
        <title>{{ t('contact.page_title') }}</title>
        <meta name="description" :content="t('contact.page_description')" />
    </Head>

    <div class="min-h-screen bg-slate-50 flex flex-col">

        <!-- ヘッダー -->
        <header class="bg-[#0A1A3A] text-white px-4 py-4">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="HRI" class="h-9 w-auto bg-white/90 rounded-lg p-1" />
                    <span class="hidden sm:block text-xs font-semibold tracking-[0.18em] uppercase text-white/90">
                        Human Reliability Intelligence
                    </span>
                </Link>
                <div class="flex items-center gap-3">
                    <LanguageSwitcher :dark="true" />
                    <Link href="/" class="text-sm text-white/70 hover:text-white transition">
                        ← {{ t('contact.back_home') }}
                    </Link>
                </div>
            </div>
        </header>

        <!-- ヒーロー -->
        <section class="bg-[#0A1A3A] text-white pt-10 pb-16 px-4 text-center">
            <span class="inline-block text-xs font-semibold tracking-[0.2em] uppercase text-teal-300 mb-3">
                {{ t('contact.hero_label') }}
            </span>
            <h1 class="text-2xl sm:text-3xl font-black mb-3">{{ t('contact.hero_title') }}</h1>
            <p class="text-white/70 text-sm max-w-lg mx-auto">{{ t('contact.hero_desc') }}</p>
        </section>

        <!-- メインコンテンツ -->
        <main class="flex-1 max-w-2xl mx-auto w-full px-4 -mt-8 pb-16">

            <!-- 送信成功 -->
            <div v-if="success" class="bg-white rounded-2xl shadow-md p-8 text-center">
                <div class="text-5xl mb-4">✅</div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ t('contact.success_title') }}</h2>
                <p class="text-gray-600 text-sm mb-4">{{ t('contact.success_desc') }}</p>
                <p v-if="inquiry_no" class="text-lg font-mono font-bold text-teal-700 bg-teal-50 rounded-lg px-4 py-2 inline-block mb-6">
                    {{ inquiry_no }}
                </p>
                <br />
                <Link href="/" class="inline-block mt-2 bg-[#0A1A3A] text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-slate-700 transition">
                    {{ t('contact.back_home') }}
                </Link>
            </div>

            <!-- フォーム -->
            <div v-else class="bg-white rounded-2xl shadow-md p-6 sm:p-8">
                <h2 class="text-lg font-bold text-gray-900 mb-6">{{ t('contact.form_title') }}</h2>

                <!-- SLA note -->
                <div class="bg-teal-50 border border-teal-200 rounded-lg px-4 py-2 text-xs text-teal-700 mb-6">
                    {{ t('contact.sla_note') }}
                </div>

                <!-- サーバーエラー -->
                <div v-if="serverError" class="bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm text-red-700 mb-4">
                    {{ t('contact.error_msg') }}
                </div>

                <div class="space-y-5">

                    <!-- 名前 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ t('contact.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                            :class="errors.name ? 'border-red-400' : 'border-gray-300'" />
                        <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
                    </div>

                    <!-- メール -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ t('contact.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.email" type="email"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                            :class="errors.email ? 'border-red-400' : 'border-gray-300'" />
                        <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
                    </div>

                    <!-- 電話（任意） -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ t('contact.phone') }}
                            <span class="text-gray-400 font-normal">{{ t('contact.phone_hint') }}</span>
                        </label>
                        <input v-model="form.phone" type="tel"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
                    </div>

                    <!-- カテゴリ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ t('contact.category') }} <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.category"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                            :class="errors.category ? 'border-red-400' : 'border-gray-300'">
                            <option value="">—</option>
                            <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                {{ cat.label }}
                            </option>
                        </select>
                        <p v-if="errors.category" class="text-xs text-red-500 mt-1">{{ errors.category }}</p>
                    </div>

                    <!-- 件名 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ t('contact.subject') }} <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.subject" type="text"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                            :class="errors.subject ? 'border-red-400' : 'border-gray-300'" />
                        <p v-if="errors.subject" class="text-xs text-red-500 mt-1">{{ errors.subject }}</p>
                    </div>

                    <!-- メッセージ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ t('contact.message') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea v-model="form.message" rows="5"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
                            :class="errors.message ? 'border-red-400' : 'border-gray-300'"></textarea>
                        <p v-if="errors.message" class="text-xs text-red-500 mt-1">{{ errors.message }}</p>
                    </div>

                    <!-- 送信ボタン -->
                    <button @click="submit" :disabled="submitting"
                        class="w-full bg-[#0A1A3A] text-white font-semibold py-3 rounded-xl hover:bg-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ submitting ? t('contact.submitting') : t('contact.submit') }}
                    </button>

                </div>
            </div>
        </main>

        <!-- フッター -->
        <footer class="bg-slate-950 text-slate-400 py-6 text-center text-xs">
            © 2026 PT. NIKI KINDAICHI THERR INDONESIA. HRI (Human Reliability Intelligence). Privacy, consent, and audit-log controls applied. All rights reserved.
        </footer>

    </div>
</template>