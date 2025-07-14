<script setup>
defineProps({
  message: {
    type: String,
    default: '',
  },
});
</script>

<template>
  <div v-if="message" role="alert" aria-live="polite">
    <p class="text-sm text-red-600 dark:text-red-500">
      {{ message }}
    </p>
  </div>
</template>
