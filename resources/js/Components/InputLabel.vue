<script setup>
defineProps({
  value: {
    type: String,
    default: '',
  },
});
</script>

<template>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
    <span>{{ value || $slots.default?.() }}</span>
  </label>
</template>
