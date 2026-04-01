<script setup>
import Button from '@/Components/Admin/Components/Button.vue';
import Card from '@/Components/Admin/Components/Card.vue';
import Divider from '@/Components/Admin/Components/Divider.vue';
import SectionHeader from '@/Components/Admin/Components/SectionHeader.vue';
import ImageViewer from '@/Components/Admin/Components/ImageViewer.vue';
import AdminLayout from '@/Components/Admin/Layout/AdminLayout.vue';
import { MagnifyingGlassIcon, UserCircleIcon } from '@heroicons/vue/24/outline';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: (h, page) =>
        h(AdminLayout, {
            title: 'Persetujuan Akhir Sertifikasi',
            subtitle: 'Keputusan akhir: Setujui / Tolak / Kembalikan'
        }, () => page)
});

const props = defineProps({
    cases:      { type: Array,  default: () => [] },
    detail:     { type: Object, default: null },
    selectedId: { type: Number, default: null },
});

// ---- 左パネル ----
const search = ref('');
const filteredCases = computed(() =>
    props.cases.filter(c =>
        c.name?.toLowerCase().includes(search.value.toLowerCase()) ||
        c.member_id?.toLowerCase().includes(search.value.toLowerCase())
    )
);

function selectCase(id) {
    router.get(route('admin.admin.evaluate'), { id }, { preserveScroll: true });
}

const adminNotes   = ref('');
const returnReason = ref('');

watch(() => props.detail, (d) => {
    adminNotes.value   = d?.admin_notes ?? '';
    returnReason.value = '';
}, { immediate: true });

// ---- 画像ビューアー ----
const showImage    = ref(false);
const viewImageSrc = ref('');
function openImage(src) {
    viewImageSrc.value = src;
    showImage.value    = true;
}

// ---- スコア表示ヘルパー ----
function scoreColor(score) {
    if (score >= 80) return 'text-green-600';
    if (score >= 60) return 'text-yellow-600';
    return 'text-red-600';
}
function scoreBg(score) {
    if (score >= 80) return 'bg-green-50 border-green-200';
    if (score >= 60) return 'bg-yellow-50 border-yellow-200';
    return 'bg-red-50 border-red-200';
}

function bandColor(band) {
    const map = {
        'HIGH':     'bg-green-100 text-green-800',
        'MODERATE': 'bg-blue-100 text-blue-800',
        'LIMITED':  'bg-yellow-100 text-yellow-800',
        'LOW':      'bg-red-100 text-red-800',
    };
    return map[band] ?? 'bg-slate-100 text-slate-600';
}

function judgmentColor(j) {
    if (j === 'STRONGLY_RECOMMENDED_FOR_VERIFIED_VIEW') return 'bg-green-100 text-green-800';
    if (j === 'VERIFIED_WITH_RESERVATIONS')             return 'bg-blue-100 text-blue-800';
    if (j === 'LIMITED_RELIABILITY')                    return 'bg-yellow-100 text-yellow-800';
    if (j === 'HUMAN_REVIEW_STRONGLY_RECOMMENDED')      return 'bg-orange-100 text-orange-800';
    if (j === 'RETURN_REQUIRED')                        return 'bg-red-100 text-red-800';
    return 'bg-slate-100 text-slate-600';
}

function judgmentLabel(j) {
    const map = {
        'STRONGLY_RECOMMENDED_FOR_VERIFIED_VIEW': '✅ Sangat Direkomendasikan',
        'VERIFIED_WITH_RESERVATIONS':             '🔵 Terverifikasi dengan Reservasi',
        'LIMITED_RELIABILITY':                    '⚠️ Keandalan Terbatas',
        'HUMAN_REVIEW_STRONGLY_RECOMMENDED':      '👤 Perlu Tinjauan Manusia',
        'RETURN_REQUIRED':                        '🔄 Perlu Dikembalikan',
    };
    return map[j] ?? j ?? '-';
}

function validityBadge(v) {
    if (!v)              return { text: '—',           cls: 'bg-slate-100 text-slate-400' };
    if (v === 'VALID')   return { text: '✅ VALID',    cls: 'bg-green-100 text-green-700' };
    if (v === 'INVALID') return { text: '❌ INVALID',  cls: 'bg-red-100 text-red-700' };
    if (v === 'UNVERIFIED') return { text: '⚠️ UNVERIFIED', cls: 'bg-yellow-100 text-yellow-700' };
    return { text: v, cls: 'bg-slate-100 text-slate-500' };
}

function verificationStatusBadge(s) {
    const map = {
        'VERIFIED':           { text: '✅ Verified',          cls: 'bg-green-100 text-green-700' },
        'PARTIALLY_VERIFIED': { text: '🔵 Partially Verified', cls: 'bg-blue-100 text-blue-700' },
        'UNVERIFIED':         { text: '⚠️ Unverified',        cls: 'bg-yellow-100 text-yellow-700' },
        'CONTRADICTED':       { text: '❌ Contradicted',      cls: 'bg-red-100 text-red-700' },
    };
    return map[s] ?? { text: s ?? '-', cls: 'bg-slate-100 text-slate-500' };
}

// ---- アクション ----
const processing = ref(false);

function doApprove() {
    if (!props.detail) return;
    if (!confirm('Setujui sertifikasi ini? Tindakan ini tidak dapat dibatalkan.')) return;
    processing.value = true;
    router.post(
        route('admin.admin.approve', props.detail.id),
        { admin_notes: adminNotes.value },
        { onFinish: () => { processing.value = false; } }
    );
}

function doConditionalApprove() {
    if (!props.detail) return;
    if (!adminNotes.value.trim()) {
        alert('Isi kondisi persetujuan di kolom Catatan Admin terlebih dahulu.');
        return;
    }
    if (!confirm('Setujui dengan kondisi?')) return;
    processing.value = true;
    router.post(
        route('admin.admin.conditionalApprove', props.detail.id),
        { admin_notes: adminNotes.value },
        { onFinish: () => { processing.value = false; } }
    );
}

function doReject() {
    if (!props.detail) return;
    if (!adminNotes.value.trim()) {
        alert('Isi alasan penolakan di kolom Catatan Admin terlebih dahulu.');
        return;
    }
    if (!confirm('Tolak sertifikasi ini?')) return;
    processing.value = true;
    router.post(
        route('admin.admin.reject', props.detail.id),
        { admin_notes: adminNotes.value },
        { onFinish: () => { processing.value = false; } }
    );
}

function doReturn() {
    if (!props.detail) return;
    if (!returnReason.value.trim()) {
        alert('Isi alasan pengembalian terlebih dahulu.');
        return;
    }
    if (!confirm('Kembalikan ke Tim Investigator?')) return;
    processing.value = true;
    router.post(
        route('admin.admin.return', props.detail.id),
        { return_reason: returnReason.value },
        { onFinish: () => { processing.value = false; } }
    );
}

function doEscalate() {
    if (!props.detail) return;
    if (!adminNotes.value.trim()) {
        alert('Isi alasan eskalasi di kolom Catatan Admin terlebih dahulu.');
        return;
    }
    if (!confirm('Eskalasi ke pemeriksaan manusia?')) return;
    processing.value = true;
    router.post(
        route('admin.admin.escalate', props.detail.id),
        { admin_notes: adminNotes.value },
        { onFinish: () => { processing.value = false; } }
    );
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-10 gap-6 items-start">

        <!-- ===== 左パネル ===== -->
        <Card class="md:col-span-3 self-start md:sticky md:top-28 h-fit">
            <SectionHeader title="Antrian Persetujuan" subtitle="Menunggu keputusan akhir" />

            <div class="flex gap-2 mb-4 mt-4">
                <div class="relative flex-1">
                    <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm"
                    />
                </div>
                <Button variant="secondary" size="sm" @click="search = ''">Reset</Button>
            </div>

            <Divider />

            <div class="space-y-3 mt-4 max-h-[400px] overflow-y-auto pr-1">
                <div v-if="filteredCases.length === 0" class="text-center py-8 text-slate-400 text-sm">
                    Tidak ada kasus yang menunggu
                </div>
                <div
                    v-for="c in filteredCases"
                    :key="c.id"
                    @click="selectCase(c.id)"
                    class="p-4 rounded-xl border cursor-pointer transition"
                    :class="c.id === selectedId
                        ? 'border-admin-primary-600 bg-admin-primary-50'
                        : 'border-slate-200 hover:border-admin-primary-400 hover:bg-admin-primary-50'"
                >
                    <div class="flex items-center gap-2">
                        <UserCircleIcon class="w-5 h-5 text-admin-primary-600 shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ c.name || c.member_id }}</p>
                            <p v-if="c.name" class="text-xs text-admin-primary-600 font-mono">{{ c.member_id }}</p>
                            <p class="text-xs text-slate-500">{{ c.submitted_at }}</p>
                            <!-- エスカレート表示 -->
                            <span v-if="c.survey_status === 'escalated_to_human'"
                                class="inline-block mt-1 text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">
                                👤 Eskalasi
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Card>

        <!-- ===== 右パネル ===== -->
        <Card class="md:col-span-7">

            <div v-if="!detail" class="py-20 text-center text-slate-400">
                <p class="text-lg">👈 Pilih kasus dari daftar</p>
                <p class="text-sm mt-2">Belum ada kasus yang dipilih</p>
            </div>

            <template v-else>

                <!-- ===== エスカレートバナー ===== -->
                <div v-if="detail.survey_status === 'escalated_to_human'"
                    class="mb-6 p-4 bg-orange-50 border border-orange-300 rounded-xl">
                    <p class="font-semibold text-orange-800">👤 Kasus Dieskalasi untuk Pemeriksaan Manusia</p>
                    <p class="text-sm text-orange-700 mt-1">
                        Kasus ini memerlukan perhatian khusus. Tinjau dengan seksama sebelum mengambil keputusan.
                    </p>
                </div>

                <!-- ===== v2.4 AI スコアサマリー ===== -->
                <div :class="['border rounded-2xl p-6 mb-6', scoreBg(detail.base_score ?? 0)]">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Anggota</p>
                            <p class="text-lg font-bold text-slate-800">{{ detail.profile?.full_name }}</p>
                            <p class="text-sm font-mono text-admin-primary-600">{{ detail.profile?.member_id }}</p>
                            <p v-if="detail.ai_review_completed_at" class="text-xs text-slate-400 mt-1">
                                AI Review: {{ detail.ai_review_completed_at }}
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-slate-500 mb-1">Base Score</p>
                            <p :class="['text-5xl font-extrabold', scoreColor(detail.base_score ?? 0)]">
                                {{ detail.base_score ?? '-' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">/ 100</p>
                        </div>
                    </div>

                    <!-- v2.4 指標グリッド -->
                    <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-white/80 rounded-xl p-3 text-center">
                            <p class="text-xs text-slate-500">Truthfulness</p>
                            <p class="text-xl font-bold text-slate-700">{{ detail.truthfulness_percent ?? '-' }}%</p>
                        </div>
                        <div class="bg-white/80 rounded-xl p-3 text-center">
                            <p class="text-xs text-slate-500">Consistency</p>
                            <p class="text-xl font-bold text-slate-700">{{ detail.consistency_percent ?? '-' }}%</p>
                        </div>
                        <div class="bg-white/80 rounded-xl p-3 text-center">
                            <p class="text-xs text-slate-500">HRI Suitability</p>
                            <p class="text-xl font-bold text-slate-700">{{ detail.hri_suitability_score ?? '-' }}</p>
                        </div>
                        <div class="bg-white/80 rounded-xl p-3 text-center">
                            <p class="text-xs text-slate-500">Work Ability</p>
                            <p class="text-xl font-bold text-slate-700">{{ detail.work_ability_score ?? '-' }}</p>
                            <span v-if="detail.work_ability_band"
                                :class="['text-xs px-2 py-0.5 rounded-full font-semibold mt-1 inline-block', bandColor(detail.work_ability_band)]">
                                {{ detail.work_ability_band }}
                            </span>
                        </div>
                    </div>

                    <!-- Claude Overall Judgment -->
                    <div class="mt-4 p-3 bg-white/80 rounded-xl">
                        <p class="text-xs text-slate-500 mb-1">Claude Overall Judgment</p>
                        <span v-if="detail.claude_overall_judgment"
                            :class="['text-sm font-semibold px-3 py-1 rounded-full', judgmentColor(detail.claude_overall_judgment)]">
                            {{ judgmentLabel(detail.claude_overall_judgment) }}
                        </span>
                        <p v-if="detail.claude_overall_reason" class="text-xs text-slate-600 mt-2">
                            {{ detail.claude_overall_reason }}
                        </p>
                    </div>

                    <!-- Enterprise View Summary -->
                    <div v-if="detail.enterprise_view_summary" class="mt-3 p-3 bg-blue-50/80 rounded-xl">
                        <p class="text-xs text-blue-600 font-semibold mb-1">📊 Ringkasan untuk Perusahaan</p>
                        <p class="text-sm text-slate-700">{{ detail.enterprise_view_summary }}</p>
                    </div>
                </div>

                <!-- ===== v2.4 カテゴリ別スコア詳細 ===== -->
                <div v-if="detail.score_items?.length" class="mb-6">
                    <SectionHeader title="Skor per Kategori (AI v2.4)" subtitle="Sistem penambahan poin — 100 poin total" />
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div v-for="item in detail.score_items" :key="item.category"
                            class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                            <p class="text-xs text-slate-500 mb-1">{{ item.label }}</p>
                            <div class="flex items-end gap-1">
                                <span class="text-2xl font-bold text-slate-800">{{ item.actual_score }}</span>
                                <span class="text-sm text-slate-400 mb-1">/ {{ item.max_score }}</span>
                            </div>
                            <!-- プログレスバー -->
                            <div class="mt-2 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all"
                                    :style="{ width: item.max_score > 0 ? (item.actual_score / item.max_score * 100) + '%' : '0%' }"
                                    :class="item.actual_score / item.max_score >= 0.8 ? 'bg-green-500' : item.actual_score / item.max_score >= 0.5 ? 'bg-yellow-500' : 'bg-red-500'"
                                ></div>
                            </div>
                            <span :class="['text-xs mt-1 inline-block', verificationStatusBadge(item.verification_status).cls, 'px-1.5 py-0.5 rounded']">
                                {{ verificationStatusBadge(item.verification_status).text }}
                            </span>
                            <p v-if="item.score_reason" class="text-xs text-slate-500 mt-2 leading-relaxed">
                                {{ item.score_reason }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Risk Flags & Conditions -->
                <div v-if="detail.case_review?.risk_flags?.length || detail.case_review?.conditions?.length" class="mb-6 space-y-3">
                    <div v-if="detail.case_review?.risk_flags?.length"
                        class="p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm font-semibold text-red-800 mb-2">⚠️ Risk Flags</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li v-for="(flag, i) in detail.case_review.risk_flags" :key="i"
                                class="text-sm text-red-700">{{ flag }}</li>
                        </ul>
                    </div>
                    <div v-if="detail.case_review?.conditions?.length"
                        class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <p class="text-sm font-semibold text-amber-800 mb-2">📋 Kondisi Persetujuan</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li v-for="(cond, i) in detail.case_review.conditions" :key="i"
                                class="text-sm text-amber-700">{{ cond }}</li>
                        </ul>
                    </div>
                </div>

                <Divider class="my-6" />

                <!-- ===== 写真 & KTP ===== -->
                <SectionHeader title="Identitas Anggota" subtitle="Foto & dokumen identitas" />
                <div class="flex gap-6 mt-4 mb-6 flex-wrap">
                    <div>
                        <p class="text-xs text-slate-500 mb-2">Foto Profil</p>
                        <div class="w-28 h-28 rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center cursor-pointer"
                            @click="detail.profile?.profile_photo && openImage('/storage/' + detail.profile.profile_photo)">
                            <img v-if="detail.profile?.profile_photo"
                                :src="'/storage/' + detail.profile.profile_photo"
                                class="w-full h-full object-cover" />
                            <span v-else class="text-3xl">👤</span>
                        </div>
                    </div>
                    <div v-if="detail.profile?.ktp_card">
                        <p class="text-xs text-slate-500 mb-2">Foto KTP</p>
                        <img :src="'/storage/' + detail.profile.ktp_card"
                            class="h-28 rounded-xl border border-slate-200 cursor-pointer object-cover"
                            @click="openImage('/storage/' + detail.profile.ktp_card)" />
                    </div>
                </div>

                <Divider class="my-6" />

                <!-- ===== 調査結果テーブル（4セクション）===== -->
                <template v-for="section in [
                    { title: 'Data Pribadi',       subtitle: 'Hasil investigasi', items: detail.inv_basic },
                    { title: 'Riwayat Pendidikan', subtitle: 'Hasil investigasi', items: detail.inv_edu   },
                    { title: 'Pengalaman Kerja',   subtitle: 'Hasil investigasi', items: detail.inv_work  },
                    { title: 'Sertifikat',         subtitle: 'Hasil investigasi', items: detail.inv_cert  },
                ]" :key="section.title">

                    <SectionHeader :title="section.title" :subtitle="section.subtitle" />

                    <div class="mt-4 mb-8 overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-600">
                                    <th class="text-left px-4 py-3 rounded-tl-xl font-semibold">Item</th>
                                    <th class="text-left px-4 py-3 font-semibold">Nilai</th>
                                    <th class="text-center px-4 py-3 rounded-tr-xl font-semibold w-40">Investigasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-if="section.items?.length">
                                    <tr v-for="item in section.items" :key="item.item_name"
                                        :class="[
                                            'border-b border-slate-100 transition',
                                            item.validity === 'INVALID'     ? 'bg-red-50 hover:bg-red-100' :
                                            item.validity === 'UNVERIFIED'  ? 'bg-yellow-50 hover:bg-yellow-100' :
                                            'hover:bg-slate-50'
                                        ]">
                                        <td class="px-4 py-3 text-slate-700 font-medium">{{ item.label }}</td>
                                        <td class="px-4 py-3 text-slate-600 max-w-[200px] truncate" :title="item.value">
                                            {{ item.value }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex flex-col items-center gap-1">
                                                <span :class="['text-xs px-2 py-1 rounded-full font-medium', validityBadge(item.validity).cls]">
                                                    {{ validityBadge(item.validity).text }}
                                                </span>
                                                <p v-if="item.inv_notes" class="text-xs text-slate-500 text-left">
                                                    {{ item.inv_notes }}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-else>
                                    <td colspan="3" class="px-4 py-6 text-center text-slate-400">Tidak ada data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>

                <!-- ===== 素行（Conduct）セクション ===== -->
                <template v-if="detail.inv_conduct?.length">
                    <SectionHeader title="Perilaku Kerja (Conduct)" subtitle="Hasil konfirmasi ke atasan/HR perusahaan sebelumnya" />

                    <div class="mt-4 mb-8 overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-amber-50 text-slate-600">
                                    <th class="text-left px-4 py-3 rounded-tl-xl font-semibold">Perusahaan</th>
                                    <th class="text-left px-4 py-3 font-semibold">Item Conduct</th>
                                    <th class="text-center px-4 py-3 rounded-tr-xl font-semibold w-48">Hasil Konfirmasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in detail.inv_conduct" :key="item.item_name"
                                    :class="[
                                        'border-b border-slate-100 transition',
                                        item.validity === 'INVALID'     ? 'bg-red-50 hover:bg-red-100' :
                                        item.validity === 'UNVERIFIED'  ? 'bg-yellow-50 hover:bg-yellow-100' :
                                        'hover:bg-amber-50/30'
                                    ]">
                                    <td class="px-4 py-3 text-slate-500 text-xs">{{ item.company_name }}</td>
                                    <td class="px-4 py-3 text-slate-700 font-medium">{{ item.label }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span v-if="item.validity"
                                                :class="['text-xs px-2 py-1 rounded-full font-medium', validityBadge(item.validity).cls]">
                                                {{ validityBadge(item.validity).text }}
                                            </span>
                                            <span v-else class="text-xs text-slate-300">未調査</span>
                                            <p v-if="item.inv_notes" class="text-xs text-slate-500 text-left">
                                                {{ item.inv_notes }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>

                <Divider class="my-6" />

                <!-- ===== 管理メモ & 差し戻し理由 ===== -->
                <SectionHeader title="Keputusan Admin" subtitle="Catatan & tindakan akhir" />

                <div class="space-y-5 mt-5">
                    <div>
                        <label class="text-sm font-semibold text-slate-700 block mb-2">
                            📝 Catatan Admin
                            <span class="text-xs font-normal text-slate-400 ml-1">（Wajib diisi jika menolak / kondisi / eskalasi）</span>
                        </label>
                        <textarea
                            v-model="adminNotes"
                            rows="4"
                            placeholder="Tulis catatan, alasan penolakan, atau kondisi persetujuan..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm"
                        ></textarea>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700 block mb-2">
                            🔄 Alasan Pengembalian ke Investigator
                            <span class="text-xs font-normal text-slate-400 ml-1">（Wajib diisi jika mengembalikan）</span>
                        </label>
                        <textarea
                            v-model="returnReason"
                            rows="3"
                            placeholder="Jelaskan apa yang perlu ditinjau ulang oleh tim investigator..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm"
                        ></textarea>
                    </div>
                </div>

                <Divider class="my-6" />

                <!-- ===== アクションボタン ===== -->
                <div class="flex flex-wrap justify-end gap-3">
                    <!-- 差し戻し -->
                    <Button variant="secondary" @click="doReturn" :disabled="processing">
                        🔄 Kembalikan ke Investigator
                    </Button>
                    <!-- エスカレート -->
                    <Button variant="secondary" @click="doEscalate" :disabled="processing"
                        class="bg-orange-50 border-orange-300 text-orange-700 hover:bg-orange-100">
                        👤 Eskalasi ke Manusia
                    </Button>
                    <!-- 却下 -->
                    <Button variant="danger" @click="doReject" :disabled="processing">
                        ❌ Tolak
                    </Button>
                    <!-- 条件付き承認 -->
                    <Button variant="outline" @click="doConditionalApprove" :disabled="processing"
                        class="border-blue-400 text-blue-700 hover:bg-blue-50">
                        🔵 Setujui dengan Kondisi
                    </Button>
                    <!-- 承認 -->
                    <Button @click="doApprove" :disabled="processing">
                        ✅ Setujui Sertifikasi
                    </Button>
                </div>

            </template>
        </Card>
    </div>

    <ImageViewer :show="showImage" :src="viewImageSrc" @close="showImage = false" />
</template>