<template>
  <div>
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="mb-1.5 block text-sm font-medium text-gray-700"
    >
      {{ label }}
      <span v-if="required" class="ml-0.5 text-red-500">*</span>
    </label>

    <!-- Select -->
    <select
      :id="inputId"
      v-bind="$attrs"
      :value="modelValue"
      :required="required"
      :disabled="disabled"
      :class="[
        error
          ? 'border-red-300 focus:border-red-400 focus:ring-red-400'
          : 'border-gray-200 focus:border-gray-400 focus:ring-gray-400',
        'w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-gray-900 transition focus:outline-none focus:ring-1 disabled:cursor-not-allowed disabled:opacity-50',
      ]"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <!-- Placeholder option -->
      <option v-if="placeholder" value="" disabled :selected="!modelValue">
        {{ placeholder }}
      </option>

      <!-- Options -->
      <option
        v-for="opt in options"
        :key="opt.value ?? opt"
        :value="opt.value ?? opt"
      >
        {{ opt.label ?? opt }}
      </option>

      <!-- Allow arbitrary <option> elements via slot -->
      <slot />
    </select>

    <!-- Inline error -->
    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Prevent $attrs from being applied twice (we bind them manually on <select>)
defineOptions({ inheritAttrs: false })

// ── Props / Emits ─────────────────────────────────────────────────────────────
const props = defineProps({
  /** Bound value (v-model). */
  modelValue: { type: [String, Number, null], default: '' },

  /** Input label text. Omit to skip the label element. */
  label: { type: String, default: '' },

  /**
   * Flat option list. Each item is either:
   * - a primitive (used as both value and label), or
   * - an object with { value, label } keys.
   * For richer options (e.g. grouped), use the default slot instead.
   */
  options: { type: Array, default: () => [] },

  /** Placeholder text shown as the first disabled option. */
  placeholder: { type: String, default: '' },

  /** Validation error message shown below the select. */
  error:    { type: String,  default: '' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])

// Stable element ID for label ↔ input association
const inputId = computed(() =>
  `select-${props.label?.toLowerCase().replace(/\s+/g, '-') ?? Math.random().toString(36).slice(2)}`
)
</script>
