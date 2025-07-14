<template>
  <div class="max-w-5xl mx-auto p-8">
    <div v-if="subjects.length" class="flex flex-wrap gap-4 justify-center">
      <div v-for="subject in subjects" :key="subject"
        class="relative bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-400 rounded-xl shadow transition-all duration-200 p-6 min-w-[220px] text-center flex flex-col items-center justify-center group cursor-pointer hover:scale-105 hover:border-blue-500 dark:hover:border-blue-300 hover:shadow-2xl"
        @click="goToSubject(subject)">
        <span :style="{ backgroundColor: avatarColor(subject) }" class="w-12 h-12 rounded-full flex items-center justify-center text-2xl font-bold text-white mb-2 select-none">
          {{ subject.charAt(0).toUpperCase() }}
        </span>
        <span class="text-lg font-bold text-blue-700 dark:text-white group-hover:text-blue-900 dark:group-hover:text-blue-200 mb-2">{{ subject }}</span>
        <div v-if="subjectTeacherNames[subject]" class="mt-2 text-sm text-gray-500 dark:text-gray-300">
          Teacher: {{ subjectTeacherNames[subject] }}
        </div>
      </div>
    </div>
    <div v-else class="text-gray-500 dark:text-gray-400">No subjects enrolled.</div>
  </div>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
defineOptions({ layout: StudentLayout });
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
const page = usePage();
const student = computed(() => page.props.student || {});
const subjects = computed(() => student.value.subjects || []);
const subjectTeachers = computed(() => page.props.subject_teachers || {});
const subjectTeacherNames = computed(() => page.props.subject_teacher_names || {});

// Generate a color based on the subject name
function avatarColor(subject) {
  // Simple hash to color
  const colors = [
    '#2563eb', '#9333ea', '#eab308', '#10b981', '#f59e42', '#ef4444', '#14b8a6', '#6366f1', '#f43f5e', '#0ea5e9', '#a21caf', '#f59e42'
  ];
  let hash = 0;
  for (let i = 0; i < subject.length; i++) {
    hash = subject.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
}

function goToSubject(subject) {
  const level = encodeURIComponent(student.value.structure?.title || '');
  const stream = encodeURIComponent(student.value.stream || '');
  const subj = encodeURIComponent(subject);
  router.visit(`/student/subjects/${level}/${stream}/${subj}`);
}
</script>

<style scoped>
.group:hover .group-hover\:opacity-100 {
  opacity: 1 !important;
  pointer-events: auto !important;
}
</style> 