<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0 -translate-y-1"
    leave-active-class="transition duration-150 ease-in"
    leave-to-class="opacity-0 -translate-y-1"
  >
    <div v-if="visible" :class="wrapperClasses" role="alert">

      <!-- Icon -->
      <div class="shrink-0">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <!-- info -->
          <path v-if="type === 'info'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          <!-- success -->
          <path v-else-if="type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          <!-- warning -->
          <path v-else-if="type === 'warning'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          <!-- error -->
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <p v-if="title" :class="['text-sm font-semibold', titleColorClass]">{{ title }}</p>
        <p :class="['text-sm', title ? 'mt-0.5' : '', messageColorClass]">
          <slot>{{ message }}</slot>
        </p>
      </div>

      <!-- Dismiss button -->
      <button
        v-if="dismissible"
        class="shrink-0 rounded-lg p-0.5 opacity-60 transition hover:opacity-100"
        @click="visible = false"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>

    </div>
  </Transition>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator: (v) => ['info', 'success', 'warning', 'error'].includes(v),
  },
  title:       { type: String,  default: null },
  message:     { type: String,  default: null },
  dismissible: { type: Boolean, default: false },
})

const visible = ref(true)

const config = {
  info:    { wrapper: 'bg-blue-50 border-blue-200 text-blue-700',    title: 'text-blue-800',  message: 'text-blue-700' },
  success: { wrapper: 'bg-green-50 border-green-200 text-green-700', title: 'text-green-800', message: 'text-green-700' },
  warning: { wrapper: 'bg-yellow-50 border-yellow-200 text-yellow-700', title: 'text-yellow-800', message: 'text-yellow-700' },
  error:   { wrapper: 'bg-red-50 border-red-200 text-red-700',       title: 'text-red-800',   message: 'text-red-700' },
}

const wrapperClasses   = computed(() => `flex items-start gap-3 rounded-2xl border p-4 ${config[props.type].wrapper}`)
const titleColorClass  = computed(() => config[props.type].title)
const messageColorClass = computed(() => config[props.type].message)
</script>
