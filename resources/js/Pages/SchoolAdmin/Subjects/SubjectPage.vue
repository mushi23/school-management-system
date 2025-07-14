<template>
  <SchoolAdminLayout>
    <div class="max-w-5xl mx-auto p-6">
      <button @click="$inertia.visit('/school-admin/subjects')" class="flex items-center mb-4 px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-100 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>
      <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Subject Management</h1>
        <!-- Teacher Profile Card (top right, not overlapping) -->
        <div v-if="teacher" class="flex items-center gap-4 bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-700 rounded-xl shadow p-4 min-w-[260px] md:ml-8 md:mt-0 mt-4">
          <img :src="teacher.profile_picture || '/img/profile-placeholder.png'" alt="Profile" class="w-14 h-14 rounded-full border-2 border-blue-300 dark:border-blue-700 bg-gray-100 dark:bg-gray-800" />
          <div>
            <div class="text-lg font-bold text-gray-800 dark:text-white">{{ teacher.name }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-300">{{ teacher.email }}</div>
          </div>
        </div>
      </div>
      <!-- Subject Overview -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-300 mb-4">Subject Overview</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 items-center">
          <div class="flex flex-col items-center">
            <span class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Subject</span>
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-200">{{ subject }}</span>
          </div>
          <div class="flex flex-col items-center">
            <span class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Level</span>
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-200">{{ level }}</span>
          </div>
          <div class="flex flex-col items-center">
            <span class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Stream</span>
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-200">{{ stream }}</span>
          </div>
          <div class="flex flex-col items-center">
            <span class="text-xs uppercase text-gray-500 dark:text-gray-400 mb-1">Status</span>
            <span :class="[
              'inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold',
              combinedStatus === 'Combined'
                ? 'bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200'
            ]">
              <i v-if="combinedStatus === 'Combined'" class="fas fa-link"></i>
              <i v-else class="fas fa-stream"></i>
              {{ combinedStatus }}
            </span>
          </div>
        </div>
      </section>
      <!-- Class Roster -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Class Roster</h2>
        <!-- TODO: List of students, add/remove -->
        <div class="text-gray-500 dark:text-gray-400">[Student list placeholder]</div>
      </section>
      <!-- Schedule -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Schedule</h2>
        <!-- TODO: Lesson schedule, upcoming/past lessons -->
        <div class="text-gray-500 dark:text-gray-400">[Schedule placeholder]</div>
      </section>
      <!-- Attendance -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Attendance</h2>
        <!-- TODO: Attendance records, mark/view -->
        <div class="text-gray-500 dark:text-gray-400">[Attendance placeholder]</div>
      </section>
      <!-- Assessments & Grades -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Assessments & Grades</h2>
        <!-- TODO: Assignments, tests, grades -->
        <div class="text-gray-500 dark:text-gray-400">[Assessments & Grades placeholder]</div>
      </section>
      <!-- Resources -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Resources & Materials</h2>
        <!-- TODO: Files, links, notes -->
        <div class="text-gray-500 dark:text-gray-400">[Resources placeholder]</div>
      </section>
      <!-- Announcements -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Announcements</h2>
        <!-- TODO: Announcements/messages -->
        <div class="text-gray-500 dark:text-gray-400">[Announcements placeholder]</div>
      </section>
      <!-- Analytics -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Analytics & Reports</h2>
        <!-- TODO: Performance, attendance, trends -->
        <div class="text-gray-500 dark:text-gray-400">[Analytics placeholder]</div>
      </section>
      <!-- Admin Actions -->
      <section class="mb-8 bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-2">Admin Actions</h2>
        <!-- TODO: Edit subject, change teacher, combine/separate, archive, export -->
        <div class="text-gray-500 dark:text-gray-400">[Admin actions placeholder]</div>
      </section>
    </div>
  </SchoolAdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SchoolAdminLayout from '@/Layouts/SchoolAdminLayout.vue';

const page = usePage();
const teacher = computed(() => page.props.teacher);
const subject = computed(() => page.props.subject);
const level = computed(() => page.props.level);
const stream = computed(() => page.props.stream);
const combinedStatus = computed(() => page.props.combined_status);
</script> 