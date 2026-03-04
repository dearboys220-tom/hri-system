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
      title: 'Halaman Perusahaan',
      subtitle: ''
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

    <h2>Company</h2>

  </div>

  <ImageViewer
    :show="showImage"
    :src="profileImage"
    @close="showImage = false"
  />
</template>
