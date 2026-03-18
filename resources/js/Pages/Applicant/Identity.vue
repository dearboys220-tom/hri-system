<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    profile: Object,
});

const form = useForm({
    nik: props.profile?.nik ?? '',
    ktp_address: props.profile?.ktp_address ?? '',
    ktp_card: null,
});

const previewUrl = ref(null);

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.ktp_card = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('applicant.identity.update'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Verifikasi Identitas" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Verifikasi Identitas</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

                <!-- 説明カード -->
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-6 flex gap-3">
                    <span class="text-2xl">ℹ️</span>
                    <div>
                        <p class="font-semibold text-blue-800 text-sm">Mengapa perlu verifikasi identitas?</p>
                        <p class="text-sm text-blue-700 mt-1">
                            NIK dan foto KTP diperlukan untuk memverifikasi identitas Anda sebelum mengajukan sertifikasi HRI.
                        </p>
                    </div>
                </div>

                <!-- フォームカード -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h3 class="font-bold text-gray-800 text-lg mb-6">Data Identitas</h3>

                    <form @submit.prevent="submit" class="space-y-6" enctype="multipart/form-data">

                        <!-- NIK -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                NIK (Nomor Induk Kependudukan) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.nik"
                                type="text"
                                maxlength="16"
                                placeholder="Masukkan 16 digit NIK"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                :class="{ 'border-red-400': form.errors.nik }"
                            />
                            <p v-if="form.errors.nik" class="text-red-500 text-xs mt-1">{{ form.errors.nik }}</p>
                            <p class="text-xs text-gray-400 mt-1">16 digit angka sesuai KTP</p>
                        </div>

                        <!-- KTP住所 -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Alamat sesuai KTP <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="form.ktp_address"
                                rows="3"
                                placeholder="Masukkan alamat lengkap sesuai KTP"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                :class="{ 'border-red-400': form.errors.ktp_address }"
                            ></textarea>
                            <p v-if="form.errors.ktp_address" class="text-red-500 text-xs mt-1">{{ form.errors.ktp_address }}</p>
                        </div>

                        <!-- KTP写真 -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Foto KTP <span class="text-red-500">*</span>
                            </label>

                            <!-- 既存画像プレビュー -->
                            <div v-if="profile?.ktp_card && !previewUrl" class="mb-3">
                                <p class="text-xs text-gray-400 mb-2">Foto KTP saat ini:</p>
                                <img
                                    :src="'/storage/' + profile.ktp_card"
                                    alt="KTP"
                                    class="w-full max-w-sm rounded-xl border border-gray-200"
                                />
                            </div>

                            <!-- 新しい画像プレビュー -->
                            <div v-if="previewUrl" class="mb-3">
                                <p class="text-xs text-gray-400 mb-2">Preview:</p>
                                <img
                                    :src="previewUrl"
                                    alt="Preview KTP"
                                    class="w-full max-w-sm rounded-xl border border-gray-200"
                                />
                            </div>

                            <!-- ファイル選択 -->
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 transition cursor-pointer"
                                @click="$refs.fileInput.click()"
                            >
                                <p class="text-sm text-gray-500">
                                    {{ profile?.ktp_card ? 'Klik untuk mengganti foto KTP' : 'Klik untuk upload foto KTP' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG — Maks. 2MB</p>
                            </div>
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onFileChange"
                            />
                            <p v-if="form.errors.ktp_card" class="text-red-500 text-xs mt-1">{{ form.errors.ktp_card }}</p>
                        </div>

                        <!-- 送信ボタン -->
                        <div class="flex gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 bg-indigo-600 text-white font-semibold py-3 rounded-xl hover:bg-indigo-700 transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Identitas' }}
                            </button>
                            <Link
                                href="/applicant/dashboard"
                                class="px-6 py-3 border border-gray-300 text-gray-600 rounded-xl hover:bg-gray-50 transition text-sm font-medium flex items-center"
                            >
                                Kembali
                            </Link>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>