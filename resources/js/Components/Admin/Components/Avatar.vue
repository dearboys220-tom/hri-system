<script setup>
defineProps({
  src: String,
  size: {
    type: String,
    default: 'md'
  }
})

const sizes = {
  sm: "w-10 h-10",
  md: "w-16 h-16",
  lg: "w-24 h-24"
}
</script>

<template>
  <img
    :src="src"
    class="rounded-full object-cover border-4 border-white shadow"
    :class="sizes[size]"
  />
</template>
