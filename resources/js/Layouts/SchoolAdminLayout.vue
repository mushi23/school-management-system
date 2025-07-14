<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-950 relative">
    <!-- Vertical Sidebar Navigation (absolute, overlays content) -->
    <div class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-gray-900 shadow-lg border-r border-gray-200 dark:border-gray-700 z-30 transition-transform duration-500" :class="showNavigation ? 'translate-x-0' : '-translate-x-full'">
      <!-- School Logo at Top -->
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full border-2 border-blue-300 dark:border-blue-700 bg-gray-200 dark:bg-gray-700 overflow-hidden shadow-lg ring-2 ring-blue-400/30 dark:ring-blue-700/40">
            <img
              v-if="school && school.logo"
              :src="school.logo"
              alt="School Logo"
              class="w-full h-full object-cover object-center"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <i class="fas fa-school text-lg text-gray-400 dark:text-gray-500"></i>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 truncate">{{ school?.name || 'School Admin' }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $page.props.auth.user.email }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 p-4">
        <ul class="space-y-2">
          <li>
            <Link :href="route('schooladmin.dashboard')" :class="navClass('schooladmin.dashboard') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>
              </svg>
              Dashboard
            </Link>
          </li>
          <li>
            <Link :href="route('schooladmin.teachers')" :class="navClass('schooladmin.teachers') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
              Teachers
            </Link>
          </li>
          <li>
            <Link :href="route('schooladmin.students')" :class="navClass('schooladmin.students') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              Students
            </Link>
          </li>
          <li>
            <Link :href="route('schooladmin.subjects')" :class="navClass('schooladmin.subjects') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              Subjects
            </Link>
          </li>
          <li>
            <Link :href="route('schooladmin.mpesa-settings.index')" :class="navClass('schooladmin.mpesa-settings.index') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
              M-PESA Settings
            </Link>
          </li>
          <li>
            <Link :href="route('schooladmin.receipt-template')" :class="navClass('schooladmin.receipt-template') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Receipt Template
            </Link>
          </li>
          <li>
            <button @click="showProfileModal = true" :class="'w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Settings
            </button>
          </li>
        </ul>
      </nav>

      <!-- Profile Section at Bottom -->
      <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full border border-gray-300 dark:border-gray-600 overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
            <img 
              v-if="$page.props.auth.user.profile_picture_url" 
              :src="$page.props.auth.user.profile_picture_url" 
              class="w-full h-full object-cover"
              @error="e => { e.target.style.display = 'none'; e.target.nextElementSibling.style.display = 'flex'; }"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <i class="fas fa-user text-gray-500 dark:text-gray-400"></i>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $page.props.auth.user.name || $page.props.auth.user.email }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">School Admin</p>
          </div>
          <button @click="toggleTheme" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
            <span v-if="theme === 'dark'" class="text-sm">🌙</span>
            <span v-else class="text-sm">☀️</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div :class="['flex flex-col min-h-screen transition-all duration-500', showNavigation ? 'ml-64' : 'ml-0']">
      <!-- Top Header Bar -->
      <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 shadow-sm transition-all duration-500">
        <!-- Navigation Toggle Button -->
        <button 
          @click="toggleNavigation" 
          class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center transition-all duration-300 hover:bg-gray-200 dark:hover:bg-gray-700"
        >
          <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <!-- Page Title -->
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
          {{ getPageTitle() }}
        </h1>

        <!-- Profile Dropdown -->
        <div class="relative">
          <button @click="showProfileDropdown = !showProfileDropdown" class="flex items-center gap-2 focus:outline-none">
            <div class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-600 overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
              <img 
                v-if="$page.props.auth.user.profile_picture_url" 
                :src="$page.props.auth.user.profile_picture_url" 
                class="w-full h-full object-cover"
                @error="e => { e.target.style.display = 'none'; e.target.nextElementSibling.style.display = 'flex'; }"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <i class="fas fa-user text-gray-500 dark:text-gray-400 text-sm"></i>
              </div>
            </div>
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-300 transition-transform duration-200" :class="{'rotate-180': showProfileDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div v-if="showProfileDropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 z-30">
            <button
              @click="showProfileModal = true; showProfileDropdown = false"
              class="block w-full text-left px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-t-lg"
            >
              Profile Settings
            </button>
            <form method="POST" :action="route('logout')">
              <button type="submit" class="w-full text-left px-4 py-2 text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-b-lg">Logout</button>
            </form>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 p-6 overflow-auto">
        <Transition name="scale-fade" mode="out-in">
          <slot />
        </Transition>
      </main>
    </div>

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
          <Settings :school="$page.props.auth.user.school" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useTheme } from '@/useTheme.js';
import { usePage, Link } from '@inertiajs/vue3';
import Settings from '@/Pages/SchoolAdmin/Settings.vue';

console.log('SchoolAdminLayout mounted');

const { theme, toggleTheme } = useTheme();
const page = usePage();
const school = page.props.school;
const showProfileDropdown = ref(false);
const showProfileModal = ref(false);
const showNavigation = ref(true);

const toggleNavigation = () => {
  showNavigation.value = !showNavigation.value;
};

const getPageTitle = () => {
  const currentRoute = route().current();
  switch (currentRoute) {
    case 'schooladmin.dashboard':
      return 'Dashboard';
    case 'schooladmin.teachers':
      return 'Teachers';
    case 'schooladmin.students':
      return 'Students';
    case 'schooladmin.subjects':
      return 'Subjects';
    case 'schooladmin.mpesa-settings.index':
      return 'M-PESA Settings';
    case 'schooladmin.mpesa-settings.create':
      return 'Add M-PESA Setting';
    case 'schooladmin.mpesa-settings.edit':
      return 'Edit M-PESA Setting';
    case 'schooladmin.receipt-template':
      return 'Receipt Template';
    default:
      return 'School Admin Portal';
  }
};

watch(() => school, (val) => {
  console.log('School prop changed:', val);
});

function navClass(routeName) {
  return [
    route().current(routeName)
      ? 'bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200'
      : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800',
  ].join(' ');
}
</script>

<style scoped>
@keyframes gradientMove {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
.animate-gradient {
  background-size: 200% 200%;
  animation: gradientMove 8s ease-in-out infinite;
}
.profile-card {
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18), 0 1.5px 6px 0 rgba(80, 0, 255, 0.10);
  border-radius: 1rem;
}
@media (max-width: 768px) {
  nav ul {
    flex-direction: column;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
  }
  .profile-card {
    right: 2vw !important;
    top: 1vw !important;
  }
  .relative.z-10.flex.flex-col.items-center.justify-center.pt-10.pb-4 {
    padding-top: 2rem;
    padding-bottom: 1rem;
  }
}
/* Scale (zoom) transition for main content */
.scale-fade-enter-active, .scale-fade-leave-active {
  transition: opacity 0.35s cubic-bezier(.4,0,.2,1), transform 0.35s cubic-bezier(.4,0,.2,1);
}
.scale-fade-enter-from, .scale-fade-leave-to {
  opacity: 0;
  transform: scale(0.96);
}
.scale-fade-leave-from, .scale-fade-enter-to {
  opacity: 1;
  transform: scale(1);
}
</style>

