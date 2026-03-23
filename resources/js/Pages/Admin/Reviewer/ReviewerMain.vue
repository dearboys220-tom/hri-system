<script setup>
import Button from '@/Components/Admin/Components/Button.vue';
import Card from '@/Components/Admin/Components/Card.vue';
import Divider from '@/Components/Admin/Components/Divider.vue';
import ImageViewer from '@/Components/Admin/Components/ImageViewer.vue';
import SectionHeader from '@/Components/Admin/Components/SectionHeader.vue';
import InvestReviewLayout from '@/Components/Admin/Layout/InvestReviewLayout.vue';
import { MagnifyingGlassIcon, UserCircleIcon } from '@heroicons/vue/24/outline';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: (h, page) =>
        h(InvestReviewLayout, {
            title: 'Tim Reviewer',
            subtitle: 'Evaluasi & Penilaian Hasil Investigasi'
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
    router.get(route('admin.reviewer.index'), { id }, { preserveScroll: true });
}

// ---- 減点入力 ----
const deductionInputs  = ref({});
const reviewerComments = ref('');
const returnReason     = ref('');

watch(() => props.detail, (d) => {
    reviewerComments.value = d?.reviewer_comments ?? '';
    returnReason.value = '';
    if (!d) return;

    const map = d.review_map ?? {};
    const inputs = {};

    const sections = [
        { items: d.inv_basic, category: 'basic_info' },
        { items: d.inv_edu,   category: 'education' },
        { items: d.inv_work,  category: 'work' },
        { items: d.inv_cert,  category: 'certification' },
    ];

    sections.forEach(({ items, category }) => {
        items?.forEach(item => {
            inputs[item.item_name] = {
                actual_deduction: map[item.item_name]?.actual_deduction ?? 0,
                notes:            map[item.item_name]?.notes ?? '',
                category,
                max_deduction:    item.max_deduction ?? 10,
            };
        });
    });

    deductionInputs.value = inputs;
}, { immediate: true });

// ---- 加重平均スコア計算 ----
const WEIGHTS = {
    basic_info:    0.15,
    education:     0.35,
    work:          0.40,
    certification: 0.10,
    other:         0.00,
};

const MAX_DEDUCTIONS = {
    basic_info:    15,
    education:     35,
    work:          40,
    certification: 10,
};

const liveDeductions = computed(() => {
    const byCategory = { basic_info: 0, education: 0, work: 0, certification: 0, other: 0 };

    Object.values(deductionInputs.value).forEach(d => {
        const cat = d.category ?? 'other';
        byCategory[cat] = (byCategory[cat] ?? 0) + Number(d.actual_deduction ?? 0);
    });

    // 加重平均方式: カテゴリ減点率 × 重み × 100
    let totalWeighted = 0;
    Object.keys(WEIGHTS).forEach(cat => {
        const max    = MAX_DEDUCTIONS[cat] ?? 1;
        const actual = byCategory[cat] ?? 0;
        const ratio  = max > 0 ? Math.min(actual / max, 1.0) : 0;
        totalWeighted += ratio * WEIGHTS[cat] * 100;
    });

    return {
        byCategory,
        totalWeighted: Math.round(totalWeighted * 100) / 100,
    };
});

const liveScore = computed(() => Math.max(0, 100 - liveDeductions.value.totalWeighted));

// ---- カテゴリ別加重減点を計算 ----
function categoryWeightedDeduction(cat, maxPts) {
    const actual = liveDeductions.value.byCategory[cat] ?? 0;
    const ratio  = maxPts > 0 ? Math.min(actual / maxPts, 1.0) : 0;
    return (ratio * WEIGHTS[cat] * 100).toFixed(2);
}

// ---- バッジ・カード色 ----
function badgeClass(validity) {
    if (validity === 'VALID')   return 'bg-green-100 text-green-700 border border-green-300';
    if (validity === 'INVALID') return 'bg-red-100 text-red-700 border border-red-300';
    return 'bg-yellow-100 text-yellow-700 border border-yellow-300';
}

function cardClass(validity) {
    if (validity === 'VALID')   return 'border-green-200 bg-green-50/40';
    if (validity === 'INVALID') return 'border-red-200 bg-red-50/40';
    return 'border-yellow-200 bg-yellow-50/40';
}

function badgeLabel(validity) {
    if (validity === 'VALID')   return 'Valid';
    if (validity === 'INVALID') return 'Tidak Valid';
    return 'Belum Dicek';
}

// ---- 画像ビューアー ----
const showImage    = ref(false);
const viewImageSrc = ref('');
function openImage(src) { viewImageSrc.value = src; showImage.value = true; }

// ---- アクション ----
const saving = ref(false);

function buildItems() {
    return Object.entries(deductionInputs.value).map(([item_name, d]) => ({
        item_name,
        category:         d.category,
        actual_deduction: Number(d.actual_deduction ?? 0),
        notes:            d.notes ?? '',
    }));
}

function saveAll() {
    if (!props.detail) return;
    saving.value = true;
    router.post(
        route('admin.reviewer.save', props.detail.id),
        { reviewer_comments: reviewerComments.value, items: buildItems() },
        { onFinish: () => { saving.value = false; } }
    );
}

function sendComplete() {
    if (!props.detail) return;
    if (!confirm('Kirim ke Tim Admin? Pastikan penilaian sudah selesai.')) return;
    router.post(route('admin.reviewer.complete', props.detail.id));
}

function sendReturn() {
    if (!props.detail) return;
    if (!returnReason.value.trim()) { alert('Isi alasan pengembalian.'); return; }
    router.post(route('admin.reviewer.return', props.detail.id), { return_reason: returnReason.value });
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-10 gap-6 items-start">

        <!-- ===== 左パネル ===== -->
        <Card class="md:col-span-3 self-start md:sticky md:top-28 h-fit">
            <SectionHeader title="Daftar Kasus" subtitle="Pilih kasus untuk dinilai" />

            <div class="flex gap-2 mt-4 mb-4">
                <div class="relative flex-1">
                    <MagnifyingGlassIcon class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                    <input v-model="search" placeholder="Cari nama..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-admin-primary-600" />
                </div>
                <Button variant="secondary" size="sm" @click="search = ''">Reset</Button>
            </div>

            <Divider />

            <div class="space-y-3 mt-4 max-h-[400px] overflow-y-auto pr-1">
                <div v-if="filteredCases.length === 0" class="text-center py-8 text-slate-400 text-sm">
                    Tidak ada kasus
                </div>
                <div v-for="c in filteredCases" :key="c.id" @click="selectCase(c.id)"
                    class="p-4 rounded-xl border cursor-pointer transition"
                    :class="c.id === selectedId
                        ? 'border-admin-primary-600 bg-admin-primary-50'
                        : 'border-slate-200 hover:border-admin-primary-400 hover:bg-admin-primary-50'">
                    <div class="flex items-center gap-2">
                        <UserCircleIcon class="w-5 h-5 text-admin-primary-600 shrink-0" />
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ c.name || c.member_id }}</p>
                            <p v-if="c.name" class="text-xs text-admin-primary-600 font-mono">{{ c.member_id }}</p>
                            <p class="text-xs text-slate-500">{{ c.submitted_at }}</p>
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

                <!-- ===== PROFIL ===== -->
                <SectionHeader title="Informasi Pemohon" subtitle="Ringkasan data utama pelamar" />
                <div class="mt-4 flex flex-col md:flex-row md:items-center gap-6">
                    <div class="flex justify-center">
                        <img v-if="detail.profile?.profile_photo"
                            :src="'/storage/' + detail.profile.profile_photo"
                            class="w-24 h-24 rounded-2xl object-cover shadow cursor-pointer"
                            @click="openImage('/storage/' + detail.profile.profile_photo)" />
                        <div v-else class="w-24 h-24 rounded-2xl bg-slate-100 flex items-center justify-center text-4xl">👤</div>
                    </div>
                    <div class="flex-1 grid grid-cols-2 gap-4 text-sm">
                        <div><p class="text-xs text-slate-500">Nama Lengkap</p><p class="font-semibold">{{ detail.profile?.full_name ?? '-' }}</p></div>
                        <div><p class="text-xs text-slate-500">NIK</p><p class="font-semibold">{{ detail.profile?.nik ?? '-' }}</p></div>
                        <div><p class="text-xs text-slate-500">Telepon</p><p class="font-semibold">{{ detail.profile?.phone_number ?? '-' }}</p></div>
                        <div><p class="text-xs text-slate-500">No. Member</p><p class="font-semibold font-mono text-admin-primary-600">{{ detail.profile?.member_id ?? '-' }}</p></div>
                    </div>
                </div>

                <Divider class="my-6" />

                <!-- ===== スコアパネル ===== -->
                <div class="border-2 border-blue-400 bg-blue-50 rounded-2xl p-6 mb-6">
                    <SectionHeader title="Skor Akhir Penilaian" subtitle="Metode: Weighted Average (Rata-rata Terbobot)" />
                    <div class="mt-6 flex flex-col lg:flex-row items-center gap-8">

                        <!-- スコア円 -->
                        <div class="flex flex-col items-center shrink-0">
                            <div class="w-40 h-40 rounded-full bg-white border-4 border-blue-500 flex items-center justify-center shadow-lg">
                                <div class="text-center">
                                    <p class="text-5xl font-bold text-blue-700">{{ liveScore.toFixed(1) }}</p>
                                    <p class="text-xs text-slate-500">dari 100</p>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">
                                Total Pengurangan Terbobot:
                                <span class="font-bold text-red-600">-{{ liveDeductions.totalWeighted }}</span>
                            </p>
                        </div>

                        <!-- カテゴリ別 -->
                        <div class="flex-1 grid grid-cols-2 gap-4">
                            <div v-for="[cat, label, weight, maxPts] in [
                                ['basic_info',    'Informasi Dasar', '15%', 15],
                                ['education',     'Pendidikan',      '35%', 35],
                                ['work',          'Riwayat Kerja',   '40%', 40],
                                ['certification', 'Sertifikat',      '10%', 10],
                            ]" :key="cat" class="border rounded-2xl p-4 bg-white">
                                <p class="text-xs font-medium text-slate-600">{{ label }}</p>
                                <p class="text-xs text-slate-400">Bobot: {{ weight }} / Maks: {{ maxPts }} poin</p>
                                <p class="mt-2 text-lg font-bold text-red-600">
                                    -{{ categoryWeightedDeduction(cat, maxPts) }} Poin
                                </p>
                                <p class="text-xs text-slate-400">
                                    Input: {{ liveDeductions.byCategory[cat] }} / {{ maxPts }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 計算方式の説明 -->
                    <div class="mt-4 p-3 bg-white rounded-xl border border-blue-200 text-xs text-slate-500">
                        💡 <strong>Cara Hitung:</strong>
                        Skor = 100 − Σ(Pengurangan ÷ Maks Kategori × Bobot × 100)
                    </div>
                </div>

                <!-- ===== 4セクション共通 ===== -->
                <template v-for="[sectionKey, title, subtitle] in [
                    ['inv_basic', 'Informasi Dasar',    'Hasil investigasi data pribadi'],
                    ['inv_edu',   'Riwayat Pendidikan', 'Hasil investigasi data pendidikan'],
                    ['inv_work',  'Pengalaman Kerja',   'Hasil investigasi riwayat pekerjaan'],
                    ['inv_cert',  'Sertifikat',         'Hasil investigasi sertifikasi'],
                ]" :key="sectionKey">

                    <SectionHeader :title="title" :subtitle="subtitle" />

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div v-if="!detail[sectionKey]?.length" class="text-slate-400 text-sm col-span-2">
                            Tidak ada data
                        </div>

                        <div v-for="item in detail[sectionKey]" :key="item.item_name"
                            class="border rounded-2xl p-4 transition" :class="cardClass(item.validity)">

                            <!-- ラベル + バッジ -->
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <p class="text-xs text-slate-500 font-medium">{{ item.label || item.item_name }}</p>
                                <span class="px-2 py-0.5 text-xs rounded-lg font-semibold whitespace-nowrap shrink-0"
                                    :class="badgeClass(item.validity)">
                                    {{ badgeLabel(item.validity) }}
                                </span>
                            </div>

                            <!-- 値 -->
                            <p class="text-sm font-semibold text-slate-800 mb-1">{{ item.value ?? '-' }}</p>

                            <!-- 調査メモ -->
                            <p v-if="item.notes" class="text-xs text-slate-500 mb-2 italic">
                                📝 {{ item.notes }}
                            </p>

                            <!-- INVALID の場合のみ減点入力 -->
                            <div v-if="item.validity === 'INVALID'" class="mt-2 space-y-1">
                                <label class="text-xs text-slate-500">
                                    Pengurangan Poin (0 – {{ item.max_deduction ?? 10 }})
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    :max="item.max_deduction ?? 10"
                                    step="1"
                                    v-model="deductionInputs[item.item_name].actual_deduction"
                                    class="w-full rounded-lg border border-red-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                                />
                            </div>
                        </div>
                    </div>

                    <Divider class="my-6" />
                </template>

                <!-- ===== CATATAN ===== -->
                <SectionHeader title="Catatan Reviewer" subtitle="Komentar & Pengembalian ke Investigator" />
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-700 mb-2 block">Komentar Reviewer</label>
                        <textarea v-model="reviewerComments" rows="3" placeholder="Tulis komentar penilaian..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:ring-2 focus:ring-admin-primary-600 focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700 mb-2 block">Alasan Pengembalian ke Investigator (Opsional)</label>
                        <textarea v-model="returnReason" rows="3" placeholder="Isi jika perlu dikembalikan ke tim investigasi..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:ring-2 focus:ring-admin-primary-600 focus:outline-none"></textarea>
                    </div>
                </div>

                <Divider class="my-6" />

                <!-- ===== ボタン ===== -->
                <div class="flex flex-wrap justify-end gap-3">
                    <Button variant="secondary" @click="sendReturn">
                        ↩️ Kembalikan ke Investigator
                    </Button>
                    <Button variant="outline" @click="saveAll" :disabled="saving">
                        {{ saving ? 'Menyimpan...' : '💾 Simpan' }}
                    </Button>
                    <Button @click="sendComplete">
                        ✅ Selesai → Kirim ke Admin
                    </Button>
                </div>

            </template>
        </Card>
    </div>

    <ImageViewer :show="showImage" :src="viewImageSrc" @close="showImage = false" />
</template>