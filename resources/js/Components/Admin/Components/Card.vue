<script setup>
import { ref, computed, useSlots } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  padding: {
    type: String,
    default: 'p-6'
  },
  collapsible: {
    type: Boolean,
    default: false
  },
  defaultOpen: {
    type: Boolean,
    default: true
  },
  title: {
    type: String,
    default: ''
  }
})

const isOpen = ref(props.defaultOpen)

const toggle = () => {
  if (props.collapsible) {
    isOpen.value = !isOpen.value
  }
}

const hasHeaderSlot = computed(() => !!useSlots().header)
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

    <div
      v-if="title || collapsible || hasHeaderSlot"
      class="flex justify-between items-center px-6 py-5"
      :class="{ 'cursor-pointer hover:bg-slate-50': collapsible }"
      @click="toggle"
    >

      <slot name="header">
        <h3 v-if="title" class="text-base font-semibold text-slate-800">
          {{ title }}
        </h3>
      </slot>

      <div class="flex items-center gap-3">
        <slot name="headerRight" />

        <ChevronDownIcon
          v-if="collapsible"
          class="w-5 h-5 text-slate-500 transition-transform duration-300"
          :class="{ 'rotate-180': isOpen }"
        />
      </div>

    </div>

    <transition name="fade">
      <div v-show="!collapsible || isOpen" :class="padding">
        <slot />
      </div>
    </transition>

  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
