<script setup>
defineProps({
  label: String,
  value: String
})
</script>

<template>
  <div>
    <p class="text-xs text-slate-500">{{ label }}</p>
    <p class="text-sm font-semibold text-slate-800">
      {{ value }}
    </p>
  </div>
</template>
