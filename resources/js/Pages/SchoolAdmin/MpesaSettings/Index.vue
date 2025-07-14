<template>
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">M-PESA Payment Settings</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Configure M-PESA payment integration for your school</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="showProductionTest = !showProductionTest"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <i class="fas fa-vial mr-2"></i>
            Test Production
          </button>
          <Link
            :href="route('schooladmin.mpesa-settings.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <i class="fas fa-plus mr-2"></i>
            Add New Setting
          </Link>
        </div>
      </div>

      <!-- Production Test Modal -->
      <div v-if="showProductionTest" class="mb-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Test Production M-PESA Credentials</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Test your production credentials before creating a setting</p>
        </div>
        
        <form @submit.prevent="testProductionCredentials" class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Consumer Key</label>
              <input
                v-model="productionTest.consumer_key"
                type="text"
                required
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Production Consumer Key"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Consumer Secret</label>
              <input
                v-model="productionTest.consumer_secret"
                type="password"
                required
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Production Consumer Secret"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shortcode</label>
              <input
                v-model="productionTest.shortcode"
                type="text"
                required
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Production Shortcode"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Passkey</label>
              <input
                v-model="productionTest.passkey"
                type="password"
                required
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Production Passkey"
              />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Callback URL (Optional)</label>
            <input
              v-model="productionTest.callback_url"
              type="url"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="https://your-domain.com/api/mpesa/callback"
            />
          </div>

          <!-- Test Results -->
          <div v-if="productionTestResult" class="mt-4 p-4 rounded-lg" :class="productionTestResult.success ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700'">
            <div class="flex">
              <div class="flex-shrink-0">
                <i :class="productionTestResult.success ? 'fas fa-check-circle text-green-400' : 'fas fa-exclamation-circle text-red-400'"></i>
              </div>
              <div class="ml-3">
                <h4 class="text-sm font-medium" :class="productionTestResult.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'">
                  {{ productionTestResult.message }}
                </h4>
                <div v-if="productionTestResult.recommendations" class="mt-2 text-sm" :class="productionTestResult.success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
                  <ul class="list-disc list-inside space-y-1">
                    <li v-for="rec in productionTestResult.recommendations" :key="rec">{{ rec }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showProductionTest = false"
              class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="testingProduction"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
            >
              <i v-if="testingProduction" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-vial mr-2"></i>
              {{ testingProduction ? 'Testing...' : 'Test Credentials' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Active Setting Alert -->
      <div v-if="activeSetting" class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-400"></i>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
              Active M-PESA Setting
            </h3>
            <div class="mt-2 text-sm text-green-700 dark:text-green-300">
              <p><strong>Shortcode:</strong> {{ activeSetting.shortcode }}</p>
              <p><strong>Environment:</strong> {{ activeSetting.environment }}</p>
              <p v-if="activeSetting.description"><strong>Description:</strong> {{ activeSetting.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Settings List -->
      <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
          <li v-for="setting in settings" :key="setting.id" class="px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                    <i class="fas fa-mobile-alt text-blue-600 dark:text-blue-400"></i>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="flex items-center">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ setting.shortcode }}
                    </h3>
                    <span v-if="setting.is_active" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                      Active
                    </span>
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="setting.environment === 'sandbox' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'">
                      {{ setting.environment }}
                    </span>
                  </div>
                  <p v-if="setting.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ setting.description }}
                  </p>
                  <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    Created {{ formatDate(setting.created_at) }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="testCredentials(setting)"
                  :disabled="testing === setting.id"
                  class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                >
                  <i v-if="testing === setting.id" class="fas fa-spinner fa-spin mr-1"></i>
                  <i v-else class="fas fa-vial mr-1"></i>
                  {{ testing === setting.id ? 'Testing...' : 'Test' }}
                </button>
                
                <button
                  v-if="!setting.is_active"
                  @click="activateSetting(setting)"
                  :disabled="activating === setting.id"
                  class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                >
                  <i v-if="activating === setting.id" class="fas fa-spinner fa-spin mr-1"></i>
                  <i v-else class="fas fa-check mr-1"></i>
                  {{ activating === setting.id ? 'Activating...' : 'Activate' }}
                </button>

                <Link
                  :href="route('schooladmin.mpesa-settings.edit', setting.id)"
                  class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <i class="fas fa-edit mr-1"></i>
                  Edit
                </Link>

                <button
                  @click="deleteSetting(setting)"
                  :disabled="deleting === setting.id"
                  class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                >
                  <i v-if="deleting === setting.id" class="fas fa-spinner fa-spin mr-1"></i>
                  <i v-else class="fas fa-trash mr-1"></i>
                  {{ deleting === setting.id ? 'Deleting...' : 'Delete' }}
                </button>
              </div>
            </div>
          </li>
        </ul>

        <!-- Empty State -->
        <div v-if="settings.length === 0" class="text-center py-12">
          <i class="fas fa-mobile-alt text-gray-400 text-4xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No M-PESA settings configured</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">
            Get started by adding your first M-PESA payment configuration.
          </p>
          <Link
            :href="route('schooladmin.mpesa-settings.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <i class="fas fa-plus mr-2"></i>
            Add First Setting
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SchoolAdminLayout from '@/Layouts/SchoolAdminLayout.vue';

defineOptions({ layout: SchoolAdminLayout });

const props = defineProps({
  settings: Array,
  activeSetting: Object,
  school: Object,
});

const testing = ref(null);
const activating = ref(null);
const deleting = ref(null);
const showProductionTest = ref(false);
const testingProduction = ref(false);
const productionTestResult = ref(null);

const productionTest = ref({
  consumer_key: '',
  consumer_secret: '',
  shortcode: '',
  passkey: '',
  callback_url: ''
});

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString();
}

async function testCredentials(setting) {
  testing.value = setting.id;
  
  try {
    const response = await fetch(route('schooladmin.mpesa-settings.test', setting.id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('✅ Credentials are valid! Access token generated successfully.');
    } else {
      alert('❌ Credentials test failed: ' + data.message);
    }
  } catch (error) {
    alert('❌ Error testing credentials: ' + error.message);
  } finally {
    testing.value = null;
  }
}

async function activateSetting(setting) {
  if (!confirm('Are you sure you want to activate this M-PESA setting? This will deactivate all other settings.')) {
    return;
  }
  
  activating.value = setting.id;
  
  try {
    await router.post(route('schooladmin.mpesa-settings.activate', setting.id));
  } catch (error) {
    alert('Error activating setting: ' + error.message);
  } finally {
    activating.value = null;
  }
}

async function deleteSetting(setting) {
  if (!confirm('Are you sure you want to delete this M-PESA setting? This action cannot be undone.')) {
    return;
  }
  
  deleting.value = setting.id;
  
  try {
    await router.delete(route('schooladmin.mpesa-settings.destroy', setting.id));
  } catch (error) {
    alert('Error deleting setting: ' + error.message);
  } finally {
    deleting.value = null;
  }
}

async function testProductionCredentials() {
  testingProduction.value = true;
  productionTestResult.value = null;
  
  try {
    const response = await fetch(route('schooladmin.mpesa-settings.test-production'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(productionTest.value)
    });
    
    const data = await response.json();
    productionTestResult.value = data;
    
    if (data.success) {
      console.log('Production test successful:', data);
    } else {
      console.error('Production test failed:', data);
    }
  } catch (error) {
    productionTestResult.value = {
      success: false,
      message: 'Network error: ' + error.message,
      recommendations: [
        '❌ Check your internet connection',
        '❌ Verify the server is accessible',
        '❌ Try again in a few moments'
      ]
    };
    console.error('Production test error:', error);
  } finally {
    testingProduction.value = false;
  }
}
</script> 