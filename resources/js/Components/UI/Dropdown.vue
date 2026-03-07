<template>
  <div ref="rootEl" class="relative inline-block">

    <!-- Trigger -->
    <div @click="toggle">
      <slot name="trigger" :open="open" />
    </div>

    <!-- Menu -->
    <Transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-75 ease-in"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="open"
        :class="[
          'absolute z-50 mt-1 min-w-[10rem] rounded-xl border border-gray-100 bg-white py-1 shadow-lg',
          alignClass,
        ]"
      >
        <slot :close="close" />
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  align: {
    type: String,
    default: 'left',
    validator: (v) => ['left', 'right'].includes(v),
  },
})

const emit = defineEmits(['open', 'close'])

const open   = ref(false)
const rootEl = ref(null)

const alignClass = computed(() =>
  props.align === 'right' ? 'right-0' : 'left-0'
)

function toggle() {
  open.value ? close() : openMenu()
}

function openMenu() {
  open.value = true
  emit('open')
}

function close() {
  open.value = false
  emit('close')
}

// Close when clicking outside
function handleOutsideClick(e) {
  if (rootEl.value && !rootEl.value.contains(e.target)) {
    close()
  }
}

// Close on Escape
function handleKeydown(e) {
  if (e.key === 'Escape') close()
}

onMounted(() => {
  document.addEventListener('mousedown', handleOutsideClick)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleOutsideClick)
  document.removeEventListener('keydown', handleKeydown)
})

// Expose close so parent can close programmatically
defineExpose({ close, open })
</script>
