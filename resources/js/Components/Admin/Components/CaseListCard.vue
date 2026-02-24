<script setup>
defineProps({
  cases: Array
})
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

    <h3 class="font-semibold text-admin-primary-700 mb-4">
      Daftar Permintaan
    </h3>

    <div class="space-y-3">
      <div
        v-for="item in cases"
        :key="item.id"
        class="p-4 rounded-xl border border-slate-200 hover:border-admin-primary-700 hover:bg-[#E6F0FF] cursor-pointer transition"
      >
        <p class="text-sm font-medium text-slate-800">
          {{ item.member_number }}
        </p>
        <p class="text-xs text-slate-500">
          {{ item.name }}
        </p>
      </div>
    </div>

  </div>
</template>
