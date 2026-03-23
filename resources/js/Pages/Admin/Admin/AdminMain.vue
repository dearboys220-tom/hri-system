<script setup>
import Button from '@/Components/Admin/Components/Button.vue';
import Card from '@/Components/Admin/Components/Card.vue';
import Divider from '@/Components/Admin/Components/Divider.vue';
import InfoField from '@/Components/Admin/Components/InfoField.vue';
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

// ---- フォームデータ ----
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

// ---- validity バッジ ----
function validityBadge(v) {
    if (!v) return { text: '-', cls: 'bg-slate-100 text-slate-400' };
    if (v === 'VALID')   return { text: '✅ VALID',   cls: 'bg-green-100 text-green-700' };
    if (v === 'INVALID') return { text: '❌ INVALID', cls: 'bg-red-100 text-red-700' };
    return { text: v, cls: 'bg-slate-100 text-slate-500' };
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

function doReject() {
    if (!props.detail) return;
    if (!adminNotes.value.trim()) {
        alert('Isi alasan penolakan terlebih dahulu di kolom Catatan Admin.');
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
    if (!confirm('Kembalikan ke Tim Reviewer?')) return;
    processing.value = true;
    router.post(
        route('admin.admin.return', props.detail.id),
        { return_reason: returnReason.value },
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

                <!-- ===== スコアサマリー ===== -->
                <div :class="['border rounded-2xl p-6 mb-6', scoreBg(detail.final_score)]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Anggota</p>
                            <p class="text-lg font-bold text-slate-800">{{ detail.profile?.full_name }}</p>
                            <p class="text-sm font-mono text-admin-primary-600">{{ detail.profile?.member_id }}</p>
                            <p v-if="detail.review_completed_date" class="text-xs text-slate-400 mt-1">
                                Review selesai: {{ detail.review_completed_date }}
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-slate-500 mb-1">Skor HRI</p>
                            <p :class="['text-5xl font-extrabold', scoreColor(detail.final_score)]">
                                {{ detail.final_score }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">/ 100</p>
                        </div>
                    </div>

                    <!-- カテゴリ別減点 -->
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div v-for="(val, cat) in detail.deductions?.by_category" :key="cat"
                             class="bg-white/70 rounded-xl p-3 text-center">
                            <p class="text-xs text-slate-500 capitalize">{{ cat.replace('_', ' ') }}</p>
                            <p class="text-lg font-bold text-slate-700">-{{ val }}</p>
                        </div>
                    </div>
                </div>

                <!-- レビューコメント -->
                <div v-if="detail.reviewer_comments" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                    <p class="text-xs text-blue-500 mb-1 font-semibold">💬 Komentar Tim Reviewer</p>
                    <p class="text-sm text-blue-800 whitespace-pre-wrap">{{ detail.reviewer_comments }}</p>
                </div>

                <Divider class="my-6" />

                <!-- ===== プロフィール写真 & KTP ===== -->
                <SectionHeader title="Identitas Anggota" subtitle="Foto & dokumen identitas" />
                <div class="flex gap-6 mt-4 mb-6 flex-wrap">
                    <div>
                        <p class="text-xs text-slate-500 mb-2">Foto Profil</p>
                        <div
                            class="w-28 h-28 rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center cursor-pointer"
                            @click="detail.profile?.profile_photo && openImage('/storage/' + detail.profile.profile_photo)"
                        >
                            <img v-if="detail.profile?.profile_photo"
                                 :src="'/storage/' + detail.profile.profile_photo"
                                 class="w-full h-full object-cover" />
                            <span v-else class="text-3xl">👤</span>
                        </div>
                    </div>
                    <div v-if="detail.profile?.ktp_card">
                        <p class="text-xs text-slate-500 mb-2">Foto KTP</p>
                        <img
                            :src="'/storage/' + detail.profile.ktp_card"
                            class="h-28 rounded-xl border border-slate-200 cursor-pointer object-cover"
                            @click="openImage('/storage/' + detail.profile.ktp_card)"
                        />
                    </div>
                </div>

                <Divider class="my-6" />

                <!-- ===== 調査結果テーブル ===== -->
                <template v-for="section in [
                    { title: 'Data Pribadi',       subtitle: 'Hasil investigasi & penilaian', items: detail.inv_basic },
                    { title: 'Riwayat Pendidikan', subtitle: 'Hasil investigasi & penilaian', items: detail.inv_edu  },
                    { title: 'Pengalaman Kerja',   subtitle: 'Hasil investigasi & penilaian', items: detail.inv_work },
                    { title: 'Sertifikat',         subtitle: 'Hasil investigasi & penilaian', items: detail.inv_cert },
                ]" :key="section.title">

                    <SectionHeader :title="section.title" :subtitle="section.subtitle" />

                    <div class="mt-4 mb-8 overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-600">
                                    <th class="text-left px-4 py-3 rounded-tl-xl font-semibold">Item</th>
                                    <th class="text-left px-4 py-3 font-semibold">Nilai</th>
                                    <th class="text-center px-4 py-3 font-semibold w-32">Investigasi</th>
                                    <th class="text-center px-4 py-3 font-semibold w-28">Pengurangan</th>
                                    <th class="text-left px-4 py-3 rounded-tr-xl font-semibold">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-if="section.items?.length">
                                    <tr
                                        v-for="item in section.items"
                                        :key="item.item_name"
                                        class="border-b border-slate-100 hover:bg-slate-50 transition"
                                    >
                                        <td class="px-4 py-3 text-slate-700 font-medium">{{ item.label }}</td>
                                        <td class="px-4 py-3 text-slate-600 max-w-[200px] truncate" :title="item.value">{{ item.value }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span :class="['text-xs px-2 py-1 rounded-full font-medium', validityBadge(item.validity).cls]">
                                                {{ validityBadge(item.validity).text }}
                                            </span>
                                            <p v-if="item.inv_notes" class="text-xs text-slate-400 mt-1">{{ item.inv_notes }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                v-if="item.actual_deduction !== null"
                                                :class="['text-sm font-bold', item.actual_deduction > 0 ? 'text-red-600' : 'text-green-600']"
                                            >
                                                -{{ item.actual_deduction }}
                                            </span>
                                            <span v-if="item.max_deduction !== null" class="text-xs text-slate-400"> / {{ item.max_deduction }}</span>
                                            <span v-if="item.actual_deduction === null" class="text-slate-300">-</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 text-xs">{{ item.review_notes || '-' }}</td>
                                    </tr>
                                </template>
                                <tr v-else>
                                    <td colspan="5" class="px-4 py-6 text-center text-slate-400">Tidak ada data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </template>

                <Divider class="my-6" />

                <!-- ===== 管理チームメモ & 差戻し理由 ===== -->
                <SectionHeader title="Keputusan Admin" subtitle="Catatan & tindakan akhir" />

                <div class="space-y-5 mt-5">
                    <div>
                        <label class="text-sm font-semibold text-slate-700 block mb-2">
                            📝 Catatan Admin
                            <span class="text-xs font-normal text-slate-400 ml-1">（wajib diisi jika menolak）</span>
                        </label>
                        <textarea
                            v-model="adminNotes"
                            rows="4"
                            placeholder="Tulis catatan atau alasan penolakan..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm"
                        ></textarea>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700 block mb-2">
                            ↩️ Alasan Pengembalian ke Reviewer
                            <span class="text-xs font-normal text-slate-400 ml-1">（wajib diisi jika mengembalikan）</span>
                        </label>
                        <textarea
                            v-model="returnReason"
                            rows="3"
                            placeholder="Jelaskan apa yang perlu ditinjau ulang..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-admin-primary-600 focus:outline-none text-sm"
                        ></textarea>
                    </div>
                </div>

                <Divider class="my-6" />

                <!-- ===== アクションボタン ===== -->
                <div class="flex flex-wrap justify-end gap-3">
                    <Button variant="secondary" @click="doReturn" :disabled="processing">
                        ↩️ Kembalikan ke Reviewer
                    </Button>
                    <Button variant="danger" @click="doReject" :disabled="processing">
                        ❌ Tolak
                    </Button>
                    <Button @click="doApprove" :disabled="processing">
                        ✅ Setujui Sertifikasi
                    </Button>
                </div>

            </template>
        </Card>
    </div>

    <ImageViewer :show="showImage" :src="viewImageSrc" @close="showImage = false" />
</template>