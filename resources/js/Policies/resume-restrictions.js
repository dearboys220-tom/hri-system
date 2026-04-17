const styles = `<style>
  #hri-vr{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:#0f172a}
  #hri-vr .wrap{max-width:880px;margin:0 auto;padding:24px 16px}
  #hri-vr header{background:#0A1A3A;color:#fff;border-radius:16px;padding:20px 18px;margin-bottom:16px}
  #hri-vr h1{margin:0 0 6px;font-size:1.35rem;line-height:1.25;color:#fff}
  #hri-vr .meta{opacity:.9;font-size:.9rem;color:#fff}
  #hri-vr .card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px;margin:16px 0}
  #hri-vr h2{font-size:1.1rem;margin:0 0 10px;color:#0A1A3A}
  #hri-vr h3{font-size:1rem;margin:14px 0 8px;color:#1e3a8a}
  #hri-vr p{margin:10px 0;line-height:1.8}
  #hri-vr ul,#hri-vr ol{margin:10px 0 10px 20px;line-height:1.8}
  #hri-vr li{margin:6px 0}
  #hri-vr .note{background:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:12px;margin:12px 0}
  #hri-vr .warn{background:#fff7ed;border:1px solid #fdba74;border-radius:12px;padding:12px;margin:12px 0}
  #hri-vr .ok{background:#e8f5e8;border:1px solid #66bb6a;border-radius:12px;padding:12px;margin:12px 0}
  #hri-vr .grid{display:grid;gap:10px}
  #hri-vr .grid.two{grid-template-columns:1fr}
  #hri-vr .legal{font-size:.9rem;opacity:.85}
  #hri-vr code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:.9em}
  @media(min-width:768px){
    #hri-vr .wrap{padding:28px 20px}
    #hri-vr h1{font-size:1.5rem}
    #hri-vr .grid.two{grid-template-columns:1fr 1fr}
  }
</style>`

// ============================================================
// 個人向け — インドネシア語
// ============================================================
export const id_individual = styles + `
<div id="hri-vr"><div class="wrap">
<header>
  <h1>📄 Kebijakan Penggunaan & Pembatasan Tampilan Verified Resume (Individu)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA — Merek: HRI<br>
  Terakhir diperbarui: <strong>13 Desember 2025</strong><br>
  Prinsip utama: <strong>Reference Only</strong> (tampilan online terbatas, bukan dokumen untuk disalin/diunduh).</div>
</header>
<nav class="card toc"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#vr1">Pasal 1 — Tujuan & Ruang Lingkup</a></li>
  <li><a href="#vr2">Pasal 2 — Definisi</a></li>
  <li><a href="#vr3">Pasal 3 — Prinsip "Reference Only"</a></li>
  <li><a href="#vr4">Pasal 4 — Pembatasan Akses</a></li>
  <li><a href="#vr5">Pasal 5 — Larangan</a></li>
</ol></div><div><ol start="6">
  <li><a href="#vr6">Pasal 6 — Data Identifikasi & Perlindungan NIK</a></li>
  <li><a href="#vr7">Pasal 7 — Penggunaan untuk Lamaran Kerja</a></li>
  <li><a href="#vr8">Pasal 8 — Log, Audit, & Penegakan</a></li>
  <li><a href="#vr9">Pasal 9 — Perubahan & Bahasa</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="vr1"><h2>Pasal 1 — Tujuan & Ruang Lingkup</h2>
<p>Kebijakan ini mengatur cara Anggota Individu menggunakan fitur <strong>Verified Resume</strong> di HRI, serta pembatasan tampilan dan akses bagi pihak yang melihat Verified Resume ("Penerima"). Kebijakan ini berlaku untuk semua tampilan Verified Resume melalui situs HRI dan subdomain terkait.</p>
<div class="note"><strong>Tujuan:</strong> menjaga akurasi, mencegah penyalahgunaan data pribadi, dan memastikan Verified Resume digunakan hanya sebagai referensi terbatas dalam konteks yang sah (mis. rekrutmen).</div>
</section>
<section class="card" id="vr2"><h2>Pasal 2 — Definisi</h2>
<ul>
  <li><strong>Verified Resume</strong>: resume yang telah melalui proses verifikasi internal HRI dan ditampilkan dengan penanda (mis. watermark / status "HRI Verified").</li>
  <li><strong>Anggota (Individu)</strong>: pengguna perorangan yang memiliki akun HRI.</li>
  <li><strong>Penerima</strong>: pihak yang diberi akses terbatas untuk melihat Verified Resume (mis. perusahaan anggota HRI saat proses rekrutmen).</li>
  <li><strong>Reference Only</strong>: mekanisme tampilan online terbatas untuk tujuan referensi, bukan untuk pengarsipan bebas, penyalinan massal, atau redistribusi.</li>
  <li><strong>Token Akses</strong>: tautan/kode/izin akses yang dibangkitkan sistem untuk mengatur siapa, kapan, dan berapa kali Verified Resume dapat dilihat.</li>
</ul>
</section>
<section class="card" id="vr3"><h2>Pasal 3 — Prinsip "Reference Only"</h2>
<p>Verified Resume disediakan sebagai <strong>tampilan online terbatas</strong> untuk Penerima. Prinsip ini berarti:</p>
<ul>
  <li>Penerima hanya boleh melihat Verified Resume untuk tujuan yang sah dan terbatas (mis. evaluasi rekrutmen).</li>
  <li>Verified Resume bukan "dokumen bebas" untuk disalin, disimpan, atau dipublikasikan kembali.</li>
  <li>Tampilan dapat memuat watermark/status, dan komponen tertentu dapat dibatasi demi perlindungan data.</li>
</ul>
<div class="warn"><strong>Penting:</strong> HRI dapat menerapkan pembatasan teknis, termasuk pemblokiran cetak, pembatasan tampilan, atau proteksi lain, sejauh wajar dan diperlukan untuk keamanan.</div>
</section>
<section class="card" id="vr4"><h2>Pasal 4 — Pembatasan Akses</h2>
<p>Akses Penerima terhadap Verified Resume dapat dikontrol melalui satu atau beberapa mekanisme berikut:</p>
<ul>
  <li><strong>Akses Khusus Langganan</strong>: hanya diberikan kepada pengguna yang telah berlangganan atau terdaftar resmi.</li>
  <li><strong>Masa Berlaku Langganan</strong>: hanya dapat digunakan dalam jangka waktu tertentu dan akan otomatis kedaluwarsa setelah lewat tanggal yang ditentukan.</li>
  <li><strong>Pencatatan Akses</strong>: sistem mencatat waktu akses, IP, User-agent, dan parameter teknis lain untuk audit.</li>
</ul>
</section>
<section class="card" id="vr5"><h2>Pasal 5 — Larangan</h2>
<p>Untuk melindungi data pribadi dan integritas layanan, tindakan berikut <strong>dilarang</strong>:</p>
<ul>
  <li>Mengunduh, menyimpan, atau mengarsipkan Verified Resume di luar mekanisme yang disediakan HRI.</li>
  <li>Mencetak, memotret, merekam layar, atau menyalin isi Verified Resume untuk tujuan redistribusi.</li>
  <li>Menjual, mempublikasikan, atau menyebarkan kembali Verified Resume atau bagian darinya.</li>
  <li>Mencoba mengakali pembatasan teknis (mis. scraping, automation, atau bypass proteksi).</li>
</ul>
<div class="warn">Pelanggaran dapat mengakibatkan <strong>pencabutan akses</strong>, <strong>penangguhan akun</strong>, dan/atau tindakan lain sesuai Ketentuan Penggunaan serta hukum yang berlaku.</div>
</section>
<section class="card" id="vr6"><h2>Pasal 6 — Data Identifikasi & Perlindungan NIK</h2>
<p>HRI menerapkan pembatasan ketat terhadap data identifikasi resmi (mis. <strong>NIK</strong> dan nomor identitas lain). Secara prinsip:</p>
<ul>
  <li>NIK tidak ditampilkan penuh kepada Penerima, kecuali diwajibkan oleh hukum atau persetujuan eksplisit yang sah.</li>
  <li>Komponen data tertentu dapat ditampilkan dalam bentuk <strong>anonim</strong> (contoh: <code>************1234</code>).</li>
  <li>Dokumen identitas asli (mis. KTP) tidak disediakan kepada Penerima sebagai konten yang dapat diunduh.</li>
</ul>
<div class="note">Pemrosesan data identifikasi dilakukan sesuai <strong>Kebijakan Privasi</strong> dan prinsip minimisasi data.</div>
</section>
<section class="card" id="vr7"><h2>Pasal 7 — Penggunaan untuk Lamaran Kerja</h2>
<p>Anggota Individu dapat menggunakan Verified Resume untuk mendukung lamaran kerja kepada perusahaan anggota HRI. Dengan mengaktifkan Verified Resume untuk lamaran tertentu:</p>
<ul>
  <li>Anda menyetujui bahwa perusahaan tujuan menerima akses <strong>reference only</strong> (tampilan online terbatas) terhadap Verified Resume.</li>
  <li>Keputusan rekrutmen sepenuhnya berada pada tanggung jawab perusahaan, dan HRI tidak menjamin hasilnya.</li>
</ul>
</section>
<section class="card" id="vr8"><h2>Pasal 8 — Log, Audit, & Penegakan</h2>
<ol>
  <li>HRI mencatat aktivitas akses dan penggunaan Verified Resume untuk tujuan keamanan, audit, dan kepatuhan.</li>
  <li>Jika terdeteksi penyalahgunaan, HRI dapat membatasi akses, menonaktifkan langganan, atau menangguhkan akun.</li>
  <li>HRI dapat meminta klarifikasi/konfirmasi kepada Anggota dalam batas yang wajar untuk menjaga integritas layanan.</li>
</ol>
</section>
<section class="card" id="vr9"><h2>Pasal 9 — Perubahan & Bahasa</h2>
<ol>
  <li>Kebijakan ini dapat diperbarui dari waktu ke waktu. Perubahan material akan diberitahukan melalui situs atau media yang sesuai.</li>
  <li>Bahasa Indonesia adalah versi resmi. Terjemahan bahasa lain bersifat referensi.</li>
</ol>
<div class="ok">Dengan menggunakan fitur Verified Resume, Anda menyatakan telah membaca dan memahami kebijakan ini.</div>
</section>
<section class="card"><h2>Kontak</h2>
<p class="legal">Dukungan: <a href="mailto:support@hri-check.com">support@hri-check.com</a></p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 日本語
// ============================================================
export const ja_individual = styles + `
<div id="hri-vr"><div class="wrap">
<header>
  <h1>📄 Verified Resume 利用・表示制限ポリシー（個人会員）</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  最終更新日：<strong>2025年12月13日</strong><br>
  基本原則：<strong>Reference Only</strong>（限定的オンライン表示のみ。コピー・ダウンロード不可）</div>
</header>
<nav class="card toc"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#vr1">第1条 — 目的・適用範囲</a></li>
  <li><a href="#vr2">第2条 — 定義</a></li>
  <li><a href="#vr3">第3条 — 「参照のみ」原則</a></li>
  <li><a href="#vr4">第4条 — アクセス制限</a></li>
  <li><a href="#vr5">第5条 — 禁止事項</a></li>
</ol></div><div><ol start="6">
  <li><a href="#vr6">第6条 — 識別データ・NIK保護</a></li>
  <li><a href="#vr7">第7条 — 求人応募への利用</a></li>
  <li><a href="#vr8">第8条 — ログ・監査・執行</a></li>
  <li><a href="#vr9">第9条 — 変更・言語</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="vr1"><h2>第1条 — 目的・適用範囲</h2>
<p>本ポリシーは、個人会員がHRIの<strong>Verified Resume</strong>機能を利用する方法、およびVerified Resumeを閲覧する受信者（「受信者」）に対する表示・アクセスの制限を規定します。本ポリシーはHRIサイトおよび関連サブドメインを通じたVerified Resumeのすべての表示に適用されます。</p>
<div class="note"><strong>目的：</strong>正確性の維持、個人データの不正利用防止、およびVerified Resumeが正当なコンテキスト（採用等）における限定的な参照資料としてのみ使用されることを確保します。</div>
</section>
<section class="card" id="vr2"><h2>第2条 — 定義</h2>
<ul>
  <li><strong>Verified Resume</strong>：HRIの内部認証プロセスを経て、マーク（ウォーターマーク・「HRI Verified」ステータス等）付きで表示される履歴書。</li>
  <li><strong>会員（個人）</strong>：HRIアカウントを持つ個人ユーザー。</li>
  <li><strong>受信者</strong>：Verified Resumeへの限定アクセスが付与される者（採用プロセス中のHRI企業会員等）。</li>
  <li><strong>Reference Only（参照のみ）</strong>：参照目的のための限定的なオンライン表示メカニズム。自由なアーカイブ・大量コピー・再配布のためではない。</li>
  <li><strong>アクセストークン</strong>：Verified Resumeの閲覧者・閲覧時期・閲覧回数を制御するためにシステムが生成するリンク/コード/アクセス許可。</li>
</ul>
</section>
<section class="card" id="vr3"><h2>第3条 — 「参照のみ」原則</h2>
<p>Verified Resumeは受信者向けの<strong>限定的なオンライン表示</strong>として提供されます。この原則は以下を意味します：</p>
<ul>
  <li>受信者はVerified Resumeを正当かつ限定的な目的（採用評価等）のみに閲覧できます。</li>
  <li>Verified Resumeはコピー・保存・再公開できる「フリードキュメント」ではありません。</li>
  <li>表示にはウォーターマーク/ステータスが含まれる場合があり、データ保護のため特定のコンポーネントが制限される場合があります。</li>
</ul>
<div class="warn"><strong>重要：</strong>HRIはセキュリティのために合理的かつ必要な範囲で、印刷ブロック・表示制限・その他の保護など、技術的制限を適用する場合があります。</div>
</section>
<section class="card" id="vr4"><h2>第4条 — アクセス制限</h2>
<p>受信者のVerified Resumeへのアクセスは以下の1つまたは複数のメカニズムで制御されます：</p>
<ul>
  <li><strong>サブスクリプション限定アクセス</strong>：サブスクリプション契約者または正式登録者のみに特定機能へのアクセスが提供されます。</li>
  <li><strong>サブスクリプション有効期間</strong>：特定の期間のみ利用可能で、指定日を過ぎると自動的に期限切れになります。継続アクセスにはサブスクリプションの更新が必要です。</li>
  <li><strong>アクセス記録</strong>：システムはアクセス時刻・IP・ユーザーエージェント・その他の技術パラメータを監査のために記録します。</li>
</ul>
</section>
<section class="card" id="vr5"><h2>第5条 — 禁止事項</h2>
<p>個人データとサービスの整合性を保護するため、以下の行為は<strong>禁止</strong>されています：</p>
<ul>
  <li>HRIが提供するメカニズム以外でのVerified Resumeのダウンロード・保存・アーカイブ。</li>
  <li>再配布目的でのVerified Resumeの内容の印刷・写真撮影・画面録画・コピー。</li>
  <li>Verified Resumeまたはその一部の販売・公開・再配布。</li>
  <li>技術的制限を回避しようとする試み（スクレイピング・自動化・保護のバイパス等）。</li>
</ul>
<div class="warn">違反はアクセスの取り消し、アカウント停止、および/または利用規約と適用法に基づくその他の措置を招く場合があります。</div>
</section>
<section class="card" id="vr6"><h2>第6条 — 識別データ・NIK保護</h2>
<p>HRIは公式識別データ（<strong>NIK</strong>およびその他の識別番号）に対して厳格な制限を適用します。原則として：</p>
<ul>
  <li>NIKは、法律による義務または正当な明示的同意がない限り、受信者に完全な形では表示されません。</li>
  <li>特定のデータコンポーネントは<strong>匿名化</strong>された形式で表示される場合があります（例：<code>************1234</code>）。</li>
  <li>元の身分証明書（KTP等）はダウンロード可能なコンテンツとして受信者に提供されません。</li>
</ul>
<div class="note">識別データの処理は<strong>プライバシーポリシー</strong>およびデータ最小化の原則に従って行われます。</div>
</section>
<section class="card" id="vr7"><h2>第7条 — 求人応募への利用</h2>
<p>個人会員はHRI企業会員への求人応募のサポートにVerified Resumeを使用できます。特定の応募に対してVerified Resumeを有効にすることにより：</p>
<ul>
  <li>応募先企業がVerified Resumeへの<strong>参照のみ</strong>（限定的オンライン表示）アクセスを受け取ることに同意したものとみなします。</li>
  <li>採用の意思決定は完全に企業の責任であり、HRIはその結果を保証しません。</li>
</ul>
</section>
<section class="card" id="vr8"><h2>第8条 — ログ・監査・執行</h2>
<ol>
  <li>HRIはセキュリティ・監査・コンプライアンスのためVerified Resumeのアクセスおよび使用活動を記録します。</li>
  <li>不正利用（サブスクリプションの共有・スクレイピング・バイパスの試み等）が検出された場合、HRIはアクセスの制限・サブスクリプションの無効化・アカウントの停止を行う場合があります。</li>
  <li>HRIはサービスの整合性を維持するため、合理的な範囲で会員に説明・確認を求める場合があります。</li>
</ol>
</section>
<section class="card" id="vr9"><h2>第9条 — 変更・言語</h2>
<ol>
  <li>本ポリシーは随時更新される場合があります。重要な変更はサイトまたは適切な媒体を通じて通知します。</li>
  <li>インドネシア語版が正式版です。他言語の翻訳は参考情報です。</li>
</ol>
<div class="ok">Verified Resume機能を使用することにより、本ポリシーを読み理解したことを表明します。</div>
</section>
<section class="card"><h2>連絡先</h2>
<p class="legal">サポート：<a href="mailto:support@hri-check.com">support@hri-check.com</a></p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 韓国語
// ============================================================
export const ko_individual = styles + `
<div id="hri-vr"><div class="wrap">
<header>
  <h1>📄 Verified Resume 이용 및 표시 제한 정책 (개인 회원)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  최종 업데이트: <strong>2025년 12월 13일</strong><br>
  기본 원칙: <strong>Reference Only</strong> (제한적 온라인 표시만 가능. 복사·다운로드 불가)</div>
</header>
<nav class="card toc"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#vr1">제1조 — 목적 및 적용 범위</a></li>
  <li><a href="#vr2">제2조 — 정의</a></li>
  <li><a href="#vr3">제3조 — "참조 전용" 원칙</a></li>
  <li><a href="#vr4">제4조 — 접근 제한</a></li>
  <li><a href="#vr5">제5조 — 금지 사항</a></li>
</ol></div><div><ol start="6">
  <li><a href="#vr6">제6조 — 식별 데이터 및 NIK 보호</a></li>
  <li><a href="#vr7">제7조 — 입사 지원에의 이용</a></li>
  <li><a href="#vr8">제8조 — 로그, 감사 및 집행</a></li>
  <li><a href="#vr9">제9조 — 변경 및 언어</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="vr1"><h2>제1조 — 목적 및 적용 범위</h2>
<p>본 정책은 개인 회원이 HRI의 <strong>Verified Resume</strong> 기능을 이용하는 방식과 Verified Resume를 열람하는 수신자("수신자")에 대한 표시 및 접근 제한을 규정합니다.</p>
<div class="note"><strong>목적:</strong> 정확성 유지, 개인정보 오용 방지, Verified Resume가 정당한 맥락(채용 등)에서 제한적 참조 자료로만 사용되도록 보장합니다.</div>
</section>
<section class="card" id="vr2"><h2>제2조 — 정의</h2>
<ul>
  <li><strong>Verified Resume</strong>: HRI 내부 인증 절차를 거쳐 표시(워터마크/"HRI Verified" 상태 등)와 함께 제공되는 이력서.</li>
  <li><strong>회원(개인)</strong>: HRI 계정을 보유한 개인 사용자.</li>
  <li><strong>수신자</strong>: Verified Resume에 대한 제한적 접근이 부여된 자(채용 프로세스 중 HRI 기업 회원 등).</li>
  <li><strong>Reference Only(참조 전용)</strong>: 참조 목적을 위한 제한적 온라인 표시 메커니즘. 자유로운 보관·대량 복사·재배포를 위한 것이 아님.</li>
  <li><strong>접근 토큰</strong>: 누가, 언제, 몇 번 Verified Resume를 열람할 수 있는지를 제어하기 위해 시스템이 생성하는 링크/코드/접근 허가.</li>
</ul>
</section>
<section class="card" id="vr3"><h2>제3조 — "참조 전용" 원칙</h2>
<p>Verified Resume는 수신자를 위한 <strong>제한적 온라인 표시</strong>로 제공됩니다. 이 원칙은 다음을 의미합니다:</p>
<ul>
  <li>수신자는 정당하고 제한적인 목적(채용 평가 등)으로만 Verified Resume를 열람할 수 있습니다.</li>
  <li>Verified Resume는 복사·저장·재공개할 수 있는 "자유 문서"가 아닙니다.</li>
  <li>표시에 워터마크/상태가 포함될 수 있으며, 데이터 보호를 위해 특정 구성 요소가 제한될 수 있습니다.</li>
</ul>
<div class="warn"><strong>중요:</strong> HRI는 보안을 위해 합리적이고 필요한 범위에서 인쇄 차단, 표시 제한 또는 기타 보호 등 기술적 제한을 적용할 수 있습니다.</div>
</section>
<section class="card" id="vr4"><h2>제4조 — 접근 제한</h2>
<p>수신자의 Verified Resume 접근은 다음 하나 이상의 메커니즘으로 제어될 수 있습니다:</p>
<ul>
  <li><strong>구독 전용 접근</strong>: 구독 계약자 또는 정식 등록자에게만 특정 기능 접근이 제공됩니다.</li>
  <li><strong>구독 유효 기간</strong>: 특정 기간 동안만 이용 가능하며 지정일 이후 자동으로 만료됩니다. 계속 접근하려면 구독을 갱신해야 합니다.</li>
  <li><strong>접근 기록</strong>: 시스템은 감사를 위해 접근 시간, IP, 사용자 에이전트 및 기타 기술 매개변수를 기록합니다.</li>
</ul>
</section>
<section class="card" id="vr5"><h2>제5조 — 금지 사항</h2>
<p>개인정보와 서비스 무결성을 보호하기 위해 다음 행위는 <strong>금지</strong>됩니다:</p>
<ul>
  <li>HRI가 제공하는 메커니즘 외부에서 Verified Resume를 다운로드, 저장 또는 보관하는 행위.</li>
  <li>재배포 목적으로 Verified Resume 내용을 인쇄, 촬영, 화면 녹화 또는 복사하는 행위.</li>
  <li>Verified Resume 또는 그 일부를 판매, 공개 또는 재배포하는 행위.</li>
  <li>기술적 제한을 우회하려는 시도(스크래핑, 자동화 또는 보호 우회 등).</li>
</ul>
<div class="warn">위반은 접근 취소, 계정 정지 및/또는 이용약관 및 적용 법률에 따른 기타 조치를 초래할 수 있습니다.</div>
</section>
<section class="card" id="vr6"><h2>제6조 — 식별 데이터 및 NIK 보호</h2>
<p>HRI는 공식 식별 데이터(<strong>NIK</strong> 및 기타 식별 번호)에 대해 엄격한 제한을 적용합니다. 원칙적으로:</p>
<ul>
  <li>NIK는 법률에 의한 의무 또는 정당한 명시적 동의가 없는 한 수신자에게 완전한 형태로 표시되지 않습니다.</li>
  <li>특정 데이터 구성 요소는 <strong>익명화</strong>된 형식으로 표시될 수 있습니다(예: <code>************1234</code>).</li>
  <li>원본 신분증(KTP 등)은 다운로드 가능한 콘텐츠로 수신자에게 제공되지 않습니다.</li>
</ul>
<div class="note">식별 데이터 처리는 <strong>개인정보처리방침</strong> 및 데이터 최소화 원칙에 따라 이루어집니다.</div>
</section>
<section class="card" id="vr7"><h2>제7조 — 입사 지원에의 이용</h2>
<p>개인 회원은 HRI 기업 회원에 대한 입사 지원을 지원하기 위해 Verified Resume를 사용할 수 있습니다. 특정 지원에 Verified Resume를 활성화함으로써:</p>
<ul>
  <li>지원 대상 기업이 Verified Resume에 대한 <strong>참조 전용</strong>(제한적 온라인 표시) 접근을 받는 것에 동의한 것으로 간주합니다.</li>
  <li>채용 결정은 전적으로 기업의 책임이며 HRI는 그 결과를 보장하지 않습니다.</li>
</ul>
</section>
<section class="card" id="vr8"><h2>제8조 — 로그, 감사 및 집행</h2>
<ol>
  <li>HRI는 보안, 감사 및 규정 준수를 위해 Verified Resume의 접근 및 사용 활동을 기록합니다.</li>
  <li>오용(구독 공유, 스크래핑 또는 우회 시도 등)이 감지되면 HRI는 접근 제한, 구독 비활성화 또는 계정 정지를 할 수 있습니다.</li>
  <li>HRI는 서비스 무결성 유지를 위해 합리적인 범위에서 회원에게 설명·확인을 요청할 수 있습니다.</li>
</ol>
</section>
<section class="card" id="vr9"><h2>제9조 — 변경 및 언어</h2>
<ol>
  <li>본 정책은 수시로 업데이트될 수 있습니다. 중요한 변경 사항은 사이트 또는 적절한 매체를 통해 통지합니다.</li>
  <li>인도네시아어 버전이 공식 버전입니다. 다른 언어 번역본은 참고용입니다.</li>
</ol>
<div class="ok">Verified Resume 기능을 사용함으로써 본 정책을 읽고 이해했음을 선언합니다.</div>
</section>
<section class="card"><h2>연락처</h2>
<p class="legal">지원: <a href="mailto:support@hri-check.com">support@hri-check.com</a></p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 英語
// ============================================================
export const en_individual = styles + `
<div id="hri-vr"><div class="wrap">
<header>
  <h1>📄 Verified Resume Usage & Display Restriction Policy (Individual Members)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Last updated: <strong>December 13, 2025</strong><br>
  Core principle: <strong>Reference Only</strong> (limited online display only. No copying or downloading).</div>
</header>
<nav class="card toc"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#vr1">Article 1 — Purpose & Scope</a></li>
  <li><a href="#vr2">Article 2 — Definitions</a></li>
  <li><a href="#vr3">Article 3 — "Reference Only" Principle</a></li>
  <li><a href="#vr4">Article 4 — Access Restrictions</a></li>
  <li><a href="#vr5">Article 5 — Prohibitions</a></li>
</ol></div><div><ol start="6">
  <li><a href="#vr6">Article 6 — Identification Data & NIK Protection</a></li>
  <li><a href="#vr7">Article 7 — Use for Job Applications</a></li>
  <li><a href="#vr8">Article 8 — Logs, Audit & Enforcement</a></li>
  <li><a href="#vr9">Article 9 — Changes & Language</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="vr1"><h2>Article 1 — Purpose & Scope</h2>
<p>This policy governs how Individual Members use the <strong>Verified Resume</strong> feature on HRI, as well as display and access restrictions for parties viewing the Verified Resume ("Recipients"). This policy applies to all Verified Resume displays through the HRI site and related subdomains.</p>
<div class="note"><strong>Purpose:</strong> to maintain accuracy, prevent misuse of personal data, and ensure Verified Resume is used only as a limited reference in legitimate contexts (e.g., recruitment).</div>
</section>
<section class="card" id="vr2"><h2>Article 2 — Definitions</h2>
<ul>
  <li><strong>Verified Resume</strong>: a resume that has undergone HRI's internal verification process and is displayed with a mark (e.g., watermark / "HRI Verified" status).</li>
  <li><strong>Member (Individual)</strong>: an individual user who holds an HRI account.</li>
  <li><strong>Recipient</strong>: a party granted limited access to view the Verified Resume (e.g., an HRI corporate member during the recruitment process).</li>
  <li><strong>Reference Only</strong>: a limited online display mechanism for reference purposes, not for free archiving, mass copying, or redistribution.</li>
  <li><strong>Access Token</strong>: a link/code/access permission generated by the system to control who, when, and how many times a Verified Resume can be viewed.</li>
</ul>
</section>
<section class="card" id="vr3"><h2>Article 3 — "Reference Only" Principle</h2>
<p>Verified Resume is provided as a <strong>limited online display</strong> for Recipients. This principle means:</p>
<ul>
  <li>Recipients may only view the Verified Resume for legitimate and limited purposes (e.g., recruitment evaluation).</li>
  <li>Verified Resume is not a "free document" to be copied, stored, or republished.</li>
  <li>The display may include watermarks/status, and certain components may be restricted for data protection.</li>
</ul>
<div class="warn"><strong>Important:</strong> HRI may apply technical restrictions, including print blocking, display restrictions, or other protections, to the extent reasonable and necessary for security.</div>
</section>
<section class="card" id="vr4"><h2>Article 4 — Access Restrictions</h2>
<p>Recipient access to Verified Resume may be controlled through one or more of the following mechanisms:</p>
<ul>
  <li><strong>Subscription-only Access</strong>: access to certain features is only granted to subscribed or officially registered users.</li>
  <li><strong>Subscription Validity Period</strong>: only usable within a specific time period and automatically expires after the designated date. Users must renew their subscription to continue accessing data.</li>
  <li><strong>Access Recording</strong>: the system records access time, IP, user-agent, and other technical parameters for audit.</li>
</ul>
</section>
<section class="card" id="vr5"><h2>Article 5 — Prohibitions</h2>
<p>To protect personal data and service integrity, the following actions are <strong>prohibited</strong>:</p>
<ul>
  <li>Downloading, storing, or archiving Verified Resume outside the mechanisms provided by HRI.</li>
  <li>Printing, photographing, screen recording, or copying Verified Resume content for redistribution purposes.</li>
  <li>Selling, publishing, or redistributing Verified Resume or any part thereof.</li>
  <li>Attempting to circumvent technical restrictions (e.g., scraping, automation, or bypassing protections).</li>
</ul>
<div class="warn">Violations may result in <strong>revocation of access</strong>, <strong>account suspension</strong>, and/or other actions pursuant to the Terms of Use and applicable law.</div>
</section>
<section class="card" id="vr6"><h2>Article 6 — Identification Data & NIK Protection</h2>
<p>HRI applies strict restrictions on official identification data (e.g., <strong>NIK</strong> and other identification numbers). In principle:</p>
<ul>
  <li>NIK is not displayed in full to Recipients, unless required by law or with valid explicit consent.</li>
  <li>Certain data components may be displayed in <strong>anonymized</strong> form (e.g., <code>************1234</code>).</li>
  <li>Original identity documents (e.g., KTP) are not provided to Recipients as downloadable content.</li>
</ul>
<div class="note">Processing of identification data is carried out in accordance with the <strong>Privacy Policy</strong> and the principle of data minimization.</div>
</section>
<section class="card" id="vr7"><h2>Article 7 — Use for Job Applications</h2>
<p>Individual Members may use Verified Resume to support job applications to HRI corporate members. By activating Verified Resume for a specific application:</p>
<ul>
  <li>You agree that the target company receives <strong>reference only</strong> (limited online display) access to your Verified Resume.</li>
  <li>Recruitment decisions rest entirely with the company, and HRI does not guarantee the outcome.</li>
</ul>
</section>
<section class="card" id="vr8"><h2>Article 8 — Logs, Audit & Enforcement</h2>
<ol>
  <li>HRI records Verified Resume access and usage activities for security, audit, and compliance purposes.</li>
  <li>If misuse is detected (e.g., subscription sharing, scraping, or bypass attempts), HRI may restrict access, deactivate subscriptions, or suspend accounts.</li>
  <li>HRI may request clarification/confirmation from Members within reasonable limits to maintain service integrity.</li>
</ol>
</section>
<section class="card" id="vr9"><h2>Article 9 — Changes & Language</h2>
<ol>
  <li>This policy may be updated from time to time. Material changes will be notified through the site or appropriate media.</li>
  <li>The Indonesian version is the official version. Translations in other languages are for reference only.</li>
</ol>
<div class="ok">By using the Verified Resume feature, you confirm that you have read and understood this policy.</div>
</section>
<section class="card"><h2>Contact</h2>
<p class="legal">Support: <a href="mailto:support@hri-check.com">support@hri-check.com</a></p>
</section>
</main></div></div>`

// ============================================================
// 企業向け — インドネシア語
// ============================================================
export const id_company = styles + `
<div id="hri-vr"><div class="wrap">
<header>
  <h1>📘 Kebijakan Pembatasan Penggunaan Verified Resume — Perusahaan</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Merek: HRI)<br>
  Berlaku sejak: <strong>13 Desember 2025</strong></div>
</header>
<nav class="card toc"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#vr1">Pasal 1 — Kedudukan Verified Resume</a></li>
  <li><a href="#vr2">Pasal 2 — Sifat Informasi (Reference Only)</a></li>
  <li><a href="#vr3">Pasal 3 — Penggunaan yang Diizinkan</a></li>
  <li><a href="#vr4">Pasal 4 — Larangan</a></li>
</ol></div><div><ol start="5">
  <li><a href="#vr5">Pasal 5 — Tanggung Jawab & Risiko</a></li>
  <li><a href="#vr6">Pasal 6 — Perusahaan Asing & Penggunaan Lintas Negara</a></li>
  <li><a href="#vr7">Pasal 7 — Pemantauan & Penegakan</a></li>
  <li><a href="#vr8">Pasal 8 — Perubahan & Hukum yang Berlaku</a></li>
</ol></div></div>
<p class="legal">Dokumen terkait: <strong>Ketentuan Penggunaan HRI</strong>, <strong>Kebijakan Privasi</strong>, dan kebijakan HRI lainnya.</p>
</nav>
<main>
<section class="card" id="vr1"><h2>Pasal 1 — Kedudukan Verified Resume</h2>
<p><strong>Verified Resume</strong> adalah informasi ringkas hasil proses verifikasi terbatas yang disediakan oleh HRI untuk tujuan referensi dalam proses rekrutmen.</p>
<p>Verified Resume <strong>bukan</strong> jaminan kebenaran mutlak, rekomendasi, atau penilaian akhir atas pelamar.</p>
</section>
<section class="card" id="vr2"><h2>Pasal 2 — Sifat Informasi (Reference Only)</h2>
<ul>
  <li>Verified Resume disediakan <strong>hanya sebagai referensi</strong>.</li>
  <li>HRI tidak menjamin keakuratan, kelengkapan, atau keterkinian informasi.</li>
  <li>Keputusan rekrutmen sepenuhnya berada pada Perusahaan.</li>
</ul>
<div class="warn">Penggunaan Verified Resume sebagai dasar tunggal keputusan rekrutmen merupakan risiko Perusahaan sendiri.</div>
</section>
<section class="card" id="vr3"><h2>Pasal 3 — Penggunaan yang Diizinkan</h2>
<p>Perusahaan hanya diperbolehkan menggunakan Verified Resume untuk tujuan <strong>evaluasi internal dalam proses seleksi</strong>.</p>
<ul>
  <li>Penggunaan bersifat sementara selama proses rekrutmen berlangsung;</li>
  <li>Penggunaan terbatas pada personel yang berwenang.</li>
</ul>
</section>
<section class="card" id="vr4"><h2>Pasal 4 — Larangan</h2>
<ul>
  <li><strong>Dilarang</strong> mengunduh, mencetak, menyimpan, atau mengekspor Verified Resume dalam bentuk apa pun (termasuk PDF).</li>
  <li><strong>Dilarang</strong> membagikan Verified Resume kepada pihak ketiga.</li>
  <li><strong>Dilarang</strong> menggunakan Verified Resume untuk tujuan non-rekrutmen.</li>
  <li><strong>Dilarang</strong> mengklaim atau menyatakan bahwa HRI menjamin kualitas pelamar.</li>
</ul>
</section>
<section class="card" id="vr5"><h2>Pasal 5 — Tanggung Jawab & Risiko</h2>
<p>Perusahaan menanggung seluruh risiko yang timbul dari penggunaan Verified Resume, termasuk kesalahan interpretasi atau ketergantungan berlebihan.</p>
<div class="note">HRI tidak bertanggung jawab atas kerugian langsung maupun tidak langsung akibat penggunaan Verified Resume.</div>
</section>
<section class="card" id="vr6"><h2>Pasal 6 — Perusahaan Asing & Penggunaan Lintas Negara</h2>
<p>Perusahaan asing atau penggunaan lintas negara atas Verified Resume dilakukan atas tanggung jawab Perusahaan sepenuhnya, termasuk kepatuhan hukum di yurisdiksi terkait.</p>
</section>
<section class="card" id="vr7"><h2>Pasal 7 — Pemantauan & Penegakan</h2>
<p>Akses dan penggunaan Verified Resume dicatat dan dapat diaudit. HRI berhak mengambil tindakan pembatasan atau penghentian akses apabila terjadi pelanggaran kebijakan ini.</p>
</section>
<section class="card" id="vr8"><h2>Pasal 8 — Perubahan & Hukum yang Berlaku</h2>
<ol>
  <li>Kebijakan ini dapat diperbarui dari waktu ke waktu.</li>
  <li>Diatur dan ditafsirkan berdasarkan <strong>hukum Republik Indonesia</strong>.</li>
</ol>
<div class="ok">Dengan mengakses Verified Resume melalui HRI, Perusahaan menyetujui kebijakan ini.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 日本語
// ============================================================
export const ja_company = styles + `
<div id="hri-vr"><div class="wrap">
<header>
  <h1>📘 Verified Resume 利用制限ポリシー（企業会員）</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  施行日：<strong>2025年12月13日</strong></div>
</header>
<nav class="card toc"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#vr1">第1条 — Verified Resumeの位置づけ</a></li>
  <li><a href="#vr2">第2条 — 情報の性質（参照のみ）</a></li>
  <li><a href="#vr3">第3条 — 許可される利用</a></li>
  <li><a href="#vr4">第4条 — 禁止事項</a></li>
</ol></div><div><ol start="5">
  <li><a href="#vr5">第5条 — 責任・リスク</a></li>
  <li><a href="#vr6">第6条 — 外国企業・国際利用</a></li>
  <li><a href="#vr7">第7条 — 監視・執行</a></li>
  <li><a href="#vr8">第8条 — 変更・準拠法</a></li>
</ol></div></div>
<p class="legal">関連文書：<strong>HRI利用規約</strong>、<strong>プライバシーポリシー</strong>、その他HRIポリシー</p>
</nav>
<main>
<section class="card" id="vr1"><h2>第1条 — Verified Resumeの位置づけ</h2>
<p><strong>Verified Resume</strong>は、採用プロセスにおける参照目的のためにHRIが提供する、限定的な認証プロセスの結果をまとめた情報です。</p>
<p>Verified Resumeは絶対的な正確性の保証・推薦・応募者に関する最終評価では<strong>ありません</strong>。</p>
</section>
<section class="card" id="vr2"><h2>第2条 — 情報の性質（参照のみ）</h2>
<ul>
  <li>Verified Resumeは<strong>参照資料としてのみ</strong>提供されます。</li>
  <li>HRIは情報の正確性・完全性・最新性を保証しません。</li>
  <li>採用の意思決定は完全に企業に委ねられています。</li>
</ul>
<div class="warn">Verified Resumeを採用決定の唯一の根拠として使用することは企業自身のリスクです。</div>
</section>
<section class="card" id="vr3"><h2>第3条 — 許可される利用</h2>
<p>企業は<strong>選考プロセスにおける内部評価</strong>の目的でのみVerified Resumeを使用することが許可されます。</p>
<ul>
  <li>採用プロセス進行中の一時的な利用；</li>
  <li>権限ある担当者に限定した利用。</li>
</ul>
</section>
<section class="card" id="vr4"><h2>第4条 — 禁止事項</h2>
<ul>
  <li>いかなる形式（PDFを含む）でのVerified Resumeのダウンロード・印刷・保存・エクスポートは<strong>禁止</strong>です。</li>
  <li>第三者へのVerified Resumeの共有は<strong>禁止</strong>です。</li>
  <li>採用以外の目的でのVerified Resumeの使用は<strong>禁止</strong>です。</li>
  <li>HRIが応募者の品質を保証するという主張または表明は<strong>禁止</strong>です。</li>
</ul>
</section>
<section class="card" id="vr5"><h2>第5条 — 責任・リスク</h2>
<p>企業はVerified Resumeの使用から生じるすべてのリスク（解釈の誤りや過度の依存を含む）を負います。</p>
<div class="note">HRIはVerified Resumeの使用による直接的または間接的な損害について責任を負いません。</div>
</section>
<section class="card" id="vr6"><h2>第6条 — 外国企業・国際利用</h2>
<p>外国企業またはVerified Resumeの国際的な利用は、関連法域における法的遵守を含め、企業が全責任を負います。</p>
</section>
<section class="card" id="vr7"><h2>第7条 — 監視・執行</h2>
<p>Verified Resumeへのアクセスと使用は記録され、監査される場合があります。HRIは本ポリシーの違反が発生した場合、アクセスの制限または停止の措置を講じる権利を有します。</p>
</section>
<section class="card" id="vr8"><h2>第8条 — 変更・準拠法</h2>
<ol>
  <li>本ポリシーは随時更新される場合があります。</li>
  <li><strong>インドネシア共和国の法律</strong>に基づき解釈・適用されます。</li>
</ol>
<div class="ok">HRIを通じてVerified Resumeにアクセスすることにより、企業は本ポリシーに同意したものとみなします。</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 韓国語
// ============================================================
export const ko_company = styles + `
<div id="hri-vr"><div class="wrap">
<header>
  <h1>📘 Verified Resume 이용 제한 정책 (기업 회원)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  시행일: <strong>2025년 12월 13일</strong></div>
</header>
<nav class="card toc"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#vr1">제1조 — Verified Resume의 위치</a></li>
  <li><a href="#vr2">제2조 — 정보의 성질 (참조 전용)</a></li>
  <li><a href="#vr3">제3조 — 허용되는 이용</a></li>
  <li><a href="#vr4">제4조 — 금지 사항</a></li>
</ol></div><div><ol start="5">
  <li><a href="#vr5">제5조 — 책임 및 위험</a></li>
  <li><a href="#vr6">제6조 — 외국 기업 및 국제 이용</a></li>
  <li><a href="#vr7">제7조 — 모니터링 및 집행</a></li>
  <li><a href="#vr8">제8조 — 변경 및 준거법</a></li>
</ol></div></div>
<p class="legal">관련 문서: <strong>HRI 이용약관</strong>, <strong>개인정보처리방침</strong> 및 기타 HRI 정책</p>
</nav>
<main>
<section class="card" id="vr1"><h2>제1조 — Verified Resume의 위치</h2>
<p><strong>Verified Resume</strong>는 채용 프로세스에서 참조 목적으로 HRI가 제공하는 제한적인 인증 프로세스 결과의 요약 정보입니다.</p>
<p>Verified Resume는 절대적인 정확성 보장, 추천 또는 지원자에 대한 최종 평가가 <strong>아닙니다</strong>.</p>
</section>
<section class="card" id="vr2"><h2>제2조 — 정보의 성질 (참조 전용)</h2>
<ul>
  <li>Verified Resume는 <strong>참조 자료로만</strong> 제공됩니다.</li>
  <li>HRI는 정보의 정확성, 완전성 또는 최신성을 보장하지 않습니다.</li>
  <li>채용 결정은 전적으로 기업에 있습니다.</li>
</ul>
<div class="warn">Verified Resume를 채용 결정의 유일한 근거로 사용하는 것은 기업 자신의 위험입니다.</div>
</section>
<section class="card" id="vr3"><h2>제3조 — 허용되는 이용</h2>
<p>기업은 <strong>선발 프로세스에서의 내부 평가</strong> 목적으로만 Verified Resume를 사용할 수 있습니다.</p>
<ul>
  <li>채용 프로세스 진행 중의 일시적인 이용;</li>
  <li>권한 있는 담당자로 제한된 이용.</li>
</ul>
</section>
<section class="card" id="vr4"><h2>제4조 — 금지 사항</h2>
<ul>
  <li>어떠한 형식(PDF 포함)으로도 Verified Resume를 다운로드, 인쇄, 저장 또는 내보내는 것은 <strong>금지</strong>됩니다.</li>
  <li>제3자에게 Verified Resume를 공유하는 것은 <strong>금지</strong>됩니다.</li>
  <li>채용 이외의 목적으로 Verified Resume를 사용하는 것은 <strong>금지</strong>됩니다.</li>
  <li>HRI가 지원자의 품질을 보장한다고 주장하거나 표명하는 것은 <strong>금지</strong>됩니다.</li>
</ul>
</section>
<section class="card" id="vr5"><h2>제5조 — 책임 및 위험</h2>
<p>기업은 Verified Resume 이용으로 발생하는 모든 위험(잘못된 해석이나 과도한 의존 포함)을 부담합니다.</p>
<div class="note">HRI는 Verified Resume 이용으로 인한 직접적 또는 간접적 손해에 대해 책임을 지지 않습니다.</div>
</section>
<section class="card" id="vr6"><h2>제6조 — 외국 기업 및 국제 이용</h2>
<p>외국 기업 또는 Verified Resume의 국제적 이용은 관련 법역에서의 법적 준수를 포함하여 전적으로 기업의 책임입니다.</p>
</section>
<section class="card" id="vr7"><h2>제7조 — 모니터링 및 집행</h2>
<p>Verified Resume에 대한 접근 및 사용은 기록되고 감사될 수 있습니다. HRI는 본 정책 위반이 발생할 경우 접근 제한 또는 중단 조치를 취할 권리를 가집니다.</p>
</section>
<section class="card" id="vr8"><h2>제8조 — 변경 및 준거법</h2>
<ol>
  <li>본 정책은 수시로 업데이트될 수 있습니다.</li>
  <li><strong>인도네시아 공화국 법률</strong>에 따라 해석·적용됩니다.</li>
</ol>
<div class="ok">HRI를 통해 Verified Resume에 접근함으로써 기업은 본 정책에 동의한 것으로 간주합니다.</div>
</section>
</main></div></div>`

// ============================================================
// 企業向け — 英語
// ============================================================
export const en_company = styles + `
<div id="hri-vr"><div class="wrap">
<header>
  <h1>📘 Verified Resume Usage Restriction Policy (Corporate Members)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Effective date: <strong>December 13, 2025</strong></div>
</header>
<nav class="card toc"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#vr1">Article 1 — Position of Verified Resume</a></li>
  <li><a href="#vr2">Article 2 — Nature of Information (Reference Only)</a></li>
  <li><a href="#vr3">Article 3 — Permitted Use</a></li>
  <li><a href="#vr4">Article 4 — Prohibitions</a></li>
</ol></div><div><ol start="5">
  <li><a href="#vr5">Article 5 — Responsibility & Risk</a></li>
  <li><a href="#vr6">Article 6 — Foreign Companies & Cross-border Use</a></li>
  <li><a href="#vr7">Article 7 — Monitoring & Enforcement</a></li>
  <li><a href="#vr8">Article 8 — Changes & Governing Law</a></li>
</ol></div></div>
<p class="legal">Related documents: <strong>HRI Terms of Use</strong>, <strong>Privacy Policy</strong>, and other HRI policies.</p>
</nav>
<main>
<section class="card" id="vr1"><h2>Article 1 — Position of Verified Resume</h2>
<p><strong>Verified Resume</strong> is a summary of limited verification process results provided by HRI for reference purposes in the recruitment process.</p>
<p>Verified Resume is <strong>not</strong> a guarantee of absolute accuracy, a recommendation, or a final assessment of an applicant.</p>
</section>
<section class="card" id="vr2"><h2>Article 2 — Nature of Information (Reference Only)</h2>
<ul>
  <li>Verified Resume is provided <strong>for reference purposes only</strong>.</li>
  <li>HRI does not guarantee the accuracy, completeness, or currency of information.</li>
  <li>Recruitment decisions rest entirely with the Company.</li>
</ul>
<div class="warn">Using Verified Resume as the sole basis for recruitment decisions is the Company's own risk.</div>
</section>
<section class="card" id="vr3"><h2>Article 3 — Permitted Use</h2>
<p>Companies are only permitted to use Verified Resume for <strong>internal evaluation purposes during the selection process</strong>.</p>
<ul>
  <li>Use is temporary while the recruitment process is ongoing;</li>
  <li>Use is limited to authorized personnel.</li>
</ul>
</section>
<section class="card" id="vr4"><h2>Article 4 — Prohibitions</h2>
<ul>
  <li>Downloading, printing, storing, or exporting Verified Resume in any format (including PDF) is <strong>prohibited</strong>.</li>
  <li>Sharing Verified Resume with third parties is <strong>prohibited</strong>.</li>
  <li>Using Verified Resume for non-recruitment purposes is <strong>prohibited</strong>.</li>
  <li>Claiming or stating that HRI guarantees applicant quality is <strong>prohibited</strong>.</li>
</ul>
</section>
<section class="card" id="vr5"><h2>Article 5 — Responsibility & Risk</h2>
<p>The Company bears all risks arising from the use of Verified Resume, including misinterpretation or over-reliance.</p>
<div class="note">HRI is not responsible for direct or indirect damages resulting from the use of Verified Resume.</div>
</section>
<section class="card" id="vr6"><h2>Article 6 — Foreign Companies & Cross-border Use</h2>
<p>Foreign companies or cross-border use of Verified Resume is entirely the responsibility of the Company, including legal compliance in relevant jurisdictions.</p>
</section>
<section class="card" id="vr7"><h2>Article 7 — Monitoring & Enforcement</h2>
<p>Access to and use of Verified Resume is recorded and may be audited. HRI reserves the right to take restriction or access termination measures if a violation of this policy occurs.</p>
</section>
<section class="card" id="vr8"><h2>Article 8 — Changes & Governing Law</h2>
<ol>
  <li>This policy may be updated from time to time.</li>
  <li>Governed and interpreted in accordance with the <strong>laws of the Republic of Indonesia</strong>.</li>
</ol>
<div class="ok">By accessing Verified Resume through HRI, the Company agrees to this policy.</div>
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