<template>
    <aside class="w-[340px] p-6 space-y-6">

    <!-- Search -->
        <div class="bg-white rounded-2xl shadow-sm border border-admin-gray-200 p-6">

            <h2 class="font-semibold text-admin-gray-900 flex items-center gap-2 mb-4">
            <MagnifyingGlassIcon class="w-5 h-5 text-admin-primary-700" />
            Pencarian Anggota
            </h2>

            <div class="flex gap-2">
            <input
                type="text"
                placeholder="Cari NIK / Nama"
                class="flex-1 rounded-xl border border-admin-gray-300 px-4 py-2 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none"
            />
            <button class="bg-admin-primary-700 hover:bg-admin-primary-600 text-white px-4 rounded-xl transition">
                Cari
            </button>
            </div>

        </div>

        <!-- Request List -->
        <div class="bg-white rounded-2xl shadow-sm border border-admin-gray-200 p-6">

            <h2 class="font-semibold text-admin-gray-900 flex items-center gap-2 mb-4">
            <ClipboardDocumentListIcon class="w-5 h-5 text-admin-primary-700" />
            Permintaan Investigasi
            </h2>

            <div class="space-y-3">

            <div class="p-4 rounded-xl border border-admin-gray-200 hover:border-admin-primary-600 hover:bg-admin-primary-100 transition cursor-pointer">
                <p class="font-medium text-admin-gray-900">
                HRIM-BNSIVHM
                </p>
                <p class="text-sm text-admin-gray-500 flex items-center gap-1">
                <UserCircleIcon class="w-4 h-4" />
                TIKA • 03/02/2026
                </p>
            </div>

            </div>

        </div>

    </aside>
</template>