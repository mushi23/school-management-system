<template>
  <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar -->
    <nav class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 p-6 flex flex-col">
      <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Admin Panel</h2>
      <ul class="space-y-4 flex-1">
  <li>
    <button
      @click="view = 'dashboard'"
      class="block w-full text-left py-2 px-4 rounded hover:bg-primary hover:text-white transition text-gray-700 dark:text-gray-200"
    >
      Dashboard
    </button>
  </li>
  <li>
    <button
      @click="view = 'schools'"
      class="block w-full text-left py-2 px-4 rounded hover:bg-primary hover:text-white transition text-gray-700 dark:text-gray-200"
    >
      Schools
    </button>
  </li>
  <li>
    <button
      @click="view = 'users'"
      class="block w-full text-left py-2 px-4 rounded hover:bg-primary hover:text-white transition text-gray-700 dark:text-gray-200"
    >
      Users
    </button>
  </li>
</ul>


      <!-- Theme Toggle Button -->
      <button
        @click="toggleTheme"
        class="mt-6 px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-primary hover:text-white transition flex items-center justify-center"
      >
        <span v-if="theme === 'light'" class="flex items-center">
          <i class="fas fa-moon mr-2"></i>
          Dark Mode
        </span>
        <span v-else class="flex items-center">
          <i class="fas fa-sun mr-2 text-yellow-500"></i>
          Light Mode
        </span>
      </button>

      <div class="text-sm text-gray-500 dark:text-gray-400 mt-6">&copy; 2025 YourCompany</div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      <!-- Top nav -->
      <header class="bg-white dark:bg-gray-800 shadow px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ view === 'dashboard' ? 'Dashboard' : view === 'schools' ? 'Schools' : 'Users' }}</h1>
        <div class="flex items-center space-x-4">
          <!-- Profile Dropdown -->
          <div class="relative">
            <button
              @click="showProfileDropdown = !showProfileDropdown"
              class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
            >
              <i class="fas fa-user-circle text-2xl text-gray-600 dark:text-gray-300"></i>
              <span class="text-sm text-gray-600 dark:text-gray-300">{{ $page.props.auth.user.email }}</span>
              <i class="fas fa-chevron-down text-xs text-gray-500 dark:text-gray-400"></i>
            </button>
            
            <!-- Profile Dropdown Menu -->
            <div
              v-if="showProfileDropdown"
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
            >
              <div class="py-2">
                <button
                  @click="showProfileModal = true; showProfileDropdown = false"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                  <i class="fas fa-user-edit mr-2"></i>
                  Profile Settings
                </button>
                <hr class="my-1 border-gray-200 dark:border-gray-700">
                <a
                  href="#"
                  class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                  <i class="fas fa-sign-out-alt mr-2"></i>
                  Logout
                </a>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <div class="flex-1 p-8 overflow-auto text-gray-900 dark:text-gray-100">
      <!-- Dashboard View -->
      <template v-if="view === 'dashboard'">
        <h1 class="text-3xl font-semibold mb-8">Welcome, Admin</h1>

        <!-- Create School Button -->
        <button
          @click="openCreateSchool"
          class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded transition flex items-center justify-center"
          :disabled="loadingCreateSchool"
        >
          <template v-if="loadingCreateSchool">
            <i class="fas fa-spinner fa-spin mr-2"></i> Creating...
          </template>
          <template v-else>
            ➕ Create School
          </template>
        </button>

        <!-- Modal -->
        <div
          v-if="showModal"
          class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50"
        >
          <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg p-8 relative shadow-lg">
            <button
              class="absolute top-4 right-4 text-3xl font-bold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
              @click="showModal = false"
              aria-label="Close modal"
            >
              &times;
            </button>

            <h2 class="text-2xl font-semibold mb-6 dark:text-gray-100">Create New School</h2>
            <SchoolForm @close="showModal = false" />
          </div>
        </div>
      </template>

      <!-- Schools View -->
      <template v-else-if="view === 'schools'">
        <SchoolList />
      </template>

      <!-- Users View -->
      <template v-else-if="view === 'users'">
        <h2 class="text-2xl font-semibold dark:text-gray-100">User Management Coming Soon</h2>
      </template>
      </div>
    </main>

    <!-- Profile Settings Modal -->
    <div
      v-if="showProfileModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="showProfileModal = false"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Profile Settings</h2>
            <button
              @click="showProfileModal = false"
              class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors duration-200"
            >
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>
        <div class="p-6">
          <AdminSettings />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useTheme } from '@/useTheme'; // Adjust path as needed

import SchoolForm from '@/components/SchoolForm.vue';
import SchoolList from '@/pages/SchoolList.vue';
import AdminSettings from '@/Pages/AdminSettings.vue';

export default {
  name: 'AdminDashboard',
  components: {
    SchoolForm,
    SchoolList,
    AdminSettings,
  },
  setup() {
    const { theme, toggleTheme } = useTheme();
    return { theme, toggleTheme };
  },
  data() {
    return {
      showModal: false,
      view: 'dashboard', // default view
      loadingCreateSchool: false,
      showProfileDropdown: false,
      showProfileModal: false,
    };
  },
  methods: {
    async openCreateSchool() {
      this.loadingCreateSchool = true;
      await new Promise((resolve) => setTimeout(resolve, 1000));
      this.showModal = true;
      this.loadingCreateSchool = false;
    },
  },
};
</script>

<style scoped>
/* Optional spinner styles */
</style>


