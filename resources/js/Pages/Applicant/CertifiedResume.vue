<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    profile:        Object,
    isValid:        Boolean,
    daysRemaining:  Number,
    educations:     Array,
    works:          Array,
    certifications: Array,
});

const months = ['Januari','Februari','Maret','April','Mei','Juni',
                'Juli','Agustus','September','Oktober','November','Desember'];

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

function formatPeriod(start, end) {
    if (!start) return '-';
    return `${formatDate(start)} - ${end ? formatDate(end) : 'Sekarang'}`;
}

function printPage() {
    window.print();
}
</script>

<template>
    <Head title="CV Tersertifikasi HRI" />

    <!-- ========== NAV BAR ========== -->
    <div class="resume-nav print-hide">
        <div class="nav-content">
            <h1 class="nav-title">🏆 Riwayat Hidup Tersertifikasi oleh HRI</h1>
            <div class="nav-actions">
                <Link href="/applicant/dashboard" class="nav-btn secondary">← Kembali ke Profil</Link>
                <button @click="printPage" class="nav-btn primary" :disabled="!isValid">
                    📥 Simpan CV
                </button>
            </div>
        </div>
    </div>

    <!-- 期限警告 -->
    <div v-if="!isValid" class="expiry-warning expired print-hide">
        Sertifikasi telah kedaluwarsa. Silakan ajukan sertifikasi ulang.
        <Link href="/applicant/confirmation" style="color:#B91C1C;text-decoration:underline;margin-left:10px;">
            Ajukan Sertifikasi Ulang →
        </Link>
    </div>
    <div v-else-if="daysRemaining <= 7" class="expiry-warning print-hide">
        Sertifikasi akan kedaluwarsa dalam {{ daysRemaining }} hari. Segera ajukan perpanjangan.
    </div>

    <!-- ========== RESUME BODY ========== -->
    <div class="resume-container">
        <div id="certified-resume">

            <!-- ===== HEADER ===== -->
            <div class="resume-header">

                <!-- プロフィール写真 -->
                <div class="photo-col">
                    <div class="photo-circle"
                        :style="profile.profile_photo
                            ? `background-image:url('/storage/${profile.profile_photo}')`
                            : 'background:#8aacbb'">
                        <span v-if="!profile.profile_photo" style="font-size:48px;color:white;">👤</span>
                    </div>
                </div>

                <!-- 個人情報 -->
                <div class="info-col">
                    <h1 class="cv-name">{{ profile.full_name }}</h1>
                    <p class="cv-ref">Nomor Rujukan HRI: <strong>{{ profile.member_id }}</strong></p>
                    <p class="cv-tagline">Profesional Tersertifikasi oleh HRI</p>
                    <div v-if="profile.current_address" class="cv-address">
                        <strong>Alamat:</strong> {{ profile.current_address }}
                    </div>
                    <div class="cv-contacts">
                        <div><strong>Email:</strong> {{ profile.email || '-' }}</div>
                        <div><strong>Tel:</strong> {{ profile.phone_number || '-' }}</div>
                        <div v-if="profile.whatsapp_number"><strong>WA:</strong> {{ profile.whatsapp_number }}</div>
                    </div>
                </div>

                <!-- HRI認証パネル（斜め白） -->
                <div class="cert-panel">
                    <div class="cert-panel-content">
                        <img src="/images/hri-certified-logo.png" alt="HRI Certified"
                            class="cert-logo"
                            onerror="this.style.display='none'" />
                        <p class="cert-title">HRI CERTIFIED</p>
                        <div class="cert-dates">
                            <div>Sertifikasi: {{ formatDate(profile.certification_date) }}</div>
                            <div>Berlaku hingga: {{ formatDate(profile.certification_expiry_date) }}</div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /HEADER -->

            <!-- ===== MAIN CONTENT ===== -->
            <div class="resume-body">

                <!-- 個人情報詳細 -->
                <section class="resume-section">
                    <h2 class="section-title">Informasi Personal</h2>
                    <div class="info-two-col">
                        <table class="info-table">
                            <tr>
                                <td class="info-label">Identitas</td>
                                <td class="verified">✅ Terverifikasi oleh HRI</td>
                            </tr>
                            <tr>
                                <td class="info-label">Tanggal Lahir</td>
                                <td>{{ formatDate(profile.birth_date) }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Jenis Kelamin</td>
                                <td>{{ profile.gender || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Kewarganegaraan</td>
                                <td>{{ profile.nationality || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Status Pernikahan</td>
                                <td>{{ profile.marital_status || '-' }}</td>
                            </tr>
                        </table>
                        <table class="info-table">
                            <tr>
                                <td class="info-label">Email</td>
                                <td>{{ profile.email || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Telepon</td>
                                <td>{{ profile.phone_number || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">WhatsApp</td>
                                <td>{{ profile.whatsapp_number || '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </section>

                <!-- 学歴 -->
                <section class="resume-section">
                    <h2 class="section-title">Riwayat Pendidikan</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Sekolah</th>
                                <th>Tingkat Pendidikan</th>
                                <th>Jurusan</th>
                                <th>Alamat Sekolah</th>
                                <th>Periode</th>
                                <th>IPK / Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!educations || educations.length === 0">
                                <td colspan="6" class="empty-cell">Riwayat pendidikan tidak tersedia</td>
                            </tr>
                            <tr v-for="e in educations" :key="e.id">
                                <td>
                                    {{ e.school || '-' }}
                                    <div v-if="e.achievements" class="sub-note">Prestasi: {{ e.achievements }}</div>
                                </td>
                                <td>{{ e.level || '-' }}</td>
                                <td>
                                    {{ e.major || '-' }}
                                    <span v-if="e.degree" class="sub-note"> ({{ e.degree }})</span>
                                </td>
                                <td>N/A</td>
                                <td>{{ formatPeriod(e.enrollment_date, e.graduation_date) }}</td>
                                <td>{{ e.gpa || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- 職歴 -->
                <section class="resume-section">
                    <h2 class="section-title">Riwayat Pekerjaan</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Perusahaan</th>
                                <th>Jabatan / Posisi</th>
                                <th>Alamat Perusahaan</th>
                                <th>Jenis Pekerjaan</th>
                                <th>Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!works || works.length === 0">
                                <td colspan="5" class="empty-cell">Riwayat pekerjaan tidak tersedia</td>
                            </tr>
                            <tr v-for="w in works" :key="w.id">
                                <td>{{ w.company || '-' }}</td>
                                <td>
                                    {{ w.position || '-' }}
                                    <div v-if="w.duties" class="sub-note">{{ w.duties }}</div>
                                </td>
                                <td>N/A</td>
                                <td>{{ w.employment_type || '-' }}</td>
                                <td>{{ formatPeriod(w.start_date, w.end_date) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- 資格・証明書 -->
                <section class="resume-section">
                    <h2 class="section-title">Sertifikat &amp; Kualifikasi</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Sertifikat</th>
                                <th>Skor / Level / Tingkatan</th>
                                <th>Instansi Penerbit</th>
                                <th>Tanggal Terbit</th>
                                <th>Masa Berlaku</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!certifications || certifications.length === 0">
                                <td colspan="5" class="empty-cell">Sertifikat tidak tersedia</td>
                            </tr>
                            <tr v-for="c in certifications" :key="c.id">
                                <td>{{ c.name || '-' }}</td>
                                <td>N/A</td>
                                <td>{{ c.organization || '-' }}</td>
                                <td>{{ formatDate(c.issued_date) }}</td>
                                <td>{{ c.valid_until ? formatDate(c.valid_until) : 'Seumur hidup' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

            </div>
            <!-- /MAIN CONTENT -->

        </div><!-- /certified-resume -->
    </div><!-- /resume-container -->

    <!-- フッター免責事項 -->
    <div class="footer-section">
        <div class="footer-disclaimer">
            <div>1. Sertifikat ini diterbitkan oleh HRI Company berdasarkan hasil investigasi, penilaian, dan persetujuan manajemen.</div>
            <div>2. Sertifikat ini bukan merupakan jaminan mutlak kelayakan kerja, melainkan hasil penilaian faktual dan kredibel sesuai standar internal HRI Company.</div>
            <div>3. Memuat hanya hasil penilaian akhir, tanpa menyertakan data investigasi internal.</div>
            <div>4. Sertifikat ini hanya dipergunakan untuk tujuan terbatas dan tidak boleh dipergunakan untuk hal lain yang bukan merupakan tujuan verifikasi.</div>
            <div>5. Keaslian dokumen dan penilaian dapat diverifikasi melalui Nomor Rujukan HRI di sistem resmi https://hri-check.com/company/</div>
        </div>
    </div>

    <!-- ========== 印刷時のウォーターマーク ========== -->
    <div class="print-watermark">
        <img src="/images/hri-certified-logo.png" alt="" />
        <div class="wm-text">HRI CERTIFIED</div>
        <div class="wm-sub">{{ formatDate(profile?.certification_date) }}</div>
        <div class="wm-sub">Berlaku hingga: {{ formatDate(profile?.certification_expiry_date) }}</div>
    </div>

</template>

<style scoped>
/* ============================================================
   NAV
   ============================================================ */
.resume-nav {
    background: linear-gradient(135deg, #1976D2, #1565C0);
    color: white; padding: 18px 0;
    position: sticky; top: 0; z-index: 100;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}
.nav-content {
    max-width: 960px; margin: 0 auto; padding: 0 24px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
}
.nav-title { font-size: 20px; font-weight: 700; margin: 0; }
.nav-actions { display: flex; gap: 10px; }
.nav-btn {
    padding: 10px 22px; border-radius: 8px;
    font-size: 13px; font-weight: 600;
    text-decoration: none; border: none; cursor: pointer; transition: all 0.2s;
}
.nav-btn.primary { background: #FF6B35; color: white; }
.nav-btn.primary:hover:not(:disabled) { background: #e55a26; transform: translateY(-1px); }
.nav-btn.primary:disabled { background: #ccc; cursor: not-allowed; }
.nav-btn.secondary { background: rgba(255,255,255,0.15); color: white; }
.nav-btn.secondary:hover { background: rgba(255,255,255,0.25); }

/* ============================================================
   ALERT
   ============================================================ */
.expiry-warning {
    max-width: 960px; margin: 14px auto; padding: 12px 20px;
    border-radius: 8px; text-align: center; font-weight: 600; font-size: 14px;
    background: #FEF3C7; border: 1px solid #F59E0B; color: #92400E;
}
.expiry-warning.expired { background: #FEE2E2; border-color: #DC2626; color: #B91C1C; }

/* ============================================================
   CONTAINER
   ============================================================ */
.resume-container {
    max-width: 960px; margin: 24px auto 0; padding: 0 16px;
}
#certified-resume {
    background: white;
    box-shadow: 0 4px 30px rgba(0,0,0,0.10);
    overflow: hidden;
}

/* ============================================================
   HEADER
   ※ flexboxで認証パネルを完全に高さ合わせ
   ============================================================ */
.resume-header {
    background: #7499AB;
    color: white;
    display: flex;           /* ← gridからflexに変更して高さを完全一致 */
    align-items: stretch;    /* ← 各列を同じ高さに */
    min-height: 220px;
    overflow: hidden;
}

/* --- 写真 --- */
.photo-col {
    flex: 0 0 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 28px 10px 28px 24px;
}
.photo-circle {
    width: 140px; height: 140px;
    border-radius: 50%;
    border: 3px solid white;
    background: #8aacbb center/cover no-repeat;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

/* --- 個人情報 --- */
.info-col {
    flex: 1 1 auto;
    padding: 28px 16px;
}
.cv-name {
    font-size: 30px; font-weight: 800;
    margin: 0 0 6px;
    text-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.cv-ref { font-size: 13px; font-weight: 700; color: #000080; margin: 0 0 4px; }
.cv-tagline { font-size: 14px; opacity: 0.9; margin: 0 0 14px; }
.cv-address {
    font-size: 13px; line-height: 1.5;
    padding: 8px 0; margin-bottom: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.25);
}
.cv-contacts {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 4px 16px; font-size: 13px;
}

/* --- 認証パネル（斜め白） --- */
.cert-panel {
    flex: 0 0 230px;
    background: white;
    /* clip-pathで左端だけ斜めに切る。align-self:stretchで上下の線が出ない */
    clip-path: polygon(18% 0%, 101% 0%, 101% 100%, 0% 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    /* 上下の線を確実に消すため高さをヘッダーより少し大きくする */
    margin-top: -2px;
    margin-bottom: -2px;
    margin-right: -2px;
}
.cert-panel-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    padding: 28px 20px 28px 48px;
    text-align: center;
}
.cert-logo { width: 130px; height: auto; mix-blend-mode: multiply; }
.cert-title { font-size: 15px; font-weight: 800; color: #1565C0; letter-spacing: 1px; margin: 0; }
.cert-dates { font-size: 10px; color: #555; line-height: 1.8; }

/* ============================================================
   BODY
   ============================================================ */
.resume-body { padding: 20px 24px 16px; }
.resume-section { margin-bottom: 20px; }

.section-title {
    font-size: 17px; font-weight: 800;
    color: #7499AB;
    border-bottom: 2px solid #7499AB;
    padding-bottom: 6px; margin: 0 0 12px;
}

/* 個人情報2カラム */
.info-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.info-table { width: 100%; border-collapse: collapse; }
.info-table td { padding: 7px 4px; font-size: 13px; border: none; }
.info-label { font-weight: 700; width: 45%; color: #374151; }
.verified { color: #16a34a; font-weight: 700; }

/* データテーブル（学歴・職歴・資格） */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    background: #7499AB; color: white;
    border: 1px solid #b0c4ce;
    padding: 10px 12px;
    text-align: left; font-size: 12px; font-weight: 700;
}
.data-table td {
    border: 1px solid #dde5eb;
    padding: 9px 12px;
    font-size: 12px; vertical-align: top;
    color: #1e293b;
}
.data-table tbody tr:nth-child(even) td { background: #f7fafc; }
.empty-cell {
    text-align: center; color: #9CA3AF;
    padding: 20px; font-style: italic;
}
.sub-note {
    font-size: 11px; color: #6B7280;
    margin-top: 3px; line-height: 1.5;
}

/* ============================================================
   FOOTER
   ============================================================ */
.footer-section {
    max-width: 960px; margin: 0 auto 40px; padding: 0 16px;
}
.footer-disclaimer {
    padding: 12px 16px;
    border-top: 2px solid #7499AB;
    font-size: 10px; color: #6B7280; line-height: 1.8;
    background: white;
    box-shadow: 0 4px 30px rgba(0,0,0,0.10);
}
.footer-disclaimer div { margin-bottom: 2px; }

/* ============================================================
   WATERMARK（印刷時のみ表示）
   ============================================================ */
.print-watermark {
    display: none;
}

/* ============================================================
   PRINT
   ============================================================ */
@media print {
    .print-hide { display: none !important; }
    .resume-container { max-width: none !important; margin: 0 !important; padding: 0 !important; }
    .footer-section { max-width: none !important; margin: 0 !important; padding: 0 !important; }
    #certified-resume { box-shadow: none !important; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    @page { size: A4; margin: 4mm 2mm; }
    .data-table { page-break-inside: auto; }
    .data-table tr { page-break-inside: avoid; }
    .section-title { page-break-after: avoid; }

    /* ウォーターマーク：印刷時のみ表示 */
    .print-watermark {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.07;
        pointer-events: none;
        z-index: 9999;
        gap: 4px;
        width: 300px;
    }
    .print-watermark img {
        width: 260px;
        height: auto;
    }
    .wm-text {
        font-size: 28px;
        font-weight: 900;
        color: #1565C0;
        letter-spacing: 4px;
        text-align: center;
    }
    .wm-sub {
        font-size: 12px;
        color: #333;
        text-align: center;
    }
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 700px) {
    .resume-header { flex-wrap: wrap; }
    .cert-panel { display: none; }
    .info-two-col { grid-template-columns: 1fr; }
    .nav-title { font-size: 15px; }
    .photo-circle { width: 80px; height: 80px; }
    .cv-name { font-size: 20px; }
    .cv-contacts { grid-template-columns: 1fr; }
}
</style>