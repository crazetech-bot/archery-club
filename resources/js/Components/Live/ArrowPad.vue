<template>
  <div class="select-none">
    <!-- Arrow slots — shows entered arrows for the current end -->
    <div class="mb-5 flex items-center justify-center gap-2">
      <div
        v-for="n in arrowsPerEnd"
        :key="n"
        :class="[
          'flex h-12 w-12 items-center justify-center rounded-full text-sm font-bold transition-all',
          arrowSlotClass(n),
        ]"
      >
        {{ arrows[n - 1] ?? '' }}
      </div>
    </div>

    <!-- Score buttons -->
    <div class="grid grid-cols-4 gap-2">
      <button
        v-for="value in scoreValues"
        :key="value"
        :disabled="isFull || disabled"
        :class="[
          'rounded-xl py-4 text-base font-bold transition-all active:scale-95',
          scoreButtonClass(value),
          isFull || disabled ? 'cursor-not-allowed opacity-40' : 'cursor-pointer',
        ]"
        @click="handleScore(value)"
      >
        {{ value }}
      </button>
    </div>

    <!-- Action row -->
    <div class="mt-4 flex gap-2">
      <!-- Undo last arrow -->
      <button
        :disabled="arrows.length === 0 || disabled"
        class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-gray-200 py-3 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
        @click="undo"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
        </svg>
        Undo
      </button>

      <!-- Submit end -->
      <button
        :disabled="!isFull || disabled"
        class="flex flex-[2] items-center justify-center gap-1.5 rounded-xl bg-gray-900 py-3 text-sm font-semibold text-white transition-all hover:bg-gray-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
        @click="submitEnd"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 13l4 4L19 7" />
        </svg>
        Submit End
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  arrowsPerEnd: {
    type: Number,
    default: 6,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit-end'])

// Score values shown on the pad — ordered for ergonomics
const scoreValues = ['X', '10', '9', '8', '7', '6', '5', '4', '3', '2', '1', 'M']

// Current end arrows entered so far
const arrows = ref([])

const isFull = computed(() => arrows.value.length >= props.arrowsPerEnd)

function handleScore(value) {
  if (isFull.value || props.disabled) return
  arrows.value.push(value)
}

function undo() {
  arrows.value.pop()
}

function submitEnd() {
  if (!isFull.value) return

  emit('submit-end', [...arrows.value])
  arrows.value = []
}

// Visual styling per score zone
function scoreButtonClass(value) {
  if (value === 'X') return 'bg-yellow-400 text-yellow-900 shadow-sm shadow-yellow-200'
  if (value === '10') return 'bg-yellow-300 text-yellow-900 shadow-sm shadow-yellow-100'
  if (value === '9') return 'bg-red-400 text-white shadow-sm shadow-red-200'
  if (value === '8') return 'bg-red-300 text-white shadow-sm shadow-red-100'
  if (value === '7') return 'bg-sky-400 text-white shadow-sm shadow-sky-200'
  if (value === '6') return 'bg-sky-300 text-white shadow-sm shadow-sky-100'
  if (value === 'M') return 'bg-gray-200 text-gray-500'
  return 'bg-gray-100 text-gray-700 hover:bg-gray-200'
}

// Slot ring color based on entered score
function arrowSlotClass(n) {
  const score = arrows.value[n - 1]
  if (!score) return 'border-2 border-dashed border-gray-200 text-gray-300'
  if (score === 'X') return 'bg-yellow-400 text-yellow-900'
  if (score === '10') return 'bg-yellow-300 text-yellow-900'
  if (score === '9' || score === '8') return 'bg-red-400 text-white'
  if (score === '7' || score === '6') return 'bg-sky-400 text-white'
  if (score === 'M') return 'bg-gray-200 text-gray-400'
  return 'bg-gray-100 text-gray-700'
}
</script>
