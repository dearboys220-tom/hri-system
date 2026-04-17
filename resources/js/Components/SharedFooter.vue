<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
    <footer class="bg-slate-950 text-slate-400 py-12 pb-24 md:pb-12">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8">

                <!-- ロゴ・説明 -->
                <div class="max-w-xs">
                    <img src="/images/logo.png" alt="HRI" class="h-10 w-auto mb-3" />
                    <p class="text-sm leading-relaxed">{{ t('footer.desc') }}</p>
                </div>

                <!-- リンク -->
                <div>
                    <div class="text-white font-semibold mb-3 text-sm uppercase tracking-[0.18em]">
                        {{ t('footer.legal_title') }}
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <Link href="/about" class="hover:text-white transition">
                                {{ t('footer.about') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/policies" class="hover:text-white transition">
                                {{ t('footer.policies') }}
                            </Link>
                        </li>
                        <li>
                            <Link href="/contact" class="hover:text-white transition">
                                {{ t('footer.contact') }}
                            </Link>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="border-t border-slate-800 mt-8 pt-6 text-xs text-center">
                {{ t('footer.copyright') }}
            </div>
        </div>
    </footer>
</template>