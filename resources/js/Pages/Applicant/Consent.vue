 <script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const agreedTerms = ref(false);
const agreedInvestigation = ref(false);

const form = useForm({
    agreed_terms: false,
    agreed_investigation: false,
});

const submit = () => {
    form.agreed_terms = agreedTerms.value;
    form.agreed_investigation = agreedInvestigation.value;
    form.post(route('applicant.consent.store'));
};

const canSubmit = () => agreedTerms.value && agreedInvestigation.value;
</script>

<template>
    <Head title="Persetujuan Pengguna" />

    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
        <div class="bg-white rounded-2xl shadow-md w-full max-w-lg p-8">

            <!-- ロゴ・タイトル -->
            <div class="text-center mb-8">
                <div class="text-3xl font-bold text-indigo-600 mb-2">HRI</div>
                <h1 class="text-xl font-bold text-gray-800">Persetujuan Pengguna</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Harap baca dan setujui kebijakan berikut sebelum melanjutkan.
                </p>
            </div>

            <!-- 同意項目1：利用規約 -->
            <div class="border border-gray-200 rounded-xl p-4 mb-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="agreedTerms"
                        class="mt-1 w-5 h-5 text-indigo-600 rounded cursor-pointer"
                    />
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">
                            Ketentuan Penggunaan & Kebijakan Privasi
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Saya telah membaca dan menyetujui
                            <a href="https://hri-check.com/terms-applicant/" target="_blank"
                               class="text-indigo-600 underline">Ketentuan Penggunaan</a>,
                            <a href="https://hri-check.com/privacy-applicant/" target="_blank"
                               class="text-indigo-600 underline">Kebijakan Privasi</a>,
                            dan seluruh
                            <a href="https://hri-check.com/important-policies/" target="_blank"
                               class="text-indigo-600 underline">kebijakan pengguna HRI</a>.
                        </p>
                    </div>
                </label>
            </div>

            <!-- 同意項目2：調査・検証への同意 -->
            <div class="border border-gray-200 rounded-xl p-4 mb-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="agreedInvestigation"
                        class="mt-1 w-5 h-5 text-indigo-600 rounded cursor-pointer"
                    />
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">
                            Persetujuan Investigasi & Verifikasi Data
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Saya menyetujui bahwa data yang saya berikan dapat diverifikasi
                            dan diinvestigasi oleh tim HRI dalam proses sertifikasi.
                            <a href="https://hri-check.com/investigation-consent/" target="_blank"
                               class="text-indigo-600 underline">Selengkapnya →</a>
                        </p>
                    </div>
                </label>
            </div>

            <!-- エラー表示 -->
            <div v-if="form.errors.agreed_terms || form.errors.agreed_investigation"
                 class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4 text-sm text-red-600">
                Harap centang semua persetujuan sebelum melanjutkan.
            </div>

            <!-- 同意ボタン -->
            <button
                @click="submit"
                :disabled="!canSubmit() || form.processing"
                class="w-full py-3 rounded-xl text-white font-semibold transition"
                :class="canSubmit()
                    ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer'
                    : 'bg-gray-300 cursor-not-allowed'"
            >
                {{ form.processing ? 'Memproses...' : 'Setuju & Lanjutkan' }}
            </button>

            <p class="text-xs text-gray-400 text-center mt-4">
                Persetujuan ini dicatat beserta tanggal dan waktu untuk keperluan hukum.
            </p>

        </div>
    </div>
</template>
