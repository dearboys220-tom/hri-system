<script setup>
import Button from '@/Components/Admin/Components/Button.vue';
import Card from '@/Components/Admin/Components/Card.vue';
import Divider from '@/Components/Admin/Components/Divider.vue';
import FormGroup from '@/Components/Admin/Components/FormGroup.vue';
import ImageViewer from '@/Components/Admin/Components/ImageViewer.vue';
import InfoField from '@/Components/Admin/Components/InfoField.vue';
import SectionHeader from '@/Components/Admin/Components/SectionHeader.vue';
import Select from '@/Components/Admin/Components/Select.vue';
import InvestReviewLayout from '@/Components/Admin/Layout/InvestReviewLayout.vue';
import { MagnifyingGlassIcon, UserCircleIcon,ClockIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'

const status = ref('')
const search = ref('')
const showImage = ref(false)

defineOptions({
  layout: (h, page) =>
    h(InvestReviewLayout, {
      title: 'Tim Investigasi',
      subtitle: 'Penyelidikan & Manajemen Kasus yang Ditangani'
    }, () => page)
})
const profileImage = ref('https://static0.polygonimages.com/wordpress/wp-content/uploads/2025/12/frieren-looking-up-in-the-trailer-for-season-2.jpg?w=1200&h=628&fit=crop')
const profileFields = [
  { label: 'Nama', value: 'TIKA' },
  { label: 'NIK', value: '3215054208010008' },
  { label: 'Alamat sesuai KTP', value: 'Jl. Merdeka No. 1' },
  { label: 'Jenis Kelamin', value: 'Perempuan' },
  { label: 'Status Pernikahan', value: 'Belum Menikah' },
  { label: 'Kewarganegaraan', value: 'Indonesia' },
  { label: 'Tanggal Lahir', value: '01 Januari 2001' },
  { label: 'Alamat Saat Ini', value: 'Jakarta Selatan' },
  { label: 'Telepon', value: '08123456789' },
  { label: 'E-mail', value: 'tika@email.com' },
  { label: 'WhatsApp', value: '08123456789' },
  { label: 'LinkedIn', value: 'linkedin.com/in/tika' },
  { label: 'Facebook', value: 'facebook.com/tika' },
  { label: 'Instagram', value: '@tika' }
]
const educations = ref([
  {
    id: 1,
    namaSekolah: 'Universitas Teknologi',
    namaSekolahStatus: '',

    pendidikanTerakhir: 'S1',
    pendidikanTerakhirStatus: '',

    alamatSekolah: 'Bandung',
    alamatSekolahStatus: '',

    jurusan: 'Teknik Informatika',
    jurusanStatus: '',

    tanggalMasuk: '2018',
    tanggalMasukStatus: '',

    tanggalLulus: '2022',
    tanggalLulusStatus: '',

    statusKelulusan: 'Lulus',
    statusKelulusanStatus: '',

    ipk: '3.85',
    ipkStatus: '',

    penghargaan: 'Cumlaude',
    penghargaanStatus: '',

    file: 'https://picsum.photos/800/1000',
    fileStatus: ''
  },
  {
    id: 1,
    namaSekolah: 'Universitas Teknologi',
    namaSekolahStatus: '',

    pendidikanTerakhir: 'S1',
    pendidikanTerakhirStatus: '',

    alamatSekolah: 'Bandung',
    alamatSekolahStatus: '',

    jurusan: 'Teknik Informatika',
    jurusanStatus: '',

    tanggalMasuk: '2018',
    tanggalMasukStatus: '',

    tanggalLulus: '2022',
    tanggalLulusStatus: '',

    statusKelulusan: 'Lulus',
    statusKelulusanStatus: '',

    ipk: '3.85',
    ipkStatus: '',

    penghargaan: 'Cumlaude',
    penghargaanStatus: '',

    file: 'https://picsum.photos/800/1000',
    fileStatus: ''
  }
])

const workExperiences = ref([
  {
    id: 1,

    namaPerusahaan: 'PT Teknologi Nusantara',
    namaPerusahaanStatus: '',

    alamatPerusahaan: 'Jakarta Selatan',
    alamatPerusahaanStatus: '',

    tanggalMulai: '2020',
    tanggalSelesai: '2023',
    tanggalStatus: '',

    jenisPekerjaan: 'Full Time',
    jenisPekerjaanStatus: '',

    jabatan: 'Software Engineer',
    jabatanStatus: '',

    deskripsi: 'Mengembangkan sistem internal perusahaan.',
    deskripsiStatus: '',

    alasanBerhenti: 'Mencari tantangan baru',
    alasanBerhentiStatus: '',

    pencapaian: 'Meningkatkan performa sistem 40%',
    pencapaianStatus: '',

    namaAtasan: 'Budi Santoso',
    namaAtasanStatus: '',

    jabatanAtasan: 'IT Manager',
    jabatanAtasanStatus: '',

    noTelpAktif: '08123456789',
    noTelpAktifStatus: '',

    surat: 'https://picsum.photos/800/1000',
    suratStatus: ''
  }
])
const certificates = ref([
  {
    id: 1,

    namaSertifikat: 'ISO Compliance Training',
    namaSertifikatStatus: '',

    instansi: 'Badan Sertifikasi Nasional',
    instansiStatus: '',

    tanggalTerbit: '2023-01-12',
    tanggalTerbitStatus: '',

    masaBerlaku: '2026-01-12',
    masaBerlakuStatus: '',

    skor: 'A',
    skorStatus: '',

    keterangan: 'Sertifikasi tingkat lanjutan',
    keteranganStatus: '',

    lampiran: 'https://picsum.photos/900/1200',
    lampiranStatus: ''
  }
])

</script>


<template>
  <div class="grid grid-cols-1 md:grid-cols-10 gap-6 items-start">

    <Card class="md:col-span-3 self-start md:sticky md:top-28 h-fit">

      <!-- Header + Open Page Button -->
      <div class="flex justify-between items-start mb-4">
        <SectionHeader
          title="Daftar Permintaan"
          subtitle="Cari & pilih permintaan"
        />

        <Button
          variant="outline"
          size="sm"
          class="shrink-0"
        >
          Buka Halaman
        </Button>
      </div>

      <!-- Search + Reset -->
      <div class="flex gap-2 mb-4 pr-3">

        <div class="relative flex-1">
          <MagnifyingGlassIcon
            class="w-4 h-4 absolute left-3 top-3 text-slate-400"
          />
          <input
            v-model="search"
            type="text"
            placeholder="Cari nama / NIK..."
            class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300
                  focus:ring-2 focus:ring-admin-primary-600 focus:outline-none
                  text-sm"
          />
        </div>

        <Button
          variant="secondary"
          size="sm"
          @click="search = ''"
        >
          Reset
        </Button>

      </div>

      <Divider />

      <!-- Scrollable List (Max 5 visible) -->
      <div
        class="space-y-3 mt-4
              max-h-[360px]
              overflow-y-auto
              pr-1"
      >

        <div
          v-for="i in 12"
          :key="i"
          class="p-4 rounded-xl border border-slate-200
                hover:border-admin-primary-600
                hover:bg-admin-primary-50
                cursor-pointer transition"
        >
          <div class="flex items-center gap-2">
            <UserCircleIcon class="w-5 h-5 text-admin-primary-600" />
            <div>
              <p class="text-sm font-medium text-slate-800">
                HRIM-00{{ i }}
              </p>
              <p class="text-xs text-slate-500">
                TIKA • 03/02/2026
              </p>
            </div>
          </div>
        </div>

      </div>

    </Card>



    <!-- ================= RIGHT CARD (70%) ================= -->
    <Card class="md:col-span-7">
      <SectionHeader
        title="Profile"
        subtitle="Verifikasi data pribadi anggota"
      />

      <div class="mt-6 space-y-8">

        <!-- FOTO PROFIL CENTER -->
        <!-- FOTO PROFIL CENTER -->
        <div class="flex justify-center">
          <div
            class="relative w-36 sm:w-44 md:w-48 cursor-pointer group"
            @click="showImage = true"
          >
            <img
              :src="profileImage"
              class="w-full aspect-square rounded-2xl object-cover
                    border-4 border-white shadow-lg"
            />

            <!-- Overlay Hover -->
            <div
              class="absolute inset-0 rounded-2xl
                    bg-black/40 opacity-0
                    group-hover:opacity-100
                    transition duration-300
                    flex items-center justify-center"
            >
              <span class="text-white text-sm font-medium">
                Perbesar
              </span>
            </div>
          </div>
        </div>


        <!-- DATA PROFIL -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

          <div
            v-for="field in profileFields"
            :key="field.label"
            class="space-y-2"
          >
            <InfoField :label="field.label" :value="field.value" />

            <Select>
              <option value="">Pilih Status</option>
              <option value="valid">Valid</option>
              <option value="invalid">Invalid</option>
            </Select>
          </div>

        </div>

      </div>
      <Divider class="my-8" />

      <!-- ================= SECTION 2: RIWAYAT PENDIDIKAN ================= -->
      <SectionHeader
        title="Riwayat Pendidikan"
        subtitle="Verifikasi data pendidikan"
      />

      <div class="mt-6 space-y-8">

        <div
          v-for="(edu, index) in educations"
          :key="edu.id"
          class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50"
        >

          <h3 class="font-semibold text-slate-700 mb-6">
            Pendidikan {{ index + 1 }}
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Nama Sekolah -->
            <div class="space-y-2">
              <InfoField label="Nama Sekolah" :value="edu.namaSekolah" />
              <Select v-model="edu.namaSekolahStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- Pendidikan Terakhir -->
            <div class="space-y-2">
              <InfoField label="Pendidikan Terakhir" :value="edu.pendidikanTerakhir" />
              <Select v-model="edu.pendidikanTerakhirStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- Alamat Sekolah -->
            <div class="space-y-2">
              <InfoField label="Alamat Sekolah" :value="edu.alamatSekolah" />
              <Select v-model="edu.alamatSekolahStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- Jurusan -->
            <div class="space-y-2">
              <InfoField label="Jurusan" :value="edu.jurusan" />
              <Select v-model="edu.jurusanStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- Tanggal Masuk -->
            <div class="space-y-2">
              <InfoField label="Tanggal Masuk" :value="edu.tanggalMasuk" />
              <Select v-model="edu.tanggalMasukStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- Tanggal Lulus -->
            <div class="space-y-2">
              <InfoField label="Tanggal Lulus" :value="edu.tanggalLulus" />
              <Select v-model="edu.tanggalLulusStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- Status Kelulusan -->
            <div class="space-y-2">
              <InfoField label="Status Kelulusan" :value="edu.statusKelulusan" />
              <Select v-model="edu.statusKelulusanStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- IPK -->
            <div class="space-y-2">
              <InfoField label="IPK / Nilai Akhir" :value="edu.ipk" />
              <Select v-model="edu.ipkStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- Penghargaan -->
            <div class="space-y-2 md:col-span-2">
              <InfoField label="Penghargaan" :value="edu.penghargaan" />
              <Select v-model="edu.penghargaanStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

            <!-- File -->
            <div class="space-y-2 md:col-span-2">
              <InfoField label="Ijazah & Transkrip Nilai" value="Lihat Dokumen" />

              <div
                class="text-admin-primary-700 text-sm font-medium cursor-pointer"
                @click="showEducationFile = edu.file"
              >
                Lihat File
              </div>

              <Select v-model="edu.fileStatus">
                <option value="">Pilih Status</option>
                <option value="valid">Valid</option>
                <option value="invalid">Invalid</option>
              </Select>
            </div>

          </div>

        </div>

      </div>


  <Divider class="my-8" />

  <!-- ================= SECTION 3: PENGALAMAN & SERTIFIKAT ================= -->
    <SectionHeader
      title="Pengalaman Kerja"
      subtitle="Verifikasi riwayat pekerjaan"
    />

  <div class="mt-6 space-y-8">

    <div
      v-for="(work, index) in workExperiences"
      :key="work.id"
      class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50"
    >

      <h3 class="font-semibold text-slate-700 mb-6">
        Pengalaman Kerja {{ index + 1 }}
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Nama Perusahaan -->
        <div class="space-y-2">
          <InfoField label="Nama Perusahaan" :value="work.namaPerusahaan" />
          <Select v-model="work.namaPerusahaanStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Alamat -->
        <div class="space-y-2">
          <InfoField label="Alamat Perusahaan" :value="work.alamatPerusahaan" />
          <Select v-model="work.alamatPerusahaanStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Tanggal Mulai -->
        <div class="space-y-2">
          <InfoField label="Tanggal Mulai" :value="work.tanggalMulai" />
          <Select v-model="work.tanggalStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Tanggal Selesai -->
        <div class="space-y-2">
          <InfoField label="Tanggal Selesai" :value="work.tanggalSelesai" />
          <Select v-model="work.tanggalStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Jenis Pekerjaan -->
        <div class="space-y-2">
          <InfoField label="Jenis Pekerjaan" :value="work.jenisPekerjaan" />
          <Select v-model="work.jenisPekerjaanStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Jabatan -->
        <div class="space-y-2">
          <InfoField label="Departemen / Jabatan" :value="work.jabatan" />
          <Select v-model="work.jabatanStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Deskripsi -->
        <div class="space-y-2 md:col-span-2">
          <InfoField label="Deskripsi Pekerjaan" :value="work.deskripsi" />
          <Select v-model="work.deskripsiStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Alasan Berhenti -->
        <div class="space-y-2">
          <InfoField label="Alasan Berhenti" :value="work.alasanBerhenti" />
          <Select v-model="work.alasanBerhentiStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Pencapaian -->
        <div class="space-y-2">
          <InfoField label="Pencapaian / Prestasi" :value="work.pencapaian" />
          <Select v-model="work.pencapaianStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Nama Atasan -->
        <div class="space-y-2">
          <InfoField label="Nama Atasan" :value="work.namaAtasan" />
          <Select v-model="work.namaAtasanStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Jabatan Atasan -->
        <div class="space-y-2">
          <InfoField label="Jabatan Atasan" :value="work.jabatanAtasan" />
          <Select v-model="work.jabatanAtasanStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- No Telp -->
        <div class="space-y-2">
          <InfoField label="No. Telp Aktif" :value="work.noTelpAktif" />
          <Select v-model="work.noTelpAktifStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Surat -->
        <div class="space-y-2 md:col-span-2">
          <InfoField label="Surat Keterangan Pengalaman Kerja" value="Lihat Dokumen" />

          <div
            class="text-admin-primary-700 text-sm font-medium cursor-pointer"
            @click="showImage = work.surat"
          >
            Lihat File
          </div>

          <Select v-model="work.suratStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

      </div>

    </div>

  </div>
  <Divider class="my-8" />

  <SectionHeader
    title="Sertifikat"
    subtitle="Verifikasi sertifikasi yang dimiliki"
  />

  <div class="mt-6 space-y-8">

    <div
      v-for="(cert, index) in certificates"
      :key="cert.id"
      class="border border-slate-200 rounded-2xl p-6 bg-slate-50/50"
    >

      <h3 class="font-semibold text-slate-700 mb-6">
        Sertifikat {{ index + 1 }}
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Nama Sertifikat -->
        <div class="space-y-2">
          <InfoField label="Nama Sertifikat" :value="cert.namaSertifikat" />
          <Select v-model="cert.namaSertifikatStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Instansi -->
        <div class="space-y-2">
          <InfoField label="Instansi Penerbit" :value="cert.instansi" />
          <Select v-model="cert.instansiStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Tanggal Terbit -->
        <div class="space-y-2">
          <InfoField label="Tanggal Terbit" :value="cert.tanggalTerbit" />
          <Select v-model="cert.tanggalTerbitStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Masa Berlaku -->
        <div class="space-y-2">
          <InfoField label="Masa Berlaku" :value="cert.masaBerlaku" />
          <Select v-model="cert.masaBerlakuStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Skor / Level -->
        <div class="space-y-2">
          <InfoField label="Skor / Level / Tingkatan" :value="cert.skor" />
          <Select v-model="cert.skorStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Keterangan -->
        <div class="space-y-2 md:col-span-2">
          <InfoField label="Keterangan" :value="cert.keterangan" />
          <Select v-model="cert.keteranganStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

        <!-- Lampiran -->
        <div class="space-y-2 md:col-span-2">
          <InfoField
            label="Lampiran Sertifikat"
            value="Lihat Lampiran"
          />

          <div
            class="text-admin-primary-700 text-sm font-medium cursor-pointer"
            @click="showImage = cert.lampiran"
          >
            Lihat File
          </div>

          <Select v-model="cert.lampiranStatus">
            <option value="">Pilih Status</option>
            <option value="valid">Valid</option>
            <option value="invalid">Invalid</option>
          </Select>
        </div>

      </div>

    </div>

  </div>
  <Divider class="my-8" />

  <!-- ================= SECTION 4: CATATAN INVESTIGATOR ================= -->
  <SectionHeader
    title="Catatan Investigator"
    subtitle="Internal & Permintaan Koreksi"
  />

  <div class="space-y-8 mt-6">

    <!-- Progress Internal -->
    <div>
      <h3 class="text-sm font-semibold text-slate-700 mb-2">
        Catatan Progress Anda (Untuk Diri Sendiri)
      </h3>
      <p class="text-xs text-slate-500 mb-3">
        Tulis progress hari ini untuk melanjutkan besok. 
        Catatan ini hanya untuk Anda dan tidak dikirim ke anggota.
      </p>

      <textarea
        rows="4"
        placeholder="Tulis progress hari ini..."
        class="w-full rounded-xl border border-slate-300 px-4 py-3
               focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm"
      ></textarea>

      <p class="text-xs text-slate-400 mt-2">
        <span class="inline-flex items-center gap-1">
          <ClockIcon class="w-4 h-4 shrink-0" />
          <span>
            Terakhir disimpan: 07/02/2026 14:11 (6 hari yang lalu)
          </span>
        </span>
      </p>
    </div>

    <!-- Permintaan Koreksi -->
    <div>
      <h3 class="text-sm font-semibold text-slate-700 mb-2">
        Permintaan Koreksi (Opsional)
      </h3>
      <p class="text-xs text-slate-500 mb-3">
        Jika memerlukan koreksi dari anggota, isi detail di bawah ini sebelum mengirim.
      </p>

      <textarea
        rows="4"
        placeholder="Detail koreksi yang diperlukan..."
        class="w-full rounded-xl border border-slate-300 px-4 py-3
               focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm"
      ></textarea>
    </div>

  </div>

  <Divider class="my-8" />

  <!-- ACTION BUTTON -->
  <div class="flex justify-end gap-3">
    <Button variant="secondary">Batal</Button>
    <Button>Simpan</Button>
  </div>

    </Card>


  </div>
  <ImageViewer
    :show="showImage"
    :src="profileImage"
    @close="showImage = false"
  />
</template>

