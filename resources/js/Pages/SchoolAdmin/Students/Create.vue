<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeModal">
    <div class="bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto w-full max-w-3xl rounded-2xl shadow-xl relative p-6">
      <!-- ❌ Close Button -->
      <button
        @click="closeModal"
        class="absolute top-4 right-4 text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 text-2xl transition-colors duration-200"
      >
        &times;
      </button>

      <!-- ✅ Title -->
      <h2 class="text-2xl font-semibold text-center text-gray-700 dark:text-gray-200 mb-6 mt-2">Register New Student</h2>

      <!-- 🖼️ Profile Picture Upload -->
      <div class="flex justify-center mb-6">
        <label for="profile_picture" class="relative w-32 h-32 rounded-full border-4 border-white dark:border-gray-700 shadow-md cursor-pointer ring-offset-2 ring-offset-white dark:ring-offset-gray-800 ring-2 hover:ring-blue-500 group transition-all duration-300">
          <input id="profile_picture" type="file" class="hidden" accept="image/*" @change="handleImageUpload" />
          <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover rounded-full" />
          <div v-else class="flex items-center justify-center w-full h-full text-blue-400 dark:text-blue-300 text-4xl font-bold bg-blue-50 dark:bg-blue-900/20 rounded-full">+</div>
        </label>
      </div>

      <!-- 👤 Form -->
      <form @submit.prevent="submitForm" class="space-y-5">
        <FormGroup label="Full Name" id="full_name" v-model="form.full_name" :error="form.errors.full_name" />

        <div>
          <label class="form-label mb-1">Date of Birth</label>
          <CalendarView v-model="form.dob" />
          <p v-if="form.errors.dob" class="form-error mt-1">{{ form.errors.dob }}</p>
        </div>

        <FormGroup label="Email" id="email" type="email" v-model="form.email" :error="form.errors.email" />

        <div>
          <label class="form-label mb-1">Gender</label>
          <div class="flex items-center gap-6 mt-2">
            <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-200">
              <input type="radio" value="male" v-model="form.gender" class="form-radio text-blue-600" />
              <span>Male</span>
            </label>
            <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-200">
              <input type="radio" value="female" v-model="form.gender" class="form-radio text-blue-600" />
              <span>Female</span>
            </label>
            <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-200">
              <input type="radio" value="other" v-model="form.gender" class="form-radio text-blue-600" />
              <span>Other</span>
            </label>
          </div>
          <p v-if="form.errors.gender" class="form-error mt-1">{{ form.errors.gender }}</p>
        </div>

        <div>
          <label class="form-label mb-1">Guardians</label>
          <div class="space-y-2">
            <div v-for="(guardian, index) in form.guardians" :key="index" class="grid grid-cols-1 sm:grid-cols-2 gap-2 items-center">
              <input v-model="guardian.name" type="text" class="form-input" :placeholder="`Guardian ${index + 1} Name`" />
              <input v-model="guardian.phone" type="tel" class="form-input" placeholder="+2547XXXXXXX" />
              <div class="sm:col-span-2">
                <button type="button" @click="removeGuardian(index)" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm transition-colors duration-200" v-if="form.guardians.length > 1">
                  Remove
                </button>
              </div>
            </div>
            <button type="button" @click="addGuardian" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-1 transition-colors duration-200">+ Add Guardian</button>
          </div>
          <p v-if="form.errors.guardians" class="form-error mt-1">{{ form.errors.guardians }}</p>
        </div>

        <div>
          <label for="structure_id" class="form-label">Grade / Class</label>
          <select v-model="form.structure_id" id="structure_id" @change="updateStreams" class="form-input">
            <option disabled value="">Select class</option>
            <option v-for="structure in structures" :key="structure.id" :value="structure.id">{{ structure.title }}</option>
          </select>
          <p v-if="form.errors.structure_id" class="form-error">{{ form.errors.structure_id }}</p>
        </div>

        <div v-if="availableStreams.length">
          <label for="stream" class="form-label">Stream</label>
          <select v-model="form.stream" id="stream" class="form-input">
            <option disabled value="">Select stream</option>
            <option v-for="stream in availableStreams" :key="stream" :value="stream">{{ stream }}</option>
          </select>
          <p v-if="form.errors.stream" class="form-error">{{ form.errors.stream }}</p>
        </div>

        <div class="flex items-center gap-4 pt-4">
          <button type="submit" class="px-6 py-2 bg-blue-600 dark:bg-blue-500 text-white font-medium rounded-md hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-600/30 transition-all duration-200 disabled:opacity-60" :disabled="form.processing">
            <span v-if="form.processing" class="flex items-center gap-2">
              <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Submitting...
            </span>
            <span v-else>Create Student</span>
          </button>
          <button type="button" @click="closeModal" class="text-sm text-blue-600 dark:text-blue-400 hover:underline transition-colors duration-200">Cancel</button>
        </div>

        <p v-if="successMessage" class="text-green-600 dark:text-green-400 mt-2">{{ successMessage }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { useToast } from 'vue-toastification'
import FormGroup from '@/Components/FormGroup.vue'
import CalendarView from '@/components/CalendarView.vue'

const props = defineProps({ structures: Array })
const emit = defineEmits(['close'])

const toast = useToast()
const imagePreview = ref(null)
const availableStreams = ref([])

const form = useForm({
  full_name: '',
  dob: '',
  gender: '',
  email: '',
  guardians: [{ name: '', phone: '' }],
  structure_id: '',
  stream: '',
  profile_picture: null,
})

const page = usePage()
const successMessage = computed(() => page.props.flash.success)

function closeModal() {
  emit('close')
}

function addGuardian() {
  form.guardians.push({ name: '', phone: '' })
}

function removeGuardian(index) {
  form.guardians.splice(index, 1)
}

function handleImageUpload(event) {
  const file = event.target.files[0]
  if (file) {
    form.profile_picture = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

function updateStreams() {
  const selected = props.structures.find(s => s.id === form.structure_id)
  // Map to stream names only
  availableStreams.value = (selected?.streams ?? []).map(s => typeof s === 'string' ? s : s.name)
  form.stream = ''
}

function submitForm() {
  form.post('/school-admin/students', {
    forceFormData: true,
    onSuccess: () => {
      toast.success('Student account created successfully.')
      form.reset()
      imagePreview.value = null
      availableStreams.value = []
      closeModal()
    },
    onError: (errors) => {
      console.log('Form errors:', errors)
      toast.error(errors.form?.[0] || 'Something went wrong. Please try again.')
    },
  })
}
</script>

<style scoped>
.form-label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.25rem;
  color: #374151;
}
.dark .form-label {
  color: #e5e7eb;
}
.form-input {
  width: 100%;
  padding: 0.65rem 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db;
  background-color: #ffffff;
  color: #374151;
  transition: all 0.2s ease-in-out;
}
.dark .form-input {
  border-color: #4b5563;
  background-color: #374151;
  color: #e5e7eb;
}
.form-input:hover,
.form-input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
  outline: none;
}
.dark .form-input:hover,
.dark .form-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}
.form-input::placeholder {
  color: #9ca3af;
}
.dark .form-input::placeholder {
  color: #6b7280;
}
.form-error {
  font-size: 0.875rem;
  color: #dc2626;
  margin-top: 0.25rem;
}
.dark .form-error {
  color: #f87171;
}
</style>

