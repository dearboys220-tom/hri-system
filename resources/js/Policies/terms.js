// ============================================================
// 共通スタイル
// ============================================================
const styles = `<style>
  #hri-tos{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:#0f172a}
  #hri-tos .wrap{max-width:880px;margin:0 auto;padding:24px 16px}
  #hri-tos header{background:#0A1A3A;color:#fff;border-radius:16px;padding:20px 18px;margin-bottom:16px}
  #hri-tos h1{margin:0 0 6px;font-size:1.35rem;line-height:1.25;color:#fff}
  #hri-tos .meta{opacity:.9;font-size:.9rem;color:#fff}
  #hri-tos .card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px;margin:16px 0}
  #hri-tos h2{font-size:1.1rem;margin:0 0 10px;color:#0A1A3A}
  #hri-tos h3{font-size:1rem;margin:14px 0 8px;color:#1e3a8a}
  #hri-tos p{margin:10px 0;line-height:1.8}
  #hri-tos ul,#hri-tos ol{margin:10px 0 10px 20px;line-height:1.8}
  #hri-tos li{margin:6px 0}
  #hri-tos .note{background:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:12px;margin:12px 0}
  #hri-tos .warn{background:#fff7ed;border:1px solid #fdba74;border-radius:12px;padding:12px;margin:12px 0}
  #hri-tos .ok{background:#e8f5e8;border:1px solid #66bb6a;border-radius:12px;padding:12px;margin:12px 0}
  #hri-tos .grid{display:grid;gap:10px}
  #hri-tos .grid.two{grid-template-columns:1fr}
  #hri-tos .legal{font-size:.9rem;opacity:.85}
  @media(min-width:768px){
    #hri-tos .wrap{padding:28px 20px}
    #hri-tos h1{font-size:1.5rem}
    #hri-tos .grid.two{grid-template-columns:1fr 1fr}
  }
</style>`

// ============================================================
// 個人向け — インドネシア語
// ============================================================
export const id_individual = styles + `
<div id="hri-tos"><div class="wrap">
<header>
  <h1>Ketentuan Penggunaan HRI — Anggota Individu</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Merek: HRI)<br>
  Terakhir diperbarui: <strong>13 Desember 2025</strong></div>
</header>
<nav class="card"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#p1">Pasal 1 — Ruang Lingkup & Persetujuan</a></li>
  <li><a href="#p2">Pasal 2 — Definisi</a></li>
  <li><a href="#p3">Pasal 3 — Pendaftaran & Akun</a></li>
  <li><a href="#p4">Pasal 4 — Layanan, Lowongan, dan Lamaran Kerja</a></li>
</ol></div><div><ol start="5">
  <li><a href="#p5">Pasal 5 — Verified Resume</a></li>
  <li><a href="#p6">Pasal 6 — Larangan Penyalahgunaan</a></li>
  <li><a href="#p7">Pasal 7 — Penangguhan/Penghentian Akun</a></li>
  <li><a href="#p8">Pasal 8 — Batasan Tanggung Jawab</a></li>
  <li><a href="#p9">Pasal 9 — Hukum & Sengketa</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="p1"><h2>Pasal 1 — Ruang Lingkup & Persetujuan</h2>
<p>Ketentuan Penggunaan HRI mengatur penggunaan seluruh layanan HRI yang dioperasikan oleh PT. NIKI KINDAICHI THREE INDONESIA, melalui situs https://hri-check.com dan subdomain terkait, termasuk layanan verifikasi dan Verified Resume.</p>
<p>Dengan mengakses atau menggunakan Layanan, mendaftar sebagai anggota, membuat akun, atau menggunakan fitur apa pun, Anda menyatakan telah membaca, memahami, dan menyetujui untuk terikat pada Ketentuan ini.</p>
<div class="note"><strong>Catatan:</strong> Ketentuan ini harus dibaca bersama dengan <strong>Kebijakan Privasi</strong> dan <strong>Kebijakan Cookie</strong> HRI.</div>
</section>
<section class="card" id="p2"><h2>Pasal 2 — Definisi</h2>
<ul>
  <li><strong>Layanan</strong>: seluruh layanan yang disediakan HRI, termasuk verifikasi data, pengelolaan profil, lowongan kerja, dan Verified Resume.</li>
  <li><strong>Anggota</strong>: individu yang melakukan pendaftaran dan memiliki akun pada Layanan HRI.</li>
  <li><strong>Lowongan Kerja</strong>: informasi pekerjaan yang dipublikasikan oleh perusahaan anggota HRI melalui fitur lowongan di Layanan.</li>
  <li><strong>Lamaran</strong>: tindakan Anggota mengajukan aplikasi/permohonan kerja kepada perusahaan anggota HRI melalui Layanan.</li>
  <li><strong>Verified Resume</strong>: resume yang telah melalui proses verifikasi internal HRI dan diterbitkan dengan penanda khusus.</li>
  <li><strong>Penerima</strong>: pihak ketiga yang dapat melihat Verified Resume secara terbatas berdasarkan mekanisme sah.</li>
</ul>
</section>
<section class="card" id="p3"><h2>Pasal 3 — Pendaftaran & Akun</h2>
<ol>
  <li>Untuk menggunakan fungsi tertentu, Anda harus mendaftar dan membuat akun.</li>
  <li>Anda wajib memberikan informasi yang akurat, terkini, dan lengkap, serta memperbarui jika terjadi perubahan.</li>
  <li>Anda bertanggung jawab menjaga kerahasiaan kredensial akun dan semua aktivitas yang terjadi melalui akun Anda.</li>
  <li>HRI berhak menolak pendaftaran, menangguhkan, atau menghentikan akun apabila terjadi pelanggaran.</li>
</ol>
</section>
<section class="card" id="p4"><h2>Pasal 4 — Layanan, Lowongan, dan Lamaran Kerja</h2>
<h3>4.1 Akses Lowongan Kerja</h3>
<p>Anggota dapat melihat lowongan kerja yang dipublikasikan oleh perusahaan anggota HRI sesuai ketersediaan fitur di Layanan.</p>
<h3>4.2 Mengajukan Lamaran</h3>
<p>Anggota dapat mengajukan Lamaran melalui Layanan. Data tertentu pada profil Anda akan diteruskan kepada perusahaan tujuan <strong>sebatas untuk tujuan rekrutmen</strong> sesuai Kebijakan Privasi.</p>
<div class="warn"><strong>Penting:</strong> Keputusan rekrutmen adalah tanggung jawab perusahaan. HRI tidak menjamin hasil lamaran.</div>
<h3>4.3 Etika & Kepatuhan</h3>
<ul>
  <li>Dilarang mengajukan Lamaran dengan identitas palsu atau informasi yang sengaja menyesatkan.</li>
  <li>Dilarang mengganggu atau mengintimidasi pihak perusahaan.</li>
  <li>HRI dapat membatasi fitur Lamaran jika terdapat indikasi penyalahgunaan.</li>
</ul>
</section>
<section class="card" id="p5"><h2>Pasal 5 — Verified Resume</h2>
<p>Verified Resume disediakan untuk membantu Anggota membuktikan kebenaran data tertentu kepada Penerima secara terbatas.</p>
<div class="note"><strong>Catatan:</strong> Akses Penerima bersifat <strong>reference only</strong> (tampilan online terbatas).</div>
</section>
<section class="card" id="p6"><h2>Pasal 6 — Larangan Penyalahgunaan</h2>
<ul>
  <li>Memberikan data palsu atau memanipulasi proses verifikasi.</li>
  <li>Menyalin atau mencoba mengekstrak data pihak lain tanpa hak.</li>
  <li>Upaya mengganggu keamanan sistem.</li>
  <li>Mengunggah data identifikasi sensitif pihak lain tanpa hak.</li>
</ul>
</section>
<section class="card" id="p7"><h2>Pasal 7 — Penangguhan/Penghentian Akun</h2>
<p>Jika terdapat indikasi pelanggaran, HRI berhak memberikan peringatan, menangguhkan sementara akses, atau menghentikan akun secara permanen.</p>
</section>
<section class="card" id="p8"><h2>Pasal 8 — Batasan Tanggung Jawab</h2>
<ul>
  <li>HRI tidak menjamin bahwa Anggota akan memperoleh pekerjaan atau panggilan wawancara.</li>
  <li>HRI tidak bertanggung jawab atas keputusan rekrutmen perusahaan.</li>
  <li>HRI tidak bertanggung jawab atas kerugian tidak langsung atau kehilangan peluang.</li>
</ul>
</section>
<section class="card" id="p9"><h2>Pasal 9 — Hukum & Penyelesaian Sengketa</h2>
<p>Ketentuan ini diatur oleh hukum Republik Indonesia.</p>
<ol>
  <li>Penyelesaian internal melalui kontak resmi HRI;</li>
  <li>Mekanisme pengaduan sesuai kebijakan pengaduan HRI;</li>
  <li>Jika diperlukan, penyelesaian melalui jalur yang diizinkan hukum Indonesia.</li>
</ol>
<div class="note"><strong>Bahasa:</strong> Ketentuan ini disusun dalam Bahasa Indonesia sebagai versi resmi. Terjemahan hanya untuk kemudahan.</div>
<p class="legal">Kontak: <strong>support@hri-check.com</strong></p>
</section>
<section class="card"><h2>Informasi Perusahaan</h2>
<p class="legal">PT. NIKI KINDAICHI THREE INDONESIA (Merek: HRI)<br>Situs: https://hri-check.com</p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 日本語
// ============================================================
export const ja_individual = styles + `
<div id="hri-tos"><div class="wrap">
<header>
  <h1>HRI 利用規約 — 個人会員</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  最終更新日：<strong>2025年12月13日</strong></div>
</header>
<nav class="card"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#p1">第1条 — 適用範囲・同意</a></li>
  <li><a href="#p2">第2条 — 定義</a></li>
  <li><a href="#p3">第3条 — 会員登録・アカウント</a></li>
  <li><a href="#p4">第4条 — サービス・求人・応募</a></li>
</ol></div><div><ol start="5">
  <li><a href="#p5">第5条 — Verified Resume</a></li>
  <li><a href="#p6">第6条 — 禁止事項</a></li>
  <li><a href="#p7">第7条 — アカウント停止・終了</a></li>
  <li><a href="#p8">第8条 — 免責・責任制限</a></li>
  <li><a href="#p9">第9条 — 準拠法・紛争解決</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="p1"><h2>第1条 — 適用範囲・同意</h2>
<p>本利用規約は、PT. NIKI KINDAICHI THREE INDONESIAが運営するHRIの全サービス（https://hri-check.com および関連サブドメイン、認証サービス、Verified Resumeを含む）の利用を規定します。</p>
<p>サービスへのアクセス、会員登録、アカウント作成、または機能の利用をもって、本規約を読み、理解し、同意したものとみなします。</p>
<div class="note"><strong>注意：</strong>本規約は<strong>プライバシーポリシー</strong>および<strong>Cookieポリシー</strong>と併せてお読みください。</div>
</section>
<section class="card" id="p2"><h2>第2条 — 定義</h2>
<ul>
  <li><strong>サービス</strong>：データ認証、プロフィール管理、求人、Verified Resumeを含むHRIが提供する全サービス。</li>
  <li><strong>会員</strong>：HRIサービスに登録しアカウントを保有する個人。</li>
  <li><strong>求人情報</strong>：HRI企業会員がサービス上に掲載する求人情報。</li>
  <li><strong>応募</strong>：会員がサービスを通じてHRI企業会員に行う入社応募。</li>
  <li><strong>Verified Resume</strong>：HRIの内部認証プロセスを経て発行された履歴書。</li>
  <li><strong>受信者</strong>：正規の仕組みに基づいてVerified Resumeを限定的に閲覧できる第三者。</li>
</ul>
</section>
<section class="card" id="p3"><h2>第3条 — 会員登録・アカウント</h2>
<ol>
  <li>認証申請・Verified Resume・応募等の機能を利用するには、登録してアカウントを作成する必要があります。</li>
  <li>正確・最新・完全な情報を提供し、変更があった場合は速やかに更新する義務があります。</li>
  <li>アカウントの認証情報の秘密保持と、アカウントを通じて行われるすべての活動に責任を負います。</li>
  <li>HRIは、規約違反や不正利用の兆候がある場合、登録拒否・停止・アカウント終了を行う権利を有します。</li>
</ol>
</section>
<section class="card" id="p4"><h2>第4条 — サービス・求人・応募</h2>
<h3>4.1 求人情報へのアクセス</h3>
<p>会員は、HRI企業会員が掲載する求人情報をサービス上で閲覧できます。</p>
<h3>4.2 応募</h3>
<p>会員はサービスを通じてHRI企業会員に応募できます。プロフィール上の特定データが応募先企業に<strong>採用目的のみ</strong>でプライバシーポリシーに従い提供されます。</p>
<div class="warn"><strong>重要：</strong>採用の意思決定は企業の責任です。HRIは応募結果を保証しません。</div>
<h3>4.3 倫理・コンプライアンス</h3>
<ul>
  <li>虚偽の身元や意図的に誤解を招く情報での応募は禁止です。</li>
  <li>企業担当者への嫌がらせや不適切な行為は禁止です。</li>
  <li>不正利用の兆候がある場合、HRIは応募機能を制限できます。</li>
</ul>
</section>
<section class="card" id="p5"><h2>第5条 — Verified Resume</h2>
<p>Verified Resumeは、会員が特定データの正確性を受信者に限定的に証明するために提供されます。</p>
<div class="note"><strong>注意：</strong>受信者のアクセスは<strong>参照のみ</strong>（限定的なオンライン表示）です。</div>
</section>
<section class="card" id="p6"><h2>第6条 — 禁止事項</h2>
<ul>
  <li>虚偽データの提供や認証・応募プロセスの操作。</li>
  <li>権限なく他者のデータをコピー・配布・抽出しようとする行為。</li>
  <li>システムセキュリティを侵害する試み。</li>
  <li>権限なく他者の機密個人情報をアップロードする行為。</li>
</ul>
</section>
<section class="card" id="p7"><h2>第7条 — アカウント停止・終了</h2>
<p>規約違反または不正利用の兆候がある場合、HRIは警告・一時停止・重大な違反の場合は永久停止を行う権利を有します。</p>
</section>
<section class="card" id="p8"><h2>第8条 — 免責・責任制限</h2>
<ul>
  <li>HRIは会員が就職・面接・内定を得ることを保証しません。</li>
  <li>HRIは企業の採用決定について責任を負いません。</li>
  <li>HRIは間接損害・機会の喪失について責任を負いません。</li>
</ul>
</section>
<section class="card" id="p9"><h2>第9条 — 準拠法・紛争解決</h2>
<p>本規約はインドネシア共和国の法律に準拠します。</p>
<ol>
  <li>HRI公式窓口を通じた内部解決；</li>
  <li>HRIの苦情処理ポリシーに基づく手続き；</li>
  <li>必要に応じてインドネシア法が認める手続きによる解決。</li>
</ol>
<div class="note"><strong>言語：</strong>本規約はインドネシア語を正式版とします。他言語の翻訳は参考のみであり、法的拘束力を持ちません。</div>
<p class="legal">サポート連絡先：<strong>support@hri-check.com</strong></p>
</section>
<section class="card"><h2>会社情報</h2>
<p class="legal">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>サイト：https://hri-check.com</p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 韓国語
// ============================================================
export const ko_individual = styles + `
<div id="hri-tos"><div class="wrap">
<header>
  <h1>HRI 이용약관 — 개인 회원</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  최종 업데이트: <strong>2025년 12월 13일</strong></div>
</header>
<nav class="card"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#p1">제1조 — 적용 범위 및 동의</a></li>
  <li><a href="#p2">제2조 — 정의</a></li>
  <li><a href="#p3">제3조 — 회원 가입 및 계정</a></li>
  <li><a href="#p4">제4조 — 서비스, 채용공고 및 지원</a></li>
</ol></div><div><ol start="5">
  <li><a href="#p5">제5조 — Verified Resume</a></li>
  <li><a href="#p6">제6조 — 금지 사항</a></li>
  <li><a href="#p7">제7조 — 계정 정지/해지</a></li>
  <li><a href="#p8">제8조 — 면책 및 책임 제한</a></li>
  <li><a href="#p9">제9조 — 준거법 및 분쟁 해결</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="p1"><h2>제1조 — 적용 범위 및 동의</h2>
<p>본 이용약관은 PT. NIKI KINDAICHI THREE INDONESIA가 운영하는 HRI의 모든 서비스의 이용을 규정합니다.</p>
<p>서비스 접속, 회원 가입, 계정 생성 또는 기능 이용 시 본 약관을 읽고 이해하며 동의한 것으로 간주합니다.</p>
<div class="note"><strong>참고:</strong> 본 약관은 <strong>개인정보처리방침</strong> 및 <strong>Cookie 정책</strong>과 함께 읽어주세요.</div>
</section>
<section class="card" id="p2"><h2>제2조 — 정의</h2>
<ul>
  <li><strong>서비스</strong>: 데이터 인증, 프로필 관리, 채용공고, Verified Resume 등 HRI가 제공하는 모든 서비스.</li>
  <li><strong>회원</strong>: HRI 서비스에 가입하여 계정을 보유한 개인.</li>
  <li><strong>채용공고</strong>: HRI 기업 회원이 서비스를 통해 게시한 채용 정보.</li>
  <li><strong>지원</strong>: 회원이 서비스를 통해 HRI 기업 회원에게 하는 입사 지원.</li>
  <li><strong>Verified Resume</strong>: HRI의 내부 인증 절차를 거쳐 발급된 이력서.</li>
  <li><strong>수신자</strong>: 정당한 절차에 따라 Verified Resume를 제한적으로 열람할 수 있는 제3자.</li>
</ul>
</section>
<section class="card" id="p3"><h2>제3조 — 회원 가입 및 계정</h2>
<ol>
  <li>인증 신청, Verified Resume, 지원 등의 기능을 이용하려면 가입 및 계정 생성이 필요합니다.</li>
  <li>정확하고 최신의 완전한 정보를 제공하고 변경 시 즉시 업데이트할 의무가 있습니다.</li>
  <li>계정 인증 정보의 기밀 유지와 계정을 통한 모든 활동에 책임을 집니다.</li>
  <li>HRI는 약관 위반 또는 부정 이용 징후가 있을 경우 가입 거절, 정지, 계정 해지를 할 권리를 가집니다.</li>
</ol>
</section>
<section class="card" id="p4"><h2>제4조 — 서비스, 채용공고 및 지원</h2>
<h3>4.1 채용공고 접근</h3>
<p>회원은 HRI 기업 회원이 게시한 채용공고를 서비스에서 열람할 수 있습니다.</p>
<h3>4.2 지원</h3>
<p>회원은 서비스를 통해 HRI 기업 회원에 지원할 수 있습니다. 특정 데이터가 지원 기업에 <strong>채용 목적으로만</strong> 개인정보처리방침에 따라 제공됩니다.</p>
<div class="warn"><strong>중요:</strong> 채용 결정은 기업의 책임입니다. HRI는 지원 결과를 보장하지 않습니다.</div>
<h3>4.3 윤리 및 준수</h3>
<ul>
  <li>허위 신원이나 오해를 일으키는 정보로의 지원은 금지됩니다.</li>
  <li>기업 담당자를 방해하거나 괴롭히는 행위는 금지됩니다.</li>
  <li>부정 이용 징후가 있을 경우 HRI는 지원 기능을 제한할 수 있습니다.</li>
</ul>
</section>
<section class="card" id="p5"><h2>제5조 — Verified Resume</h2>
<p>Verified Resume는 회원이 특정 데이터의 정확성을 수신자에게 제한적으로 증명하기 위해 제공됩니다.</p>
<div class="note"><strong>참고:</strong> 수신자의 접근은 <strong>참조 전용</strong>입니다.</div>
</section>
<section class="card" id="p6"><h2>제6조 — 금지 사항</h2>
<ul>
  <li>허위 데이터 제공 또는 인증·지원 절차 조작.</li>
  <li>권한 없이 타인의 데이터를 복사·배포·추출하려는 행위.</li>
  <li>시스템 보안 침해 시도.</li>
  <li>권한 없이 타인의 민감한 개인정보를 업로드하는 행위.</li>
</ul>
</section>
<section class="card" id="p7"><h2>제7조 — 계정 정지/해지</h2>
<p>약관 위반 또는 부정 이용 징후가 있을 경우 HRI는 경고, 일시 정지, 심각한 위반의 경우 영구 해지를 할 권리를 가집니다.</p>
</section>
<section class="card" id="p8"><h2>제8조 — 면책 및 책임 제한</h2>
<ul>
  <li>HRI는 회원이 취업, 면접, 내정을 얻을 것을 보장하지 않습니다.</li>
  <li>HRI는 기업의 채용 결정에 대해 책임을 지지 않습니다.</li>
  <li>HRI는 간접 손해, 기회 상실에 대해 책임을 지지 않습니다.</li>
</ul>
</section>
<section class="card" id="p9"><h2>제9조 — 준거법 및 분쟁 해결</h2>
<p>본 약관은 인도네시아 공화국 법률에 준거합니다.</p>
<ol>
  <li>HRI 공식 채널을 통한 내부 해결;</li>
  <li>HRI 민원 처리 정책에 따른 절차;</li>
  <li>필요시 인도네시아 법이 허용하는 절차를 통한 해결.</li>
</ol>
<div class="note"><strong>언어:</strong> 본 약관은 인도네시아어를 공식 버전으로 합니다. 다른 언어 번역본은 참고용입니다.</div>
<p class="legal">지원 연락처: <strong>support@hri-check.com</strong></p>
</section>
<section class="card"><h2>회사 정보</h2>
<p class="legal">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>사이트: https://hri-check.com</p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 英語
// ============================================================
export const en_individual = styles + `
<div id="hri-tos"><div class="wrap">
<header>
  <h1>HRI Terms of Use — Individual Members</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Last updated: <strong>December 13, 2025</strong></div>
</header>
<nav class="card"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#p1">Article 1 — Scope & Agreement</a></li>
  <li><a href="#p2">Article 2 — Definitions</a></li>
  <li><a href="#p3">Article 3 — Registration & Account</a></li>
  <li><a href="#p4">Article 4 — Services, Jobs & Applications</a></li>
</ol></div><div><ol start="5">
  <li><a href="#p5">Article 5 — Verified Resume</a></li>
  <li><a href="#p6">Article 6 — Prohibited Conduct</a></li>
  <li><a href="#p7">Article 7 — Account Suspension/Termination</a></li>
  <li><a href="#p8">Article 8 — Limitation of Liability</a></li>
  <li><a href="#p9">Article 9 — Governing Law & Disputes</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="p1"><h2>Article 1 — Scope & Agreement</h2>
<p>These Terms of Use govern the use of all HRI services operated by PT. NIKI KINDAICHI THREE INDONESIA, through https://hri-check.com and related subdomains, including verification services and Verified Resume.</p>
<p>By accessing or using the Service, registering, creating an account, or using any feature, you confirm that you have read, understood, and agreed to be bound by these Terms.</p>
<div class="note"><strong>Note:</strong> These Terms must be read together with HRI's <strong>Privacy Policy</strong> and <strong>Cookie Policy</strong>.</div>
</section>
<section class="card" id="p2"><h2>Article 2 — Definitions</h2>
<ul>
  <li><strong>Service</strong>: All services provided by HRI, including data verification, profile management, job listings, and Verified Resume.</li>
  <li><strong>Member</strong>: An individual who has registered and holds an account on the HRI Service.</li>
  <li><strong>Job Listing</strong>: Employment information published by HRI corporate members through the Service.</li>
  <li><strong>Application</strong>: The act of a Member submitting a job application to an HRI corporate member through the Service.</li>
  <li><strong>Verified Resume</strong>: A resume issued with a special mark after undergoing HRI's internal verification process.</li>
  <li><strong>Recipient</strong>: A third party that may view the Verified Resume in a limited capacity through legitimate means.</li>
</ul>
</section>
<section class="card" id="p3"><h2>Article 3 — Registration & Account</h2>
<ol>
  <li>To use certain features, you must register and create an account.</li>
  <li>You must provide accurate, current, and complete information, and update it if changes occur.</li>
  <li>You are responsible for maintaining the confidentiality of your credentials and all activities conducted through your account.</li>
  <li>HRI reserves the right to refuse registration, suspend, or terminate accounts in case of violations or suspected misuse.</li>
</ol>
</section>
<section class="card" id="p4"><h2>Article 4 — Services, Jobs & Applications</h2>
<h3>4.1 Access to Job Listings</h3>
<p>Members may view job listings published by HRI corporate members on the Service.</p>
<h3>4.2 Submitting Applications</h3>
<p>Members may submit Applications through the Service. Certain profile data will be shared with the target company <strong>solely for recruitment purposes</strong> in accordance with the Privacy Policy.</p>
<div class="warn"><strong>Important:</strong> Recruitment decisions are the responsibility of the company. HRI does not guarantee application outcomes.</div>
<h3>4.3 Ethics & Compliance</h3>
<ul>
  <li>Submitting applications with false identities or misleading information is prohibited.</li>
  <li>Harassing or intimidating company representatives is prohibited.</li>
  <li>HRI may restrict application features if misuse is suspected.</li>
</ul>
</section>
<section class="card" id="p5"><h2>Article 5 — Verified Resume</h2>
<p>Verified Resume is provided to help Members prove the accuracy of certain data to Recipients in a limited capacity.</p>
<div class="note"><strong>Note:</strong> Recipient access is <strong>reference only</strong> (limited online viewing).</div>
</section>
<section class="card" id="p6"><h2>Article 6 — Prohibited Conduct</h2>
<ul>
  <li>Providing false data or manipulating the verification/application process.</li>
  <li>Copying, distributing, or attempting to extract third-party data without authorization.</li>
  <li>Attempting to compromise system security.</li>
  <li>Uploading another person's sensitive identification data without authorization.</li>
</ul>
</section>
<section class="card" id="p7"><h2>Article 7 — Account Suspension/Termination</h2>
<p>If there is evidence of a violation, HRI reserves the right to issue a warning, temporarily suspend access, or permanently terminate the account in serious cases.</p>
</section>
<section class="card" id="p8"><h2>Article 8 — Limitation of Liability</h2>
<ul>
  <li>HRI does not guarantee that Members will obtain employment, interviews, or job offers.</li>
  <li>HRI is not responsible for corporate recruitment decisions.</li>
  <li>HRI is not liable for indirect damages or lost opportunities.</li>
</ul>
</section>
<section class="card" id="p9"><h2>Article 9 — Governing Law & Dispute Resolution</h2>
<p>These Terms are governed by the laws of the Republic of Indonesia.</p>
<ol>
  <li>Internal resolution through official HRI contact;</li>
  <li>Complaint mechanism pursuant to HRI's complaint policy;</li>
  <li>If necessary, dispute resolution through channels permitted by Indonesian law.</li>
</ol>
<div class="note"><strong>Language:</strong> These Terms are drafted in Indonesian as the official version. Translations are for convenience only and are not legally binding.</div>
<p class="legal">Support: <strong>support@hri-check.com</strong></p>
</section>
<section class="card"><h2>Company Information</h2>
<p class="legal">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>Website: https://hri-check.com</p>
</section>
</main></div></div>`

// ============================================================
// 企業向け — インドネシア語
// ============================================================
export const id_company = styles + `
<div id="hri-tos"><div class="wrap">
<header>
  <h1>Ketentuan Penggunaan Layanan HRI — Perusahaan</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Merek: HRI)<br>
  Berlaku sejak: <strong>13 Desember 2025</strong></div>
</header>
<nav class="card"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#c1">Pasal 1 — Ruang Lingkup & Persetujuan</a></li>
  <li><a href="#c2">Pasal 2 — Kedudukan HRI</a></li>
  <li><a href="#c3">Pasal 3 — Tanggung Jawab Perusahaan</a></li>
  <li><a href="#c4">Pasal 4 — Pembatasan Penggunaan Data</a></li>
</ol></div><div><ol start="5">
  <li><a href="#c5">Pasal 5 — Perusahaan Asing & Penggunaan Lintas Negara</a></li>
  <li><a href="#c6">Pasal 6 — Penangguhan & Penghentian Layanan</a></li>
  <li><a href="#c7">Pasal 7 — Perubahan Ketentuan</a></li>
  <li><a href="#c8">Pasal 8 — Hukum yang Berlaku</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="c1"><h2>Pasal 1 — Ruang Lingkup & Persetujuan</h2>
<p>Ketentuan ini mengatur penggunaan seluruh layanan HRI oleh <strong>Perusahaan</strong>, namun tidak terbatas pada pembuatan akun, pemasangan lowongan kerja, akses data pelamar, dan penggunaan informasi <strong>Verified Resume</strong>.</p>
<p>Dengan mendaftarkan akun atau menggunakan layanan HRI, Perusahaan menyatakan telah membaca, memahami, dan <strong>menyetujui ketentuan ini secara mengikat</strong>.</p>
</section>
<section class="card" id="c2"><h2>Pasal 2 — Kedudukan HRI</h2>
<ul>
  <li>HRI adalah <strong>penyedia sistem dan platform digital</strong>.</li>
  <li>HRI <strong>bukan</strong> pemberi kerja, agen perekrutan, atau perantara ketenagakerjaan.</li>
  <li>HRI tidak menjadi pihak dalam hubungan kerja antara Perusahaan dan pelamar.</li>
</ul>
</section>
<section class="card" id="c3"><h2>Pasal 3 — Tanggung Jawab Perusahaan</h2>
<ul>
  <li>Seluruh isi lowongan kerja merupakan tanggung jawab Perusahaan.</li>
  <li>Keputusan seleksi dan penerimaan kerja sepenuhnya berada pada Perusahaan.</li>
  <li>Perusahaan bertanggung jawab atas kepatuhan hukum ketenagakerjaan dan perlindungan data.</li>
  <li>Penggunaan data pelamar wajib dibatasi hanya untuk tujuan rekrutmen.</li>
</ul>
</section>
<section class="card" id="c4"><h2>Pasal 4 — Pembatasan Penggunaan Data</h2>
<ul>
  <li>Data pelamar hanya dapat diakses melalui sistem HRI.</li>
  <li><strong>Dilarang</strong> mengunduh, mencetak, menyimpan, atau mengekspor data dalam bentuk apa pun (termasuk PDF).</li>
  <li><strong>Dilarang</strong> memberikan akses kepada pihak ketiga tanpa persetujuan tertulis HRI.</li>
</ul>
<div class="warn">Pelanggaran terhadap ketentuan ini dapat mengakibatkan penangguhan atau pengakhiran akun Perusahaan.</div>
</section>
<section class="card" id="c5"><h2>Pasal 5 — Perusahaan Asing & Penggunaan Lintas Negara</h2>
<p>Layanan HRI pada prinsipnya ditujukan bagi Perusahaan di Indonesia. Perusahaan asing yang menggunakan HRI menyatakan dan menyetujui bahwa:</p>
<ul>
  <li>Hukum Republik Indonesia berlaku;</li>
  <li>Kepatuhan hukum di yurisdiksi lain menjadi tanggung jawab Perusahaan;</li>
  <li>Bahasa Indonesia adalah bahasa resmi yang mengikat.</li>
</ul>
</section>
<section class="card" id="c6"><h2>Pasal 6 — Penangguhan & Penghentian Layanan</h2>
<p>HRI berhak membatasi, menangguhkan, atau menghentikan layanan apabila terdapat dugaan pelanggaran ketentuan ini atau kebijakan terkait.</p>
</section>
<section class="card" id="c7"><h2>Pasal 7 — Perubahan Ketentuan</h2>
<p>Ketentuan ini dapat diperbarui dari waktu ke waktu. Versi terbaru yang dipublikasikan pada situs resmi HRI berlaku mengikat.</p>
</section>
<section class="card" id="c8"><h2>Pasal 8 — Hukum yang Berlaku</h2>
<p>Ketentuan ini diatur dan ditafsirkan berdasarkan <strong>hukum Republik Indonesia</strong>.</p>
<div class="ok">Dengan melanjutkan penggunaan layanan HRI, Perusahaan menyetujui Ketentuan Penggunaan ini.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 日本語
// ============================================================
export const ja_company = styles + `
<div id="hri-tos"><div class="wrap">
<header>
  <h1>HRI サービス利用規約 — 企業会員</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  施行日：<strong>2025年12月13日</strong></div>
</header>
<nav class="card"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#c1">第1条 — 適用範囲・同意</a></li>
  <li><a href="#c2">第2条 — HRIの位置づけ</a></li>
  <li><a href="#c3">第3条 — 企業の責任</a></li>
  <li><a href="#c4">第4条 — データ利用制限</a></li>
</ol></div><div><ol start="5">
  <li><a href="#c5">第5条 — 外国企業・国際利用</a></li>
  <li><a href="#c6">第6条 — サービスの停止・終了</a></li>
  <li><a href="#c7">第7条 — 規約の変更</a></li>
  <li><a href="#c8">第8条 — 準拠法</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="c1"><h2>第1条 — 適用範囲・同意</h2>
<p>本規約は、アカウント作成、求人掲載、応募者データへのアクセス、<strong>Verified Resume</strong>情報の利用を含む、<strong>企業</strong>によるHRIの全サービス利用を規定します。</p>
<p>アカウントを登録またはHRIサービスを利用することにより、企業は本規約を読み、理解し、<strong>法的拘束力をもって同意した</strong>ものとみなします。</p>
</section>
<section class="card" id="c2"><h2>第2条 — HRIの位置づけ</h2>
<ul>
  <li>HRIは<strong>デジタルシステム・プラットフォームの提供者</strong>です。</li>
  <li>HRIは雇用主、採用エージェント、または職業紹介業者では<strong>ありません</strong>。</li>
  <li>HRIは企業と応募者間の雇用関係の当事者ではありません。</li>
</ul>
</section>
<section class="card" id="c3"><h2>第3条 — 企業の責任</h2>
<ul>
  <li>求人情報の内容はすべて企業の責任です。</li>
  <li>選考・採用の決定は企業が全責任を負います。</li>
  <li>企業は労働法・データ保護法の遵守に責任を負います。</li>
  <li>応募者データの利用は採用目的のみに限定する義務があります。</li>
</ul>
</section>
<section class="card" id="c4"><h2>第4条 — データ利用制限</h2>
<ul>
  <li>応募者データはHRIシステムを通じてのみアクセスできます。</li>
  <li>いかなる形式（PDFを含む）でのデータのダウンロード・印刷・保存・エクスポートは<strong>禁止</strong>です。</li>
  <li>HRIの書面による同意なく第三者にアクセスを提供することは<strong>禁止</strong>です。</li>
</ul>
<div class="warn">これらの規定に違反した場合、企業アカウントの停止または終了を招く可能性があります。</div>
</section>
<section class="card" id="c5"><h2>第5条 — 外国企業・国際利用</h2>
<p>HRIサービスは原則としてインドネシア国内の企業向けです。HRIを利用する外国企業は以下を宣言・同意します：</p>
<ul>
  <li>インドネシア共和国の法律が適用されること；</li>
  <li>他の法域における法的遵守は企業の責任であること；</li>
  <li>インドネシア語が法的拘束力を持つ公式言語であること。</li>
</ul>
</section>
<section class="card" id="c6"><h2>第6条 — サービスの停止・終了</h2>
<p>HRIは、本規約または関連ポリシーの違反が疑われる場合、サービスの制限・停止・終了を行う権利を有します。</p>
</section>
<section class="card" id="c7"><h2>第7条 — 規約の変更</h2>
<p>本規約は随時更新される場合があります。HRI公式サイトに掲載された最新版が法的拘束力を持ちます。</p>
</section>
<section class="card" id="c8"><h2>第8条 — 準拠法</h2>
<p>本規約は<strong>インドネシア共和国の法律</strong>に基づき解釈・適用されます。</p>
<div class="ok">HRIサービスの利用を継続することにより、企業は本利用規約に同意したものとみなします。</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 韓国語
// ============================================================
export const ko_company = styles + `
<div id="hri-tos"><div class="wrap">
<header>
  <h1>HRI 서비스 이용약관 — 기업 회원</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  시행일: <strong>2025년 12월 13일</strong></div>
</header>
<nav class="card"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#c1">제1조 — 적용 범위 및 동의</a></li>
  <li><a href="#c2">제2조 — HRI의 위치</a></li>
  <li><a href="#c3">제3조 — 기업의 책임</a></li>
  <li><a href="#c4">제4조 — 데이터 이용 제한</a></li>
</ol></div><div><ol start="5">
  <li><a href="#c5">제5조 — 외국 기업 및 국제 이용</a></li>
  <li><a href="#c6">제6조 — 서비스 정지 및 해지</a></li>
  <li><a href="#c7">제7조 — 약관 변경</a></li>
  <li><a href="#c8">제8조 — 준거법</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="c1"><h2>제1조 — 적용 범위 및 동의</h2>
<p>본 약관은 계정 생성, 채용공고 게시, 지원자 데이터 접근, <strong>Verified Resume</strong> 정보 이용을 포함한 <strong>기업</strong>의 HRI 전체 서비스 이용을 규정합니다.</p>
<p>계정을 등록하거나 HRI 서비스를 이용함으로써 기업은 본 약관을 읽고 이해하며 <strong>법적 구속력 있게 동의</strong>한 것으로 간주합니다.</p>
</section>
<section class="card" id="c2"><h2>제2조 — HRI의 위치</h2>
<ul>
  <li>HRI는 <strong>디지털 시스템 및 플랫폼 제공자</strong>입니다.</li>
  <li>HRI는 고용주, 채용 대행사, 또는 직업 소개업자가 <strong>아닙니다</strong>.</li>
  <li>HRI는 기업과 지원자 간의 고용 관계 당사자가 아닙니다.</li>
</ul>
</section>
<section class="card" id="c3"><h2>제3조 — 기업의 책임</h2>
<ul>
  <li>채용공고의 모든 내용은 기업의 책임입니다.</li>
  <li>선발 및 채용 결정은 전적으로 기업에 있습니다.</li>
  <li>기업은 노동법 및 데이터 보호법 준수에 책임을 집니다.</li>
  <li>지원자 데이터 이용은 채용 목적으로만 제한되어야 합니다.</li>
</ul>
</section>
<section class="card" id="c4"><h2>제4조 — 데이터 이용 제한</h2>
<ul>
  <li>지원자 데이터는 HRI 시스템을 통해서만 접근할 수 있습니다.</li>
  <li>어떠한 형식(PDF 포함)으로도 데이터를 다운로드, 인쇄, 저장, 내보내기하는 것은 <strong>금지</strong>됩니다.</li>
  <li>HRI의 서면 동의 없이 제3자에게 접근을 제공하는 것은 <strong>금지</strong>됩니다.</li>
</ul>
<div class="warn">이러한 규정을 위반할 경우 기업 계정이 정지되거나 해지될 수 있습니다.</div>
</section>
<section class="card" id="c5"><h2>제5조 — 외국 기업 및 국제 이용</h2>
<p>HRI 서비스는 원칙적으로 인도네시아 내 기업을 위한 것입니다. HRI를 이용하는 외국 기업은 다음을 선언하고 동의합니다:</p>
<ul>
  <li>인도네시아 공화국 법률이 적용됨;</li>
  <li>다른 법역에서의 법적 준수는 기업의 책임;</li>
  <li>인도네시아어가 법적 구속력이 있는 공식 언어임.</li>
</ul>
</section>
<section class="card" id="c6"><h2>제6조 — 서비스 정지 및 해지</h2>
<p>HRI는 본 약관 또는 관련 정책의 위반이 의심되는 경우 서비스를 제한, 정지 또는 해지할 권리를 가집니다.</p>
</section>
<section class="card" id="c7"><h2>제7조 — 약관 변경</h2>
<p>본 약관은 수시로 업데이트될 수 있습니다. HRI 공식 사이트에 게시된 최신 버전이 법적 구속력을 가집니다.</p>
</section>
<section class="card" id="c8"><h2>제8조 — 준거법</h2>
<p>본 약관은 <strong>인도네시아 공화국 법률</strong>에 따라 해석·적용됩니다.</p>
<div class="ok">HRI 서비스 이용을 계속함으로써 기업은 본 이용약관에 동의한 것으로 간주합니다.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 英語
// ============================================================
export const en_company = styles + `
<div id="hri-tos"><div class="wrap">
<header>
  <h1>HRI Service Terms of Use — Corporate Members</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Effective date: <strong>December 13, 2025</strong></div>
</header>
<nav class="card"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#c1">Article 1 — Scope & Agreement</a></li>
  <li><a href="#c2">Article 2 — HRI's Position</a></li>
  <li><a href="#c3">Article 3 — Corporate Responsibilities</a></li>
  <li><a href="#c4">Article 4 — Data Use Restrictions</a></li>
</ol></div><div><ol start="5">
  <li><a href="#c5">Article 5 — Foreign Companies & Cross-border Use</a></li>
  <li><a href="#c6">Article 6 — Service Suspension & Termination</a></li>
  <li><a href="#c7">Article 7 — Changes to Terms</a></li>
  <li><a href="#c8">Article 8 — Governing Law</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="c1"><h2>Article 1 — Scope & Agreement</h2>
<p>These Terms govern the use of all HRI services by <strong>Companies</strong>, including but not limited to account creation, job posting, access to applicant data, and use of <strong>Verified Resume</strong> information.</p>
<p>By registering an account or using HRI services, the Company confirms it has read, understood, and <strong>agreed to these Terms as legally binding</strong>.</p>
</section>
<section class="card" id="c2"><h2>Article 2 — HRI's Position</h2>
<ul>
  <li>HRI is a <strong>digital system and platform provider</strong>.</li>
  <li>HRI is <strong>not</strong> an employer, recruitment agency, or employment intermediary.</li>
  <li>HRI is not a party to the employment relationship between the Company and applicants.</li>
</ul>
</section>
<section class="card" id="c3"><h2>Article 3 — Corporate Responsibilities</h2>
<ul>
  <li>All job listing content is the sole responsibility of the Company.</li>
  <li>Selection and hiring decisions rest entirely with the Company.</li>
  <li>The Company is responsible for compliance with labor laws and data protection regulations.</li>
  <li>Applicant data use must be strictly limited to recruitment purposes.</li>
</ul>
</section>
<section class="card" id="c4"><h2>Article 4 — Data Use Restrictions</h2>
<ul>
  <li>Applicant data may only be accessed through the HRI system.</li>
  <li>Downloading, printing, storing, or exporting data in any format (including PDF) is <strong>prohibited</strong>.</li>
  <li>Providing access to third parties without HRI's written consent is <strong>prohibited</strong>.</li>
</ul>
<div class="warn">Violation of these provisions may result in suspension or termination of the Company's account.</div>
</section>
<section class="card" id="c5"><h2>Article 5 — Foreign Companies & Cross-border Use</h2>
<p>HRI services are principally intended for companies in Indonesia. Foreign companies using HRI declare and agree that:</p>
<ul>
  <li>The laws of the Republic of Indonesia apply;</li>
  <li>Compliance with laws in other jurisdictions is the Company's responsibility;</li>
  <li>Indonesian is the official legally binding language.</li>
</ul>
</section>
<section class="card" id="c6"><h2>Article 6 — Service Suspension & Termination</h2>
<p>HRI reserves the right to restrict, suspend, or terminate services if there is suspected violation of these Terms or related policies.</p>
</section>
<section class="card" id="c7"><h2>Article 7 — Changes to Terms</h2>
<p>These Terms may be updated from time to time. The latest version published on the official HRI website shall be legally binding.</p>
</section>
<section class="card" id="c8"><h2>Article 8 — Governing Law</h2>
<p>These Terms are governed and interpreted in accordance with the <strong>laws of the Republic of Indonesia</strong>.</p>
<div class="ok">By continuing to use HRI services, the Company agrees to these Terms of Use.</div>
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