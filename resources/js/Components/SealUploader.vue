<template>
  <div class="seal-uploader">
    <!-- Upload Area -->
    <div class="upload-area">
      <div v-if="!sealUrl" class="upload-placeholder">
        <div class="upload-icon">
          <i class="fas fa-stamp text-4xl text-gray-400"></i>
        </div>
        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
          Upload School Seal/Stamp
        </h4>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 text-center max-w-md">
          Upload your school's official seal or stamp. For best results, use SVG format or PNG with transparent background.
        </p>
        
        <!-- Format Guidelines -->
        <div class="format-guidelines mb-4">
          <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <i class="fas fa-info-circle"></i>
            <span>Recommended: SVG format for best quality</span>
          </div>
        </div>
        
        <!-- Upload Buttons -->
        <div class="upload-buttons space-y-3">
          <button
            @click="$refs.fileInput.click()"
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <i class="fas fa-upload mr-2"></i>
            Upload Seal File
          </button>
          
          <div class="text-center text-sm text-gray-500 dark:text-gray-400">or</div>
          
          <button
            @click="showGenerator = true"
            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          >
            <i class="fas fa-magic mr-2"></i>
            Generate Seal
          </button>
        </div>
        
        <input
          ref="fileInput"
          type="file"
          accept=".svg,.png,.jpg,.jpeg"
          @change="handleFileUpload"
          class="hidden"
        />
      </div>
      
      <!-- Current Seal Display -->
      <div v-else class="current-seal">
        <div class="seal-preview">
          <img :src="sealUrl" alt="School Seal" class="w-32 h-32 object-contain border rounded-lg">
        </div>
        <div class="seal-actions mt-4 space-y-2">
          <button
            @click="showGenerator = true"
            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          >
            <i class="fas fa-magic mr-2"></i>
            Regenerate Seal
          </button>
          <button
            @click="deleteSeal"
            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            <i class="fas fa-trash mr-2"></i>
            Delete Seal
          </button>
        </div>
      </div>
    </div>

    <!-- Seal Generator Modal -->
    <Modal :show="showGenerator" @close="showGenerator = false" max-width="4xl">
      <div class="p-6">
        <div class="flex items-center mb-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex-1">
            Generate School Seal
          </h3>
        </div>

        <div class="flex flex-col md:flex-row gap-8">
          <div class="flex-1 flex flex-col items-center">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Preview</h4>
            <div class="preview-container bg-gray-50 dark:bg-gray-800 rounded-lg p-4 flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600" style="overflow: hidden; position: relative; width: 320px; height: 320px;">
              <div v-if="previewSvg" v-html="previewSvg" class="seal-svg w-full h-full flex-shrink-0"></div>
              <div v-else class="text-gray-400 text-center">
                <i class="fas fa-eye text-2xl mb-2"></i>
                <p class="text-sm">Preview will appear here</p>
              </div>
            </div>
            <!-- Crest Preview -->
            <div v-if="crestPreview" class="mt-3 flex flex-col items-center">
              <span class="text-xs text-gray-500 mb-1">Crest Preview:</span>
              <div class="w-16 h-16 border rounded bg-white flex items-center justify-center">
                <img v-if="crestFile && crestFile.type === 'image/png'" :src="crestPreview" alt="Crest" style="max-width:100%;max-height:100%;" />
                <div v-else v-html="crestPreview"></div>
              </div>
              <button @click="removeCrest" class="mt-2 text-xs text-red-500 hover:underline">Remove Crest</button>
            </div>
          </div>
          <div class="flex-1 space-y-4">
            <!-- Crest Controls -->
            <div class="mb-4 border-b pb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School Crest (SVG only)</label>
              <input type="file" accept=".svg" @change="handleCrestUpload" class="block w-full text-xs text-gray-500" />
              <div v-if="crestError" class="text-xs text-red-500 mt-1">{{ crestError }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-2">
                <i class="fas fa-info-circle"></i>
                Crest SVGs should have a transparent background and no solid black fills in the center. <span class="underline cursor-pointer" title="See example crest below">See example</span>
              </div>
              <div class="mt-2 flex items-center gap-2">
                <span class="text-xs text-gray-400">Example:</span>
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="border rounded bg-white dark:bg-gray-900">
                  <circle cx="16" cy="16" r="14" stroke="#222" stroke-width="2" fill="none"/>
                  <rect x="10" y="10" width="12" height="8" stroke="#222" stroke-width="1.5" fill="none"/>
                  <path d="M16 10 L16 18" stroke="#222" stroke-width="1"/>
                  <path d="M10 14 L22 14" stroke="#222" stroke-width="1"/>
                </svg>
              </div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Crest Size</label>
              <input type="range" min="30" max="100" v-model="crestSize" class="w-full" />
              <div class="text-xs text-gray-500 mt-1">{{ crestSize }} px</div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Crest Vertical Position</label>
              <input type="range" min="70" max="130" v-model="crestY" class="w-full" />
              <div class="text-xs text-gray-500 mt-1">{{ crestY }} px</div>
            </div>
            <!-- Add Seal Style Selector -->
            <div class="mb-4 border-b pb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Seal Style</label>
              <select v-model="sealData.style" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600">
                <option v-for="style in sealStyles" :key="style.value" :value="style.value">{{ style.label }}</option>
              </select>
            </div>
            <!-- Text Controls -->
            <div class="mb-4 border-b pb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School Name Font Size</label>
              <input type="range" min="8" max="36" v-model="schoolNameFontSize" class="w-full" />
              <div class="text-xs text-gray-500 mt-1">{{ schoolNameFontSize }} px</div>
              <!-- Remove School Name Vertical Position and Slogan Position for classic style -->
              <div v-if="sealData.style === 'classic'" class="mb-4 border-b pb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top Arc Radius (School Name)</label>
                <input type="range" min="70" max="90" v-model="schoolNameArcRadius" class="w-full" />
                <div class="text-xs text-gray-500">Radius: {{ schoolNameArcRadius }}</div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Bottom Arc Radius (Slogan)</label>
                <input type="range" min="70" max="90" v-model="sloganArcRadius" class="w-full" />
                <div class="text-xs text-gray-500">Radius: {{ sloganArcRadius }}</div>
              </div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">School Name Font Family</label>
              <select v-model="schoolNameFontFamily" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600">
                <option value="Arial, sans-serif">Arial</option>
                <option value="Georgia, serif">Georgia</option>
                <option value="Times New Roman, serif">Times New Roman</option>
                <option value="Verdana, sans-serif">Verdana</option>
                <option value="Tahoma, sans-serif">Tahoma</option>
              </select>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">School Name Color</label>
              <input type="color" v-model="schoolNameColor" class="w-12 h-8 rounded border border-gray-300 dark:border-gray-600" />
            </div>
            <!-- Slogan Controls -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slogan Font Size</label>
              <input type="range" min="8" max="28" v-model="sloganFontSize" class="w-full" />
              <div class="text-xs text-gray-500 mt-1">{{ sloganFontSize }} px</div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Slogan Font Family</label>
              <select v-model="sloganFontFamily" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600">
                <option value="Arial, sans-serif">Arial</option>
                <option value="Georgia, serif">Georgia</option>
                <option value="Times New Roman, serif">Times New Roman</option>
                <option value="Verdana, sans-serif">Verdana</option>
                <option value="Tahoma, sans-serif">Tahoma</option>
              </select>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Slogan Color</label>
              <input type="color" v-model="sloganColor" class="w-12 h-8 rounded border border-gray-300 dark:border-gray-600" />
            </div>
            <!-- Modern style text position and alignment controls -->
            <div v-if="sealData.style === 'modern'" class="mb-4 border-b pb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School Name Position</label>
              <div class="flex gap-2 items-center">
                <span class="text-xs">X</span>
                <input type="range" min="20" max="180" v-model="modernNameX" class="flex-1" />
                <span class="text-xs">Y</span>
                <input type="range" min="20" max="70" v-model="modernNameY" class="flex-1" />
                <select v-model="modernNameAlign" class="ml-2 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 text-xs">
                  <option value="start">Left</option>
                  <option value="middle">Center</option>
                  <option value="end">Right</option>
                </select>
              </div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Slogan Position</label>
              <div class="flex gap-2 items-center">
                <span class="text-xs">X</span>
                <input type="range" min="20" max="180" v-model="modernSloganX" class="flex-1" />
                <span class="text-xs">Y</span>
                <input type="range" min="130" max="195" v-model="modernSloganY" class="flex-1" />
                <select v-model="modernSloganAlign" class="ml-2 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 text-xs">
                  <option value="start">Left</option>
                  <option value="middle">Center</option>
                  <option value="end">Right</option>
                </select>
              </div>
            </div>
            <!-- Add arc height controls for academic style -->
            <div v-if="sealData.style === 'academic'" class="mb-4 border-b pb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top Arc Height</label>
              <input type="range" min="10" max="60" v-model="academicTopArcHeight" class="w-full" />
              <div class="text-xs text-gray-500">Q Control Y: {{ academicTopArcHeight }}</div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Bottom Arc Height</label>
              <input type="range" min="140" max="190" v-model="academicBottomArcHeight" class="w-full" />
              <div class="text-xs text-gray-500">Q Control Y: {{ academicBottomArcHeight }}</div>
            </div>
            <!-- Minimal style text position controls -->
            <div v-if="sealData.style === 'minimal'" class="mb-4 border-b pb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">School Name Position</label>
              <div class="flex gap-2 items-center">
                <span class="text-xs">X</span>
                <input type="range" min="0" max="200" v-model="minimalNameX" class="flex-1" />
                <span class="text-xs">Y</span>
                <input type="range" min="60" max="120" v-model="minimalNameY" class="flex-1" />
              </div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4 mb-2">Slogan Position</label>
              <div class="flex gap-2 items-center">
                <span class="text-xs">X</span>
                <input type="range" min="0" max="200" v-model="minimalSloganX" class="flex-1" />
                <span class="text-xs">Y</span>
                <input type="range" min="120" max="180" v-model="minimalSloganY" class="flex-1" />
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="showGenerator = false"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="generateSeal"
            :disabled="generating"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <i v-if="generating" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-magic mr-2"></i>
            {{ generating ? 'Generating...' : 'Generate Seal' }}
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import Modal from './Modal.vue'
import axios from 'axios'

const props = defineProps({
  sealUrl: {
    type: String,
    default: null
  },
  currentSealStyle: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['file-selected', 'remove'])

const fileInput = ref(null)
const showGenerator = ref(false)
const generating = ref(false)
const schoolData = ref(null)
const previewSvg = ref('')

const crestFile = ref(null)
const crestPreview = ref('')
const crestError = ref('')
const crestSize = ref(60)
const crestY = ref(100)

const sealData = ref({
  schoolName: '',
  motto: '',
  style: props.currentSealStyle || 'classic',
  primaryColor: '#1f2937'
})

const sealStyles = [
  {
    value: 'classic',
    label: 'Classic',
    description: 'Traditional circular design'
  },
  {
    value: 'modern',
    label: 'Modern',
    description: 'Clean square with rounded corners'
  },
  {
    value: 'academic',
    label: 'Academic',
    description: 'Shield-style design'
  },
  {
    value: 'minimal',
    label: 'Minimal',
    description: 'Simple and clean'
  }
]

const presetColors = [
  { value: '#1f2937', name: 'Dark Blue' },
  { value: '#4f46e5', name: 'Indigo' },
  { value: '#10b981', name: 'Green' },
  { value: '#f59e0b', name: 'Orange' },
  { value: '#ef4444', name: 'Red' },
  { value: '#3b82f6', name: 'Blue' },
  { value: '#8b5cf6', name: 'Purple' },
  { value: '#06b6d4', name: 'Teal' },
  { value: '#f97316', name: 'Orange' },
  { value: '#059669', name: 'Green' },
  { value: '#1d4ed8', name: 'Blue' },
  { value: '#7c3aed', name: 'Purple' },
  { value: '#0891b2', name: 'Teal' },
  { value: '#d97706', name: 'Orange' },
  { value: '#991b1b', name: 'Red' },
  { value: '#064e3b', name: 'Dark Green' },
  { value: '#111827', name: 'Dark Gray' },
  { value: '#f3f4f6', name: 'Light Gray' },
  { value: '#ffffff', name: 'White' },
  { value: '#000000', name: 'Black' },
]

const schoolNameFontSize = ref(20)
const schoolNameFontFamily = ref('Arial, sans-serif')
const schoolNameColor = ref('#1f2937')
const sloganFontSize = ref(14)
const sloganFontFamily = ref('Arial, sans-serif')
const sloganColor = ref('#10b981')
const sloganPosition = ref('inside')
const schoolNameY = ref(65)
const sloganArcRadius = ref(80)
const sloganArcStart = ref(200) // default: bottom arc
const sloganArcSweep = ref(140)
const schoolNameArcRadius = ref(80)

// Modern style text position and alignment controls
const modernNameX = ref(100)
const modernNameY = ref(40)
const modernNameAlign = ref('middle')
const modernSloganX = ref(100)
const modernSloganY = ref(180)
const modernSloganAlign = ref('middle')

// Add arc height controls for academic style
const academicTopArcHeight = ref(30) // default Y for Q control point (top arc)
const academicBottomArcHeight = ref(165) // default Y for Q control point (bottom arc)

// Minimal style text position controls
const minimalNameX = ref(100)
const minimalNameY = ref(95)
const minimalSloganX = ref(100)
const minimalSloganY = ref(115)

const isDarkMode = ref(false)

function detectDarkMode() {
  isDarkMode.value = document.documentElement.classList.contains('dark')
}

// Watch for changes in the current seal style prop
watch(() => props.currentSealStyle, (newStyle) => {
  if (newStyle && newStyle !== sealData.value.style) {
    sealData.value.style = newStyle
  }
})

const loadSchoolData = async () => {
  try {
    const response = await axios.get('/api/user/school')
    if (response.data.success) {
      schoolData.value = response.data.school
      
      // Pre-populate seal data with school information
      sealData.value.schoolName = schoolData.value.name || ''
      sealData.value.motto = schoolData.value.slogan || ''
      
      // Use school's brand color if available, otherwise use default
      if (schoolData.value.brand_color) {
        sealData.value.primaryColor = schoolData.value.brand_color
      }
    }
  } catch (error) {
    console.error('Failed to load school data:', error)
  }
}

const createCurvedText = (id, text, r, start, end, fontFamily, fontSize, color, weight, italic) => {
  const startPt = polarToCartesian(100, 100, r, start)
  const endPt = polarToCartesian(100, 100, r, end)
  return `
    <defs>
      <path id='${id}' d='M ${startPt.x},${startPt.y} A ${r},${r} 0 1,1 ${endPt.x},${endPt.y}' />
    </defs>
    <text font-family='${fontFamily}' font-size='${fontSize}' fill='${color}' ${weight ? `font-weight='${weight}'` : ''} ${italic ? `font-style='italic'` : ''}>
      <textPath href='#${id}' startOffset='50%' text-anchor='middle' dominant-baseline='middle'>${text}</textPath>
    </text>
  `
}

const createSealSVG = () => {
  const { schoolName, motto, style, primaryColor } = sealData.value
  // Clean text to avoid special character issues but preserve basic punctuation
  const cleanSchoolName = schoolName.replace(/[^ -\x7f\w\s\-&]/g, '').trim()
  const cleanMotto = motto ? motto.replace(/[^ -\x7f\w\s\-&]/g, '').trim() : ''

  // Crest SVG/PNG logic
  let crestMarkup = ''
  if (crestPreview.value) {
    if (crestFile.value && crestFile.value.type === 'image/svg+xml') {
      crestMarkup = `
        <svg x="${100 - crestSize.value / 2}" y="${crestY.value - crestSize.value / 2}" width="${crestSize.value}" height="${crestSize.value}" viewBox="0 0 200 200" preserveAspectRatio="xMidYMid meet" style="background: none;">
          ${crestPreview.value}
        </svg>
      `
    } else if (crestFile.value && crestFile.value.type === 'image/png') {
      crestMarkup = `<image x='${100 - crestSize.value / 2}' y='${crestY.value - crestSize.value / 2}' width='${crestSize.value}' height='${crestSize.value}' href='${crestPreview.value}' style='background: none;' preserveAspectRatio='xMidYMid meet' />`
    }
  }

  // Curved text logic for styles that support it
  let topArc = ''
  let bottomArc = ''
  if (cleanSchoolName && style !== 'minimal') {
    topArc = createCurvedText(
      'schoolname-arc',
      cleanSchoolName.toUpperCase(),
      schoolNameArcRadius.value,
      270, 90,
      schoolNameFontFamily.value,
      schoolNameFontSize.value,
      schoolNameColor.value,
      'bold',
      false
    )
  }
  if (cleanMotto && style !== 'minimal') {
    bottomArc = createCurvedText(
      'slogan-arc',
      cleanMotto,
      sloganArcRadius.value,
      90, 270,
      sloganFontFamily.value,
      sloganFontSize.value,
      sloganColor.value,
      '',
      true
    )
  }

  // Straight text for minimal style
  let minimalName = ''
  let minimalSlogan = ''
  if (style === 'minimal') {
    if (cleanSchoolName) {
      minimalName = `<text x="100" y="95" text-anchor="middle" font-family="${schoolNameFontFamily.value}" font-size="${schoolNameFontSize.value}" font-weight="bold" fill="${schoolNameColor.value}">${cleanSchoolName}</text>`
    }
    if (cleanMotto) {
      minimalSlogan = `<text x="100" y="115" text-anchor="middle" font-family="${sloganFontFamily.value}" font-size="${sloganFontSize.value}" fill="${sloganColor.value}" font-style="italic">${cleanMotto}</text>`
    }
  }

  if (style === 'classic') {
    return `<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="sealGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:${primaryColor};stop-opacity:1" />
      <stop offset="100%" style="stop-color:${primaryColor};stop-opacity:0.7" />
    </linearGradient>
  </defs>
  <!-- Outer circle -->
  <circle cx="100" cy="100" r="95" fill="none" stroke="url(#sealGradient)" stroke-width="3"/>
  <!-- Inner circle (smaller for bigger open space) -->
  <circle cx="100" cy="100" r="65" fill="none" stroke="${primaryColor}" stroke-width="1"/>
  <!-- Crest -->
  ${crestMarkup}
  <!-- School name on top arc -->
  ${topArc}
  <!-- Slogan on bottom arc -->
  ${bottomArc}
</svg>`
  } else if (style === 'modern') {
    return `<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="sealGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:${primaryColor};stop-opacity:1" />
      <stop offset="100%" style="stop-color:${primaryColor};stop-opacity:0.7" />
    </linearGradient>
  </defs>
  <!-- Square with rounded corners -->
  <rect x="10" y="10" width="180" height="180" rx="20" fill="none" stroke="url(#sealGradient)" stroke-width="3"/>
  <!-- Inner square -->
  <rect x="20" y="20" width="160" height="160" rx="15" fill="none" stroke="${primaryColor}" stroke-width="1"/>
  <!-- Crest -->
  ${crestMarkup}
  <!-- School name at top -->
  ${cleanSchoolName ? `<text x="${modernNameX.value}" y="${modernNameY.value}" text-anchor="${modernNameAlign.value}" font-family="${schoolNameFontFamily.value}" font-size="${schoolNameFontSize.value}" font-weight="bold" fill="${schoolNameColor.value}">${cleanSchoolName.toUpperCase()}</text>` : ''}
  <!-- Slogan at bottom -->
  ${cleanMotto ? `<text x="${modernSloganX.value}" y="${modernSloganY.value}" text-anchor="${modernSloganAlign.value}" font-family="${sloganFontFamily.value}" font-size="${sloganFontSize.value}" fill="${sloganColor.value}" font-style="italic">${cleanMotto}</text>` : ''}
</svg>`
  } else if (style === 'academic') {
    // Use adjustable arc heights for text
    const topArcPath = `M 60 55 Q 100 ${academicTopArcHeight.value} 140 55`;
    const bottomArcPath = `M 55 120 Q 100 ${academicBottomArcHeight.value} 145 120`;

    // Top arc text (school name)
    let academicTopArc = '';
    if (cleanSchoolName) {
      academicTopArc = `
        <defs>
          <path id='academic-top-arc' d='${topArcPath}' />
        </defs>
        <text font-family='${schoolNameFontFamily.value}' font-size='${schoolNameFontSize.value}' fill='${schoolNameColor.value}' font-weight='bold'>
          <textPath href='#academic-top-arc' startOffset='50%' text-anchor='middle' dominant-baseline='middle'>${cleanSchoolName.toUpperCase()}</textPath>
        </text>
      `;
    }
    // Bottom arc text (slogan)
    let academicBottomArc = '';
    if (cleanMotto) {
      academicBottomArc = `
        <defs>
          <path id='academic-bottom-arc' d='${bottomArcPath}' />
        </defs>
        <text font-family='${sloganFontFamily.value}' font-size='${sloganFontSize.value}' fill='${sloganColor.value}' font-style='italic'>
          <textPath href='#academic-bottom-arc' startOffset='50%' text-anchor='middle' dominant-baseline='middle'>${cleanMotto}</textPath>
        </text>
      `;
    }
    return `<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="sealGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:${primaryColor};stop-opacity:1" />
      <stop offset="100%" style="stop-color:${primaryColor};stop-opacity:0.7" />
    </linearGradient>
  </defs>
  <!-- Shield shape -->
  <path d="M100 20 L160 50 L160 120 C160 150 130 170 100 180 C70 170 40 150 40 120 L40 50 Z" fill="none" stroke="url(#sealGradient)" stroke-width="3"/>
  <!-- Inner shield -->
  <path d="M100 25 L150 50 L150 120 C150 145 125 160 100 170 C75 160 50 145 50 120 L50 50 Z" fill="none" stroke="${primaryColor}" stroke-width="1"/>
  <!-- Crest -->
  ${crestMarkup}
  <!-- School name on top arc -->
  ${academicTopArc}
  <!-- Slogan on bottom arc -->
  ${academicBottomArc}
</svg>`
  } else { // minimal
    if (cleanSchoolName) {
      minimalName = `<text x="${minimalNameX.value}" y="${minimalNameY.value}" text-anchor="middle" font-family="${schoolNameFontFamily.value}" font-size="${schoolNameFontSize.value}" font-weight="bold" fill="${schoolNameColor.value}">${cleanSchoolName}</text>`
    }
    if (cleanMotto) {
      minimalSlogan = `<text x="${minimalSloganX.value}" y="${minimalSloganY.value}" text-anchor="middle" font-family="${sloganFontFamily.value}" font-size="${sloganFontSize.value}" fill="${sloganColor.value}" font-style="italic">${cleanMotto}</text>`
    }
    return `<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
  <!-- Simple circle -->
  <circle cx="100" cy="100" r="90" fill="none" stroke="${primaryColor}" stroke-width="2"/>
  <!-- Crest -->
  ${crestMarkup}
  <!-- School name -->
  ${minimalName}
  <!-- Motto -->
  ${minimalSlogan}
</svg>`
  }
}

const generateSeal = async () => {
  if (!sealData.value.schoolName.trim()) {
    alert('Please ensure school name is available')
    return
  }

  generating.value = true
  
  try {
    const svgContent = createSealSVG()
    const blob = new Blob([svgContent], { type: 'image/svg+xml' })
    const file = new File([blob], `${sealData.value.schoolName.replace(/\s+/g, '_')}_seal.svg`, { type: 'image/svg+xml' })
    
    // Add the seal style to the file object so it can be accessed by the parent component
    file.sealStyle = sealData.value.style
    
    console.log('Generated seal with style:', sealData.value.style)
    
    emit('file-selected', file)
    showGenerator.value = false
  } catch (error) {
    console.error('Failed to generate seal:', error)
    alert('Failed to generate seal. Please try again.')
  } finally {
    generating.value = false
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  emit('file-selected', file)
}

const deleteSeal = () => {
  if (confirm('Are you sure you want to delete the current seal?')) {
    emit('remove')
  }
}

const updateColorFromPicker = (event) => {
  sealData.value.primaryColor = event.target.value
}

const validateColorInput = (event) => {
  const color = event.target.value
  if (color.startsWith('#')) {
    const hex = color.substring(1)
    if (hex.length === 3) {
      sealData.value.primaryColor = `#${hex[0]}${hex[0]}${hex[1]}${hex[1]}${hex[2]}${hex[2]}`
    } else if (hex.length === 6) {
      sealData.value.primaryColor = `#${hex}`
    }
  }
}

const resetToSchoolColor = () => {
  if (schoolData.value && schoolData.value.brand_color) {
    sealData.value.primaryColor = schoolData.value.brand_color
  } else {
    sealData.value.primaryColor = '#1f2937' // Default
  }
}

function handleCrestUpload(event) {
  const file = event.target.files[0]
  if (!file) return
  if (file.type !== 'image/svg+xml') {
    crestError.value = 'Only SVG files are allowed for the crest.'
    return
  }
  crestError.value = ''
  crestFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    // Parse SVG and check for white or black background
    let svgText = e.target.result
    // Remove <metadata>...</metadata> tags
    svgText = svgText.replace(/<metadata[\s\S]*?<\/metadata>/gi, '');
    // Check for white fill in rect, circle, or path
    const whiteFillRegex = /<(rect|circle|path)[^>]*fill=["']?(#fff(fff)?|rgb\(255, ?255, ?255\))["']?/i
    if (whiteFillRegex.test(svgText)) {
      crestError.value = 'Please upload a vector SVG crest with a transparent background.'
      crestFile.value = null
      crestPreview.value = ''
      return
    }
    // Check for black fill in rect, circle, or path
    const blackFillRegex = /<(rect|circle|ellipse|path)[^>]*fill=["']?(#000(000)?|black|rgb\(0, ?0, ?0\))["']?/i
    if (blackFillRegex.test(svgText)) {
      crestError.value = 'Your crest SVG contains black-filled shapes. Please ensure the center is transparent.'
      crestFile.value = null
      crestPreview.value = ''
      return
    }
    // Extract the full <svg>...</svg> markup for preview
    const match = svgText.match(/<svg[\s\S]*?<\/svg>/i)
    crestPreview.value = match ? match[0] : svgText
  }
  reader.readAsText(file)
}

function removeCrest() {
  crestFile.value = null
  crestPreview.value = ''
  crestError.value = ''
}

function polarToCartesian(cx, cy, r, angleDeg) {
  const angleRad = (angleDeg - 90) * Math.PI / 180.0;
  return {
    x: cx + r * Math.cos(angleRad),
    y: cy + r * Math.sin(angleRad)
  };
}

// Watch for changes in seal data to update preview
watch(sealData, () => {
  if (sealData.value.schoolName && sealData.value.style) {
    previewSvg.value = createSealSVG()
  }
}, { deep: true })

// Explicit watcher for seal style
watch(() => sealData.value.style, (newStyle) => {
  console.log('Seal style changed to:', newStyle);
  previewSvg.value = createSealSVG();
});

// Watch for crest changes to update preview
watch([crestFile, crestPreview], () => {
  previewSvg.value = createSealSVG()
})

// Watch for crestSize changes to update preview
watch(crestSize, () => {
  previewSvg.value = createSealSVG()
})

// Watch for crestY changes to update preview
watch(crestY, () => {
  previewSvg.value = createSealSVG()
})

// Watch for school data changes to update seal data
watch(schoolData, (newSchoolData) => {
  if (newSchoolData) {
    sealData.value.schoolName = newSchoolData.name || ''
    sealData.value.motto = newSchoolData.slogan || ''
    if (newSchoolData.brand_color) {
      sealData.value.primaryColor = newSchoolData.brand_color
    }
  }
}, { deep: true })

// Watch for font and color controls to update preview
watch([schoolNameFontSize, schoolNameFontFamily, schoolNameColor, sloganFontSize, sloganFontFamily, sloganColor], () => {
  previewSvg.value = createSealSVG()
})

// Watch for arc controls
watch([schoolNameArcRadius, sloganArcRadius], () => {
  previewSvg.value = createSealSVG()
})

// Watch for modern style text position and alignment controls
watch([modernNameX, modernNameY, modernNameAlign, modernSloganX, modernSloganY, modernSloganAlign], () => {
  previewSvg.value = createSealSVG()
})

// Watch for arc height controls for academic style
watch([academicTopArcHeight, academicBottomArcHeight], () => {
  previewSvg.value = createSealSVG()
})

// Watch for minimal style text position controls to update preview
watch([minimalNameX, minimalNameY, minimalSloganX, minimalSloganY], () => {
  previewSvg.value = createSealSVG()
})

onMounted(() => {
  detectDarkMode()
  loadSchoolData()
})

// Watch for theme changes
if (window && window.matchMedia) {
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', detectDarkMode)
}

// Set default text color based on theme
watch(isDarkMode, (dark) => {
  if (dark) {
    if (schoolNameColor.value === '#1f2937') schoolNameColor.value = '#fff'
    if (sloganColor.value === '#10b981') sloganColor.value = '#f3f4f6'
  } else {
    if (schoolNameColor.value === '#fff') schoolNameColor.value = '#1f2937'
    if (sloganColor.value === '#f3f4f6') sloganColor.value = '#10b981'
  }
})
</script>

<style scoped>
.seal-uploader {
  @apply w-full;
}

.upload-area {
  @apply border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center;
}

.upload-placeholder {
  @apply space-y-4;
}

.upload-icon {
  @apply flex justify-center;
}

.upload-buttons {
  @apply flex flex-col space-y-3;
}

.current-seal {
  @apply text-center;
}

.seal-preview {
  @apply flex justify-center;
}

.seal-actions {
  @apply flex flex-col space-y-2;
}

.preview-container {
  @apply min-h-[160px] flex items-center justify-center relative;
}

.preview-container svg {
  @apply max-w-full max-h-full;
}

.settings-section {
  @apply space-y-4;
}

/* Color picker styling */
input[type="color"] {
  @apply cursor-pointer;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  @apply p-0;
}

input[type="color"]::-webkit-color-swatch {
  @apply border-0 rounded;
}

/* Preset color buttons */
.preset-color-btn {
  @apply transition-all duration-200;
}

.preset-color-btn:hover {
  @apply transform scale-110;
}

.preset-color-btn.active {
  @apply transform scale-110 ring-2 ring-offset-2 ring-blue-500;
}

.seal-svg {
  width: 128px;
  height: 128px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.seal-svg svg {
  width: 100% !important;
  height: 100% !important;
  max-width: 100%;
  max-height: 100%;
  display: block;
}
</style> 