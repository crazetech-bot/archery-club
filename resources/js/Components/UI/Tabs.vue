<template>
  <div>

    <!-- Tab bar -->
    <div
      :class="[
        'flex',
        variant === 'underline'
          ? 'border-b border-gray-200 gap-0'
          : 'gap-1 rounded-xl bg-gray-100 p-1',
      ]"
    >
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :disabled="tab.disabled"
        :class="tabClasses(tab)"
        @click="select(tab)"
      >
        <!-- Tab icon -->
        <component v-if="tab.icon" :is="tab.icon" class="h-4 w-4 shrink-0" />

        {{ tab.label }}

        <!-- Count badge on tab -->
        <span
          v-if="tab.count !== undefined"
          :class="[
            'ml-1.5 rounded-full px-1.5 py-0.5 text-xs font-semibold tabular-nums',
            modelValue === tab.value
              ? 'bg-white/20 text-white'
              : 'bg-gray-200 text-gray-600',
          ]"
        >
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- Tab panels -->
    <div class="mt-4">
      <slot :active="modelValue" />
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  /** Currently active tab value — use v-model */
  modelValue: { type: [String, Number], required: true },

  /**
   * Array of tab definitions:
   * { label: string, value: string|number, icon?: Component, count?: number, disabled?: boolean }
   */
  tabs: {
    type: Array,
    required: true,
  },

  variant: {
    type: String,
    default: 'pills',
    validator: (v) => ['pills', 'underline'].includes(v),
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

function select(tab) {
  if (tab.disabled) return
  emit('update:modelValue', tab.value)
  emit('change', tab.value)
}

function tabClasses(tab) {
  const isActive = props.modelValue === tab.value

  if (props.variant === 'underline') {
    return [
      'flex items-center gap-1.5 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors',
      isActive
        ? 'border-gray-900 text-gray-900'
        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
      tab.disabled ? 'cursor-not-allowed opacity-40' : 'cursor-pointer',
    ]
  }

  // pills variant
  return [
    'flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition-all',
    isActive
      ? 'bg-gray-900 text-white shadow-sm'
      : 'text-gray-600 hover:text-gray-900',
    tab.disabled ? 'cursor-not-allowed opacity-40' : 'cursor-pointer',
  ]
}
</script>
