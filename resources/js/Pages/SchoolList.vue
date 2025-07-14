<template>
  <div>
    <h2 class="text-xl font-bold mb-2 dark:text-white">🏫 Registered Schools</h2>

    <!-- Enhanced Filter Section -->
    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
      <!-- Basic Filter Buttons -->
      <div class="mb-4">
        <button
          :class="['px-4 py-2 mr-2 rounded', filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white']"
          @click="setFilter('all')"
        >
          All
        </button>
        <button
          :class="['px-4 py-2 rounded', filter === 'deleted' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white']"
          @click="setFilter('deleted')"
        >
          Deleted Schools
        </button>
        <button
          :class="['px-4 py-2 rounded', filter === 'no_curriculum' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white']"
          @click="setFilter('no_curriculum')"
        >
          No Curriculum
        </button>
      </div>

      <!-- Advanced Filters -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Schools</label>
          <input
            v-model="advancedFilters.search"
            type="text"
            placeholder="Search"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
            @input="debouncedFetchSchools"
          />
        </div>

        <!-- County Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">County</label>
          <select
            v-model="advancedFilters.county"
            @change="fetchSchools"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          >
            <option value="">All Counties</option>
            <option v-for="county in availableCounties" :key="county" :value="county">{{ county }}</option>
          </select>
        </div>

        <!-- Sub County Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sub County</label>
          <select
            v-model="advancedFilters.sub_county"
            @change="fetchSchools"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          >
            <option value="">All Sub Counties</option>
            <option v-for="subCounty in availableSubCounties" :key="subCounty" :value="subCounty">{{ subCounty }}</option>
          </select>
        </div>
      </div>

      <!-- Sort and Display Options -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order</label>
          <select
            v-model="advancedFilters.sort_direction"
            @change="fetchSchools"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          >
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
          </select>
        </div>

        <div class="flex items-end">
          <button
            @click="clearAdvancedFilters"
            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-gray-600 dark:text-gray-300">Loading schools...</div>

    <div v-else>
      <!-- Results Summary -->
      <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
        Showing {{ filteredSchools.length }} of {{ schools.length }} schools
        <span v-if="hasActiveFilters" class="ml-2 text-blue-600 dark:text-blue-400">(filtered)</span>
      </div>

      <!-- Table with responsive wrapper -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
          <thead>
            <tr class="bg-gray-100 dark:bg-gray-700 text-left text-gray-900 dark:text-gray-100">
              <th class="py-2 px-4">#</th>
              <th class="py-2 px-4">School Name</th>
              <th class="py-2 px-4">Location</th>
              <th class="py-2 px-4">Contact Info</th>
              <th class="py-2 px-4">Admin User</th>
              <th class="py-2 px-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(school, index) in filteredSchools"
              :key="school.id"
              @click="handleSchoolClick(school)"
              class="cursor-pointer"
              :class="[
                'transition-colors hover:bg-gray-50 dark:hover:bg-gray-700',
                school.deleted_at ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : 'text-gray-800 dark:text-gray-100'
              ]"
            >
              <td class="py-3 px-4">{{ index + 1 }}</td>
              
              <!-- School Name & Registration -->
              <td class="py-3 px-4">
                <div class="flex flex-col">
                  <span class="font-medium">{{ school.name }}</span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ school.registration_number }}
                  </span>
                  <div class="flex items-center mt-1 space-x-2">
                    <span v-if="school.active" class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                      <i class="fas fa-check-circle mr-1"></i>
                      Active
                    </span>
                    <span v-else class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                      <i class="fas fa-times-circle mr-1"></i>
                      Inactive
                    </span>
                    <span v-if="school.deleted_at" class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200">
                      <i class="fas fa-trash mr-1"></i>
                      Deleted
                    </span>
                    <span v-if="!school.curriculum_key" class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100 ml-2">
                      <i class="fas fa-exclamation-triangle mr-1"></i>
                      No Curriculum
                    </span>
                  </div>
                </div>
              </td>
              
              <!-- Location -->
              <td class="py-3 px-4">
                <div class="flex flex-col text-sm">
                  <span>{{ school.county || 'N/A' }}</span>
                  <span class="text-gray-500 dark:text-gray-400">{{ school.sub_county || 'N/A' }}</span>
                </div>
              </td>
              
              <!-- Contact Info -->
              <td class="py-3 px-4">
                <div class="flex flex-col text-sm">
                  <span class="truncate max-w-xs" :title="school.contact_email">{{ school.contact_email }}</span>
                  <span class="text-gray-500 dark:text-gray-400">{{ school.contact_phone }}</span>
                </div>
              </td>
              
              <!-- Admin User Info -->
              <td class="py-3 px-4">
                <div v-if="school.admin_user" class="flex flex-col text-sm">
                  <span class="font-medium">{{ school.admin_user.name }}</span>
                  <span class="text-gray-500 dark:text-gray-400 truncate max-w-xs" :title="school.admin_user.email">
                    {{ school.admin_user.email }}
                  </span>
                  <div class="flex items-center mt-1 space-x-2">
                    <span v-if="school.admin_user.active" class="inline-flex items-center px-1 py-0.5 rounded text-xs bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200">
                      <i class="fas fa-user-check mr-1"></i>
                      Active
                    </span>
                    <span v-else class="inline-flex items-center px-1 py-0.5 rounded text-xs bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200">
                      <i class="fas fa-user-times mr-1"></i>
                      Inactive
                    </span>
                    <span v-if="school.admin_user.email_verified_at" class="inline-flex items-center px-1 py-0.5 rounded text-xs bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200">
                      <i class="fas fa-envelope-check mr-1"></i>
                      Verified
                    </span>
                    <span v-else class="inline-flex items-center px-1 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-200">
                      <i class="fas fa-envelope mr-1"></i>
                      Unverified
                    </span>
                  </div>
                  <span class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    {{ school.admin_user.phone || 'No phone' }}
                  </span>
                </div>
                <div v-else class="text-sm text-red-500 dark:text-red-400">
                  <i class="fas fa-exclamation-triangle mr-1"></i>
                  No admin user
                </div>
              </td>
              
              <!-- Actions -->
              <td class="py-3 px-4">
                <div class="flex justify-center space-x-2">
                  <template v-if="school.deleted_at">
                    <button
                      @click.stop="confirmRestoreSchool(school.id)"
                      title="Restore School"
                      class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 backdrop-blur-sm border border-gray-200 dark:border-gray-600 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all"
                    >
                      <i class="fas fa-undo text-green-600"></i>
                    </button>
                    <button
                      @click.stop="confirmPermanentlyDeleteSchool(school.id)"
                      title="Permanently Delete"
                      class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 backdrop-blur-sm border border-gray-200 dark:border-gray-600 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all"
                    >
                      <i class="fas fa-ban text-red-700"></i>
                    </button>
                  </template>
                  <template v-else>
                    <button
                      v-if="school.active"
                      @click.stop="confirmDisableSchool(school.id)"
                      title="Disable School"
                      class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 backdrop-blur-sm border border-gray-200 dark:border-gray-600 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all group"
                    >
                      <div class="relative">
                        <i class="fas fa-user-slash text-yellow-600"></i>
                        <span class="absolute -bottom-5 left-1/2 transform -translate-x-1/2 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                          Disable
                        </span>
                      </div>
                    </button>
                    <button
                      v-else
                      @click.stop="confirmEnableSchool(school.id)"
                      title="Enable School"
                      class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 backdrop-blur-sm border border-gray-200 dark:border-gray-600 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all group"
                    >
                      <div class="relative">
                        <i class="fas fa-user-check text-green-600"></i>
                        <span class="absolute -bottom-5 left-1/2 transform -translate-x-1/2 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                          Enable
                        </span>
                      </div>
                    </button>

                    <button
                      @click.stop="confirmDeleteSchool(school.id)"
                      title="Delete School"
                      class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 backdrop-blur-sm border border-gray-200 dark:border-gray-600 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all group"
                    >
                      <div class="relative">
                        <i class="fas fa-trash text-red-600"></i>
                        <span class="absolute -bottom-5 left-1/2 transform -translate-x-1/2 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                          Delete
                        </span>
                      </div>
                    </button>

                    <button
                      @click.stop="confirmResetPassword(school.id, school.contact_email)"
                      title="Reset Admin Password"
                      class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 backdrop-blur-sm border border-gray-200 dark:border-gray-600 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all group"
                    >
                      <div class="relative">
                        <i class="fas fa-unlock text-blue-600"></i>
                        <span class="absolute -bottom-5 left-1/2 transform -translate-x-1/2 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                          Reset
                        </span>
                      </div>
                    </button>

                    <button
                      @click.stop="handleSchoolClick(school)"
                      class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 backdrop-blur-sm border border-gray-200 dark:border-gray-600 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all"
                      title="Manage Users"
                    >
                      <i class="fas fa-users text-purple-600"></i>
                    </button>
                  </template>
                </div>
              </td>
            </tr>
            <tr v-if="filteredSchools.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-500 dark:text-gray-400">
                <i class="fas fa-school text-4xl mb-2"></i>
                <div>No schools to display.</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="error" class="text-red-600 dark:text-red-400 mt-4">{{ error }}</div>

    <!-- User Management Modal - Now placed at the bottom of the template -->
    <!-- User Management Modal -->
<Modal :show="showUserEditModal" @close="showUserEditModal = false">
  <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
    <h2 class="text-xl font-bold mb-4 dark:text-white">Manage Users for {{ selectedSchool?.name }}</h2>
    
    <!-- Users Table -->
    <div class="overflow-x-auto mb-6">
      <table class="min-w-full bg-white dark:bg-gray-700 rounded shadow">
        <thead>
          <tr class="bg-gray-100 dark:bg-gray-600 text-left text-gray-900 dark:text-gray-100">
            <th class="py-2 px-4">Name</th>
            <th class="py-2 px-4">Email</th>
            <th class="py-2 px-4">Status</th>
            <th class="py-2 px-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in schoolUsers" :key="user.id" class="border-b border-gray-200 dark:border-gray-600">
            <td class="py-3 px-4">{{ user.name }}</td>
            <td class="py-3 px-4">{{ user.email }}</td>
            <td class="py-3 px-4">
              <span v-if="user.active" class="text-green-600 dark:text-green-400">Active</span>
              <span v-else class="text-red-600 dark:text-red-400">Inactive</span>
            </td>
            <td class="py-3 px-4">
              <button
                @click="editUser(user)"
                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
              >
                Edit
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</Modal>

<!-- Edit User Modal -->
<Modal :show="editingUser !== null" @close="editingUser = null">
  <div class="p-6 bg-white dark:bg-gray-800 rounded-lg">
    <h2 class="text-xl font-bold mb-4 dark:text-white">Edit User</h2>
    
    <form @submit.prevent="updateUser">
      <div class="mb-4">
        <label class="block text-gray-700 dark:text-gray-300 mb-2">Name</label>
        <input
          v-model="userForm.name"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
          required
        >
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 dark:text-gray-300 mb-2">Email</label>
        <input
          v-model="userForm.email"
          type="email"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
          required
        >
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 dark:text-gray-300 mb-2">Phone</label>
        <input
          v-model="userForm.phone"
          type="tel"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
        >
      </div>
      
      <div class="mb-4">
        <label class="flex items-center">
          <input
            v-model="userForm.active"
            type="checkbox"
            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
          >
          <span class="ml-2 text-gray-700 dark:text-gray-300">Active</span>
        </label>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="editingUser = null"
          class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
        >
          Save Changes
        </button>
      </div>
    </form>
  </div>
</Modal>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { debounce } from 'lodash';
import Modal from '@/Components/Modal.vue'

const toast = useToast();

const schools = ref([]);
const loading = ref(true);
const error = ref(null);

// Original filter state: 'all', 'active', 'deleted'
const filter = ref('all');

// Advanced filters
const advancedFilters = ref({
  search: '',
  county: '',
  sub_county: '',
  min_students: '',
  max_students: '',
  sort_by: 'name',
  sort_direction: 'asc'
});

// Computed properties for filter options
const availableCounties = computed(() => {
  const counties = [...new Set(schools.value.map(s => s.county).filter(Boolean))];
  return counties.sort();
});

const availableSubCounties = computed(() => {
  const subCounties = [...new Set(schools.value.map(s => s.sub_county).filter(Boolean))];
  return subCounties.sort();
});

const hasActiveFilters = computed(() => {
  return advancedFilters.value.search ||
         advancedFilters.value.county ||
         advancedFilters.value.sub_county ||
         advancedFilters.value.min_students ||
         advancedFilters.value.max_students ||
         filter.value !== 'all';
});

// Enhanced filtered schools with advanced filtering including admin user search
const filteredSchools = computed(() => {
  let filtered = schools.value;

  // Apply basic filter (all/active/deleted)
  if (filter.value === 'active') {
    filtered = filtered.filter(s => !s.deleted_at);
  } else if (filter.value === 'deleted') {
    filtered = filtered.filter(s => s.deleted_at);
  } else if (filter.value === 'no_curriculum') {
    filtered = filtered.filter(s => !('curriculum_key' in s) || s.curriculum_key === null || s.curriculum_key === '');
  }

  // Apply advanced filters
  if (advancedFilters.value.search) {
    const search = advancedFilters.value.search.toLowerCase();
    filtered = filtered.filter(s => 
      s.name.toLowerCase().includes(search) ||
      s.contact_email.toLowerCase().includes(search) ||
      (s.county && s.county.toLowerCase().includes(search)) ||
      (s.sub_county && s.sub_county.toLowerCase().includes(search)) ||
      (s.admin_user && s.admin_user.name.toLowerCase().includes(search)) ||
      (s.admin_user && s.admin_user.email.toLowerCase().includes(search))
    );
  }

  if (advancedFilters.value.county) {
    filtered = filtered.filter(s => s.county === advancedFilters.value.county);
  }

  if (advancedFilters.value.sub_county) {
    filtered = filtered.filter(s => s.sub_county === advancedFilters.value.sub_county);
  }

  if (advancedFilters.value.min_students) {
    filtered = filtered.filter(s => s.total_students >= parseInt(advancedFilters.value.min_students));
  }

  if (advancedFilters.value.max_students) {
    filtered = filtered.filter(s => s.total_students <= parseInt(advancedFilters.value.max_students));
  }

  // Apply sorting
  const sortBy = advancedFilters.value.sort_by;
  const sortDirection = advancedFilters.value.sort_direction;

  filtered = filtered.sort((a, b) => {
    let aVal = a[sortBy];
    let bVal = b[sortBy];

    // Handle null/undefined values
    if (aVal == null) aVal = '';
    if (bVal == null) bVal = '';

    // Convert to string for comparison if needed
    if (typeof aVal === 'string') {
      aVal = aVal.toLowerCase();
      bVal = bVal.toLowerCase();
    }

    let comparison = 0;
    if (aVal > bVal) comparison = 1;
    if (aVal < bVal) comparison = -1;

    return sortDirection === 'desc' ? -comparison : comparison;
  });

  return filtered;
});

// Create debounced function for search
const debouncedFetchSchools = debounce(() => {
  // For client-side filtering, we don't need to refetch
  // The computed property will handle the filtering
}, 300);

const fetchSchools = async () => {
  try {
    loading.value = true;
    error.value = null;

    await axios.get('/sanctum/csrf-cookie');
    await axios.get('/api/me');

    // Build query parameters for server-side filtering
    const params = new URLSearchParams();
    
    // Add advanced filters as query parameters
    if (advancedFilters.value.search) params.append('search', advancedFilters.value.search);
    if (advancedFilters.value.county) params.append('county', advancedFilters.value.county);
    if (advancedFilters.value.sub_county) params.append('sub_county', advancedFilters.value.sub_county);
    if (advancedFilters.value.min_students) params.append('min_students', advancedFilters.value.min_students);
    if (advancedFilters.value.max_students) params.append('max_students', advancedFilters.value.max_students);
    
    // Add sorting
    params.append('sort_by', advancedFilters.value.sort_by);
    params.append('sort_direction', advancedFilters.value.sort_direction);
    
    // Add deleted filter based on current filter state
    if (filter.value === 'active') {
      params.append('deleted', 'false');
    } else if (filter.value === 'deleted') {
      params.append('deleted', 'only');
    }
    
    // Disable pagination to get all results for client-side filtering
    params.append('paginate', 'false');

    const queryString = params.toString();
    const url = queryString ? `/api/admin/schools?${queryString}` : '/api/admin/schools';
    
    const response = await axios.get(url);
    schools.value = response.data;
  } catch (err) {
    console.error('Failed to fetch schools:', err);
    error.value =
      err.response?.data?.message ||
      err.message ||
      'Failed to fetch schools. Please check console for details.';
    toast.error(error.value, { timeout: 2000 });
  } finally {
    loading.value = false;
  }
};

const setFilter = (newFilter) => {
  filter.value = newFilter;
  fetchSchools(); // Refetch with new filter
};

const clearAdvancedFilters = () => {
  advancedFilters.value = {
    search: '',
    county: '',
    sub_county: '',
    min_students: '',
    max_students: '',
    sort_by: 'name',
    sort_direction: 'asc'
  };
  fetchSchools();
};

// Watch for changes in advanced filters that should trigger server requests
watch(
  () => [advancedFilters.value.county, advancedFilters.value.sub_county, advancedFilters.value.min_students, advancedFilters.value.max_students, advancedFilters.value.sort_by, advancedFilters.value.sort_direction],
  () => {
    fetchSchools();
  },
  { deep: true }
);

// Confirmation helper using window.confirm but you can replace with a modal if you want
const confirmAction = (message) => window.confirm(message);

const disableSchool = async (id) => {
  try {
    await axios.post(`/api/admin/schools/${id}/disable`);
    toast.success('School disabled successfully.', { timeout: 2000 });
    await fetchSchools();
  } catch (err) {
    console.error('Failed to disable school:', err);
    toast.error(err.response?.data?.message || 'Failed to disable school.', { timeout: 2000 });
  }
};

const enableSchool = async (id) => {
  try {
    await axios.post(`/api/admin/schools/${id}/enable`);
    toast.success('School enabled successfully.', { timeout: 2000 });
    await fetchSchools();
  } catch (err) {
    console.error('Failed to enable school:', err);
    toast.error(err.response?.data?.message || 'Failed to enable school.', { timeout: 2000 });
  }
};

const deleteSchool = async (id) => {
  try {
    await axios.delete(`/api/admin/schools/${id}`);
    toast.success('School soft deleted.', { timeout: 2000 });
    await fetchSchools();
  } catch (err) {
    console.error('Failed to delete school:', err);
    toast.error(err.response?.data?.message || 'Failed to delete school.', { timeout: 2000 });
  }
};

const permanentlyDeleteSchool = async (id) => {
  try {
    await axios.delete(`/api/admin/schools/${id}/force`);
    toast.success('School permanently deleted.', { timeout: 2000 });
    await fetchSchools();
  } catch (err) {
    console.error('Permanent delete failed:', err);
    toast.error(err.response?.data?.message || 'Failed to permanently delete school.', { timeout: 2000 });
  }
};

const restoreSchool = async (id) => {
  try {
    await axios.post(`/api/admin/schools/${id}/restore`);
    toast.success('School restored.', { timeout: 2000 });
    await fetchSchools();
  } catch (err) {
    console.error('Failed to restore school:', err);
    toast.error(err.response?.data?.message || 'Failed to restore school.', { timeout: 2000 });
  }
};

const resetPassword = async (id, email) => {
  try {
    const response = await axios.post(`/api/admin/schools/${id}/reset-password`, { email });
    toast.success(response.data.message || 'Password reset successfully.', { timeout: 2000 });
  } catch (err) {
    console.error('Failed to reset password:', err);
    toast.error(err.response?.data?.message || 'Failed to reset password.', { timeout: 2000 });
  }
};

// Confirm wrappers to add confirmation dialogs before the action
const confirmDisableSchool = async (id) => {
  if (!confirmAction('Are you sure you want to disable this school?')) return;
  await disableSchool(id);
};

const confirmEnableSchool = async (id) => {
  if (!confirmAction('Are you sure you want to enable this school?')) return;
  await enableSchool(id);
};

const confirmDeleteSchool = async (id) => {
  if (!confirmAction('Are you sure you want to delete this school? This action is reversible.')) return;
  await deleteSchool(id);
};

const confirmPermanentlyDeleteSchool = async (id) => {
  if (!confirmAction('⚠️ This will permanently delete the school and all its users. This cannot be undone. Proceed?')) return;
  await permanentlyDeleteSchool(id);
};

const confirmRestoreSchool = async (id) => {
  if (!confirmAction('Are you sure you want to restore this school?')) return;
  await restoreSchool(id);
};

const confirmResetPassword = async (id, email) => {
  if (!confirmAction('Are you sure you want to reset the admin password for this school?')) return;
  await resetPassword(id, email);
};

// Replace the data() and methods with these declarations
const selectedSchool = ref(null);
const schoolUsers = ref([]);
const showUserEditModal = ref(false);
const editingUser = ref(null);
const userForm = ref({
  name: '',
  email: '',
  phone: '',
  active: true
});

const handleSchoolClick = async (school) => {
  if (!school?.id) {
    console.error('Invalid school object:', school);
    return;
  }

  const schoolId = school.id;
  selectedSchool.value = school;
  try {
    const response = axios.get(`/api/admin/schools/${schoolId}/users`)
    schoolUsers.value = response.data;
    showUserEditModal.value = true;
  } catch (error) {
    console.error('Error fetching users:', error);
    toast.error('Failed to fetch users', { timeout: 2000 });
  }
};

const editUser = (user) => {
  editingUser.value = user;
  userForm.value = {
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    active: user.active
  };
};

const updateUser = async () => {
  try {
    const response = await axios.put(
      `/api/users/${editingUser.value.id}`,
      userForm.value
    );
    
    // Update local data
    const index = schoolUsers.value.findIndex(u => u.id === editingUser.value.id);
    schoolUsers.value.splice(index, 1, response.data.user);
    
    // Show success message
    showUserEditModal.value = false;
    toast.success('User updated successfully', { timeout: 2000 });
  } catch (error) {
    console.error('Error updating user:', error);
    toast.error('Failed to update user', { timeout: 2000 });
  }
};

onMounted(() => {
  fetchSchools();
});

</script>
