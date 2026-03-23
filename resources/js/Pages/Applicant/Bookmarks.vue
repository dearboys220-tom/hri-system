<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    bookmarks: Array,
});

const bookmarkList = ref([...props.bookmarks]);
const removing = ref(null);

function formatSalary(min, max) {
    const fmt = v => 'Rp ' + Number(v).toLocaleString('id-ID');
    if (min && max) return `${fmt(min)} – ${fmt(max)}`;
    if (min) return `ab ${fmt(min)}`;
    return 'Negotiable';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
}

async function removeBookmark(bookmarkId, jobPostId) {
    removing.value = bookmarkId;
    try {
        await fetch(`/applicant/bookmarks/${jobPostId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        bookmarkList.value = bookmarkList.value.filter(b => b.bookmark_id !== bookmarkId);
    } finally {
        removing.value = null;
    }
}
</script>

<template>
    <Head title="Lowongan Disimpan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Lowongan Disimpan
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- 件数 -->
                <p class="text-sm text-gray-500">
                    {{ bookmarkList.length }} lowongan disimpan
                </p>

                <!-- ブックマークなし -->
                <div v-if="bookmarkList.length === 0"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <p class="text-4xl mb-3">🔖</p>
                    <p class="text-gray-500 font-medium">Belum ada lowongan yang disimpan</p>
                    <p class="text-gray-400 text-sm mt-1">Simpan lowongan favoritmu agar mudah ditemukan!</p>
                    <Link href="/jobs"
                          class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition">
                        Cari Lowongan
                    </Link>
                </div>

                <!-- リスト -->
                <div v-else class="space-y-3">
                    <div v-for="item in bookmarkList" :key="item.bookmark_id"
                         class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-800 truncate">{{ item.job.title }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">{{ item.job.company_name }}</p>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-gray-400">
                                    <span>📍 {{ item.job.location }}</span>
                                    <span>💼 {{ item.job.employment_type }}</span>
                                    <span>💰 {{ formatSalary(item.job.salary_min, item.job.salary_max) }}</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">
                                    ⏰ Deadline: {{ formatDate(item.job.application_deadline) }}
                                </p>

                                <!-- 締切済みバッジ -->
                                <span v-if="item.job.status === 'closed'"
                                      class="inline-block mt-2 text-xs bg-red-100 text-red-600 font-medium px-2.5 py-0.5 rounded-full">
                                    Lowongan Ditutup
                                </span>
                            </div>

                            <!-- 削除ボタン -->
                            <button @click="removeBookmark(item.bookmark_id, item.job.id)"
                                    :disabled="removing === item.bookmark_id"
                                    class="flex-shrink-0 text-gray-300 hover:text-red-400 transition text-xl leading-none"
                                    title="Hapus dari simpanan">
                                {{ removing === item.bookmark_id ? '...' : '🔖' }}
                            </button>
                        </div>

                        <!-- ボタン行 -->
                        <div class="mt-4 flex gap-2 justify-end">
                            <button @click="removeBookmark(item.bookmark_id, item.job.id)"
                                    :disabled="removing === item.bookmark_id"
                                    class="text-xs text-red-400 hover:text-red-600 font-medium transition">
                                Hapus Simpanan
                            </button>
                            <Link :href="`/jobs/${item.job.id}`"
                                  class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-1.5 rounded-xl transition">
                                Lihat Lowongan
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- 戻るボタン -->
                <div class="text-center pt-2">
                    <Link href="/jobs"
                          class="inline-block bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-6 py-2.5 rounded-xl transition">
                        ← Kembali ke Daftar Lowongan
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>