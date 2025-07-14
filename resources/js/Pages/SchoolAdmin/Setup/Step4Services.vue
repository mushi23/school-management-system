<template>
  <div class="max-w-2xl mx-auto p-6">
    <!-- Header Section -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl mb-4">
        <span class="text-2xl">🛎️</span>
      </div>
      <h2 class="text-3xl font-bold text-gray-900 mb-2">Services Offered</h2>
      <p class="text-gray-600">Select the additional services your school provides</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
      <form @submit.prevent="handleNext" class="space-y-8">
        <!-- Predefined Services Section -->
        <div class="space-y-6">
          <div class="flex items-center mb-4">
            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Standard Services</h3>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label 
              v-for="service in predefinedServices" 
              :key="service.value" 
              class="flex items-center space-x-3 p-3 bg-gray-50 hover:bg-purple-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-purple-200"
            >
              <div class="flex items-center h-5">
                <input
                  type="checkbox"
                  :value="service.value"
                  v-model="store.services"
                  class="w-5 h-5 text-purple-600 rounded border-gray-300 focus:ring-purple-500 transition-all"
                />
              </div>
              <span class="text-sm font-medium text-gray-700">{{ service.label }}</span>
            </label>
          </div>
        </div>

        <!-- Custom Services Section -->
        <div class="space-y-6 pt-6 border-t border-gray-100">
          <div class="flex items-center mb-4">
            <svg class="w-5 h-5 mr-2 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Custom Services</h3>
          </div>

          <div class="space-y-2">
            <label class="flex items-center text-sm font-semibold text-gray-700 mb-3">
              <svg class="w-4 h-4 mr-2 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
              Other Services
              <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full font-medium">Optional</span>
            </label>
            <input
              v-model="store.custom"
              type="text"
              placeholder="e.g. Daycare, Gifted Program, After-school Care"
              class="w-full pl-4 pr-4 py-4 border-2 border-gray-200 rounded-xl transition-all duration-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none hover:border-gray-300"
            />
            <p class="text-xs text-gray-500 ml-1">Add any services not listed above (separate with commas)</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700">
          <div class="flex items-center">
            <button 
              type="button" 
              @click="$emit('back')" 
              class="group inline-flex items-center px-6 py-3 border-2 border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all duration-200 hover:border-gray-400 dark:hover:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-900 active:scale-95"
            >
              <svg class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
              </svg>
              <span>Back</span>
            </button>
            <div class="ml-6 flex items-center text-sm text-gray-500 dark:text-gray-400">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Step 4 of 6
            </div>
          </div>
          <button 
            type="submit" 
            class="group relative inline-flex items-center px-8 py-3 bg-gradient-to-r from-purple-600 to-violet-600 text-white font-semibold rounded-xl transition-all duration-200 hover:from-purple-700 hover:to-violet-700 hover:shadow-lg hover:shadow-purple-500/25 focus:outline-none focus:ring-4 focus:ring-purple-100 dark:focus:ring-purple-900 active:scale-95"
          >
            <span>Continue</span>
            <svg class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
          </button>
        </div>
      </form>
    </div>

    <!-- Progress Indicator -->
    <div class="mt-8 flex justify-center">
      <div class="flex items-center space-x-2">
        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
        <div class="w-8 h-1 bg-blue-500 dark:bg-blue-900 rounded"></div>
        <div class="w-3 h-3 bg-green-500 dark:bg-green-900 rounded-full"></div>
        <div class="w-8 h-1 bg-green-500 dark:bg-green-900 rounded"></div>
        <div class="w-3 h-3 bg-orange-500 dark:bg-orange-900 rounded-full"></div>
        <div class="w-8 h-1 bg-orange-500 dark:bg-orange-900 rounded"></div>
        <div class="w-3 h-3 bg-purple-500 dark:bg-purple-900 rounded-full"></div>
        <div class="w-8 h-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        <div class="w-8 h-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useOnboardingStore } from '@/stores/useOnboardingStore';

const emit = defineEmits(['next', 'back']);
const store = useOnboardingStore();

const predefinedServices = [
  { label: 'Boarding Facilities', value: 'boarding' },
  { label: 'Transport Services', value: 'transport' },
  { label: 'Meals / Canteen', value: 'meals' },
  { label: 'Clubs & Extracurriculars', value: 'clubs' },
  { label: 'Special Education Support', value: 'special_ed' },
  { label: 'Library & Research', value: 'library' },
];

function handleNext() {
  emit('next');
}
</script>