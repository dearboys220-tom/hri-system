<script setup>
import { usePage } from '@inertiajs/vue3';

defineProps({
  centered: Boolean,
  title: String,
  description: String,
  image: String,
  gradient: String
})
const page = usePage()
</script>

<template>
  <div :key="page.component" class="flex min-h-screen">

    <!-- LEFT -->
    <div class="hidden z-0 md:flex md:w-[65%] relative">

      <div
        class="absolute inset-0 bg-cover bg-center transition-all duration-500"
        :style="{ backgroundImage: `url(${image})` }"
      ></div>

      <div
        :class="[
          'absolute inset-0 bg-gradient-to-br transition-all duration-500',
          gradient
        ]"
      ></div>

      <div class="relative z-10 flex items-center px-24 text-white">
        <Transition name="slide-fade" mode="out-in">
          <div :key="title" class="max-w-xl">
            <h1 class="text-5xl font-bold mb-6">
              {{ title }}
            </h1>
            <p class="text-lg text-gray-200">
              {{ description }}
            </p>
          </div>
        </Transition>
      </div>

    </div>

    <div class="relative w-full md:w-[35%] bg-gradient-to-br from-white to-gray-100 z-20">
        <div class="h-screen flex">
            <div
            :class="[
                'flex-1 flex justify-center p-8',
                centered ? 'items-center' : 'items-start'
            ]"
            >
            <div class="w-full max-w-md">
                <slot />
            </div>
            </div>
        </div>
    </div>


  </div>
</template>