<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
defineOptions({ layout: StudentLayout });
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const student = computed(() => page.props.student);
const subjects = computed(() => student.value?.subjects || []);
const subjectTeachers = computed(() => page.props.subject_teachers || {});

const feeBalance = computed(() => {
  // Placeholder value; will be fetched from backend in next steps
  return 12000;
});

function handlePayClick() {
  alert('Payment flow coming soon!');
}

function goToSubject(subject) {
  const level = encodeURIComponent(student.value.structure?.title || '');
  const stream = encodeURIComponent(student.value.stream || '');
  const subj = encodeURIComponent(subject);
  router.visit(`/student/subjects/${level}/${stream}/${subj}`);
}
</script>

<template>
  <div class="flex flex-1 min-h-[70vh] items-center justify-start w-full pl-8 md:pl-24">
    <div class="relative w-full max-w-2xl mx-auto">
      <div class="bg-white/90 dark:bg-gray-900/90 rounded-2xl shadow-xl p-8 flex flex-col items-center border border-gray-100 dark:border-gray-800">
        <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white drop-shadow">🎓 Student Dashboard</h1>
        <p class="text-lg mb-6 text-gray-700 dark:text-gray-300 text-center">Welcome, <span class="font-semibold text-blue-700 dark:text-blue-300">{{ student?.name }}</span>! This is your dashboard.</p>
        <div class="flex flex-col sm:flex-row gap-6 w-full justify-center mt-4">
          <div class="flex-1 bg-blue-50 dark:bg-blue-900/40 rounded-xl p-6 flex flex-col items-center shadow border border-blue-100 dark:border-blue-800">
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-200">{{ student?.admission_number }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-300 mt-1">Admission Number</span>
          </div>
          <div class="flex-1 bg-purple-50 dark:bg-purple-900/40 rounded-xl p-6 flex flex-col items-center shadow border border-purple-100 dark:border-purple-800">
            <span class="text-2xl font-bold text-purple-700 dark:text-purple-200">{{ student?.structure?.title || 'N/A' }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-300 mt-1">Class/Level</span>
          </div>
        </div>
      </div>
      <div class="bg-green-50 dark:bg-green-900/40 rounded-xl p-6 flex flex-col items-center shadow border border-green-100 dark:border-green-800 mt-6 w-full">
        <span class="text-xl font-bold text-green-700 dark:text-green-200">Fee Balance: Ksh {{ feeBalance }}</span>
        <button @click="handlePayClick" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">Pay</button>
      </div>
    </div>
  </div>
</template>

