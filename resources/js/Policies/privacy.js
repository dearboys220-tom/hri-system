// ============================================================
// 共通スタイル
// ============================================================
const styles = `<style>
  #hri-pp{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:#0f172a}
  #hri-pp .wrap{max-width:880px;margin:0 auto;padding:24px 16px}
  #hri-pp header{background:#0A1A3A;color:#fff;border-radius:16px;padding:20px 18px;margin-bottom:16px}
  #hri-pp h1{margin:0 0 6px;font-size:1.35rem;line-height:1.25;color:#fff}
  #hri-pp .meta{opacity:.9;font-size:.9rem;color:#fff}
  #hri-pp .card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px;margin:16px 0}
  #hri-pp h2{font-size:1.1rem;margin:0 0 10px;color:#0A1A3A}
  #hri-pp h3{font-size:1rem;margin:14px 0 8px;color:#1e3a8a}
  #hri-pp p{margin:10px 0;line-height:1.8}
  #hri-pp ul,#hri-pp ol{margin:10px 0 10px 20px;line-height:1.8}
  #hri-pp li{margin:6px 0}
  #hri-pp .note{background:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:12px;margin:12px 0}
  #hri-pp .warn{background:#fff7ed;border:1px solid #fdba74;border-radius:12px;padding:12px;margin:12px 0}
  #hri-pp .ok{background:#e8f5e8;border:1px solid #66bb6a;border-radius:12px;padding:12px;margin:12px 0}
  #hri-pp .grid{display:grid;gap:10px}
  #hri-pp .grid.two{grid-template-columns:1fr}
  #hri-pp .legal{font-size:.9rem;opacity:.85}
  @media(min-width:768px){
    #hri-pp .wrap{padding:28px 20px}
    #hri-pp h1{font-size:1.5rem}
    #hri-pp .grid.two{grid-template-columns:1fr 1fr}
  }
</style>`

// ============================================================
// 個人向け — インドネシア語
// ============================================================
export const id_individual = styles + `
<div id="hri-pp"><div class="wrap">
<header>
  <h1>🔒 Kebijakan Privasi Anggota HRI (Individu)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA — Merek: HRI<br>
  Terakhir diperbarui: <strong>20 November 2025</strong></div>
</header>
<nav class="card toc"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#pp1">Pasal 1 — Posisi & Ruang Lingkup</a></li>
  <li><a href="#pp2">Pasal 2 — Prinsip Dasar</a></li>
  <li><a href="#pp3">Pasal 3 — Jenis Data Pribadi</a></li>
  <li><a href="#pp4">Pasal 4 — Tujuan Pemrosesan</a></li>
  <li><a href="#pp5">Pasal 5 — Lowongan & Lamaran Kerja</a></li>
  <li><a href="#pp6">Pasal 6 — Verified Resume</a></li>
  <li><a href="#pp7">Pasal 7 — Dasar Hukum</a></li>
</ol></div><div><ol start="8">
  <li><a href="#pp8">Pasal 8 — Penyimpanan & Retensi</a></li>
  <li><a href="#pp9">Pasal 9 — Keamanan & Log</a></li>
  <li><a href="#pp10">Pasal 10 — Hak Subjek Data</a></li>
  <li><a href="#pp11">Pasal 11 — Transfer Lintas Batas</a></li>
  <li><a href="#pp12">Pasal 12 — Kontak & DPO</a></li>
  <li><a href="#pp13">Pasal 13 — Perubahan & Bahasa</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="pp1"><h2>Pasal 1 — Posisi & Ruang Lingkup</h2>
<p>Kebijakan Privasi ini menjelaskan bagaimana Data Pribadi ditangani saat menggunakan layanan HRI melalui https://hri-check.com dan subdomain terkait, termasuk fitur verifikasi dan Verified Resume.</p>
<p>Kebijakan ini berlaku bagi perorangan (seperti pencari kerja, karyawan, atau individu lain) yang membuat akun, menggunakan fitur verifikasi, Verified Resume, atau layanan lain yang tersedia di Layanan.</p>
</section>
<section class="card" id="pp2"><h2>Pasal 2 — Prinsip Dasar</h2>
<ol>
  <li>Kami memproses Data Pribadi secara sah, adil, transparan, terbatas pada tujuan tertentu, dan meminimalkan data.</li>
  <li>Kami menerapkan akurasi, pembatasan penyimpanan, integritas & kerahasiaan, serta akuntabilitas.</li>
  <li>Kami mematuhi UU PDP (UU No.27/2022), PP 71/2019, dan peraturan terkait lainnya.</li>
</ol>
</section>
<section class="card" id="pp3"><h2>Pasal 3 — Jenis Data Pribadi yang Diproses</h2>
<p>Kami dapat memproses kategori data berikut (sesuai kebutuhan fitur yang Anda gunakan):</p>
<ul>
  <li><strong>Identitas & akun</strong>: nama, email, nomor telepon, kredensial akun.</li>
  <li><strong>Profil & resume</strong>: pendidikan, pengalaman kerja, sertifikasi, kemampuan, portofolio.</li>
  <li><strong>Data verifikasi</strong>: dokumen pendukung yang Anda berikan, hasil verifikasi, catatan validasi.</li>
  <li><strong>Data lamaran</strong>: lowongan yang dilamar, waktu pengiriman, status proses di platform.</li>
  <li><strong>Data teknis</strong>: alamat IP, user agent, dan parameter teknis untuk keamanan & audit.</li>
</ul>
<div class="warn"><strong>Perlindungan nomor identifikasi (mis. NIK):</strong> HRI menerapkan pembatasan ketat dan tidak menjadikan nomor identifikasi sebagai konten yang bebas diakses. Prinsip layanan adalah <strong>tampilan online (reference only)</strong> untuk penerima.</div>
</section>
<section class="card" id="pp4"><h2>Pasal 4 — Tujuan Pemrosesan</h2>
<ol>
  <li>Menyediakan dan mengelola akun, profil, serta akses fitur Layanan.</li>
  <li>Melakukan proses verifikasi sesuai permintaan Anda dan menerbitkan <strong>Verified Resume</strong>.</li>
  <li>Menyediakan fitur lowongan kerja dan <strong>lamaran kerja</strong> bagi anggota individu.</li>
  <li>Menjaga keamanan sistem, pencegahan penipuan, serta kepatuhan hukum dan audit internal.</li>
  <li>Perbaikan layanan, termasuk analitik berbasis cookie (lihat Kebijakan Cookie).</li>
</ol>
</section>
<section class="card" id="pp5"><h2>Pasal 5 — Lowongan & Lamaran Kerja</h2>
<p>Anggota individu dapat melihat lowongan yang dipublikasikan oleh perusahaan anggota HRI dan mengajukan lamaran melalui Layanan. Saat Anda mengajukan lamaran, kami dapat membagikan data tertentu kepada perusahaan tujuan <strong>sebatas untuk tujuan rekrutmen</strong>.</p>
<div class="note"><strong>Data yang umumnya dibagikan saat lamaran:</strong>
<ul>
  <li>Informasi profil/resume yang relevan untuk rekrutmen;</li>
  <li>Kontak yang diperlukan agar perusahaan dapat menghubungi Anda;</li>
  <li>Jika menggunakan Verified Resume, aksesnya diberikan sebagai <strong>reference only</strong>.</li>
</ul></div>
<div class="warn"><strong>Batasan:</strong> Keputusan rekrutmen berada pada tanggung jawab perusahaan. HRI tidak mengendalikan kebijakan internal perusahaan.</div>
</section>
<section class="card" id="pp6"><h2>Pasal 6 — Verified Resume (Reference Only)</h2>
<p>HRI memposisikan Verified Resume sebagai referensi terbatas dengan prinsip <strong>"tampilan online (reference only)"</strong> kepada penerima dan pembatasan ketat terhadap data identifikasi sensitif.</p>
<ul>
  <li>Akses penerima bersifat terbatas, berbasis mekanisme yang sah, dan untuk tujuan rekrutmen yang wajar.</li>
  <li>Detail periode dan biaya layanan Verified Resume dikelola sebagai kebijakan layanan terpisah.</li>
</ul>
</section>
<section class="card" id="pp7"><h2>Pasal 7 — Dasar Hukum Pemrosesan</h2>
<ul>
  <li>Persetujuan Anda (mis. pembuatan akun, pengajuan verifikasi);</li>
  <li>Pelaksanaan kontrak (Ketentuan Penggunaan) untuk menyediakan Layanan;</li>
  <li>Kepatuhan kewajiban hukum;</li>
  <li>Kepentingan yang sah (mis. pencegahan penipuan dan keamanan platform).</li>
</ul>
</section>
<section class="card" id="pp8"><h2>Pasal 8 — Penyimpanan & Retensi</h2>
<p>Kami menyimpan Data Pribadi selama diperlukan untuk tujuan pemrosesan, kepatuhan hukum, penyelesaian sengketa, dan keamanan sistem. Retensi dapat berbeda tergantung jenis data dan kebutuhan layanan.</p>
</section>
<section class="card" id="pp9"><h2>Pasal 9 — Keamanan & Log</h2>
<ol>
  <li>Kami menerapkan langkah teknis dan organisasional yang wajar untuk melindungi Data Pribadi dari akses tidak sah, kehilangan, atau kebocoran.</li>
  <li>Kami dapat menyimpan catatan teknis (log) seperti IP address dan user agent untuk keamanan dan pemenuhan kewajiban hukum.</li>
  <li>Log dilindungi sebagai Data Pribadi dan hanya diakses pihak berwenang.</li>
</ol>
</section>
<section class="card" id="pp10"><h2>Pasal 10 — Hak Subjek Data</h2>
<p>Anda memiliki hak sesuai UU PDP, termasuk meminta akses, perbaikan, penghapusan, penarikan persetujuan, dan mekanisme lain sebagaimana diatur kebijakan terkait. Untuk prosedur rinci, lihat "Kebijakan Hak Subjek Data HRI".</p>
</section>
<section class="card" id="pp11"><h2>Pasal 11 — Transfer Lintas Batas Data Pribadi</h2>
<p>Jika Data Pribadi ditransfer ke luar wilayah Indonesia, kami memastikan transfer mematuhi persyaratan perlindungan data yang berlaku, termasuk perlindungan kontraktual, teknis, dan organisasional yang memadai.</p>
</section>
<section class="card" id="pp12"><h2>Pasal 12 — Kontak, Keluhan, & DPO</h2>
<div class="ok">
<p><strong>Kontak Umum & Keluhan Data Pribadi</strong><br>
Email: <a href="mailto:support@hri-check.com">support@hri-check.com</a><br>
Ruko Golden Boulevard Blok S-18, RT 003/RW 06,<br>
Kel. Lengkong Karya, Kec. Serpong Utara,<br>
Kota Tangerang Selatan, Banten 15310, Indonesia</p>
<hr>
<p><strong>Data Protection Officer (DPO) Internal</strong><br>
Nama: <strong>Mohammad Iqbal Ibnu Anshori</strong></p>
</div>
</section>
<section class="card" id="pp13"><h2>Pasal 13 — Perubahan Kebijakan & Bahasa</h2>
<ol>
  <li>Kami dapat memperbarui kebijakan ini dari waktu ke waktu. Jika perubahan material, kami akan memberikan pemberitahuan sebelumnya.</li>
  <li>Jika terdapat perbedaan penafsiran antara Bahasa Indonesia dan terjemahan bahasa lain, maka <strong>versi Bahasa Indonesia</strong> yang berlaku.</li>
</ol>
<div class="note">Dengan menggunakan Layanan HRI, Anda menyatakan telah membaca dan memahami Kebijakan Privasi ini.</div>
</section>
<section class="card"><h2>Informasi Perusahaan</h2>
<p class="legal">PT. NIKI KINDAICHI THREE INDONESIA (Merek: HRI)<br>Situs: https://hri-check.com</p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 日本語
// ============================================================
export const ja_individual = styles + `
<div id="hri-pp"><div class="wrap">
<header>
  <h1>🔒 HRI プライバシーポリシー（個人会員）</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  最終更新日：<strong>2025年11月20日</strong></div>
</header>
<nav class="card toc"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#pp1">第1条 — 位置づけ・適用範囲</a></li>
  <li><a href="#pp2">第2条 — 基本原則</a></li>
  <li><a href="#pp3">第3条 — 処理する個人データの種類</a></li>
  <li><a href="#pp4">第4条 — 処理目的</a></li>
  <li><a href="#pp5">第5条 — 求人・応募</a></li>
  <li><a href="#pp6">第6条 — Verified Resume</a></li>
  <li><a href="#pp7">第7条 — 処理の法的根拠</a></li>
</ol></div><div><ol start="8">
  <li><a href="#pp8">第8条 — 保存・保持期間</a></li>
  <li><a href="#pp9">第9条 — セキュリティ・ログ</a></li>
  <li><a href="#pp10">第10条 — データ主体の権利</a></li>
  <li><a href="#pp11">第11条 — 国際データ移転</a></li>
  <li><a href="#pp12">第12条 — 連絡先・DPO</a></li>
  <li><a href="#pp13">第13条 — 変更・言語</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="pp1"><h2>第1条 — 位置づけ・適用範囲</h2>
<p>本プライバシーポリシーは、https://hri-check.com および関連サブドメインを通じてHRIサービスを利用する際の個人データの取り扱いを説明します（認証機能・Verified Resumeを含む）。</p>
<p>本ポリシーは、アカウントを作成し、認証機能・Verified Resume・その他のサービスを利用する個人（求職者・従業員・その他の個人）に適用されます。</p>
</section>
<section class="card" id="pp2"><h2>第2条 — 基本原則</h2>
<ol>
  <li>個人データは適法・公正・透明な方法で、特定の目的に限定し、最小限のデータで処理します。</li>
  <li>正確性・保存制限・完全性・機密性・説明責任の原則を適用します。</li>
  <li>インドネシアPDP法（法律第27号/2022年）、PP 71/2019、および関連規制を遵守します。</li>
</ol>
</section>
<section class="card" id="pp3"><h2>第3条 — 処理する個人データの種類</h2>
<p>利用する機能に応じて以下のカテゴリのデータを処理する場合があります：</p>
<ul>
  <li><strong>身元・アカウント</strong>：氏名、メールアドレス、電話番号、アカウント認証情報。</li>
  <li><strong>プロフィール・履歴書</strong>：学歴、職歴、資格、スキル、ポートフォリオ。</li>
  <li><strong>認証データ</strong>：提出いただいた補足資料、認証結果、検証記録。</li>
  <li><strong>応募データ</strong>：応募した求人、提出日時、プラットフォーム上の処理状況。</li>
  <li><strong>技術データ</strong>：IPアドレス、ユーザーエージェント、セキュリティ・監査用の技術パラメータ。</li>
</ul>
<div class="warn"><strong>識別番号（NIK等）の保護：</strong>HRIは識別番号を自由にアクセスできるコンテンツとはせず、厳格な制限を適用します。受信者へのサービス原則は<strong>オンライン表示（参照のみ）</strong>です。</div>
</section>
<section class="card" id="pp4"><h2>第4条 — 処理目的</h2>
<ol>
  <li>アカウント・プロフィール・サービス機能へのアクセスの提供・管理。</li>
  <li>リクエストに応じた認証プロセスの実施と<strong>Verified Resume</strong>の発行。</li>
  <li>個人会員向けの求人情報・<strong>応募</strong>機能の提供。</li>
  <li>システムセキュリティ・詐欺防止・法的遵守・内部監査の維持。</li>
  <li>サービス改善（Cookie分析を含む。Cookieポリシーを参照）。</li>
</ol>
</section>
<section class="card" id="pp5"><h2>第5条 — 求人・応募（個人会員専用）</h2>
<p>個人会員はHRI企業会員が公開する求人を閲覧し、サービスを通じて応募できます。応募時、特定データを応募先企業に<strong>採用目的のみ</strong>で共有する場合があります。</p>
<div class="note"><strong>応募時に一般的に共有されるデータ：</strong>
<ul>
  <li>採用に関連するプロフィール・履歴書情報；</li>
  <li>企業が連絡するために必要な連絡先；</li>
  <li>Verified Resumeを使用する場合、<strong>参照のみ</strong>（限定的オンライン表示）としてアクセスが提供されます。</li>
</ul></div>
<div class="warn"><strong>制限：</strong>採用の意思決定は企業の責任です。HRIは企業の内部方針を管理しません。</div>
</section>
<section class="card" id="pp6"><h2>第6条 — Verified Resume（参照のみ原則）</h2>
<p>HRIはVerified Resumeを受信者（企業等）への<strong>「オンライン表示（参照のみ）」</strong>原則による限定的な参照資料として位置づけ、機密識別データに対する厳格な制限を適用します。</p>
<ul>
  <li>受信者のアクセスは正当なメカニズムに基づき制限され、採用・評価の合理的な目的のためです。</li>
  <li>Verified Resumeサービスの期間・料金の詳細は別途サービスポリシーとして管理されます。</li>
</ul>
</section>
<section class="card" id="pp7"><h2>第7条 — 処理の法的根拠</h2>
<ul>
  <li>お客様の同意（アカウント作成・認証申請・特定機能の利用等）；</li>
  <li>契約の履行（利用規約）によるサービス提供；</li>
  <li>法的義務の遵守（システムセキュリティ・適切な記録を含む）；</li>
  <li>正当な利益（詐欺防止・プラットフォームセキュリティ等）。</li>
</ul>
</section>
<section class="card" id="pp8"><h2>第8条 — 保存・保持期間</h2>
<p>個人データは、処理目的・法的遵守・紛争解決・システムセキュリティに必要な期間保存します。保持期間はデータの種類とサービスの必要性によって異なる場合があります。</p>
</section>
<section class="card" id="pp9"><h2>第9条 — セキュリティ・ログ</h2>
<ol>
  <li>不正アクセス・紛失・漏洩から個人データを保護するための合理的な技術的・組織的措置を講じます。</li>
  <li>セキュリティと法的義務の遵守のため、IPアドレスやユーザーエージェントなどの技術記録（ログ）を保存する場合があります。</li>
  <li>ログは個人を特定できる範囲で個人データとして保護され、権限のある当事者のみがアクセスします。</li>
</ol>
</section>
<section class="card" id="pp10"><h2>第10条 — データ主体の権利</h2>
<p>PDP法に基づき、アクセス・訂正・削除・同意の撤回（許可される範囲で）およびその他のメカニズムを求める権利を有します。詳細な手順については「HRIデータ主体の権利ポリシー」をご覧ください。</p>
</section>
<section class="card" id="pp11"><h2>第11条 — 国際データ移転</h2>
<p>個人データをインドネシア域外に移転する場合、契約上・技術的・組織的な適切な保護措置を含む適用データ保護要件に準拠した移転を確保します。必要に応じて通知・同意を行います。</p>
</section>
<section class="card" id="pp12"><h2>第12条 — 連絡先・苦情・DPO</h2>
<div class="ok">
<p><strong>一般連絡先・個人データに関する苦情</strong><br>
メール：<a href="mailto:support@hri-check.com">support@hri-check.com</a><br>
Ruko Golden Boulevard Blok S-18, RT 003/RW 06,<br>
Kel. Lengkong Karya, Kec. Serpong Utara,<br>
Kota Tangerang Selatan, Banten 15310, Indonesia</p>
<hr>
<p><strong>社内データ保護責任者（DPO）</strong><br>
氏名：<strong>Mohammad Iqbal Ibnu Anshori</strong></p>
</div>
</section>
<section class="card" id="pp13"><h2>第13条 — ポリシーの変更・言語</h2>
<ol>
  <li>本ポリシーは随時更新される場合があります。重要な変更がある場合は、事前にサイトまたは適切な媒体を通じて通知します。</li>
  <li>インドネシア語版と他言語の翻訳との間に解釈の相違がある場合、<strong>インドネシア語版</strong>が優先されます。</li>
</ol>
<div class="note">HRIサービスを利用することにより、本プライバシーポリシーを読み理解したことを表明します。</div>
</section>
<section class="card"><h2>会社情報</h2>
<p class="legal">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>サイト：https://hri-check.com</p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 韓国語
// ============================================================
export const ko_individual = styles + `
<div id="hri-pp"><div class="wrap">
<header>
  <h1>🔒 HRI 개인정보처리방침 (개인 회원)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  최종 업데이트: <strong>2025년 11월 20일</strong></div>
</header>
<nav class="card toc"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#pp1">제1조 — 위치 및 적용 범위</a></li>
  <li><a href="#pp2">제2조 — 기본 원칙</a></li>
  <li><a href="#pp3">제3조 — 처리하는 개인정보 유형</a></li>
  <li><a href="#pp4">제4조 — 처리 목적</a></li>
  <li><a href="#pp5">제5조 — 채용공고 및 지원</a></li>
  <li><a href="#pp6">제6조 — Verified Resume</a></li>
  <li><a href="#pp7">제7조 — 처리의 법적 근거</a></li>
</ol></div><div><ol start="8">
  <li><a href="#pp8">제8조 — 저장 및 보존</a></li>
  <li><a href="#pp9">제9조 — 보안 및 로그</a></li>
  <li><a href="#pp10">제10조 — 정보주체의 권리</a></li>
  <li><a href="#pp11">제11조 — 국가 간 데이터 이전</a></li>
  <li><a href="#pp12">제12조 — 연락처 및 DPO</a></li>
  <li><a href="#pp13">제13조 — 변경 및 언어</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="pp1"><h2>제1조 — 위치 및 적용 범위</h2>
<p>본 개인정보처리방침은 https://hri-check.com 및 관련 서브도메인을 통해 HRI 서비스를 이용할 때 개인정보가 처리되는 방식을 설명합니다(인증 기능 및 Verified Resume 포함).</p>
<p>본 방침은 계정을 생성하고 인증 기능, Verified Resume 또는 기타 서비스를 이용하는 개인(구직자, 직원 또는 기타 개인)에게 적용됩니다.</p>
</section>
<section class="card" id="pp2"><h2>제2조 — 기본 원칙</h2>
<ol>
  <li>개인정보를 합법적·공정·투명하게, 특정 목적으로 제한하여, 최소화하여 처리합니다.</li>
  <li>정확성, 보존 제한, 무결성·기밀성, 책임성 원칙을 적용합니다.</li>
  <li>인도네시아 PDP법(법률 제27호/2022년), PP 71/2019 및 관련 규정을 준수합니다.</li>
</ol>
</section>
<section class="card" id="pp3"><h2>제3조 — 처리하는 개인정보 유형</h2>
<p>이용하는 기능에 따라 다음 카테고리의 데이터를 처리할 수 있습니다:</p>
<ul>
  <li><strong>신원 및 계정</strong>: 이름, 이메일, 전화번호, 계정 인증 정보.</li>
  <li><strong>프로필 및 이력서</strong>: 학력, 경력, 자격증, 능력, 포트폴리오.</li>
  <li><strong>인증 데이터</strong>: 제출하신 지원 서류, 인증 결과, 검증 기록.</li>
  <li><strong>지원 데이터</strong>: 지원한 채용공고, 제출 시간, 플랫폼 내 처리 상태.</li>
  <li><strong>기술 데이터</strong>: IP 주소, 사용자 에이전트, 보안 및 감사용 기술 매개변수.</li>
</ul>
<div class="warn"><strong>식별 번호(NIK 등) 보호:</strong> HRI는 식별 번호를 자유롭게 접근 가능한 콘텐츠로 취급하지 않으며 엄격한 제한을 적용합니다. 서비스 원칙은 수신자에게 <strong>온라인 표시(참조 전용)</strong>입니다.</div>
</section>
<section class="card" id="pp4"><h2>제4조 — 처리 목적</h2>
<ol>
  <li>계정, 프로필 및 서비스 기능 접근의 제공 및 관리.</li>
  <li>요청에 따른 인증 프로세스 수행 및 <strong>Verified Resume</strong> 발급.</li>
  <li>개인 회원을 위한 채용공고 및 <strong>입사 지원</strong> 기능 제공.</li>
  <li>시스템 보안, 사기 방지, 법적 준수 및 내부 감사 유지.</li>
  <li>Cookie 기반 분석을 포함한 서비스 개선(Cookie 정책 참조).</li>
</ol>
</section>
<section class="card" id="pp5"><h2>제5조 — 채용공고 및 지원 (개인 회원 전용)</h2>
<p>개인 회원은 HRI 기업 회원이 게시한 채용공고를 열람하고 서비스를 통해 지원할 수 있습니다. 지원 시 특정 데이터를 지원 기업에 <strong>채용 목적으로만</strong> 공유할 수 있습니다.</p>
<div class="note"><strong>지원 시 일반적으로 공유되는 데이터:</strong>
<ul>
  <li>채용에 관련된 프로필/이력서 정보;</li>
  <li>기업이 연락하는 데 필요한 연락처;</li>
  <li>Verified Resume를 사용하는 경우 <strong>참조 전용</strong>(제한적 온라인 열람)으로 접근이 제공됩니다.</li>
</ul></div>
<div class="warn"><strong>제한:</strong> 채용 결정은 기업의 책임입니다. HRI는 기업의 내부 방침을 통제하지 않습니다.</div>
</section>
<section class="card" id="pp6"><h2>제6조 — Verified Resume (참조 전용 원칙)</h2>
<p>HRI는 Verified Resume를 수신자(기업 등)에 대한 <strong>"온라인 표시(참조 전용)"</strong> 원칙에 따른 제한적 참조 자료로 위치시키며 민감한 식별 데이터에 대한 엄격한 제한을 적용합니다.</p>
<ul>
  <li>수신자의 접근은 정당한 메커니즘에 기반하여 제한되며 채용·평가의 합리적인 목적을 위한 것입니다.</li>
  <li>Verified Resume 서비스의 기간 및 비용 세부 사항은 별도 서비스 정책으로 관리됩니다.</li>
</ul>
</section>
<section class="card" id="pp7"><h2>제7조 — 처리의 법적 근거</h2>
<ul>
  <li>귀하의 동의(계정 생성, 인증 신청, 특정 기능 이용 등);</li>
  <li>서비스 제공을 위한 계약(이용약관) 이행;</li>
  <li>법적 의무 준수(시스템 보안 및 적절한 기록 포함);</li>
  <li>정당한 이익(사기 방지 및 플랫폼 보안 등).</li>
</ul>
</section>
<section class="card" id="pp8"><h2>제8조 — 저장 및 보존</h2>
<p>개인정보는 처리 목적, 법적 준수, 분쟁 해결, 시스템 보안에 필요한 기간 동안 보존합니다. 보존 기간은 데이터 유형과 서비스 필요성에 따라 다를 수 있습니다.</p>
</section>
<section class="card" id="pp9"><h2>제9조 — 보안 및 로그</h2>
<ol>
  <li>무단 접근, 분실 또는 유출로부터 개인정보를 보호하기 위한 합리적인 기술적·조직적 조치를 시행합니다.</li>
  <li>보안 및 법적 의무 이행을 위해 IP 주소 및 사용자 에이전트 등의 기술 기록(로그)을 보존할 수 있습니다.</li>
  <li>로그는 개인을 식별할 수 있는 범위에서 개인정보로 보호되며 권한 있는 당사자만 접근합니다.</li>
</ol>
</section>
<section class="card" id="pp10"><h2>제10조 — 정보주체의 권리</h2>
<p>PDP법에 따라 접근, 정정, 삭제, 동의 철회(허용되는 범위 내에서) 및 기타 메커니즘을 요청할 권리가 있습니다. 자세한 절차는 "HRI 정보주체 권리 정책"을 참조하십시오.</p>
</section>
<section class="card" id="pp11"><h2>제11조 — 국가 간 개인정보 이전</h2>
<p>개인정보를 인도네시아 외부로 이전하는 경우 계약적·기술적·조직적 보호 조치를 포함한 적용 가능한 데이터 보호 요구 사항을 준수하는 이전을 보장합니다.</p>
</section>
<section class="card" id="pp12"><h2>제12조 — 연락처, 불만 및 DPO</h2>
<div class="ok">
<p><strong>일반 연락처 및 개인정보 관련 불만</strong><br>
이메일: <a href="mailto:support@hri-check.com">support@hri-check.com</a><br>
Ruko Golden Boulevard Blok S-18, RT 003/RW 06,<br>
Kel. Lengkong Karya, Kec. Serpong Utara,<br>
Kota Tangerang Selatan, Banten 15310, Indonesia</p>
<hr>
<p><strong>사내 개인정보 보호책임자(DPO)</strong><br>
성명: <strong>Mohammad Iqbal Ibnu Anshori</strong></p>
</div>
</section>
<section class="card" id="pp13"><h2>제13조 — 정책 변경 및 언어</h2>
<ol>
  <li>본 방침은 수시로 업데이트될 수 있습니다. 중요한 변경이 있을 경우 사이트 또는 적절한 매체를 통해 사전에 통지합니다.</li>
  <li>인도네시아어 버전과 다른 언어 번역본 간에 해석 차이가 있을 경우 <strong>인도네시아어 버전</strong>이 우선합니다.</li>
</ol>
<div class="note">HRI 서비스를 이용함으로써 본 개인정보처리방침을 읽고 이해했음을 선언합니다.</div>
</section>
<section class="card"><h2>회사 정보</h2>
<p class="legal">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>사이트: https://hri-check.com</p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 英語
// ============================================================
export const en_individual = styles + `
<div id="hri-pp"><div class="wrap">
<header>
  <h1>🔒 HRI Privacy Policy (Individual Members)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Last updated: <strong>November 20, 2025</strong></div>
</header>
<nav class="card toc"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#pp1">Article 1 — Position & Scope</a></li>
  <li><a href="#pp2">Article 2 — Core Principles</a></li>
  <li><a href="#pp3">Article 3 — Types of Personal Data</a></li>
  <li><a href="#pp4">Article 4 — Processing Purposes</a></li>
  <li><a href="#pp5">Article 5 — Job Listings & Applications</a></li>
  <li><a href="#pp6">Article 6 — Verified Resume</a></li>
  <li><a href="#pp7">Article 7 — Legal Basis for Processing</a></li>
</ol></div><div><ol start="8">
  <li><a href="#pp8">Article 8 — Storage & Retention</a></li>
  <li><a href="#pp9">Article 9 — Security & Logs</a></li>
  <li><a href="#pp10">Article 10 — Data Subject Rights</a></li>
  <li><a href="#pp11">Article 11 — Cross-border Transfers</a></li>
  <li><a href="#pp12">Article 12 — Contact & DPO</a></li>
  <li><a href="#pp13">Article 13 — Changes & Language</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="pp1"><h2>Article 1 — Position & Scope</h2>
<p>This Privacy Policy explains how Personal Data is handled when using HRI services through https://hri-check.com and related subdomains, including verification features and Verified Resume.</p>
<p>This Policy applies to individuals (such as job seekers, employees, or other individuals) who create accounts, use verification features, Verified Resume, or other available services.</p>
</section>
<section class="card" id="pp2"><h2>Article 2 — Core Principles</h2>
<ol>
  <li>We process Personal Data lawfully, fairly, transparently, limited to specific purposes, and with data minimization.</li>
  <li>We apply accuracy, storage limitation, integrity & confidentiality, and accountability.</li>
  <li>We comply with Indonesia's PDP Law (Law No.27/2022), PP 71/2019, and related regulations.</li>
</ol>
</section>
<section class="card" id="pp3"><h2>Article 3 — Types of Personal Data Processed</h2>
<p>We may process the following categories of data (depending on the features you use):</p>
<ul>
  <li><strong>Identity & Account</strong>: name, email, phone number, account credentials.</li>
  <li><strong>Profile & Resume</strong>: education, work experience, certifications, skills, portfolio.</li>
  <li><strong>Verification Data</strong>: supporting documents you provide, verification results, validation records.</li>
  <li><strong>Application Data</strong>: jobs applied for, submission time, processing status on the platform.</li>
  <li><strong>Technical Data</strong>: IP address, user agent, and technical parameters for security & audit.</li>
</ul>
<div class="warn"><strong>Identification number protection (e.g., NIK):</strong> HRI applies strict restrictions and does not make identification numbers freely accessible content. The service principle is <strong>online display (reference only)</strong> for recipients.</div>
</section>
<section class="card" id="pp4"><h2>Article 4 — Processing Purposes</h2>
<ol>
  <li>Providing and managing accounts, profiles, and access to Service features.</li>
  <li>Conducting verification processes as requested and issuing <strong>Verified Resume</strong>.</li>
  <li>Providing job listing and <strong>job application</strong> features for individual members.</li>
  <li>Maintaining system security, fraud prevention, legal compliance, and internal audit.</li>
  <li>Service improvement, including cookie-based analytics (see Cookie Policy).</li>
</ol>
</section>
<section class="card" id="pp5"><h2>Article 5 — Job Listings & Applications (Individual Members)</h2>
<p>Individual members may view job listings published by HRI corporate members and submit applications through the Service. When you submit an application, we may share certain data with the target company <strong>solely for recruitment purposes</strong>.</p>
<div class="note"><strong>Data generally shared upon application:</strong>
<ul>
  <li>Profile/resume information relevant to recruitment;</li>
  <li>Contact information necessary for the company to reach you;</li>
  <li>If using Verified Resume for the application, access is provided as <strong>reference only</strong>.</li>
</ul></div>
<div class="warn"><strong>Limitations:</strong> Recruitment decisions are the responsibility of the company. HRI does not control the company's internal policies.</div>
</section>
<section class="card" id="pp6"><h2>Article 6 — Verified Resume (Reference Only Principle)</h2>
<p>HRI positions Verified Resume as a limited reference for recipients (e.g., companies) based on the <strong>"online display (reference only)"</strong> principle, with strict restrictions on sensitive identification data.</p>
<ul>
  <li>Recipient access is limited, based on legitimate mechanisms, and for reasonable recruitment/assessment purposes.</li>
  <li>Details of Verified Resume service periods and fees are managed as a separate service policy.</li>
</ul>
</section>
<section class="card" id="pp7"><h2>Article 7 — Legal Basis for Processing</h2>
<ul>
  <li>Your consent (e.g., account creation, verification submission, use of certain features);</li>
  <li>Performance of contract (Terms of Use) to provide the Service;</li>
  <li>Compliance with legal obligations (including system security and reasonable record-keeping);</li>
  <li>Legitimate interests (e.g., fraud prevention and platform security).</li>
</ul>
</section>
<section class="card" id="pp8"><h2>Article 8 — Storage & Retention</h2>
<p>We retain Personal Data for as long as necessary for processing purposes, legal compliance, dispute resolution, and system security. Retention may differ depending on data type and service needs.</p>
</section>
<section class="card" id="pp9"><h2>Article 9 — Security & Logs</h2>
<ol>
  <li>We implement reasonable technical and organizational measures to protect Personal Data from unauthorized access, loss, or leakage.</li>
  <li>We may retain technical records (logs) such as IP addresses and user agents for security and legal compliance.</li>
  <li>Logs are protected as Personal Data to the extent they can identify individuals and are only accessed by authorized parties.</li>
</ol>
</section>
<section class="card" id="pp10"><h2>Article 10 — Data Subject Rights</h2>
<p>You have rights under the PDP Law, including requesting access, correction, deletion, withdrawal of consent (within permitted limits), and other mechanisms as regulated by related policies. For detailed procedures, see "HRI Data Subject Rights Policy".</p>
</section>
<section class="card" id="pp11"><h2>Article 11 — Cross-border Transfer of Personal Data</h2>
<p>If Personal Data is transferred outside Indonesia, we ensure the transfer complies with applicable data protection requirements, including adequate contractual, technical, and organizational protections. Where required, notification/consent will be obtained.</p>
</section>
<section class="card" id="pp12"><h2>Article 12 — Contact, Complaints & DPO</h2>
<div class="ok">
<p><strong>General Contact & Personal Data Complaints</strong><br>
Email: <a href="mailto:support@hri-check.com">support@hri-check.com</a><br>
Ruko Golden Boulevard Blok S-18, RT 003/RW 06,<br>
Kel. Lengkong Karya, Kec. Serpong Utara,<br>
Kota Tangerang Selatan, Banten 15310, Indonesia</p>
<hr>
<p><strong>Internal Data Protection Officer (DPO)</strong><br>
Name: <strong>Mohammad Iqbal Ibnu Anshori</strong></p>
</div>
</section>
<section class="card" id="pp13"><h2>Article 13 — Policy Changes & Language</h2>
<ol>
  <li>We may update this policy from time to time. For material changes, we will provide prior notice through the site or appropriate media.</li>
  <li>If there are differences in interpretation between the Indonesian version and translations in other languages, the <strong>Indonesian version</strong> shall prevail.</li>
</ol>
<div class="note">By using HRI Services, you confirm that you have read and understood this Privacy Policy.</div>
</section>
<section class="card"><h2>Company Information</h2>
<p class="legal">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>Website: https://hri-check.com</p>
</section>
</main></div></div>`

// ============================================================
// 企業向け — インドネシア語
// ============================================================
export const id_company = styles + `
<div id="hri-pp"><div class="wrap">
<header>
  <h1>🔐 Kebijakan Privasi — Perusahaan</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Merek: HRI)<br>
  Berlaku sejak: <strong>13 Desember 2025</strong></div>
</header>
<nav class="card toc"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#cp1">Pasal 1 — Ruang Lingkup</a></li>
  <li><a href="#cp2">Pasal 2 — Peran Para Pihak</a></li>
  <li><a href="#cp3">Pasal 3 — Jenis Data yang Diproses</a></li>
  <li><a href="#cp4">Pasal 4 — Tujuan Pemrosesan</a></li>
  <li><a href="#cp5">Pasal 5 — Pembatasan & Larangan</a></li>
</ol></div><div><ol start="6">
  <li><a href="#cp6">Pasal 6 — Keamanan & Retensi</a></li>
  <li><a href="#cp7">Pasal 7 — Perusahaan Asing & Transfer Lintas Negara</a></li>
  <li><a href="#cp8">Pasal 8 — Hak Subjek Data</a></li>
  <li><a href="#cp9">Pasal 9 — Perubahan Kebijakan</a></li>
  <li><a href="#cp10">Pasal 10 — Hukum yang Berlaku</a></li>
</ol></div></div>
<p class="legal">Dokumen terkait: <strong>Ketentuan Penggunaan HRI</strong>, <strong>Kebijakan Cookie</strong>, dan kebijakan HRI lainnya.</p>
</nav>
<main>
<section class="card" id="cp1"><h2>Pasal 1 — Ruang Lingkup</h2>
<p>Kebijakan Privasi ini mengatur pemrosesan data pribadi sehubungan dengan penggunaan layanan HRI oleh <strong>Perusahaan</strong>, termasuk data akun perusahaan, data perwakilan perusahaan, serta data pelamar yang diakses melalui platform HRI.</p>
</section>
<section class="card" id="cp2"><h2>Pasal 2 — Peran Para Pihak</h2>
<ul>
  <li><strong>Perusahaan</strong> adalah <strong>Pengendali Data Pribadi</strong> atas data pelamar.</li>
  <li><strong>HRI</strong> bertindak sebagai:
    <ul>
      <li>Pengendali Data Pribadi independen untuk data akun HRI; dan/atau</li>
      <li>Pengelola Data Pribadi terbatas (processor) atas instruksi Perusahaan.</li>
    </ul>
  </li>
  <li>Tidak ada pengalihan tanggung jawab hukum dari Perusahaan kepada HRI.</li>
</ul>
<div class="note">Pembagian peran ini mengikuti UU No.27 Tahun 2022 tentang Perlindungan Data Pribadi (PDP).</div>
</section>
<section class="card" id="cp3"><h2>Pasal 3 — Jenis Data yang Diproses</h2>
<ul>
  <li>Data akun Perusahaan dan perwakilan yang berwenang;</li>
  <li>Data pelamar (identitas, riwayat kerja, pendidikan, sertifikasi) sebagaimana ditampilkan di HRI;</li>
  <li>Data teknis: log akses, alamat IP, user-agent, waktu dan aktivitas.</li>
</ul>
<div class="note">HRI tidak menyediakan fitur unduhan atau ekspor data (termasuk PDF).</div>
</section>
<section class="card" id="cp4"><h2>Pasal 4 — Tujuan Pemrosesan</h2>
<ol>
  <li>Penyediaan dan pengelolaan layanan HRI;</li>
  <li>Fasilitasi proses rekrutmen oleh Perusahaan;</li>
  <li>Keamanan sistem, audit, dan pencegahan penyalahgunaan;</li>
  <li>Pemenuhan kewajiban hukum dan permintaan otoritas yang sah.</li>
</ol>
<div class="warn">Data pelamar dilarang digunakan untuk tujuan di luar rekrutmen.</div>
</section>
<section class="card" id="cp5"><h2>Pasal 5 — Pembatasan & Larangan</h2>
<ul>
  <li><strong>Dilarang</strong> mengunduh, mencetak, menyimpan, atau mengekspor data pelamar di luar sistem HRI.</li>
  <li><strong>Dilarang</strong> membagikan data kepada pihak ketiga atau afiliasi tanpa dasar hukum dan persetujuan HRI.</li>
  <li>Penggunaan lintas negara menjadi tanggung jawab penuh Perusahaan.</li>
</ul>
</section>
<section class="card" id="cp6"><h2>Pasal 6 — Keamanan & Retensi</h2>
<p>HRI menerapkan langkah teknis dan organisatoris yang wajar untuk melindungi data. Retensi data dilakukan sesuai tujuan pemrosesan, kebijakan internal, dan hukum yang berlaku.</p>
</section>
<section class="card" id="cp7"><h2>Pasal 7 — Perusahaan Asing & Transfer Lintas Negara</h2>
<p>Perusahaan asing dan/atau akses lintas negara menyatakan bahwa setiap transfer data dilakukan sesuai hukum yang berlaku dan menjadi tanggung jawab Perusahaan sepenuhnya.</p>
</section>
<section class="card" id="cp8"><h2>Pasal 8 — Hak Subjek Data</h2>
<p>Permintaan hak subjek data dari pelamar merupakan tanggung jawab Perusahaan sebagai Pengendali Data. HRI dapat memberikan dukungan terbatas sesuai hukum.</p>
</section>
<section class="card" id="cp9"><h2>Pasal 9 — Perubahan Kebijakan</h2>
<p>Kebijakan ini dapat diperbarui dari waktu ke waktu. Versi terbaru yang dipublikasikan pada situs resmi HRI berlaku mengikat.</p>
</section>
<section class="card" id="cp10"><h2>Pasal 10 — Hukum yang Berlaku</h2>
<p>Kebijakan Privasi ini diatur dan ditafsirkan berdasarkan <strong>hukum Republik Indonesia</strong>.</p>
<div class="ok">Dengan menggunakan layanan HRI, Perusahaan menyetujui Kebijakan Privasi ini.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 日本語
// ============================================================
export const ja_company = styles + `
<div id="hri-pp"><div class="wrap">
<header>
  <h1>🔐 HRI プライバシーポリシー（企業会員）</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  施行日：<strong>2025年12月13日</strong></div>
</header>
<nav class="card toc"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#cp1">第1条 — 適用範囲</a></li>
  <li><a href="#cp2">第2条 — 各当事者の役割</a></li>
  <li><a href="#cp3">第3条 — 処理するデータの種類</a></li>
  <li><a href="#cp4">第4条 — 処理目的</a></li>
  <li><a href="#cp5">第5条 — 制限・禁止事項</a></li>
</ol></div><div><ol start="6">
  <li><a href="#cp6">第6条 — セキュリティ・保持期間</a></li>
  <li><a href="#cp7">第7条 — 外国企業・国際移転</a></li>
  <li><a href="#cp8">第8条 — データ主体の権利</a></li>
  <li><a href="#cp9">第9条 — ポリシーの変更</a></li>
  <li><a href="#cp10">第10条 — 準拠法</a></li>
</ol></div></div>
<p class="legal">関連文書：<strong>HRI利用規約</strong>、<strong>Cookieポリシー</strong>、その他HRIポリシー</p>
</nav>
<main>
<section class="card" id="cp1"><h2>第1条 — 適用範囲</h2>
<p>本プライバシーポリシーは、企業アカウントデータ・企業代表者データ・HRIプラットフォームを通じてアクセスする応募者データを含む、<strong>企業</strong>によるHRIサービス利用に関連する個人データの処理を規定します。</p>
</section>
<section class="card" id="cp2"><h2>第2条 — 各当事者の役割</h2>
<ul>
  <li><strong>企業</strong>は応募者データに対する<strong>個人データ管理者</strong>です。</li>
  <li><strong>HRI</strong>は以下として機能します：
    <ul>
      <li>HRIアカウントデータに対する独立した個人データ管理者；および/または</li>
      <li>企業の指示に基づく限定的な個人データ処理者（プロセッサー）。</li>
    </ul>
  </li>
  <li>企業からHRIへの法的責任の移転はありません。</li>
</ul>
<div class="note">この役割分担はインドネシアPDP法（法律第27号/2022年）に従います。</div>
</section>
<section class="card" id="cp3"><h2>第3条 — 処理するデータの種類</h2>
<ul>
  <li>企業アカウントおよび権限ある代表者のデータ；</li>
  <li>HRIで表示される応募者データ（身元・職歴・学歴・資格）；</li>
  <li>技術データ：アクセスログ・IPアドレス・ユーザーエージェント・日時・活動。</li>
</ul>
<div class="note">HRIはデータのダウンロードまたはエクスポート機能（PDFを含む）を提供していません。</div>
</section>
<section class="card" id="cp4"><h2>第4条 — 処理目的</h2>
<ol>
  <li>HRIサービスの提供・管理；</li>
  <li>企業による採用プロセスの促進；</li>
  <li>システムセキュリティ・監査・不正利用防止；</li>
  <li>法的義務の履行および正当な当局からの要求への対応。</li>
</ol>
<div class="warn">応募者データを採用以外の目的に使用することは禁止されています。</div>
</section>
<section class="card" id="cp5"><h2>第5条 — 制限・禁止事項</h2>
<ul>
  <li>HRIシステム外での応募者データのダウンロード・印刷・保存・エクスポートは<strong>禁止</strong>です。</li>
  <li>法的根拠およびHRIの同意なく第三者またはグループ会社にデータを共有することは<strong>禁止</strong>です。</li>
  <li>国際的な利用は企業が全責任を負います。</li>
</ul>
</section>
<section class="card" id="cp6"><h2>第6条 — セキュリティ・保持期間</h2>
<p>HRIはデータを保護するための合理的な技術的・組織的措置を講じます。データの保持は処理目的・内部ポリシー・適用法に従って行われます。</p>
</section>
<section class="card" id="cp7"><h2>第7条 — 外国企業・国際データ移転</h2>
<p>外国企業および/または国際的なアクセスは、すべてのデータ移転が適用法に従って行われ、完全に企業の責任であることを宣言します。</p>
</section>
<section class="card" id="cp8"><h2>第8条 — データ主体の権利</h2>
<p>応募者からのデータ主体の権利の要求は、データ管理者としての企業の責任です。HRIは法律に従って限定的なサポートを提供する場合があります。</p>
</section>
<section class="card" id="cp9"><h2>第9条 — ポリシーの変更</h2>
<p>本ポリシーは随時更新される場合があります。HRI公式サイトに掲載された最新版が法的拘束力を持ちます。</p>
</section>
<section class="card" id="cp10"><h2>第10条 — 準拠法</h2>
<p>本プライバシーポリシーは<strong>インドネシア共和国の法律</strong>に基づき解釈・適用されます。</p>
<div class="ok">HRIサービスを利用することにより、企業は本プライバシーポリシーに同意したものとみなします。</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 韓国語
// ============================================================
export const ko_company = styles + `
<div id="hri-pp"><div class="wrap">
<header>
  <h1>🔐 HRI 개인정보처리방침 (기업 회원)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  시행일: <strong>2025년 12월 13일</strong></div>
</header>
<nav class="card toc"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#cp1">제1조 — 적용 범위</a></li>
  <li><a href="#cp2">제2조 — 각 당사자의 역할</a></li>
  <li><a href="#cp3">제3조 — 처리하는 데이터 유형</a></li>
  <li><a href="#cp4">제4조 — 처리 목적</a></li>
  <li><a href="#cp5">제5조 — 제한 및 금지 사항</a></li>
</ol></div><div><ol start="6">
  <li><a href="#cp6">제6조 — 보안 및 보존</a></li>
  <li><a href="#cp7">제7조 — 외국 기업 및 국제 이전</a></li>
  <li><a href="#cp8">제8조 — 정보주체의 권리</a></li>
  <li><a href="#cp9">제9조 — 정책 변경</a></li>
  <li><a href="#cp10">제10조 — 준거법</a></li>
</ol></div></div>
<p class="legal">관련 문서: <strong>HRI 이용약관</strong>, <strong>Cookie 정책</strong> 및 기타 HRI 정책</p>
</nav>
<main>
<section class="card" id="cp1"><h2>제1조 — 적용 범위</h2>
<p>본 개인정보처리방침은 기업 계정 데이터, 기업 대표자 데이터, HRI 플랫폼을 통해 접근하는 지원자 데이터를 포함한 <strong>기업</strong>의 HRI 서비스 이용과 관련한 개인정보 처리를 규정합니다.</p>
</section>
<section class="card" id="cp2"><h2>제2조 — 각 당사자의 역할</h2>
<ul>
  <li><strong>기업</strong>은 지원자 데이터에 대한 <strong>개인정보 처리자(Controller)</strong>입니다.</li>
  <li><strong>HRI</strong>는 다음으로 기능합니다:
    <ul>
      <li>HRI 계정 데이터에 대한 독립적인 개인정보 처리자; 및/또는</li>
      <li>기업의 지시에 따른 제한적 개인정보 수탁자(Processor).</li>
    </ul>
  </li>
  <li>기업에서 HRI로의 법적 책임 이전은 없습니다.</li>
</ul>
<div class="note">이 역할 분담은 인도네시아 PDP법(법률 제27호/2022년)을 따릅니다.</div>
</section>
<section class="card" id="cp3"><h2>제3조 — 처리하는 데이터 유형</h2>
<ul>
  <li>기업 계정 및 권한 있는 대표자 데이터;</li>
  <li>HRI에 표시된 지원자 데이터(신원, 경력, 학력, 자격증);</li>
  <li>기술 데이터: 접근 로그, IP 주소, 사용자 에이전트, 시간 및 활동.</li>
</ul>
<div class="note">HRI는 데이터 다운로드 또는 내보내기 기능(PDF 포함)을 제공하지 않습니다.</div>
</section>
<section class="card" id="cp4"><h2>제4조 — 처리 목적</h2>
<ol>
  <li>HRI 서비스의 제공 및 관리;</li>
  <li>기업의 채용 프로세스 지원;</li>
  <li>시스템 보안, 감사 및 부정 이용 방지;</li>
  <li>법적 의무 이행 및 정당한 당국의 요청 대응.</li>
</ol>
<div class="warn">지원자 데이터를 채용 이외의 목적으로 사용하는 것은 금지됩니다.</div>
</section>
<section class="card" id="cp5"><h2>제5조 — 제한 및 금지 사항</h2>
<ul>
  <li>HRI 시스템 외부에서 지원자 데이터를 다운로드, 인쇄, 저장 또는 내보내는 것은 <strong>금지</strong>됩니다.</li>
  <li>법적 근거 및 HRI의 동의 없이 제3자 또는 계열사에 데이터를 공유하는 것은 <strong>금지</strong>됩니다.</li>
  <li>국제적 이용은 기업이 전적으로 책임집니다.</li>
</ul>
</section>
<section class="card" id="cp6"><h2>제6조 — 보안 및 보존</h2>
<p>HRI는 데이터를 보호하기 위한 합리적인 기술적·조직적 조치를 시행합니다. 데이터 보존은 처리 목적, 내부 정책 및 적용 법률에 따라 이루어집니다.</p>
</section>
<section class="card" id="cp7"><h2>제7조 — 외국 기업 및 국제 데이터 이전</h2>
<p>외국 기업 및/또는 국제 접근은 모든 데이터 이전이 적용 법률에 따라 이루어지며 전적으로 기업의 책임임을 선언합니다.</p>
</section>
<section class="card" id="cp8"><h2>제8조 — 정보주체의 권리</h2>
<p>지원자로부터의 정보주체 권리 요청은 개인정보 처리자인 기업의 책임입니다. HRI는 법률에 따라 제한적인 지원을 제공할 수 있습니다.</p>
</section>
<section class="card" id="cp9"><h2>제9조 — 정책 변경</h2>
<p>본 방침은 수시로 업데이트될 수 있습니다. HRI 공식 사이트에 게시된 최신 버전이 법적 구속력을 가집니다.</p>
</section>
<section class="card" id="cp10"><h2>제10조 — 준거법</h2>
<p>본 개인정보처리방침은 <strong>인도네시아 공화국 법률</strong>에 따라 해석·적용됩니다.</p>
<div class="ok">HRI 서비스를 이용함으로써 기업은 본 개인정보처리방침에 동의한 것으로 간주합니다.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 英語
// ============================================================
export const en_company = styles + `
<div id="hri-pp"><div class="wrap">
<header>
  <h1>🔐 HRI Privacy Policy (Corporate Members)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Effective date: <strong>December 13, 2025</strong></div>
</header>
<nav class="card toc"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#cp1">Article 1 — Scope</a></li>
  <li><a href="#cp2">Article 2 — Role of Each Party</a></li>
  <li><a href="#cp3">Article 3 — Types of Data Processed</a></li>
  <li><a href="#cp4">Article 4 — Processing Purposes</a></li>
  <li><a href="#cp5">Article 5 — Restrictions & Prohibitions</a></li>
</ol></div><div><ol start="6">
  <li><a href="#cp6">Article 6 — Security & Retention</a></li>
  <li><a href="#cp7">Article 7 — Foreign Companies & Cross-border Transfers</a></li>
  <li><a href="#cp8">Article 8 — Data Subject Rights</a></li>
  <li><a href="#cp9">Article 9 — Policy Changes</a></li>
  <li><a href="#cp10">Article 10 — Governing Law</a></li>
</ol></div></div>
<p class="legal">Related documents: <strong>HRI Terms of Use</strong>, <strong>Cookie Policy</strong>, and other HRI policies.</p>
</nav>
<main>
<section class="card" id="cp1"><h2>Article 1 — Scope</h2>
<p>This Privacy Policy governs the processing of personal data in connection with the use of HRI services by <strong>Companies</strong>, including company account data, company representative data, and applicant data accessed through the HRI platform.</p>
</section>
<section class="card" id="cp2"><h2>Article 2 — Role of Each Party</h2>
<ul>
  <li>The <strong>Company</strong> is the <strong>Personal Data Controller</strong> for applicant data.</li>
  <li><strong>HRI</strong> acts as:
    <ul>
      <li>An independent Personal Data Controller for HRI account data; and/or</li>
      <li>A limited Personal Data Processor under the Company's instructions.</li>
    </ul>
  </li>
  <li>There is no transfer of legal responsibility from the Company to HRI.</li>
</ul>
<div class="note">This role allocation follows Indonesia's PDP Law (Law No.27/2022).</div>
</section>
<section class="card" id="cp3"><h2>Article 3 — Types of Data Processed</h2>
<ul>
  <li>Company account and authorized representative data;</li>
  <li>Applicant data (identity, work history, education, certifications) as displayed in HRI;</li>
  <li>Technical data: access logs, IP address, user-agent, time and activity.</li>
</ul>
<div class="note">HRI does not provide data download or export features (including PDF).</div>
</section>
<section class="card" id="cp4"><h2>Article 4 — Processing Purposes</h2>
<ol>
  <li>Provision and management of HRI services;</li>
  <li>Facilitation of recruitment processes by the Company;</li>
  <li>System security, audit, and misuse prevention;</li>
  <li>Fulfillment of legal obligations and requests from legitimate authorities.</li>
</ol>
<div class="warn">Applicant data is prohibited from being used for purposes other than recruitment.</div>
</section>
<section class="card" id="cp5"><h2>Article 5 — Restrictions & Prohibitions</h2>
<ul>
  <li>Downloading, printing, storing, or exporting applicant data outside the HRI system is <strong>prohibited</strong>.</li>
  <li>Sharing data with third parties or affiliates without legal basis and HRI's consent is <strong>prohibited</strong>.</li>
  <li>Cross-border use is the full responsibility of the Company.</li>
</ul>
</section>
<section class="card" id="cp6"><h2>Article 6 — Security & Retention</h2>
<p>HRI implements reasonable technical and organizational measures to protect data. Data retention is carried out in accordance with processing purposes, internal policies, and applicable law.</p>
</section>
<section class="card" id="cp7"><h2>Article 7 — Foreign Companies & Cross-border Transfers</h2>
<p>Foreign companies and/or cross-border access declare that all data transfers are carried out in accordance with applicable law and are entirely the responsibility of the Company.</p>
</section>
<section class="card" id="cp8"><h2>Article 8 — Data Subject Rights</h2>
<p>Requests for data subject rights from applicants are the responsibility of the Company as Data Controller. HRI may provide limited support as permitted by law.</p>
</section>
<section class="card" id="cp9"><h2>Article 9 — Policy Changes</h2>
<p>This policy may be updated from time to time. The latest version published on the official HRI website shall be legally binding.</p>
</section>
<section class="card" id="cp10"><h2>Article 10 — Governing Law</h2>
<p>This Privacy Policy is governed and interpreted in accordance with the <strong>laws of the Republic of Indonesia</strong>.</p>
<div class="ok">By using HRI services, the Company agrees to this Privacy Policy.</div>
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