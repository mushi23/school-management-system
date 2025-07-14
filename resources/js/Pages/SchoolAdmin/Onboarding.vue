<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

// Import all 6 steps
import Step1 from './Setup/Step1Identity.vue';
import Step2 from './Setup/Step2Location.vue';
import Step3 from './Setup/Step3Structure.vue';
import Step4 from './Setup/Step4Services.vue';
import Step5 from './Setup/Step5BrandStory.vue';
import Step6 from './Setup/Step6Admin.vue';

const currentStep = ref(1);

const steps = [
  { id: 1, title: 'School Details', component: Step1 },
  { id: 2, title: 'Contact Info', component: Step2 },
  { id: 3, title: 'Programs / Services', component: Step3 },
  { id: 4, title: 'Branding & Media', component: Step4 },
  { id: 5, title: 'Summary', component: Step5 },
  { id: 6, title: 'Confirmation', component: Step6 },
];

const form = useForm({
  organizationName: '',
  organizationType: '',
  country: '',
  region: '',
  contactEmail: '',
  contactPhone: '',
  website: '',
  terms: '',
  streams: '',
  levels: [],
  services: [],
  custom: '',
  logo: '',
  background: '',
  brandColor: '#2563eb',
  slogan: '',
});

function next() {
  if (currentStep.value < steps.length) currentStep.value++;
}

function previous() {
  if (currentStep.value > 1) currentStep.value--;
}

function submit() {
  form.patch(route('schooladmin.onboarding.update'));
}
</script>

<template>
  <Head title="School Onboarding" />

  <div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-semibold mb-4">Setup Wizard</h1>

    <div class="mb-6 text-gray-600">
      Step {{ currentStep }} of {{ steps.length }}: 
      <strong>{{ steps[currentStep - 1].title }}</strong>
    </div>

    <!-- Dynamic component -->
    <component
  :is="steps[currentStep - 1].component"
  v-bind="form"
  @update="form.fill"
  @next="next"
  @back="previous"
  @submit="submit"
/>


    <div class="flex justify-between mt-6">

    </div>
  </div>
</template>



