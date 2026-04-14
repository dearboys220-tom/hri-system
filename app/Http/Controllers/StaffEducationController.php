<?php

namespace App\Http\Controllers;

use App\Models\StaffEducationRecord;
use App\Models\StaffActivityLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class StaffEducationController extends Controller
{
    // ──────────────────────────────────────────────────────
    // 必須モジュール定義
    // ──────────────────────────────────────────────────────
    public static function modules(): array
    {
        return [
            [
                'code'          => 'company_rules',
                'title'         => 'Peraturan Perusahaan HRI',
                'description'   => 'Memahami aturan dasar perusahaan, kode etik, dan tanggung jawab karyawan.',
                'required_score' => 80,
                'restricted_features' => ['Semua fitur sistem'],
                'content'       => [
                    ['heading' => '1. Visi & Misi HRI', 'body' => 'HRI adalah sistem sertifikasi SDM yang beroperasi di Indonesia dengan misi menyediakan layanan verifikasi latar belakang yang terpercaya, transparan, dan sesuai hukum yang berlaku. Seluruh karyawan wajib memahami dan mendukung visi ini dalam setiap pekerjaan sehari-hari.'],
                    ['heading' => '2. Kode Etik Karyawan', 'body' => 'Karyawan HRI dilarang: (a) memberikan informasi internal kepada pihak luar tanpa izin resmi, (b) menggunakan data anggota untuk kepentingan pribadi, (c) menerima gratifikasi dari anggota atau calon anggota, (d) membuat pernyataan publik atas nama perusahaan tanpa persetujuan atasan.'],
                    ['heading' => '3. Struktur Organisasi', 'body' => 'HRI memiliki 5 departemen: Divisi Investigasi, Divisi Manajemen Penilaian, Divisi Manajemen Strategi, Divisi Pengembangan AI, dan Divisi Pemasaran. Setiap karyawan bertanggung jawab kepada Manajer Lokal dan rantai komando yang berlaku.'],
                    ['heading' => '4. Jam Kerja & Kehadiran', 'body' => 'Karyawan wajib mengisi presensi harian melalui sistem GPS. Ketidakhadiran tanpa keterangan akan dicatat sebagai pelanggaran. Permohonan izin atau sakit harus diajukan melalui sistem sebelum atau sesegera mungkin setelah ketidakhadiran terjadi.'],
                    ['heading' => '5. Sanksi & Disiplin', 'body' => 'Pelanggaran aturan perusahaan akan ditangani melalui prosedur: teguran lisan → peringatan pertama → peringatan kedua → peringatan terakhir → tindakan disiplin lebih lanjut sesuai hukum ketenagakerjaan Indonesia (UU Ketenagakerjaan).'],
                ],
                'quiz' => [
                    ['question' => 'Apa yang DILARANG dilakukan oleh karyawan HRI?', 'options' => ['Menggunakan sistem internal untuk pekerjaan', 'Memberikan informasi internal kepada pihak luar tanpa izin', 'Mengajukan laporan tugas tepat waktu', 'Mengikuti pelatihan perusahaan'], 'answer' => 1],
                    ['question' => 'Berapa jumlah departemen di HRI?', 'options' => ['3 departemen', '4 departemen', '5 departemen', '6 departemen'], 'answer' => 2],
                    ['question' => 'Apa prosedur pertama jika terjadi pelanggaran ringan?', 'options' => ['Pemutusan hubungan kerja', 'Peringatan terakhir', 'Teguran lisan', 'Pengurangan gaji'], 'answer' => 2],
                    ['question' => 'Kapan karyawan harus mengajukan permohonan izin?', 'options' => ['Seminggu sebelumnya saja', 'Sebelum atau sesegera mungkin setelah ketidakhadiran', 'Hanya saat sakit keras', 'Tidak perlu, cukup tidak hadir'], 'answer' => 1],
                    ['question' => 'Apa yang dimaksud dengan gratifikasi yang dilarang?', 'options' => ['Bonus dari perusahaan', 'Komisi resmi dari HRI', 'Pemberian dari anggota atau calon anggota untuk kepentingan pribadi', 'Insentif kinerja yang disetujui'], 'answer' => 2],
                ],
            ],
            [
                'code'          => 'pdp_basic',
                'title'         => 'Dasar-Dasar Perlindungan Data Pribadi (PDP)',
                'description'   => 'Memahami Undang-Undang PDP Indonesia dan kewajiban karyawan dalam menangani data pribadi.',
                'required_score' => 80,
                'restricted_features' => ['Akses ke semua data pribadi anggota'],
                'content'       => [
                    ['heading' => '1. Apa itu UU PDP?', 'body' => 'Undang-Undang Perlindungan Data Pribadi (UU PDP) adalah regulasi Indonesia yang mengatur pengumpulan, penggunaan, penyimpanan, dan penghapusan data pribadi. Setiap karyawan yang menangani data anggota wajib memahami dan mematuhi UU ini.'],
                    ['heading' => '2. Data Pribadi yang Dilindungi', 'body' => 'Data yang dilindungi meliputi: nama lengkap, NIK, alamat, nomor telepon, riwayat pekerjaan, riwayat pendidikan, data keuangan, dan informasi kesehatan. Data ini TIDAK boleh dibagikan, disalin, atau digunakan di luar tujuan yang telah disetujui oleh pemilik data.'],
                    ['heading' => '3. Prinsip Pemrosesan Data', 'body' => 'Data pribadi hanya boleh diproses jika: (a) ada persetujuan tertulis dari pemilik data, (b) sesuai dengan tujuan yang dinyatakan saat pengumpulan, (c) tidak lebih dari yang diperlukan (data minimization). Setiap akses ke data anggota dicatat dalam sistem audit.'],
                    ['heading' => '4. Hak-Hak Pemilik Data', 'body' => 'Anggota berhak: (a) mengetahui data apa yang tersimpan, (b) meminta koreksi data yang salah, (c) meminta penghapusan data (right to be forgotten), (d) menarik persetujuan kapan saja. Karyawan WAJIB membantu pemenuhan hak-hak ini melalui jalur resmi.'],
                    ['heading' => '5. Konsekuensi Pelanggaran PDP', 'body' => 'Pelanggaran UU PDP dapat berujung pada sanksi pidana dan denda bagi perusahaan maupun individu. Karyawan yang terbukti melanggar akan diproses sesuai prosedur disiplin perusahaan dan dapat dilaporkan kepada otoritas yang berwenang.'],
                ],
                'quiz' => [
                    ['question' => 'Apa yang dimaksud dengan prinsip "data minimization"?', 'options' => ['Mengumpulkan sebanyak mungkin data', 'Hanya mengumpulkan data yang benar-benar diperlukan', 'Menghapus semua data setelah digunakan', 'Menyimpan data di perangkat pribadi'], 'answer' => 1],
                    ['question' => 'Kapan data pribadi anggota boleh diproses?', 'options' => ['Kapan saja jika diperlukan', 'Hanya jika ada persetujuan tertulis dari pemilik data', 'Jika diminta oleh rekan kerja', 'Jika data sudah lama disimpan'], 'answer' => 1],
                    ['question' => 'Apa hak anggota terkait data mereka?', 'options' => ['Tidak memiliki hak apapun setelah mendaftar', 'Berhak meminta koreksi dan penghapusan data', 'Hanya bisa melihat data, tidak bisa mengubah', 'Hanya manajer yang bisa mengakses data anggota'], 'answer' => 1],
                    ['question' => 'Apa konsekuensi pelanggaran UU PDP bagi karyawan?', 'options' => ['Hanya diperingatkan secara lisan', 'Dapat dikenai sanksi disiplin dan dilaporkan ke otoritas', 'Tidak ada konsekuensi selama data tidak bocor', 'Cukup membayar denda perusahaan'], 'answer' => 1],
                ],
            ],
            [
                'code'          => 'privacy_and_data_handling',
                'title'         => 'Privasi & Penanganan Data di HRI',
                'description'   => 'Prosedur teknis penanganan data anggota dalam sistem HRI sehari-hari.',
                'required_score' => 80,
                'restricted_features' => ['Mengunggah laporan tugas', 'Melihat profil staf lain'],
                'content'       => [
                    ['heading' => '1. Akses Data Berdasarkan Kebutuhan', 'body' => 'Karyawan hanya boleh mengakses data yang diperlukan untuk tugas mereka. Investigator mengakses data untuk keperluan investigasi. Admin mengakses data untuk pengelolaan kasus. Mengakses data di luar tugas resmi adalah pelanggaran.'],
                    ['heading' => '2. Larangan Berbagi Data', 'body' => 'Data anggota TIDAK BOLEH: dikirim melalui WhatsApp/LINE pribadi, difoto menggunakan kamera ponsel pribadi, disalin ke dokumen pribadi, dibagikan kepada pihak yang tidak berkepentingan. Semua transfer data harus melalui sistem resmi HRI.'],
                    ['heading' => '3. Penanganan Laporan Tugas', 'body' => 'Saat mengunggah laporan tugas, pastikan: hanya menyertakan informasi yang relevan dengan tugas, tidak menyertakan data pribadi yang tidak perlu, foto bukti diambil sesuai instruksi dan tidak menyertakan informasi pihak ketiga yang tidak terlibat.'],
                    ['heading' => '4. Prosedur Jika Menemukan Kebocoran Data', 'body' => 'Jika karyawan menemukan atau mencurigai kebocoran data: (a) segera laporkan kepada atasan langsung, (b) jangan mencoba "memperbaiki" sendiri, (c) dokumentasikan apa yang diketahui, (d) kerjasama penuh dengan investigasi internal. Waktu respons maksimal 2 jam sejak diketahui.'],
                ],
                'quiz' => [
                    ['question' => 'Melalui mana transfer data anggota HARUS dilakukan?', 'options' => ['WhatsApp pribadi untuk kecepatan', 'Email pribadi karyawan', 'Sistem resmi HRI', 'Google Drive pribadi'], 'answer' => 2],
                    ['question' => 'Apa yang harus dilakukan jika menemukan kebocoran data?', 'options' => ['Diam saja agar tidak menimbulkan masalah', 'Segera laporkan kepada atasan dalam 2 jam', 'Perbaiki sendiri terlebih dahulu', 'Beritahu rekan kerja untuk minta saran'], 'answer' => 1],
                    ['question' => 'Siapa yang boleh mengakses data anggota?', 'options' => ['Semua karyawan tanpa terkecuali', 'Hanya karyawan yang memiliki tugas terkait data tersebut', 'Hanya manajer', 'Siapapun yang meminta'], 'answer' => 1],
                ],
            ],
            [
                'code'          => 'prohibition_on_private_storage',
                'title'         => 'Larangan Penyimpanan Data di Perangkat Pribadi',
                'description'   => 'Memahami larangan menyimpan data HRI di perangkat atau penyimpanan pribadi.',
                'required_score' => 80,
                'restricted_features' => ['Mengunggah foto bukti tugas'],
                'content'       => [
                    ['heading' => '1. Perangkat Pribadi vs Perangkat Kerja', 'body' => 'Perangkat pribadi (ponsel, laptop, tablet pribadi) TIDAK BOLEH digunakan untuk menyimpan data anggota HRI. Penyimpanan data hanya boleh dilakukan melalui sistem HRI yang terenkripsi. Jika membutuhkan perangkat kerja, hubungi manajer lokal.'],
                    ['heading' => '2. Larangan Penyimpanan Cloud Pribadi', 'body' => 'Layanan cloud pribadi seperti Google Drive pribadi, Dropbox, iCloud, OneDrive pribadi TIDAK BOLEH digunakan untuk menyimpan dokumen atau data yang berkaitan dengan pekerjaan HRI. Gunakan hanya sistem penyimpanan resmi yang disediakan oleh HRI.'],
                    ['heading' => '3. Foto Bukti Tugas', 'body' => 'Foto yang diambil untuk keperluan bukti tugas HARUS: diunggah langsung melalui sistem HRI, tidak disimpan di galeri ponsel setelah diunggah, tidak dikirim melalui media sosial atau aplikasi pesan pribadi. Foto yang tidak sesuai prosedur akan dianggap tidak sah sebagai bukti.'],
                    ['heading' => '4. Konsekuensi Penyimpanan Pribadi', 'body' => 'Karyawan yang terbukti menyimpan data anggota di perangkat pribadi akan mendapatkan: peringatan pertama untuk pelanggaran pertama, peringatan kedua disertai pembatasan akses untuk pelanggaran berulang, dan dapat dilanjutkan ke proses disiplin lebih berat sesuai peraturan perusahaan.'],
                ],
                'quiz' => [
                    ['question' => 'Apakah boleh menyimpan data anggota di Google Drive pribadi?', 'options' => ['Boleh jika folder dipassword', 'Boleh untuk sementara saja', 'TIDAK BOLEH, harus menggunakan sistem resmi HRI', 'Boleh jika disetujui rekan kerja'], 'answer' => 2],
                    ['question' => 'Setelah mengunggah foto bukti tugas ke sistem HRI, apa yang harus dilakukan?', 'options' => ['Simpan di galeri sebagai cadangan', 'Kirim ke WhatsApp atasan sebagai konfirmasi', 'Hapus dari galeri ponsel', 'Tidak perlu melakukan apapun'], 'answer' => 2],
                    ['question' => 'Apa yang terjadi jika karyawan berulang kali menyimpan data di perangkat pribadi?', 'options' => ['Tidak ada konsekuensi', 'Hanya ditegur sekali', 'Mendapat peringatan kedua dan pembatasan akses', 'Langsung dipecat'], 'answer' => 2],
                ],
            ],
            [
                'code'          => 'payment_and_approval_rules',
                'title'         => 'Aturan Pembayaran & Persetujuan',
                'description'   => 'Memahami alur persetujuan, batas kewenangan, dan aturan pengeluaran di HRI.',
                'required_score' => 80,
                'restricted_features' => ['Melihat & menyetujui perhitungan gaji', 'Melihat catatan penggajian'],
                'content'       => [
                    ['heading' => '1. Hierarki Persetujuan', 'body' => 'Semua persetujuan mengikuti hierarki resmi: Manajer Lokal (local_manager) → Presiden (president) → YUHEI (Pemilik/Keputusan Akhir). Persetujuan tidak boleh diwakilkan atau dilewati. Tidak ada "persetujuan mewakili" yang diakui oleh sistem.'],
                    ['heading' => '2. Batas Pengeluaran', 'body' => 'Pengeluaran operasional di lapangan sebesar Rp3.000.000 atau kurang (biaya transportasi, alat tulis, kartu nama, utilitas) dapat disetujui oleh LEE (Kepala Pelaksana Lapangan). Pengeluaran di atas batas ini atau yang menyangkut hukum, pajak, dan data pribadi HARUS diteruskan kepada YUHEI.'],
                    ['heading' => '3. Larangan Pemecahan Anggaran', 'body' => 'Dilarang keras memecah satu pengeluaran menjadi beberapa transaksi kecil untuk menghindari batas persetujuan. Contoh: pengeluaran Rp5.000.000 TIDAK BOLEH dipecah menjadi dua transaksi Rp2.500.000. Pelanggaran ini termasuk kategori kecurangan keuangan.'],
                    ['heading' => '4. Proses Penggajian', 'body' => 'Penggajian diproses melalui alur: AI (Draft Perhitungan) → Manajer Lokal (Konfirmasi) → Presiden (Persetujuan) → YUHEI (Keputusan Akhir untuk kasus khusus). Tidak ada karyawan yang boleh mengubah angka gaji tanpa melalui proses persetujuan resmi ini.'],
                    ['heading' => '5. Dokumentasi Keuangan', 'body' => 'Semua pengeluaran harus didokumentasikan dengan: kwitansi/struk resmi, tujuan pengeluaran yang jelas, persetujuan dari pihak yang berwenang. Pengeluaran tanpa dokumentasi tidak akan diproses. Dokumen keuangan dikirim ke akuntan eksternal setiap bulan.'],
                ],
                'quiz' => [
                    ['question' => 'Siapa yang berwenang menyetujui pengeluaran lapangan di bawah Rp3.000.000?', 'options' => ['Semua karyawan bisa menyetujui sendiri', 'LEE (Kepala Pelaksana Lapangan)', 'YUHEI saja', 'Manajer Lokal mana saja'], 'answer' => 1],
                    ['question' => 'Apakah boleh memecah pengeluaran Rp6.000.000 menjadi dua transaksi Rp3.000.000?', 'options' => ['Boleh jika ada keperluan mendesak', 'Boleh jika disetujui rekan kerja', 'TIDAK BOLEH, termasuk kecurangan keuangan', 'Boleh asal ada kwitansi'], 'answer' => 2],
                    ['question' => 'Siapa yang memberikan persetujuan AKHIR dalam proses penggajian reguler?', 'options' => ['AI (sistem)', 'Manajer Lokal', 'Presiden', 'YUHEI untuk kasus khusus, Presiden untuk reguler'], 'answer' => 3],
                    ['question' => 'Apa yang diperlukan untuk setiap pengeluaran operasional?', 'options' => ['Cukup laporan lisan kepada atasan', 'Kwitansi resmi, tujuan jelas, dan persetujuan berwenang', 'Hanya persetujuan rekan kerja', 'Tidak perlu dokumentasi untuk jumlah kecil'], 'answer' => 1],
                ],
            ],
        ];
    }

    // ──────────────────────────────────────────────────────
    // ポータル画面
    // ──────────────────────────────────────────────────────
    public function index()
    {
        $user = Auth::user();
        $completedCodes = StaffEducationRecord::completedCodes($user->id);

        $modules = collect(self::modules())->map(function ($module) use ($completedCodes) {
            $module['is_completed'] = in_array($module['code'], $completedCodes);
            unset($module['content'], $module['quiz']); // 一覧には含めない
            return $module;
        })->toArray();

        $totalCount     = count($modules);
        $completedCount = count($completedCodes);
        $allCompleted   = $completedCount >= $totalCount;

        return Inertia::render('Staff/Education/Index', [
            'modules'        => $modules,
            'completedCount' => $completedCount,
            'totalCount'     => $totalCount,
            'allCompleted'   => $allCompleted,
        ]);
    }

    // ──────────────────────────────────────────────────────
    // モジュール詳細・クイズ画面
    // ──────────────────────────────────────────────────────
    public function show(string $moduleCode)
    {
        $user   = Auth::user();
        $module = collect(self::modules())->firstWhere('code', $moduleCode);

        if (!$module) {
            abort(404);
        }

        $record = StaffEducationRecord::where('staff_user_id', $user->id)
            ->where('module_code', $moduleCode)
            ->first();

        $module['is_completed']   = $record?->is_completed ?? false;
        $module['quiz_score']     = $record?->quiz_score;
        $module['attempt_count']  = $record?->attempt_count ?? 0;
        $module['completed_at']   = $record?->completed_at?->toDateTimeString();

        return Inertia::render('Staff/Education/Module', [
            'module' => $module,
        ]);
    }

    // ──────────────────────────────────────────────────────
    // クイズ送信・完了登録
    // ──────────────────────────────────────────────────────
    public function complete(Request $request, string $moduleCode)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $user   = Auth::user();
        $module = collect(self::modules())->firstWhere('code', $moduleCode);

        if (!$module) {
            return back()->withErrors(['module' => 'Modul tidak ditemukan.']);
        }

        $quiz    = $module['quiz'];
        $answers = $request->input('answers'); // ['0' => 1, '1' => 2, ...]

        // スコア計算
        $correct = 0;
        foreach ($quiz as $i => $q) {
            if (isset($answers[$i]) && (int)$answers[$i] === $q['answer']) {
                $correct++;
            }
        }
        $score      = (int) round(($correct / count($quiz)) * 100);
        $isPassed   = $score >= $module['required_score'];

        // レコードを upsert
        $record = StaffEducationRecord::firstOrNew([
            'staff_user_id' => $user->id,
            'module_code'   => $moduleCode,
        ]);
        $record->attempt_count++;
        $record->quiz_score = $score;

        if ($isPassed && !$record->is_completed) {
            $record->is_completed = true;
            $record->completed_at = Carbon::now();
        }
        $record->save();

        // staff_activity_logs に記録（Section 31.5 準拠）
        if ($isPassed) {
            try {
                \App\Models\StaffActivityLog::create([
                    'staff_user_id' => $user->id,
                    'action_type'   => 'EDUCATION_COMPLETED',
                    'description'   => "モジュール「{$module['title']}」完了（スコア: {$score}点）",
                    'target_table'  => 'staff_education_records',
                    'target_id'     => $record->id,
                    'ip_address'    => $request->ip(),
                ]);
            } catch (\Exception $e) {
                // ログテーブルのカラム名が異なる場合は無視
            }

            AuditLog::create([
                'action_type'      => 'EDUCATION_COMPLETED',
                'actor_user_id'    => $user->id,
                'target_table'     => 'staff_education_records',
                'target_record_id' => $record->id,
                'description'      => "教育モジュール完了: {$moduleCode} | スコア: {$score}",
                'ip_address'       => $request->ip(),
            ]);
        }

        return back()->with([
            'quiz_result' => [
                'score'      => $score,
                'is_passed'  => $isPassed,
                'correct'    => $correct,
                'total'      => count($quiz),
                'required'   => $module['required_score'],
            ],
        ]);
    }
}