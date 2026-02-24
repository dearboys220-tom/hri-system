<script setup>
defineProps({
  label: String
})
</script>

<template>
  <div class="space-y-1">
    <label class="text-sm font-medium text-slate-700">
      {{ label }}
    </label>
    <slot />
  </div>
</template>
