<template>
  <div class="p-8 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Subject: {{ subject }}</h1>
    <div class="mb-4">
      <span class="font-semibold">Level:</span> {{ level }}<br />
      <span class="font-semibold">Stream:</span> {{ stream }}
    </div>
    <div v-if="teacher">
      <h2 class="text-lg font-semibold mb-2">Subject Teacher</h2>
      <div class="p-4 bg-gray-100 rounded shadow">
        <div><span class="font-semibold">Name:</span> {{ teacher.full_name || 'N/A' }}</div>
        <div><span class="font-semibold">Email:</span> {{ teacher.email || 'N/A' }}</div>
      </div>
    </div>
    <div v-else class="text-gray-500">No teacher assigned for this subject.</div>
    <div class="mt-8">
      <Link href="/student/dashboard" class="text-blue-600 hover:underline">&larr; Back to Dashboard</Link>
    </div>
  </div>
</template>

<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
defineOptions({ layout: StudentLayout });
import { usePage, Link } from '@inertiajs/vue3';
const page = usePage();
const level = page.props.level;
const stream = page.props.stream;
const subject = page.props.subject;
const teacher = page.props.teacher;
</script> 