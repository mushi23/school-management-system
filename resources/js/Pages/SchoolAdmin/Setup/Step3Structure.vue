<template>
  <div class="max-w-3xl mx-auto p-6">
    <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-6 text-center">School Facilities Setup</h2>

    <!-- Core Facilities -->
    <section class="mb-10 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6">
      <h3 class="text-xl font-semibold text-orange-600 mb-2">Core Facilities</h3>
      <p class="text-gray-500 mb-4 text-sm">
        These include learning spaces like classrooms and offices.
        Below are auto-generated based on your class/stream structure. You may customize them.
      </p>

      <div v-for="(item, index) in store.facilities.core" :key="'core-' + index" class="flex items-center mb-3 gap-2">
        <input
          v-model="store.facilities.core[index]"
          class="w-full border rounded-xl px-4 py-2 focus:ring-orange-200 focus:border-orange-400"
          placeholder="e.g. Grade 1 Blue"
        />
        <button @click="store.facilities.core.splice(index, 1)" class="text-red-500 hover:text-red-700">✖</button>
      </div>

      <div class="flex gap-4 mt-4 flex-wrap">
        <button @click="store.facilities.core.push('')" class="text-sm text-orange-600 dark:text-orange-300 hover:underline">
          + Add Custom Facility
        </button>
        <button @click="autoFillClassrooms" class="text-sm text-gray-500 dark:text-gray-300 hover:underline">
          🔁 Auto-fill Classrooms
        </button>
        <button @click="addSuggestedOffices" class="text-sm text-orange-500 dark:text-orange-300 hover:underline">
          🏢 Add Suggested Offices
        </button>
      </div>
    </section>

    <!-- Specialized Facilities -->
    <section class="mb-10 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6">
      <h3 class="text-xl font-semibold text-indigo-600 mb-2">Specialized Facilities</h3>
      <p class="text-gray-500 mb-4 text-sm">Facilities used for specific subjects or co-curriculars (e.g., labs, music rooms, fields).</p>

      <div v-for="(item, index) in store.facilities.specialized" :key="'spec-' + index" class="flex items-center mb-3 gap-2">
        <input
          v-model="store.facilities.specialized[index]"
          placeholder="e.g. Chemistry Lab"
          class="w-full border rounded-xl px-4 py-2 focus:ring-indigo-200 focus:border-indigo-400"
        />
        <button @click="store.facilities.specialized.splice(index, 1)" class="text-red-500 hover:text-red-700">✖</button>
      </div>

      <div class="flex gap-4 mt-2 flex-wrap">
        <button @click="store.facilities.specialized.push('')" class="text-sm text-indigo-600 hover:underline">
          + Add Specialized Facility
        </button>
        <button @click="addSuggestedSpecialized" class="text-sm text-indigo-500 hover:underline">
          🧪 Add Suggested Specialized Facilities
        </button>
      </div>
    </section>

    <!-- Transport & Parking -->
    <section class="mb-10 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6">
      <h3 class="text-xl font-semibold text-teal-600 mb-2">School Transport & Parking</h3>
      <p class="text-gray-500 mb-4 text-sm">Capture available parking and transport vehicles in your school.</p>

      <div class="mb-6">
        <label class="block font-semibold text-sm text-gray-700 mb-1">Parking Spaces (Total)</label>
        <input
          v-model.number="store.facilities.transport.parkingSpaces"
          type="number"
          min="0"
          placeholder="e.g. 15"
          class="w-full border rounded-xl px-4 py-2"
        />
      </div>

<div class="mb-4">
  <h4 class="text-md font-semibold text-gray-700 mb-2">School Vehicles</h4>

  <div
    v-for="(vehicle, index) in store.facilities.transport.vehicles"
    :key="'vehicle-' + index"
    class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3 items-end"
  >
    <!-- Vehicle Type Select -->
    <select
      v-model="vehicle.type"
      @change="setDefaultCapacity(vehicle)"
      class="w-full border rounded-xl px-4 py-2"
    >
      <option disabled value="">Select Vehicle Type</option>
      <option v-for="type in vehicleTypes" :key="type.label" :value="type.label">
        {{ type.label }}
      </option>
    </select>

    <!-- Editable Capacity -->
    <input
      v-model.number="vehicle.capacity"
      type="number"
      min="1"
      placeholder="Capacity"
      class="w-full border rounded-xl px-4 py-2"
    />

    <!-- -->
    <button @click="store.facilities.transport.vehicles.splice(index, 1)" class="text-red-500 hover:text-red-700">
      ✖ Remove
    </button>
  </div>

  <button @click="addVehicle()" class="text-sm text-teal-600 hover:underline">
    + Add Another Vehicle
  </button>
</div>

    </section>

    <!-- Navigation Buttons -->
    <div class="flex justify-between items-center mt-10 pt-6 border-t border-gray-200 dark:border-gray-700">
      <button @click="$emit('back')" class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 text-sm font-medium">← Back</button>
      <button @click="$emit('next')" class="bg-orange-600 text-white px-6 py-3 rounded-xl hover:bg-orange-700">Continue →</button>
    </div>
  </div>
</template>


<script setup>
import { useOnboardingStore } from '@/stores/useOnboardingStore';
import { computed, onMounted } from 'vue';
import { watchEffect } from 'vue'

const onboarding = useOnboardingStore()



const store = useOnboardingStore();
defineEmits(['next', 'back']);

// 🏫 Suggested Facility Presets
const commonOffices = [
  'Headteacher's Office',
  'Deputy Headteacher's Office',
  'Staffroom',
  'Reception',
  'Administrator Office',
  'Bursar Office',
  'Counselor's Office',
  'Library',
  'Meeting Room',
  'ICT Room',
  'Sick Bay',
  'Store Room',
  'Kitchen',
  'Dining Hall',
];

const specializedDefaults = [
  'Science Lab',
  'Computer Lab',
  'Home Science Room',
  'Art Room',
  'Music Room',
  'Drama Hall',
  'Sports Field',
  'Swimming Pool',
  'Chapel',
  'Workshop',
  'Agriculture Plot',
  'Greenhouse',
];

// 🧠 Computed: Classrooms from Step 2
const groupedClassrooms = computed(() => store.generateClassroomsFromStructure());
const classrooms = computed(() => groupedClassrooms.value.flatMap(group => group.classrooms ?? []));

// 🚀 On Mount Check
onMounted(() => {
  console.log('✅ [Step3 Mounted]');
  console.log('Levels:', store.levels);
  console.log('Streams:', store.streams);

  if (!store.levels.length || !store.streams) {
    store.showToast('Missing class structure data. Please go back and fill in.', 'error');
  }

  const generated = store.generateClassroomsFromStructure();
  console.log('Generated classrooms:', generated);
});

// 🚐 Predefined vehicle types and their default capacities
const vehicleTypes = [
  { label: 'Bus', capacity: 33 },
  { label: 'Minibus', capacity: 14 },
  { label: 'Van', capacity: 8 },
  { label: 'Saloon Car', capacity: 4 },
  { label: 'Pickup Truck', capacity: 2 }
];

// ➕ Add blank vehicle object
function addVehicle() {
  store.facilities.transport.vehicles.push({ type: '', capacity: null });
}

watchEffect(() => {
  console.log('Structure updated:', onboarding.structure)
})

// 🧠 Auto-fill capacity when type selected
function setDefaultCapacity(vehicle) {
  const match = vehicleTypes.find(v => v.label === vehicle.type);
  if (match && !vehicle.capacity) {
    vehicle.capacity = match.capacity;
  }
}


// 📥 Auto-fill Classrooms
function autoFillClassrooms() {
  const generated = store.generateClassroomsFromStructure()
    .flatMap(level => level.classrooms.map(room => room.name));

  const current = new Set(store.facilities.core.map(f => f.toLowerCase()));
  const newOnes = generated.filter(name => !current.has(name.toLowerCase()));

  store.facilities.core.push(...newOnes);
}

// 🏢 Add Suggested Offices
function addSuggestedOffices() {
  const current = new Set(store.facilities.core.map(f => f.toLowerCase()));
  const newOnes = commonOffices.filter(name => !current.has(name.toLowerCase()));
  store.facilities.core.push(...newOnes);
}

// 🧪 Add Suggested Specialized Facilities
function addSuggestedSpecialized() {
  const current = new Set(store.facilities.specialized.map(f => f.toLowerCase()));
  const newOnes = specializedDefaults.filter(name => !current.has(name.toLowerCase()));
  store.facilities.specialized.push(...newOnes);
}
</script>
