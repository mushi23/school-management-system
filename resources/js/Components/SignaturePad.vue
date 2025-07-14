<template>
  <div class="signature-pad-container">
    <div class="signature-pad-header">
      <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Draw Your Signature</h4>
      <div class="flex items-center gap-2">
        <button
          @click="clearCanvas"
          class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
        >
          <i class="fas fa-eraser mr-1"></i>
          Clear
        </button>
        <button
          @click="undo"
          :disabled="!canUndo"
          class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <i class="fas fa-undo mr-1"></i>
          Undo
        </button>
      </div>
    </div>
    
    <div class="signature-pad-wrapper">
      <canvas
        ref="canvas"
        :width="canvasWidth"
        :height="canvasHeight"
        class="signature-canvas border border-gray-300 dark:border-gray-600 rounded cursor-crosshair"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
      ></canvas>
    </div>
    
    <div class="signature-pad-controls">
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
          <label class="text-xs text-gray-700 dark:text-gray-300">Pen Color:</label>
          <input
            v-model="penColor"
            type="color"
            class="w-8 h-6 border border-gray-300 dark:border-gray-600 rounded"
          />
        </div>
        <div class="flex items-center gap-2">
          <label class="text-xs text-gray-700 dark:text-gray-300">Pen Width:</label>
          <input
            v-model.number="penWidth"
            type="range"
            min="1"
            max="10"
            step="0.5"
            class="w-20"
          />
          <span class="text-xs text-gray-500">{{ penWidth }}px</span>
        </div>
      </div>
      
      <div class="flex items-center gap-2">
        <button
          @click="saveSignature"
          :disabled="!hasSignature"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        >
          <i class="fas fa-save mr-1"></i>
          Save Signature
        </button>
        <button
          @click="$emit('cancel')"
          class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const props = defineProps({
  width: {
    type: Number,
    default: 400
  },
  height: {
    type: Number,
    default: 200
  }
})

const emit = defineEmits(['save', 'cancel'])

const canvas = ref(null)
const canvasWidth = ref(props.width)
const canvasHeight = ref(props.height)
const penColor = ref('#000000')
const penWidth = ref(3)
const isDrawing = ref(false)
const hasSignature = ref(false)
const canUndo = ref(false)

// Drawing state
let ctx = null
let lastX = 0
let lastY = 0
let drawingHistory = []
let currentPath = []

onMounted(() => {
  initCanvas()
})

const initCanvas = () => {
  if (!canvas.value) return
  
  ctx = canvas.value.getContext('2d')
  ctx.fillStyle = '#ffffff'
  ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value)
  
  // Set initial drawing styles
  ctx.strokeStyle = penColor.value
  ctx.lineWidth = penWidth.value
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
}

const startDrawing = (e) => {
  isDrawing.value = true
  const rect = canvas.value.getBoundingClientRect()
  lastX = e.clientX - rect.left
  lastY = e.clientY - rect.top
  
  // Start new path
  currentPath = [{ x: lastX, y: lastY }]
  ctx.beginPath()
  ctx.moveTo(lastX, lastY)
}

const draw = (e) => {
  if (!isDrawing.value) return
  
  const rect = canvas.value.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  
  // Update drawing styles
  ctx.strokeStyle = penColor.value
  ctx.lineWidth = penWidth.value
  
  ctx.lineTo(x, y)
  ctx.stroke()
  
  currentPath.push({ x, y })
  lastX = x
  lastY = y
}

const stopDrawing = () => {
  if (isDrawing.value) {
    isDrawing.value = false
    if (currentPath.length > 1) {
      drawingHistory.push([...currentPath])
      canUndo.value = true
      hasSignature.value = true
    }
  }
}

// Touch event handlers
const handleTouchStart = (e) => {
  e.preventDefault()
  const touch = e.touches[0]
  const mouseEvent = new MouseEvent('mousedown', {
    clientX: touch.clientX,
    clientY: touch.clientY
  })
  startDrawing(mouseEvent)
}

const handleTouchMove = (e) => {
  e.preventDefault()
  const touch = e.touches[0]
  const mouseEvent = new MouseEvent('mousemove', {
    clientX: touch.clientX,
    clientY: touch.clientY
  })
  draw(mouseEvent)
}

const handleTouchEnd = (e) => {
  e.preventDefault()
  stopDrawing()
}

const clearCanvas = () => {
  if (!ctx) return
  
  ctx.fillStyle = '#ffffff'
  ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value)
  
  drawingHistory = []
  currentPath = []
  hasSignature.value = false
  canUndo.value = false
}

const undo = () => {
  if (drawingHistory.length === 0) return
  
  // Redraw everything except the last path
  ctx.fillStyle = '#ffffff'
  ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value)
  
  drawingHistory.pop()
  
  // Redraw remaining paths
  drawingHistory.forEach(path => {
    if (path.length > 1) {
      ctx.beginPath()
      ctx.moveTo(path[0].x, path[0].y)
      path.forEach(point => {
        ctx.lineTo(point.x, point.y)
      })
      ctx.stroke()
    }
  })
  
  canUndo.value = drawingHistory.length > 0
  hasSignature.value = drawingHistory.length > 0
}

const saveSignature = () => {
  if (!hasSignature.value) return
  
  // Convert canvas to blob
  canvas.value.toBlob((blob) => {
    const file = new File([blob], 'signature.png', { type: 'image/png' })
    emit('save', file)
  }, 'image/png')
}
</script>

<style scoped>
.signature-pad-container {
  @apply bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600;
}

.signature-pad-header {
  @apply flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-600;
}

.signature-pad-wrapper {
  @apply p-4;
}

.signature-canvas {
  @apply bg-white;
  touch-action: none; /* Prevent scrolling on touch devices */
}

.signature-pad-controls {
  @apply flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-600;
}

/* Prevent text selection during drawing */
.signature-canvas {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
</style> 