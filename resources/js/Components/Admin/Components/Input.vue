<script setup>
defineProps({
  modelValue: String,
  placeholder: String
})

defineEmits(['update:modelValue'])
</script>

<template>
  <input
    :value="modelValue"
    :placeholder="placeholder"
    @input="$emit('update:modelValue', $event.target.value)"
    class="w-full px-4 py-2 rounded-xl border border-slate-300 
           focus:ring-2 focus:ring-admin-primary-700 focus:border-admin-primary-700 
           outline-none transition"
  />
</template>
