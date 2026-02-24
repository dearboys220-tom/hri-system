<script setup>
import {
  MagnifyingGlassIcon,
  ArrowRightOnRectangleIcon,
  BookOpenIcon
} from '@heroicons/vue/24/outline'
import Button from './Button.vue';

defineProps({
  title: {
    type: String,
    default: 'Tim'
  },
  subtitle: {
    type: String,
    default: 'Penyelidikan'
  }
})
</script>

<template>
   <header
      class="sticky top-0 z-40
            bg-gradient-to-r from-admin-primary-700 to-admin-primary-900
            px-4 sm:px-6 md:px-8 py-5 shadow-md"
    >
    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-5">

      <!-- LEFT -->
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-100 flex items-center gap-3">
          <MagnifyingGlassIcon class="w-5 h-5 sm:w-6 sm:h-6 text-slate-200" />
          {{ title }}
        </h1>

        <p class="text-sm text-slate-300 mt-1">
          {{ subtitle }}
        </p>
      </div>

      <!-- RIGHT -->
      <div class="flex flex-col items-end gap-3 w-full md:w-auto">

        <!-- Penanggung Jawab -->
        <div
          class="text-sm text-slate-200 text-right w-full md:w-auto
                 order-2 md:order-1"
        >
          Penanggung Jawab:
          <span class="font-semibold text-white">
            Etest02
          </span>
        </div>

        <!-- Buttons -->
        <div
          class="flex w-full md:w-auto gap-3
                 order-1 md:order-2"
        >
          <Button
            variant="secondary"
            size="sm"
            class="flex-1 md:flex-none"
          >
            <BookOpenIcon class="w-4 h-4" />
            Panduan
          </Button>

          <Button
            variant="danger"
            size="sm"
            class="flex-1 md:flex-none"
          >
            <ArrowRightOnRectangleIcon class="w-4 h-4" />
            Keluar
          </Button>
        </div>

      </div>

    </div>
  </header>
</template>
