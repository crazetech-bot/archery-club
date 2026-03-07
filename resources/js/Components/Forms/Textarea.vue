<template>
  <div :class="block ? 'w-full' : 'inline-block'">

    <!-- Label -->
    <label
      v-if="label"
      :for="textareaId"
      class="mb-1.5 block text-sm font-medium text-gray-700"
    >
      {{ label }}
      <span v-if="required" class="ml-0.5 text-red-500">*</span>
    </label>

    <!-- Textarea -->
    <textarea
      :id="textareaId"
      :value="modelValue"
      :placeholder="placeholder"
      :rows="rows"
      :disabled="disabled"
      :readonly="readonly"
      :maxlength="maxlength"
      :class="textareaClasses"
      v-bind="$attrs"
      @input="emit('update:modelValue', $event.target.value)"
      @blur="emit('blur', $event)"
      @focus="emit('focus', $event)"
    />

    <!-- Footer row: char count + error/hint -->
    <div class="mt-1.5 flex items-start justify-between gap-2">
      <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
      <p v-else-if="hint" class="text-xs text-gray-400">{{ hint }}</p>
      <span v-else class="flex-1" />

      <p
        v-if="maxlength"
        :class="[
          'shrink-0 text-xs tabular-nums',
          charCount >= maxlength ? 'text-red-500' : 'text-gray-400',
        ]"
      >
        {{ charCount }}/{{ maxlength }}
      </p>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue:  { type: String,  default: '' },
  label:       { type: String,  default: null },
  placeholder: { type: String,  default: null },
  hint:        { type: String,  default: null },
  error:       { type: String,  default: null },
  rows:        { type: Number,  default: 3 },
  maxlength:   { type: Number,  default: null },
  disabled:    { type: Boolean, default: false },
  readonly:    { type: Boolean, default: false },
  required:    { type: Boolean, default: false },
  block:       { type: Boolean, default: true },
  /** Allow the user to resize the textarea */
  resize:      { type: Boolean, default: true },
  id:          { type: String,  default: null },
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const textareaId = computed(() => props.id ?? `textarea-${Math.random().toString(36).slice(2, 7)}`)

const charCount = computed(() => (props.modelValue ?? '').length)

const textareaClasses = computed(() => [
  'w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-gray-900 transition',
  'placeholder:text-gray-300',
  'focus:outline-none focus:ring-2 focus:ring-gray-900',
  'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400',
  props.resize ? 'resize-y' : 'resize-none',
  props.error
    ? 'border-red-300 focus:ring-red-500'
    : 'border-gray-200 hover:border-gray-300',
])
</script>
