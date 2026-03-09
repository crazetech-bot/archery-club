<template>
  <!--
    A self-contained metric tile: small-caps label + large numeric value.
    Used in MetricsPanel (analytics grid) and ScoreSummary (supporting metrics).

    Color variants:
      gray    → text-gray-900  (neutral totals)
      amber   → text-amber-500 (X count)
      emerald → text-emerald-600 (hit rate / positive metrics)
      red     → text-red-500   (misses / negative metrics)
  -->
  <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100">
    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ label }}</p>
    <p class="mt-1.5 font-bold tabular-nums" :class="[sizeClass, colorClass]">
      <slot />
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  /** Displayed as a small-caps label above the value. */
  label: { type: String, required: true },

  /**
   * Typography size of the value.
   *   'xl'   → text-2xl  (MetricsPanel analytics grid — default)
   *   'hero' → text-3xl  (ScoreSummary supporting metrics if needed)
   */
  size: {
    type: String,
    default: 'xl',
    validator: (v) => ['xl', 'hero'].includes(v),
  },

  /**
   * Colour of the value text.
   *   'gray'    → neutral
   *   'amber'   → X count / gold metrics
   *   'emerald' → positive / percentage metrics
   *   'red'     → negative / miss metrics
   */
  color: {
    type: String,
    default: 'gray',
    validator: (v) => ['gray', 'amber', 'emerald', 'red'].includes(v),
  },
})

// ── Style maps ────────────────────────────────────────────────────────────────
const sizeMap = {
  xl:   'text-2xl',
  hero: 'text-3xl',
}

const colorMap = {
  gray:    'text-gray-900',
  amber:   'text-amber-500',
  emerald: 'text-emerald-600',
  red:     'text-red-500',
}

const sizeClass  = computed(() => sizeMap[props.size]  ?? sizeMap.xl)
const colorClass = computed(() => colorMap[props.color] ?? colorMap.gray)
</script>
