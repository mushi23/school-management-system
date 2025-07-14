<template>
  <div class="space-y-6">
    <!-- School Profile Section -->
    <div class="bg-white dark:bg-gray-900 p-6 shadow rounded-lg transition-colors duration-200">
      <header>
        <h2 class="text-lg font-medium text-white flex items-center">
          <i class="fas fa-school mr-2 text-green-600"></i>
          School Profile
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Manage your school's branding and visual identity.
        </p>
      </header>
      <div class="mt-6">
        <SchoolProfileSettings :school="school" />
      </div>
    </div>

    <!-- User Profile Section -->
    <div class="bg-white dark:bg-gray-900 p-6 shadow rounded-lg transition-colors duration-200">
      <header>
        <h2 class="text-lg font-medium text-white flex items-center">
          <i class="fas fa-user mr-2 text-blue-600"></i>
          User Profile
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Update your personal account information and password.
        </p>
      </header>
      <div class="mt-6">
        <UpdateProfileInformationForm
          :must-verify-email="mustVerifyEmail"
          :status="status"
          class="max-w-xl"
        />
      </div>
    </div>

    <div class="bg-white dark:bg-gray-900 p-6 shadow rounded-lg transition-colors duration-200">
      <UpdatePasswordForm class="max-w-xl" />
    </div>
  </div>
</template>

<script setup>
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import SchoolProfileSettings from '@/Pages/SchoolAdmin/SchoolProfileSettings.vue';

defineProps({
  mustVerifyEmail: {
    type: Boolean,
    default: false,
  },
  status: {
    type: String,
    default: '',
  },
  school: {
    type: Object,
    required: true
  }
});
</script> 