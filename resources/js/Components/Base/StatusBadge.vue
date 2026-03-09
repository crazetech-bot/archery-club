<template>
  <span :class="badgeClass" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">
    {{ status }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  /**
   * Scorecard lifecycle status.
   * Matches the values used throughout Module-4.
   */
  status: {
    type: String,
    required: true,
    validator: (v) => ['draft', 'submitted', 'locked'].includes(v),
  },
})

// ── Badge style map ───────────────────────────────────────────────────────────
const badgeClass = computed(() => ({
  draft:     'bg-gray-100 text-gray-500 ring-1 ring-gray-200',
  submitted: 'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
  locked:    'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
}[props.status] ?? 'bg-gray-100 text-gray-500 ring-1 ring-gray-200'))
</script>
