<template>
  <div class="max-w-2xl mx-auto p-6">
    <!-- Header Section -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mb-4">
        <span class="text-2xl">🏫</span>
      </div>
      <h2 class="text-3xl font-bold text-gray-900 mb-2">School Identity</h2>
      <p class="text-gray-600">Let's set up your school's basic information</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
      <form @submit.prevent="handleNext" class="space-y-8">
        
        <!-- School Name Section -->
        <div class="space-y-2">
          <label class="flex items-center text-sm font-semibold text-gray-700 mb-3">
            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            School Name
          </label>
          <div class="relative">
            <input
              v-model="store.organizationName"
              type="text"
              readonly
              class="w-full pl-4 pr-12 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-xl text-gray-700 font-medium cursor-not-allowed focus:outline-none"
            />
            <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
            </div>
          </div>
          <p class="text-xs text-gray-500 ml-1">This field is automatically populated and cannot be edited</p>
        </div>

        <!-- School Motto Section -->
        <div class="space-y-2">
          <label class="flex items-center text-sm font-semibold text-gray-700 mb-3">
            <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
            </svg>
            School Motto / Tagline
          </label>
          <div class="relative">
            <input
              v-model="store.slogan"
              type="text"
              class="w-full pl-4 pr-4 py-4 border-2 border-gray-200 rounded-xl transition-all duration-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none hover:border-gray-300"
              placeholder="e.g. Excellence Through Discipline"
            />
          </div>
          <p class="text-xs text-gray-500 ml-1">A inspiring phrase that represents your school's values</p>
        </div>

        <!-- Education Systems Section -->
        <div class="space-y-2">
          <label class="flex items-center text-sm font-semibold text-gray-700 mb-3">
            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            Education Systems
            <span class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-600 rounded-full font-medium">Required</span>
          </label>
          <div class="relative">
            <Multiselect
              v-model="store.educationSystems"
              :options="educationOptions"
              mode="tags"
              placeholder="Select or type education systems..."
              class="multiselect-custom"
              :searchable="true"
              :create-option="true"
            />
          </div>
          <p class="text-xs text-gray-500 ml-1">Select all education systems your school offers</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700">
          <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Step 1 of 6
          </div>
          <button 
            type="submit" 
            class="group relative inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl transition-all duration-200 hover:from-blue-700 hover:to-purple-700 hover:shadow-lg hover:shadow-blue-500/25 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900 active:scale-95"
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
        <div class="w-8 h-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        <div class="w-8 h-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        <div class="w-8 h-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        <div class="w-8 h-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        <div class="w-8 h-1 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="w-3 h-3 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch, ref } from 'vue'
import { useOnboardingStore } from '@/stores/useOnboardingStore'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const store = useOnboardingStore()
const emit = defineEmits(['next'])

const curriculumKeyMap = {
  'CBC': 1,
  '8-4-4': 2,
  'IGCSE': 3,
  'A-Level': 4,
  'IB (International Baccalaureate)': 5,
  'Cambridge International': 6,
  'American Curriculum': 7,
  'Other': 8
}

// Track pending change (don't apply immediately)
const pendingCurriculumChange = ref(null)

// Watch for selected curriculum system
watch(() => store.educationSystems, (selected) => {
  const newKeys = selected.map(system => curriculumKeyMap[system])
  const newPrimaryKey = newKeys[0] ?? null

  // If trying to switch curriculum and structure already exists — confirm
  if (
    newPrimaryKey &&
    store.curriculum_key !== newPrimaryKey &&
    store.structure &&
    Object.keys(store.structure).length > 0
  ) {
    pendingCurriculumChange.value = {
      primary: newPrimaryKey,
      keys: newKeys
    }
  } else {
    // Safe to apply directly
    store.curriculum_key = newPrimaryKey
    store.curriculumKeys = newKeys
  }
}, {
  // ❌ Removed immediate: true to prevent running on mount
  immediate: false
})


onMounted(() => {
  store.fetchSchoolInfo()
})

async function handleNext() {
  if (!store.educationSystems.length || !store.curriculum_key) {
    const errorElement = document.createElement('div')
    errorElement.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-bounce'
    errorElement.textContent = 'Please select at least one education system.'
    document.body.appendChild(errorElement)
    setTimeout(() => { errorElement.remove() }, 3000)
    return
  }

  // Confirm curriculum change if previous structure exists
  if (pendingCurriculumChange.value) {
    const confirmed = confirm(
      'Changing the education system will reset your previous setup. Continue?'
    )

    if (!confirmed) {
      // Revert selection
      store.educationSystems = Object.entries(curriculumKeyMap)
        .filter(([_, key]) => store.curriculum_key === key)
        .map(([name]) => name)

      pendingCurriculumChange.value = null
      return
    }

    // Apply the change + clear structure
    store.setStructure({})
    store.setCurriculumData(null)
    store.curriculum_key = pendingCurriculumChange.value.primary
    store.curriculumKeys = pendingCurriculumChange.value.keys
    pendingCurriculumChange.value = null
  }

  try {
    await store.updateSchoolCurriculum(store.curriculum_key)

    console.log('Primary curriculum_key:', store.curriculum_key)
    console.log('All selected curriculumKeys:', store.curriculumKeys)

    emit('next')
  } catch (err) {
    console.error('Failed to update curriculum_key:', err)
  }
}


const educationOptions = Object.keys(curriculumKeyMap)
</script>

<style>
/* Custom multiselect styling */
.multiselect-custom {
  --ms-font-size: 1rem;
  --ms-line-height: 1.5;
  --ms-bg: #ffffff;
  --ms-bg-disabled: #f3f4f6;
  --ms-border-color: #d1d5db;
  --ms-border-width: 2px;
  --ms-border-color-active: #8b5cf6;
  --ms-border-radius: 0.75rem;
  --ms-py: 0.75rem;
  --ms-px: 1rem;
  --ms-ring-width: 4px;
  --ms-ring-color: #ddd6fe;
  --ms-placeholder-color: #9ca3af;
  --ms-max-width: 100%;
}

.multiselect-custom .multiselect-tag {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 0.5rem;
  padding: 0.25rem 0.75rem;
  margin: 0.125rem;
  font-weight: 500;
  font-size: 0.875rem;
}

.multiselect-custom .multiselect-tag-remove {
  color: rgba(255, 255, 255, 0.8);
  margin-left: 0.5rem;
}

.multiselect-custom .multiselect-tag-remove:hover {
  color: white;
}
</style>
