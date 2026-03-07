<template>
  <div :class="classes">

    <!-- Header -->
    <div
      v-if="$slots.header || title"
      :class="[
        'flex items-center justify-between',
        padded ? 'px-5 pt-5' : '',
        ($slots.default || $slots.footer) ? 'pb-4 border-b border-gray-50' : '',
      ]"
    >
      <div>
        <p v-if="label" class="mb-0.5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ label }}</p>
        <h3 v-if="title" class="text-sm font-semibold text-gray-900">{{ title }}</h3>
      </div>
      <slot name="header" />
    </div>

    <!-- Body -->
    <div v-if="$slots.default" :class="padded ? 'px-5 py-4' : ''">
      <slot />
    </div>

    <!-- Footer -->
    <div
      v-if="$slots.footer"
      :class="[
        'border-t border-gray-50',
        padded ? 'px-5 py-4' : '',
      ]"
    >
      <slot name="footer" />
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title:   { type: String,  default: null },
  /** Small uppercase label above the title */
  label:   { type: String,  default: null },
  /** Add standard padding to header/body/footer */
  padded:  { type: Boolean, default: true },
  /** Highlight ring colour */
  ring: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'none', 'yellow', 'green', 'red'].includes(v),
  },
  /** Remove default shadow */
  flat:    { type: Boolean, default: false },
})

const ringMap = {
  default: 'ring-gray-100',
  none:    '',
  yellow:  'ring-yellow-200',
  green:   'ring-green-200',
  red:     'ring-red-200',
}

const classes = computed(() => [
  'rounded-2xl bg-white',
  props.ring !== 'none' ? `ring-1 ${ringMap[props.ring]}` : '',
  props.flat ? '' : 'shadow-sm',
])
</script>
