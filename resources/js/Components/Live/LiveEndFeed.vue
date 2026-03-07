<template>
  <div class="space-y-2">
    <TransitionGroup
      tag="div"
      class="space-y-2"
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
    >
      <div
        v-for="end in visibleEnds"
        :key="end.id ?? end.end_number"
        class="flex items-center gap-3 rounded-xl bg-gray-50 px-3 py-2"
      >
        <!-- End number -->
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600">
          {{ end.end_number }}
        </span>

        <!-- Arrow badges -->
        <div class="flex flex-1 flex-wrap gap-1">
          <span
            v-for="(arrow, i) in end.arrows"
            :key="i"
            :class="['flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold', arrowClass(arrow.score)]"
          >
            {{ arrow.score }}
          </span>
        </div>

        <!-- End total -->
        <span class="shrink-0 text-sm font-bold tabular-nums text-gray-800">
          {{ end.total_score }}
        </span>

        <!-- X badge -->
        <span
          v-if="end.x_count > 0"
          class="shrink-0 rounded-full bg-yellow-100 px-1.5 py-0.5 text-xs font-semibold text-yellow-700"
        >
          {{ end.x_count }}X
        </span>
      </div>
    </TransitionGroup>

    <!-- Truncated indicator -->
    <p v-if="ends.length > maxVisible" class="text-center text-xs text-gray-400">
      + {{ ends.length - maxVisible }} earlier end{{ ends.length - maxVisible !== 1 ? 's' : '' }}
    </p>

    <!-- Empty state -->
    <p v-if="ends.length === 0" class="py-2 text-center text-xs text-gray-400">
      No ends yet
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  /** Array of LiveEnd objects with nested arrows */
  ends: {
    type: Array,
    default: () => [],
  },
  /** How many of the most recent ends to display */
  maxVisible: {
    type: Number,
    default: 4,
  },
})

// Show the N most recent ends, newest first
const visibleEnds = computed(() =>
  [...props.ends].reverse().slice(0, props.maxVisible)
)

function arrowClass(score) {
  const s = String(score).toUpperCase()
  if (s === 'X')               return 'bg-yellow-400 text-yellow-900'
  if (s === '10')              return 'bg-yellow-300 text-yellow-900'
  if (s === '9' || s === '8') return 'bg-red-400 text-white'
  if (s === '7' || s === '6') return 'bg-sky-400 text-white'
  if (s === 'M')              return 'bg-gray-200 text-gray-400'
  return 'bg-gray-100 text-gray-700'
}
</script>
