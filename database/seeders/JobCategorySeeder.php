<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('job_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            'Akuntansi & Keuangan' => [
                'Staf Akuntansi / Pembukuan', 'Hutang Dagang', 'Piutang / Pengendalian Kredit',
                'Analisis & Pelaporan', 'Audit Eksternal', 'Audit Internal',
                'Layanan Perbankan', 'Keuangan Korporat', 'Pelaporan Keuangan',
                'Analisis Keuangan', 'Manajemen Dana', 'Perbankan Investasi',
                'Penggajian', 'Manajemen Risiko', 'Perpajakan', 'Perbendaharaan',
                'Manajemen Kekayaan',
            ],
            'Administrasi & Support' => [
                'Asisten Administrasi', 'Tata Usaha / Tugas Umum', 'Entri Data',
                'Resepsionis', 'Sekretaris', 'Manajer Kantor', 'Dokumentasi',
                'Pengarsipan & Pencatatan', 'Penerjemahan & Interpretasi', 'Penjadwalan & Koordinasi',
            ],
            'Periklanan & Media' => [
                'Manajemen Akun', 'Perencanaan Iklan', 'Manajemen Merek',
                'Pembuatan Konten', 'Penulisan Naskah', 'Desain Kreatif',
                'Iklan Digital', 'Manajemen Acara', 'Komunikasi Pemasaran',
                'Perencanaan Media', 'Humas & Komunikasi', 'Manajemen Media Sosial',
            ],
            'Pertanian & Kehutanan' => [
                'Riset Pertanian', 'Produksi Tanaman', 'Usaha Tani',
                'Perikanan & Akuakultur', 'Kehutanan', 'Peternakan',
                'Perkebunan', 'Penjualan Produk Pertanian', 'Pengolahan Makanan', 'Agribisnis',
            ],
            'Arsitektur & Desain' => [
                'Desain Arsitektur', 'Teknik Layanan Bangunan (MEP)', 'Gambar CAD / Drafting',
                'Desain Interior', 'Arsitektur Lanskap', 'Perencanaan',
                'Manajemen Proyek', 'Estimator / Quantity Surveying',
                'Teknik Struktur', 'Perencanaan Kota',
            ],
            'Otomotif' => [
                'Desain Otomotif', 'Penjualan Otomotif', 'Perawatan & Perbaikan',
                'Manufaktur', 'Suku Cadang & Aksesori', 'Kontrol Kualitas',
                'Teknisi Servis', 'Manajemen Suku Cadang', 'Inspeksi Kendaraan', 'Manajemen Bengkel',
            ],
            'Perbankan & Layanan Keuangan' => [
                'Manajemen Cabang', 'Perbankan Komersial', 'Kepatuhan',
                'Analisis Kredit', 'Layanan Pelanggan Perbankan', 'Asuransi',
                'Penasihat Investasi', 'Pemberian Kredit', 'Keuangan Mikro',
                'Operasional Perbankan', 'Pemrosesan Pembayaran', 'Manajemen Portofolio',
                'Perbankan Ritel', 'Manajemen Risiko Perbankan', 'Syariah / Perbankan Islam',
                'Perdagangan & Tresuri',
            ],
            'Layanan Komunitas & Sosial' => [
                'Layanan Anak & Keluarga', 'Konseling', 'Layanan Disabilitas',
                'Layanan Darurat', 'Pekerjaan Lingkungan', 'Bantuan Kemanusiaan',
                'Hubungan Masyarakat', 'Kerja Migran', 'Layanan Lansia',
                'Pekerjaan Agama', 'Pekerjaan Sosial', 'Pekerjaan Pemuda',
            ],
            'Konstruksi' => [
                'Manajemen Bangunan', 'Sipil / Konstruksi', 'Estimasi Biaya',
                'Manajemen Fasilitas', 'Manajemen Proyek Konstruksi', 'Kontrol Kualitas Konstruksi',
                'Keselamatan & Lingkungan', 'Surveying', 'Pengerjaan Kayu',
            ],
            'Konsultasi & Strategi' => [
                'Konsultasi Bisnis', 'Pengembangan Bisnis', 'Perubahan Manajemen',
                'Konsultasi Keuangan', 'Konsultasi HR', 'Konsultasi IT',
                'Manajemen Pengetahuan', 'Konsultasi Hukum', 'Konsultasi Manajemen',
                'Konsultasi Risiko', 'Perencanaan Strategis',
            ],
            'Layanan Pelanggan' => [
                'Penasihat Pelanggan', 'Layanan Pelanggan', 'Helpdesk & Support',
                'Manajemen Hubungan Pelanggan', 'Penanganan Pengaduan', 'Reservasi & Pemesanan',
                'Layanan Lapangan', 'Koordinasi Layanan',
            ],
            'Desain & Kreatif' => [
                'Animasi & Multimedia', 'Desain Grafis', 'Ilustrasi',
                'Desain Produk', 'Fotografi', 'Videografi & Produksi Video',
                'Desain UI/UX', 'Desain Web',
            ],
            'Pendidikan & Pelatihan' => [
                'Pengembangan Kurikulum', 'Pendidikan Anak Usia Dini', 'Pendidikan Khusus',
                'Bimbingan & Konseling', 'Instruktur / Pelatih', 'Manajemen Pendidikan',
                'Penelitian Pendidikan', 'Pengajar / Guru', 'Tentor / Les Privat',
                'Pelatihan & Pengembangan SDM', 'Pendidikan Vokasi',
            ],
            'Teknik' => [
                'Teknik Aerospace', 'Teknik Otomasi', 'Teknik Biomedis',
                'Teknik Kimia', 'Teknik Sipil', 'Teknik Kontrol & Instrumentasi',
                'Teknik Elektro', 'Teknik Energi', 'Teknik Lingkungan',
                'Teknik Industri', 'Teknik Mesin', 'Teknik Pertambangan',
                'Teknik Minyak & Gas', 'Teknik Proses', 'Teknik Struktural',
                'Teknik Sistem', 'Teknik Telekomunikasi',
            ],
            'Hiburan & Pariwisata' => [
                'Atraksi & Rekreasi', 'Katering & Banquet', 'Kasino & Gaming',
                'Manajemen Acara & Pameran', 'Pemandu Wisata', 'Manajemen Hotel',
                'Housekeeping', 'Pengelolaan Resor', 'Restoran & Food Service',
                'Manajemen Pariwisata & Perjalanan',
            ],
            'Kesehatan & Medis' => [
                'Manajemen Klinik', 'Dokter Umum', 'Dokter Spesialis',
                'Gigi & Ortodonti', 'Apoteker', 'Keperawatan',
                'Bidan', 'Fisioterapi', 'Radiologi & Imaging',
                'Laboratorium Medis', 'Rekam Medis', 'Manajemen Rumah Sakit',
                'Kesehatan Masyarakat', 'Gizi & Dietisien', 'Optometri',
                'Kesehatan Mental & Psikologi', 'Bedah',
            ],
            'SDM & Rekrutmen' => [
                'Hubungan Industrial', 'Kompensasi & Benefit', 'HR Generalis',
                'HR Officer', 'HRIS & Sistem HR', 'Pengembangan SDM',
                'Hubungan Tenaga Kerja', 'Manajemen Kinerja', 'Rekrutmen & Seleksi',
                'Remunerasi', 'Pelatihan & Pengembangan',
            ],
            'Asuransi' => [
                'Agen & Penasihat Asuransi', 'Aktuaria', 'Klaim Asuransi',
                'Underwriting', 'Manajer Cabang Asuransi', 'Konsultasi Asuransi',
                'Operasional Asuransi',
            ],
            'Teknologi Informasi' => [
                'Administrator Database', 'Analis Bisnis IT', 'Analis Data',
                'Ilmuwan Data', 'Developer (Frontend)', 'Developer (Backend)',
                'Developer (Mobile)', 'Developer (Fullstack)', 'DevOps & SRE',
                'Konsultan IT', 'Manajer IT', 'Keamanan Informasi / Cybersecurity',
                'Machine Learning / AI Engineer', 'Jaringan & Infrastruktur',
                'Manajemen Produk IT', 'QA & Testing', 'Arsitek Solusi',
                'Manajer Proyek IT', 'Dukungan Teknis', 'UI / UX Designer',
            ],
            'Hukum' => [
                'Hukum Perusahaan', 'Kepatuhan & Regulasi', 'Hukum Pidana',
                'Hukum Ketenagakerjaan', 'Hukum Lingkungan', 'Staf Hukum',
                'Pengacara / Advokat', 'Paralegal', 'Manajemen Kontrak',
                'Kekayaan Intelektual', 'Litigasi',
            ],
            'Logistik & Distribusi' => [
                'Kepabeanan', 'Pergudangan & Distribusi', 'Pengemudi',
                'Ekspor / Impor', 'Manajemen Armada', 'Pengiriman & Kurir',
                'Logistik', 'Manajemen Rantai Pasokan', 'Manajemen Transportasi',
                'Perencanaan Inventaris',
            ],
            'Manufaktur & Operasional' => [
                'Perencanaan Produksi', 'Manajemen Kualitas', 'Operasional Pabrik',
                'Teknik Produksi', 'Keselamatan Industri', 'Riset & Pengembangan',
                'Lean / Six Sigma', 'Manajemen Material',
            ],
            'Pemasaran & Digital' => [
                'Pemasaran Konten', 'CRM & Loyalitas', 'Pemasaran Digital',
                'E-Commerce', 'Pemasaran Acara', 'Pemasaran Lapangan',
                'Manajemen Produk', 'Riset Pasar', 'Pemasaran Media Sosial',
                'Strategi Pemasaran', 'SEO / SEM', 'Pemasaran Email',
            ],
            'Pertambangan & Energi' => [
                'Geologi', 'Kesehatan & Keselamatan Tambang', 'Teknik Tambang',
                'Manajemen Operasi Tambang', 'Minyak & Gas', 'Energi Terbarukan',
                'Manajemen Lingkungan Energi',
            ],
            'Properti & Real Estate' => [
                'Manajemen Aset', 'Pengembang Properti', 'Agen Properti',
                'Penilaian Properti', 'Manajemen Properti Komersial',
                'Manajemen Properti Residensial', 'Pemasaran Properti',
            ],
            'Ritel & E-Commerce' => [
                'Manajemen Kategori', 'Manajemen Toko', 'Merchandising',
                'Penjualan Online', 'Staf Kasir', 'Visual Merchandising',
                'Manajemen Stok Ritel',
            ],
            'Penjualan' => [
                'Penjualan Langsung', 'Account Manager', 'Business Development',
                'Penjualan Korporat', 'Inside Sales', 'Key Account Manager',
                'Pre-Sales / Solution Selling', 'Penjualan Ritel',
                'Manajemen Wilayah Penjualan', 'Penjualan Telekomunikasi',
            ],
            'Ilmu Pengetahuan & Riset' => [
                'Bioteknologi', 'Kimia & Farmasi', 'Ilmu Lingkungan',
                'Penelitian Laboratorium', 'Ilmu Pangan', 'Fisika & Material',
                'Riset & Pengembangan', 'Ilmu Sosial',
            ],
            'Telekomunikasi' => [
                'Teknik Jaringan Telko', 'Operasional Telko', 'Penjualan Telko',
                'Layanan Pelanggan Telko', 'Manajemen Produk Telko',
                'Radio Frekuensi (RF)', 'Transmisi & Backbone',
            ],
            'Lainnya' => [
                'Fresh Graduate', 'Part Time / Paruh Waktu', 'Magang / Internship',
                'Freelance / Kontrak', 'Pekerja Rumahan / Remote',
                'Pekerjaan Lainnya',
            ],
        ];

        $sort = 0;
        foreach ($categories as $parentName => $children) {
            $parentId = DB::table('job_categories')->insertGetId([
                'parent_id'  => null,
                'name'       => $parentName,
                'slug'       => Str::slug($parentName),
                'sort_order' => $sort++,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $childSort = 0;
            foreach ($children as $childName) {
                DB::table('job_categories')->insert([
                    'parent_id'  => $parentId,
                    'name'       => $childName,
                    'slug'       => Str::slug($parentName . '-' . $childName),
                    'sort_order' => $childSort++,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Job categories seeded: ' . DB::table('job_categories')->count() . ' records.');
    }
}