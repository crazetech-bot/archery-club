<template>
  <!--
    Renders a colour-coded circular arrow-score badge.
    Colours follow the project design spec:
      X=amber-400  10=yellow-300  9-8=red-500  7-6=red-400
      5-4=sky-500  3-2=sky-300    1=gray-200   M=white+gray-ring
  -->
  <div
    :class="[colorClass, sizeClass]"
    class="flex items-center justify-center rounded-full font-bold tabular-nums ring-2 ring-offset-1"
  >
    <!-- Default slot lets callers override the label; falls back to computed label -->
    <slot>{{ label }}</slot>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  /** Numeric arrow score (0–10). Ignored when isX or isMiss is true. */
  score: { type: Number, default: null },

  /** True when the arrow is an X (inner gold, counts as 10). */
  isX: { type: Boolean, default: false },

  /** True when the arrow missed the target entirely. */
  isMiss: { type: Boolean, default: false },

  /**
   * Circle diameter:
   *   'md' → h-10 w-10 (recorded shots, standard display)
   *   'sm' → h-8  w-8  (pending preview, supporting contexts)
   */
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['md', 'sm'].includes(v),
  },
})

// ── Size class ────────────────────────────────────────────────────────────────
const sizeClass = computed(() =>
  props.size === 'sm' ? 'h-8 w-8 text-xs' : 'h-10 w-10 text-sm'
)

// ── Colour class ──────────────────────────────────────────────────────────────
/**
 * Returns a complete set of background + ring + text classes based on the
 * arrow result, matching the authoritative colour spec from the design system.
 */
const colorClass = computed(() => {
  if (props.isMiss) return 'bg-white     ring-gray-300  text-gray-500'
  if (props.isX)    return 'bg-amber-400 ring-amber-300 text-white'
  const s = props.score ?? 0
  if (s === 10) return 'bg-yellow-300 ring-yellow-200 text-gray-900'
  if (s >= 8)   return 'bg-red-500    ring-red-400    text-white'
  if (s >= 6)   return 'bg-red-400    ring-red-300    text-white'
  if (s >= 4)   return 'bg-sky-500    ring-sky-400    text-white'
  if (s >= 2)   return 'bg-sky-300    ring-sky-200    text-white'
  if (s === 1)  return 'bg-gray-200   ring-gray-200   text-gray-700'
  return 'bg-gray-100 ring-gray-200 text-gray-500'
})

// ── Default label ─────────────────────────────────────────────────────────────
/** Shown when no slot content is provided. */
const label = computed(() => {
  if (props.isMiss) return 'M'
  if (props.isX)    return 'X'
  return props.score ?? ''
})
</script>
