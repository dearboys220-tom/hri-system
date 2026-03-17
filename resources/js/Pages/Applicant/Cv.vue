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

const activeTab = ref('education');
const showEduForm = ref(false);
const showWorkForm = ref(false);
const showCertForm = ref(false);
const editingEduId = ref(null);
const editingWorkId = ref(null);
const editingCertId = ref(null);

// ===== 学歴フォーム =====
const eduForm = useForm({
    level: '',
    school: '',
    school_location: '',
    major: '',
    enrollment_date: '',
    graduation_date: '',
    graduation_status: '',
    gpa: '',
    achievements: '',
    ijazah_transcript: null,
});

const openEduForm = () => {
    editingEduId.value = null;
    eduForm.reset();
    showEduForm.value = true;
};

const editEdu = (edu) => {
    editingEduId.value = edu.id;
    eduForm.level = edu.level ?? '';
    eduForm.school = edu.school ?? '';
    eduForm.school_location = edu.school_location ?? '';
    eduForm.major = edu.major ?? '';
    eduForm.enrollment_date = edu.enrollment_date ?? '';
    eduForm.graduation_date = edu.graduation_date ?? '';
    eduForm.graduation_status = edu.graduation_status ?? '';
    eduForm.gpa = edu.gpa ?? '';
    eduForm.achievements = edu.achievements ?? '';
    showEduForm.value = true;
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
};

const submitEdu = () => {
    if (editingEduId.value) {
        eduForm.post(route('applicant.cv.education.update', editingEduId.value), {
            forceFormData: true,
            onSuccess: () => { eduForm.reset(); showEduForm.value = false; editingEduId.value = null; },
        });
    } else {
        eduForm.post(route('applicant.cv.education.store'), {
            forceFormData: true,
            onSuccess: () => { eduForm.reset(); showEduForm.value = false; },
        });
    }
};

const deleteEdu = (id) => {
    if (confirm('Hapus data pendidikan ini?')) {
        router.delete(route('applicant.cv.education.destroy', id));
    }
};

// ===== 職歴フォーム =====
const workForm = useForm({
    company: '',
    company_address: '',
    position: '',
    employment_type: '',
    start_date: '',
    end_date: '',
    duties: '',
    resignation_reason: '',
    achievements: '',
    supervisor_name: '',
    supervisor_position: '',
    supervisor_contact: '',
    employment_certificate: null,
});

const openWorkForm = () => {
    editingWorkId.value = null;
    workForm.reset();
    showWorkForm.value = true;
};

const editWork = (work) => {
    editingWorkId.value = work.id;
    workForm.company = work.company ?? '';
    workForm.company_address = work.company_address ?? '';
    workForm.position = work.position ?? '';
    workForm.employment_type = work.employment_type ?? '';
    workForm.start_date = work.start_date ?? '';
    workForm.end_date = work.end_date ?? '';
    workForm.duties = work.duties ?? '';
    workForm.resignation_reason = work.resignation_reason ?? '';
    workForm.achievements = work.achievements ?? '';
    workForm.supervisor_name = work.supervisor_name ?? '';
    workForm.supervisor_position = work.supervisor_position ?? '';
    workForm.supervisor_contact = work.supervisor_contact ?? '';
    showWorkForm.value = true;
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
};

const submitWork = () => {
    if (editingWorkId.value) {
        workForm.post(route('applicant.cv.work.update', editingWorkId.value), {
            forceFormData: true,
            onSuccess: () => { workForm.reset(); showWorkForm.value = false; editingWorkId.value = null; },
        });
    } else {
        workForm.post(route('applicant.cv.work.store'), {
            forceFormData: true,
            onSuccess: () => { workForm.reset(); showWorkForm.value = false; },
        });
    }
};

const deleteWork = (id) => {
    if (confirm('Hapus data pekerjaan ini?')) {
        router.delete(route('applicant.cv.work.destroy', id));
    }
};

// ===== 資格フォーム =====
const certForm = useForm({
    name: '',
    organization: '',
    issued_date: '',
    valid_until: '',
    certificate_score: '',
    notes: '',
    certificate_file: null,
});

const openCertForm = () => {
    editingCertId.value = null;
    certForm.reset();
    showCertForm.value = true;
};

const editCert = (cert) => {
    editingCertId.value = cert.id;
    certForm.name = cert.name ?? '';
    certForm.organization = cert.organization ?? '';
    certForm.issued_date = cert.issued_date ?? '';
    certForm.valid_until = cert.valid_until ?? '';
    certForm.certificate_score = cert.certificate_score ?? '';
    certForm.notes = cert.notes ?? '';
    showCertForm.value = true;
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
};

const submitCert = () => {
    if (editingCertId.value) {
        certForm.post(route('applicant.cv.certification.update', editingCertId.value), {
            forceFormData: true,
            onSuccess: () => { certForm.reset(); showCertForm.value = false; editingCertId.value = null; },
        });
    } else {
        certForm.post(route('applicant.cv.certification.store'), {
            forceFormData: true,
            onSuccess: () => { certForm.reset(); showCertForm.value = false; },
        });
    }
};

const deleteCert = (id) => {
    if (confirm('Hapus data sertifikasi ini?')) {
        router.delete(route('applicant.cv.certification.destroy', id));
    }
};
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
                <div class="flex border-b border-gray-200 bg-white rounded-t-lg">
                    <button
                        v-for="tab in [
                            { key: 'education', label: 'Pendidikan' },
                            { key: 'work', label: 'Pengalaman' },
                            { key: 'certification', label: 'Sertifikasi' },
                        ]"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        class="flex-1 py-3 text-sm font-medium border-b-2 transition"
                        :class="activeTab === tab.key
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500'"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- ===== 学歴タブ ===== -->
                <div v-if="activeTab === 'education'" class="space-y-3">

                    <!-- 登録済みカード -->
                    <div
                        v-for="edu in educations"
                        :key="edu.id"
                        class="bg-white rounded-xl shadow-sm p-4"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-800">{{ edu.school }}</p>
                                <p class="text-sm text-gray-500">{{ edu.level }}{{ edu.major ? ' • ' + edu.major : '' }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ edu.enrollment_date ?? '?' }} ～ {{ edu.graduation_date ?? 'Sekarang' }}
                                </p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <button
                                    @click="editEdu(edu)"
                                    class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg"
                                >Edit</button>
                                <button
                                    @click="deleteEdu(edu.id)"
                                    class="text-xs bg-red-50 text-red-500 px-3 py-1 rounded-lg"
                                >Hapus</button>
                            </div>
                        </div>
                    </div>

                    <!-- 追加ボタン -->
                    <button
                        v-if="!showEduForm"
                        @click="openEduForm"
                        class="w-full py-4 border-2 border-dashed border-indigo-300 rounded-xl text-indigo-600 font-medium text-sm hover:bg-indigo-50 transition"
                    >
                        ＋ Tambah Pendidikan
                    </button>

                    <!-- 入力フォーム -->
                    <div v-if="showEduForm" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                        <h3 class="font-semibold text-gray-700">
                            {{ editingEduId ? 'Edit Pendidikan' : 'Tambah Pendidikan' }}
                        </h3>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                            <select v-model="eduForm.level" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih</option>
                                <option>SD</option><option>SMP</option><option>SMA/SMK</option>
                                <option>D1</option><option>D2</option><option>D3</option>
                                <option>S1</option><option>S2</option><option>S3</option>
                            </select>
                            <p v-if="eduForm.errors.level" class="text-red-500 text-xs mt-1">{{ eduForm.errors.level }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Sekolah <span class="text-red-500">*</span></label>
                            <input v-model="eduForm.school" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="eduForm.errors.school" class="text-red-500 text-xs mt-1">{{ eduForm.errors.school }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Alamat Sekolah</label>
                            <input v-model="eduForm.school_location" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jurusan</label>
                            <input v-model="eduForm.major" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Masuk</label>
                                <input v-model="eduForm.enrollment_date" type="date" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Lulus</label>
                                <input v-model="eduForm.graduation_date" type="date" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Status Kelulusan</label>
                            <select v-model="eduForm.graduation_status" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih</option>
                                <option>Lulus</option>
                                <option>Tidak Lulus</option>
                                <option>Masih Bersekolah</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">IPK / Nilai Akhir</label>
                            <input v-model="eduForm.gpa" type="number" step="0.01" min="0" max="4" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Penghargaan / Prestasi</label>
                            <textarea v-model="eduForm.achievements" rows="3" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Ijazah & Transkrip (PDF/JPG, maks 5MB)</label>
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png"
                                @change="e => eduForm.ijazah_transcript = e.target.files[0]"
                                class="w-full text-sm text-gray-500" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                @click="submitEdu"
                                :disabled="eduForm.processing"
                                class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 transition"
                            >
                                {{ eduForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                            <button
                                @click="showEduForm = false; editingEduId = null; eduForm.reset()"
                                class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl text-sm font-medium"
                            >
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
                                <p class="font-semibold text-gray-800">{{ work.company }}</p>
                                <p class="text-sm text-gray-500">{{ work.position }} • {{ work.employment_type }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ work.start_date }} ～ {{ work.end_date ?? 'Sekarang' }}
                                </p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <button @click="editWork(work)" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg">Edit</button>
                                <button @click="deleteWork(work.id)" class="text-xs bg-red-50 text-red-500 px-3 py-1 rounded-lg">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <button
                        v-if="!showWorkForm"
                        @click="openWorkForm"
                        class="w-full py-4 border-2 border-dashed border-indigo-300 rounded-xl text-indigo-600 font-medium text-sm hover:bg-indigo-50 transition"
                    >
                        ＋ Tambah Pengalaman Kerja
                    </button>

                    <div v-if="showWorkForm" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                        <h3 class="font-semibold text-gray-700">
                            {{ editingWorkId ? 'Edit Pekerjaan' : 'Tambah Pekerjaan' }}
                        </h3>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Perusahaan <span class="text-red-500">*</span></label>
                            <input v-model="workForm.company" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="workForm.errors.company" class="text-red-500 text-xs mt-1">{{ workForm.errors.company }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Alamat Perusahaan</label>
                            <input v-model="workForm.company_address" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jabatan <span class="text-red-500">*</span></label>
                            <input v-model="workForm.position" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="workForm.errors.position" class="text-red-500 text-xs mt-1">{{ workForm.errors.position }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jenis Pekerjaan <span class="text-red-500">*</span></label>
                            <select v-model="workForm.employment_type" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm">
                                <option value="">Pilih</option>
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
                                <input v-model="workForm.start_date" type="date" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                                <p v-if="workForm.errors.start_date" class="text-red-500 text-xs mt-1">{{ workForm.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Selesai</label>
                                <input v-model="workForm.end_date" type="date" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Deskripsi Pekerjaan</label>
                            <textarea v-model="workForm.duties" rows="3" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Pencapaian / Prestasi</label>
                            <textarea v-model="workForm.achievements" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Alasan Berhenti</label>
                            <textarea v-model="workForm.resignation_reason" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Atasan</label>
                            <input v-model="workForm.supervisor_name" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Jabatan Atasan</label>
                            <input v-model="workForm.supervisor_position" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">No. Telp Atasan</label>
                            <input v-model="workForm.supervisor_contact" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Surat Keterangan Kerja (PDF/JPG, maks 5MB)</label>
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png"
                                @change="e => workForm.employment_certificate = e.target.files[0]"
                                class="w-full text-sm text-gray-500" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                @click="submitWork"
                                :disabled="workForm.processing"
                                class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 transition"
                            >
                                {{ workForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                            <button
                                @click="showWorkForm = false; editingWorkId = null; workForm.reset()"
                                class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl text-sm font-medium"
                            >
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
                                <p class="font-semibold text-gray-800">{{ cert.name }}</p>
                                <p class="text-sm text-gray-500">{{ cert.organization }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ cert.issued_date }} ～ {{ cert.valid_until ?? 'Tanpa Batas' }}
                                </p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <button @click="editCert(cert)" class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg">Edit</button>
                                <button @click="deleteCert(cert.id)" class="text-xs bg-red-50 text-red-500 px-3 py-1 rounded-lg">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <button
                        v-if="!showCertForm"
                        @click="openCertForm"
                        class="w-full py-4 border-2 border-dashed border-indigo-300 rounded-xl text-indigo-600 font-medium text-sm hover:bg-indigo-50 transition"
                    >
                        ＋ Tambah Sertifikasi
                    </button>

                    <div v-if="showCertForm" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                        <h3 class="font-semibold text-gray-700">
                            {{ editingCertId ? 'Edit Sertifikasi' : 'Tambah Sertifikasi' }}
                        </h3>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Sertifikat <span class="text-red-500">*</span></label>
                            <input v-model="certForm.name" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="certForm.errors.name" class="text-red-500 text-xs mt-1">{{ certForm.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Instansi Penerbit <span class="text-red-500">*</span></label>
                            <input v-model="certForm.organization" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            <p v-if="certForm.errors.organization" class="text-red-500 text-xs mt-1">{{ certForm.errors.organization }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Tanggal Terbit <span class="text-red-500">*</span></label>
                                <input v-model="certForm.issued_date" type="date" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                                <p v-if="certForm.errors.issued_date" class="text-red-500 text-xs mt-1">{{ certForm.errors.issued_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Masa Berlaku</label>
                                <input v-model="certForm.valid_until" type="date" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Skor / Level</label>
                            <input v-model="certForm.certificate_score" type="text" placeholder="Contoh: 850, Level B2" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Keterangan</label>
                            <textarea v-model="certForm.notes" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-3 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Lampiran Sertifikat (PDF/JPG, maks 5MB)</label>
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png"
                                @change="e => certForm.certificate_file = e.target.files[0]"
                                class="w-full text-sm text-gray-500" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                @click="submitCert"
                                :disabled="certForm.processing"
                                class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 transition"
                            >
                                {{ certForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                            <button
                                @click="showCertForm = false; editingCertId = null; certForm.reset()"
                                class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl text-sm font-medium"
                            >
                                Batal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 完了ボタン -->
                <div class="pt-2 pb-8">
                    <a href="/applicant/dashboard"
                       class="block w-full text-center bg-gray-700 text-white py-4 rounded-xl text-sm font-medium hover:bg-gray-800 transition">
                        Selesai & Kembali ke Dashboard
                    </a>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>