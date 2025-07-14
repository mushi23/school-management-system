<template>
  <div class="max-w-4xl mx-auto p-6">
    <!-- Header Section -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl mb-4">
        <span class="text-2xl">📚</span>
      </div>
      <h2 class="text-3xl font-bold text-gray-900 mb-2">CBC Structure Setup</h2>
      <p class="text-gray-600">Configure your Competency-Based Curriculum structure</p>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
<!-- Curriculum Stages -->
<div v-for="(stage, stageKey) in curriculumData.stages" :key="stageKey" class="mb-8">
  <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
    <span class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full mr-3">
      {{ stageKey.split('_')[1] }}
    </span>
    {{ formatStageName(stageKey) }}
  </h3>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <div
      v-for="(level, levelKey) in stage.levels"
      :key="levelKey"
      class="p-4 rounded-lg border-2 transition-all duration-200 cursor-pointer"
      :class="{
        'border-blue-500 bg-blue-50': structure[levelKey]?.selected,
        'border-gray-200 hover:border-blue-300': !structure[levelKey]?.selected
      }"
      @click="handleLevelClick(levelKey, $event)"
    >
      <div class="flex items-center justify-between">
        <span class="font-medium text-gray-800">{{ level.name || levelKey }}</span>
        <span
          class="w-5 h-5 rounded-full flex items-center justify-center text-xs"
          :class="{
            'bg-blue-500 text-white': structure[levelKey]?.selected,
            'bg-gray-200 text-gray-500': !structure[levelKey]?.selected
          }"
        >
          {{ structure[levelKey]?.selected ? '✓' : '+' }}
        </span>
      </div>
      
      <button
        v-if="structure[levelKey]?.selected"
        @click.stop="openLevelModal(levelKey)"
        class="edit-level-btn text-blue-600 text-xs mt-2 underline hover:text-blue-800"
      >
        Edit Configuration
      </button>
    </div>
  </div>
</div>


      <!-- Action Buttons -->
      <div class="flex items-center justify-between pt-8 border-t border-gray-100">
        <button 
          @click="emit('back')"
          class="group inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl transition-all duration-200 hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 active:scale-95"
        >
          <svg class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
          </svg>
          <span>Back</span>
        </button>
        
        <button 
          @click="handleNext"
          :disabled="isLoading"
          class="group relative inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl transition-all duration-200 hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg hover:shadow-blue-500/25 focus:outline-none focus:ring-4 focus:ring-blue-100 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
        >
          <span>{{ isLoading ? 'Saving...' : 'Save & Continue' }}</span>
          <svg v-if="!isLoading" class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Level Configuration Modal -->
    <div
      v-if="activeLevel"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-6 flex justify-between items-center z-10">
          <h3 class="text-xl font-bold text-gray-800">
            Configuring: <span class="text-blue-600">{{ activeLevel }}</span>
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>


        <!-- Step Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <div class="flex px-6">
            <button
              v-for="(step, index) in steps"
              :key="index"
              @click="modalStep = index + 1"
              class="px-4 py-3 text-sm font-medium relative"
              :class="{
                'text-blue-600': modalStep === index + 1,
                'text-gray-500 hover:text-gray-700': modalStep !== index + 1
              }"
            >
              {{ step }}
              <span
                v-if="modalStep === index + 1"
                class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500 rounded-t"
              ></span>
            </button>
          </div>
        </div>

        <!-- Modal Content -->
        <div class="p-6 space-y-6">
          <!-- Step 1: Fee Breakdown -->
          <div v-if="modalStep === 1" class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-800 flex items-center">
              <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Fee Structure For New Admissions
            </h4>
 <div class="space-y-4">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Number of terms for this level : </span>
                </label>
                <input
                  type="number"
                  min="1"
                  max="4"
                  v-model.number="termCount"
                  @change="updateTermList"
                  class="input input-bordered w-full max-w-xs"
                />
              </div>
</div>
            
            <div class="space-y-3">
              <div
                v-for="(item, index) in structure[activeLevel].fees"
                :key="index"
                class="flex gap-3 items-center"
              >
                <input
                  v-model="structure[activeLevel].fees[index].name"
                  type="text"
                  class="flex-1 input input-bordered"
                  placeholder="Fee Name (e.g. Tuition)"
                />
                <div class="relative w-32">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">KES</span>
                  <input
                    v-model="structure[activeLevel].fees[index].price"
                    type="number"
                    class="input input-bordered w-full pl-10"
                    placeholder="Amount"
                  />
                </div>

                <button
                  @click="structure[activeLevel].fees.splice(index, 1)"
                  class="btn btn-circle btn-sm btn-ghost text-red-500 hover:bg-red-50"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
            
            <button
              @click="structure[activeLevel].fees.push({ name: '', price: '' })"
              class="btn btn-sm btn-outline border-blue-500 text-blue-600 hover:bg-blue-50"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Fee Item
            </button>
          </div>

          <!-- Step 2: Requirements -->
          <div v-else-if="modalStep === 2" class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-800 flex items-center">
              <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              Student Requirements
            </h4>
            
            <div class="space-y-4">
              <div
                v-for="(group, groupIndex) in structure[activeLevel].requirements"
                :key="groupIndex"
                class="border rounded-lg p-4 bg-gray-50"
              >
                <div class="flex items-center justify-between mb-3">
                  <input
                    v-model="group.header"
                    class="input input-bordered font-semibold w-full mr-2"
                    placeholder="Requirement Group Name (e.g. Uniform)"
                  />
                  <button
                    @click="removeGroup(groupIndex)"
                    class="btn btn-circle btn-sm btn-ghost text-red-500 hover:bg-red-50"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>

                <div class="space-y-2">
                  <div
                    v-for="(item, itemIndex) in group.items"
                    :key="itemIndex"
                    class="flex gap-2 items-center"
                  >
                    <input
                      v-model="group.items[itemIndex]"
                      type="text"
                      class="input input-bordered flex-1"
                      placeholder="e.g. Black shoes, Blue sweater"
                    />
                    <button
                      @click="group.items.splice(itemIndex, 1)"
                      class="btn btn-circle btn-sm btn-ghost text-red-500 hover:bg-red-50"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </div>

                <button
                  @click="group.items.push('')"
                  class="btn btn-sm btn-outline mt-3 border-blue-500 text-blue-600 hover:bg-blue-50"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add Item
                </button>
              </div>
            </div>

            <div class="flex items-center gap-3 mt-4">
              <input
                v-model="newGroupHeader"
                type="text"
                class="input input-bordered flex-1"
                placeholder="New requirement group name"
              />
              <button
                @click="addRequirementGroup"
                class="btn btn-sm btn-primary"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Group
              </button>
            </div>
          </div>

          <!-- Step 3: Streams -->
          <div v-else-if="modalStep === 3" class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-800 flex items-center">
              <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
              </svg>
              Class Streams
            </h4>
            
            <div class="space-y-4">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Number of streams in this level : </span>
                </label>
                <input
                  type="number"
                  min="1"
                  max="10"
                  v-model.number="streamCount"
                  @change="updateStreamList"
                  class="input input-bordered w-full max-w-xs"
                />
              </div>

              <div v-if="structure[activeLevel].streams.length > 0" class="space-y-3">
                <label class="label">
                  <span class="label-text">Stream Names</span>
                </label>
                <div v-for="(name, index) in structure[activeLevel].streams" :key="index" class="flex items-center gap-2">
                  <span class="w-8 text-gray-500">#{{ index + 1 }}</span>
                  <input
                    v-model="structure[activeLevel].streams[index]"
                    type="text"
                    class="input input-bordered flex-1"
                    placeholder="e.g. A, B, C or Red, Blue, Green"
                  />
                </div>
              </div>
            </div>
          </div>

         <!-- Step 4: Academic Calendar -->
<div v-else-if="modalStep === 4" class="space-y-4">
  <h4 class="text-lg font-semibold text-gray-800 flex items-center">
    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
    Academic Calendar
  </h4>

  <!-- Select term for Fee Structure -->
  <div v-if="activeLevel && structure[activeLevel]?.terms?.length" class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">
      Select Term
    </label>
    <select v-model="selectedTermIndex" class="select select-bordered w-full max-w-xs">
      <option
        v-for="(term, index) in structure[activeLevel].terms"
        :key="index"
        :value="index"
      >
        {{ term.name || `Term ${index + 1}` }}
      </option>
    </select>
  </div>

  <!-- Fee Structure Table -->
  <div
    v-if="structure[activeLevel]?.fees?.length && structure[activeLevel]?.terms?.length"
    class="mt-6"
  >
    <h4 class="text-lg font-semibold text-gray-800 mb-4">
      Fee Structure for {{ structure[activeLevel].terms[selectedTermIndex]?.name || `Term ${selectedTermIndex + 1}` }}
    </h4>

    <div class="overflow-x-auto">
      <table class="min-w-full table-auto border-collapse border border-gray-300">
        <thead class="bg-gray-100">
          <tr>
            <th class="border border-gray-300 px-4 py-2">Fee Item</th>
            <th class="border border-gray-300 px-4 py-2">Amount (KES)</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(feeItem, feeIndex) in structure[activeLevel].fees"
            :key="feeIndex"
          >
            <td class="border border-gray-300 px-4 py-2 font-semibold">
              {{ feeItem.name || `Fee ${feeIndex + 1}` }}
            </td>
            <td class="border border-gray-300 px-2 py-1">
              <input
                v-if="structure[activeLevel].terms[selectedTermIndex]?.fees?.[feeIndex]"
                type="number"
                class="w-full border border-gray-300 rounded px-2 py-1"
                v-model.number="structure[activeLevel].terms[selectedTermIndex].fees[feeIndex].price"
                placeholder="KES"
              />
              <span v-else class="text-gray-400 italic text-sm">N/A</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Calendar Terms and Dates -->
  <div
    v-if="activeLevel && structure[activeLevel]?.terms?.length"
    v-for="(term, index) in structure[activeLevel].terms"
    :key="index"
    class="border rounded-lg p-4 bg-gray-50"
  >
    <div class="mb-3">
      <input
        v-model="term.name"
        type="text"
        class="input input-bordered w-full"
        placeholder="Term Name (e.g. Term 1)"
      />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="form-control">
        <label class="label">
          <span class="label-text">Start Date</span>
        </label>
        <div class="relative">
          <input
            :value="term.start_date"
            @click="openDatePicker(index, 'start_date')"
            readonly
            class="input input-bordered w-full cursor-pointer"
            placeholder="Select start date"
          />
        </div>
      </div>

      <div class="form-control">
        <label class="label">
          <span class="label-text">End Date</span>
        </label>
        <div class="relative">
          <input
            :value="term.end_date"
            @click="openDatePicker(index, 'end_date')"
            readonly
            class="input input-bordered w-full cursor-pointer"
            placeholder="Select end date"
          />
        </div>
      </div>
    </div>
  </div>
</div>
</div>
        

        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 flex justify-between">
          <button
            v-if="modalStep > 1"
            @click="modalStep--"
            class="btn btn-outline border-gray-300 dark:border-gray-700 hover:border-gray-400"
          >
            Back
          </button>
          <div v-else></div>
          
          <button
            v-if="modalStep < 4"
            @click="modalStep++"
            class="btn btn-primary"
          >
            Next
          </button>
          <button
            v-else
            @click="closeModal"
            class="btn btn-success"
          >
            Save & Close
          </button>
        </div>
      </div>
    </div>

    <!-- Date Picker Modal -->
  <div
    v-if="editingDate.termIndex !== null"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
  >
    <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h4 class="text-lg font-bold">
            Select {{ editingDate.field === 'start_date' ? 'Start' : 'End' }} Date
          </h4>
          <button @click="editingDate.termIndex = null" class="btn btn-circle btn-sm btn-ghost">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <vue-cal
  style="height: 400px"
  class="vuecal--rounded-theme holiday-calendar"
  :time="false"
  :disable-views="['years', 'year', 'day', 'week']"
  :events="holidayEvents"
  @cell-click="selectDate"
  @event-click="showHolidayDetails"
  :min-date="minDate"
  :max-date="maxDate"
  active-view="month"
  hide-view-selector
  :selected-date="selectedDate"
  events-on-month-view="short"
>
  <template #event="{ event }">
    <div class="holiday-badge" :class="event.class">
      <div class="holiday-icon">
        <svg v-if="event.type === 'Public holiday'" width="12" height="12" viewBox="0 0 24 24">
          <path fill="currentColor" d="M12,2L4.5,20.29L5.21,21L12,18L18.79,21L19.5,20.29L12,2Z"/>
        </svg>
        <svg v-else-if="event.type === 'Observance'" width="12" height="12" viewBox="0 0 24 24">
          <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2Z"/>
        </svg>
      </div>
      <span class="holiday-title">{{ event.title }}</span>
    </div>
  </template>
</vue-cal>
      </div>
    </div>
  </div>

<!-- Holiday Details Modal -->
<div
  v-if="selectedHoliday"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
  @click.self="selectedHoliday = null"
>
  <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md overflow-hidden shadow-xl">
    <div class="modal-header" :class="selectedHoliday.class">
      <h3 class="text-xl font-bold text-white px-6 py-4">{{ selectedHoliday.title }}</h3>
    </div>
    <div class="p-6 space-y-4">
      <div class="flex items-center text-gray-600">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>{{ formatHolidayDate(selectedHoliday.start) }}</span>
      </div>
      
      <div class="flex items-center text-gray-600">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ selectedHoliday.type }}</span>
      </div>
      
      <div class="pt-2 border-t border-gray-100">
        <p class="text-gray-700">{{ selectedHoliday.description }}</p>
      </div>
      
      <div class="flex justify-end pt-4">
        <button 
          @click="selectedHoliday = null"
          class="btn btn-outline border-gray-300 dark:border-gray-700 hover:border-gray-400"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</div>
</div>
</template>



<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useOnboardingStore } from '@/stores/useOnboardingStore'
import VueCal from 'vue-cal'
import 'vue-cal/dist/vuecal.css'
import { watch } from 'vue'
import { storeToRefs } from 'pinia'


const selectedTermIndex = ref(0)

const emit = defineEmits(['next', 'back'])
const selectedHoliday = ref(null)

const selectedStreams = computed(() => {
  return (levelKey) => {
    const level = structure.value?.[levelKey];
    return level?.streams?.filter(s => s?.selected) || [];
  };
});


function showHolidayDetails(event) {
  selectedHoliday.value = event
  event.event.stopPropagation()
}

function formatHolidayDate(date) {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// New: Get holiday type class based on primary_type
function getHolidayClass(holiday) {
  const type = holiday.primary_type.toLowerCase()
  switch (type) {
    case 'public holiday':
      return 'public-holiday'
    case 'national holiday':
      return 'national-holiday'
    case 'observance':
      return 'observance-holiday'
    case 'optional holiday':
      return 'optional-holiday'
    case 'season':
      return 'season-event'
    default:
      return 'holiday-event'
  }
}

async function loadHolidays() {
  try {
    const { data } = await axios.get('/api/holidays')
    holidayEvents.value = data.map(h => ({
      start: new Date(h.date.iso),
      end: new Date(h.date.iso),
      title: h.name,
      description: h.description || '',
      class: getHolidayClass(h), // This should use the function we defined earlier
      type: h.primary_type,
      country: h.country.name,
      locations: h.locations,
      content: `<div class="holiday-tooltip">
        <h4>${h.name}</h4>
        <p><strong>Type:</strong> ${h.primary_type}</p>
        <p>${h.description}</p>
      </div>`,
      fullDetails: h
    }))
  } catch (err) {
    console.error('Failed to load holidays:', err)
    alert('Failed to load holiday data')
  }
}


// Data
const store = useOnboardingStore()
const curriculumData = ref({ stages: {} })
const { structure } = storeToRefs(store)
const termStructure = ref([])


const activeLevel = ref(null)
const modalStep = ref(1)
const newGroupHeader = ref('')
const streamCount = ref(1)
const termCount = ref(1)
let lastActiveLevel = null
const holidayEvents = ref([])
const editingDate = ref({
  termIndex: null,
  field: '',
  open: false
})
const isLoading = ref(false)

const steps = ['Fees', 'Requirements', 'Streams', 'Calendar']
const minDate = new Date(new Date().getFullYear(), 0, 1)
const maxDate = new Date(new Date().getFullYear() + 1, 11, 31)

// Computed
const selectedDate = computed(() => {
  if (editingDate.value.termIndex === null) return null
  const dateStr = structure.value[activeLevel.value].terms[editingDate.value.termIndex][editingDate.value.field]
  return dateStr ? new Date(dateStr) : new Date()
})

watch(termStructure, (newTerms) => {
  const termCount = newTerms.length
if (!structure.value[activeLevel.value].termFees || structure.value[activeLevel.value].termFees.length !== termCount) {
  structure.value[activeLevel.value].termFees = Array.from({ length: termCount }, () => [])
}
})

watch(modalStep, (step) => {
  if (step === 4 && structure.value[activeLevel.value]?.terms?.length) {
    syncFeesToTerms()
  }
})

watch(
  () => structure.value[activeLevel.value]?.terms?.map(term => term.fees?.map(f => f.price)),
  () => {
    store.setStructure(structure.value)
  },
  { deep: true }
)

function deepCleanStructure(rawStructure) {
  const result = {}

  for (const [levelKey, level] of Object.entries(rawStructure)) {
    if (!level.selected) continue

    const cleaned = {
      selected: true,

      fees: Array.isArray(level.fees)
        ? level.fees
            .filter(f => f.name?.trim() && f.price !== '')
            .map(f => ({
              name: f.name.trim(),
              price: Number(f.price) || 0
            }))
        : [],

      requirements: Array.isArray(level.requirements)
        ? level.requirements
            .filter(r => r.header?.trim() && Array.isArray(r.items) && r.items.length > 0)
            .map(r => ({
              header: r.header.trim(),
              items: r.items.filter(i => i?.trim())
            }))
        : [],

      streams: Array.isArray(level.streams)
        ? level.streams.filter(s => typeof s === 'string' && s.trim())
        : [],

      terms: Array.isArray(level.terms)
        ? level.terms
            .filter(t => t.name?.trim() && t.start_date && t.end_date)
            .map(t => ({
              name: t.name.trim(),
              start_date: t.start_date,
              end_date: t.end_date,
              fees: Array.isArray(t.fees)
                ? t.fees
                    .filter(f => f.name?.trim() && f.price !== '')
                    .map(f => ({
                      name: f.name.trim(),
                      price: Number(f.price) || 0
                    }))
                : []
            }))
        : []
    }

    result[levelKey] = cleaned
  }

  return result
}


// Methods
function toggleLevel(levelKey) {
  const current = structure.value[levelKey]
  current.selected = !current.selected

  if (current.selected) {
    modalStep.value = 1

    if (lastActiveLevel && lastActiveLevel !== levelKey) {
      const previous = structure.value[lastActiveLevel]

      current.fees = previous.fees.map(fee => ({ name: fee.name, price: fee.price }))
      current.requirements = previous.requirements.map(group => ({
        header: group.header,
        items: [...group.items]
      }))
      current.streams = [...previous.streams]
      current.terms = previous.terms.map(term => ({
        name: term.name,
        start_date: term.start_date,
        end_date: term.end_date
      }))
    }

    activeLevel.value = levelKey
    streamCount.value = current.streams.length || 1
    termCount.value = current.terms.length || 1
    lastActiveLevel = levelKey
  } else if (activeLevel.value === levelKey) {
    activeLevel.value = null
  }

  // ✅ Persist structure changes

  store.setStructure(structure.value)
}



function syncFeesToTerms() {
  const level = structure.value[activeLevel.value];
  const feeTemplate = level.fees || [];

  level.terms = level.terms || [];

  level.terms.forEach((term, termIndex) => {
    term.fees = term.fees || [];

    for (let i = 0; i < feeTemplate.length; i++) {
      const globalFee = feeTemplate[i];
      const existingFee = term.fees[i];

      if (existingFee) {
        // Keep price, update name
        existingFee.name = globalFee.name || `Fee ${i + 1}`;
      } else {
        // Add new fee
        term.fees[i] = {
          name: globalFee.name,
          price: globalFee.price ?? 0,
        };
      }
    }

    // Trim extra fees if needed
    if (term.fees.length > feeTemplate.length) {
      term.fees = term.fees.slice(0, feeTemplate.length);
    }
  });
}

function initializeLevelStructure(levelIndex) {
  const level = structure.value[levelIndex];
  if (!level) return console.warn('⚠️ Level not found for index:', levelIndex);

  level.fees = level.fees || [];
  level.terms = level.terms || [];

  level.terms.forEach(term => {
    term.fees = level.fees.map(f => ({
      name: f.name,
      price: f.price || 0,
    }));
  });
}

function handleLevelClick(levelKey, event) {
  // If edit button was clicked, skip toggling
  if (event?.target?.closest('.edit-level-btn')) return

  toggleLevel(levelKey)
}

function openLevelModal(levelKey) {
  if (!structure.value[levelKey].selected) return
  activeLevel.value = levelKey
  modalStep.value = 1
}



function updateTermList() {
  const count = Math.min(4, Math.max(1, termCount.value));
  const level = structure.value[activeLevel.value];
  const updatedTerms = [...level.terms];
  const feeTemplate = (level.fees || []).map(fee => ({
    name: fee.name,
    price: 0,
  }));

  for (let i = level.terms.length; i < count; i++) {
    let startDate = '';
    let endDate = '';

    if (i > 0 && updatedTerms[i - 1]?.end_date) {
      const prevEnd = new Date(updatedTerms[i - 1].end_date);
      if (!isNaN(prevEnd)) {
        const start = new Date(prevEnd);
        start.setDate(start.getDate() + 14);
        const end = new Date(start);
        end.setDate(start.getDate() + 90);
        startDate = formatDate(start);
        endDate = formatDate(end);
      }
    }

    updatedTerms.push({
      name: `Term ${i + 1}`,
      start_date: startDate,
      end_date: endDate,
      fees: JSON.parse(JSON.stringify(feeTemplate))
    });
  }

  if (count < updatedTerms.length) {
    updatedTerms.splice(count);
  }

  structure.value[activeLevel.value].terms = updatedTerms;
  initializeLevelStructure(activeLevel.value); // ✅ CORRECT
  syncFeesToTerms();
  store.setStructure(structure.value);
}



function formatDate(date) {
  return date.toISOString().split('T')[0]
}

function closeModal() {
  activeLevel.value = null
  modalStep.value = 1
}

function removeGroup(index) {
  structure.value[activeLevel.value].requirements.splice(index, 1)
  store.setStructure(structure.value)
}

function addRequirementGroup() {
  if (!newGroupHeader.value.trim()) return
  structure.value[activeLevel.value].requirements.push({
    header: newGroupHeader.value.trim(),
    items: ['']
  })
  newGroupHeader.value = ''
  store.setStructure(structure.value)
}

function formatStageName(name) {
  return name.replace(/_/g, ' ')
}

function updateStreamList() {
  const count = Math.min(10, Math.max(1, streamCount.value))
  const currentStreams = structure.value[activeLevel.value].streams || []
  const updated = []

  for (let i = 0; i < count; i++) {
    updated.push(currentStreams[i] || String.fromCharCode(65 + i))
  }

  store.setStructure(structure.value)
  structure.value[activeLevel.value].streams = updated
}


function validateTermDates() {
  for (const levelKey in structure.value) {
    const level = structure.value[levelKey]
    if (!level.selected) continue

    for (const term of level.terms) {
      // Check for empty dates
      if (!term.start_date || !term.end_date) {
        alert(`Please complete all term dates for ${levelKey}`)
        return false
      }

      const start = new Date(term.start_date)
      const end = new Date(term.end_date)

      // Check date order
      if (start >= end) {
        alert(`Start date must be before end date for term "${term.name}" in ${levelKey}`)
        return false
      }

      // Check for overlaps
      const sortedTerms = [...level.terms].sort((a, b) =>
        new Date(a.start_date) - new Date(b.start_date)
      )
      for (let i = 1; i < sortedTerms.length; i++) {
        const prevEnd = new Date(sortedTerms[i - 1].end_date)
        const currStart = new Date(sortedTerms[i].start_date)
        if (currStart <= prevEnd) {
          alert(`Term "${sortedTerms[i].name}" overlaps with "${sortedTerms[i - 1].name}"`)
          return false
        }
      }
    }
  }
  return true
}

function isDisabledDate(date) {
  const d = new Date(date)
  
  // Disable weekends
  if ([0, 6].includes(d.getDay())) return true

  // Disable holidays
  const iso = d.toISOString().split('T')[0]
  return holidayEvents.value.some(ev => {
    const evDate = new Date(ev.start).toISOString().split('T')[0]
    return evDate === iso
  })
}

function handleHolidayClick(event) {
  store.alert(`Holiday: ${event.title}`, 'info')
}

function formatLocalDate(date) {
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function selectDate(day) {
  const dateValue = day.date || day
  if (!dateValue) return

  const dateStr = formatLocalDate(dateValue)
  const { termIndex, field } = editingDate.value
  structure.value[activeLevel.value].terms[termIndex][field] = dateStr
  editingDate.value.termIndex = null
  store.setStructure(structure.value)
}

function openDatePicker(index, field) {
  editingDate.value.termIndex = index
  editingDate.value.field = field
}

async function handleNext() {
  // Custom curriculum validation
  const selectedLevels = Object.entries(structure.value)
    .filter(([_, level]) => level.selected)

  if (selectedLevels.length === 0) {
    alert('Please select at least one level and complete its structure.')
    return
  }

  for (const [levelKey, level] of selectedLevels) {
    if (!level.fees?.length) {
      alert(`Please add at least one fee for ${levelKey}.`)
      return
    }

    if (!level.streams?.length) {
      alert(`Please define at least one stream for ${levelKey}.`)
      return
    }

    if (!level.terms?.length) {
      alert(`Please define at least one term for ${levelKey}.`)
      return
    }

    for (const term of level.terms) {
      if (!term.start_date || !term.end_date) {
        alert(`Please complete term dates for all terms in ${levelKey}.`)
        return
      }
    }
  }

  // Term-level validation
  if (!validateTermDates()) return

  isLoading.value = true

  try {
    // Extract levels & streams
    const levels = selectedLevels.map(([levelKey, value]) => ({
      name: levelKey,
      classes: value.streams.join(', ')
    }))

    const allStreams = new Set()
    for (const [_, level] of selectedLevels) {
      (level.streams || []).forEach(s => allStreams.add(s))
    }

    store.updateStepData({
      levels,
      streams: Array.from(allStreams).join(', ')
    })

    const cleaned = deepCleanStructure(structure.value)
structure.value = cleaned
await store.setStructure(cleaned)

    alert('Structure saved successfully!', 'success')
    emit('next')
  } catch (error) {
    alert('Failed to save structure', 'error')
    console.error('Save error:', error)
  } finally {
    isLoading.value = false
  }
}




// Lifecycle
onMounted(async () => {
  let curriculumKey = store.curriculum_key

  if (!curriculumKey && store.curriculumKeys.length) {
    curriculumKey = store.curriculumKeys[0]
    store.curriculum_key = curriculumKey
  }

  if (!curriculumKey) return

  try {
    if (!store.curriculumData) {
      const { data } = await axios.get(`/api/school/curriculum-structure/${curriculumKey}`)
      store.setCurriculumData(data)
    }

    curriculumData.value = store.curriculumData

const isValidStructure = store.structure &&
  typeof store.structure === 'object' &&
  !Array.isArray(store.structure) &&
  Object.keys(store.structure).length > 0

if (isValidStructure) {
  return
}


    for (const stage of Object.values(curriculumData.value.stages)) {
      for (const [levelKey] of Object.entries(stage.levels)) {
        structure.value[levelKey] = {
          selected: false,
          fees: [{ name: 'Tuition', price: '' }],
          requirements: [{ header: 'Uniform', items: ['Shoes', 'Socks'] }],
          streams: ['A', 'B'],
          terms: [{ name: 'Term 1', start_date: '', end_date: '' }]
        }
      }
    }

    await loadHolidays()
  } catch (err) {
    console.error('Failed to load curriculum structure:', err)
    store.showToast('Failed to load curriculum data', 'error')
  }
})
</script>

<style>
.vuecal__event.holiday-event {
  background-color: #fff3e0 !important;
  color: #e65100 !important;
  border-left: 3px solid #e65100;
  padding: 2px 4px;
  font-size: 0.8em;
  cursor: pointer;
  transition: all 0.2s;
}

.vuecal__event.holiday-event:hover {
  background-color: #ffe0b2 !important;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.vuecal--rounded-theme .vuecal__cell {
  border-radius: 8px;
  margin: 2px;
}

.vuecal--rounded-theme .vuecal__cell-content {
  border-radius: 6px;
}

.vuecal__cell--sat,
.vuecal__cell--sun {
  background-color: #f8f9fa !important;
}

.vuecal__cell--selected {
  background-color: #e3f2fd !important;
  color: #1976d2 !important;
  font-weight: bold;
}

.vuecal__cell--today {
  background-color: #fff8e1 !important;
}

.vuecal__cell--out-of-scope {
  opacity: 0.4;
}

/* Holiday Calendar Styles */
.holiday-calendar .vuecal__event {
  font-size: 0.7rem;
  padding: 2px 4px;
  margin: 1px 0;
  border-left: 3px solid;
  cursor: pointer;
  transition: all 0.2s;
}

.holiday-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
  overflow: hidden;
}

.holiday-icon {
  display: flex;
  align-items: center;
}

.holiday-title {
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Holiday Type Classes */
.vuecal__event.public-holiday {
  background-color: #ffebee !important;
  border-left-color: #f44336;
  color: #c62828 !important;
}

.vuecal__event.national-holiday {
  background-color: #e8f5e9 !important;
  border-left-color: #4caf50;
  color: #2e7d32 !important;
}

.vuecal__event.observance {
  background-color: #e3f2fd !important;
  border-left-color: #2196f3;
  color: #1565c0 !important;
}

.vuecal__event.optional-holiday {
  background-color: #f3e5f5 !important;
  border-left-color: #9c27b0;
  color: #6a1b9a !important;
  border-left-style: dashed;
}

.vuecal__event.season-event {
  background-color: #fff3e0 !important;
  border-left-color: #ff9800;
  color: #e65100 !important;
}

/* Modal Header Classes */
.modal-header.public-holiday {
  background-color: #f44336;
}

.modal-header.national-holiday {
  background-color: #4caf50;
}

.modal-header.observance {
  background-color: #2196f3;
}

.modal-header.optional-holiday {
  background-color: #9c27b0;
}

.modal-header.season-event {
  background-color: #ff9800;
}
</style>
