<template>
  <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100" :class="padding">

    <!-- Optional header row (title + action slot) -->
    <div
      v-if="title || $slots.action"
      :class="[headerBorder ? 'border-b border-gray-100' : '', 'mb-4 flex items-center justify-between', padding === 'p-0' ? 'px-5 pt-5' : '']"
    >
      <p v-if="title" class="text-xs font-semibold uppercase tracking-widest text-gray-400">
        {{ title }}
      </p>
      <slot name="action" />
    </div>

    <!-- Card body -->
    <slot />

  </div>
</template>

<script setup>
// ── Props ─────────────────────────────────────────────────────────────────────
defineProps({
  /**
   * Optional section label rendered as a small-caps header above the content.
   * Pass an empty string or omit to skip the header row entirely.
   */
  title: { type: String, default: '' },

  /**
   * Padding class applied to the outer wrapper.
   * - 'p-5'  → default (standard card, matches existing scoring cards)
   * - 'p-0'  → no padding (use when the content provides its own, e.g. a table)
   */
  padding: {
    type: String,
    default: 'p-5',
    validator: (v) => ['p-5', 'p-4', 'p-6', 'p-0'].includes(v),
  },

  /**
   * Whether to draw a bottom border under the header row.
   * Useful when the card body starts immediately below the header.
   */
  headerBorder: { type: Boolean, default: false },
})
</script>
