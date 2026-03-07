<template>
  <div :class="block ? 'w-full' : 'inline-block'">

    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="mb-1.5 block text-sm font-medium text-gray-700"
    >
      {{ label }}
      <span v-if="required" class="ml-0.5 text-red-500">*</span>
    </label>

    <!-- Input wrapper -->
    <div class="relative">

      <!-- Left icon -->
      <div
        v-if="$slots['icon-left']"
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
      >
        <slot name="icon-left" />
      </div>

      <!-- Input element -->
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :autocomplete="autocomplete"
        :class="inputClasses"
        v-bind="$attrs"
        @input="emit('update:modelValue', $event.target.value)"
        @blur="emit('blur', $event)"
        @focus="emit('focus', $event)"
        @keydown.enter="emit('enter', $event)"
      />

      <!-- Right icon / clear button -->
      <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-1">
        <button
          v-if="clearable && modelValue"
          type="button"
          class="text-gray-300 hover:text-gray-500"
          @click="emit('update:modelValue', '')"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <slot name="icon-right" />
      </div>
    </div>

    <!-- Helper / error text -->
    <p v-if="error" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="mt-1.5 text-xs text-gray-400">{{ hint }}</p>

  </div>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue:   { type: [String, Number], default: '' },
  label:        { type: String,  default: null },
  placeholder:  { type: String,  default: null },
  type:         { type: String,  default: 'text' },
  hint:         { type: String,  default: null },
  error:        { type: String,  default: null },
  autocomplete: { type: String,  default: 'off' },
  disabled:     { type: Boolean, default: false },
  readonly:     { type: Boolean, default: false },
  required:     { type: Boolean, default: false },
  clearable:    { type: Boolean, default: false },
  block:        { type: Boolean, default: true },
  /** Unique id — auto-generated if omitted */
  id:           { type: String,  default: null },
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'enter'])

const inputId = computed(() => props.id ?? `input-${Math.random().toString(36).slice(2, 7)}`)

const inputClasses = computed(() => [
  'w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-gray-900 transition',
  'placeholder:text-gray-300',
  'focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-0',
  'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400',
  props.$slots?.['icon-left'] ? 'pl-9'  : '',
  props.$slots?.['icon-right'] || props.clearable ? 'pr-9' : '',
  props.error
    ? 'border-red-300 focus:ring-red-500'
    : 'border-gray-200 hover:border-gray-300',
])
</script>
