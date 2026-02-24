<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary'
  },
  size: {
    type: String,
    default: 'md'
  },
  block: Boolean,
  loading: Boolean,
  disabled: Boolean,
  customClass: {
    type: String,
    default: ''
  }
})

const base =
  "relative overflow-hidden inline-flex items-center justify-center gap-2 rounded-xl font-medium \
   transition-colors duration-300 ease-out \
   focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed group"

const variants = {
  primary: {
    base: "bg-admin-primary-700 text-white",
    sweep: "bg-admin-primary-800"
  },

  secondary: {
    base: "bg-slate-100 text-slate-700",
    sweep: "bg-slate-200"
  },

  danger: {
    base: "bg-admin-danger text-white",
    sweep: "bg-red-700"
  },

  ghost: {
    base: "bg-transparent text-admin-primary-700 border border-admin-primary-200",
    sweep: "bg-admin-primary-50"
  },

  outline: {
    base: "bg-transparent border border-admin-primary-700 text-admin-primary-700 hover:text-slate-200",
    sweep: "bg-admin-primary-700"
  },

  soft: {
    base: "bg-white/15 text-slate-100 border border-white/20 backdrop-blur-sm",
    sweep: "bg-white/25"
  }
}

const sizes = {
  sm: "px-3 py-1.5 text-sm",
  md: "px-4 py-2 text-sm",
  lg: "px-5 py-3 text-base"
}

const computedClass = computed(() => [
  base,
  variants[props.variant].base,
  sizes[props.size],
  props.block ? 'w-full' : '',
  props.customClass
])

const sweepClass = computed(() => variants[props.variant].sweep)
</script>

<template>
  <button
    :class="computedClass"
    :disabled="disabled || loading"
  >

    <span
      class="absolute inset-0
             -translate-x-full
             group-hover:translate-x-0
             transition-transform duration-500 ease-out"
      :class="sweepClass"
    ></span>

    <span class="relative z-10 flex items-center gap-2">

      <span
        v-if="loading"
        class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
      ></span>

      <slot v-else />

    </span>

  </button>
</template>
