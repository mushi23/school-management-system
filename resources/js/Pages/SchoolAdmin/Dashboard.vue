<template>
  <SchoolAdminLayout>
    <div>
      <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">🏫 School Admin Dashboard</h1>

      <!-- School Branding Section -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden mb-6 transition-all duration-300 hover:shadow-2xl">
        <!-- Background Image with Edit Overlay -->
        <div class="relative h-80 bg-gradient-to-r from-blue-500 to-purple-600 overflow-hidden">
          <div v-if="currentBackground" class="absolute inset-0">
            <img 
              :src="currentBackground" 
              alt="School Background" 
              class="w-full h-full object-cover object-center transition-transform duration-300 hover:scale-105"
              @error="handleImageError('background')"
            />
            <!-- Blurred background overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-20 backdrop-blur-[1px]"></div>
          </div>
          
          <!-- Background Edit Overlay -->
          <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-all duration-300">
            <div class="cursor-pointer bg-white bg-opacity-95 rounded-xl px-6 py-3 text-sm font-medium text-gray-700 hover:bg-opacity-100 hover:scale-105 transition-all duration-200 shadow-xl">
              <i class="fas fa-camera mr-2"></i>
              Change Cover Photo
            </div>
          </div>
          
          <!-- Clickable Background Area -->
          <div 
            class="absolute inset-0 cursor-pointer"
            @click="triggerBackgroundUpload"
          >
            <input
              ref="backgroundInput"
              type="file"
              @change="handleBackgroundChange"
              accept="image/*"
              class="hidden"
            />
          </div>
        </div>

        <!-- School Info -->
        <div class="relative px-6 pb-6 -mt-16">
          <div class="flex items-end mb-4">
            <!-- School Logo with Edit Overlay -->
            <div class="relative">
              <div class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 bg-gray-200 dark:bg-gray-700 overflow-hidden shadow-xl transition-all duration-300 hover:shadow-2xl">
                <img 
                  v-if="currentLogo" 
                  :src="currentLogo" 
                  alt="School Logo" 
                  class="w-full h-full object-cover object-center transition-transform duration-300 hover:scale-110"
                  @error="handleImageError('logo')"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <i class="fas fa-school text-4xl text-gray-400 dark:text-gray-500"></i>
                </div>
              </div>
              
              <!-- Logo Edit Button -->
              <div class="absolute bottom-0 right-0 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-2.5 cursor-pointer transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-110">
                <i class="fas fa-camera text-sm"></i>
              </div>
              
              <!-- Clickable Logo Area -->
              <div 
                class="absolute inset-0 cursor-pointer"
                @click="triggerLogoUpload"
              >
                <input
                  ref="logoInput"
                  type="file"
                  @change="handleLogoChange"
                  accept="image/*"
                  class="hidden"
                />
              </div>
            </div>
          </div>
          
          <!-- School Details - Moved below images -->
          <div class="mt-4">
            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-xl p-4 shadow-lg border border-white/40 dark:border-gray-700/60">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 drop-shadow-sm">
                {{ school.name || 'School Name' }}
              </h2>
              <p v-if="school.slogan" class="text-gray-700 dark:text-gray-200 mt-2 text-lg font-medium drop-shadow-sm">
                {{ school.slogan }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Success Message -->
      <div v-if="showSuccessMessage" class="mb-6">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 shadow-lg">
          <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-3 text-lg"></i>
            <span class="text-green-800 dark:text-green-200 font-medium">Images updated successfully!</span>
          </div>
        </div>
      </div>

      <!-- Image Change Notification -->
      <div v-if="hasPendingChanges" class="mb-6">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 shadow-lg">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 text-lg"></i>
              <div>
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                  Pending Image Changes
                </h3>
                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                  You have unsaved image changes. Click "Save Changes" to make them permanent.
                </p>
              </div>
            </div>
            <div class="flex gap-2">
              <button
                @click="cancelChanges"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition-all duration-200 hover:shadow-md"
              >
                Cancel
              </button>
              <button
                @click="saveChanges"
                :disabled="saving"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i v-if="saving" class="fas fa-spinner fa-spin mr-1"></i>
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Upload Progress -->
      <div v-if="uploading" class="mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 shadow-lg">
          <div class="flex items-center">
            <i class="fas fa-spinner fa-spin text-blue-600 mr-3 text-lg"></i>
            <span class="text-blue-800 dark:text-blue-200 font-medium">Processing image...</span>
          </div>
        </div>
      </div>

      <!-- Button Row -->
      <div class="flex flex-row gap-4 mb-6">
        <button
          @click="openCreateStudent"
          class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded transition flex items-center justify-center"
          :disabled="loadingCreateStudent"
        >
          <template v-if="loadingCreateStudent">
            <i class="fas fa-spinner fa-spin mr-2"></i> Creating...
          </template>
          <template v-else>
            ➕ Create Student
          </template>
        </button>

        <button
          @click="openCreateTeacher"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition flex items-center justify-center"
          :disabled="loadingCreateTeacher"
        >
          <template v-if="loadingCreateTeacher">
            <i class="fas fa-spinner fa-spin mr-2"></i> Creating...
          </template>
          <template v-else>
            ➕ Create Teacher
          </template>
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow transition-colors duration-200">
          <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Students</h2>
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ stats.students }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow transition-colors duration-200">
          <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Teachers</h2>
          <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">{{ stats.teachers }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow transition-colors duration-200">
          <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Notifications</h2>
          <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-2">0</p>
        </div>
      </div>

      <div class="mt-8">
        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-2">Recent Activity</h3>
        <ul class="bg-white dark:bg-gray-800 rounded-2xl shadow divide-y divide-gray-200 dark:divide-gray-700 transition-colors duration-200">
          <li class="p-4 text-gray-700 dark:text-gray-200">📌 Feature coming soon...</li>
        </ul>
      </div>

      <!-- Modals -->
      <CreateStudentModal v-if="showStudentModal" :structures="structures" @close="showStudentModal = false" />
      <CreateTeacherModal v-if="showTeacherModal" @close="showTeacherModal = false" />
    </div>
  </SchoolAdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import SchoolAdminLayout from '@/Layouts/SchoolAdminLayout.vue'
import CreateStudentModal from '@/Pages/SchoolAdmin/Students/Create.vue'
import CreateTeacherModal from '@/Pages/SchoolAdmin/Teachers/Create.vue'
import { usePage } from '@inertiajs/vue3'

const showStudentModal = ref(false)
const showTeacherModal = ref(false)
const loadingCreateStudent = ref(false)
const loadingCreateTeacher = ref(false)
const uploading = ref(false)
const saving = ref(false)
const showSuccessMessage = ref(false)
const page = usePage()
const structures = page.props.structures
const school = page.props.school
const stats = page.props.stats

// File input refs
const logoInput = ref(null)
const backgroundInput = ref(null)

// Temporary image states
const tempLogo = ref(null)
const tempBackground = ref(null)

// Current displayed images (original or temporary)
const currentLogo = computed(() => {
  if (tempLogo.value) {
    return tempLogo.value
  }
  return school.logo || null
})

const currentBackground = computed(() => {
  if (tempBackground.value) {
    return tempBackground.value
  }
  return school.background || null
})

// Check if there are pending changes
const hasPendingChanges = computed(() => {
  return tempLogo.value || tempBackground.value
})

// File handling
const handleLogoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    uploading.value = true
    
    // Create temporary preview
    const reader = new FileReader()
    reader.onload = (e) => {
      tempLogo.value = e.target.result
      uploading.value = false
    }
    reader.readAsDataURL(file)
  }
}

const handleBackgroundChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    uploading.value = true
    
    // Create temporary preview
    const reader = new FileReader()
    reader.onload = (e) => {
      tempBackground.value = e.target.result
      uploading.value = false
    }
    reader.readAsDataURL(file)
  }
}

// Save changes
const saveChanges = async () => {
  if (!hasPendingChanges.value) return
  
  saving.value = true
  
  try {
    const formData = new FormData()
    
    // Get the actual files from the file inputs using refs
    if (tempLogo.value && logoInput.value && logoInput.value.files[0]) {
      formData.append('logo', logoInput.value.files[0])
    }
    
    if (tempBackground.value && backgroundInput.value && backgroundInput.value.files[0]) {
      formData.append('background', backgroundInput.value.files[0])
    }
    
    await router.post(route('schooladmin.profile.branding'), formData, {
      preserveScroll: true,
    })
    
    // Clear temporary states
    tempLogo.value = null
    tempBackground.value = null
    
    // Show success message
    showSuccessMessage.value = true
    setTimeout(() => {
      showSuccessMessage.value = false
    }, 3000)
    
    // Refresh the page to get updated data
    router.reload()
    
  } catch (error) {
    console.error('Error saving changes:', error)
  } finally {
    saving.value = false
  }
}

// Cancel changes
const cancelChanges = () => {
  tempLogo.value = null
  tempBackground.value = null
  
  // Clear file inputs using refs
  if (logoInput.value) logoInput.value.value = ''
  if (backgroundInput.value) backgroundInput.value.value = ''
}

// Trigger file uploads
const triggerLogoUpload = () => {
  logoInput.value?.click()
}

const triggerBackgroundUpload = () => {
  backgroundInput.value?.click()
}

// Handle image loading errors
const handleImageError = (type) => {
  console.error(`Failed to load ${type} image:`, type === 'logo' ? currentLogo.value : currentBackground.value)
}

async function openCreateStudent() {
  loadingCreateStudent.value = true
  await new Promise((resolve) => setTimeout(resolve, 1000))
  showStudentModal.value = true
  loadingCreateStudent.value = false
}

async function openCreateTeacher() {
  loadingCreateTeacher.value = true
  await new Promise((resolve) => setTimeout(resolve, 1000))
  showTeacherModal.value = true
  loadingCreateTeacher.value = false
}
</script>

