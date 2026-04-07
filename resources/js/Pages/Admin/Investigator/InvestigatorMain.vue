<script setup>
import Button from '@/Components/Admin/Components/Button.vue';
import Card from '@/Components/Admin/Components/Card.vue';
import Divider from '@/Components/Admin/Components/Divider.vue';
import InfoField from '@/Components/Admin/Components/InfoField.vue';
import SectionHeader from '@/Components/Admin/Components/SectionHeader.vue';
import StatusSelect from '@/Components/Admin/Components/StatusSelect.vue';
import ImageViewer from '@/Components/Admin/Components/ImageViewer.vue';
import InvestReviewLayout from '@/Components/Admin/Layout/InvestReviewLayout.vue';
import AiChatWidget from '@/Components/AiChatWidget.vue';
import { MagnifyingGlassIcon, UserCircleIcon } from '@heroicons/vue/24/outline';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: (h, page) =>
        h(InvestReviewLayout, {
            title: 'Tim Investigasi',
            subtitle: 'Penyelidikan & Manajemen Kasus'
        }, () => page)
});

const props = defineProps({
    cases:      { type: Array,  default: () => [] },
    detail:     { type: Object, default: null },
    selectedId: { type: Number, default: null },
});

// ---- フローティングチャット ----
const chatOpen      = ref(false);
const currentCaseNo = computed(() => props.detail?.case_no ?? '');

// ---- 左パネル ----
const search = ref('');
const filteredCases = computed(() =>
    props.cases.filter(c =>
        c.name?.toLowerCase().includes(search.value.toLowerCase()) ||
        c.member_id?.toLowerCase().includes(search.value.toLowerCase())
    )
);

function selectCase(id) {
    router.get(route('admin.investigator.index'), { id }, { preserveScroll: true });
}

// ---- 初期値取得ヘルパー ----
function initStatus(itemName) {
    return props.detail?.investigation_map?.[itemName]?.validity ?? '';
}
function initNotes(itemName) {
    return props.detail?.investigation_map?.[itemName]?.notes ?? '';
}

// ---- ステータス & メモ管理 ----
const profileStatuses = ref({});
const profileMemos    = ref({});
const eduStatuses     = ref({});
const eduMemos        = ref({});
const workStatuses    = ref({});
const workMemos       = ref({});
const certStatuses    = ref({});
const certMemos       = ref({});
const conductStatuses = ref({});
const conductMemos    = ref({});

const investigationNotes = ref('');
const returnReason       = ref('');

watch(() => props.detail, (d) => {
    investigationNotes.value = d?.investigation_notes ?? '';
    returnReason.value = '';

    if (!d) return;

    const profileKeys = [
        'full_name','nik','ktp_address','gender','marital_status',
        'nationality','birth_date','current_address','phone_number','whatsapp_number'
    ];
    profileKeys.forEach(k => {
        profileStatuses.value[k] = initStatus(k);
        profileMemos.value[k]    = initNotes(k);
    });

    d.educations?.forEach((_, i) => {
        ['school_name','education_level','school_location','degree_name',
         'enrollment_date','graduation_date','graduation_status','ipk_gpa','academic_achievements'].forEach(k => {
            eduStatuses.value[`${i}_${k}`] = initStatus(`edu_${i}_${k}`);
            eduMemos.value[`${i}_${k}`]    = initNotes(`edu_${i}_${k}`);
        });
    });

    d.works?.forEach((_, i) => {
        ['company_name','company_address','department_position','employment_type',
         'employment_start_date','employment_end_date','job_description',
         'supervisor_full_name','supervisor_position','supervisor_phone'].forEach(k => {
            workStatuses.value[`${i}_${k}`] = initStatus(`work_${i}_${k}`);
            workMemos.value[`${i}_${k}`]    = initNotes(`work_${i}_${k}`);
        });
    });

    d.certifications?.forEach((_, i) => {
        ['certificate_name','issuing_organization','issue_date','expiration_date','certificate_score'].forEach(k => {
            certStatuses.value[`${i}_${k}`] = initStatus(`cert_${i}_${k}`);
            certMemos.value[`${i}_${k}`]    = initNotes(`cert_${i}_${k}`);
        });
    });

    d.works?.forEach((_, i) => {
        ['stabilitas_kehadiran','kepatuhan_instruksi','kerja_sama_tim','sikap_kerja','pelanggaran_disiplin'].forEach(k => {
            conductStatuses.value[`${i}_${k}`] = initStatus(`conduct_${i}_${k}`);
            conductMemos.value[`${i}_${k}`]    = initNotes(`conduct_${i}_${k}`);
        });
    });
}, { immediate: true });

// ---- 画像ビューアー ----
const showImage    = ref(false);
const viewImageSrc = ref('');
function openImage(src) {
    viewImageSrc.value = src;
    showImage.value    = true;
}

// ---- 保存用データ構築 ----
function buildItems() {
    const items = [];
    const d = props.detail;
    if (!d) return items;

    const profileKeys = [
        'full_name','nik','ktp_address','gender','marital_status',
        'nationality','birth_date','current_address','phone_number','whatsapp_number'
    ];
    profileKeys.forEach(k => {
        if (profileStatuses.value[k]) {
            items.push({ item_name: k, category: 'basic_info', validity: profileStatuses.value[k], notes: profileMemos.value[k] ?? '' });
        }
    });

    d.educations?.forEach((_, i) => {
        ['school_name','education_level','school_location','degree_name',
         'enrollment_date','graduation_date','graduation_status','ipk_gpa','academic_achievements'].forEach(k => {
            if (eduStatuses.value[`${i}_${k}`]) {
                items.push({ item_name: `edu_${i}_${k}`, category: 'education', validity: eduStatuses.value[`${i}_${k}`], notes: eduMemos.value[`${i}_${k}`] ?? '' });
            }
        });
    });

    d.works?.forEach((_, i) => {
        ['company_name','company_address','department_position','employment_type',
         'employment_start_date','employment_end_date','job_description',
         'supervisor_full_name','supervisor_position','supervisor_phone'].forEach(k => {
            if (workStatuses.value[`${i}_${k}`]) {
                items.push({ item_name: `work_${i}_${k}`, category: 'work', validity: workStatuses.value[`${i}_${k}`], notes: workMemos.value[`${i}_${k}`] ?? '' });
            }
        });
    });

    d.certifications?.forEach((_, i) => {
        ['certificate_name','issuing_organization','issue_date','expiration_date','certificate_score'].forEach(k => {
            if (certStatuses.value[`${i}_${k}`]) {
                items.push({ item_name: `cert_${i}_${k}`, category: 'certification', validity: certStatuses.value[`${i}_${k}`], notes: certMemos.value[`${i}_${k}`] ?? '' });
            }
        });
    });

    d.works?.forEach((_, i) => {
        ['stabilitas_kehadiran','kepatuhan_instruksi','kerja_sama_tim','sikap_kerja','pelanggaran_disiplin'].forEach(k => {
            const v = conductStatuses.value[`${i}_${k}`];
            if (v) {
                items.push({
                    item_name: `conduct_${i}_${k}`,
                    category:  'conduct',
                    validity:  v,
                    notes:     conductMemos.value[`${i}_${k}`] ?? '',
                });
            }
        });
    });

    return items;
}

// ---- アクション ----
const saving = ref(false);

function saveAll() {
    if (!props.detail) return;
    saving.value = true;
    router.post(
        route('admin.investigator.save', props.detail.id),
        { investigation_notes: investigationNotes.value, items: buildItems() },
        { onFinish: () => { saving.value = false; } }
    );
}

function sendComplete() {
    if (!props.detail) return;
    if (!confirm('Kirim ke Tim Admin? AI akan melakukan scoring otomatis. Pastikan semua data sudah diperiksa.')) return;
    router.post(route('admin.investigator.complete', props.detail.id));
}

function sendCorrection() {
    if (!props.detail) return;
    if (!returnReason.value.trim()) {
        alert('Isi detail koreksi terlebih dahulu.');
        return;
    }
    router.post(
        route('admin.investigator.correction', props.detail.id),
        { return_reason: returnReason.value }
    );
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-10 gap-6 items-start">

        <!-- ===== 左パネル：案件一覧 ===== -->
        <Card class="md:col-span-3 self-start md:sticky md:top-28 h-fit">
            <SectionHeader title="Daftar Kasus" subtitle="Pilih kasus untuk diperiksa" />

            <div class="flex gap-2 mb-4 mt-4">
                <div class="relative flex-1">
                    <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm"
                    />
                </div>
                <Button variant="secondary" size="sm" @click="search = ''">Reset</Button>
            </div>

            <Divider />

            <div class="space-y-3 mt-4 max-h-[400px] overflow-y-auto pr-1">
                <div v-if="filteredCases.length === 0" class="text-center py-8 text-slate-400 text-sm">
                    Tidak ada kasus
                </div>
                <div
                    v-for="c in filteredCases"
                    :key="c.id"
                    @click="selectCase(c.id)"
                    class="p-4 rounded-xl border cursor-pointer transition"
                    :class="c.id === selectedId
                        ? 'border-admin-primary-600 bg-admin-primary-50'
                        : 'border-slate-200 hover:border-admin-primary-400 hover:bg-admin-primary-50'"
                >
                    <div class="flex items-center gap-2">
                        <UserCircleIcon class="w-5 h-5 text-admin-primary-600 shrink-0" />
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ c.name || c.member_id }}</p>
                            <p v-if="c.name" class="text-xs text-admin-primary-600 font-mono">{{ c.member_id }}</p>
                            <p class="text-xs text-slate-500">{{ c.submitted_at }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Card>

        <!-- ===== 右パネル：案件詳細 ===== -->
        <Card class="md:col-span-7">

            <div v-if="!detail" class="py-20 text-center text-slate-400">
                <p class="text-lg">👈 Pilih kasus dari daftar</p>
                <p class="text-sm mt-2">Belum ada kasus yang dipilih</p>
            </div>

            <template v-else>

                <!-- ===== SECTION 1: PROFIL ===== -->
                <SectionHeader title="Profil Anggota" subtitle="Verifikasi data pribadi" />

                <div class="mt-6">
                    <div class="flex justify-center mb-6">
                        <div class="relative w-36 cursor-pointer group"
                            @click="detail.profile?.profile_photo && openImage('/storage/' + detail.profile.profile_photo)">
                            <img v-if="detail.profile?.profile_photo"
                                :src="'/storage/' + detail.profile.profile_photo"
                                class="w-full aspect-square rounded-2xl object-cover border-4 border-white shadow-lg" />
                            <div v-else class="w-full aspect-square rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 text-4xl">
                                👤
                            </div>
                        </div>
                    </div>

                    <div v-if="detail.profile?.ktp_card" class="mb-6 p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <p class="text-xs text-slate-500 mb-2">Foto KTP</p>
                        <img :src="'/storage/' + detail.profile.ktp_card"
                            class="h-32 rounded-lg cursor-pointer"
                            @click="openImage('/storage/' + detail.profile.ktp_card)" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <template v-for="field in [
                            { key: 'full_name',       label: 'Nama Lengkap',      value: detail.profile?.full_name },
                            { key: 'nik',             label: 'NIK',               value: detail.profile?.nik },
                            { key: 'ktp_address',     label: 'Alamat KTP',        value: detail.profile?.ktp_address },
                            { key: 'gender',          label: 'Jenis Kelamin',     value: detail.profile?.gender },
                            { key: 'marital_status',  label: 'Status Pernikahan', value: detail.profile?.marital_status },
                            { key: 'nationality',     label: 'Kewarganegaraan',   value: detail.profile?.nationality },
                            { key: 'birth_date',      label: 'Tanggal Lahir',     value: detail.profile?.birth_date },
                            { key: 'current_address', label: 'Alamat Saat Ini',   value: detail.profile?.current_address },
                            { key: 'phone_number',    label: 'Telepon',           value: detail.profile?.phone_number },
                            { key: 'whatsapp_number', label: 'WhatsApp',          value: detail.profile?.whatsapp_number },
                        ]" :key="field.key">
                            <div class="space-y-2">
                                <InfoField :label="field.label" :value="field.value ?? '-'" />
                                <StatusSelect
                                    v-model="profileStatuses[field.key]"
                                    v-model:memoValue="profileMemos[field.key]"
                                />
                            </div>
                        </template>
                    </div>
                </div>

                <Divider class="my-8" />

                <!-- ===== SECTION 2: PENDIDIKAN ===== -->
                <SectionHeader title="Riwayat Pendidikan" subtitle="Verifikasi data pendidikan" />

                <div class="mt-6 space-y-6">
                    <div v-if="detail.educations?.length === 0" class="text-slate-400 text-sm">
                        Tidak ada data pendidikan
                    </div>
                    <div v-for="(edu, i) in detail.educations" :key="edu.id"
                        class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50">
                        <h3 class="font-semibold text-slate-700 mb-4">Pendidikan {{ i + 1 }}</h3>

                        <div v-if="edu.ijazah_transcript" class="mb-4 p-3 bg-white rounded-xl border border-slate-200">
                            <p class="text-xs text-slate-500 mb-2">Ijazah &amp; Transkrip</p>
                            <a :href="'/storage/' + edu.ijazah_transcript" target="_blank"
                                class="text-sm text-indigo-600 hover:underline">
                                📄 Lihat Dokumen
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="f in [
                                { key: 'school_name',           label: 'Nama Sekolah / Institusi', value: edu.school_name },
                                { key: 'education_level',       label: 'Tingkat Pendidikan',       value: edu.education_level },
                                { key: 'school_location',       label: 'Alamat Sekolah',           value: edu.school_location },
                                { key: 'degree_name',           label: 'Jurusan / Program Studi',  value: edu.degree_name },
                                { key: 'enrollment_date',       label: 'Tanggal Masuk',            value: edu.enrollment_date },
                                { key: 'graduation_date',       label: 'Tanggal Lulus',            value: edu.graduation_date },
                                { key: 'graduation_status',     label: 'Status Kelulusan',         value: edu.graduation_status },
                                { key: 'ipk_gpa',               label: 'IPK / Nilai Akhir',        value: edu.ipk_gpa },
                                { key: 'academic_achievements', label: 'Penghargaan / Prestasi',   value: edu.academic_achievements },
                            ]" :key="f.key" class="space-y-2">
                                <InfoField :label="f.label" :value="f.value ?? '-'" />
                                <StatusSelect
                                    v-model="eduStatuses[`${i}_${f.key}`]"
                                    v-model:memoValue="eduMemos[`${i}_${f.key}`]"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <Divider class="my-8" />

                <!-- ===== SECTION 3: PENGALAMAN KERJA ===== -->
                <SectionHeader title="Pengalaman Kerja" subtitle="Verifikasi riwayat pekerjaan" />

                <div class="mt-6 space-y-6">
                    <div v-if="detail.works?.length === 0" class="text-slate-400 text-sm">
                        Tidak ada data pengalaman kerja
                    </div>
                    <div v-for="(w, i) in detail.works" :key="w.id"
                        class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50">
                        <h3 class="font-semibold text-slate-700 mb-4">Pengalaman {{ i + 1 }}</h3>

                        <div v-if="w.employment_certificate" class="mb-4 p-3 bg-white rounded-xl border border-slate-200">
                            <p class="text-xs text-slate-500 mb-2">Surat Keterangan Kerja</p>
                            <a :href="'/storage/' + w.employment_certificate" target="_blank"
                                class="text-sm text-indigo-600 hover:underline">
                                📄 Lihat Dokumen
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="f in [
                                { key: 'company_name',          label: 'Nama Perusahaan',     value: w.company_name },
                                { key: 'company_address',       label: 'Alamat Perusahaan',   value: w.company_address },
                                { key: 'department_position',   label: 'Jabatan / Posisi',    value: w.department_position },
                                { key: 'employment_type',       label: 'Jenis Pekerjaan',     value: w.employment_type },
                                { key: 'employment_start_date', label: 'Tanggal Mulai',       value: w.employment_start_date },
                                { key: 'employment_end_date',   label: 'Tanggal Selesai',     value: w.employment_end_date ?? 'Masih Bekerja' },
                                { key: 'job_description',       label: 'Deskripsi Pekerjaan', value: w.job_description },
                                { key: 'supervisor_full_name',  label: 'Nama Atasan',         value: w.supervisor_full_name },
                                { key: 'supervisor_position',   label: 'Jabatan Atasan',      value: w.supervisor_position },
                                { key: 'supervisor_phone',      label: 'No. Telp Atasan',     value: w.supervisor_phone },
                            ]" :key="f.key" class="space-y-2">
                                <InfoField :label="f.label" :value="f.value ?? '-'" />
                                <StatusSelect
                                    v-model="workStatuses[`${i}_${f.key}`]"
                                    v-model:memoValue="workMemos[`${i}_${f.key}`]"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <Divider class="my-8" />

                <!-- ===== SECTION 4: SERTIFIKAT ===== -->
                <SectionHeader title="Sertifikat" subtitle="Verifikasi sertifikasi yang dimiliki" />

                <div class="mt-6 space-y-6">
                    <div v-if="detail.certifications?.length === 0" class="text-slate-400 text-sm">
                        Tidak ada data sertifikat
                    </div>
                    <div v-for="(c, i) in detail.certifications" :key="c.id"
                        class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50">
                        <h3 class="font-semibold text-slate-700 mb-4">Sertifikat {{ i + 1 }}</h3>

                        <div v-if="c.certificate_attachment" class="mb-4 p-3 bg-white rounded-xl border border-slate-200">
                            <p class="text-xs text-slate-500 mb-2">Lampiran Sertifikat</p>
                            <a :href="'/storage/' + c.certificate_attachment" target="_blank"
                                class="text-sm text-indigo-600 hover:underline">
                                📄 Lihat Dokumen
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="f in [
                                { key: 'certificate_name',     label: 'Nama Sertifikat',   value: c.certificate_name },
                                { key: 'issuing_organization', label: 'Instansi Penerbit', value: c.issuing_organization },
                                { key: 'issue_date',           label: 'Tanggal Terbit',    value: c.issue_date },
                                { key: 'expiration_date',      label: 'Masa Berlaku',      value: c.expiration_date ?? 'Seumur Hidup' },
                                { key: 'certificate_score',    label: 'Skor / Level',      value: c.certificate_score },
                            ]" :key="f.key" class="space-y-2">
                                <InfoField :label="f.label" :value="f.value ?? '-'" />
                                <StatusSelect
                                    v-model="certStatuses[`${i}_${f.key}`]"
                                    v-model:memoValue="certMemos[`${i}_${f.key}`]"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <Divider class="my-8" />

                <!-- ===== SECTION 5: PERILAKU KERJA (CONDUCT) ===== -->
                <SectionHeader
                    title="Perilaku Kerja (Conduct)"
                    subtitle="Konfirmasi langsung ke atasan/HR perusahaan sebelumnya"
                />

                <div class="mt-4 mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                    <p class="font-semibold mb-2">⚠️ Panduan Conduct Investigation</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Hubungi atasan / HR perusahaan sebelumnya menggunakan <strong>No. Telp Atasan</strong></li>
                        <li>Jika tidak bisa dihubungi, pilih <strong>UNVERIFIED</strong> dan tulis alasannya di catatan</li>
                        <li>UNVERIFIED bukan berarti otomatis 0 poin — AI akan mempertimbangkan konteksnya</li>
                        <li class="text-red-700 font-medium">DILARANG: agama, kesehatan, keluarga, politik, kehidupan pribadi, orientasi seksual, rumor</li>
                        <li>Hanya perilaku <strong>terkait pekerjaan</strong> yang boleh dikonfirmasi</li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <div v-if="detail.works?.length === 0" class="text-slate-400 text-sm py-4 text-center">
                        Tidak ada data pengalaman kerja — conduct tidak dapat dievaluasi
                    </div>

                    <div v-for="(w, i) in detail.works" :key="`conduct_${w.id}`"
                        class="border border-amber-200 rounded-2xl p-6 bg-amber-50/30">

                        <h3 class="font-semibold text-slate-700 mb-1">
                            Conduct — {{ w.company_name || `Perusahaan ${i + 1}` }}
                        </h3>

                        <div class="mb-5 p-3 bg-white rounded-xl border border-amber-200 text-xs text-slate-600">
                            <p class="font-semibold text-slate-700 mb-1">📞 Kontak untuk Konfirmasi:</p>
                            <p class="mt-1">
                                👤 Atasan:
                                <span class="font-medium">{{ w.supervisor_full_name || '—' }}</span>
                                <span v-if="w.supervisor_position" class="text-slate-400">（{{ w.supervisor_position }}）</span>
                            </p>
                            <p class="mt-1">
                                📱 No. Telp:
                                <span class="font-medium text-indigo-700">{{ w.supervisor_phone || '—' }}</span>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="f in [
                                { key: 'stabilitas_kehadiran', label: 'Stabilitas Kehadiran', desc: 'Ketepatan waktu masuk & tingkat absensi' },
                                { key: 'kepatuhan_instruksi',  label: 'Kepatuhan Instruksi',  desc: 'Ketaatan pada perintah atasan & peraturan perusahaan' },
                                { key: 'kerja_sama_tim',       label: 'Kerja Sama Tim',       desc: 'Kemampuan bekerja sama dengan rekan & tim' },
                                { key: 'sikap_kerja',          label: 'Sikap Kerja',          desc: 'Inisiatif, tanggung jawab, dan etos kerja' },
                                { key: 'pelanggaran_disiplin', label: 'Pelanggaran Disiplin', desc: 'Catatan sanksi, pelanggaran, & status rekomendasi rehire' },
                            ]" :key="f.key" class="space-y-1">
                                <p class="text-sm font-medium text-slate-700">{{ f.label }}</p>
                                <p class="text-xs text-slate-400 mb-2">{{ f.desc }}</p>
                                <select
                                    v-model="conductStatuses[`${i}_${f.key}`]"
                                    class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none transition-colors"
                                    :class="{
                                        'bg-green-50  border-green-400  text-green-800':  conductStatuses[`${i}_${f.key}`] === 'VALID',
                                        'bg-red-50    border-red-400    text-red-800':    conductStatuses[`${i}_${f.key}`] === 'INVALID',
                                        'bg-yellow-50 border-yellow-400 text-yellow-800': conductStatuses[`${i}_${f.key}`] === 'UNVERIFIED',
                                        'bg-white border-slate-300':                      !conductStatuses[`${i}_${f.key}`],
                                    }"
                                >
                                    <option value="">— Belum diperiksa —</option>
                                    <option value="VALID">✅ VALID — Baik / Terkonfirmasi</option>
                                    <option value="INVALID">❌ INVALID — Ada masalah</option>
                                    <option value="UNVERIFIED">⚠️ UNVERIFIED — Tidak dapat dikonfirmasi</option>
                                </select>
                                <textarea
                                    v-model="conductMemos[`${i}_${f.key}`]"
                                    rows="2"
                                    placeholder="Catatan hasil konfirmasi ke atasan/HR... (wajib jika INVALID atau UNVERIFIED)"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:ring-2 focus:ring-amber-400 focus:outline-none resize-none mt-1"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <Divider class="my-8" />

                <!-- ===== SECTION 6: CATATAN ===== -->
                <SectionHeader title="Catatan Investigator" subtitle="Internal & Permintaan Koreksi" />

                <div class="space-y-6 mt-6">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-2">Catatan Progress (Untuk Diri Sendiri)</h3>
                        <p class="text-xs text-slate-500 mb-3">Catatan ini hanya untuk Anda dan tidak dikirim ke anggota.</p>
                        <textarea v-model="investigationNotes" rows="4"
                            placeholder="Tulis progress hari ini..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm">
                        </textarea>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-2">Permintaan Koreksi ke Anggota (Opsional)</h3>
                        <p class="text-xs text-slate-500 mb-3">Isi jika perlu meminta koreksi dari anggota.</p>
                        <textarea v-model="returnReason" rows="4"
                            placeholder="Detail koreksi yang diperlukan..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm">
                        </textarea>
                    </div>
                </div>

                <Divider class="my-8" />

                <!-- ===== アクションボタン ===== -->
                <div class="flex flex-wrap justify-end gap-3">
                    <Button variant="secondary" @click="sendCorrection">
                        ↩️ Minta Koreksi ke Anggota
                    </Button>
                    <Button variant="outline" @click="saveAll" :disabled="saving">
                        {{ saving ? 'Menyimpan...' : '💾 Simpan' }}
                    </Button>
                    <Button @click="sendComplete">
                        ✅ Selesai → Kirim ke Admin
                    </Button>
                </div>

            </template>
        </Card>
    </div>

    <!-- ===== 画像ビューアー ===== -->
    <ImageViewer :show="showImage" :src="viewImageSrc" @close="showImage = false" />

    <!-- ===== フローティング AI チャット ===== -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="chatOpen"
            class="fixed bottom-24 right-6 z-50 w-96 h-[560px] bg-gray-800 rounded-2xl shadow-2xl border border-gray-700 flex flex-col overflow-hidden"
        >
            <!-- パネルヘッダー -->
            <div class="flex items-center justify-between px-4 py-3 bg-gray-900 border-b border-gray-700 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🤖</span>
                    <div>
                        <p class="text-sm font-semibold text-white">AI Asisten Investigasi</p>
                        <p class="text-xs text-gray-400">
                            {{ currentCaseNo ? currentCaseNo : 'Pilih kasus terlebih dahulu' }}
                        </p>
                    </div>
                </div>
                <button
                    @click="chatOpen = false"
                    class="text-gray-400 hover:text-white transition-colors text-xl leading-none"
                >
                    ✕
                </button>
            </div>

            <!-- チャットウィジェット（autoCase で案件を自動セット・ドロップダウン非表示） -->
            <AiChatWidget :requests="[]" :auto-case="currentCaseNo" />
        </div>
    </Transition>

    <!-- フローティングボタン -->
    <button
        @click="chatOpen = !chatOpen"
        class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-2xl transition-all duration-200"
        :class="chatOpen
            ? 'bg-gray-700 hover:bg-gray-600'
            : 'bg-yellow-500 hover:bg-yellow-400'"
        :title="chatOpen ? 'Tutup AI Chat' : 'Konsultasi AI'"
    >
        {{ chatOpen ? '✕' : '🤖' }}
    </button>
</template>