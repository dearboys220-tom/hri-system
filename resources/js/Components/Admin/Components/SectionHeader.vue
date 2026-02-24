<script setup>
defineProps({
  title: String,
  subtitle: String
})
</script>

<template>
  <div class="mb-4">
    <h3 class="text-lg font-semibold text-admin-primary-700">
      {{ title }}
    </h3>
    <p v-if="subtitle" class="text-sm text-slate-500">
      {{ subtitle }}
    </p>
  </div>
</template>
