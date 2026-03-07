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

    <!-- Input + calendar icon wrapper -->
    <div ref="rootEl" class="relative">
      <div class="relative">
        <!-- Calendar icon -->
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
          <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>

        <!-- Text display input -->
        <input
          :id="inputId"
          type="text"
          :value="displayValue"
          :placeholder="placeholder"
          :disabled="disabled"
          readonly
          :class="[
            'w-full cursor-pointer rounded-xl border bg-white py-2.5 pl-9 pr-9 text-sm text-gray-900 transition',
            'focus:outline-none focus:ring-2 focus:ring-gray-900',
            'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400',
            error ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 hover:border-gray-300',
          ]"
          @click="!disabled && togglePicker()"
        />

        <!-- Clear button -->
        <button
          v-if="modelValue && clearable"
          type="button"
          class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-300 hover:text-gray-500"
          @click.stop="clear"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Calendar dropdown -->
      <Transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="opacity-0 scale-95"
        leave-active-class="transition duration-75 ease-in"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="pickerOpen"
          class="absolute left-0 top-full z-50 mt-1 w-72 rounded-2xl border border-gray-100 bg-white p-4 shadow-lg"
        >
          <!-- Month navigation -->
          <div class="mb-3 flex items-center justify-between">
            <button
              type="button"
              class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100"
              @click="prevMonth"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>

            <button
              type="button"
              class="text-sm font-semibold text-gray-800 hover:text-gray-900"
              @click="viewYear = !viewYear"
            >
              {{ monthLabel }} {{ viewMonth.getFullYear() }}
            </button>

            <button
              type="button"
              class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100"
              @click="nextMonth"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>

          <!-- Year grid (shown when title is clicked) -->
          <div v-if="viewYear" class="mb-3 grid grid-cols-4 gap-1">
            <button
              v-for="yr in yearRange"
              :key="yr"
              type="button"
              :class="[
                'rounded-lg py-1.5 text-xs font-medium transition',
                viewMonth.getFullYear() === yr
                  ? 'bg-gray-900 text-white'
                  : 'text-gray-600 hover:bg-gray-100',
              ]"
              @click="setYear(yr)"
            >
              {{ yr }}
            </button>
          </div>

          <template v-else>
            <!-- Day-of-week headers -->
            <div class="mb-1 grid grid-cols-7 text-center">
              <span
                v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']"
                :key="d"
                class="py-1 text-xs font-medium text-gray-400"
              >
                {{ d }}
              </span>
            </div>

            <!-- Day cells -->
            <div class="grid grid-cols-7 gap-y-0.5">
              <!-- Leading blank cells -->
              <div v-for="n in leadingBlanks" :key="`b-${n}`" />

              <button
                v-for="day in daysInMonth"
                :key="day"
                type="button"
                :disabled="isDayDisabled(day)"
                :class="[
                  'flex h-8 w-8 items-center justify-center rounded-full text-sm transition',
                  isDaySelected(day) ? 'bg-gray-900 font-semibold text-white' : '',
                  isToday(day) && !isDaySelected(day) ? 'font-semibold text-gray-900 ring-1 ring-gray-300' : '',
                  !isDaySelected(day) && !isToday(day) && !isDayDisabled(day) ? 'text-gray-700 hover:bg-gray-100' : '',
                  isDayDisabled(day) ? 'cursor-not-allowed text-gray-300' : '',
                ]"
                @click="selectDay(day)"
              >
                {{ day }}
              </button>
            </div>
          </template>

          <!-- Today shortcut -->
          <div class="mt-3 border-t border-gray-50 pt-3 text-center">
            <button
              type="button"
              class="text-xs font-medium text-gray-500 hover:text-gray-900"
              @click="selectToday"
            >
              Today
            </button>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Helper / error text -->
    <p v-if="error" class="mt-1.5 text-xs text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="mt-1.5 text-xs text-gray-400">{{ hint }}</p>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  /** ISO date string: 'YYYY-MM-DD' */
  modelValue:  { type: String,  default: null },
  label:       { type: String,  default: null },
  placeholder: { type: String,  default: 'Select date' },
  hint:        { type: String,  default: null },
  error:       { type: String,  default: null },
  /** Minimum selectable date as 'YYYY-MM-DD' */
  min:         { type: String,  default: null },
  /** Maximum selectable date as 'YYYY-MM-DD' */
  max:         { type: String,  default: null },
  disabled:    { type: Boolean, default: false },
  required:    { type: Boolean, default: false },
  clearable:   { type: Boolean, default: true },
  block:       { type: Boolean, default: true },
  id:          { type: String,  default: null },
})

const emit = defineEmits(['update:modelValue', 'change'])

const pickerOpen = ref(false)
const viewYear   = ref(false)
const rootEl     = ref(null)

// The month currently displayed in the calendar
const viewMonth  = ref(props.modelValue ? new Date(props.modelValue + 'T00:00:00') : new Date())

const inputId = computed(() => props.id ?? `datepicker-${Math.random().toString(36).slice(2, 7)}`)

// ── Display ───────────────────────────────────────────────────────────────────

const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December']

const displayValue = computed(() => {
  if (!props.modelValue) return ''
  const [y, m, d] = props.modelValue.split('-')
  return `${MONTHS[parseInt(m) - 1]} ${parseInt(d)}, ${y}`
})

const monthLabel = computed(() => MONTHS[viewMonth.value.getMonth()])

// ── Calendar grid ─────────────────────────────────────────────────────────────

const daysInMonth = computed(() => {
  return new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth() + 1, 0).getDate()
})

const leadingBlanks = computed(() => {
  return new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth(), 1).getDay()
})

const yearRange = computed(() => {
  const current = new Date().getFullYear()
  return Array.from({ length: 20 }, (_, i) => current - 10 + i)
})

// ── Day state checks ──────────────────────────────────────────────────────────

function isoForDay(day) {
  const y = viewMonth.value.getFullYear()
  const m = String(viewMonth.value.getMonth() + 1).padStart(2, '0')
  return `${y}-${m}-${String(day).padStart(2, '0')}`
}

function isDaySelected(day) { return isoForDay(day) === props.modelValue }

function isToday(day) {
  const today = new Date()
  return (
    day === today.getDate() &&
    viewMonth.value.getMonth() === today.getMonth() &&
    viewMonth.value.getFullYear() === today.getFullYear()
  )
}

function isDayDisabled(day) {
  const iso = isoForDay(day)
  if (props.min && iso < props.min) return true
  if (props.max && iso > props.max) return true
  return false
}

// ── Actions ───────────────────────────────────────────────────────────────────

function togglePicker() { pickerOpen.value = !pickerOpen.value }

function selectDay(day) {
  const iso = isoForDay(day)
  emit('update:modelValue', iso)
  emit('change', iso)
  pickerOpen.value = false
  viewYear.value   = false
}

function selectToday() {
  const today = new Date()
  viewMonth.value = new Date(today.getFullYear(), today.getMonth(), 1)
  selectDay(today.getDate())
}

function clear() {
  emit('update:modelValue', null)
  emit('change', null)
}

function prevMonth() {
  viewMonth.value = new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth() - 1, 1)
}

function nextMonth() {
  viewMonth.value = new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth() + 1, 1)
}

function setYear(yr) {
  viewMonth.value = new Date(yr, viewMonth.value.getMonth(), 1)
  viewYear.value  = false
}

// ── Click outside to close ────────────────────────────────────────────────────

function handleOutsideClick(e) {
  if (rootEl.value && !rootEl.value.contains(e.target)) {
    pickerOpen.value = false
    viewYear.value   = false
  }
}

onMounted(()   => document.addEventListener('mousedown', handleOutsideClick))
onUnmounted(() => document.removeEventListener('mousedown', handleOutsideClick))
</script>
