<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  src: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['close'])

const close = () => emit('close')

// ================= ZOOM =================
const scale = ref(1)
const MIN_ZOOM = 1
const MAX_ZOOM = 4
const STEP = 0.25

// ================= PAN =================
const posX = ref(0)
const posY = ref(0)

const isDragging = ref(false)
let startX = 0
let startY = 0

const zoomIn = () => {
  if (scale.value < MAX_ZOOM) scale.value += STEP
}

const zoomOut = () => {
  if (scale.value > MIN_ZOOM) scale.value -= STEP
}

const resetZoom = () => {
  scale.value = 1
  posX.value = 0
  posY.value = 0
}

// ================= WHEEL ZOOM =================
const handleWheel = (e) => {
  if (!props.show) return
  e.preventDefault()

  if (e.deltaY < 0) zoomIn()
  else zoomOut()
}

// ================= DRAG =================
const startDrag = (e) => {
  if (scale.value <= 1) return
  isDragging.value = true
  startX = e.clientX - posX.value
  startY = e.clientY - posY.value
}

const onDrag = (e) => {
  if (!isDragging.value) return
  posX.value = e.clientX - startX
  posY.value = e.clientY - startY
}

const stopDrag = () => {
  isDragging.value = false
}

// ================= ESC CLOSE =================
const handleKey = (e) => {
  if (e.key === 'Escape') close()
}

// ================= LIFECYCLE =================
onMounted(() => {
  window.addEventListener('keydown', handleKey)
  window.addEventListener('wheel', handleWheel, { passive: false })
  window.addEventListener('mousemove', onDrag)
  window.addEventListener('mouseup', stopDrag)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKey)
  window.removeEventListener('wheel', handleWheel)
  window.removeEventListener('mousemove', onDrag)
  window.removeEventListener('mouseup', stopDrag)
})

// Lock background scroll
watch(
  () => props.show,
  (val) => {
    const html = document.documentElement

    if (val) {
      html.style.overflow = 'hidden'
      resetZoom()
    } else {
      html.style.overflow = ''
    }
  }
)
</script>

<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm
             flex items-center justify-center p-6 overflow-hidden"
      @click.self="close"
    >
      <div class="relative max-w-6xl w-full flex justify-center">

        <!-- IMAGE WRAPPER -->
        <div
          class="cursor-grab active:cursor-grabbing"
          @mousedown="startDrag"
        >
          <img
            :src="src"
            :style="{
              transform: `translate(${posX}px, ${posY}px) scale(${scale})`
            }"
            class="max-h-[85vh] object-contain rounded-2xl shadow-2xl
                   transition-transform duration-100 ease-out select-none"
            draggable="false"
          />
        </div>

        <!-- CONTROLS -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2
                    flex gap-3 bg-black/60 backdrop-blur-md
                    px-4 py-2 rounded-full text-white text-sm">

          <button @click.stop="zoomOut">−</button>
          <button @click.stop="resetZoom">Reset</button>
          <button @click.stop="zoomIn">＋</button>

        </div>

        <!-- CLOSE -->
        <button
          class="absolute -top-4 -right-4 bg-white text-black
                 rounded-full w-9 h-9 flex items-center justify-center
                 shadow-lg hover:scale-110 transition"
          @click="close"
        >
          ✕
        </button>

      </div>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
