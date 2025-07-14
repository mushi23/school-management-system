<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg p-6 relative flex flex-col max-h-[90vh]">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 dark:hover:text-white"
          @click="$emit('close')"
        >
          <i class="fas fa-times"></i>
        </button>

        <!-- Modal Title -->
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
          {{ title }}
        </h3>

        <!-- Modal Content Slot -->
        <div class="text-gray-700 dark:text-gray-300 overflow-y-auto flex-1 min-h-0" style="max-height:60vh;">
          <slot />
        </div>

        <!-- Modal Footer Slot -->
        <div v-if="$slots.footer" class="mt-6 flex justify-end space-x-2">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'Modal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    title: {
      type: String,
      default: 'Modal Title',
    },
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

