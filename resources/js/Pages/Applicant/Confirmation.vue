<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    profile: Object,
    educations: Array,
    works: Array,
    certs: Array,
    isFreeAvailable: Boolean,
    daysRemaining: Number,
    price: Number,
    isAlreadySubmitted: Boolean,
    pendingPaymentRequest: Object,
});

const confirmed  = ref(false);
const processing = ref(false);
const errorMsg   = ref('');
const form = useForm({});

const submitFree = () => {
    if (!confirmed.value) return;
    form.post(route('applicant.confirmation.store'));
};

const submitPaid = async () => {
    if (processing.value) return;
    processing.value = true;
    errorMsg.value   = '';
    try {
        const res = await axios.post('/applicant/certification/payment');
        const { snap_token, snap_url } = res.data;
        await loadSnapScript(snap_url);
        window.snap.pay(snap_token, {
            onSuccess: () => { window.location.href = '/applicant/dashboard?payment=success'; },
            onPending: () => { window.location.href = '/applicant/dashboard?payment=pending'; },
            onError:   () => { errorMsg.value = 'Pembayaran gagal. Silakan coba lagi.'; processing.value = false; },
            onClose:   () => { errorMsg.value = 'Pembayaran dibatalkan.'; processing.value = false; },
        });
    } catch (e) {
        errorMsg.value   = 'Terjadi kesalahan. Silakan coba lagi.';
        processing.value = false;
    }
};

const loadSnapScript = (url) => {
    return new Promise((resolve) => {
        if (window.snap) { resolve(); return; }
        const script = document.createElement('script');
        script.src = url; script.onload = resolve;
        document.head.appendChild(script);
    });
};

const submit = () => {
    if (props.isFreeAvailable) submitFree();
    else submitPaid();
};

function formatDate(dateStr) {
    if (!dateStr) return null;
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    return `${dd}/${mm}/${yyyy}`;
}

// 値がなければ「Belum diisi」を返すヘルパー
const val = (v) => v || null;
</script>

<template>
    <Head title="Konfirmasi CV" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Konfirmasi CV</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- タイトル -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                    <p class="text-3xl mb-2">📋</p>
                    <h1 class="text-xl font-bold text-gray-800">Konfirmasi CV Lengkap</h1>
                    <p class="text-sm text-gray-500 mt-1">Silakan periksa semua informasi CV Anda sebelum mengajukan sertifikasi</p>
                </div>

                <!-- 無料/有料バナー -->
                <div v-if="isFreeAvailable"
                     class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-4 flex items-center gap-3 text-white">
                    <span class="text-2xl">🎁</span>
                    <div>
                        <p class="font-bold">Sertifikasi GRATIS!</p>
                        <p class="text-sm opacity-90">Berakhir dalam <strong>{{ daysRemaining }} hari</strong></p>
                    </div>
                    <span class="ml-auto font-bold text-lg">Rp 0</span>
                </div>
                <div v-else class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 flex items-center gap-3">
                    <span class="text-2xl">💳</span>
                    <div>
                        <p class="font-semibold text-indigo-800">Biaya Sertifikasi</p>
                        <p class="text-sm text-indigo-600">Pembayaran melalui QRIS / GoPay / Transfer Bank</p>
                    </div>
                    <span class="ml-auto font-bold text-indigo-700 text-lg">Rp 35.000</span>
                </div>

                <!-- ===== 基本情報 ===== -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                        <span class="text-indigo-600 text-lg">👤</span>
                        <h3 class="font-bold text-gray-800">Informasi Dasar</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template v-for="field in [
                                { label: 'Nama Lengkap',      value: profile?.full_name },
                                { label: 'NIK',               value: profile?.nik, mono: true },
                                { label: 'Jenis Kelamin',     value: profile?.gender },
                                { label: 'Tanggal Lahir', value: formatDate(profile?.birth_date) },
                                { label: 'Kewarganegaraan',   value: profile?.nationality },
                                { label: 'Status Pernikahan', value: profile?.marital_status },
                                { label: 'Nomor Telepon',     value: profile?.phone_number },
                                { label: 'Nomor WhatsApp',    value: profile?.whatsapp_number },
                                { label: 'Alamat KTP',        value: profile?.ktp_address },
                                { label: 'Alamat Saat Ini',   value: profile?.current_address },
                            ]" :key="field.label">
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-400 mb-1">{{ field.label }}</p>
                                    <p v-if="val(field.value)"
                                        class="text-sm font-medium text-gray-800"
                                        :class="field.mono ? 'font-mono' : ''">
                                        {{ field.value }}
                                    </p>
                                    <p v-else class="text-sm text-red-400 flex items-center gap-1">
                                        ✗ Belum diisi
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ===== 学歴 ===== -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                        <span class="text-indigo-600 text-lg">🎓</span>
                        <h3 class="font-bold text-gray-800">Riwayat Pendidikan</h3>
                        <span class="ml-auto text-xs text-gray-400 bg-white px-2 py-1 rounded-full border">{{ educations.length }} data</span>
                    </div>
                    <div class="p-6">
                        <div v-if="educations.length === 0" class="text-sm text-gray-400 text-center py-6">
                            Belum ada data pendidikan
                        </div>
                        <div v-for="(edu, idx) in educations" :key="edu.id"
                            class="mb-6 last:mb-0 border border-gray-100 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-indigo-600">📚 Pendidikan {{ idx + 1 }}</p>
                            </div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template v-for="f in [
                                    { label: 'Tingkat Pendidikan',    value: edu.education_level },
                                    { label: 'Nama Sekolah',          value: edu.school_name },
                                    { label: 'Alamat Sekolah',        value: edu.school_location },
                                    { label: 'Jurusan / Program Studi', value: edu.degree_name },
                                    { label: 'Tanggal Masuk', value: formatDate(edu.enrollment_date) },
                                    { label: 'Tanggal Lulus', value: formatDate(edu.graduation_date) },
                                    { label: 'Status Kelulusan',      value: edu.graduation_status },
                                    { label: 'IPK / Nilai Akhir',     value: edu.ipk_gpa },
                                    { label: 'Penghargaan / Prestasi',value: edu.academic_achievements },
                                ]" :key="f.label">
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">{{ f.label }}</p>
                                        <p v-if="val(f.value)" class="text-sm font-medium text-gray-800">{{ f.value }}</p>
                                        <p v-else class="text-sm text-red-400">✗ Belum diisi</p>
                                    </div>
                                </template>
                                <!-- Ijazahファイル -->
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-400 mb-1">Ijazah &amp; Transkrip</p>
                                    <p v-if="edu.ijazah_transcript" class="text-sm text-green-600 font-medium">✅ File terlampir</p>
                                    <p v-else class="text-sm text-red-400">✗ Belum diisi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== 職歴 ===== -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                        <span class="text-indigo-600 text-lg">💼</span>
                        <h3 class="font-bold text-gray-800">Riwayat Pekerjaan</h3>
                        <span class="ml-auto text-xs text-gray-400 bg-white px-2 py-1 rounded-full border">{{ works.length }} data</span>
                    </div>
                    <div class="p-6">
                        <div v-if="works.length === 0" class="text-sm text-gray-400 text-center py-6">
                            Belum ada data pekerjaan
                        </div>
                        <div v-for="(work, idx) in works" :key="work.id"
                            class="mb-6 last:mb-0 border border-gray-100 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-indigo-600">💼 Pekerjaan {{ idx + 1 }}</p>
                            </div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template v-for="f in [
                                    { label: 'Nama Perusahaan',       value: work.company_name },
                                    { label: 'Alamat Perusahaan',     value: work.company_address },
                                    { label: 'Jabatan / Posisi',      value: work.department_position },
                                    { label: 'Jenis Pekerjaan',       value: work.employment_type },
                                    { label: 'Tanggal Mulai',   value: formatDate(work.employment_start_date) },
                                    { label: 'Tanggal Selesai', value: formatDate(work.employment_end_date) },
                                    { label: 'Deskripsi Pekerjaan',   value: work.job_description },
                                    { label: 'Pencapaian / Prestasi', value: work.employment_achievements },
                                    { label: 'Alasan Berhenti',       value: work.resignation_reason },
                                    { label: 'Nama Atasan',           value: work.supervisor_full_name },
                                    { label: 'Jabatan Atasan',        value: work.supervisor_position },
                                    { label: 'No. Telp Atasan',       value: work.supervisor_phone },
                                ]" :key="f.label">
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">{{ f.label }}</p>
                                        <p v-if="val(f.value)" class="text-sm font-medium text-gray-800">{{ f.value }}</p>
                                        <p v-else class="text-sm text-red-400">✗ Belum diisi</p>
                                    </div>
                                </template>
                                <!-- 雇用証明書ファイル -->
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-400 mb-1">Surat Keterangan Kerja</p>
                                    <p v-if="work.employment_certificate" class="text-sm text-green-600 font-medium">✅ File terlampir</p>
                                    <p v-else class="text-sm text-red-400">✗ Belum diisi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== 資格 ===== -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                        <span class="text-indigo-600 text-lg">🏅</span>
                        <h3 class="font-bold text-gray-800">Sertifikat &amp; Kualifikasi</h3>
                        <span class="ml-auto text-xs text-gray-400 bg-white px-2 py-1 rounded-full border">{{ certs.length }} data</span>
                    </div>
                    <div class="p-6">
                        <div v-if="certs.length === 0" class="text-sm text-gray-400 text-center py-6">
                            Belum ada data sertifikat
                        </div>
                        <div v-for="(cert, idx) in certs" :key="cert.id"
                            class="mb-6 last:mb-0 border border-gray-100 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-indigo-600">🏅 Sertifikat {{ idx + 1 }}</p>
                            </div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template v-for="f in [
                                    { label: 'Nama Sertifikat',       value: cert.certificate_name },
                                    { label: 'Instansi Penerbit',     value: cert.issuing_organization },
                                    { label: 'Tanggal Terbit', value: formatDate(cert.issue_date) },
                                    { label: 'Masa Berlaku',   value: formatDate(cert.expiration_date) },
                                    { label: 'Skor / Level / Tingkatan', value: cert.certificate_score },
                                    { label: 'Keterangan',            value: cert.certificate_notes },
                                ]" :key="f.label">
                                    <div class="bg-gray-50 rounded-xl p-3">
                                        <p class="text-xs text-gray-400 mb-1">{{ f.label }}</p>
                                        <p v-if="val(f.value)" class="text-sm font-medium text-gray-800">{{ f.value }}</p>
                                        <p v-else class="text-sm text-red-400">✗ Belum diisi</p>
                                    </div>
                                </template>
                                <!-- 資格添付ファイル -->
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <p class="text-xs text-gray-400 mb-1">Lampiran Sertifikat</p>
                                    <p v-if="cert.certificate_attachment" class="text-sm text-green-600 font-medium">✅ File terlampir</p>
                                    <p v-else class="text-sm text-red-400">✗ Belum diisi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 注意事項 -->
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                    <h3 class="font-bold text-red-700 mb-3 flex items-center gap-2">
                        ⚠️ Hal Penting Sebelum Mengajukan
                    </h3>
                    <ul class="space-y-2 text-sm text-red-700">
                        <li>• Setelah mengajukan, Anda <strong>tidak dapat mengajukan lagi selama 3 bulan</strong></li>
                        <li>• Pastikan semua informasi yang diisi adalah <strong>benar dan akurat</strong></li>
                        <li>• Data yang salah akan mempengaruhi <strong>penilaian sertifikasi Anda</strong></li>
                        <li>• Proses verifikasi memakan waktu <strong>3-5 hari kerja</strong></li>
                    </ul>
                </div>

                <!-- エラーメッセージ -->
                <div v-if="errorMsg"
                     class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm text-center">
                    {{ errorMsg }}
                </div>

                <!-- pending_payment：再決済 -->
                <div v-if="pendingPaymentRequest && !isAlreadySubmitted"
                     class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 text-center">
                    <p class="text-yellow-700 font-semibold text-lg">⏳ Menunggu Pembayaran</p>
                    <p class="text-sm text-yellow-600 mt-1">Silakan selesaikan pembayaran untuk melanjutkan proses sertifikasi.</p>
                    <div class="mt-4 space-y-2">
                        <button @click="submitPaid" :disabled="processing"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-bold py-3 rounded-xl transition text-sm">
                            {{ processing ? 'Memproses...' : '💳 Bayar Sekarang Rp 35.000' }}
                        </button>
                        <Link href="/applicant/dashboard" class="block text-sm text-gray-500 hover:underline mt-2">
                            → Kembali ke Dashboard
                        </Link>
                    </div>
                </div>

                <!-- 最終確認（新規申請） -->
                <div v-else-if="!isAlreadySubmitted && !pendingPaymentRequest"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 text-center">✅ Konfirmasi Final</h3>
                    <label class="flex items-start gap-3 cursor-pointer bg-gray-50 rounded-xl p-4">
                        <input type="checkbox" v-model="confirmed"
                            class="mt-1 w-5 h-5 text-indigo-600 rounded cursor-pointer" />
                        <span class="text-sm text-gray-700">
                            Saya telah memeriksa semua informasi CV dan memastikan data yang diisi adalah benar
                        </span>
                    </label>
                    <div class="flex gap-3 mt-6">
                        <Link href="/applicant/cv"
                            class="flex-1 text-center border border-gray-300 text-gray-600 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                            ← Kembali ke CV
                        </Link>
                        <button @click="submit"
                            :disabled="!confirmed || processing || form.processing"
                            class="flex-1 py-3 rounded-xl text-white text-sm font-semibold transition"
                            :class="confirmed && !processing ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'">
                            <span v-if="processing || form.processing">Memproses...</span>
                            <span v-else-if="isFreeAvailable">✅ Kirim (GRATIS)</span>
                            <span v-else>💳 Bayar Rp 35.000 &amp; Kirim</span>
                        </button>
                    </div>
                </div>

                <!-- 申請中 -->
                <div v-else class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-center">
                    <p class="text-blue-700 font-semibold text-lg">✅ Pengajuan sedang diproses</p>
                    <p class="text-sm text-blue-500 mt-1">Tim HRI sedang memverifikasi data Anda.</p>
                    <Link href="/applicant/dashboard" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">
                        → Kembali ke Dashboard
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>