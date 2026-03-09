<template>
  <!--
    A tactile pill / toggle button used for:
      • Score buttons in ShotGrid   (square=true,  activeVariant="gray")
      • X / Miss toggles in ShotGrid (square=false, activeVariant="amber" / "red")
      • End selector pills in EndEditor (square=false, activeVariant="gray")

    Sizing:
      square=true  → h-10 w-10 (equal-dimension for numeric score buttons)
      square=false → h-10 px-4 (variable-width for labelled toggles / end pills)

    Colour map avoids dynamic Tailwind class strings; all variants are
    whitelisted as full class names so the JIT scanner can detect them.
  -->
  <button
    :class="[
      active ? activeMap[activeVariant] : inactiveClass,
      square ? 'h-10 w-10' : 'h-10 px-4',
    ]"
    class="inline-flex items-center justify-center gap-1.5 rounded-xl border text-sm font-semibold transition-all active:scale-95 disabled:opacity-50"
    v-bind="$attrs"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  /** Whether the pill is in its selected / active state. */
  active: { type: Boolean, default: false },

  /**
   * Active colour variant.
   *   'gray'  → bg-gray-900  (score buttons, end pills)
   *   'amber' → bg-amber-400 (X toggle)
   *   'red'   → bg-red-400   (Miss toggle)
   */
  activeVariant: {
    type: String,
    default: 'gray',
    validator: (v) => ['gray', 'amber', 'red'].includes(v),
  },

  /** True renders h-10 w-10 (square); false renders h-10 px-4. */
  square: { type: Boolean, default: false },
})

// v-bind="$attrs" passes any extra attrs (class, style, @click, etc.) through
// to the root button, so callers never need extra wrappers.
defineOptions({ inheritAttrs: false })

// ── Style maps ────────────────────────────────────────────────────────────────

/** Full class string for each active variant (whitelisted for Tailwind JIT). */
const activeMap = {
  gray:  'bg-gray-900  text-white border-gray-900  ring-2 ring-gray-900  ring-offset-1',
  amber: 'bg-amber-400 text-white border-amber-400 ring-2 ring-amber-300 ring-offset-1',
  red:   'bg-red-400   text-white border-red-400   ring-2 ring-red-300   ring-offset-1',
}

/** Inactive state is identical for all variants. */
const inactiveClass =
  'bg-white text-gray-700 border-gray-200 hover:border-gray-300 hover:bg-gray-50'
</script>
