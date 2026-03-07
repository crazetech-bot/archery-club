<template>
  <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    <!-- Total Score -->
    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
      <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Total</p>
      <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">{{ totalScore }}</p>
      <p class="mt-0.5 text-xs text-gray-400">{{ endsShot }} end{{ endsShot !== 1 ? 's' : '' }} shot</p>
    </div>

    <!-- Average Per End -->
    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
      <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Avg / End</p>
      <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">{{ averagePerEnd }}</p>
      <p class="mt-0.5 text-xs text-gray-400">{{ arrowsPerEnd }} arrows/end</p>
    </div>

    <!-- X Count -->
    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-yellow-50">
      <p class="text-xs font-medium uppercase tracking-widest text-yellow-500">X Count</p>
      <p class="mt-1 text-3xl font-bold tabular-nums text-yellow-500">{{ xCount }}</p>
      <p class="mt-0.5 text-xs text-gray-400">inner gold hits</p>
    </div>

    <!-- 10 Count -->
    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
      <p class="text-xs font-medium uppercase tracking-widest text-gray-400">10s + Xs</p>
      <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">{{ tenCount }}</p>
      <p class="mt-0.5 text-xs text-gray-400">gold zone</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  /** Array of completed LiveEnd objects */
  ends: {
    type: Array,
    default: () => [],
  },
  arrowsPerEnd: {
    type: Number,
    default: 6,
  },
})

const endsShot = computed(() => props.ends.length)

const totalScore = computed(() =>
  props.ends.reduce((sum, end) => sum + (end.total_score ?? 0), 0)
)

const xCount = computed(() =>
  props.ends.reduce((sum, end) => sum + (end.x_count ?? 0), 0)
)

const tenCount = computed(() =>
  props.ends.reduce((sum, end) => sum + (end.ten_count ?? 0), 0)
)

const averagePerEnd = computed(() => {
  if (endsShot.value === 0) return '—'
  return (totalScore.value / endsShot.value).toFixed(1)
})
</script>
