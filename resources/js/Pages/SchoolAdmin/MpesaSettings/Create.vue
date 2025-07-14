<template>
  <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Add M-PESA Setting</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Configure a new M-PESA payment integration for your school</p>
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

          <!-- Help Text -->
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                  M-PESA Configuration Help
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                  <ul class="list-disc list-inside space-y-1">
                    <li>Get your credentials from the <a href="https://developer.safaricom.co.ke/" target="_blank" class="underline">M-PESA Daraja Portal</a></li>
                    <li>For sandbox testing, use the test credentials provided by Safaricom</li>
                    <li>For production, use your live M-PESA business credentials</li>
                    <li>Only one setting can be active at a time per school</li>
                  </ul>
                </div>
              </div>
            </div>
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
              {{ form.processing ? 'Saving...' : 'Save Setting' }}
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

const form = useForm({
  consumer_key: '',
  consumer_secret: '',
  shortcode: '',
  passkey: '',
  environment: 'sandbox',
  callback_url: '',
  description: '',
  is_active: true,
});

function submit() {
  try {
    console.log('Submitting form:', form.data());
    console.log('Form has errors:', Object.keys(form.errors).length > 0);
    console.log('Form is processing:', form.processing);
    
    // Check if all required fields are present
    const requiredFields = ['consumer_key', 'consumer_secret', 'shortcode', 'passkey', 'environment'];
    const missingFields = requiredFields.filter(field => !form[field]);
    
    if (missingFields.length > 0) {
      console.error('Missing required fields:', missingFields);
      alert('Please fill in all required fields: ' + missingFields.join(', '));
      return;
    }
    
    console.log('Route URL:', route('schooladmin.mpesa-settings.store'));
    
    form.post(route('schooladmin.mpesa-settings.store'), {
      onSuccess: () => {
        console.log('Form submitted successfully');
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
        alert('Form submission failed: ' + JSON.stringify(errors));
      },
      onFinish: () => {
        console.log('Form submission finished');
      }
    });
  } catch (error) {
    console.error('Error in submit function:', error);
    alert('An error occurred: ' + error.message);
  }
}
</script> 