<template>
  <span :class="classes">
    <!-- Optional dot indicator -->
    <span
      v-if="dot"
      :class="[
        'h-1.5 w-1.5 rounded-full',
        dotColorClass,
        pulse ? 'animate-pulse' : '',
      ]"
    />
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  color: {
    type: String,
    default: 'gray',
    validator: (v) =>
      ['gray', 'green', 'red', 'yellow', 'blue', 'purple', 'sky', 'orange'].includes(v),
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md'].includes(v),
  },
  /** Show a coloured dot before the label */
  dot:   { type: Boolean, default: false },
  /** Animate the dot (for live/active states) */
  pulse: { type: Boolean, default: false },
})

const colorMap = {
  gray:   'bg-gray-100 text-gray-600',
  green:  'bg-green-100 text-green-700',
  red:    'bg-red-100 text-red-600',
  yellow: 'bg-yellow-100 text-yellow-700',
  blue:   'bg-blue-100 text-blue-700',
  purple: 'bg-purple-100 text-purple-700',
  sky:    'bg-sky-100 text-sky-700',
  orange: 'bg-orange-100 text-orange-700',
}

const dotColorMap = {
  gray:   'bg-gray-500',
  green:  'bg-green-500',
  red:    'bg-red-500',
  yellow: 'bg-yellow-500',
  blue:   'bg-blue-500',
  purple: 'bg-purple-500',
  sky:    'bg-sky-500',
  orange: 'bg-orange-500',
}

const sizeMap = {
  sm: 'px-1.5 py-0.5 text-xs gap-1',
  md: 'px-2.5 py-0.5 text-xs gap-1.5',
}

const classes = computed(() => [
  'inline-flex items-center rounded-full font-medium',
  colorMap[props.color],
  sizeMap[props.size],
])

const dotColorClass = computed(() => dotColorMap[props.color])
</script>
