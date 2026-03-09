<template>
  <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">

    <!-- Card header -->
    <div class="mb-5 flex items-center justify-between">
      <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Score Summary</p>
      <StatusBadge :status="scorecard.status" />
    </div>

    <!-- Stats: hero layout -->
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

      <!-- Total Score — hero metric -->
      <div class="flex-1">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Score</p>
        <p class="mt-1 text-5xl font-bold leading-none tabular-nums text-gray-900">
          {{ scorecard.total_score ?? 0 }}
        </p>
      </div>

      <!-- Vertical divider (desktop only) -->
      <div class="hidden h-16 w-px bg-gray-100 sm:block"></div>

      <!-- Supporting metrics -->
      <div class="flex gap-8">

        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">X Count</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-amber-500">
            {{ scorecard.x_count ?? 0 }}
          </p>
        </div>

        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Arrows</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-700">
            {{ scorecard.arrow_count ?? 0 }}
          </p>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import StatusBadge from '@/Components/Base/StatusBadge.vue'

// ── Props ─────────────────────────────────────────────────────────────────────
defineProps({
  scorecard: {
    type: Object,
    required: true,
  },
})
</script>
