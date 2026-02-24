<script setup>
import Topbar from '../Components/Topbar.vue';

defineProps({
  title: String,
  subtitle: String
})
</script>

<template>
  <div class="min-h-screen flex bg-admin-gray-100">

    <!-- <Sidebar /> -->

    <div class="flex-1 flex flex-col">

      <!-- kirim title ke Topbar -->
      <Topbar :title="title" :subtitle="subtitle" />

      <main class="p-8">
        <slot />
      </main>

    </div>

  </div>
</template>