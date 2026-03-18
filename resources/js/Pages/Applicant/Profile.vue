<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    profile: Object,
});

const user = usePage().props.auth.user;

const form = useForm({
    desired_position: props.profile?.desired_position ?? '',
    desired_salary:   props.profile?.desired_salary ?? '',
    facebook_url:     props.profile?.facebook_url ?? '',
    linkedin_url:     props.profile?.linkedin_url ?? '',
    instagram_url:    props.profile?.instagram_url ?? '',
    self_pr:          props.profile?.self_pr ?? '',
    profile_photo:    null,
});

const photoPreview = ref(
    props.profile?.profile_photo
        ? `/storage/${props.profile.profile_photo}`
        : null
);

const onPhotoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (ev) => { photoPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('applicant.profile.update'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Profil Saya" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Profil Saya</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- 成功メッセージ -->
                <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-xl p-4 text-green-700 text-sm">
                    ✅ {{ $page.props.flash.success }}
                </div>

                <!-- 顔写真 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4">📷 Foto Profil</h3>
                    <div class="flex items-center gap-6">
                        <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center overflow-hidden shrink-0">
                            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                            <span v-else class="text-3xl font-bold text-indigo-600">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div>
                            <label class="cursor-pointer bg-indigo-600 text-white text-sm px-4 py-2 rounded-xl hover:bg-indigo-700 transition">
                                Pilih Foto
                                <input type="file" class="hidden" accept="image/*" @change="onPhotoChange" />
                            </label>
                            <p class="text-xs text-gray-400 mt-2">JPG / PNG, maksimal 2MB</p>
                            <p v-if="form.errors.profile_photo" class="text-xs text-red-500 mt-1">{{ form.errors.profile_photo }}</p>
                        </div>
                    </div>
                </div>

                <!-- 希望職種・給与 -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="font-bold text-gray-800 mb-2">💼 Preferensi Kerja</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Posisi yang Diinginkan</label>
                        <input
                            v-model="form.desired_position"
                            type="text"
                            placeholder="Contoh: HR Manager, Staff Accounting"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="form.errors.desired_position" class="text-xs text-red-500 mt-1">{{ form.errors.desired_position }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ekspektasi Gaji (IDR / bulan)</label>
                        <input
                            v-model="form.desired_salary"
                            type="number"
                            placeholder="Contoh: 5000000"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="form.errors.desired_salary" class="text-xs text-red-500 mt-1">{{ form.errors.desired_salary }}</p>
                    </div>
                </div>

                <!-- 自己PR -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4">📝 Tentang Saya</h3>
                    <textarea
                        v-model="form.self_pr"
                        rows="4"
                        placeholder="Ceritakan tentang diri Anda, keahlian, dan pengalaman singkat..."
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                    ></textarea>
                    <p v-if="form.errors.self_pr" class="text-xs text-red-500 mt-1">{{ form.errors.self_pr }}</p>
                </div>

                <!-- SNSリンク -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="font-bold text-gray-800 mb-2">🔗 Media Sosial</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                        <input
                            v-model="form.facebook_url"
                            type="url"
                            placeholder="https://facebook.com/username"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="form.errors.facebook_url" class="text-xs text-red-500 mt-1">{{ form.errors.facebook_url }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                        <input
                            v-model="form.linkedin_url"
                            type="url"
                            placeholder="https://linkedin.com/in/username"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="form.errors.linkedin_url" class="text-xs text-red-500 mt-1">{{ form.errors.linkedin_url }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                        <input
                            v-model="form.instagram_url"
                            type="url"
                            placeholder="https://instagram.com/username"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="form.errors.instagram_url" class="text-xs text-red-500 mt-1">{{ form.errors.instagram_url }}</p>
                    </div>
                </div>

                <!-- 保存ボタン -->
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="w-full py-3 rounded-xl text-white font-semibold text-sm transition"
                    :style="form.processing ? 'background-color: #d1d5db;' : 'background-color: #4f46e5;'"
                >
                    {{ form.processing ? 'Menyimpan...' : '💾 Simpan Profil' }}
                </button>

            </div>
        </div>
    </AuthenticatedLayout>
</template>