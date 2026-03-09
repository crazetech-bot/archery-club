<template>
  <button
    v-bind="$attrs"
    :type="type"
    :disabled="disabled || loading"
    :class="[variantClass, sizeClass, 'inline-flex items-center justify-center font-semibold transition active:scale-95 disabled:cursor-not-allowed disabled:opacity-50']"
  >
    <!-- Loading spinner -->
    <svg
      v-if="loading"
      class="-ml-0.5 mr-1.5 animate-spin"
      :class="spinnerSize"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
    </svg>

    <slot>{{ loading ? (loadingText ?? label) : label }}</slot>
  </button>
</template>

<script setup>
import { computed } from 'vue'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  /** Button label (used when no slot is provided). */
  label: { type: String, default: '' },

  /**
   * Visual variant.
   * - primary   → gray-900 fill  (default action)
   * - secondary → bordered ghost (cancel / back)
   * - warning   → amber fill     (lock / destructive-ish)
   * - danger    → red fill       (delete)
   */
  variant: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'secondary', 'warning', 'danger'].includes(v),
  },

  /** Size variant. */
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },

  type:        { type: String,  default: 'button' },
  disabled:    { type: Boolean, default: false },
  loading:     { type: Boolean, default: false },
  loadingText: { type: String,  default: null },
})

// ── Computed classes ──────────────────────────────────────────────────────────
const variantClass = computed(() => ({
  primary:   'rounded-xl bg-gray-900 text-white hover:bg-gray-700',
  secondary: 'rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50',
  warning:   'rounded-xl bg-amber-500 text-white hover:bg-amber-600',
  danger:    'rounded-xl bg-red-500 text-white hover:bg-red-600',
}[props.variant]))

const sizeClass = computed(() => ({
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-4 py-2 text-sm',
  lg: 'px-6 py-2.5 text-sm',
}[props.size]))

const spinnerSize = computed(() => ({
  sm: 'h-3 w-3',
  md: 'h-3.5 w-3.5',
  lg: 'h-4 w-4',
}[props.size]))
</script>
