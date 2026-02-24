<script setup>
import { CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  status: {
    type: String,
    default: ''
  }
})

const badgeClass = computed(() => {
  if (props.status === 'valid')
    return 'bg-green-100 text-green-700 border border-green-300'
  if (props.status === 'invalid')
    return 'bg-red-100 text-red-700 border border-red-300'
  return 'bg-slate-100 text-slate-600 border border-slate-300'
})
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold"
    :class="badgeClass"
  >
    <CheckCircleIcon
      v-if="status === 'valid'"
      class="w-4 h-4"
    />

    <XCircleIcon
      v-else-if="status === 'invalid'"
      class="w-4 h-4"
    />

    {{ status === 'valid'
      ? 'Valid'
      : status === 'invalid'
        ? 'Tidak Valid'
        : 'Belum Dinilai' }}
  </span>
</template>
