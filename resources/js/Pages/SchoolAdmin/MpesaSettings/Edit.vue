<template>
  <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit M-PESA Setting</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Update your M-PESA payment configuration</p>
      </div>

      <!-- Form -->
      <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Environment Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Environment
            </label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="form.environment"
                  value="sandbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                  <strong>Sandbox</strong> - For testing and development
                </span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="form.environment"
                  value="production"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                />
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                  <strong>Production</strong> - For live payments
                </span>
              </label>
            </div>
          </div>

          <!-- Consumer Key -->
          <div>
            <label for="consumer_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Consumer Key
            </label>
            <input
              id="consumer_key"
              v-model="form.consumer_key"
              type="text"
              required
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter your M-PESA Consumer Key"
            />
          </div>

          <!-- Consumer Secret -->
          <div>
            <label for="consumer_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Consumer Secret
            </label>
            <input
              id="consumer_secret"
              v-model="form.consumer_secret"
              type="password"
              required
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter your M-PESA Consumer Secret"
            />
          </div>

          <!-- Shortcode -->
          <div>
            <label for="shortcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Shortcode
            </label>
            <input
              id="shortcode"
              v-model="form.shortcode"
              type="text"
              required
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="e.g., 174379"
            />
          </div>

          <!-- Passkey -->
          <div>
            <label for="passkey" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Passkey
            </label>
            <input
              id="passkey"
              v-model="form.passkey"
              type="password"
              required
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter your M-PESA Passkey"
            />
          </div>

          <!-- Callback URL -->
          <div>
            <label for="callback_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Callback URL (Optional)
            </label>
            <input
              id="callback_url"
              v-model="form.callback_url"
              type="url"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="https://your-domain.com/api/mpesa/callback"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Leave empty to use the default callback URL
            </p>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Description (Optional)
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="e.g., Production M-PESA for ABC School"
            ></textarea>
          </div>

          <!-- Active Setting -->
          <div class="flex items-center">
            <input
              id="is_active"
              v-model="form.is_active"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
              Make this the active setting
            </label>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3">
            <Link
              :href="route('schooladmin.mpesa-settings.index')"
              class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-save mr-2"></i>
              {{ form.processing ? 'Updating...' : 'Update Setting' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import SchoolAdminLayout from '@/Layouts/SchoolAdminLayout.vue';

defineOptions({ layout: SchoolAdminLayout });

const props = defineProps({
  setting: Object,
});

const form = useForm({
  consumer_key: props.setting.consumer_key,
  consumer_secret: props.setting.consumer_secret,
  shortcode: props.setting.shortcode,
  passkey: props.setting.passkey,
  environment: props.setting.environment,
  callback_url: props.setting.callback_url || '',
  description: props.setting.description || '',
  is_active: props.setting.is_active,
});

function submit() {
  form.put(route('schooladmin.mpesa-settings.update', props.setting.id));
}
</script> 