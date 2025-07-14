<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 shadow rounded-lg transition-colors duration-200">
      <UpdateProfileInformationForm
        :must-verify-email="mustVerifyEmail"
        :status="status"
        class="max-w-xl"
      />
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 shadow rounded-lg transition-colors duration-200">
      <UpdatePasswordForm class="max-w-xl" />
    </div>
  </div>
</template>

<script setup>
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';

defineProps({
  mustVerifyEmail: {
    type: Boolean,
    default: false,
  },
  status: {
    type: String,
    default: '',
  },
});
</script> 