<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user)

const menuItems = [
    {
        label: 'Manajemen Staf',
        href:  '/manager/staff',
        icon:  '👥',
        desc:  'Daftar & pendaftaran staf internal',
    },
    {
        label: 'Instruksi Tugas',
        href:  '/manager/task-orders',
        icon:  '📋',
        desc:  'Buat dan kelola instruksi kerja untuk staf',
    },
    {
        label: 'Laporan Tugas',
        href:  '/manager/reports',
        icon:  '📝',
        desc:  'Daftar laporan yang dikirim oleh staf',
    },
    {
        label: 'Pengajuan Izin',
        href:  '/manager/absence-requests',
        icon:  '📅',
        desc:  'Setujui atau tolak pengajuan izin staf',
    },
    {
        label: 'Penilaian AI',
        href:  '/manager/evaluations',
        icon:  '📊',
        desc:  'Buat dan konfirmasi penilaian kinerja staf',
    },
    {
        label: 'Manajemen Gaji',
        href:  '/manager/salary',
        icon:  '💰',
        desc:  'Perhitungan dan persetujuan gaji staf',
    },
]

function logout() {
    router.post('/staff/logout')
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <div class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        HRI
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">HRI System</p>
                        <p class="text-xs text-gray-400">Manager Panel</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ user?.name }}</span>
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded font-semibold">Local Manager</span>
                    <button @click="logout" class="text-xs text-gray-500 hover:text-red-500 transition">Logout</button>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-6 py-8">

            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-2xl px-6 py-5 text-white mb-6">
                <p class="text-lg font-bold">Selamat datang, {{ user?.name }}</p>
                <p class="text-indigo-200 text-sm mt-1">Panel Manager — Kelola staf dan operasional internal HRI</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                    v-for="item in menuItems"
                    :key="item.label"
                    :href="item.href"
                    class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-md transition group"
                >
                    <div class="text-3xl mb-3">{{ item.icon }}</div>
                    <p class="font-semibold text-gray-800 group-hover:text-indigo-600 transition">{{ item.label }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ item.desc }}</p>
                </a>
            </div>

        </div>
    </div>
</template>