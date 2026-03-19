<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    job: Object,
    categories: Object,
});

const form = useForm({
    title:                  props.job.title ?? '',
    workplace_photo:        null,
    category:               props.job.category ?? '',
    subcategory:            props.job.subcategory ?? '',
    employment_type:        props.job.employment_type ?? '',
    education_requirement:  props.job.education_requirement ?? '',
    experience_level:       props.job.experience_level ?? '',
    working_days:           props.job.working_days ?? [],
    working_hours:          props.job.working_hours ?? '',
    language_requirements:  props.job.language_requirements ?? [],
    gender:                 props.job.gender ?? '',
    age_min:                props.job.age_min ?? '',
    age_max:                props.job.age_max ?? '',
    marital_status:         props.job.marital_status ?? '',
    salary_min:             props.job.salary_min ?? '',
    salary_max:             props.job.salary_max ?? '',
    location:               props.job.location ?? '',
    job_description:        props.job.job_description ?? '',
    required_skills:        props.job.required_skills ?? '',
    preferred_skills:       props.job.preferred_skills ?? '',
    application_deadline:   props.job.application_deadline?.toString().split('T')[0] ?? '',
    start_date:             props.job.start_date?.toString().split('T')[0] ?? '',
    special_requirements:   props.job.special_requirements ?? '',
    status:                 props.job.status ?? 'active',
});

const photoPreview = ref(
    props.job.workplace_photo ? `/storage/${props.job.workplace_photo}` : null
);

function onPhotoChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.workplace_photo = file;
    photoPreview.value = URL.createObjectURL(file);
}

function submit() {
    form.post(`/company/jobs/${props.job.id}`, { forceFormData: true });
}

const employmentTypes   = ['Full-time', 'Part-time', 'Kontrak', 'Freelance', 'Magang'];
const educationOptions  = ['SMA/SMK', 'D3', 'S1', 'S2', 'S3', 'Tidak Dipersyaratkan'];
const experienceOptions = ['Fresh Graduate', '1–2 tahun', '3–5 tahun', '5–10 tahun', '10+ tahun'];
const workingDaysOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
const languageOptions   = ['Bahasa Indonesia', 'Bahasa Inggris', 'Bahasa Mandarin', 'Bahasa Jepang', 'Bahasa Arab'];
const genderOptions     = ['Laki-laki', 'Perempuan', 'Tidak Dipersyaratkan'];
const maritalOptions    = ['Belum Menikah', 'Menikah', 'Tidak Dipersyaratkan'];
</script>

<template>
    <Head title="Edit Lowongan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Lowongan</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-5">

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- ① 基本情報 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="font-semibold text-gray-700 border-b pb-2">📋 Informasi Lowongan</h3>

                        <!-- ステータス -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Lowongan</label>
                            <select v-model="form.status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                <option value="active">Aktif</option>
                                <option value="closed">Ditutup</option>
                            </select>
                        </div>

                        <!-- 職場写真 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Tempat Kerja</label>
                            <div class="flex items-center gap-4">
                                <div class="w-24 h-24 rounded-xl bg-gray-100 overflow-hidden border border-gray-200 flex items-center justify-center flex-shrink-0">
                                    <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                                    <span v-else class="text-3xl text-gray-300">🏢</span>
                                </div>
                                <label class="cursor-pointer bg-gray-50 border border-dashed border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-500 hover:bg-gray-100 transition">
                                    📷 Ganti Foto
                                    <input type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul / Nama Posisi <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bidang Pekerjaan</label>
                                <select v-model="form.category" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white" @change="form.subcategory = ''">
                                    <option value="">-- Pilih Bidang --</option>
                                    <option v-for="(cat, key) in categories" :key="key" :value="key">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Kepegawaian <span class="text-red-500">*</span></label>
                                <select v-model="form.employment_type" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="">-- Pilih Status --</option>
                                    <option v-for="opt in employmentTypes" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="form.category && categories[form.category]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Spesialisasi</label>
                            <select v-model="form.subcategory" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                <option value="">-- Pilih Spesialisasi --</option>
                                <option v-for="(label, subkey) in categories[form.category].subcategories" :key="subkey" :value="subkey">{{ label }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                            <input v-model="form.location" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                        </div>
                    </div>

                    <!-- ② 勤務条件 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="font-semibold text-gray-700 border-b pb-2">🕐 Kondisi Kerja</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kerja</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="day in workingDaysOptions" :key="day"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm cursor-pointer transition"
                                    :class="form.working_days.includes(day) ? 'bg-indigo-50 border-indigo-400 text-indigo-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <input type="checkbox" :value="day" v-model="form.working_days" class="hidden" />
                                    {{ day }}
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Kerja</label>
                            <input v-model="form.working_hours" type="text" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="08.00 – 17.00 WIB" />
                        </div>
                    </div>

                    <!-- ③ 給与・締切 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="font-semibold text-gray-700 border-b pb-2">💰 Gaji & Jadwal</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Minimum (IDR)</label>
                                <input v-model="form.salary_min" type="number" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Maksimum (IDR)</label>
                                <input v-model="form.salary_max" type="number" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Batas Lamaran <span class="text-red-500">*</span></label>
                                <input v-model="form.application_deadline" type="date" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai Kerja</label>
                                <input v-model="form.start_date" type="date" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                            </div>
                        </div>
                    </div>

                    <!-- ④ 応募要件 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="font-semibold text-gray-700 border-b pb-2">📌 Persyaratan Pelamar</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan Minimal</label>
                                <select v-model="form.education_requirement" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="opt in educationOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pengalaman Kerja</label>
                                <select v-model="form.experience_level" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="opt in experienceOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Persyaratan Bahasa</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="lang in languageOptions" :key="lang"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm cursor-pointer transition"
                                    :class="form.language_requirements.includes(lang) ? 'bg-indigo-50 border-indigo-400 text-indigo-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <input type="checkbox" :value="lang" v-model="form.language_requirements" class="hidden" />
                                    {{ lang }}
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                                <select v-model="form.gender" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="opt in genderOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Usia Min</label>
                                <input v-model="form.age_min" type="number" min="15" max="99" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Usia Max</label>
                                <input v-model="form.age_max" type="number" min="15" max="99" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Pernikahan</label>
                            <select v-model="form.marital_status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                <option value="">-- Pilih --</option>
                                <option v-for="opt in maritalOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- ⑤ 詳細 -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h3 class="font-semibold text-gray-700 border-b pb-2">📝 Detail Pekerjaan</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Pekerjaan <span class="text-red-500">*</span></label>
                            <textarea v-model="form.job_description" rows="6" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterampilan yang Dibutuhkan</label>
                            <textarea v-model="form.required_skills" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keahlian Tambahan</label>
                            <textarea v-model="form.preferred_skills" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Persyaratan Khusus</label>
                            <textarea v-model="form.special_requirements" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        </div>
                    </div>

                    <!-- 保存ボタン -->
                    <div class="flex justify-end gap-3 pb-8">
                        <Link href="/company/jobs" class="border border-gray-300 text-gray-600 px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                            Batal
                        </Link>
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