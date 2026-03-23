<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    memoValue:  { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'update:memoValue']);

const localMemo = ref(props.memoValue);

watch(localMemo, (v) => emit('update:memoValue', v));
watch(() => props.memoValue, (v) => { localMemo.value = v; });
</script>

<template>
    <div class="space-y-2">
        <!-- セレクトボックス -->
        <select
            :value="modelValue"
            @change="emit('update:modelValue', $event.target.value)"
            class="w-full rounded-xl px-3 py-2 text-sm border-2 focus:outline-none focus:ring-2 transition"
            :class="{
                'border-slate-300 focus:ring-slate-300':   !modelValue,
                'border-green-400 bg-green-50 focus:ring-green-300 text-green-700': modelValue === 'VALID',
                'border-red-400   bg-red-50   focus:ring-red-300   text-red-700':   modelValue === 'INVALID',
            }"
        >
            <option value="">Pilih Status</option>
            <option value="VALID">✅ Valid</option>
            <option value="INVALID">❌ Invalid</option>
        </select>

        <!-- INVALIDのときだけメモ欄を表示 -->
        <textarea
            v-if="modelValue === 'INVALID'"
            v-model="localMemo"
            rows="2"
            placeholder="Tulis alasan invalid..."
            class="w-full rounded-xl border-2 border-red-300 bg-red-50 px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:outline-none placeholder-red-300"
        ></textarea>
    </div>
</template>