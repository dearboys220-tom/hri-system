<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    profile: Object,
});

const flash = computed(() => usePage().props.flash ?? {});

const form = useForm({
    company_name:        props.profile?.company_name        ?? '',
    pic_name:            props.profile?.pic_name            ?? '',
    pic_position:        props.profile?.pic_position        ?? '',
    pic_phone:           props.profile?.pic_phone           ?? '',
    company_address:     props.profile?.company_address     ?? '',
    company_phone:       props.profile?.company_phone       ?? '',
    company_website:     props.profile?.company_website     ?? '',
    company_description: props.profile?.company_description ?? '',
    industry_type:       props.profile?.industry_type       ?? '',
    company_size:        props.profile?.company_size        ?? '',
    company_logo:        null,
});

const logoPreview = ref(
    props.profile?.company_logo
        ? `/storage/${props.profile.company_logo}`
        : null
);

function onLogoChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.company_logo = file;
    logoPreview.value = URL.createObjectURL(file);
}

function submit() {
    form.post('/company/profile', {
        forceFormData: true,
    });
}

const industryOptions = [
    'Teknologi Informasi',
    'Keuangan & Perbankan',
    'Manufaktur',
    'Perdagangan & Ritel',
    'Konstruksi & Properti',
    'Kesehatan',
    'Pendidikan',
    'Logistik & Transportasi',
    'Media & Hiburan',
    'Pertanian & Perkebunan',
    'Energi & Pertambangan',
    'Lainnya',
];

const sizeOptions = [
    '1–10 karyawan',
    '11–50 karyawan',
    '51–100 karyawan',
    '101–500 karyawan',
    '500+ karyawan',
];

// 認証ステータス表示
const verificationConfig = {
    pending:   { label: 'Menunggu Verifikasi', color: 'bg-yellow-100 text-yellow-800', icon: '⏳' },
    verified:  { label: 'Terverifikasi',        color: 'bg-green-100 text-green-700',  icon: '✅' },
    suspended: { label: 'Ditangguhkan',         color: 'bg-red-100 text-red-700',      icon: '🚫' },
    rejected:  { label: 'Ditolak',              color: 'bg-gray-100 text-gray-600',    icon: '❌' },
};
const verif = verificationConfig[props.profile?.company_verification_status] ?? verificationConfig.pending;
</script>

<template>
    <Head title="Profil Perusahaan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Profil Perusahaan
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- 成功メッセージ -->
                <div v-if="flash.success" class="bg-green-50 border border-green-200 rounded-xl p-4 text-green-700 text-sm font-medium">
                    ✅ {{ flash.success }}
                </div>

                <!-- ヘッダーカード -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
                    <!-- ロゴ -->
                    <div class="relative">
                        <div class="w-20 h-20 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center border border-gray-200">
                            <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                            <span v-else class="text-3xl text-gray-300">🏢</span>
                        </div>
                        <label class="absolute -bottom-2 -right-2 bg-indigo-600 text-white rounded-full w-7 h-7 flex items-center justify-center cursor-pointer hover:bg-indigo-700 shadow text-xs">
                            ✏️
                            <input type="file" accept="image/*" class="hidden" @change="onLogoChange" />
                        </label>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ profile?.company_name ?? '-' }}</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Member ID: <span class="font-mono font-semibold text-indigo-600">{{ profile?.member_id ?? '-' }}</span></p>
                        <span :class="['inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold', verif.color]">
                            {{ verif.icon }} {{ verif.label }}
                        </span>
                    </div>
                </div>

                <!-- フォーム -->
                <form @submit.prevent="submit" class="space-y-5">

                    <!-- 基本情報 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="font-semibold text-gray-700 border-b pb-2">📋 Informasi Dasar</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan <span class="text-red-500">*</span></label>
                            <input v-model="form.company_name" type="text"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                placeholder="PT. Contoh Indonesia" />
                            <p v-if="form.errors.company_name" class="text-red-500 text-xs mt-1">{{ form.errors.company_name }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Industri</label>
                                <select v-model="form.industry_type"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="">-- Pilih Industri --</option>
                                    <option v-for="opt in industryOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran Perusahaan</label>
                                <select v-model="form.company_size"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="">-- Pilih Ukuran --</option>
                                    <option v-for="opt in sizeOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Perusahaan</label>
                            <textarea v-model="form.company_description" rows="4"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                placeholder="Ceritakan tentang perusahaan Anda..."></textarea>
                        </div>
                    </div>

                    <!-- 連絡先情報 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="font-semibold text-gray-700 border-b pb-2">📞 Kontak Perusahaan</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon Perusahaan</label>
                                <input v-model="form.company_phone" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    placeholder="021-XXXXXXXX" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                                <input v-model="form.company_website" type="url"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    placeholder="https://perusahaan.com" />
                                <p v-if="form.errors.company_website" class="text-red-500 text-xs mt-1">{{ form.errors.company_website }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Perusahaan</label>
                            <textarea v-model="form.company_address" rows="3"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                placeholder="Jl. Contoh No. 123, Jakarta..."></textarea>
                        </div>
                    </div>

                    <!-- 担当者情報 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="font-semibold text-gray-700 border-b pb-2">👤 Penanggung Jawab</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC <span class="text-red-500">*</span></label>
                                <input v-model="form.pic_name" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    placeholder="Nama lengkap" />
                                <p v-if="form.errors.pic_name" class="text-red-500 text-xs mt-1">{{ form.errors.pic_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan PIC</label>
                                <input v-model="form.pic_position" type="text"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    placeholder="HRD Manager" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP PIC <span class="text-red-500">*</span></label>
                            <input v-model="form.pic_phone" type="text"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                placeholder="08XXXXXXXXXX" />
                            <p v-if="form.errors.pic_phone" class="text-red-500 text-xs mt-1">{{ form.errors.pic_phone }}</p>
                        </div>
                    </div>

                    <!-- 保存ボタン -->
                    <div class="flex justify-end gap-3 pb-8">
                        <a href="/company/dashboard"
                            class="border border-gray-300 text-gray-600 px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit" :disabled="form.processing"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-xl text-sm font-semibold transition disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : '💾 Simpan Perubahan' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>