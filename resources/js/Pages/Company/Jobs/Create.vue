<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    isFreePostAvailable: Boolean,
    freePostDaysLeft:    Number,
    isVerified:          Boolean,
    categoriesGrouped:   Array,
});

const form = useForm({
    title:                  '',
    workplace_photo:        null,
    job_category_id:        '',
    employment_type:        '',
    education_requirement:  '',
    experience_level:       '',
    working_days:           [],
    working_hours:          '',
    language_requirements:  [],
    gender:                 '',
    age_min:                '',
    age_max:                '',
    marital_status:         '',
    salary_min:             '',
    salary_max:             '',
    location:               '',
    job_description:        '',
    required_skills:        '',
    preferred_skills:       '',
    application_deadline:   '',
    start_date:             '',
    special_requirements:   '',
});

const photoPreview = ref(null);

function onPhotoChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.workplace_photo = file;
    photoPreview.value = URL.createObjectURL(file);
}

function submit() {
    form.post('/company/jobs', { forceFormData: true });
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
    <Head title="Posting Lowongan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Posting Lowongan</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-5">

                <!-- 未認証ブロック -->
                <div v-if="!isVerified" class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
                    <p class="text-2xl mb-2">⚠️</p>
                    <p class="font-semibold text-red-700">Akun perusahaan belum diverifikasi</p>
                    <p class="text-sm text-red-600 mt-1">Posting lowongan hanya tersedia setelah akun Anda diverifikasi oleh tim HRI.</p>
                    <Link href="/company/dashboard"
                          class="inline-block mt-4 bg-red-600 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-red-700 transition">
                        Kembali ke Dashboard
                    </Link>
                </div>

                <template v-else>
                    <!-- 無料/有料バナー -->
                    <div v-if="isFreePostAvailable"
                         class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-4 text-white flex items-center gap-3">
                        <span class="text-2xl">🎉</span>
                        <div>
                            <p class="font-semibold">Posting GRATIS tersedia!</p>
                            <p class="text-sm text-emerald-100">Berlaku {{ freePostDaysLeft }} hari lagi. Lowongan ini tidak dikenakan biaya.</p>
                        </div>
                    </div>
                    <div v-else class="bg-orange-50 border border-orange-200 rounded-2xl p-4 flex items-center gap-3">
                        <span class="text-xl">💰</span>
                        <p class="text-sm text-orange-700">Posting lowongan dikenakan biaya <strong>Rp 250.000</strong> per lowongan.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">

                        <!-- ① 基本情報 -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                            <h3 class="font-semibold text-gray-700 border-b pb-2">📋 Informasi Lowongan</h3>

                            <!-- 職場写真 -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Tempat Kerja</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-24 h-24 rounded-xl bg-gray-100 overflow-hidden border border-gray-200
                                                flex items-center justify-center flex-shrink-0">
                                        <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                                        <span v-else class="text-3xl text-gray-300">🏢</span>
                                    </div>
                                    <label class="cursor-pointer bg-gray-50 border border-dashed border-gray-300
                                                  rounded-xl px-4 py-3 text-sm text-gray-500 hover:bg-gray-100 transition">
                                        📷 Pilih Foto
                                        <input type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
                                    </label>
                                </div>
                            </div>

                            <!-- タイトル -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Judul / Nama Posisi <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.title" type="text"
                                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                       placeholder="Contoh: Staff HR Senior" />
                                <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                            </div>

                            <!-- カテゴリ（階層セレクト） -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Pekerjaan</label>
                                    <select v-model="form.job_category_id"
                                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                        <option value="">-- Pilih Kategori --</option>
                                        <template v-for="parent in categoriesGrouped" :key="parent.id">
                                            <optgroup :label="parent.name">
                                                <option v-for="child in parent.children"
                                                        :key="child.id" :value="child.id">
                                                    {{ child.name }}
                                                </option>
                                            </optgroup>
                                        </template>
                                    </select>
                                    <p v-if="form.errors.job_category_id" class="text-red-500 text-xs mt-1">
                                        {{ form.errors.job_category_id }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Status Kepegawaian <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="form.employment_type"
                                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                        <option value="">-- Pilih Status --</option>
                                        <option v-for="opt in employmentTypes" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>
                                    <p v-if="form.errors.employment_type" class="text-red-500 text-xs mt-1">
                                        {{ form.errors.employment_type }}
                                    </p>
                                </div>
                            </div>

                            <!-- 場所 -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Lokasi Penempatan Kerja <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.location" type="text"
                                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                       placeholder="Contoh: Jakarta Selatan" />
                                <p v-if="form.errors.location" class="text-red-500 text-xs mt-1">{{ form.errors.location }}</p>
                            </div>
                        </div>

                        <!-- ② 勤務条件 -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                            <h3 class="font-semibold text-gray-700 border-b pb-2">🏢 Kondisi Kerja</h3>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kerja</label>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="day in workingDaysOptions" :key="day"
                                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm cursor-pointer transition"
                                           :class="form.working_days.includes(day)
                                               ? 'bg-indigo-50 border-indigo-400 text-indigo-700'
                                               : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                        <input type="checkbox" :value="day" v-model="form.working_days" class="hidden" />
                                        {{ day }}
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Kerja</label>
                                <input v-model="form.working_hours" type="text"
                                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                       placeholder="Contoh: 08.00 – 17.00 WIB" />
                            </div>
                        </div>

                        <!-- ③ 給与・締切 -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                            <h3 class="font-semibold text-gray-700 border-b pb-2">💰 Gaji & Jadwal</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Minimum (IDR)</label>
                                    <input v-model="form.salary_min" type="number" min="0"
                                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                           placeholder="5000000" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Maksimum (IDR)</label>
                                    <input v-model="form.salary_max" type="number" min="0"
                                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                           placeholder="8000000" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Batas Lamaran <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.application_deadline" type="date"
                                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                                    <p v-if="form.errors.application_deadline" class="text-red-500 text-xs mt-1">
                                        {{ form.errors.application_deadline }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai Kerja</label>
                                    <input v-model="form.start_date" type="date"
                                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                                </div>
                            </div>
                        </div>

                        <!-- ④ 応募要件 -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                            <h3 class="font-semibold text-gray-700 border-b pb-2">📌 Persyaratan Pelamar</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan Minimal</label>
                                    <select v-model="form.education_requirement"
                                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                        <option value="">-- Pilih Pendidikan --</option>
                                        <option v-for="opt in educationOptions" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pengalaman Kerja</label>
                                    <select v-model="form.experience_level"
                                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                        <option value="">-- Pilih Pengalaman --</option>
                                        <option v-for="opt in experienceOptions" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Persyaratan Bahasa</label>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="lang in languageOptions" :key="lang"
                                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm cursor-pointer transition"
                                           :class="form.language_requirements.includes(lang)
                                               ? 'bg-indigo-50 border-indigo-400 text-indigo-700'
                                               : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                        <input type="checkbox" :value="lang" v-model="form.language_requirements" class="hidden" />
                                        {{ lang }}
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                                    <select v-model="form.gender"
                                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                        <option value="">-- Pilih --</option>
                                        <option v-for="opt in genderOptions" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Usia Min</label>
                                    <input v-model="form.age_min" type="number" min="15" max="99"
                                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                           placeholder="20" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Usia Max</label>
                                    <input v-model="form.age_max" type="number" min="15" max="99"
                                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                  focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                           placeholder="35" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Pernikahan</label>
                                <select v-model="form.marital_status"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                               focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="opt in maritalOptions" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- ⑤ 詳細 -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                            <h3 class="font-semibold text-gray-700 border-b pb-2">📝 Detail Pekerjaan</h3>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Deskripsi Pekerjaan <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="form.job_description" rows="6"
                                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                          placeholder="Jelaskan tanggung jawab dan tugas pekerjaan..."></textarea>
                                <p v-if="form.errors.job_description" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.job_description }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keterampilan yang Dibutuhkan</label>
                                <textarea v-model="form.required_skills" rows="3"
                                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                          placeholder="Contoh: Pengalaman HR minimal 2 tahun..."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keahlian Tambahan (Nilai Plus)</label>
                                <textarea v-model="form.preferred_skills" rows="3"
                                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                          placeholder="Contoh: Memiliki sertifikat HRI..."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Persyaratan Khusus</label>
                                <textarea v-model="form.special_requirements" rows="3"
                                          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                                 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                          placeholder="Contoh: Bersedia dinas luar kota..."></textarea>
                            </div>
                        </div>

                        <!-- 送信ボタン -->
                        <div class="flex justify-end gap-3 pb-8">
                            <Link href="/company/dashboard"
                                  class="border border-gray-300 text-gray-600 px-6 py-2.5 rounded-xl
                                         text-sm font-medium hover:bg-gray-50 transition">
                                Batal
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5
                                           rounded-xl text-sm font-semibold transition disabled:opacity-50">
                                {{ form.processing
                                    ? 'Memproses...'
                                    : (isFreePostAvailable ? '🎉 Posting GRATIS' : '💰 Posting Lowongan') }}
                            </button>
                        </div>

                    </form>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>