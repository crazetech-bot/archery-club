<template>
  <div :class="block ? 'w-full' : 'inline-block'">

    <!-- Label -->
    <label
      v-if="label"
      :for="selectId"
      class="mb-1.5 block text-sm font-medium text-gray-700"
    >
      {{ label }}
      <span v-if="required" class="ml-0.5 text-red-500">*</span>
    </label>

    <!-- Select wrapper -->
    <div class="relative">
      <select
        :id="selectId"
        :value="modelValue"
        :disabled="disabled"
        :class="selectClasses"
        v-bind="$attrs"
        @change="emit('update:modelValue', $event.target.value)"
        @blur="emit('blur', $event)"
      >
        <!-- Placeholder option -->
        <option v-if="placeholder" value="" disabled :selected="!modelValue">
          {{ placeholder }}
        </option>

        <!-- Options via prop array -->
        <template v-if="options.length">
          <option
            v-for="opt in options"
            :key="optionValue(opt)"
            :value="optionValue(opt)"
            :disabled="opt.disabled ?? false"
          >
            {{ optionLabel(opt) }}
          </option>
        </template>

        <!-- Options via default slot -->
        <slot v-else />
      </select>

      <!-- Custom chevron -->
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
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
  modelValue:  { type: [String, Number, null], default: null },
  label:       { type: String,  default: null },
  placeholder: { type: String,  default: 'Select…' },
  hint:        { type: String,  default: null },
  error:       { type: String,  default: null },
  disabled:    { type: Boolean, default: false },
  required:    { type: Boolean, default: false },
  block:       { type: Boolean, default: true },
  id:          { type: String,  default: null },
  /**
   * Pass an array of options instead of using <option> slot.
   * Items can be strings, numbers, or objects with { label, value, disabled }.
   */
  options:     { type: Array, default: () => [] },
  /** Key to use as label when options are objects */
  optionLabelKey: { type: String, default: 'label' },
  /** Key to use as value when options are objects */
  optionValueKey: { type: String, default: 'value' },
})

const emit = defineEmits(['update:modelValue', 'blur'])

const selectId = computed(() => props.id ?? `select-${Math.random().toString(36).slice(2, 7)}`)

function optionLabel(opt) {
  if (typeof opt === 'object' && opt !== null) return opt[props.optionLabelKey]
  return opt
}

function optionValue(opt) {
  if (typeof opt === 'object' && opt !== null) return opt[props.optionValueKey]
  return opt
}

const selectClasses = computed(() => [
  'w-full appearance-none rounded-xl border bg-white py-2.5 pl-3.5 pr-9 text-sm text-gray-900 transition',
  'focus:outline-none focus:ring-2 focus:ring-gray-900',
  'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400',
  props.error
    ? 'border-red-300 focus:ring-red-500'
    : 'border-gray-200 hover:border-gray-300',
  !props.modelValue ? 'text-gray-400' : '',
])
</script>
