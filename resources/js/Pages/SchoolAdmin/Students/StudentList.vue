<template>
  <SchoolAdminLayout>
    <div>
      <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-bold dark:text-white">🎓 Students Management</h2>
        <button 
          @click="fetchStudents" 
          :disabled="loading"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-md transition-colors flex items-center"
        >
          <i class="fas fa-sync-alt mr-2" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
      </div>

      <!-- Filter/Search/Sort Section -->
      <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
        <div class="flex flex-wrap gap-2 items-center mb-4">
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
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
          <!-- Class Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">Class</label>
            <select v-model="classFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-blue-400 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
              <option value="">All</option>
              <option v-for="cls in availableClasses" :key="cls" :value="cls">{{ cls }}</option>
            </select>
          </div>
          <!-- Age Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">Age</label>
            <input v-model.number="ageFilter" type="number" min="1" placeholder="Any" class="w-full px-3 py-2 border border-gray-300 dark:border-blue-400 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" />
          </div>
          <!-- Admission Number Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">Admission #</label>
            <input v-model="admissionNumberFilter" type="text" placeholder="Any" class="w-full px-3 py-2 border border-gray-300 dark:border-blue-400 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" />
          </div>
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">Search Students</label>
            <input
              v-model="search"
              type="text"
              placeholder="Search by name, email, or class"
              class="w-full px-3 py-2 border border-gray-300 dark:border-blue-400 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
            />
          </div>
          <!-- Sort -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-100 mb-1">Sort By</label>
            <select v-model="sortBy" class="w-full px-3 py-2 border border-gray-300 dark:border-blue-400 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
              <option value="full_name">Name</option>
              <option value="email">Email</option>
              <option value="class">Class</option>
              <option value="admission_number">Admission #</option>
            </select>
          </div>
        </div>
        <div class="flex items-end mt-4">
          <button @click="clearFilters" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition-colors">Clear Filters</button>
        </div>
      </div>

      <div v-if="loading" class="text-gray-600 dark:text-gray-300">Loading students...</div>
      <div v-else>
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
          Showing {{ filteredStudents.length }} of {{ students.length }} students
          <span v-if="hasActiveFilters" class="ml-2 text-blue-600 dark:text-blue-400">(filtered)</span>
        </div>
        <div v-if="filteredStudents.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
          <i class="fas fa-user-graduate text-4xl mb-2"></i>
          <div>No students to display.</div>
        </div>
        <div v-else class="flex flex-col gap-6">
          <div v-for="student in filteredStudents" :key="student.id"
            class="bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-400 rounded-2xl shadow-lg p-6 flex flex-col sm:flex-row gap-6 justify-between w-full">
            <div class="flex items-center gap-4 flex-1">
              <img
                :src="student.profile_picture_url || '/default-profile.svg'"
                alt="Profile"
                class="w-14 h-14 rounded-full object-cover border-2 border-blue-300 dark:border-blue-700 bg-gray-100 dark:bg-gray-800"
              />
              <div class="flex-1 min-w-0">
                <div class="text-lg font-bold text-gray-800 dark:text-white truncate">{{ student.full_name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-300 truncate">{{ student.email }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-300">Class: {{ student.class || 'N/A' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-300">Admission #: {{ student.admission_number || 'N/A' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-300">Stream: {{ student.stream || 'N/A' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-300">Guardian: {{ student.guardian_name || 'N/A' }}</div>
                <div class="text-xs" :class="student.user && student.user.active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ student.user && student.user.active ? 'Active' : 'Inactive' }}
                </div>
                <div class="flex items-center mt-1 space-x-2">
                  <span v-if="student.user && student.user.email_verified_at" class="inline-flex items-center px-1 py-0.5 rounded text-xs bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200">
                    <i class="fas fa-envelope-check mr-1"></i>
                    Verified
                  </span>
                  <span v-else class="inline-flex items-center px-1 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-200">
                    <i class="fas fa-envelope mr-1"></i>
                    Unverified
                  </span>
                </div>
              </div>
            </div>
            <div class="flex flex-wrap gap-2 items-center mt-4 sm:mt-0 sm:ml-6">
              <button @click.stop="editStudent(student)" title="Edit" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all" disabled>
                <i class="fas fa-edit text-blue-600"></i>
              </button>
              <button v-if="student.user && student.user.active" @click.stop="confirmDisableStudent(student)" title="Disable" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
                <i class="fas fa-user-slash text-yellow-600"></i>
              </button>
              <button v-else @click.stop="confirmEnableStudent(student)" title="Enable" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
                <i class="fas fa-user-check text-green-600"></i>
              </button>
              <button @click.stop="confirmDeleteStudent(student)" title="Delete" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
                <i class="fas fa-trash text-red-600"></i>
              </button>
              <button @click.stop="confirmResetPassword(student)" title="Reset Password" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
                <i class="fas fa-unlock text-blue-600"></i>
              </button>
              <button v-if="student.deleted_at" @click.stop="confirmRestoreStudent(student)" title="Restore Student" class="p-2 rounded-lg bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-blue-400 hover:bg-white/70 dark:hover:bg-gray-700/70 transition-all">
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

const students = ref([]);
const loading = ref(true);
const search = ref('');
const classFilter = ref('');
const ageFilter = ref('');
const admissionNumberFilter = ref('');
const sortBy = ref('full_name');
const sortDirection = ref('asc');
const toast = useToast();
const autoRefreshInterval = ref(null);
const statusFilter = ref('all');

const availableClasses = computed(() => {
  // Unique class names from students
  const set = new Set(students.value.map(s => s.class).filter(Boolean));
  return Array.from(set);
});

const fetchStudents = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/school-admin/students');
    students.value = response.data.map(student => ({
      id: student.id,
      full_name: student.name || (student.user && student.user.name) || '',
      email: student.email || (student.user && student.user.email) || '',
      class: student.structure ? student.structure.title : '',
      stream: student.stream,
      guardian_name: student.guardians && student.guardians.length > 0 ? student.guardians[0].name : '',
      user: student.user,
      deleted_at: student.deleted_at,
      dob: student.dob,
      admission_number: student.admission_number,
      profile_picture_url: student.profile_picture_url,
    }));
  } catch (err) {
    console.error('Failed to fetch students:', err);
    toast.error('Failed to fetch students.');
  } finally {
    loading.value = false;
  }
};

const hasActiveFilters = computed(() => {
  return (
    search.value ||
    classFilter.value ||
    ageFilter.value ||
    admissionNumberFilter.value ||
    sortBy.value !== 'full_name' ||
    sortDirection.value !== 'asc' ||
    statusFilter.value !== 'all'
  );
});

function calculateAge(dob) {
  if (!dob) return '';
  const birth = new Date(dob);
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const m = today.getMonth() - birth.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
    age--;
  }
  return age;
}

const filteredStudents = computed(() => {
  let filtered = students.value;
  // Status filter
  if (statusFilter.value === 'active') {
    filtered = filtered.filter(s => s.user && s.user.active);
  } else if (statusFilter.value === 'inactive') {
    filtered = filtered.filter(s => !s.user || !s.user.active);
  }
  // Class filter
  if (classFilter.value) {
    filtered = filtered.filter(s => s.class === classFilter.value);
  }
  // Age filter
  if (ageFilter.value) {
    filtered = filtered.filter(s => calculateAge(s.dob) == ageFilter.value);
  }
  // Admission number filter
  if (admissionNumberFilter.value) {
    filtered = filtered.filter(s => s.admission_number && s.admission_number.toLowerCase().includes(admissionNumberFilter.value.toLowerCase()));
  }
  // Search
  if (search.value) {
    const s = search.value.toLowerCase();
    filtered = filtered.filter(stu =>
      stu.full_name.toLowerCase().includes(s) ||
      stu.email.toLowerCase().includes(s) ||
      (stu.class && stu.class.toLowerCase().includes(s))
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

const setStatusFilter = (status) => {
  statusFilter.value = status;
};

const clearFilters = () => {
  search.value = '';
  classFilter.value = '';
  ageFilter.value = '';
  admissionNumberFilter.value = '';
  sortBy.value = 'full_name';
  sortDirection.value = 'asc';
  statusFilter.value = 'all';
};

function editStudent(student) {
  // Stub for edit action
  alert('Edit student: ' + student.full_name);
}
async function confirmDisableStudent(student) {
  if (confirm('Are you sure you want to disable this student?')) {
    try {
      await axios.post(`/api/school-admin/students/${student.id}/disable`);
      toast.success('Student disabled successfully.');
      fetchStudents();
    } catch (err) {
      toast.error('Failed to disable student.');
    }
  }
}
async function confirmEnableStudent(student) {
  if (confirm('Are you sure you want to enable this student?')) {
    try {
      await axios.post(`/api/school-admin/students/${student.id}/enable`);
      toast.success('Student enabled successfully.');
      fetchStudents();
    } catch (err) {
      toast.error('Failed to enable student.');
    }
  }
}
async function confirmDeleteStudent(student) {
  if (confirm('Are you sure you want to delete this student?')) {
    try {
      await axios.delete(`/api/school-admin/students/${student.id}`);
      toast.success('Student deleted successfully.');
      fetchStudents();
    } catch (err) {
      toast.error('Failed to delete student.');
    }
  }
}
async function confirmResetPassword(student) {
  if (confirm('Are you sure you want to reset the password for this student?')) {
    try {
      await axios.post(`/api/school-admin/students/${student.id}/reset-password`);
      toast.success('Password reset and emailed to the student.');
    } catch (err) {
      toast.error('Failed to reset password.');
    }
  }
}
async function confirmRestoreStudent(student) {
  if (confirm('Are you sure you want to restore this student?')) {
    try {
      await axios.post(`/api/school-admin/students/${student.id}/restore`);
      toast.success('Student restored successfully.');
      fetchStudents();
    } catch (err) {
      toast.error('Failed to restore student.');
    }
  }
}

onMounted(() => {
  fetchStudents();
  autoRefreshInterval.value = setInterval(() => {
    if (!loading.value) {
      fetchStudents();
    }
  }, 30000);
});

onUnmounted(() => {
  if (autoRefreshInterval.value) {
    clearInterval(autoRefreshInterval.value);
  }
});
</script> 