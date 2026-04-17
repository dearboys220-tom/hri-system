const styles = `<style>
  #hri-iv{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:#0f172a}
  #hri-iv .wrap{max-width:880px;margin:0 auto;padding:24px 16px}
  #hri-iv header{background:#0A1A3A;color:#fff;border-radius:16px;padding:20px 18px;margin-bottom:16px}
  #hri-iv h1{margin:0 0 6px;font-size:1.35rem;line-height:1.25;color:#fff}
  #hri-iv .meta{opacity:.9;font-size:.9rem;color:#fff}
  #hri-iv .card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px;margin:16px 0}
  #hri-iv h2{font-size:1.1rem;margin:0 0 10px;color:#0A1A3A}
  #hri-iv h3{font-size:1rem;margin:14px 0 8px;color:#1e3a8a}
  #hri-iv p{margin:10px 0;line-height:1.8}
  #hri-iv ul,#hri-iv ol{margin:10px 0 10px 20px;line-height:1.8}
  #hri-iv li{margin:6px 0}
  #hri-iv .note{background:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:12px;margin:12px 0}
  #hri-iv .warn{background:#fff7ed;border:1px solid #fdba74;border-radius:12px;padding:12px;margin:12px 0}
  #hri-iv .ok{background:#e8f5e8;border:1px solid #66bb6a;border-radius:12px;padding:12px;margin:12px 0}
  #hri-iv .grid{display:grid;gap:10px}
  #hri-iv .grid.two{grid-template-columns:1fr}
  #hri-iv .legal{font-size:.9rem;opacity:.85}
  @media(min-width:768px){
    #hri-iv .wrap{padding:28px 20px}
    #hri-iv h1{font-size:1.5rem}
    #hri-iv .grid.two{grid-template-columns:1fr 1fr}
  }
</style>`

// ============================================================
// 個人向け — インドネシア語
// ============================================================
export const id_individual = styles + `
<div id="hri-iv"><div class="wrap">
<header>
  <h1>🔍 Kebijakan Persetujuan Investigasi & Verifikasi — Anggota HRI (Individu)</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA — Merek: HRI<br>
  Terakhir diperbarui: <strong>13 Desember 2025</strong><br>
  Berlaku untuk proses verifikasi data pribadi, riwayat kerja, pendidikan, dan informasi terkait.</div>
</header>
<nav class="card toc"><h2>Daftar Isi</h2>
<div class="grid two"><div><ol>
  <li><a href="#iv1">Pasal 1 — Tujuan & Ruang Lingkup</a></li>
  <li><a href="#iv2">Pasal 2 — Objek Investigasi & Verifikasi</a></li>
  <li><a href="#iv3">Pasal 3 — Metode & Sumber Informasi</a></li>
  <li><a href="#iv4">Pasal 4 — Batasan & Ketidakpastian</a></li>
  <li><a href="#iv5">Pasal 5 — Kewajiban & Kerja Sama Anggota</a></li>
</ol></div><div><ol start="6">
  <li><a href="#iv6">Pasal 6 — Dampak terhadap Hasil & Lamaran Kerja</a></li>
  <li><a href="#iv7">Pasal 7 — Penyangkalan Jaminan</a></li>
  <li><a href="#iv8">Pasal 8 — Log, Audit, & Kepatuhan</a></li>
  <li><a href="#iv9">Pasal 9 — Perubahan & Bahasa</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="iv1"><h2>Pasal 1 — Tujuan & Ruang Lingkup</h2>
<p>Kebijakan ini mengatur persetujuan Anggota Individu terhadap pelaksanaan <strong>investigasi dan/atau verifikasi</strong> oleh HRI atas informasi yang Anda berikan dalam rangka penyediaan layanan verifikasi dan <strong>Verified Resume</strong>.</p>
<div class="note"><strong>Tujuan utama:</strong> meningkatkan keandalan informasi, transparansi, dan kepercayaan pihak penerima (mis. perusahaan) tanpa mengubah tanggung jawab hukum pihak-pihak terkait.</div>
</section>
<section class="card" id="iv2"><h2>Pasal 2 — Objek Investigasi & Verifikasi</h2>
<p>Dengan menyetujui kebijakan ini, Anda memberikan persetujuan kepada HRI untuk melakukan verifikasi terhadap:</p>
<ul>
  <li>Identitas dasar dan data akun;</li>
  <li>Riwayat pendidikan (sekolah, universitas, gelar);</li>
  <li>Riwayat pekerjaan (jabatan, periode, pemberi kerja);</li>
  <li>Sertifikasi, lisensi, atau keahlian profesional;</li>
  <li>Informasi pendukung lain yang Anda ajukan untuk diverifikasi.</li>
</ul>
</section>
<section class="card" id="iv3"><h2>Pasal 3 — Metode & Sumber Informasi</h2>
<p>Investigasi dan verifikasi dapat dilakukan melalui metode yang sah dan wajar, antara lain:</p>
<ul>
  <li>Review dokumen yang Anda berikan;</li>
  <li>Pencarian sumber terbuka (open-source) yang tersedia untuk umum;</li>
  <li>Konfirmasi kepada institusi atau pihak terkait secara terbatas;</li>
  <li>Analisis konsistensi data internal.</li>
</ul>
<div class="warn">HRI tidak melakukan pengumpulan data secara ilegal atau melanggar hukum. Ketersediaan dan respons pihak ketiga berada di luar kendali HRI.</div>
</section>
<section class="card" id="iv4"><h2>Pasal 4 — Batasan & Ketidakpastian</h2>
<ul>
  <li>Tidak semua informasi dapat diverifikasi sepenuhnya (mis. riwayat di luar negeri atau institusi yang tidak responsif).</li>
  <li>Status "tidak terverifikasi" atau "tidak dapat dikonfirmasi" <strong>bukan</strong> berarti informasi palsu.</li>
  <li>Hasil verifikasi mencerminkan kondisi dan bukti yang tersedia pada saat pemeriksaan.</li>
</ul>
<div class="note">Hasil dapat berubah jika informasi tambahan atau klarifikasi diberikan di kemudian hari.</div>
</section>
<section class="card" id="iv5"><h2>Pasal 5 — Kewajiban & Kerja Sama Anggota</h2>
<p>Untuk kelancaran proses, Anggota Individu berkewajiban untuk:</p>
<ul>
  <li>Memberikan informasi yang benar, akurat, dan terkini;</li>
  <li>Menyediakan dokumen pendukung yang relevan atas permintaan wajar;</li>
  <li>Tidak menghalangi atau memanipulasi proses verifikasi.</li>
</ul>
</section>
<section class="card" id="iv6"><h2>Pasal 6 — Dampak terhadap Hasil & Lamaran Kerja</h2>
<p>Anda memahami dan menyetujui bahwa hasil investigasi/verifikasi dapat memengaruhi penilaian pihak penerima (mis. perusahaan) dalam konteks rekrutmen.</p>
<div class="warn"><strong>Catatan penting:</strong> keputusan rekrutmen sepenuhnya berada pada pihak perusahaan. HRI tidak menjamin diterima atau ditolaknya lamaran kerja.</div>
</section>
<section class="card" id="iv7"><h2>Pasal 7 — Penyangkalan Jaminan</h2>
<ul>
  <li>HRI tidak menjamin bahwa seluruh informasi akan selalu dapat diverifikasi.</li>
  <li>HRI tidak menjamin hasil tertentu (mis. kelulusan seleksi atau penerimaan kerja).</li>
  <li>HRI tidak bertanggung jawab atas keputusan pihak ketiga yang didasarkan pada hasil verifikasi.</li>
</ul>
</section>
<section class="card" id="iv8"><h2>Pasal 8 — Log, Audit, & Kepatuhan</h2>
<ol>
  <li>Aktivitas verifikasi dapat dicatat untuk tujuan audit, keamanan, dan kepatuhan hukum.</li>
  <li>Pencatatan dilakukan secara wajar dan dilindungi sesuai Kebijakan Privasi.</li>
  <li>HRI berhak menolak atau menghentikan proses jika ditemukan indikasi penyalahgunaan.</li>
</ol>
</section>
<section class="card" id="iv9"><h2>Pasal 9 — Perubahan & Bahasa</h2>
<ol>
  <li>Kebijakan ini dapat diperbarui dari waktu ke waktu. Perubahan material akan diumumkan melalui situs.</li>
  <li>Bahasa Indonesia adalah versi resmi. Terjemahan bahasa lain bersifat referensi.</li>
</ol>
<div class="ok">Dengan menyetujui kebijakan ini, Anda memberikan persetujuan atas pelaksanaan investigasi dan verifikasi oleh HRI.</div>
</section>
<section class="card"><h2>Kontak</h2>
<p class="legal">Dukungan: <a href="mailto:support@hri-check.com">support@hri-check.com</a></p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 日本語
// ============================================================
export const ja_individual = styles + `
<div id="hri-iv"><div class="wrap">
<header>
  <h1>🔍 調査・検証同意ポリシー — HRI個人会員</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA（ブランド：HRI）<br>
  最終更新日：<strong>2025年12月13日</strong><br>
  個人データ・職歴・学歴・関連情報の検証プロセスに適用されます。</div>
</header>
<nav class="card toc"><h2>目次</h2>
<div class="grid two"><div><ol>
  <li><a href="#iv1">第1条 — 目的・適用範囲</a></li>
  <li><a href="#iv2">第2条 — 調査・検証の対象</a></li>
  <li><a href="#iv3">第3条 — 方法・情報源</a></li>
  <li><a href="#iv4">第4条 — 限界・不確実性</a></li>
  <li><a href="#iv5">第5条 — 会員の義務・協力</a></li>
</ol></div><div><ol start="6">
  <li><a href="#iv6">第6条 — 結果・求人応募への影響</a></li>
  <li><a href="#iv7">第7条 — 保証の免責</a></li>
  <li><a href="#iv8">第8条 — ログ・監査・遵守</a></li>
  <li><a href="#iv9">第9条 — 変更・言語</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="iv1"><h2>第1条 — 目的・適用範囲</h2>
<p>本ポリシーは、HRIによる検証サービスおよび<strong>Verified Resume</strong>の提供に関連して、お客様が提供した情報に対してHRIが実施する<strong>調査および/または検証</strong>に対する個人会員の同意を規定します。</p>
<div class="note"><strong>主な目的：</strong>関係する当事者の法的責任を変更することなく、情報の信頼性・透明性・受信者（企業等）からの信頼を向上させることです。</div>
</section>
<section class="card" id="iv2"><h2>第2条 — 調査・検証の対象</h2>
<p>本ポリシーに同意することにより、以下に対する検証をHRIに許可します：</p>
<ul>
  <li>基本的な身元情報とアカウントデータ；</li>
  <li>学歴（学校・大学・学位）；</li>
  <li>職歴（職位・期間・雇用主）；</li>
  <li>資格・免許・専門スキル；</li>
  <li>検証のために申請したその他の補足情報。</li>
</ul>
</section>
<section class="card" id="iv3"><h2>第3条 — 方法・情報源</h2>
<p>調査・検証は、以下を含む適法かつ合理的な方法で実施されます：</p>
<ul>
  <li>お客様が提供した文書のレビュー；</li>
  <li>一般公開されているオープンソースの検索；</li>
  <li>関連機関または関係者への限定的な確認；</li>
  <li>内部データの整合性分析。</li>
</ul>
<div class="warn">HRIは違法または法律に違反する方法でデータを収集しません。第三者の対応可否はHRIの管理外です。</div>
</section>
<section class="card" id="iv4"><h2>第4条 — 限界・不確実性</h2>
<ul>
  <li>すべての情報が完全に検証できるわけではありません（海外の経歴や応答のない機関など）。</li>
  <li>「未検証」または「確認不可」のステータスは、情報が虚偽であることを意味<strong>しません</strong>。</li>
  <li>検証結果は検査時点で利用可能な状況と証拠を反映したものです。</li>
</ul>
<div class="note">後日追加情報または説明が提供された場合、結果が変わる可能性があります。</div>
</section>
<section class="card" id="iv5"><h2>第5条 — 会員の義務・協力</h2>
<p>プロセスを円滑に進めるために、個人会員は以下の義務を負います：</p>
<ul>
  <li>正確・最新・真実の情報を提供すること；</li>
  <li>合理的な要求に応じて関連補足文書を提供すること；</li>
  <li>検証プロセスを妨害または操作しないこと。</li>
</ul>
</section>
<section class="card" id="iv6"><h2>第6条 — 結果・求人応募への影響</h2>
<p>採用コンテキストにおいて、調査/検証の結果が受信者（企業等）の評価に影響を与える可能性があることを理解し同意します。</p>
<div class="warn"><strong>重要な注意：</strong>採用の意思決定は完全に企業側にあります。HRIは求人応募の採用・不採用を保証しません。</div>
</section>
<section class="card" id="iv7"><h2>第7条 — 保証の免責</h2>
<ul>
  <li>HRIはすべての情報が常に検証可能であることを保証しません。</li>
  <li>HRIは特定の結果（選考通過や採用等）を保証しません。</li>
  <li>HRIは検証結果に基づく第三者の決定について責任を負いません。</li>
</ul>
</section>
<section class="card" id="iv8"><h2>第8条 — ログ・監査・遵守</h2>
<ol>
  <li>検証活動は監査・セキュリティ・法的遵守の目的で記録される場合があります。</li>
  <li>記録は合理的な方法で実施され、プライバシーポリシーに従い保護されます。</li>
  <li>HRIは不正利用の兆候が発見された場合、プロセスを拒否または停止する権利を有します。</li>
</ol>
</section>
<section class="card" id="iv9"><h2>第9条 — 変更・言語</h2>
<ol>
  <li>本ポリシーは随時更新される場合があります。重要な変更はサイトを通じて告知します。</li>
  <li>インドネシア語版が正式版です。他言語の翻訳は参考情報です。</li>
</ol>
<div class="ok">本ポリシーに同意することにより、HRIによる調査・検証の実施に対して同意を表明します。</div>
</section>
<section class="card"><h2>連絡先</h2>
<p class="legal">サポート：<a href="mailto:support@hri-check.com">support@hri-check.com</a></p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 韓国語
// ============================================================
export const ko_individual = styles + `
<div id="hri-iv"><div class="wrap">
<header>
  <h1>🔍 조사·검증 동의 정책 — HRI 개인 회원</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (브랜드: HRI)<br>
  최종 업데이트: <strong>2025년 12월 13일</strong><br>
  개인 데이터, 경력, 학력 및 관련 정보의 검증 프로세스에 적용됩니다.</div>
</header>
<nav class="card toc"><h2>목차</h2>
<div class="grid two"><div><ol>
  <li><a href="#iv1">제1조 — 목적 및 적용 범위</a></li>
  <li><a href="#iv2">제2조 — 조사·검증 대상</a></li>
  <li><a href="#iv3">제3조 — 방법 및 정보 출처</a></li>
  <li><a href="#iv4">제4조 — 한계 및 불확실성</a></li>
  <li><a href="#iv5">제5조 — 회원의 의무 및 협력</a></li>
</ol></div><div><ol start="6">
  <li><a href="#iv6">제6조 — 결과 및 입사 지원에 대한 영향</a></li>
  <li><a href="#iv7">제7조 — 보증 부인</a></li>
  <li><a href="#iv8">제8조 — 로그, 감사 및 준수</a></li>
  <li><a href="#iv9">제9조 — 변경 및 언어</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="iv1"><h2>제1조 — 목적 및 적용 범위</h2>
<p>본 정책은 인증 서비스 및 <strong>Verified Resume</strong> 제공과 관련하여 귀하가 제공한 정보에 대해 HRI가 수행하는 <strong>조사 및/또는 검증</strong>에 대한 개인 회원의 동의를 규정합니다.</p>
<div class="note"><strong>주요 목적:</strong> 관련 당사자의 법적 책임을 변경하지 않으면서 정보의 신뢰성, 투명성 및 수신자(기업 등)로부터의 신뢰를 향상시키는 것입니다.</div>
</section>
<section class="card" id="iv2"><h2>제2조 — 조사·검증 대상</h2>
<p>본 정책에 동의함으로써 다음에 대한 검증을 HRI에 허가합니다:</p>
<ul>
  <li>기본 신원 정보 및 계정 데이터;</li>
  <li>학력(학교, 대학, 학위);</li>
  <li>경력(직위, 기간, 고용주);</li>
  <li>자격증, 면허 또는 전문 기술;</li>
  <li>검증을 위해 신청한 기타 지원 정보.</li>
</ul>
</section>
<section class="card" id="iv3"><h2>제3조 — 방법 및 정보 출처</h2>
<p>조사 및 검증은 다음을 포함한 합법적이고 합리적인 방법으로 수행될 수 있습니다:</p>
<ul>
  <li>귀하가 제공한 문서 검토;</li>
  <li>일반에 공개된 오픈 소스 검색;</li>
  <li>관련 기관 또는 당사자에 대한 제한적 확인;</li>
  <li>내부 데이터 일관성 분석.</li>
</ul>
<div class="warn">HRI는 불법적이거나 법률에 위반되는 방법으로 데이터를 수집하지 않습니다. 제3자의 가용성 및 응답은 HRI의 통제 밖입니다.</div>
</section>
<section class="card" id="iv4"><h2>제4조 — 한계 및 불확실성</h2>
<ul>
  <li>모든 정보를 완전히 검증할 수 있는 것은 아닙니다(예: 해외 이력 또는 응답하지 않는 기관).</li>
  <li>"미검증" 또는 "확인 불가" 상태는 정보가 허위임을 의미하지 <strong>않습니다</strong>.</li>
  <li>검증 결과는 검사 당시 이용 가능한 상황과 증거를 반영합니다.</li>
</ul>
<div class="note">이후에 추가 정보나 설명이 제공되면 결과가 변경될 수 있습니다.</div>
</section>
<section class="card" id="iv5"><h2>제5조 — 회원의 의무 및 협력</h2>
<p>프로세스를 원활하게 진행하기 위해 개인 회원은 다음 의무를 집니다:</p>
<ul>
  <li>정확하고 최신의 진실된 정보를 제공할 것;</li>
  <li>합리적인 요청에 따라 관련 지원 문서를 제공할 것;</li>
  <li>검증 프로세스를 방해하거나 조작하지 않을 것.</li>
</ul>
</section>
<section class="card" id="iv6"><h2>제6조 — 결과 및 입사 지원에 대한 영향</h2>
<p>채용 맥락에서 조사/검증 결과가 수신자(기업 등)의 평가에 영향을 미칠 수 있음을 이해하고 동의합니다.</p>
<div class="warn"><strong>중요 사항:</strong> 채용 결정은 전적으로 기업 측에 있습니다. HRI는 입사 지원의 합격 또는 불합격을 보장하지 않습니다.</div>
</section>
<section class="card" id="iv7"><h2>제7조 — 보증 부인</h2>
<ul>
  <li>HRI는 모든 정보가 항상 검증 가능하다고 보장하지 않습니다.</li>
  <li>HRI는 특정 결과(선발 통과나 채용 등)를 보장하지 않습니다.</li>
  <li>HRI는 검증 결과에 기반한 제3자의 결정에 대해 책임을 지지 않습니다.</li>
</ul>
</section>
<section class="card" id="iv8"><h2>제8조 — 로그, 감사 및 준수</h2>
<ol>
  <li>검증 활동은 감사, 보안 및 법적 준수 목적으로 기록될 수 있습니다.</li>
  <li>기록은 합리적인 방법으로 이루어지며 개인정보처리방침에 따라 보호됩니다.</li>
  <li>HRI는 오용 징후가 발견될 경우 프로세스를 거부하거나 중단할 권리를 가집니다.</li>
</ol>
</section>
<section class="card" id="iv9"><h2>제9조 — 변경 및 언어</h2>
<ol>
  <li>본 정책은 수시로 업데이트될 수 있습니다. 중요한 변경 사항은 사이트를 통해 공지합니다.</li>
  <li>인도네시아어 버전이 공식 버전입니다. 다른 언어 번역본은 참고용입니다.</li>
</ol>
<div class="ok">본 정책에 동의함으로써 HRI의 조사 및 검증 수행에 대한 동의를 표명합니다.</div>
</section>
<section class="card"><h2>연락처</h2>
<p class="legal">지원: <a href="mailto:support@hri-check.com">support@hri-check.com</a></p>
</section>
</main></div></div>`

// ============================================================
// 個人向け — 英語
// ============================================================
export const en_individual = styles + `
<div id="hri-iv"><div class="wrap">
<header>
  <h1>🔍 Investigation & Verification Consent Policy — HRI Individual Members</h1>
  <div class="meta">PT. NIKI KINDAICHI THREE INDONESIA (Brand: HRI)<br>
  Last updated: <strong>December 13, 2025</strong><br>
  Applies to the verification process of personal data, work history, education, and related information.</div>
</header>
<nav class="card toc"><h2>Table of Contents</h2>
<div class="grid two"><div><ol>
  <li><a href="#iv1">Article 1 — Purpose & Scope</a></li>
  <li><a href="#iv2">Article 2 — Objects of Investigation & Verification</a></li>
  <li><a href="#iv3">Article 3 — Methods & Information Sources</a></li>
  <li><a href="#iv4">Article 4 — Limitations & Uncertainty</a></li>
  <li><a href="#iv5">Article 5 — Member Obligations & Cooperation</a></li>
</ol></div><div><ol start="6">
  <li><a href="#iv6">Article 6 — Impact on Results & Job Applications</a></li>
  <li><a href="#iv7">Article 7 — Disclaimer of Warranties</a></li>
  <li><a href="#iv8">Article 8 — Logs, Audit & Compliance</a></li>
  <li><a href="#iv9">Article 9 — Changes & Language</a></li>
</ol></div></div></nav>
<main>
<section class="card" id="iv1"><h2>Article 1 — Purpose & Scope</h2>
<p>This policy governs Individual Member consent to the conduct of <strong>investigation and/or verification</strong> by HRI of information you provide in connection with the provision of verification services and <strong>Verified Resume</strong>.</p>
<div class="note"><strong>Primary purpose:</strong> to improve the reliability of information, transparency, and trust of recipients (e.g., companies) without altering the legal responsibilities of the relevant parties.</div>
</section>
<section class="card" id="iv2"><h2>Article 2 — Objects of Investigation & Verification</h2>
<p>By agreeing to this policy, you authorize HRI to verify:</p>
<ul>
  <li>Basic identity and account data;</li>
  <li>Educational history (schools, universities, degrees);</li>
  <li>Work history (position, period, employer);</li>
  <li>Certifications, licenses, or professional skills;</li>
  <li>Other supporting information you submit for verification.</li>
</ul>
</section>
<section class="card" id="iv3"><h2>Article 3 — Methods & Information Sources</h2>
<p>Investigation and verification may be conducted through lawful and reasonable methods, including:</p>
<ul>
  <li>Review of documents you provide;</li>
  <li>Searches of publicly available open-source information;</li>
  <li>Limited confirmation with relevant institutions or parties;</li>
  <li>Internal data consistency analysis.</li>
</ul>
<div class="warn">HRI does not collect data illegally or in violation of the law. The availability and response of third parties is outside HRI's control.</div>
</section>
<section class="card" id="iv4"><h2>Article 4 — Limitations & Uncertainty</h2>
<ul>
  <li>Not all information can be fully verified (e.g., history abroad or unresponsive institutions).</li>
  <li>A status of "unverified" or "unable to confirm" does <strong>not</strong> mean the information is false.</li>
  <li>Verification results reflect the conditions and evidence available at the time of examination.</li>
</ul>
<div class="note">Results may change if additional information or clarification is provided at a later date.</div>
</section>
<section class="card" id="iv5"><h2>Article 5 — Member Obligations & Cooperation</h2>
<p>To facilitate the process, Individual Members are obligated to:</p>
<ul>
  <li>Provide true, accurate, and current information;</li>
  <li>Provide relevant supporting documents upon reasonable request;</li>
  <li>Not obstruct or manipulate the verification process.</li>
</ul>
</section>
<section class="card" id="iv6"><h2>Article 6 — Impact on Results & Job Applications</h2>
<p>You understand and agree that the results of investigation/verification may influence the assessment of recipients (e.g., companies) in the recruitment context.</p>
<div class="warn"><strong>Important note:</strong> recruitment decisions rest entirely with the company. HRI does not guarantee the acceptance or rejection of job applications.</div>
</section>
<section class="card" id="iv7"><h2>Article 7 — Disclaimer of Warranties</h2>
<ul>
  <li>HRI does not guarantee that all information will always be verifiable.</li>
  <li>HRI does not guarantee specific outcomes (e.g., passing selection or receiving employment).</li>
  <li>HRI is not responsible for decisions made by third parties based on verification results.</li>
</ul>
</section>
<section class="card" id="iv8"><h2>Article 8 — Logs, Audit & Compliance</h2>
<ol>
  <li>Verification activities may be recorded for audit, security, and legal compliance purposes.</li>
  <li>Recording is carried out reasonably and protected in accordance with the Privacy Policy.</li>
  <li>HRI reserves the right to refuse or discontinue the process if indications of misuse are found.</li>
</ol>
</section>
<section class="card" id="iv9"><h2>Article 9 — Changes & Language</h2>
<ol>
  <li>This policy may be updated from time to time. Material changes will be announced through the site.</li>
  <li>The Indonesian version is the official version. Translations in other languages are for reference only.</li>
</ol>
<div class="ok">By agreeing to this policy, you provide consent to HRI's conduct of investigation and verification.</div>
</section>
<section class="card"><h2>Contact</h2>
<p class="legal">Support: <a href="mailto:support@hri-check.com">support@hri-check.com</a></p>
</section>
</main></div></div>`

// ============================================================
// 企業向けは存在しない（個人専用）
// ============================================================
export const id_company = id_individual
export const ja_company = ja_individual
export const ko_company = ko_individual
export const en_company = en_individual

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