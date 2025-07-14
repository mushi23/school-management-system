<template>
  <SchoolAdminLayout>
    <div class="max-w-4xl mx-auto p-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-4">🎓 First-Time School Setup</h1>

      <component
        :is="currentComponent"
        v-bind="formData"
        @next="nextStep"
        @back="previousStep"
        @update="updateForm"
        @finish="submitSetup"
      />
    </div>
  </SchoolAdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import SchoolAdminLayout from '@/Layouts/SchoolAdminLayout.vue';

// Steps
import Step1Identity from './Step1Identity.vue';
import Step2Location from './Step2Location.vue';
import Step3Structure from './Step3Structure.vue';
import Step4Services from './Step4Services.vue';
import Step5BrandStory from './Step5BrandStory.vue';
import Step6Admin from './Step6Admin.vue';

const steps = [
  Step1Identity,
  Step2Location,
  Step3Structure,
  Step4Services,
  Step5BrandStory,
  Step6Admin
];

const currentStep = ref(0);
const formData = ref({
  school_name: '',
  motto: '',
  type: '',
  system: '',
  // ... other fields across all steps
});

const currentComponent = computed(() => steps[currentStep.value]);

function updateForm(data) {
  formData.value = { ...formData.value, ...data };
}

function nextStep() {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++;
  }
}

function previousStep() {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
}

function submitSetup() {
  // Final form submission
  console.log('Submitting form:', formData.value);
  // Send to backend (we’ll build the route/controller later)
}
</script>
