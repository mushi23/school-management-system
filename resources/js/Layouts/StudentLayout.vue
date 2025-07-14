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
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 truncate">{{ school?.name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ student?.name }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 p-4">
        <ul class="space-y-2">
          <li>
            <Link :href="route('student.dashboard')" :class="navClass('student.dashboard') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>
              </svg>
              Dashboard
            </Link>
          </li>
          <li>
            <Link :href="route('student.subjects')" :class="navClass('student.subjects') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              Subjects
            </Link>
          </li>
          <li>
            <Link :href="route('student.fees')" :class="navClass('student.fees') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
              Fees
            </Link>
          </li>
          <li>
            <Link :href="route('student.receipts')" :class="navClass('student.receipts') + ' flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 font-medium'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Receipts
            </Link>
          </li>
        </ul>
      </nav>

      <!-- Profile Section at Bottom -->
      <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
          <div class="relative w-10 h-10">
            <img 
              :src="profileImageSrc" 
              class="w-10 h-10 rounded-full border border-gray-300 dark:border-gray-600 object-cover" 
              @error="onImgError"
              @load="onImgLoad"
            />
            <div v-if="imageLoading" class="absolute inset-0 w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ student?.name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Student</p>
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
            <div class="relative w-8 h-8">
              <img 
                :src="profileImageSrc" 
                class="w-8 h-8 rounded-full border border-gray-300 dark:border-gray-600 object-cover" 
                @error="onImgError"
                @load="onImgLoad"
              />
              <div v-if="imageLoading" class="absolute inset-0 w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
            </div>
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-300 transition-transform duration-200" :class="{'rotate-180': showProfileDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div v-if="showProfileDropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 z-30">
            <Link href="/profile" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-t-lg">Profile</Link>
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
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useTheme } from '@/useTheme.js';
import { usePage, Link } from '@inertiajs/vue3';

console.log('StudentLayout mounted');

const { theme, toggleTheme } = useTheme();
const page = usePage();
const student = computed(() => page.props.student);
const school = computed(() => page.props.school);

// Computed property for profile image source with fallback
const profileImageSrc = computed(() => {
  const url = student.value?.profile_picture_url;
  // Use the profile picture URL if it exists, otherwise use default
  return url || '/default-profile.svg';
});

// Debug student prop (only log once)
if (student.value) {
  console.log('Student loaded:', {
    name: student.value.name,
    profile_picture_url: student.value.profile_picture_url,
    keys: Object.keys(student.value)
  });
}
const showProfileDropdown = ref(false);
const showNavigation = ref(true);
const imageLoading = ref(false); // Start with false since we have a fallback

const toggleNavigation = () => {
  showNavigation.value = !showNavigation.value;
};

const getPageTitle = () => {
  const currentRoute = route().current();
  switch (currentRoute) {
    case 'student.dashboard':
      return 'Dashboard';
    case 'student.subjects':
      return 'Subjects';
    case 'student.fees':
      return 'Fees';
    case 'student.receipts':
      return 'Receipts';
    default:
      return 'Student Portal';
  }
};

// Debounced watchers to prevent excessive logging
let studentChangeTimeout;
let schoolChangeTimeout;

watch(() => student.value, (val) => {
  clearTimeout(studentChangeTimeout);
  studentChangeTimeout = setTimeout(() => {
    console.log('Student prop changed:', val);
    // Reset loading state when student changes
    imageLoading.value = true;
  }, 100);
});

watch(() => school.value, (val) => {
  clearTimeout(schoolChangeTimeout);
  schoolChangeTimeout = setTimeout(() => {
    console.log('School prop changed:', val);
  }, 100);
});

function navClass(routeName) {
  return [
    route().current(routeName)
      ? 'bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200'
      : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800',
  ].join(' ');
}

// Image error handling and debugging
function onImgError(event) {
  console.log('Image failed to load:', event.target.src);
  // Set loading to false so the skeleton disappears
  imageLoading.value = false;
}

function onImgLoad(event) {
  console.log('Image loaded successfully:', event.target.src);
  imageLoading.value = false;
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