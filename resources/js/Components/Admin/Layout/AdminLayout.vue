<script setup>
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    title:    { type: String, default: 'Tim Admin' },
    subtitle: { type: String, default: '' },
})

const user = computed(() => usePage().props.auth?.user)

function logout() {
    router.post(route('staff.logout'))
}

const navItems = [
    { label: 'Dashboard',         icon: '🏠', href: '/admin/admin'           },
    { label: 'Persetujuan Akhir', icon: '✅', href: '/admin/admin/evaluate'  },
    { label: 'Perusahaan',        icon: '🏢', href: '/admin/admin/companies' },
]

const currentPath = computed(() => usePage().url)

function isActive(href) {
    return currentPath.value === href || currentPath.value.startsWith(href + '?')
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 flex flex-col">

        <header class="bg-admin-primary-900 text-white shadow-lg">
            <div class="max-w-screen-xl mx-auto px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-admin-primary-300 font-mono tracking-widest uppercase">HRI System</p>
                    <h1 class="text-xl font-bold">{{ title }}</h1>
                    <p v-if="subtitle" class="text-xs text-admin-primary-300 mt-0.5">{{ subtitle }}</p>
                </div>

                <nav class="hidden md:flex items-center gap-2">
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                        :class="isActive(item.href) ? 'bg-white/25 text-white' : 'text-admin-primary-200 hover:bg-white/10 hover:text-white'"
                    >{{ item.icon }} {{ item.label }}</a>
                </nav>

                <div class="flex items-center gap-3">
                    <span class="text-sm text-admin-primary-200 hidden sm:block">{{ user?.name }}</span>
                    <button
                        @click="logout"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                    >🚪 Keluar</button>
                </div>
            </div>

            <div class="md:hidden border-t border-white/10 px-6 py-2 flex gap-2 overflow-x-auto">
                <a
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors flex-shrink-0"
                    :class="isActive(item.href) ? 'bg-white/25 text-white' : 'text-admin-primary-200 hover:bg-white/10 hover:text-white'"
                >{{ item.icon }} {{ item.label }}</a>
            </div>
        </header>

        <main class="flex-1 max-w-screen-xl mx-auto w-full px-6 py-8">
            <slot />
        </main>
    </div>
</template>