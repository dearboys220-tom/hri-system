<script setup>
defineProps({
  title: String,
  subtitle: String
})
</script>

<template>
  <div class="mb-8">
    <h2 class="text-2xl font-semibold text-slate-800">
      {{ title }}
    </h2>
    <p class="text-slate-500 mt-1">
      {{ subtitle }}
    </p>
  </div>
</template>