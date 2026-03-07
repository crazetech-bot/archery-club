<template>
  <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
    <!-- End header -->
    <div class="mb-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white">
          {{ end.end_number }}
        </span>
        <span class="text-sm font-medium text-gray-500">End {{ end.end_number }}</span>
        <span
          v-if="end.tag"
          class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500"
        >
          {{ end.tag }}
        </span>
      </div>

      <!-- End total -->
      <div class="text-right">
        <span class="text-xl font-bold tabular-nums text-gray-900">{{ end.total_score }}</span>
        <span class="ml-1 text-xs text-gray-400">pts</span>
      </div>
    </div>

    <!-- Arrow scores -->
    <div class="flex flex-wrap gap-1.5">
      <span
        v-for="(arrow, index) in end.arrows"
        :key="index"
        :class="[
          'flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold',
          arrowClass(arrow.score),
        ]"
      >
        {{ arrow.score }}
      </span>
    </div>

    <!-- X / 10 badges -->
    <div v-if="end.x_count > 0 || end.ten_count > 0" class="mt-3 flex gap-2">
      <span
        v-if="end.x_count > 0"
        class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700"
      >
        {{ end.x_count }}X
      </span>
      <span
        v-if="end.ten_count > 0"
        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600"
      >
        {{ end.ten_count }} × 10+
      </span>
    </div>

    <!-- Notes -->
    <p v-if="end.notes" class="mt-2 text-xs italic text-gray-400">{{ end.notes }}</p>
  </div>
</template>

<script setup>
defineProps({
  /** LiveEnd object with nested arrows array */
  end: {
    type: Object,
    required: true,
  },
})

function arrowClass(score) {
  const s = String(score).toUpperCase()
  if (s === 'X') return 'bg-yellow-400 text-yellow-900'
  if (s === '10') return 'bg-yellow-300 text-yellow-900'
  if (s === '9' || s === '8') return 'bg-red-400 text-white'
  if (s === '7' || s === '6') return 'bg-sky-400 text-white'
  if (s === 'M') return 'bg-gray-200 text-gray-400'
  return 'bg-gray-100 text-gray-700'
}
</script>
