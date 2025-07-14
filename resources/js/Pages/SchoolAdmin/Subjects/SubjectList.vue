<template>
  <SchoolAdminLayout>
    <div class="max-w-5xl mx-auto p-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Subjects Management</h1>
      <p class="mb-4 text-gray-600 dark:text-gray-300">
        Manage subjects by level and stream. Select a level and stream to view, add, or manage subjects and their teachers/students.
      </p>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div v-if="loading">
          <p class="text-gray-500 dark:text-gray-400">Loading curriculum data...</p>
        </div>
        <div v-else-if="!curriculumKey">
          <div class="bg-yellow-100 dark:bg-yellow-900/40 border-l-4 border-yellow-500 dark:border-yellow-400 p-4 rounded mb-4">
            <p class="text-yellow-800 dark:text-yellow-200 font-semibold">No curriculum is set for this school. Please contact your administrator or complete the onboarding process to select a curriculum.</p>
          </div>
        </div>
        <div v-else-if="Object.keys(groupedByStage).length">
          <!-- LEVELS VIEW -->
          <template v-if="!selectedLevel && !selectedStream">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-300">Levels</h2>
              <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" @click="openAddLevelModal">Add Level</button>
            </div>
            <div class="flex flex-wrap gap-4">
              <template v-for="(stage, stageName) in groupedByStage" :key="stageName">
                <template v-if="hasSelectedLevels(stage)">
                  <template v-for="(level, levelKey) in stage.levels" :key="levelKey">
                    <template v-if="selectedLevelTitles.includes(levelKey)">
                      <div class="cursor-pointer bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-400 rounded-lg shadow hover:shadow-lg transition p-6 min-w-[160px] text-center flex flex-col items-center justify-center hover:bg-blue-50 dark:hover:bg-gray-800 group"
                        @click="selectedLevel = levelKey">
                        <span class="text-lg font-bold text-blue-700 dark:text-white group-hover:text-blue-900 dark:group-hover:text-blue-200">{{ levelKey }}</span>
                        <span class="text-xs text-gray-400 dark:text-gray-300 mt-1">{{ level.name }}</span>
                        <button class="mt-2 text-xs text-blue-500 underline hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-400" @click.stop="openLevelStructureModal(levelKey, getStructureForLevel(levelKey)?.structure)">Edit</button>
                      </div>
                    </template>
                  </template>
                </template>
              </template>
            </div>
          </template>

          <!-- STREAMS VIEW -->
          <template v-else-if="selectedLevel && !selectedStream">
            <div class="flex justify-between items-center mb-4">
              <button class="text-blue-600 hover:underline" @click="resetNavigation">&larr; Back to Levels</button>
              <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-300">Streams for {{ selectedLevel }}</h2>
              <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" @click="openAddStreamModal">Add Stream</button>
            </div>
            <div v-if="errorMessage" class="mb-4 text-red-600 dark:text-red-400 font-semibold">{{ errorMessage }}</div>
            <div class="flex flex-wrap gap-4">
              <div v-for="stream in getStreamsForLevel(selectedLevel)" :key="stream.name || stream" class="relative cursor-pointer bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-400 rounded-lg shadow hover:shadow-lg transition p-6 min-w-[220px] text-center flex flex-col items-center justify-center hover:bg-blue-50 dark:hover:bg-gray-800 group"
                @click="handleStreamClick(stream)">
                <span class="text-lg font-bold text-blue-700 dark:text-white group-hover:text-blue-900 dark:group-hover:text-blue-200">{{ stream.name || stream }}</span>
                <div class="mt-2">
                  <template v-if="stream.class_teacher_id">
                    <span class="text-xs text-gray-500 dark:text-gray-300">Class Teacher:</span>
                    <span class="font-semibold text-blue-600 dark:text-blue-300">{{ getTeacherName(stream.class_teacher_id) }}</span>
                    <div @click.stop>
                      <Dropdown>
                        <template #trigger>
                          <button class="ml-2 text-xs text-blue-500 underline hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-400">Change</button>
                        </template>
                        <template #content>
                          <div class="p-2 bg-white dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                            <select v-model="stream._pending_class_teacher_id" class="border rounded p-1 w-48 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600 mb-2">
                              <option :value="null">Select Teacher</option>
                              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id" :disabled="isTeacherAssigned(teacher.id) && stream.class_teacher_id !== teacher.id">{{ teacher.full_name }}</option>
                            </select>
                            <div class="flex gap-2">
                              <button @click.stop="saveClassTeacher(stream)" class="px-2 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded text-xs">Save</button>
                              <button @click.stop class="px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-100 rounded text-xs">Cancel</button>
                            </div>
                          </div>
                        </template>
                      </Dropdown>
                    </div>
                  </template>
                  <template v-else>
                    <span class="text-xs text-red-500 dark:text-red-400">No class teacher assigned</span>
                    <div @click.stop>
                      <Dropdown>
                        <template #trigger>
                          <button class="ml-2 text-xs text-blue-500 underline hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-400">Assign</button>
                        </template>
                        <template #content>
                          <div class="p-2 bg-white dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                            <select v-model="stream._pending_class_teacher_id" class="border rounded p-1 w-48 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600 mb-2">
                              <option :value="null">Select Teacher</option>
                              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id" :disabled="isTeacherAssigned(teacher.id) && stream.class_teacher_id !== teacher.id">{{ teacher.full_name }}</option>
                            </select>
                            <div class="flex gap-2">
                              <button @click.stop="saveClassTeacher(stream)" class="px-2 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded text-xs">Save</button>
                              <button @click.stop class="px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-100 rounded text-xs">Cancel</button>
                            </div>
                          </div>
                        </template>
                      </Dropdown>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </template>

          <!-- SUBJECTS VIEW -->
          <template v-else-if="selectedLevel && selectedStream">
            <div class="flex justify-between items-center mb-4">
              <button class="text-blue-600 hover:underline" @click="selectedStream = null">&larr; Back to Streams</button>
              <h2 class="text-xl font-semibold text-blue-700 dark:text-blue-300">Subjects for {{ selectedLevel }} - Stream {{ selectedStream }}</h2>
            </div>
            <div class="flex flex-wrap gap-4">
              <div v-for="subject in (groupedByStage && Object.values(groupedByStage).flatMap(stage => stage.levels[selectedLevel]?.courses || []))" :key="subject"
                class="relative bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-400 rounded-lg shadow hover:shadow-lg transition p-6 min-w-[220px] text-center flex flex-col items-center justify-center hover:bg-blue-50 dark:hover:bg-gray-800 group"
                @click="handleSubjectClick(subject, $event)">
                <span class="text-lg font-bold text-blue-700 dark:text-white group-hover:text-blue-900 dark:group-hover:text-blue-200 mb-2">{{ subject }}</span>
                <span v-if="isSubjectCombined(subject)" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200 text-xs font-semibold mb-2">
                  <i class="fas fa-link"></i> Combined
                  <button @click.stop="uncombineSubject(subject)" class="ml-2 text-xs text-red-500 dark:text-red-400 underline">Uncombine</button>
                </span>
                <span v-else-if="isSubjectExplicitlySeparate(subject)" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold mb-2">
                  <i class="fas fa-stream"></i> Separate
                </span>
                <div class="mt-2">
                  <template v-if="getSubjectTeacher(subject)">
                    <span class="text-xs text-gray-500 dark:text-gray-300">Teacher:</span>
                    <span class="font-semibold text-blue-600 dark:text-blue-300">{{ getTeacherName(getSubjectTeacher(subject)) }}</span>
                    <div @click.stop>
                      <Dropdown>
                        <template #trigger>
                          <button class="ml-2 text-xs text-blue-500 underline hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-400">Change</button>
                        </template>
                        <template #content>
                          <div class="p-2 bg-white dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                            <select v-model="pendingSubjectTeachers[subject]" class="border rounded p-1 w-48 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600 mb-2">
                              <option :value="null">Select Teacher</option>
                              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.full_name }}</option>
                            </select>
                            <div class="flex gap-2">
                              <button @click.stop="saveSubjectTeacher(subject)" class="px-2 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded text-xs">Save</button>
                              <button @click.stop class="px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-100 rounded text-xs">Cancel</button>
                            </div>
                          </div>
                        </template>
                      </Dropdown>
                    </div>
                  </template>
                  <template v-else>
                    <span class="text-xs text-red-500 dark:text-red-400">No teacher assigned</span>
                    <div @click.stop>
                      <Dropdown>
                        <template #trigger>
                          <button class="ml-2 text-xs text-blue-500 underline hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-400">Assign</button>
                        </template>
                        <template #content>
                          <div class="p-2 bg-white dark:bg-gray-900 dark:text-gray-100 rounded-lg">
                            <select v-model="pendingSubjectTeachers[subject]" class="border rounded p-1 w-48 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600 mb-2">
                              <option :value="null">Select Teacher</option>
                              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.full_name }}</option>
                            </select>
                            <div class="flex gap-2">
                              <button @click.stop="saveSubjectTeacher(subject)" class="px-2 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded text-xs">Save</button>
                              <button @click.stop class="px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-100 rounded text-xs">Cancel</button>
                            </div>
                          </div>
                        </template>
                      </Dropdown>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </template>
        </div>
        <div v-else>
          <p class="text-gray-500 dark:text-gray-400">No curriculum data found for this school.</p>
        </div>
      </div>
      <Modal :show="showAddLevelModal" @close="showAddLevelModal = false">
        <template #title>Add Level</template>
        <div class="mb-4 bg-white dark:bg-gray-900 dark:text-gray-100 rounded-lg max-w-lg w-full mx-auto p-8 border border-gray-200 dark:border-gray-700">
          <div class="mb-4">
            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-200">Select Level</label>
            <select v-model="newLevel" class="w-full border rounded p-2 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600">
              <option value="" disabled>Select a level</option>
              <option v-for="lvl in availableLevels" :key="lvl" :value="lvl">{{ lvl }}</option>
            </select>
            <div v-if="addLevelError" class="text-red-500 text-sm mt-2">{{ addLevelError }}</div>
          </div>
          <div class="flex justify-end gap-2 mt-4">
            <button class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600" @click="showAddLevelModal = false">Cancel</button>
            <button class="px-4 py-2 rounded bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-800" @click="submitAddLevel">Add</button>
          </div>
        </div>
      </Modal>
      <Modal :show="showAddStreamModal" @close="showAddStreamModal = false">
        <template #title>Add Stream to {{ selectedLevel }}</template>
        <div class="mb-4 bg-white dark:bg-gray-900 dark:text-gray-100 rounded-lg max-w-lg w-full mx-auto p-6 border border-gray-200 dark:border-gray-700">
          <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-200">Stream Name</label>
          <input v-model="newStream" class="w-full border rounded p-2 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" placeholder="e.g. A, B, C" />
          <div v-if="addStreamError" class="text-red-500 text-sm mt-2">{{ addStreamError }}</div>
          <div class="flex justify-end gap-2 mt-4">
            <button class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600" @click="showAddStreamModal = false">Cancel</button>
            <button class="px-4 py-2 rounded bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-800" @click="submitAddStream">Add</button>
          </div>
        </div>
      </Modal>
      <Modal :show="showLevelStructureModal" @close="showLevelStructureModal = false">
        <template #title>{{ editingLevelKey ? `Edit Structure for ${editingLevelKey}` : 'Add Level Structure' }}</template>
        <div class="mb-4 bg-white dark:bg-gray-900 dark:text-gray-100 rounded-lg max-w-4xl w-full mx-auto p-8 border border-gray-200 dark:border-gray-700">
          <!-- Step Navigation Bar -->
          <div class="border-b border-gray-100 dark:border-gray-800 mb-6 pb-2">
            <div class="flex px-6">
              <button
                v-for="step in [1,2,3,4]"
                :key="step"
                @click="levelStructureStep = step"
                class="px-4 py-3 text-sm font-medium relative focus:outline-none"
                :class="{
                  'text-blue-600 dark:text-blue-400': levelStructureStep === step,
                  'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200': levelStructureStep !== step
                }"
              >
                {{ ['Fees', 'Requirements', 'Streams', 'Calendar'][step-1] }}
                <span
                  v-if="levelStructureStep === step"
                  class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500 rounded-t"
                ></span>
              </button>
            </div>
          </div>
          <div class="flex justify-between mb-6">
            <button :disabled="levelStructureStep === 1" @click="prevLevelStructureStep" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600">Back</button>
            <span class="font-semibold">Step {{ levelStructureStep }} of 4</span>
            <button v-if="levelStructureStep < 4" :disabled="levelStructureStep === 4" @click="nextLevelStructureStep" class="px-3 py-2 rounded bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-800">Next</button>
            <span v-else class="w-16"></span>
          </div>
          <div v-if="levelStructureStep === 1">
            <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-100">Fee Structure</h4>
            <div v-for="(fee, idx) in levelStructure.fees" :key="idx" class="flex gap-2 mb-2">
              <input v-model="fee.name" placeholder="Fee Name" class="border rounded p-1 flex-1 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" />
              <input v-model="fee.price" type="number" placeholder="Amount" class="border rounded p-1 w-24 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" />
              <button @click="removeFee(idx)" class="text-red-500">&times;</button>
            </div>
            <button @click="addFee" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">+ Add Fee</button>
          </div>
          <div v-else-if="levelStructureStep === 2">
            <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-100">Requirements</h4>
            <div v-for="(group, gIdx) in levelStructure.requirements" :key="gIdx" class="mb-2 border rounded p-2 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700">
              <input v-model="group.header" placeholder="Group Name" class="border rounded p-1 mb-1 w-full bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" />
              <div v-for="(item, iIdx) in group.items" :key="iIdx" class="flex gap-2 mb-1">
                <input v-model="group.items[iIdx]" placeholder="Item" class="border rounded p-1 flex-1 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" />
                <button @click="removeRequirementItem(gIdx, iIdx)" class="text-red-500">&times;</button>
              </div>
              <button @click="addRequirementItem(gIdx)" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">+ Add Item</button>
              <button @click="removeRequirementGroup(gIdx)" class="text-red-500 text-xs ml-2">Remove Group</button>
            </div>
            <button @click="addRequirementGroup" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">+ Add Requirement Group</button>
          </div>
          <div v-else-if="levelStructureStep === 3">
            <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-100">Streams</h4>
            <div v-for="(stream, idx) in levelStructure.streams" :key="idx" class="flex gap-2 mb-2 items-center">
              <input v-model="stream.name" placeholder="Stream Name" class="border rounded p-1 flex-1 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" />
              <button @click="removeStream(idx)" class="text-red-500">&times;</button>
            </div>
            <button @click="addStream" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">+ Add Stream</button>
          </div>
          <div v-else-if="levelStructureStep === 4">
            <h4 class="font-bold mb-2 text-gray-800 dark:text-gray-100">Terms & Dates</h4>
            <div v-for="(term, idx) in levelStructure.terms" :key="idx" class="mb-2 border rounded p-2 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700">
              <input v-model="term.name" placeholder="Term Name" class="border rounded p-1 mb-1 w-full bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" />
              <div class="flex gap-2 mb-1">
                <div class="flex-1">
                  <label class="block text-xs text-gray-500 mb-1">Start Date</label>
                  <div class="relative">
                    <input :value="term.start_date" readonly class="border rounded p-1 w-full bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600 cursor-pointer" @click="openDatePicker(idx, 'start_date')" placeholder="Select start date" />
                  </div>
                </div>
                <div class="flex-1">
                  <label class="block text-xs text-gray-500 mb-1">End Date</label>
                  <div class="relative">
                    <input :value="term.end_date" readonly class="border rounded p-1 w-full bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600 cursor-pointer" @click="openDatePicker(idx, 'end_date')" placeholder="Select end date" />
                  </div>
                </div>
                <button @click="removeTerm(idx)" class="text-red-500">&times;</button>
              </div>
              <div v-for="(fee, fIdx) in term.fees" :key="fIdx" class="flex gap-2 mb-1">
                <input v-model="fee.name" placeholder="Fee Name" class="border rounded p-1 flex-1 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" />
                <input v-model="fee.price" type="number" placeholder="Amount" class="border rounded p-1 w-24 bg-white dark:bg-gray-800 dark:text-gray-100 border-gray-300 dark:border-gray-600" />
              </div>
            </div>
            <button @click="addTerm" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">+ Add Term</button>
            <Modal :show="editingDate.open" @close="editingDate.open = false">
              <template #title>Select Date</template>
              <div class="bg-white dark:bg-gray-900 rounded-lg p-2">
                <VueCal
                  style="height: 400px"
                  class="vuecal--rounded-theme holiday-calendar dark:bg-gray-900 dark:text-gray-100"
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
                </VueCal>
              </div>
            </Modal>
          </div>
          <div v-if="levelStructureError" class="text-red-500 text-sm mt-2">{{ levelStructureError }}</div>
        </div>
        <div class="flex justify-end gap-2 mt-4" v-if="levelStructureStep === 4">
          <button class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600" @click="showLevelStructureModal = false">Cancel</button>
          <button class="px-4 py-2 rounded bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-800" @click="submitLevelStructure">Save</button>
        </div>
      </Modal>
      <Modal :show="!!selectedHoliday" @close="selectedHoliday = null">
        <template #title>{{ selectedHoliday?.title }}</template>
        <div class="p-4 bg-white dark:bg-gray-900 rounded-lg">
          <div class="flex items-center text-gray-600 dark:text-gray-300 mb-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>{{ selectedHoliday?.start ? (new Date(selectedHoliday.start)).toLocaleDateString() : '' }}</span>
          </div>
          <div class="flex items-center text-gray-600 dark:text-gray-300 mb-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ selectedHoliday?.type }}</span>
          </div>
          <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
            <p class="text-gray-700 dark:text-gray-200">{{ selectedHoliday?.description }}</p>
          </div>
          <div class="flex justify-end pt-4">
            <button @click="selectedHoliday = null" class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600">Close</button>
          </div>
        </div>
      </Modal>
      <Modal :show="combineModal.show" @close="combineModal.show = false">
        <template #title>Combine Subject Classes?</template>
        <div class="p-4 bg-white dark:bg-gray-900 rounded-lg">
          <p class="mb-4 text-gray-700 dark:text-gray-200">
            The teacher <span class="font-semibold">{{ getTeacherName(combineModal.teacherId) }}</span> is already assigned to <span class="font-semibold">{{ combineModal.subject }}</span> in stream(s): <span class="font-semibold">{{ combineModal.otherStreams.join(', ') }}</span>.<br>
            Would you like to <span class="font-semibold">combine</span> these subject classes into a shared lesson (students from all streams attend together), or keep them separate?
          </p>
          <div class="flex gap-4 justify-end">
            <button class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600" @click="combineModal.show = false">Cancel</button>
            <button class="px-4 py-2 rounded bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-800" @click="confirmCombineSubject(true)">Combine</button>
            <button class="px-4 py-2 rounded bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-700" @click="confirmCombineSubject(false)">Keep Separate</button>
          </div>
        </div>
      </Modal>
    </div>
  </SchoolAdminLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import SchoolAdminLayout from '@/Layouts/SchoolAdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';
import { router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';

const loading = ref(true);
const groupedByStage = ref({});
const curriculumKey = ref(null);
const selectedStructures = ref([]);

// Drill-down navigation state
const selectedLevel = ref(null);
const selectedStream = ref(null);

// Modal state
const showAddLevelModal = ref(false);
const showAddStreamModal = ref(false);
const newLevel = ref('');
const newStream = ref('');
const addLevelError = ref('');
const addStreamError = ref('');

// Multi-step modal state for level structure
const showLevelStructureModal = ref(false);
const levelStructureStep = ref(1);
const editingLevelKey = ref('');
const levelStructure = ref({
  fees: [{ name: '', price: '' }],
  requirements: [{ header: '', items: [''] }],
  streams: [{ name: 'A', class_teacher_id: null }],
  terms: [{ name: 'Term 1', start_date: '', end_date: '', fees: [{ name: '', price: '' }] }],
});
const levelStructureError = ref('');

// Example: All possible levels (should come from backend or config in real app)
const allPossibleLevels = [
  'PP1', 'PP2', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
  'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
];

const availableLevels = computed(() => {
  return allPossibleLevels.filter(lvl => !selectedLevelTitles.value.includes(lvl));
});

const holidayEvents = ref([]);
const editingDate = ref({ termIndex: null, field: '', open: false });
const minDate = new Date(new Date().getFullYear(), 0, 1);
const maxDate = new Date(new Date().getFullYear() + 1, 11, 31);
const selectedDate = computed(() => {
  if (editingDate.value.termIndex === null) return null;
  const dateStr = levelStructure.value.terms[editingDate.value.termIndex][editingDate.value.field];
  return dateStr ? new Date(dateStr) : new Date();
});

const selectedHoliday = ref(null);

const teachers = ref([]);

const errorMessage = ref('');

const pendingSubjectTeachers = ref({});

const combineModal = ref({ show: false, subject: '', teacherId: null, otherStreams: [], pendingTeacherId: null });

// Track explicitly separate choices for subject-teacher pairs
const explicitlySeparateSubjects = ref({});

function showHolidayDetails(event) {
  selectedHoliday.value = event;
  if (event && event.event) event.event.stopPropagation();
}

async function fetchCurriculumStructure() {
  try {
    // Get the current school info (including curriculum_key)
    const schoolRes = await axios.get('/api/user/school');
    if (schoolRes.data.success) {
      curriculumKey.value = schoolRes.data.school.curriculum_key;
    } else {
      curriculumKey.value = schoolRes.data.curriculum_key; // fallback for old format
    }
    
    if (!curriculumKey.value) {
      loading.value = false;
      return;
    }
    // Fetch the curriculum structure for this school
    const { data } = await axios.get(`/api/school/curriculum-structure/${curriculumKey.value}`);
    groupedByStage.value = data.stages || {};

    // Fetch selected school structures (levels/streams)
    const structuresRes = await axios.get('/api/user/school-structures');
    selectedStructures.value = structuresRes.data;

    // Populate explicitlySeparateSubjects
    explicitlySeparateSubjects.value = {};
    for (const structure of selectedStructures.value) {
      if (structure.structure && Array.isArray(structure.structure.streams)) {
        for (const stream of structure.structure.streams) {
          if (stream.separate_subjects) {
            for (const [subject, isSeparate] of Object.entries(stream.separate_subjects)) {
              if (isSeparate && stream.subject_teachers && stream.subject_teachers[subject]) {
                const tid = stream.subject_teachers[subject];
                explicitlySeparateSubjects.value[`${subject}|${tid}|${structure.title}|${stream.name}`] = true;
              }
            }
          }
        }
      }
    }
  } catch (err) {
    console.error('Failed to load curriculum structure:', err);
    groupedByStage.value = {};
    selectedStructures.value = [];
  } finally {
    loading.value = false;
  }
}

const selectedLevelTitles = computed(() => selectedStructures.value.map(s => s.title));

// Helper to get streams for a level from selectedStructures
function getStreamsForLevel(levelKey) {
  const structure = selectedStructures.value.find(s => s.title === levelKey);
  if (!structure || !structure.structure || !Array.isArray(structure.structure.streams)) return [];
  // Normalize: always return array of objects
  return structure.structure.streams.map(stream =>
    typeof stream === 'string' ? { name: stream, class_teacher_id: null } : stream
  );
}

// Helper to get structure object for a level
function getStructureForLevel(levelKey) {
  return selectedStructures.value.find(s => s.title === levelKey);
}

// Helper to check if a stage has at least one selected level
function hasSelectedLevels(stage) {
  if (!stage || !stage.levels) return false;
  return Object.keys(stage.levels).some(levelKey => selectedLevelTitles.value.includes(levelKey));
}

function resetNavigation() {
  selectedLevel.value = null;
  selectedStream.value = null;
}

function openAddLevelModal() {
  newLevel.value = '';
  addLevelError.value = '';
  showAddLevelModal.value = true;
}

function openAddStreamModal() {
  newStream.value = '';
  addStreamError.value = '';
  showAddStreamModal.value = true;
}

async function submitAddLevel() {
  if (!newLevel.value) {
    addLevelError.value = 'Please select a level.';
    return;
  }
  showAddLevelModal.value = false;
  // Open structure modal for the new level
  openLevelStructureModal(newLevel.value);
}

async function submitAddStream() {
  if (!newStream.value) {
    addStreamError.value = 'Please enter a stream name.';
    return;
  }
  // TODO: Replace with real backend call
  try {
    // await axios.post('/api/school/structure/add-stream', { level: selectedLevel.value, stream: newStream.value });
    showAddStreamModal.value = false;
    await fetchCurriculumStructure();
  } catch (e) {
    addStreamError.value = 'Failed to add stream.';
  }
}

function openLevelStructureModal(levelKey = '', prefill = null) {
  editingLevelKey.value = levelKey;
  levelStructureStep.value = 1;
  levelStructureError.value = '';
  if (prefill) {
    // Backward compatibility: convert streams from string[] to object[] if needed
    const structureCopy = JSON.parse(JSON.stringify(prefill));
    if (Array.isArray(structureCopy.streams) && typeof structureCopy.streams[0] === 'string') {
      structureCopy.streams = structureCopy.streams.map(name => ({ name, class_teacher_id: null }));
    }
    levelStructure.value = structureCopy;
  } else {
    levelStructure.value = {
      fees: [{ name: '', price: '' }],
      requirements: [{ header: '', items: [''] }],
      streams: [{ name: 'A', class_teacher_id: null }],
      terms: [{ name: 'Term 1', start_date: '', end_date: '', fees: [{ name: '', price: '' }] }],
    };
  }
  showLevelStructureModal.value = true;
  fetchTeachersForModal();
}

async function fetchTeachersForModal() {
  try {
    const { data } = await axios.get('/api/school-admin/teachers');
    teachers.value = data;
  } catch (err) {
    teachers.value = [];
  }
}

function addFee() {
  levelStructure.value.fees.push({ name: '', price: '' });
}
function removeFee(idx) {
  levelStructure.value.fees.splice(idx, 1);
}
function addRequirementGroup() {
  levelStructure.value.requirements.push({ header: '', items: [''] });
}
function removeRequirementGroup(idx) {
  levelStructure.value.requirements.splice(idx, 1);
}
function addRequirementItem(groupIdx) {
  levelStructure.value.requirements[groupIdx].items.push('');
}
function removeRequirementItem(groupIdx, itemIdx) {
  levelStructure.value.requirements[groupIdx].items.splice(itemIdx, 1);
}
function addStream() {
  levelStructure.value.streams.push({ name: '', class_teacher_id: null });
}
function removeStream(idx) {
  levelStructure.value.streams.splice(idx, 1);
}
function addTerm() {
  levelStructure.value.terms.push({ name: '', start_date: '', end_date: '', fees: levelStructure.value.fees.map(f => ({ name: f.name, price: f.price })) });
}
function removeTerm(idx) {
  levelStructure.value.terms.splice(idx, 1);
}

function nextLevelStructureStep() {
  if (levelStructureStep.value < 4) levelStructureStep.value++;
}
function prevLevelStructureStep() {
  if (levelStructureStep.value > 1) levelStructureStep.value--;
}

async function loadHolidays() {
  try {
    const { data } = await axios.get('/api/holidays');
    holidayEvents.value = data.map(h => ({
      start: new Date(h.date.iso),
      end: new Date(h.date.iso),
      title: h.name,
      description: h.description || '',
      class: getHolidayClass(h),
      type: h.primary_type,
      country: h.country?.name,
      locations: h.locations,
    }));
  } catch (err) {
    console.error('Failed to load holidays:', err);
  }
}

function getHolidayClass(holiday) {
  const type = (holiday.primary_type || holiday.type || '').toLowerCase();
  switch (type) {
    case 'public holiday':
      return 'public-holiday';
    case 'national holiday':
      return 'national-holiday';
    case 'observance':
      return 'observance';
    case 'optional holiday':
      return 'optional-holiday';
    case 'season':
      return 'season-event';
    default:
      return 'holiday-event';
  }
}

function openDatePicker(termIdx, field) {
  editingDate.value.termIndex = termIdx;
  editingDate.value.field = field;
  editingDate.value.open = true;
}

function selectDate(day) {
  const dateValue = day.date || day;
  if (!dateValue) return;
  const dateStr = formatLocalDate(dateValue);
  const { termIndex, field } = editingDate.value;
  levelStructure.value.terms[termIndex][field] = dateStr;
  editingDate.value.termIndex = null;
  editingDate.value.field = '';
  editingDate.value.open = false;
}

function formatLocalDate(date) {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

async function submitLevelStructure() {
  try {
    if (editingLevelKey.value && getStructureForLevel(editingLevelKey.value) && getStructureForLevel(editingLevelKey.value).id) {
      // Update existing
      const structureObj = getStructureForLevel(editingLevelKey.value);
      await axios.post('/api/school/school-structure/update', {
        id: structureObj.id,
        structure: levelStructure.value
      });
    } else {
      // Create new
      await axios.post('/api/school/school-structure/create', {
        title: editingLevelKey.value,
        structure: levelStructure.value
      });
    }
    showLevelStructureModal.value = false;
    await fetchCurriculumStructure();
  } catch (e) {
    levelStructureError.value = 'Failed to save structure.';
  }
}

function syncFeesToTerms() {
  const feeTemplate = levelStructure.value.fees || [];
  levelStructure.value.terms = levelStructure.value.terms || [];
  levelStructure.value.terms.forEach((term) => {
    term.fees = term.fees || [];
    for (let i = 0; i < feeTemplate.length; i++) {
      const globalFee = feeTemplate[i];
      const existingFee = term.fees[i];
      if (existingFee) {
        // Always update the name
        existingFee.name = globalFee.name || `Fee ${i + 1}`;
        // Only update price if not customized (matches _originalPrice or is empty)
        if (
          typeof existingFee._originalPrice === 'undefined' ||
          existingFee.price === existingFee._originalPrice ||
          existingFee.price === '' ||
          existingFee.price === null
        ) {
          existingFee.price = globalFee.price ?? 0;
        }
        // Always update the _originalPrice to the latest global price
        existingFee._originalPrice = globalFee.price ?? 0;
      } else {
        // Add new fee
        term.fees[i] = {
          name: globalFee.name,
          price: globalFee.price ?? 0,
          _originalPrice: globalFee.price ?? 0,
        };
      }
    }
    // Remove extra fees if needed
    if (term.fees.length > feeTemplate.length) {
      term.fees = term.fees.slice(0, feeTemplate.length);
    }
  });
}

watch(
  () => levelStructure.value.fees.map(f => f.name),
  () => {
    if (levelStructureStep.value === 1) syncFeesToTerms();
  },
  { deep: true }
);

function goToSubject(subject) {
  if (!selectedLevel.value || !selectedStream.value || !subject) return;
  // Encode for URL safety
  const level = encodeURIComponent(selectedLevel.value);
  const stream = encodeURIComponent(selectedStream.value);
  const subj = encodeURIComponent(subject);
  router.visit(`/school-admin/subjects/${level}/${stream}/${subj}`);
}

function getTeacherName(id) {
  const t = teachers.value.find(t => t.id === id);
  return t ? t.full_name : 'Unknown';
}

async function saveClassTeacher(stream) {
  if (stream._pending_class_teacher_id !== undefined) {
    const teacher = teachers.value.find(t => t.id === stream._pending_class_teacher_id);
    const classTeacherId = teacher?.id || stream._pending_class_teacher_id;
    delete stream._pending_class_teacher_id;
    // Find the structure for the current level
    const structureObj = getStructureForLevel(selectedLevel.value);
    if (structureObj && structureObj.id) {
      // Normalize all streams to objects and update the correct one
      structureObj.structure.streams = (structureObj.structure.streams || []).map(s => {
        if (typeof s === 'string') {
          // If this is the stream being updated
          if (s === stream.name || s === stream) {
            return { name: s, class_teacher_id: classTeacherId };
          }
          return { name: s, class_teacher_id: null };
        } else if ((s.name || s) === (stream.name || stream)) {
          return { ...s, class_teacher_id: classTeacherId };
        }
        return s;
      });
      try {
        await axios.post('/api/school/school-structure/update', {
          id: structureObj.id,
          structure: structureObj.structure
        });
        await fetchCurriculumStructure();
      } catch (e) {
        // Optionally show error
      }
    }
  }
}

watch(selectedLevel, (newVal) => {
  if (newVal && !teachers.value.length) {
    fetchTeachersForModal();
  }
});

function handleStreamClick(stream) {
  if (!stream.class_teacher_id) {
    errorMessage.value = 'Please assign a class teacher before proceeding to subjects.';
    setTimeout(() => { errorMessage.value = ''; }, 3000);
    return;
  }
  selectedStream.value = stream.name || stream;
}

function isTeacherAssigned(teacherId) {
  // Returns true if the teacher is already assigned as class teacher to another stream in the current level
  const streams = getStreamsForLevel(selectedLevel.value);
  return streams.some(s => s.class_teacher_id === teacherId);
}

function getSubjectTeacher(subject) {
  // Find the selected stream object for the current level
  const structure = getStructureForLevel(selectedLevel.value);
  if (!structure || !structure.structure || !Array.isArray(structure.structure.streams)) return null;
  const streamObj = structure.structure.streams.find(s => (s.name || s) === selectedStream.value);
  if (!streamObj) return null;
  if (!streamObj.subject_teachers) streamObj.subject_teachers = {};
  return streamObj.subject_teachers[subject] || null;
}

async function saveSubjectTeacher(subject) {
  const teacherId = pendingSubjectTeachers.value[subject];
  const structure = getStructureForLevel(selectedLevel.value);
  if (!structure || !structure.structure || !Array.isArray(structure.structure.streams)) return;
  // Find the stream object
  const streamObj = structure.structure.streams.find(s => (s.name || s) === selectedStream.value);
  if (!streamObj) return;
  if (!streamObj.subject_teachers) streamObj.subject_teachers = {};
  // Check for duplicate assignment in other streams
  const otherStreams = structure.structure.streams
    .filter(s => (s.name || s) !== selectedStream.value && s.subject_teachers && s.subject_teachers[subject] === teacherId)
    .map(s => s.name);
  if (otherStreams.length > 0) {
    // Show combine modal
    combineModal.value = { show: true, subject, teacherId, otherStreams, pendingTeacherId: teacherId };
    return;
  }
  // Normal assignment
  streamObj.subject_teachers[subject] = teacherId;
  try {
    await axios.post('/api/school/school-structure/update', {
      id: structure.id,
      structure: structure.structure
    });
    await fetchCurriculumStructure();
  } catch (e) {
    // Optionally show error
  }
}

function isSubjectCombined(subject) {
  // Check if the subject is combined across streams in this level, but not explicitly separated
  const structure = getStructureForLevel(selectedLevel.value);
  if (!structure || !structure.structure || !Array.isArray(structure.structure.streams)) return false;
  const teacherStreams = {};
  for (const stream of structure.structure.streams) {
    if (stream.subject_teachers && stream.subject_teachers[subject]) {
      const tid = stream.subject_teachers[subject];
      if (!teacherStreams[tid]) teacherStreams[tid] = [];
      teacherStreams[tid].push(stream.name);
    }
  }
  // If any teacher has more than one stream for this subject, and none are explicitly separated, it's combined
  for (const [tid, streams] of Object.entries(teacherStreams)) {
    if (streams.length > 1) {
      // If ANY stream for this teacher/subject/level is explicitly separated, do not show combined
      let anySeparated = streams.some(streamName => explicitlySeparateSubjects.value[`${subject}|${tid}|${selectedLevel.value}|${streamName}`]);
      if (!anySeparated) return true;
    }
  }
  return false;
}

function isSubjectExplicitlySeparate(subject) {
  // Returns true if the subject is assigned to the same teacher in multiple streams but user chose 'Keep Separate' for this stream
  const structure = getStructureForLevel(selectedLevel.value);
  if (!structure || !structure.structure || !Array.isArray(structure.structure.streams)) return false;
  const tid = getSubjectTeacher(subject);
  if (!tid) return false;
  // Check if this stream is explicitly separated
  return !!explicitlySeparateSubjects.value[`${subject}|${tid}|${selectedLevel.value}|${selectedStream.value}`];
}

function handleSubjectClick(subject, event) {
  // Prevent navigation if the click originated from a button, select, or dropdown
  const tag = event.target.tagName.toLowerCase();
  if (["button", "select", "option", "svg", "path", "input", "label"].includes(tag) || event.target.closest(".dropdown") || event.target.closest(".dropdown-content")) {
    return;
  }
  // Only navigate if a teacher is assigned
  if (!getSubjectTeacher(subject)) {
    errorMessage.value = 'Please assign a teacher to this subject before proceeding.';
    setTimeout(() => { errorMessage.value = ''; }, 3000);
    return;
  }
  router.visit(`/school-admin/subjects/${selectedLevel.value}/${selectedStream.value}/${subject}`);
}

async function confirmCombineSubject(combine) {
  const { subject, teacherId, otherStreams } = combineModal.value;
  const structure = getStructureForLevel(selectedLevel.value);
  if (!structure || !structure.structure || !Array.isArray(structure.structure.streams)) return;
  // Find the current and other stream objects
  const allStreams = structure.structure.streams.filter(s => [selectedStream.value, ...otherStreams].includes(s.name || s));
  if (combine) {
    // Set the same teacher for the subject in all selected streams
    for (const stream of allStreams) {
      if (!stream.subject_teachers) stream.subject_teachers = {};
      stream.subject_teachers[subject] = teacherId;
      if (stream.separate_subjects) {
        delete stream.separate_subjects[subject];
      }
      // Remove from local state
      delete explicitlySeparateSubjects.value[`${subject}|${teacherId}|${selectedLevel.value}|${stream.name}`];
    }
  } else {
    // Only assign to the current stream
    const streamObj = allStreams.find(s => (s.name || s) === selectedStream.value);
    if (streamObj) {
      if (!streamObj.subject_teachers) streamObj.subject_teachers = {};
      streamObj.subject_teachers[subject] = teacherId;
      if (!streamObj.separate_subjects) streamObj.separate_subjects = {};
      streamObj.separate_subjects[subject] = true;
      // Set in local state
      explicitlySeparateSubjects.value[`${subject}|${teacherId}|${selectedLevel.value}|${selectedStream.value}`] = true;
    }
  }
  try {
    await axios.post('/api/school/school-structure/update', {
      id: structure.id,
      structure: structure.structure
    });
    combineModal.value.show = false;
    await fetchCurriculumStructure();
  } catch (e) {
    // Optionally show error
  }
}

function uncombineSubject(subject) {
  // Remove the combined flag by splitting the subject-teacher assignment per stream
  const structure = getStructureForLevel(selectedLevel.value);
  if (!structure || !structure.structure || !Array.isArray(structure.structure.streams)) return;
  // For all streams with this subject and teacher, set a unique teacher id (simulate split)
  for (const stream of structure.structure.streams) {
    if (stream.subject_teachers && stream.subject_teachers[subject]) {
      // Optionally, set to null or keep as is for manual reassignment
      // Here, we just remove the teacher assignment for demonstration
      stream.subject_teachers[subject] = null;
    }
  }
  axios.post('/api/school/school-structure/update', {
    id: structure.id,
    structure: structure.structure
  }).then(fetchCurriculumStructure);
}

onMounted(() => {
  fetchCurriculumStructure();
  loadHolidays();
});
</script>

<style>
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

/* Dark mode overrides */
.dark .vuecal--rounded-theme,
.dark .vuecal--rounded-theme .vuecal__cell-content,
.dark .vuecal--rounded-theme .vuecal__cell {
  background-color: #18181b !important;
  color: #f3f4f6 !important;
}
.dark .vuecal__cell--sat,
.dark .vuecal__cell--sun {
  background-color: #232336 !important;
}
.dark .vuecal__cell--selected {
  background-color: #1e293b !important;
  color: #38bdf8 !important;
}
.dark .vuecal__cell--today {
  background-color: #334155 !important;
}
.dark .vuecal__event.public-holiday {
  background-color: #7f1d1d !important;
  border-left-color: #f87171;
  color: #fee2e2 !important;
}
.dark .vuecal__event.national-holiday {
  background-color: #14532d !important;
  border-left-color: #4ade80;
  color: #bbf7d0 !important;
}
.dark .vuecal__event.observance {
  background-color: #1e293b !important;
  border-left-color: #38bdf8;
  color: #bae6fd !important;
}
.dark .vuecal__event.optional-holiday {
  background-color: #312e81 !important;
  border-left-color: #a78bfa;
  color: #ddd6fe !important;
  border-left-style: dashed;
}
.dark .vuecal__event.season-event {
  background-color: #78350f !important;
  border-left-color: #fbbf24;
  color: #fef3c7 !important;
}
</style> 