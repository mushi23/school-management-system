<template>
  <SchoolAdminLayout>
    <div>
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold dark:text-white">👩‍🏫 Teachers Management</h2>
        <button 
          @click="fetchTeachers" 
          :disabled="loading"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-md transition-colors flex items-center"
        >
          <i class="fas fa-sync-alt mr-2" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
      </div>

    <!-- Filter/Search/Sort Section -->
    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
      <!-- Status Filter Buttons -->
      <div class="mb-4 flex flex-wrap gap-2 items-center">
        <button
          :class="['px-4 py-2 rounded', statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white']"
          @click="setStatusFilter('all')"
        >All</button>
        <button
          :class="['px-4 py-2 rounded', statusFilter === 'active' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white']"
          @click="setStatusFilter('active')"
        >Active</button>
        <button
          :class="['px-4 py-2 rounded', statusFilter === 'inactive' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white']"
          @click="setStatusFilter('inactive')"
        >Inactive</button>
        <button
          :class="['px-4 py-2 rounded', classTeacherFilter ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 dark:text-white']"
          @click="toggleClassTeacherFilter"
        >Class Teachers</button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">Search Teachers</label>
          <input
            v-model="search"
            type="text"
            placeholder="Search by name, email, or number"
            class="w-full px-3 py-2 border border-gray-300 dark:border-blue-400 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          />
        </div>
        <!-- Sort -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">Sort By</label>
          <select v-model="sortBy" class="w-full px-3 py-2 border border-gray-300 dark:border-blue-400 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
            <option value="full_name">Name</option>
            <option value="email">Email</option>
            <option value="teacher_number">Teacher No.</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">Order</label>
          <select v-model="sortDirection" class="w-full px-3 py-2 border border-gray-300 dark:border-blue-400 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
          </select>
        </div>
      </div>
      <div class="flex items-end mt-4">
        <button @click="clearFilters" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors">Clear Filters</button>
      </div>
    </div>

    <div v-if="loading" class="text-gray-600 dark:text-gray-300">Loading teachers...</div>
    <div v-else>
      <!-- Results Summary -->
      <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
        Showing {{ filteredTeachers.length }} of {{ teachers.length }} teachers
        <span v-if="hasActiveFilters" class="ml-2 text-blue-600 dark:text-blue-400">(filtered)</span>
      </div>
      <div v-if="filteredTeachers.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
        <i class="fas fa-user-tie text-4xl mb-2"></i>
        <div>No teachers to display.</div>
      </div>
      <div v-else class="flex flex-col gap-6">
        <div v-for="(teacher, index) in filteredTeachers" :key="teacher.id"
          class="bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-400 rounded-2xl shadow-lg p-6 flex flex-col sm:flex-row gap-6 justify-between w-full">
          <div class="flex items-center gap-4 flex-1">
            <div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-2xl font-bold text-blue-700 dark:text-blue-300">
              <img
                :src="teacher.profile_picture_url || '/default-profile.svg'"
                alt="Profile"
                class="w-14 h-14 rounded-full object-cover border-2 border-blue-300 dark:border-blue-700 bg-gray-100 dark:bg-gray-800"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-lg font-bold text-gray-800 dark:text-white truncate">{{ teacher.full_name }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-300 truncate">{{ teacher.email }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-300">Teacher No: {{ teacher.teacher_number }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-300">Phone: {{ teacher.phone || 'N/A' }}</div>
              <div class="text-xs" :class="teacher.user && teacher.user.active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                {{ teacher.user && teacher.user.active ? 'Active' : 'Inactive' }}
              </div>
              <div class="flex items-center mt-1 space-x-2">
                <span v-if="teacher.user && teacher.user.email_verified_at" class="inline-flex items-center px-1 py-0.5 rounded text-xs bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200">
                  <i class="fas fa-envelope-check mr-1"></i>
                  Verified
                </span>
                <span v-else class="inline-flex items-center px-1 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-200">
                  <i class="fas fa-envelope mr-1"></i>
                  Unverified
                </span>
              </div>
              <div v-if="isClassTeacher(teacher.id)" class="mt-2 text-xs font-semibold text-blue-700 dark:text-blue-300 flex items-center gap-1">
                <i class="fas fa-chalkboard-teacher"></i> Class Teacher
                <span v-if="getClassTeacherStreams(teacher.id).length" class="ml-1 text-xs text-gray-500 dark:text-gray-300">(
                  {{ getClassTeacherStreams(teacher.id).map(s => s.level + ' - ' + s.stream).join(', ') }}
                )</span>
              </div>
            </div>
          </div>
          <div class="flex flex-wrap gap-2 items-center mt-4 sm:mt-0 sm:ml-6">
            <button @click.stop="editTeacher(teacher)" title="Edit" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all" disabled>
              <i class="fas fa-edit text-blue-600"></i>
            </button>
            <button v-if="teacher.user && teacher.user.active" @click.stop="confirmDisableTeacher(teacher)" title="Disable" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
              <i class="fas fa-user-slash text-yellow-600"></i>
            </button>
            <button v-else @click.stop="confirmEnableTeacher(teacher)" title="Enable" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
              <i class="fas fa-user-check text-green-600"></i>
            </button>
            <button @click.stop="confirmDeleteTeacher(teacher)" title="Delete" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
              <i class="fas fa-trash text-red-600"></i>
            </button>
            <button @click.stop="confirmResetPassword(teacher)" title="Reset Password" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
              <i class="fas fa-unlock text-blue-600"></i>
            </button>
            <button v-if="teacher.deleted_at" @click.stop="confirmRestoreTeacher(teacher)" title="Restore Teacher" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
              <i class="fas fa-undo text-green-600"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    </div>
  </SchoolAdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import SchoolAdminLayout from '@/Layouts/SchoolAdminLayout.vue';

const teachers = ref([]);
const loading = ref(true);
const search = ref('');
const statusFilter = ref('all');
const sortBy = ref('full_name');
const sortDirection = ref('asc');
const toast = useToast();
const autoRefreshInterval = ref(null);
const classTeacherFilter = ref(false);
const schoolStructures = ref([]);

const fetchTeachers = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/school-admin/teachers');
    teachers.value = response.data;
    // Fetch school structures for class teacher info
    const structuresRes = await axios.get('/api/user/school-structures');
    schoolStructures.value = structuresRes.data;
  } catch (err) {
    console.error('Failed to fetch teachers or structures:', err);
  } finally {
    loading.value = false;
  }
};

const setStatusFilter = (status) => {
  statusFilter.value = status;
};

const clearFilters = () => {
  search.value = '';
  statusFilter.value = 'all';
  sortBy.value = 'full_name';
  sortDirection.value = 'asc';
};

function toggleClassTeacherFilter() {
  classTeacherFilter.value = !classTeacherFilter.value;
}

function isClassTeacher(teacherId) {
  // Check all levels/streams for class_teacher_id
  return schoolStructures.value.some(structure =>
    Array.isArray(structure.structure?.streams) &&
    structure.structure.streams.some(stream => stream.class_teacher_id === teacherId)
  );
}

function getClassTeacherStreams(teacherId) {
  // Return array of { level, stream } for all streams this teacher is assigned to
  const result = [];
  for (const structure of schoolStructures.value) {
    if (Array.isArray(structure.structure?.streams)) {
      for (const stream of structure.structure.streams) {
        if (stream.class_teacher_id === teacherId) {
          result.push({ level: structure.title, stream: stream.name });
        }
      }
    }
  }
  return result;
}

const hasActiveFilters = computed(() => {
  return search.value || statusFilter.value !== 'all' || sortBy.value !== 'full_name' || sortDirection.value !== 'asc' || classTeacherFilter.value;
});

const filteredTeachers = computed(() => {
  let filtered = teachers.value;
  // Status filter
  if (statusFilter.value === 'active') {
    filtered = filtered.filter(t => t.user && t.user.active);
  } else if (statusFilter.value === 'inactive') {
    filtered = filtered.filter(t => !t.user || !t.user.active);
  }
  // Class teacher filter
  if (classTeacherFilter.value) {
    filtered = filtered.filter(t => isClassTeacher(t.id));
  }
  // Search
  if (search.value) {
    const s = search.value.toLowerCase();
    filtered = filtered.filter(t =>
      t.full_name.toLowerCase().includes(s) ||
      t.email.toLowerCase().includes(s) ||
      (t.teacher_number && t.teacher_number.toLowerCase().includes(s))
    );
  }
  // Sort
  filtered = filtered.slice().sort((a, b) => {
    let aVal = a[sortBy.value] || '';
    let bVal = b[sortBy.value] || '';
    if (typeof aVal === 'string') aVal = aVal.toLowerCase();
    if (typeof bVal === 'string') bVal = bVal.toLowerCase();
    let comparison = 0;
    if (aVal > bVal) comparison = 1;
    if (aVal < bVal) comparison = -1;
    return sortDirection.value === 'desc' ? -comparison : comparison;
  });
  return filtered;
});

function editTeacher(teacher) {
  // Stub for edit action
  alert('Edit teacher: ' + teacher.full_name);
}
async function confirmDisableTeacher(teacher) {
  if (confirm('Are you sure you want to disable this teacher?')) {
    try {
      await axios.post(`/api/school-admin/teachers/${teacher.id}/disable`);
      toast.success('Teacher disabled successfully.');
      fetchTeachers();
    } catch (err) {
      toast.error('Failed to disable teacher.');
    }
  }
}
async function confirmEnableTeacher(teacher) {
  if (confirm('Are you sure you want to enable this teacher?')) {
    try {
      await axios.post(`/api/school-admin/teachers/${teacher.id}/enable`);
      toast.success('Teacher enabled successfully.');
      fetchTeachers();
    } catch (err) {
      toast.error('Failed to enable teacher.');
    }
  }
}
async function confirmDeleteTeacher(teacher) {
  if (confirm('Are you sure you want to delete this teacher?')) {
    try {
      await axios.delete(`/api/school-admin/teachers/${teacher.id}`);
      toast.success('Teacher deleted successfully.');
      fetchTeachers();
    } catch (err) {
      toast.error('Failed to delete teacher.');
    }
  }
}
async function confirmResetPassword(teacher) {
  if (confirm('Are you sure you want to reset the password for this teacher?')) {
    try {
      await axios.post(`/api/school-admin/teachers/${teacher.id}/reset-password`);
      toast.success('Password reset and emailed to the teacher.');
    } catch (err) {
      toast.error('Failed to reset password.');
    }
  }
}
async function confirmRestoreTeacher(teacher) {
  if (confirm('Are you sure you want to restore this teacher?')) {
    try {
      await axios.post(`/api/school-admin/teachers/${teacher.id}/restore`);
      toast.success('Teacher restored successfully.');
      fetchTeachers();
    } catch (err) {
      toast.error('Failed to restore teacher.');
    }
  }
}

onMounted(() => {
  fetchTeachers();
  
  // Auto-refresh every 30 seconds to catch verification updates
  autoRefreshInterval.value = setInterval(() => {
    if (!loading.value) {
      fetchTeachers();
    }
  }, 30000); // 30 seconds
});

onUnmounted(() => {
  if (autoRefreshInterval.value) {
    clearInterval(autoRefreshInterval.value);
  }
});
</script> 