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

    <!-- Input + stepper buttons -->
    <div class="flex rounded-xl border bg-white transition" :class="wrapperBorderClass">

      <!-- Decrement -->
      <button
        v-if="stepper"
        type="button"
        :disabled="disabled || atMin"
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-l-xl text-gray-400 transition hover:bg-gray-50 hover:text-gray-700 disabled:cursor-not-allowed disabled:opacity-40"
        @click="decrement"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
        </svg>
      </button>

      <!-- Input -->
      <input
        :id="inputId"
        type="number"
        :value="modelValue"
        :min="min"
        :max="max"
        :step="step"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :class="inputClasses"
        v-bind="$attrs"
        @input="handleInput($event.target.value)"
        @blur="emit('blur', $event)"
      />

      <!-- Unit label (e.g. "m", "kg") -->
      <div
        v-if="unit"
        class="flex items-center border-l border-gray-100 px-3 text-sm text-gray-400"
      >
        {{ unit }}
      </div>

      <!-- Increment -->
      <button
        v-if="stepper"
        type="button"
        :disabled="disabled || atMax"
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-r-xl text-gray-400 transition hover:bg-gray-50 hover:text-gray-700 disabled:cursor-not-allowed disabled:opacity-40"
        @click="increment"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
      </button>

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
  modelValue:  { type: Number,  default: null },
  label:       { type: String,  default: null },
  placeholder: { type: String,  default: null },
  hint:        { type: String,  default: null },
  error:       { type: String,  default: null },
  min:         { type: Number,  default: null },
  max:         { type: Number,  default: null },
  step:        { type: Number,  default: 1 },
  /** Show +/- stepper buttons */
  stepper:     { type: Boolean, default: false },
  /** Append a unit label inside the input (e.g. "m", "lbs") */
  unit:        { type: String,  default: null },
  disabled:    { type: Boolean, default: false },
  readonly:    { type: Boolean, default: false },
  required:    { type: Boolean, default: false },
  block:       { type: Boolean, default: true },
  id:          { type: String,  default: null },
})

const emit = defineEmits(['update:modelValue', 'blur'])

const inputId = computed(() => props.id ?? `number-${Math.random().toString(36).slice(2, 7)}`)

const atMin = computed(() => props.min !== null && props.modelValue <= props.min)
const atMax = computed(() => props.max !== null && props.modelValue >= props.max)

const wrapperBorderClass = computed(() =>
  props.error ? 'border-red-300 ring-1 ring-red-300' : 'border-gray-200 hover:border-gray-300 focus-within:ring-2 focus-within:ring-gray-900'
)

const inputClasses = computed(() => [
  'w-full bg-transparent px-3.5 py-2.5 text-sm text-gray-900 tabular-nums',
  'focus:outline-none',
  'disabled:cursor-not-allowed disabled:text-gray-400',
  'placeholder:text-gray-300',
  // Remove native spinners — we use stepper buttons instead
  '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none',
])

function handleInput(raw) {
  const parsed = raw === '' ? null : Number(raw)
  if (parsed === null || isNaN(parsed)) {
    emit('update:modelValue', null)
    return
  }
  let clamped = parsed
  if (props.min !== null) clamped = Math.max(props.min, clamped)
  if (props.max !== null) clamped = Math.min(props.max, clamped)
  emit('update:modelValue', clamped)
}

function increment() {
  const current = props.modelValue ?? (props.min ?? 0)
  handleInput(String(current + props.step))
}

function decrement() {
  const current = props.modelValue ?? (props.min ?? 0)
  handleInput(String(current - props.step))
}
</script>
