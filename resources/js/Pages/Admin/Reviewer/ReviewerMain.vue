<script setup>
import Button from '@/Components/Admin/Components/Button.vue'
import Card from '@/Components/Admin/Components/Card.vue'
import Divider from '@/Components/Admin/Components/Divider.vue'
import ImageViewer from '@/Components/Admin/Components/ImageViewer.vue'
import InfoField from '@/Components/Admin/Components/InfoField.vue'
import SectionHeader from '@/Components/Admin/Components/SectionHeader.vue'
import InvestReviewLayout from '@/Components/Admin/Layout/InvestReviewLayout.vue'
import { MagnifyingGlassIcon, UserCircleIcon } from '@heroicons/vue/24/outline'
import { ref, computed } from 'vue'

defineOptions({
  layout: (h, page) =>
    h(InvestReviewLayout, {
      title: 'Halaman Penilai (Tim Review)',
      subtitle: 'Evaluasi & Hasil Peninjauan Investigator'
    }, () => page)
})

/* ================== STATE ================== */

const search = ref('')
const showImage = ref(false)

const profileImage = ref('https://picsum.photos/500/500')

/* ================== DATA (SUDAH DINILAI INVESTIGATOR) ================== */
const profileData = [
  { label: 'Nama Lengkap', value: 'TIKA', status: 'valid' },
  { label: 'NIK', value: '3215054208010008', status: 'valid' },
  { label: 'KTP', value: '32448', status: 'valid' },
  { label: 'Alamat Sesuai KTP', value: 'Tidak Ada Data', status: 'valid' },
  { label: 'Jenis Kelamin', value: 'Perempuan', status: 'valid' },
  { label: 'Tanggal Lahir', value: '02/02/1999', status: 'valid' },
  { label: 'Status Pernikahan', value: 'Belum Menikah', status: 'valid' },
  { label: 'Kewarganegaraan', value: 'Malaysia', status: 'invalid' },
  { label: 'Alamat Saat Ini', value: 'JAKSEL', status: 'invalid' },
  { label: 'Email', value: 'mellayaa17@gmail.com', status: 'invalid' },
  { label: 'Telepon', value: '08981631363', status: 'valid' },
  { label: 'WhatsApp', value: '08981631363', status: 'valid' },
  { label: 'LinkedIn', value: 'Tidak Ada Data', status: 'valid' },
  { label: 'Facebook', value: 'Tidak Ada Data', status: 'valid' },
  { label: 'Instagram', value: 'Tidak Ada Data', status: 'valid' }
]

const educations = ref([
  {
    id: 1,

    fields: [
      { label: 'Nama Sekolah', value: 'UTT', status: 'valid' },
      { label: 'Pendidikan Terakhir', value: 'Sarjana (S1)', status: 'valid' },
      { label: 'Alamat Sekolah', value: 'TANGSEL', status: 'valid' },
      { label: 'Jurusan', value: 'AKUNTANSI', status: 'valid' },
      { label: 'Tanggal Masuk', value: '12/02/2026', status: 'valid' },
      { label: 'Tanggal Lulus', value: '11/02/2026', status: 'valid' },
      { label: 'Status Kelulusan', value: 'Lulus', status: 'valid' },
      { label: 'IPK / Nilai Akhir', value: '3.05', status: 'valid' },
      { label: 'Penghargaan', value: 'Tidak Ada Data', status: 'valid' },
      { label: 'Ijazah & Transkrip Nilai', value: '1 File', status: 'valid' }
    ],
  },
   {
    id: 1,

    fields: [
      { label: 'Nama Sekolah', value: 'UTT', status: 'valid' },
      { label: 'Pendidikan Terakhir', value: 'Sarjana (S1)', status: 'valid' },
      { label: 'Alamat Sekolah', value: 'TANGSEL', status: 'valid' },
      { label: 'Jurusan', value: 'AKUNTANSI', status: 'valid' },
      { label: 'Tanggal Masuk', value: '12/02/2026', status: 'valid' },
      { label: 'Tanggal Lulus', value: '11/02/2026', status: 'valid' },
      { label: 'Status Kelulusan', value: 'Lulus', status: 'valid' },
      { label: 'IPK / Nilai Akhir', value: '3.05', status: 'valid' },
      { label: 'Penghargaan', value: 'Tidak Ada Data', status: 'valid' },
      { label: 'Ijazah & Transkrip Nilai', value: '1 File', status: 'valid' }
    ],
  }
])

const cardClass = (status) => {
  if (status === 'valid')
    return 'border-green-200 bg-green-50/40'
  if (status === 'invalid')
    return 'border-red-200 bg-red-50/40'
  return 'border-slate-200 bg-white'
}


const workExperiences = ref([
  {
    id: 1,

    fields: [
      { label: 'Nama Perusahaan', value: 'PT Teknologi Nusantara', status: 'valid' },
      { label: 'Alamat Perusahaan', value: 'Jakarta Selatan', status: 'valid' },
      { label: 'Tanggal Mulai', value: '2020', status: 'valid' },
      { label: 'Tanggal Selesai', value: '2023', status: 'valid' },
      { label: 'Jenis Pekerjaan', value: 'Full Time', status: 'valid' },
      { label: 'Jabatan', value: 'Software Engineer', status: 'valid' },
      { label: 'Deskripsi', value: 'Mengembangkan sistem internal perusahaan.', status: 'valid' },
      { label: 'Alasan Berhenti', value: 'Mencari tantangan baru', status: 'valid' },
      { label: 'Pencapaian', value: 'Meningkatkan performa sistem 40%', status: 'valid' },
      { label: 'Nama Atasan', value: 'Budi Santoso', status: 'valid' },
      { label: 'Jabatan Atasan', value: 'IT Manager', status: 'valid' },
      { label: 'No. Telp Aktif', value: '08123456789', status: 'valid' },
      { label: 'Surat Keterangan', value: '1 File', status: 'valid' }
    ],

    deduction: 0.0
  }
])

const certificates = ref([
  {
    id: 1,

    fields: [
      { label: 'Nama Sertifikat', value: 'ISO Compliance Training', status: 'valid' },
      { label: 'Instansi Penerbit', value: 'Badan Sertifikasi Nasional', status: 'valid' },
      { label: 'Tanggal Terbit', value: '2023-01-12', status: 'valid' },
      { label: 'Masa Berlaku', value: '2026-01-12', status: 'valid' },
      { label: 'Skor / Level', value: 'A', status: 'valid' },
      { label: 'Keterangan', value: 'Sertifikasi tingkat lanjutan', status: 'valid' },
      { label: 'Lampiran', value: '1 File', status: 'valid' }
    ],

    deduction: 0.0
  }
])


/* ================== SCORE SYSTEM ================== */

// const maxScore = 100

// const totalDeduction = computed(() => {
//   let total = 0

//   profileData.value.forEach(f => total += f.deduction)

//   educations.value.forEach(e => {
//     total += e.namaSekolahDeduction || 0
//     total += e.ipkDeduction || 0
//   })

//   workExperiences.value.forEach(w => {
//     total += w.namaPerusahaanDeduction || 0
//     total += w.alasanBerhentiDeduction || 0
//   })

//   certificates.value.forEach(c => {
//     total += c.namaSertifikatDeduction || 0
//   })

//   return total
// })

// const finalScore = computed(() => maxScore - totalDeduction.value)

/* ================== HELPER ================== */

const badgeClass = (status) => {
  if (status === 'valid')
    return 'bg-green-100 text-green-700 border border-green-300'
  if (status === 'invalid')
    return 'bg-red-100 text-red-700 border border-red-300'
  return 'bg-slate-100 text-slate-600 border border-slate-300'
}
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-10 gap-6 items-start">

    <Card class="md:col-span-3 self-start md:sticky md:top-28 h-fit">
      <SectionHeader
        title="Daftar Permintaan"
        subtitle="Cari & pilih permintaan"
      />

      <div class="flex gap-2 mt-4">
        <div class="relative flex-1">
          <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
          <input
            v-model="search"
            placeholder="Cari nama / NIK..."
            class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-sm"
          />
        </div>
      </div>

      <Divider class="my-4" />

      <div class="space-y-3 max-h-[360px] overflow-y-auto">
        <div
          v-for="i in 5"
          :key="i"
          class="p-4 rounded-xl border hover:bg-admin-primary-50 cursor-pointer"
        >
          <div class="flex items-center gap-2">
            <UserCircleIcon class="w-5 h-5 text-admin-primary-600" />
            <div>
              <p class="text-sm font-medium">HRIM-00{{ i }}</p>
              <p class="text-xs text-slate-500">TIKA • 03/02/2026</p>
            </div>
          </div>
        </div>
      </div>
    </Card>

    <!-- ================= RIGHT SIDE ================= -->
    <Card class="md:col-span-7 space-y-5">
        <SectionHeader
            title="Informasi Pemohon"
            subtitle="Ringkasan data utama pelamar"
        />

        <div class="mt-6 flex flex-col md:flex-row md:items-center gap-6">

            <!-- FOTO -->
            <div class="flex justify-center md:justify-start">
            <img
                src="https://picsum.photos/200"
                class="w-24 h-24 rounded-2xl object-cover shadow"
            />
            </div>

            <!-- DATA -->
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">

            <div>
                <p class="text-slate-500 text-xs">Nama Lengkap</p>
                <p class="font-semibold text-slate-800">TIKA</p>
            </div>

            <div>
                <p class="text-slate-500 text-xs">NIK</p>
                <p class="font-semibold text-slate-800">3215054208010008</p>
            </div>

            <div>
                <p class="text-slate-500 text-xs">Email</p>
                <p class="font-semibold text-slate-800">
                mellayaa17@gmail.com
                </p>
            </div>

            <div>
                <p class="text-slate-500 text-xs">Telepon</p>
                <p class="font-semibold text-slate-800">
                08981631363
                </p>
            </div>

            </div>

        </div>
        <Card class="border-2 border-blue-500 bg-blue-50 mt-6 mb-6">
            <SectionHeader
                title="Skor Akhir Penilaian"
                subtitle="Metode: Weighted Average (Rata-rata Terbobot)"
            />

            <div class="mt-8 flex flex-col lg:flex-row items-center gap-10">

                <!-- ===== LEFT: SCORE ===== -->
                <div class="flex flex-col items-center text-center">

                <div
                    class="w-44 h-44 rounded-full
                        bg-white border-4 border-blue-500
                        flex items-center justify-center
                        shadow-lg"
                >
                    <div>
                    <p class="text-5xl font-bold text-blue-700">
                        100
                    </p>
                    <p class="text-xs text-slate-500">
                        dari 100 poin
                    </p>
                    </div>
                </div>

                <p class="mt-4 text-sm text-slate-600">
                    Total Pengurangan Poin:
                    <span class="font-semibold text-red-600">
                    -0.0
                    </span>
                </p>

                </div>

                <!-- ===== RIGHT: BREAKDOWN ===== -->
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-6">

                <!-- Informasi Dasar -->
                <div class="border rounded-2xl p-4 bg-white">
                    <p class="text-xs text-slate-500 mb-1">
                    Informasi Dasar
                    </p>
                    <p class="text-xs text-slate-400">
                    Bobot: 15%
                    </p>

                    <p class="mt-3 text-lg font-semibold text-red-600">
                    -0.0 Poin
                    </p>
                </div>

                <!-- Pendidikan -->
                <div class="border rounded-2xl p-4 bg-white">
                    <p class="text-xs text-slate-500 mb-1">
                    Pendidikan
                    </p>
                    <p class="text-xs text-slate-400">
                    Bobot: 35%
                    </p>

                    <p class="mt-3 text-lg font-semibold text-red-600">
                    -0.0 Poin
                    </p>
                </div>

                <!-- Riwayat Pekerjaan -->
                <div class="border rounded-2xl p-4 bg-white">
                    <p class="text-xs text-slate-500 mb-1">
                    Riwayat Pekerjaan
                    </p>
                    <p class="text-xs text-slate-400">
                    Bobot: 40%
                    </p>

                    <p class="mt-3 text-lg font-semibold text-red-600">
                    -0.0 Poin
                    </p>
                </div>

                <!-- Sertifikat -->
                <div class="border rounded-2xl p-4 bg-white">
                    <p class="text-xs text-slate-500 mb-1">
                    Sertifikat / Kualifikasi
                    </p>
                    <p class="text-xs text-slate-400">
                    Bobot: 10%
                    </p>

                    <p class="mt-3 text-lg font-semibold text-red-600">
                    -0.0 Poin
                    </p>
                </div>

                </div>

            </div>
        </Card>

        <SectionHeader
        title="Evaluasi Detail Informasi Dasar"
        subtitle="Data pelamar & hasil investigasi"
        />

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-1">

        <div
            v-for="item in profileData"
            :key="item.label"
            class="border rounded-2xl p-4 transition"
            :class="cardClass(item.status)"
        >
            <!-- Top Row: Label + Badge -->
            <div class="flex items-start justify-between gap-1">
            <p class="text-xs text-slate-500">
                {{ item.label }}
            </p>

            <span
                class="px-3 py-1 text-xs rounded-lg font-semibold whitespace-nowrap"
                :class="badgeClass(item.status)"
            >
                {{ item.status === 'valid' ? 'Valid' : 'Tidak Valid' }}
            </span>
            </div>

            <p class="text-sm font-semibold text-slate-800 break-words">
                {{ item.value }}
            </p>

        </div>

        </div>

        <div class="mt-6 border rounded-2xl p-4 bg-slate-50">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Total Pengurangan Poin
                    </p>
                    <p class="text-xs text-slate-500">
                        Dari kategori: Informasi Dasar (Bobot 15%)
                    </p>
                </div>

                <div class="text-right">
                    <p class="text-lg font-bold text-red-600">
                        -0.0 Poin
                    </p>
                </div>

            </div>

        </div>

        <Divider />

        <SectionHeader
        title="Riwayat Pendidikan"
        subtitle="Data pendidikan pelamar & hasil investigasi"
        />

        <div class="mt-6 space-y-5">

        <Card
            v-for="(edu, index) in educations"
            :key="edu.id"
            collapsible
            :title="`Pendidikan ${index + 1}`"
            padding="p-6"
        >

        <!-- Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div
            v-for="field in edu.fields"
            :key="field.label"
            class="border rounded-2xl p-4 transition"
            :class="cardClass(field.status)"
            >
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-xs text-slate-500">
                {{ field.label }}
                </p>

                <span
                class="px-3 py-1 text-xs rounded-lg font-semibold whitespace-nowrap"
                :class="badgeClass(field.status)"
                >
                {{ field.status === 'valid' ? 'Valid' : 'Tidak Valid' }}
                </span>
            </div>

            <p class="text-sm font-semibold text-slate-800 break-words">
                {{ field.value }}
            </p>
            </div>

        </div>

        </Card>
        
        </div>
        <div class="mt-6 border rounded-2xl p-4 bg-slate-50">
            <div class="flex justify-between items-center">

                <div>
                <p class="text-sm font-semibold text-slate-700">
                    Total Pengurangan Poin
                </p>
                <p class="text-xs text-slate-500">
                    Dari kategori Pendidikan
                </p>
                </div>

                <p class="text-lg font-bold text-red-600">
                - 0 Poin
                </p>

            </div>
        </div>

        <Divider />

        <SectionHeader title="Pengalaman Kerja" subtitle="Hasil evaluasi pekerjaan" />

        <div class="mt-6 space-y-5">

        <Card
            v-for="(edu, index) in workExperiences"
            :key="edu.id"
            collapsible
            :title="`Pengalaman Kerja ${index + 1}`"
            padding="p-6"
        >

        <!-- Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div
            v-for="field in edu.fields"
            :key="field.label"
            class="border rounded-2xl p-4 transition"
            :class="cardClass(field.status)"
            >
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-xs text-slate-500">
                {{ field.label }}
                </p>

                <span
                class="px-3 py-1 text-xs rounded-lg font-semibold whitespace-nowrap"
                :class="badgeClass(field.status)"
                >
                {{ field.status === 'valid' ? 'Valid' : 'Tidak Valid' }}
                </span>
            </div>

            <p class="text-sm font-semibold text-slate-800 break-words">
                {{ field.value }}
            </p>
            </div>

        </div>

        </Card>
        
        </div>
        <div class="mt-6 border rounded-2xl p-4 bg-slate-50">
            <div class="flex justify-between items-center">

                <div>
                <p class="text-sm font-semibold text-slate-700">
                    Total Pengurangan Poin
                </p>
                <p class="text-xs text-slate-500">
                    Dari kategori Pendidikan
                </p>
                </div>

                <p class="text-lg font-bold text-red-600">
                - 0 Poin
                </p>

            </div>
        </div>

        <Divider />

        <SectionHeader title="Sertifikat" subtitle="Hasil evaluasi sertifikat" />

        <div class="mt-6 space-y-5">

        <Card
            v-for="(edu, index) in certificates"
            :key="edu.id"
            collapsible
            :title="`Sertifikat ${index + 1}`"
            padding="p-6"
        >

        <!-- Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div
            v-for="field in edu.fields"
            :key="field.label"
            class="border rounded-2xl p-4 transition"
            :class="cardClass(field.status)"
            >
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-xs text-slate-500">
                {{ field.label }}
                </p>

                <span
                class="px-3 py-1 text-xs rounded-lg font-semibold whitespace-nowrap"
                :class="badgeClass(field.status)"
                >
                {{ field.status === 'valid' ? 'Valid' : 'Tidak Valid' }}
                </span>
            </div>

            <p class="text-sm font-semibold text-slate-800 break-words">
                {{ field.value }}
            </p>
            </div>

        </div>

        </Card>
        
        </div>
        <div class="mt-6 border rounded-2xl p-4 bg-slate-50">
            <div class="flex justify-between items-center">

                <div>
                <p class="text-sm font-semibold text-slate-700">
                    Total Pengurangan Poin
                </p>
                <p class="text-xs text-slate-500">
                    Dari kategori Pendidikan
                </p>
                </div>

                <p class="text-lg font-bold text-red-600">
                - 0 Poin
                </p>

            </div>
        </div>

        <Divider />

        <div class="flex justify-end gap-3">
            <Button variant="secondary">Kembalikan</Button>
            <Button variant="danger">Tolak</Button>
            <Button>Setujui</Button>
        </div>

    </Card>

  </div>

  <ImageViewer
    :show="showImage"
    :src="profileImage"
    @close="showImage = false"
  />
</template>
