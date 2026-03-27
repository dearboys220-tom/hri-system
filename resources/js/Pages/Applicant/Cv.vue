<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    profile: Object,
    educations: Array,
    works: Array,
    certs: Array,
});

const activeTab = ref('basic');

// ===== 基本情報フォーム =====
const basicForm = useForm({
    full_name:       props.profile?.full_name ?? '',
    gender:          props.profile?.gender ?? '',
    birth_date:      props.profile?.birth_date ?? '',
    nationality:     props.profile?.nationality ?? '',
    marital_status:  props.profile?.marital_status ?? '',
    phone_number:    props.profile?.phone_number ?? '',
    whatsapp_number: props.profile?.whatsapp_number ?? '',
    current_address: props.profile?.current_address ?? '',
    self_pr:         props.profile?.self_pr ?? '',
    profile_photo:   null,
});

const submitProfile = () => {
    basicForm.post(route('applicant.cv.profile.update'), {
        forceFormData: true,
    });
};

// ===== 学歴フォーム =====
const showEduForm = ref(false);
const editingEduId = ref(null);

const eduForm = useForm({
    education_level:       '',
    school_name:           '',
    school_location:       '',
    degree_name:           '',
    enrollment_date:       '',
    graduation_date:       '',
    graduation_status:     '',
    ipk_gpa:               '',
    academic_achievements: '',
    ijazah_transcript:     null,
});

const openEduForm = () => { editingEduId.value = null; eduForm.reset(); showEduForm.value = true; };
const editEdu = (edu) => {
    editingEduId.value = edu.id;
    eduForm.education_level       = edu.education_level ?? '';
    eduForm.school_name           = edu.school_name ?? '';
    eduForm.school_location       = edu.school_location ?? '';
    eduForm.degree_name           = edu.degree_name ?? '';
    eduForm.enrollment_date       = edu.enrollment_date ?? '';
    eduForm.graduation_date       = edu.graduation_date ?? '';
    eduForm.graduation_status     = edu.graduation_status ?? '';
    eduForm.ipk_gpa               = edu.ipk_gpa ?? '';
    eduForm.academic_achievements = edu.academic_achievements ?? '';
    showEduForm.value = true;
};
const submitEdu = () => {
    const url = editingEduId.value
        ? route('applicant.cv.education.update', editingEduId.value)
        : route('applicant.cv.education.store');
    eduForm.post(url, {
        forceFormData: true,
        onSuccess: () => { eduForm.reset(); showEduForm.value = false; editingEduId.value = null; },
    });
};
const deleteEdu = (id) => { if (confirm('Hapus data pendidikan ini?')) router.delete(route('applicant.cv.education.destroy', id)); };

// ===== 職歴フォーム =====
const showWorkForm = ref(false);
const editingWorkId = ref(null);

const workForm = useForm({
    company_name:            '',
    company_address:         '',
    department_position:     '',
    employment_type:         '',
    employment_start_date:   '',
    employment_end_date:     '',
    job_description:         '',
    resignation_reason:      '',
    employment_achievements: '',
    supervisor_full_name:    '',
    supervisor_position:     '',
    supervisor_phone:        '',
    employment_certificate:  null,
});

const openWorkForm = () => { editingWorkId.value = null; workForm.reset(); showWorkForm.value = true; };
const editWork = (work) => {
    editingWorkId.value = work.id;
    workForm.company_name            = work.company_name ?? '';
    workForm.company_address         = work.company_address ?? '';
    workForm.department_position     = work.department_position ?? '';
    workForm.employment_type         = work.employment_type ?? '';
    workForm.employment_start_date   = work.employment_start_date ?? '';
    workForm.employment_end_date     = work.employment_end_date ?? '';
    workForm.job_description         = work.job_description ?? '';
    workForm.resignation_reason      = work.resignation_reason ?? '';
    workForm.employment_achievements = work.employment_achievements ?? '';
    workForm.supervisor_full_name    = work.supervisor_full_name ?? '';
    workForm.supervisor_position     = work.supervisor_position ?? '';
    workForm.supervisor_phone        = work.supervisor_phone ?? '';
    showWorkForm.value = true;
};
const submitWork = () => {
    const url = editingWorkId.value
        ? route('applicant.cv.work.update', editingWorkId.value)
        : route('applicant.cv.work.store');
    workForm.post(url, {
        forceFormData: true,
        onSuccess: () => { workForm.reset(); showWorkForm.value = false; editingWorkId.value = null; },
    });
};
const deleteWork = (id) => { if (confirm('Hapus data pekerjaan ini?')) router.delete(route('applicant.cv.work.destroy', id)); };

// ===== 資格フォーム =====
const showCertForm = ref(false);
const editingCertId = ref(null);

const certForm = useForm({
    certificate_name:       '',
    issuing_organization:   '',
    issue_date:             '',
    expiration_date:        '',
    certificate_score:      '',
    certificate_notes:      '',
    certificate_attachment: null,
});

const openCertForm = () => { editingCertId.value = null; certForm.reset(); showCertForm.value = true; };
const editCert = (cert) => {
    editingCertId.value = cert.id;
    certForm.certificate_name       = cert.certificate_name ?? '';
    certForm.issuing_organization   = cert.issuing_organization ?? '';
    certForm.issue_date             = cert.issue_date ?? '';
    certForm.expiration_date        = cert.expiration_date ?? '';
    certForm.certificate_score      = cert.certificate_score ?? '';
    certForm.certificate_notes      = cert.certificate_notes ?? '';
    showCertForm.value = true;
};
const submitCert = () => {
    const url = editingCertId.value
        ? route('applicant.cv.certification.update', editingCertId.value)
        : route('applicant.cv.certification.store');
    certForm.post(url, {
        forceFormData: true,
        onSuccess: () => { certForm.reset(); showCertForm.value = false; editingCertId.value = null; },
    });
};
const deleteCert = (id) => { if (confirm('Hapus data sertifikasi ini?')) router.delete(route('applicant.cv.certification.destroy', id)); };
</script>

<template>
    <Head title="Input CV" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Input CV</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl px-4 space-y-4">

                <!-- タブ -->
                <div class="flex border-b border-gray-200 bg-white rounded-t-lg overflow-x-auto">
                    <button
                        v-for="tab in [
                            { key: 'basic',         label: 'Data Diri' },
                            { key: 'education',     label: 'Pendidikan' },
                            { key: 'work',          label: 'Pengalaman' },
                            { key: 'certification', label: 'Sertifikasi' },
                        ]"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        class="flex-1 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                        :class="activeTab === tab.key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500'"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- ===== 基本情報タブ ===== -->
                <div v-if="activeTab === 'basic'" class="space-y-3">
                    <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                        <h3 class="font-semibold text-gray-700">Informasi Dasar</h3>

                        <div v-if="basicForm.wasSuccessful" class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">
                            ✅ Berhasil disimpan!
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input v-model="basicForm.full_name" type="text"
                                placeholder="Contoh: Budi Santoso"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="basicForm.errors.full_name" class="text-red-500 text-xs mt-1">{{ basicForm.errors.full_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select v-model="basicForm.gender" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            <p v-if="basicForm.errors.gender" class="text-red-500 text-xs mt-1">{{ basicForm.errors.gender }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input v-model="basicForm.birth_date" type="date"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="basicForm.errors.birth_date" class="text-red-500 text-xs mt-1">{{ basicForm.errors.birth_date }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Kewarganegaraan <span class="text-red-500">*</span></label>
                            <select v-model="basicForm.nationality" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih Kewarganegaraan</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Japan">Japan</option>
                                <option value="Korea">Korea</option>
                                <option value="China">China</option>
                                <option value="India">India</option>
                                <option value="Australia">Australia</option>
                                <option value="Usa">United States</option>
                                <option value="Uk">United Kingdom</option>
                                <option value="Other">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Status Pernikahan <span class="text-red-500">*</span></label>
                            <select v-model="basicForm.marital_status" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih Status Pernikahan</option>
                                <option value="Belum Menikah">Belum Menikah</option>
                                <option value="Menikah">Menikah</option>
                                <option value="Cerai">Cerai</option>
                                <option value="Cerai Mati">Cerai Mati</option>
                            </select>
                            <p v-if="basicForm.errors.marital_status" class="text-red-500 text-xs mt-1">{{ basicForm.errors.marital_status }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                            <input v-model="basicForm.phone_number" type="text"
                                placeholder="Contoh: 08123456789"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="basicForm.errors.phone_number" class="text-red-500 text-xs mt-1">{{ basicForm.errors.phone_number }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nomor WhatsApp</label>
                            <input v-model="basicForm.whatsapp_number" type="text"
                                placeholder="Contoh: 08123456789"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Alamat Saat Ini <span class="text-red-500">*</span></label>
                            <textarea v-model="basicForm.current_address" rows="3"
                                placeholder="Contoh: Jl. Sudirman No. 10, Jakarta Selatan, DKI Jakarta"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm resize-none"></textarea>
                            <p v-if="basicForm.errors.current_address" class="text-red-500 text-xs mt-1">{{ basicForm.errors.current_address }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Tentang Saya</label>
                            <textarea v-model="basicForm.self_pr" rows="4"
                                placeholder="Contoh: Saya adalah profesional HR dengan pengalaman 5 tahun di bidang rekrutmen dan pengembangan SDM..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm resize-none"></textarea>
                        </div>

                        <button
                            @click="submitProfile"
                            :disabled="basicForm.processing"
                            class="w-full bg-indigo-600 text-white py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 transition disabled:opacity-50"
                        >
                            {{ basicForm.processing ? 'Menyimpan...' : 'Simpan Data Diri' }}
                        </button>
                    </div>
                </div>

                <!-- ===== 学歴タブ ===== -->
                <div v-if="activeTab === 'education'" class="space-y-3">
                    <div v-for="edu in educations" :key="edu.id" class="bg-white rounded-xl shadow-sm p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">{{ edu.school_name }}</p>
                                <p class="text-sm text-gray-500">{{ edu.education_level }}{{ edu.degree_name ? ' — ' + edu.degree_name : '' }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ edu.enrollment_date ?? '?' }} — {{ edu.graduation_date ?? 'Sekarang' }}</p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <button @click="editEdu(edu)" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg">Edit</button>
                                <button @click="deleteEdu(edu.id)" class="text-xs bg-red-50 text-red-500 px-3 py-1 rounded-lg">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <button v-if="!showEduForm" @click="openEduForm" class="w-full py-4 border-2 border-dashed border-indigo-300 rounded-xl text-indigo-600 font-medium text-sm hover:bg-indigo-50 transition">
                        + Tambah Pendidikan
                    </button>

                    <div v-if="showEduForm" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                        <h3 class="font-semibold text-gray-700">{{ editingEduId ? 'Edit Pendidikan' : 'Tambah Pendidikan' }}</h3>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                            <select v-model="eduForm.education_level" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih Tingkat Pendidikan</option>
                                <option>SD (Sekolah Dasar)</option>
                                <option>SMP (Sekolah Menengah Pertama)</option>
                                <option>SLTA Sederajat</option>
                                <option>Diploma I (D1)</option>
                                <option>Diploma II (D2)</option>
                                <option>Diploma III (D3)</option>
                                <option>Diploma IV (D4)</option>
                                <option>Sarjana (S1)</option>
                                <option>Magister (S2)</option>
                                <option>Doktor (S3)</option>
                                <option>Lainnya</option>
                            </select>
                            <p v-if="eduForm.errors.education_level" class="text-red-500 text-xs mt-1">{{ eduForm.errors.education_level }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Sekolah / Institusi <span class="text-red-500">*</span></label>
                            <input v-model="eduForm.school_name" type="text"
                                placeholder="Contoh: Universitas Indonesia"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="eduForm.errors.school_name" class="text-red-500 text-xs mt-1">{{ eduForm.errors.school_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Alamat Sekolah</label>
                            <input v-model="eduForm.school_location" type="text"
                                placeholder="Contoh: Depok, Jawa Barat"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jurusan / Program Studi</label>
                            <input v-model="eduForm.degree_name" type="text"
                                placeholder="Contoh: Manajemen Sumber Daya Manusia"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Masuk</label>
                                <input v-model="eduForm.enrollment_date" type="date"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Lulus</label>
                                <input v-model="eduForm.graduation_date" type="date"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Status Kelulusan</label>
                            <select v-model="eduForm.graduation_status" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih Status Kelulusan</option>
                                <option>Lulus</option>
                                <option>Masih Menempuh Pendidikan</option>
                                <option>Mengundurkan Diri</option>
                                <option>Sedang Cuti Kuliah</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">IPK / Nilai Akhir</label>
                            <input v-model="eduForm.ipk_gpa" type="number" step="0.01" min="0" max="4"
                                placeholder="Contoh: 3.75"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Penghargaan / Prestasi</label>
                            <textarea v-model="eduForm.academic_achievements" rows="3"
                                placeholder="Contoh: Juara 1 Olimpiade Ekonomi Nasional 2020, Beasiswa Prestasi..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Ijazah &amp; Transkrip (PDF/JPG, maks 5MB)</label>
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png"
                                @change="e => eduForm.ijazah_transcript = e.target.files[0]"
                                class="w-full text-sm text-gray-500" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button @click="submitEdu" :disabled="eduForm.processing" class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                                {{ eduForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                            <button @click="showEduForm = false; editingEduId = null; eduForm.reset()" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl text-sm font-medium">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ===== 職歴タブ ===== -->
                <div v-if="activeTab === 'work'" class="space-y-3">
                    <div v-for="work in works" :key="work.id" class="bg-white rounded-xl shadow-sm p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">{{ work.company_name }}</p>
                                <p class="text-sm text-gray-500">{{ work.department_position }} — {{ work.employment_type }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ work.employment_start_date }} — {{ work.employment_end_date ?? 'Sekarang' }}</p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <button @click="editWork(work)" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg">Edit</button>
                                <button @click="deleteWork(work.id)" class="text-xs bg-red-50 text-red-500 px-3 py-1 rounded-lg">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <button v-if="!showWorkForm" @click="openWorkForm" class="w-full py-4 border-2 border-dashed border-indigo-300 rounded-xl text-indigo-600 font-medium text-sm hover:bg-indigo-50 transition">
                        + Tambah Pengalaman Kerja
                    </button>

                    <div v-if="showWorkForm" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                        <h3 class="font-semibold text-gray-700">{{ editingWorkId ? 'Edit Pekerjaan' : 'Tambah Pekerjaan' }}</h3>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Perusahaan <span class="text-red-500">*</span></label>
                            <input v-model="workForm.company_name" type="text"
                                placeholder="Contoh: PT. Maju Bersama Indonesia"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="workForm.errors.company_name" class="text-red-500 text-xs mt-1">{{ workForm.errors.company_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Alamat Perusahaan</label>
                            <input v-model="workForm.company_address" type="text"
                                placeholder="Contoh: Jl. Gatot Subroto No. 5, Jakarta Selatan"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jabatan <span class="text-red-500">*</span></label>
                            <input v-model="workForm.department_position" type="text"
                                placeholder="Contoh: HR Manager"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="workForm.errors.department_position" class="text-red-500 text-xs mt-1">{{ workForm.errors.department_position }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jenis Pekerjaan <span class="text-red-500">*</span></label>
                            <select v-model="workForm.employment_type" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih Jenis Pekerjaan</option>
                                <option>Full-time</option>
                                <option>Part-time</option>
                                <option>Kontrak</option>
                                <option>Freelance</option>
                                <option>Magang</option>
                            </select>
                            <p v-if="workForm.errors.employment_type" class="text-red-500 text-xs mt-1">{{ workForm.errors.employment_type }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                                <input v-model="workForm.employment_start_date" type="date"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                                <p v-if="workForm.errors.employment_start_date" class="text-red-500 text-xs mt-1">{{ workForm.errors.employment_start_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Selesai</label>
                                <input v-model="workForm.employment_end_date" type="date"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                                <p class="text-xs text-gray-400 mt-1">Kosongkan jika masih bekerja</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Deskripsi Pekerjaan</label>
                            <textarea v-model="workForm.job_description" rows="3"
                                placeholder="Contoh: Bertanggung jawab atas proses rekrutmen, pelatihan karyawan, dan administrasi kepegawaian..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Pencapaian / Prestasi</label>
                            <textarea v-model="workForm.employment_achievements" rows="2"
                                placeholder="Contoh: Berhasil menurunkan tingkat turnover karyawan sebesar 20% dalam 1 tahun..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Alasan Berhenti</label>
                            <textarea v-model="workForm.resignation_reason" rows="2"
                                placeholder="Contoh: Mencari peluang karir yang lebih baik sesuai dengan kompetensi..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Atasan</label>
                            <input v-model="workForm.supervisor_full_name" type="text"
                                placeholder="Contoh: Bapak Ahmad Yusuf"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jabatan Atasan</label>
                            <input v-model="workForm.supervisor_position" type="text"
                                placeholder="Contoh: Direktur HRD"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">No. Telp Aktif Atasan</label>
                            <input v-model="workForm.supervisor_phone" type="text"
                                placeholder="Contoh: 08123456789"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Surat Keterangan Kerja (PDF/JPG, maks 5MB)</label>
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png"
                                @change="e => workForm.employment_certificate = e.target.files[0]"
                                class="w-full text-sm text-gray-500" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button @click="submitWork" :disabled="workForm.processing" class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                                {{ workForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                            <button @click="showWorkForm = false; editingWorkId = null; workForm.reset()" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl text-sm font-medium">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ===== 資格タブ ===== -->
                <div v-if="activeTab === 'certification'" class="space-y-3">
                    <div v-for="cert in certs" :key="cert.id" class="bg-white rounded-xl shadow-sm p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">{{ cert.certificate_name }}</p>
                                <p class="text-sm text-gray-500">{{ cert.issuing_organization }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ cert.issue_date }} — {{ cert.expiration_date ?? 'Tanpa Batas' }}</p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <button @click="editCert(cert)" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg">Edit</button>
                                <button @click="deleteCert(cert.id)" class="text-xs bg-red-50 text-red-500 px-3 py-1 rounded-lg">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <button v-if="!showCertForm" @click="openCertForm" class="w-full py-4 border-2 border-dashed border-indigo-300 rounded-xl text-indigo-600 font-medium text-sm hover:bg-indigo-50 transition">
                        + Tambah Sertifikasi
                    </button>

                    <div v-if="showCertForm" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                        <h3 class="font-semibold text-gray-700">{{ editingCertId ? 'Edit Sertifikasi' : 'Tambah Sertifikasi' }}</h3>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Sertifikat / Lisensi <span class="text-red-500">*</span></label>
                            <input v-model="certForm.certificate_name" type="text"
                                placeholder="Contoh: TOEFL, Sertifikat Manajemen SDM, SIM A"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="certForm.errors.certificate_name" class="text-red-500 text-xs mt-1">{{ certForm.errors.certificate_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Instansi Penerbit <span class="text-red-500">*</span></label>
                            <input v-model="certForm.issuing_organization" type="text"
                                placeholder="Contoh: ETS, BNSP, Kepolisian RI"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="certForm.errors.issuing_organization" class="text-red-500 text-xs mt-1">{{ certForm.errors.issuing_organization }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Terbit <span class="text-red-500">*</span></label>
                                <input v-model="certForm.issue_date" type="date"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                                <p v-if="certForm.errors.issue_date" class="text-red-500 text-xs mt-1">{{ certForm.errors.issue_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Masa Berlaku</label>
                                <input v-model="certForm.expiration_date" type="date"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                                <p class="text-xs text-gray-400 mt-1">Kosongkan jika seumur hidup</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Skor / Level / Tingkatan</label>
                            <input v-model="certForm.certificate_score" type="text"
                                placeholder="Contoh: 850, Level B2, Grade A"
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Keterangan</label>
                            <textarea v-model="certForm.certificate_notes" rows="2"
                                placeholder="Contoh: Sertifikat internasional yang diakui di 150 negara..."
                                class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Lampiran Sertifikat (PDF/JPG, maks 5MB)</label>
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png"
                                @change="e => certForm.certificate_attachment = e.target.files[0]"
                                class="w-full text-sm text-gray-500" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button @click="submitCert" :disabled="certForm.processing" class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                                {{ certForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                            <button @click="showCertForm = false; editingCertId = null; certForm.reset()" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl text-sm font-medium">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 完了ボタン -->
                <div class="pt-2 pb-8">
                    <a href="/applicant/dashboard" class="block w-full text-center bg-gray-700 text-white py-4 rounded-xl text-sm font-medium hover:bg-gray-800 transition">
                        Selesai & Kembali ke Dashboard
                    </a>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>