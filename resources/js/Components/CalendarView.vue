<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 w-full max-w-md border border-gray-200 dark:border-gray-700 transition-colors duration-200">
    <!-- Controls -->
    <div class="flex items-center justify-between mb-4">
      <select 
        v-model="selectedMonth"
        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 transition-colors duration-200"
      >
        <option v-for="(month, i) in months" :key="i" :value="i">{{ month }}</option>
      </select>

      <select 
        v-model="selectedYear"
        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 ml-2 transition-colors duration-200"
      >
        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
      </select>
    </div>

    <!-- Weekday headers -->
    <div class="grid grid-cols-7 gap-1 mb-2">
      <span 
        v-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" 
        :key="d" 
        class="text-xs font-medium text-center text-gray-500 dark:text-gray-400"
      >
        {{ d }}
      </span>
    </div>

    <!-- Calendar grid -->
    <div class="grid grid-cols-7 gap-1">
      <span 
        v-for="blank in firstDayOfMonth" 
        :key="'b' + blank" 
        class="h-8"
      ></span>

      <button
        type="button"
        v-for="day in daysInMonth"
        :key="day"
        class="h-8 w-full flex items-center justify-center text-sm rounded-md transition-colors relative"
        :class="{
          'bg-blue-600 text-white font-medium': isSelected(day),
          'text-gray-400 dark:text-gray-500': isFuture(day),
          'text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700': !isFuture(day) && !isSelected(day)
        }"
        @click="selectDay(day)"
      >
        {{ day }}
        <span 
          v-if="getHoliday(day)" 
          class="absolute -top-0.5 -right-0.5 h-1.5 w-1.5 rounded-full bg-red-400"
          :title="getHoliday(day)?.name"
        ></span>
      </button>
    </div>

    <!-- Selected date display -->
    <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-300">
      <span v-if="props.modelValue">Selected: {{ formattedSelectedDate }}</span>
      <span v-else class="text-gray-400 dark:text-gray-500">No date selected</span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: String,
})

const emit = defineEmits(['update:modelValue'])

const today = new Date()
const selectedDate = props.modelValue ? new Date(props.modelValue) : new Date()

const selectedYear = ref(selectedDate.getFullYear())
const selectedMonth = ref(selectedDate.getMonth())
const selectedDay = ref(selectedDate.getDate())

const months = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]

const currentYear = today.getFullYear()
const years = Array.from({ length: 91 }, (_, i) => currentYear - i)

const daysInMonth = computed(() => {
  return new Date(selectedYear.value, selectedMonth.value + 1, 0).getDate()
})

const firstDayOfMonth = computed(() => {
  return new Date(selectedYear.value, selectedMonth.value, 1).getDay()
})

const holidays = ref([])

const formattedSelectedDate = computed(() => {
  if (!props.modelValue) return ''
  const date = new Date(props.modelValue)
  return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`
})

function pad(n) {
  return n < 10 ? '0' + n : n
}

function formatDate(year, month, day) {
  return `${year}-${pad(month + 1)}-${pad(day)}`
}

function selectDay(day) {
  if (isFuture(day)) return
  selectedDay.value = day
  const iso = formatDate(selectedYear.value, selectedMonth.value, day)
  emit('update:modelValue', iso)
}

function isSelected(day) {
  const iso = formatDate(selectedYear.value, selectedMonth.value, day)
  return props.modelValue === iso
}

function isFuture(day) {
  const date = new Date(selectedYear.value, selectedMonth.value, day)
  return date > today
}

function getHoliday(day) {
  const iso = formatDate(selectedYear.value, selectedMonth.value, day)
  return holidays.value.find(h => h.date === iso)
}

function isHoliday(day) {
  return !!getHoliday(day)
}

async function loadHolidays() {
  try {
    const { data } = await axios.get('/api/holidays')
    holidays.value = data.map(h => ({
      date: h.date.iso,
      name: h.name || 'Holiday'
    }))
  } catch (err) {
    console.error('Failed to load holidays:', err)
  }
}

onMounted(() => {
  loadHolidays()
})

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    const date = new Date(newVal)
    selectedYear.value = date.getFullYear()
    selectedMonth.value = date.getMonth()
    selectedDay.value = date.getDate()
  }
})
</script>

<style scoped>
button[disabled] {
  cursor: not-allowed;
}
</style>



