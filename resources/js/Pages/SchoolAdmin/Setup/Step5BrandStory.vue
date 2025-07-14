<template>
  <div class="max-w-2xl mx-auto p-6">
    <!-- Header Section -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-2xl mb-4">
        <span class="text-2xl">🎨</span>
      </div>
      <h2 class="text-3xl font-bold text-gray-900 mb-2">Branding & Visual Identity</h2>
      <p class="text-gray-600">Define your school's visual personality and style</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
      <form @submit.prevent="handleNext" class="space-y-8">
        <!-- Logo Upload Section -->
        <div class="space-y-6">
          <div class="flex items-center mb-4">
            <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Logo Upload</h3>
          </div>

          <div class="space-y-4">
            <label class="flex flex-col items-center justify-center w-full p-6 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition-colors duration-200">
              <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <svg class="w-8 h-8 mb-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="mb-2 text-sm text-gray-500">Click to upload or drag and drop</p>
                <p class="text-xs text-gray-500">PNG, JPG (Max. 2MB)</p>
              </div>
              <input type="file" accept="image/*" @change="onLogoChange" class="hidden" />
            </label>
            <div v-if="logoPreview" class="flex flex-col items-center">
              <img :src="logoPreview" alt="Logo Preview" class="h-32 object-contain rounded-lg border border-gray-200 p-2" />
              <button 
                @click="removeLogo" 
                type="button" 
                class="mt-2 text-sm text-amber-600 hover:text-amber-800 flex items-center"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Remove Logo
              </button>
            </div>
          </div>
        </div>

        <!-- Brand Color Section -->
        <div class="space-y-6 pt-6 border-t border-gray-100">
          <div class="flex items-center mb-4">
            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Brand Colors</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Primary Color -->
            <div class="space-y-2">
              <label class="flex items-center text-sm font-semibold text-gray-700">
                <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                Primary Brand Color
              </label>
              <div class="flex items-center">
                <input
                  v-model="store.brandColor"
                  type="color"
                  class="w-12 h-12 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
                />
                <span class="ml-4 font-mono text-sm bg-gray-100 px-3 py-2 rounded">{{ store.brandColor || '#3B82F6' }}</span>
              </div>
            </div>

            <!-- Slogan -->
            <div class="space-y-2">
              <label class="flex items-center text-sm font-semibold text-gray-700">
                <svg class="w-4 h-4 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                School Slogan
              </label>
              <input
                v-model="store.slogan"
                type="text"
                placeholder="e.g. Empowering Future Leaders"
                class="w-full pl-4 pr-4 py-3 border-2 border-gray-200 rounded-xl transition-all duration-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-100 focus:outline-none hover:border-gray-300"
              />
            </div>
          </div>
        </div>

        <!-- Background Image Section -->
        <div class="space-y-6 pt-6 border-t border-gray-100">
          <div class="flex items-center mb-4">
            <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Background Image</h3>
            <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full font-medium">Optional</span>
          </div>

          <div class="space-y-4">
            <label class="flex flex-col items-center justify-center w-full p-6 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition-colors duration-200">
              <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <svg class="w-8 h-8 mb-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="mb-2 text-sm text-gray-500">Upload background image</p>
                <p class="text-xs text-gray-500">PNG, JPG (Max. 5MB)</p>
              </div>
              <input type="file" accept="image/*" @change="onBackgroundChange" class="hidden" />
            </label>
            <div v-if="bgPreview" class="flex flex-col items-center">
              <img :src="bgPreview" alt="Background Preview" class="w-full max-h-48 object-cover rounded-lg border border-gray-200" />
              <button 
                @click="removeBackground" 
                type="button" 
                class="mt-2 text-sm text-amber-600 hover:text-amber-800 flex items-center"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Remove Background
              </button>
            </div>
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
              Step 5 of 6
            </div>
          </div>
          <button 
            type="submit" 
            class="group relative inline-flex items-center px-8 py-3 bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-semibold rounded-xl transition-all duration-200 hover:from-amber-600 hover:to-yellow-600 hover:shadow-lg hover:shadow-amber-500/25 focus:outline-none focus:ring-4 focus:ring-amber-100 dark:focus:ring-yellow-900 active:scale-95"
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
        <div class="w-8 h-1 bg-purple-500 dark:bg-purple-900 rounded"></div>
        <div class="w-3 h-3 bg-amber-500 dark:bg-amber-900 rounded-full"></div>
        <div class="w-8 h-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useOnboardingStore } from '@/stores/useOnboardingStore';

const emit = defineEmits(['next', 'back']);
const store = useOnboardingStore();

const logoPreview = ref(store.logo ? URL.createObjectURL(store.logo) : null);
const bgPreview = ref(store.background ? URL.createObjectURL(store.background) : null);

function onLogoChange(event) {
  const file = event.target.files[0];
  if (file) {
    store.logo = file;
    logoPreview.value = URL.createObjectURL(file);
  }
}

function onBackgroundChange(event) {
  const file = event.target.files[0];
  if (file) {
    store.background = file;
    bgPreview.value = URL.createObjectURL(file);
  }
}

function removeLogo() {
  store.logo = null;
  logoPreview.value = null;
}

function removeBackground() {
  store.background = null;
  bgPreview.value = null;
}

function handleNext() {
  emit('next');
}
</script>
