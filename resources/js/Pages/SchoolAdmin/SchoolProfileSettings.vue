<template>
  <div class="space-y-6">
    <!-- School Profile Header -->
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden transition-colors duration-200">
      <div class="px-6 py-8">
        <div class="text-center">
          <div class="w-24 h-24 rounded-full border-4 border-blue-200 dark:border-blue-800 bg-blue-100 dark:bg-blue-900 mx-auto mb-4 flex items-center justify-center">
            <i class="fas fa-school text-3xl text-blue-600 dark:text-blue-400"></i>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            School Profile Settings
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Update your school's basic information and branding details
          </p>
        </div>
      </div>
    </div>

    <!-- School Details Form -->
    <div class="bg-white dark:bg-gray-900 p-6 shadow rounded-lg transition-colors duration-200">
      <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
          <i class="fas fa-edit mr-2 text-blue-600"></i>
          School Information
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Update your school's basic information and branding details.
        </p>
      </header>

      <form @submit.prevent="updateSchoolBranding" class="mt-6 space-y-6">
        <!-- School Name -->
        <div>
          <InputLabel for="school_name" value="School Name" />
          <TextInput
            id="school_name"
            type="text"
            class="mt-1 block w-full"
            v-model="brandingForm.name"
            required
            autocomplete="organization"
          />
          <InputError class="mt-2" :message="brandingForm.errors.name" />
        </div>

        <!-- Slogan -->
        <div>
          <InputLabel for="slogan" value="School Slogan" />
          <TextInput
            id="slogan"
            type="text"
            class="mt-1 block w-full"
            v-model="brandingForm.slogan"
            placeholder="e.g., Excellence in Education"
          />
          <InputError class="mt-2" :message="brandingForm.errors.slogan" />
        </div>

        <!-- Brand Color -->
        <div>
          <InputLabel for="brand_color" value="Brand Color" />
          <div class="flex items-center space-x-3 mt-1">
            <input
              id="brand_color"
              type="color"
              v-model="brandingForm.brand_color"
              class="w-12 h-10 border border-gray-300 dark:border-gray-600 rounded cursor-pointer"
            />
            <TextInput
              type="text"
              class="flex-1"
              v-model="brandingForm.brand_color"
              placeholder="#2563eb"
            />
          </div>
          <InputError class="mt-2" :message="brandingForm.errors.brand_color" />
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
          <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
            <i class="fas fa-info-circle mr-1"></i>
            Image Management
          </h3>
          <p class="text-sm text-blue-700 dark:text-blue-300">
            Logo and background images can be updated directly from the dashboard. 
            Go to the dashboard and click on the camera icons to change images instantly.
          </p>
        </div>

        <div class="flex items-center gap-4">
          <PrimaryButton :disabled="brandingForm.processing">Save Details</PrimaryButton>
          <Transition
            enter-active-class="transition ease-in-out"
            enter-from-class="opacity-0"
            leave-active-class="transition ease-in-out"
            leave-to-class="opacity-0"
          >
            <p v-if="brandingForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
              School details updated successfully.
            </p>
          </Transition>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  school: {
    type: Object,
    required: true
  }
});

// Branding form (text fields only)
const brandingForm = useForm({
  name: props.school.name || '',
  slogan: props.school.slogan || '',
  brand_color: props.school.brand_color || '#2563eb',
});

// Form submission methods
const updateSchoolBranding = () => {
  // Use Inertia's form submission for text fields only
  brandingForm.post(route('schooladmin.profile.branding'), {
    preserveScroll: true,
  });
};
</script> 