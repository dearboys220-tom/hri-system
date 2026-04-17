const styles = `<style>
  #hri-ds{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:#0f172a}
  #hri-ds .wrap{max-width:880px;margin:0 auto;padding:24px 16px}
  #hri-ds header{background:#0A1A3A;color:#fff;border-radius:16px;padding:20px 18px;margin-bottom:16px}
  #hri-ds h1{margin:0 0 6px;font-size:1.35rem;line-height:1.25;color:#fff}
  #hri-ds .meta{opacity:.9;font-size:.9rem;color:#fff}
  #hri-ds .card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px;margin:16px 0}
  #hri-ds h2{font-size:1.1rem;margin:0 0 10px;color:#0A1A3A}
  #hri-ds p{margin:10px 0;line-height:1.8}
  #hri-ds ul,#hri-ds ol{margin:10px 0 10px 20px;line-height:1.8}
  #hri-ds li{margin:6px 0}
  #hri-ds .note{background:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:12px;margin:12px 0}
  #hri-ds .warn{background:#fff7ed;border:1px solid #fdba74;border-radius:12px;padding:12px;margin:12px 0}
  #hri-ds .ok{background:#e8f5e8;border:1px solid #66bb6a;border-radius:12px;padding:12px;margin:12px 0}
  #hri-ds .grid{display:grid;gap:10px}
  #hri-ds .grid.two{grid-template-columns:1fr}
  #hri-ds .legal{font-size:.9rem;opacity:.85}
  @media(min-width:768px){
    #hri-ds .wrap{padding:28px 20px}
    #hri-ds h1{font-size:1.5rem}
    #hri-ds .grid.two{grid-template-columns:1fr 1fr}
  }
</style>`

// ============================================================
// 個人向け — インドネシア語
// ============================================================
export const id_individual = styles + `
<div id="hri-ds"><div class="wrap">
<header>
  <h1>📮 Kebijakan Penanganan Keluhan & Pertanyaan — Anggota HRI (Individu)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA — Merek: HRI<br>
  Terakhir diperbarui: <strong>13 Desember 2025</strong></div>
</header>
<nav class="card toc"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#ds1">Pasal 1 — Tujuan & Prinsip</a></li>
  <li><a href="#ds2">Pasal 2 — Jenis Keluhan & Pertanyaan</a></li>
  <li><a href="#ds3">Pasal 3 — Cara Penyampaian</a></li>
  <li><a href="#ds4">Pasal 4 — Proses Penanganan</a></li>
</ol></div><div><ol start="5">
  <li><a href="#ds5">Pasal 5 — Batasan Penanganan</a></li>
  <li><a href="#ds6">Pasal 6 — Dokumentasi & Kepatuhan</a></li>
  <li><a href="#ds7">Pasal 7 — Perubahan & Bahasa</a></li>
</ol></div></div>
<p class="legal">Dokumen terkait: <strong>Ketentuan Penggunaan HRI</strong>, <strong>Kebijakan Privasi</strong>, dan <strong>Kebijakan Cookie</strong>.</p>
</nav>
<main>
<section class="card" id="ds1"><h2>Pasal 1 — Tujuan & Prinsip</h2>
<p>Kebijakan ini menetapkan mekanisme resmi bagi Anggota Individu untuk menyampaikan <strong>keluhan, pengaduan, atau pertanyaan</strong> terkait penggunaan layanan HRI.</p>
<ul>
  <li>Transparansi dan keterlacakan proses;</li>
  <li>Penanganan yang adil dan proporsional;</li>
  <li>Kepatuhan terhadap hukum yang berlaku.</li>
</ul>
</section>
<section class="card" id="ds2"><h2>Pasal 2 — Jenis Keluhan & Pertanyaan</h2>
<p>Keluhan atau pertanyaan dapat mencakup, antara lain:</p>
<ul>
  <li>Masalah akun, akses, atau keamanan;</li>
  <li>Proses verifikasi dan status <strong>Verified Resume</strong>;</li>
  <li>Akses lowongan atau pengajuan lamaran kerja;</li>
  <li>Dugaan penyalahgunaan atau pelanggaran kebijakan;</li>
  <li>Permintaan klarifikasi kebijakan.</li>
</ul>
</section>
<section class="card" id="ds3"><h2>Pasal 3 — Cara Penyampaian</h2>
<p>Anggota Individu dapat menyampaikan keluhan atau pertanyaan melalui kanal resmi berikut:</p>
<ul>
  <li>Email dukungan: <a href="mailto:support@hri-check.com">support@hri-check.com</a></li>
</ul>
<div class="note">Demi keamanan dan akurasi, HRI dapat meminta verifikasi identitas sebelum memproses keluhan tertentu.</div>
</section>
<section class="card" id="ds4"><h2>Pasal 4 — Proses Penanganan</h2>
<ol>
  <li>Penerimaan dan pencatatan keluhan/pertanyaan.</li>
  <li>Penelaahan awal untuk menentukan ruang lingkup dan prioritas.</li>
  <li>Tindak lanjut dan respons dalam jangka waktu yang wajar.</li>
  <li>Penutupan kasus atau eskalasi internal jika diperlukan.</li>
</ol>
<div class="note">Waktu respons dapat berbeda tergantung kompleksitas dan kebutuhan verifikasi tambahan.</div>
</section>
<section class="card" id="ds5"><h2>Pasal 5 — Batasan Penanganan</h2>
<ul>
  <li>HRI tidak memproses keluhan yang bersifat tidak berdasar, berulang tanpa fakta baru, atau mengandung pelecehan.</li>
  <li>Keluhan terkait keputusan rekrutmen perusahaan berada di luar kewenangan HRI.</li>
</ul>
<div class="warn">HRI berhak menolak atau menghentikan penanganan apabila ditemukan indikasi penyalahgunaan mekanisme keluhan.</div>
</section>
<section class="card" id="ds6"><h2>Pasal 6 — Dokumentasi & Kepatuhan</h2>
<p>Keluhan dan proses penanganannya dapat didokumentasikan untuk tujuan audit, peningkatan layanan, dan kepatuhan hukum, sesuai Kebijakan Privasi HRI.</p>
</section>
<section class="card" id="ds7"><h2>Pasal 7 — Perubahan & Bahasa</h2>
<ol>
  <li>Kebijakan ini dapat diperbarui dari waktu ke waktu.</li>
  <li>Bahasa Indonesia adalah versi resmi. Terjemahan bahasa lain bersifat referensi.</li>
</ol>
<div class="ok">Dengan menggunakan mekanisme ini, Anda memahami dan menyetujui kebijakan penanganan keluhan & pertanyaan ini.</div>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 日本語
// ============================================================
export const ja_individual = styles + `
<div id="hri-ds"><div class="wrap">
<header>
  <h1>📮 苦情・お問い合わせ処理ポリシー — HRI個人会員</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  最終更新日：<strong>2025年12月13日</strong></div>
</header>
<nav class="card toc"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#ds1">第1条 — 目的・原則</a></li>
  <li><a href="#ds2">第2条 — 苦情・お問い合わせの種類</a></li>
  <li><a href="#ds3">第3条 — 申し出方法</a></li>
  <li><a href="#ds4">第4条 — 処理プロセス</a></li>
</ol></div><div><ol start="5">
  <li><a href="#ds5">第5条 — 処理の限界</a></li>
  <li><a href="#ds6">第6条 — 記録・コンプライアンス</a></li>
  <li><a href="#ds7">第7条 — 変更・言語</a></li>
</ol></div></div>
<p class="legal">関連文書：<strong>HRI利用規約</strong>、<strong>プライバシーポリシー</strong>、<strong>Cookieポリシー</strong></p>
</nav>
<main>
<section class="card" id="ds1"><h2>第1条 — 目的・原則</h2>
<p>本ポリシーは、HRIサービスの利用に関する<strong>苦情・申し立て・お問い合わせ</strong>を個人会員が提出するための公式メカニズムを定めます。</p>
<ul>
  <li>プロセスの透明性と追跡可能性；</li>
  <li>公平かつ比例的な対応；</li>
  <li>適用法の遵守。</li>
</ul>
</section>
<section class="card" id="ds2"><h2>第2条 — 苦情・お問い合わせの種類</h2>
<p>苦情やお問い合わせには以下が含まれます：</p>
<ul>
  <li>アカウント・アクセス・セキュリティの問題；</li>
  <li>認証プロセスと<strong>Verified Resume</strong>のステータス；</li>
  <li>求人アクセスや応募申請；</li>
  <li>不正利用やポリシー違反の疑い；</li>
  <li>ポリシーの説明要求。</li>
</ul>
</section>
<section class="card" id="ds3"><h2>第3条 — 申し出方法</h2>
<p>個人会員は以下の公式チャネルを通じて苦情やお問い合わせを提出できます：</p>
<ul>
  <li>サポートメール：<a href="mailto:support@hri-check.com">support@hri-check.com</a></li>
</ul>
<div class="note">セキュリティと正確性のため、特定の苦情を処理する前に身元確認をお願いする場合があります。</div>
</section>
<section class="card" id="ds4"><h2>第4条 — 処理プロセス</h2>
<ol>
  <li>苦情・お問い合わせの受付と記録。</li>
  <li>範囲と優先度を決定するための初期審査。</li>
  <li>合理的な期間内でのフォローアップと回答。</li>
  <li>案件のクローズまたは必要に応じた内部エスカレーション。</li>
</ol>
<div class="note">回答時間は案件の複雑さと追加確認の必要性によって異なる場合があります。</div>
</section>
<section class="card" id="ds5"><h2>第5条 — 処理の限界</h2>
<ul>
  <li>HRIは根拠のない・新たな事実のない繰り返し・嫌がらせを含む苦情は処理しません。</li>
  <li>企業の採用決定に関する苦情はHRIの権限外です。</li>
</ul>
<div class="warn">苦情メカニズムの不正利用の兆候が見られる場合、HRIは処理を拒否または停止する権利を有します。</div>
</section>
<section class="card" id="ds6"><h2>第6条 — 記録・コンプライアンス</h2>
<p>苦情とその処理プロセスは、HRIのプライバシーポリシーに従い、監査・サービス改善・法的コンプライアンスの目的で記録される場合があります。</p>
</section>
<section class="card" id="ds7"><h2>第7条 — 変更・言語</h2>
<ol>
  <li>本ポリシーは随時更新される場合があります。</li>
  <li>インドネシア語版が正式版です。他言語の翻訳は参考情報です。</li>
</ol>
<div class="ok">本メカニズムを利用することにより、本苦情・お問い合わせ処理ポリシーを理解し同意したことを表明します。</div>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 韓国語
// ============================================================
export const ko_individual = styles + `
<div id="hri-ds"><div class="wrap">
<header>
  <h1>📮 불만 및 문의 처리 정책 — HRI 개인 회원</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  최종 업데이트: <strong>2025년 12월 13일</strong></div>
</header>
<nav class="card toc"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#ds1">제1조 — 목적 및 원칙</a></li>
  <li><a href="#ds2">제2조 — 불만 및 문의 유형</a></li>
  <li><a href="#ds3">제3조 — 제출 방법</a></li>
  <li><a href="#ds4">제4조 — 처리 프로세스</a></li>
</ol></div><div><ol start="5">
  <li><a href="#ds5">제5조 — 처리의 한계</a></li>
  <li><a href="#ds6">제6조 — 기록 및 준수</a></li>
  <li><a href="#ds7">제7조 — 변경 및 언어</a></li>
</ol></div></div>
<p class="legal">관련 문서: <strong>HRI 이용약관</strong>, <strong>개인정보처리방침</strong>, <strong>Cookie 정책</strong></p>
</nav>
<main>
<section class="card" id="ds1"><h2>제1조 — 목적 및 원칙</h2>
<p>본 정책은 HRI 서비스 이용과 관련한 <strong>불만, 민원 또는 문의</strong>를 개인 회원이 제출하기 위한 공식 메커니즘을 정합니다.</p>
<ul>
  <li>프로세스의 투명성 및 추적 가능성；</li>
  <li>공정하고 비례적인 처리；</li>
  <li>적용 법률 준수.</li>
</ul>
</section>
<section class="card" id="ds2"><h2>제2조 — 불만 및 문의 유형</h2>
<p>불만이나 문의에는 다음이 포함될 수 있습니다:</p>
<ul>
  <li>계정, 접근 또는 보안 문제；</li>
  <li>인증 프로세스 및 <strong>Verified Resume</strong> 상태；</li>
  <li>채용공고 접근이나 입사 지원；</li>
  <li>부정 이용 또는 정책 위반 의혹；</li>
  <li>정책 설명 요청.</li>
</ul>
</section>
<section class="card" id="ds3"><h2>제3조 — 제출 방법</h2>
<p>개인 회원은 다음 공식 채널을 통해 불만이나 문의를 제출할 수 있습니다:</p>
<ul>
  <li>지원 이메일: <a href="mailto:support@hri-check.com">support@hri-check.com</a></li>
</ul>
<div class="note">보안 및 정확성을 위해 특정 불만을 처리하기 전에 신원 확인을 요청할 수 있습니다.</div>
</section>
<section class="card" id="ds4"><h2>제4조 — 처리 프로세스</h2>
<ol>
  <li>불만/문의의 접수 및 기록.</li>
  <li>범위 및 우선순위 결정을 위한 초기 검토.</li>
  <li>합리적인 기간 내 후속 조치 및 답변.</li>
  <li>사건 종결 또는 필요시 내부 에스컬레이션.</li>
</ol>
<div class="note">응답 시간은 사안의 복잡성과 추가 확인의 필요성에 따라 다를 수 있습니다.</div>
</section>
<section class="card" id="ds5"><h2>제5조 — 처리의 한계</h2>
<ul>
  <li>HRI는 근거 없는, 새로운 사실 없이 반복되는, 또는 괴롭힘을 포함한 불만은 처리하지 않습니다.</li>
  <li>기업의 채용 결정과 관련한 불만은 HRI의 권한 밖입니다.</li>
</ul>
<div class="warn">불만 메커니즘의 오용 징후가 발견되면 HRI는 처리를 거부하거나 중단할 권리를 가집니다.</div>
</section>
<section class="card" id="ds6"><h2>제6조 — 기록 및 준수</h2>
<p>불만과 그 처리 프로세스는 HRI의 개인정보처리방침에 따라 감사, 서비스 개선 및 법적 준수 목적으로 기록될 수 있습니다.</p>
</section>
<section class="card" id="ds7"><h2>제7조 — 변경 및 언어</h2>
<ol>
  <li>본 정책은 수시로 업데이트될 수 있습니다.</li>
  <li>인도네시아어 버전이 공식 버전입니다. 다른 언어 번역본은 참고용입니다.</li>
</ol>
<div class="ok">본 메커니즘을 이용함으로써 본 불만 및 문의 처리 정책을 이해하고 동의했음을 선언합니다.</div>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 英語
// ============================================================
export const en_individual = styles + `
<div id="hri-ds"><div class="wrap">
<header>
  <h1>📮 Complaint & Inquiry Handling Policy — HRI Individual Members</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Last updated: <strong>December 13, 2025</strong></div>
</header>
<nav class="card toc"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#ds1">Article 1 — Purpose & Principles</a></li>
  <li><a href="#ds2">Article 2 — Types of Complaints & Inquiries</a></li>
  <li><a href="#ds3">Article 3 — Submission Method</a></li>
  <li><a href="#ds4">Article 4 — Handling Process</a></li>
</ol></div><div><ol start="5">
  <li><a href="#ds5">Article 5 — Limitations of Handling</a></li>
  <li><a href="#ds6">Article 6 — Documentation & Compliance</a></li>
  <li><a href="#ds7">Article 7 — Changes & Language</a></li>
</ol></div></div>
<p class="legal">Related documents: <strong>HRI Terms of Use</strong>, <strong>Privacy Policy</strong>, <strong>Cookie Policy</strong></p>
</nav>
<main>
<section class="card" id="ds1"><h2>Article 1 — Purpose & Principles</h2>
<p>This policy establishes the official mechanism for Individual Members to submit <strong>complaints, grievances, or inquiries</strong> regarding the use of HRI services.</p>
<ul>
  <li>Transparency and traceability of the process；</li>
  <li>Fair and proportionate handling；</li>
  <li>Compliance with applicable law.</li>
</ul>
</section>
<section class="card" id="ds2"><h2>Article 2 — Types of Complaints & Inquiries</h2>
<p>Complaints or inquiries may include, among others:</p>
<ul>
  <li>Account, access, or security issues；</li>
  <li>Verification process and <strong>Verified Resume</strong> status；</li>
  <li>Job listing access or job application submission；</li>
  <li>Suspected misuse or policy violations；</li>
  <li>Requests for policy clarification.</li>
</ul>
</section>
<section class="card" id="ds3"><h2>Article 3 — Submission Method</h2>
<p>Individual Members may submit complaints or inquiries through the following official channels:</p>
<ul>
  <li>Support email: <a href="mailto:support@hri-check.com">support@hri-check.com</a></li>
</ul>
<div class="note">For security and accuracy, HRI may request identity verification before processing certain complaints.</div>
</section>
<section class="card" id="ds4"><h2>Article 4 — Handling Process</h2>
<ol>
  <li>Receipt and recording of the complaint/inquiry.</li>
  <li>Initial review to determine scope and priority.</li>
  <li>Follow-up and response within a reasonable timeframe.</li>
  <li>Case closure or internal escalation if necessary.</li>
</ol>
<div class="note">Response times may vary depending on complexity and need for additional verification.</div>
</section>
<section class="card" id="ds5"><h2>Article 5 — Limitations of Handling</h2>
<ul>
  <li>HRI does not process complaints that are unfounded, repeatedly submitted without new facts, or contain harassment.</li>
  <li>Complaints regarding company recruitment decisions are outside HRI's authority.</li>
</ul>
<div class="warn">HRI reserves the right to refuse or discontinue handling if there are indications of misuse of the complaint mechanism.</div>
</section>
<section class="card" id="ds6"><h2>Article 6 — Documentation & Compliance</h2>
<p>Complaints and their handling process may be documented for audit, service improvement, and legal compliance purposes, in accordance with HRI's Privacy Policy.</p>
</section>
<section class="card" id="ds7"><h2>Article 7 — Changes & Language</h2>
<ol>
  <li>This policy may be updated from time to time.</li>
  <li>The Indonesian version is the official version. Translations in other languages are for reference only.</li>
</ol>
<div class="ok">By using this mechanism, you confirm that you understand and agree to this complaint & inquiry handling policy.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — インドネシア語
// ============================================================
export const id_company = styles + `
<div id="hri-ds"><div class="wrap">
<header>
  <h1>🗂️ Kebijakan Penanganan Keluhan & Pertanyaan — Perusahaan</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Merek: HRI)<br>
  Berlaku sejak: <strong>13 Desember 2025</strong></div>
</header>
<nav class="card toc"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#ds1">Pasal 1 — Tujuan & Ruang Lingkup</a></li>
  <li><a href="#ds2">Pasal 2 — Jenis Keluhan & Pertanyaan</a></li>
  <li><a href="#ds3">Pasal 3 — Saluran Resmi</a></li>
  <li><a href="#ds4">Pasal 4 — Proses Penanganan</a></li>
</ol></div><div><ol start="5">
  <li><a href="#ds5">Pasal 5 — Batasan Tanggapan</a></li>
  <li><a href="#ds6">Pasal 6 — Laporan Penyalahgunaan</a></li>
  <li><a href="#ds7">Pasal 7 — Perusahaan Asing & Sengketa</a></li>
  <li><a href="#ds8">Pasal 8 — Perubahan & Hukum yang Berlaku</a></li>
</ol></div></div>
<p class="legal">Dokumen terkait: <strong>Ketentuan Penggunaan HRI</strong>, <strong>Kebijakan Privasi</strong>, dan kebijakan HRI lainnya.</p>
</nav>
<main>
<section class="card" id="ds1"><h2>Pasal 1 — Tujuan & Ruang Lingkup</h2>
<p>Kebijakan ini mengatur tata cara <strong>pengajuan, penerimaan, penanganan, dan penyelesaian</strong> keluhan serta pertanyaan yang diajukan oleh <strong>Perusahaan</strong> terkait penggunaan layanan HRI.</p>
</section>
<section class="card" id="ds2"><h2>Pasal 2 — Jenis Keluhan & Pertanyaan</h2>
<ul>
  <li>Masalah teknis dan operasional sistem HRI;</li>
  <li>Pertanyaan mengenai kebijakan dan penggunaan fitur;</li>
  <li>Laporan dugaan pelanggaran kebijakan HRI;</li>
  <li>Permintaan klarifikasi administratif.</li>
</ul>
<div class="note">Keluhan terkait hubungan kerja atau keputusan rekrutmen <strong>bukan</strong> tanggung jawab HRI.</div>
</section>
<section class="card" id="ds3"><h2>Pasal 3 — Saluran Resmi</h2>
<p>Seluruh keluhan dan pertanyaan wajib diajukan melalui <strong>saluran resmi</strong> yang ditentukan oleh HRI, termasuk formulir dalam sistem atau alamat kontak resmi HRI.</p>
<ul>
  <li>Pengajuan harus dilakukan oleh perwakilan Perusahaan yang berwenang;</li>
  <li>Informasi pendukung harus disampaikan secara lengkap dan akurat.</li>
</ul>
</section>
<section class="card" id="ds4"><h2>Pasal 4 — Proses Penanganan</h2>
<ol>
  <li>Penerimaan dan pencatatan keluhan;</li>
  <li>Verifikasi awal dan klasifikasi;</li>
  <li>Penanganan atau eskalasi internal;</li>
  <li>Penyampaian tanggapan sesuai kapasitas HRI.</li>
</ol>
<div class="note">Seluruh proses dicatat dalam <strong>log dan arsip internal</strong>.</div>
</section>
<section class="card" id="ds5"><h2>Pasal 5 — Batasan Tanggapan</h2>
<ul>
  <li>HRI memberikan tanggapan berdasarkan informasi yang tersedia;</li>
  <li>HRI tidak berkewajiban memberikan pendapat hukum atau nasihat profesional;</li>
  <li>Waktu tanggapan dapat bervariasi tergantung kompleksitas kasus.</li>
</ul>
<div class="warn">Tidak ada jaminan penyelesaian sesuai harapan Perusahaan.</div>
</section>
<section class="card" id="ds6"><h2>Pasal 6 — Laporan Penyalahgunaan</h2>
<p>Perusahaan wajib segera melaporkan dugaan penyalahgunaan akun, pelanggaran kebijakan, atau insiden keamanan kepada HRI.</p>
</section>
<section class="card" id="ds7"><h2>Pasal 7 — Perusahaan Asing & Sengketa</h2>
<p>Untuk Perusahaan asing atau isu lintas negara, HRI menangani keluhan terbatas pada layanan platform, sedangkan kepatuhan hukum dan sengketa substansial menjadi tanggung jawab Perusahaan.</p>
</section>
<section class="card" id="ds8"><h2>Pasal 8 — Perubahan & Hukum yang Berlaku</h2>
<ol>
  <li>Kebijakan ini dapat diperbarui dari waktu ke waktu.</li>
  <li>Diatur dan ditafsirkan berdasarkan <strong>hukum Republik Indonesia</strong>.</li>
</ol>
<div class="ok">Dengan mengajukan keluhan atau pertanyaan, Perusahaan menyetujui kebijakan ini.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 日本語
// ============================================================
export const ja_company = styles + `
<div id="hri-ds"><div class="wrap">
<header>
  <h1>🗂️ 苦情・お問い合わせ処理ポリシー — 企業会員</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  施行日：<strong>2025年12月13日</strong></div>
</header>
<nav class="card toc"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#ds1">第1条 — 目的・適用範囲</a></li>
  <li><a href="#ds2">第2条 — 苦情・お問い合わせの種類</a></li>
  <li><a href="#ds3">第3条 — 公式チャネル</a></li>
  <li><a href="#ds4">第4条 — 処理プロセス</a></li>
</ol></div><div><ol start="5">
  <li><a href="#ds5">第5条 — 対応の限界</a></li>
  <li><a href="#ds6">第6条 — 不正利用の報告</a></li>
  <li><a href="#ds7">第7条 — 外国企業・紛争</a></li>
  <li><a href="#ds8">第8条 — 変更・準拠法</a></li>
</ol></div></div>
<p class="legal">関連文書：<strong>HRI利用規約</strong>、<strong>プライバシーポリシー</strong>、その他HRIポリシー</p>
</nav>
<main>
<section class="card" id="ds1"><h2>第1条 — 目的・適用範囲</h2>
<p>本ポリシーは、HRIサービスの利用に関して<strong>企業</strong>が提出する苦情・お問い合わせの<strong>申し出・受付・処理・解決</strong>の手順を規定します。</p>
</section>
<section class="card" id="ds2"><h2>第2条 — 苦情・お問い合わせの種類</h2>
<ul>
  <li>HRIシステムの技術的・運用上の問題；</li>
  <li>ポリシーや機能の利用に関する質問；</li>
  <li>HRIポリシー違反の疑いの申告；</li>
  <li>管理上の説明要求。</li>
</ul>
<div class="note">雇用関係や採用決定に関する苦情はHRIの責任では<strong>ありません</strong>。</div>
</section>
<section class="card" id="ds3"><h2>第3条 — 公式チャネル</h2>
<p>すべての苦情・お問い合わせはHRIが定める<strong>公式チャネル</strong>（システム内フォームまたはHRIの公式連絡先）を通じて提出する必要があります。</p>
<ul>
  <li>提出は企業の権限ある代表者が行うこと；</li>
  <li>補足情報は完全かつ正確に提供すること。</li>
</ul>
</section>
<section class="card" id="ds4"><h2>第4条 — 処理プロセス</h2>
<ol>
  <li>苦情の受付と記録；</li>
  <li>初期確認と分類；</li>
  <li>内部対応またはエスカレーション；</li>
  <li>HRIの能力の範囲内での回答の提供。</li>
</ol>
<div class="note">すべてのプロセスは<strong>ログと内部アーカイブ</strong>に記録されます。</div>
</section>
<section class="card" id="ds5"><h2>第5条 — 対応の限界</h2>
<ul>
  <li>HRIは利用可能な情報に基づいて回答します；</li>
  <li>HRIは法的意見や専門的なアドバイスを提供する義務はありません；</li>
  <li>回答時間は案件の複雑さによって異なる場合があります。</li>
</ul>
<div class="warn">企業の期待通りの解決を保証するものではありません。</div>
</section>
<section class="card" id="ds6"><h2>第6条 — 不正利用の報告</h2>
<p>企業はアカウントの不正利用の疑い・ポリシー違反・セキュリティインシデントをHRIに速やかに報告する義務があります。</p>
</section>
<section class="card" id="ds7"><h2>第7条 — 外国企業・紛争</h2>
<p>外国企業または国際的な問題については、HRIはプラットフォームサービスに限定した苦情を処理します。法的遵守と実質的な紛争は企業の責任です。</p>
</section>
<section class="card" id="ds8"><h2>第8条 — 変更・準拠法</h2>
<ol>
  <li>本ポリシーは随時更新される場合があります。</li>
  <li><strong>インドネシア共和国の法律</strong>に基づき解釈・適用されます。</li>
</ol>
<div class="ok">苦情またはお問い合わせを提出することにより、企業は本ポリシーに同意したものとみなします。</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 韓国語
// ============================================================
export const ko_company = styles + `
<div id="hri-ds"><div class="wrap">
<header>
  <h1>🗂️ 불만 및 문의 처리 정책 — 기업 회원</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  시행일: <strong>2025년 12월 13일</strong></div>
</header>
<nav class="card toc"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#ds1">제1조 — 목적 및 적용 범위</a></li>
  <li><a href="#ds2">제2조 — 불만 및 문의 유형</a></li>
  <li><a href="#ds3">제3조 — 공식 채널</a></li>
  <li><a href="#ds4">제4조 — 처리 프로세스</a></li>
</ol></div><div><ol start="5">
  <li><a href="#ds5">제5조 — 응답의 한계</a></li>
  <li><a href="#ds6">제6조 — 부정 이용 신고</a></li>
  <li><a href="#ds7">제7조 — 외국 기업 및 분쟁</a></li>
  <li><a href="#ds8">제8조 — 변경 및 준거법</a></li>
</ol></div></div>
<p class="legal">관련 문서: <strong>HRI 이용약관</strong>, <strong>개인정보처리방침</strong> 및 기타 HRI 정책</p>
</nav>
<main>
<section class="card" id="ds1"><h2>제1조 — 목적 및 적용 범위</h2>
<p>본 정책은 HRI 서비스 이용과 관련하여 <strong>기업</strong>이 제출하는 불만 및 문의의 <strong>제출, 접수, 처리 및 해결</strong> 절차를 규정합니다.</p>
</section>
<section class="card" id="ds2"><h2>제2조 — 불만 및 문의 유형</h2>
<ul>
  <li>HRI 시스템의 기술적·운영상 문제；</li>
  <li>정책 및 기능 이용에 관한 질문；</li>
  <li>HRI 정책 위반 의혹 신고；</li>
  <li>행정적 설명 요청.</li>
</ul>
<div class="note">고용 관계나 채용 결정과 관련한 불만은 HRI의 책임이 <strong>아닙니다</strong>.</div>
</section>
<section class="card" id="ds3"><h2>제3조 — 공식 채널</h2>
<p>모든 불만 및 문의는 HRI가 정한 <strong>공식 채널</strong>(시스템 내 양식 또는 HRI 공식 연락처)을 통해 제출해야 합니다.</p>
<ul>
  <li>제출은 기업의 권한 있는 대표자가 해야 합니다；</li>
  <li>지원 정보는 완전하고 정확하게 제공해야 합니다.</li>
</ul>
</section>
<section class="card" id="ds4"><h2>제4조 — 처리 프로세스</h2>
<ol>
  <li>불만의 접수 및 기록；</li>
  <li>초기 확인 및 분류；</li>
  <li>내부 처리 또는 에스컬레이션；</li>
  <li>HRI의 능력 범위 내에서의 답변 제공.</li>
</ol>
<div class="note">모든 프로세스는 <strong>로그 및 내부 아카이브</strong>에 기록됩니다.</div>
</section>
<section class="card" id="ds5"><h2>제5조 — 응답의 한계</h2>
<ul>
  <li>HRI는 이용 가능한 정보를 기반으로 응답합니다；</li>
  <li>HRI는 법적 의견이나 전문적 조언을 제공할 의무가 없습니다；</li>
  <li>응답 시간은 사안의 복잡성에 따라 다를 수 있습니다.</li>
</ul>
<div class="warn">기업이 기대하는 방식으로의 해결을 보장하지 않습니다.</div>
</section>
<section class="card" id="ds6"><h2>제6조 — 부정 이용 신고</h2>
<p>기업은 계정 오용 의혹, 정책 위반 또는 보안 사고를 즉시 HRI에 신고할 의무가 있습니다.</p>
</section>
<section class="card" id="ds7"><h2>제7조 — 외국 기업 및 분쟁</h2>
<p>외국 기업 또는 국제 이슈의 경우 HRI는 플랫폼 서비스에 한정된 불만을 처리하며, 법적 준수와 실질적 분쟁은 기업의 책임입니다.</p>
</section>
<section class="card" id="ds8"><h2>제8조 — 변경 및 준거법</h2>
<ol>
  <li>본 정책은 수시로 업데이트될 수 있습니다.</li>
  <li><strong>인도네시아 공화국 법률</strong>에 따라 해석·적용됩니다.</li>
</ol>
<div class="ok">불만 또는 문의를 제출함으로써 기업은 본 정책에 동의한 것으로 간주합니다.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 英語
// ============================================================
export const en_company = styles + `
<div id="hri-ds"><div class="wrap">
<header>
  <h1>🗂️ Complaint & Inquiry Handling Policy — Corporate Members</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Effective date: <strong>December 13, 2025</strong></div>
</header>
<nav class="card toc"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#ds1">Article 1 — Purpose & Scope</a></li>
  <li><a href="#ds2">Article 2 — Types of Complaints & Inquiries</a></li>
  <li><a href="#ds3">Article 3 — Official Channels</a></li>
  <li><a href="#ds4">Article 4 — Handling Process</a></li>
</ol></div><div><ol start="5">
  <li><a href="#ds5">Article 5 — Limitations of Response</a></li>
  <li><a href="#ds6">Article 6 — Reporting Misuse</a></li>
  <li><a href="#ds7">Article 7 — Foreign Companies & Disputes</a></li>
  <li><a href="#ds8">Article 8 — Changes & Governing Law</a></li>
</ol></div></div>
<p class="legal">Related documents: <strong>HRI Terms of Use</strong>, <strong>Privacy Policy</strong>, and other HRI policies.</p>
</nav>
<main>
<section class="card" id="ds1"><h2>Article 1 — Purpose & Scope</h2>
<p>This policy governs the procedures for <strong>submission, receipt, handling, and resolution</strong> of complaints and inquiries submitted by <strong>Companies</strong> regarding the use of HRI services.</p>
</section>
<section class="card" id="ds2"><h2>Article 2 — Types of Complaints & Inquiries</h2>
<ul>
  <li>Technical and operational issues with the HRI system；</li>
  <li>Questions about policies and feature usage；</li>
  <li>Reports of suspected HRI policy violations；</li>
  <li>Requests for administrative clarification.</li>
</ul>
<div class="note">Complaints related to employment relationships or recruitment decisions are <strong>not</strong> HRI's responsibility.</div>
</section>
<section class="card" id="ds3"><h2>Article 3 — Official Channels</h2>
<p>All complaints and inquiries must be submitted through <strong>official channels</strong> designated by HRI, including in-system forms or HRI's official contact address.</p>
<ul>
  <li>Submissions must be made by an authorized company representative；</li>
  <li>Supporting information must be provided completely and accurately.</li>
</ul>
</section>
<section class="card" id="ds4"><h2>Article 4 — Handling Process</h2>
<ol>
  <li>Receipt and recording of complaints；</li>
  <li>Initial verification and classification；</li>
  <li>Internal handling or escalation；</li>
  <li>Providing a response within HRI's capacity.</li>
</ol>
<div class="note">All processes are recorded in <strong>logs and internal archives</strong>.</div>
</section>
<section class="card" id="ds5"><h2>Article 5 — Limitations of Response</h2>
<ul>
  <li>HRI provides responses based on available information；</li>
  <li>HRI is not obligated to provide legal opinions or professional advice；</li>
  <li>Response times may vary depending on case complexity.</li>
</ul>
<div class="warn">There is no guarantee of resolution in accordance with the Company's expectations.</div>
</section>
<section class="card" id="ds6"><h2>Article 6 — Reporting Misuse</h2>
<p>Companies are obligated to immediately report suspected account misuse, policy violations, or security incidents to HRI.</p>
</section>
<section class="card" id="ds7"><h2>Article 7 — Foreign Companies & Disputes</h2>
<p>For foreign companies or cross-border issues, HRI handles complaints limited to platform services, while legal compliance and substantive disputes are the Company's responsibility.</p>
</section>
<section class="card" id="ds8"><h2>Article 8 — Changes & Governing Law</h2>
<ol>
  <li>This policy may be updated from time to time.</li>
  <li>Governed and interpreted in accordance with the <strong>laws of the Republic of Indonesia</strong>.</li>
</ol>
<div class="ok">By submitting a complaint or inquiry, the Company agrees to this policy.</div>
</section>
</main></div></div>`

// ============================================================
// エクスポート
// ============================================================
export default {
  id_individual,
  id_company,
  ja_individual,
  ja_company,
  ko_individual,
  ko_company,
  en_individual,
  en_company,
}