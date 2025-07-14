<template>
  <div class="max-w-3xl mx-auto p-6">
    <!-- Header Section -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 rounded-2xl mb-4">
        <span class="text-2xl">✅</span>
      </div>
      <h2 class="text-3xl font-bold text-gray-900 mb-2">Final Review & Confirmation</h2>
      <p class="text-gray-600">Verify all information before completing your setup</p>
    </div>

    <!-- Review Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
      <div class="space-y-8">

        <!-- School Info -->
        <section class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl">
          <h3 class="text-xl font-semibold mb-4">🏫 School Information</h3>
          <p><strong>Name:</strong> {{ organizationName }}</p>
          <p><strong>Type:</strong> {{ organizationType }}</p>
          <p><strong>Country:</strong> {{ country }}</p>
          <p><strong>Region:</strong> {{ region || 'Not specified' }}</p>
        </section>

        <!-- Contact Info -->
        <section class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl">
          <h3 class="text-xl font-semibold mb-4">📞 Contact Details</h3>
          <p><strong>Email:</strong> {{ contactEmail }}</p>
          <p><strong>Phone:</strong> {{ contactPhone || 'Not specified' }}</p>
          <p><strong>Website:</strong> {{ website || 'Not specified' }}</p>
        </section>

        <!-- Education System -->
        <section class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl">
          <h3 class="text-xl font-semibold mb-4">📚 Education System</h3>
          <p><strong>Curriculum(s):</strong> {{ educationSystems.join(', ') }}</p>
        </section>

        <!-- Academic Term Structure -->
        <section class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl">
          <h3 class="text-xl font-semibold mb-4">📘 Academic Term Structure</h3>
          <div v-if="selectedStructure.length" class="space-y-6">
            <div
              v-for="(level, index) in selectedStructure"
              :key="level.levelName"
              class="border-l-4 border-rose-300 pl-6"
            >
              <h4 class="text-lg font-bold text-rose-700">{{ level.levelName }}</h4>
              <p v-if="level.streams?.length"><strong>Streams:</strong> {{ level.streams.join(', ') }}</p>
              <div v-if="level.requirements?.length">
                <div v-for="(req, i) in level.requirements" :key="i">
                  <p class="font-semibold">{{ req.header }}</p>
                  <ul class="list-disc list-inside">
                    <li v-for="(item, j) in req.items" :key="j">{{ item }}</li>
                  </ul>
                </div>
              </div>
              <div v-if="level.terms?.length">
                <div v-for="(term, tIndex) in level.terms" :key="tIndex" class="mt-4">
                  <p class="text-sm font-bold">{{ term.name }}</p>
                  <p class="text-sm">{{ term.start_date }} – {{ term.end_date }}</p>
                  <ul class="text-sm list-disc list-inside">
                    <li v-for="(fee, fIndex) in term.fees" :key="fIndex">
                      {{ fee.name }}: KES {{ fee.price || 0 }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-400 italic">No structure available.</p>
        </section>

        <!-- Services -->
        <section class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl">
          <h3 class="text-xl font-semibold mb-4">🛠 Services Offered</h3>
          <ul class="list-disc list-inside">
            <li v-for="s in services" :key="s">{{ s }}</li>
          </ul>
        </section>

        <!-- Facilities -->
        <section class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl">
          <h3 class="text-xl font-semibold mb-4">🏢 Facilities</h3>
          <div>
            <h4 class="font-semibold">Core Facilities</h4>
            <ul class="list-disc list-inside">
              <li v-for="f in facilities.core" :key="f">{{ f }}</li>
            </ul>
          </div>
          <div class="mt-2">
            <h4 class="font-semibold">Specialized Facilities</h4>
            <ul class="list-disc list-inside">
              <li v-for="f in facilities.specialized" :key="f">{{ f }}</li>
            </ul>
          </div>
        </section>

        <!-- Vehicles -->
        <section class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl" v-if="facilities.transport.vehicles.length">
          <h3 class="text-xl font-semibold mb-4">🚌 Transport Vehicles</h3>
          <ul class="list-disc list-inside">
            <li v-for="(v, index) in facilities.transport.vehicles" :key="index">
              {{ v.type }} - Capacity: {{ v.capacity }}
            </li>
          </ul>
        </section>

        <!-- Branding -->
        <section class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl">
          <h3 class="text-xl font-semibold mb-4">🎨 Branding</h3>
          <p><strong>Slogan:</strong> {{ slogan || 'N/A' }}</p>
          <p><strong>Brand Color:</strong> {{ brandColor || 'N/A' }}</p>
          <div v-if="logo">
            <p class="font-semibold mt-2">Logo Preview:</p>
            <img :src="URL.createObjectURL(logo)" alt="Logo" class="h-24 mt-1" />
          </div>
          <div v-if="background">
            <p class="font-semibold mt-4">Background Preview:</p>
            <img :src="URL.createObjectURL(background)" alt="Background" class="h-24 mt-1" />
          </div>
        </section>

        <!-- Actions -->
        <div class="flex justify-between pt-6">
          <button @click="emit('back')" class="px-6 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-700 dark:text-gray-200">← Back</button>
          <button @click="submitToBackend" class="px-6 py-2 bg-rose-600 text-white rounded-lg shadow hover:bg-rose-700">Submit & Finish →</button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useOnboardingStore } from '@/stores/useOnboardingStore';
import { storeToRefs } from 'pinia';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
const URL = window.URL || window.webkitURL;

const emit = defineEmits(['back', 'submit']);
const store = useOnboardingStore();

const {
  organizationName, organizationType, country, region,
  contactEmail, contactPhone, website,
  terms, streams, levels,
  services, custom,
  slogan, brandColor, logo, background,
  educationSystems, curriculum_key, curriculumKeys, curriculumData,
  facilities, structure
} = storeToRefs(store);

const selectedStructure = computed(() => {
  return Object.entries(structure.value || {})
    .filter(([_, level]) => level.selected)
    .map(([levelName, levelData]) => ({
      levelName,
      ...levelData
    }));
});

async function submitToBackend() {
  try {
    console.log('🚀 SUBMIT triggered');

    await axios.get('/sanctum/csrf-cookie');

    const payload = {
      slogan: slogan.value,
      brand_color: brandColor.value,
      curriculum_key: curriculum_key.value,
      curriculum_names: educationSystems.value,
      services: services.value,
      facilities: JSON.parse(JSON.stringify(facilities.value)),
      structure: JSON.parse(JSON.stringify(structure.value)),
    };

    const formData = new FormData();

    // Append each key manually
    Object.entries(payload).forEach(([key, value]) => {
      formData.append(key, typeof value === 'object' ? JSON.stringify(value) : value);
    });

    // Safely attach files
    if (logo.value instanceof File) {
      formData.append('logo', logo.value);
    }
    if (background.value instanceof File) {
      formData.append('background', background.value);
    }

    // Log final FormData for verification
    for (const [key, val] of formData.entries()) {
      console.log(`📦 FormData: ${key} =`, val);
    }

router.post('/school-admin/onboarding', formData, {
  preserveScroll: true,
  onSuccess: () => {
    store.showToast('School setup complete!', 'success');
    emit('submit');
    router.visit('/school-admin/dashboard');
  },
  onError: (errors) => {
    store.showToast('Failed to submit setup.', 'error');
    console.error('❌ Form submission error:', errors);
  }
});


  } catch (error) {
    console.error('❌ SUBMIT error:', error);
    const msg = error?.response?.data?.message || 'Failed to submit setup.';
    store.showToast(msg, 'error');
  }
}


onMounted(() => {
  console.log('[🧠 STRUCTURE]', JSON.stringify(structure.value, null, 2));
});
</script>

