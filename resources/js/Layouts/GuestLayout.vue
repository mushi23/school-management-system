<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/useTheme' // adjust path if needed

const { theme, toggleTheme } = useTheme();
</script>

<template>
  <div
    class="flex min-h-screen flex-col items-center bg-gray-100 dark:bg-gray-900 pt-6 sm:justify-center sm:pt-0 relative"
  >
    <div class="absolute top-4 right-4">
      <!-- Dark mode toggle button -->
      <button
        @click="toggleTheme"
        class="p-2 rounded focus:outline-none bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition"
        :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
        title="Toggle Dark Mode"
      >
        <template v-if="theme === 'dark'">
          <!-- Sun icon for light mode -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-12.66l-.71.71M4.05 19.95l-.71.71m16.57 0l-.71-.71M4.05 4.05l-.71-.71M21 12h-1M4 12H3m14.66 4.66l-.71-.71M6.34 6.34l-.71-.71" />
            <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
          </svg>
        </template>
        <template v-else>
          <!-- Moon icon for dark mode -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800 dark:text-gray-200" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293a8 8 0 11-10.586-10.586 7 7 0 1010.586 10.586z" />
          </svg>
        </template>
      </button>
    </div>

    <div>
      <Link href="/">
        <ApplicationLogo class="h-20 w-20 fill-current text-gray-500 dark:text-gray-400" />
      </Link>
    </div>

    <div
      class="mt-6 w-full overflow-hidden bg-white dark:bg-gray-800 px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
    >
      <slot />
    </div>
  </div>
</template>
