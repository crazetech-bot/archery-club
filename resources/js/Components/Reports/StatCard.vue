<template>
  <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
    <p class="text-xs font-medium uppercase tracking-widest text-gray-400">{{ label }}</p>

    <!-- Value -->
    <div class="mt-2 flex items-end gap-2">
      <p
        :class="[
          'text-3xl font-bold tabular-nums leading-none',
          valueClass ?? 'text-gray-900',
        ]"
      >
        {{ value ?? '—' }}
      </p>
      <p v-if="unit" class="mb-0.5 text-sm text-gray-400">{{ unit }}</p>
    </div>

    <!-- Delta (improvement indicator) -->
    <div v-if="delta !== null && delta !== undefined" class="mt-2 flex items-center gap-1">
      <svg
        :class="['h-3.5 w-3.5 transition-transform', delta >= 0 ? 'text-green-500' : 'rotate-180 text-red-400']"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
      </svg>
      <span
        :class="['text-xs font-semibold', delta >= 0 ? 'text-green-600' : 'text-red-500']"
      >
        {{ delta >= 0 ? '+' : '' }}{{ delta }}%
      </span>
      <span class="text-xs text-gray-400">vs first 3 sessions</span>
    </div>

    <!-- Sub-label -->
    <p v-if="subLabel" class="mt-1 text-xs text-gray-400">{{ subLabel }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], default: null },
  unit: { type: String, default: null },
  subLabel: { type: String, default: null },
  /** Optional Tailwind text colour class override */
  valueClass: { type: String, default: null },
  /** Percentage delta shown as up/down arrow. Pass null to hide. */
  delta: { type: Number, default: null },
})
</script>
