<template>
  <component
    :is="tag"
    v-bind="tagProps"
    :disabled="disabled || loading"
    :class="classes"
    @click="emit('click', $event)"
  >
    <!-- Loading spinner -->
    <svg
      v-if="loading"
      class="h-4 w-4 animate-spin"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
    </svg>

    <!-- Left icon slot -->
    <slot name="icon-left" />

    <!-- Label -->
    <span v-if="$slots.default">
      <slot />
    </span>

    <!-- Right icon slot -->
    <slot name="icon-right" />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  /** Visual style */
  variant: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'secondary', 'danger', 'ghost', 'outline'].includes(v),
  },
  /** Size */
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
  /** Render as Inertia <Link>, <a>, or <button> */
  as: {
    type: String,
    default: 'button',
    validator: (v) => ['button', 'a', 'link'].includes(v),
  },
  href:     { type: String,  default: null },
  type:     { type: String,  default: 'button' },
  disabled: { type: Boolean, default: false },
  loading:  { type: Boolean, default: false },
  /** Stretch to full width */
  block:    { type: Boolean, default: false },
})

const emit = defineEmits(['click'])

const tag = computed(() => {
  if (props.as === 'link') return Link
  if (props.as === 'a')    return 'a'
  return 'button'
})

const tagProps = computed(() => {
  if (props.as === 'button') return { type: props.type }
  return { href: props.href }
})

const variantClasses = {
  primary:  'bg-gray-900 text-white hover:bg-gray-700 focus-visible:ring-gray-900 disabled:bg-gray-300',
  secondary:'bg-gray-100 text-gray-800 hover:bg-gray-200 focus-visible:ring-gray-400',
  danger:   'bg-red-600 text-white hover:bg-red-500 focus-visible:ring-red-500 disabled:bg-red-300',
  ghost:    'bg-transparent text-gray-600 hover:bg-gray-100 focus-visible:ring-gray-400',
  outline:  'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus-visible:ring-gray-400',
}

const sizeClasses = {
  sm: 'h-8 px-3 text-xs gap-1.5 rounded-lg',
  md: 'h-10 px-4 text-sm gap-2 rounded-xl',
  lg: 'h-12 px-6 text-base gap-2.5 rounded-xl',
}

const classes = computed(() => [
  'inline-flex items-center justify-center font-medium transition-all',
  'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
  'active:scale-95 disabled:cursor-not-allowed disabled:opacity-50',
  variantClasses[props.variant],
  sizeClasses[props.size],
  props.block ? 'w-full' : '',
])
</script>
