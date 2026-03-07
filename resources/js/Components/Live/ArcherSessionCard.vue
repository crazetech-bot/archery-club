<template>
  <div
    :class="[
      'flex flex-col rounded-2xl bg-white shadow-sm ring-1 transition-all duration-300',
      session.status === 'active'
        ? 'ring-green-200 shadow-green-50'
        : session.status === 'idle'
          ? 'ring-gray-100 opacity-60'
          : 'ring-gray-100',
    ]"
  >
    <!-- ── Card header ─────────────────────────────────────────────────────── -->
    <div class="flex items-start justify-between border-b border-gray-50 p-4">
      <div class="flex items-center gap-3">
        <!-- Avatar -->
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-900 text-sm font-bold text-white">
          {{ initials(session.archer.name) }}
        </div>

        <div>
          <p class="font-semibold text-gray-900">{{ session.archer.name }}</p>
          <p class="text-xs text-gray-400">
            <span v-if="session.round_type">{{ session.round_type }}</span>
            <span v-else>Practice</span>
            <span v-if="session.distance_metres"> · {{ session.distance_metres }}m</span>
          </p>
        </div>
      </div>

      <!-- Status + duration -->
      <div class="flex flex-col items-end gap-1">
        <span
          v-if="session.status === 'active'"
          class="flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700"
        >
          <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500" />
          Live
        </span>
        <span
          v-else-if="session.status === 'completed'"
          class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500"
        >
          Done
        </span>
        <span
          v-else
          class="rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-400"
        >
          Idle
        </span>
        <span v-if="session.started_at" class="text-xs text-gray-400">{{ sessionDuration }}</span>
      </div>
    </div>

    <!-- ── Stats row (only when session exists) ───────────────────────────── -->
    <div v-if="session.status !== 'idle'" class="grid grid-cols-4 divide-x divide-gray-50 border-b border-gray-50">
      <div class="px-3 py-3 text-center">
        <p class="text-xs text-gray-400">Total</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ session.total_score }}</p>
      </div>
      <div class="px-3 py-3 text-center">
        <p class="text-xs text-gray-400">Ends</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ session.ends.length }}</p>
      </div>
      <div class="px-3 py-3 text-center">
        <p class="text-xs text-yellow-500">X</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums text-yellow-500">{{ session.x_count }}</p>
      </div>
      <div class="px-3 py-3 text-center">
        <p class="text-xs text-gray-400">Avg</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">
          {{ session.ends.length > 0 ? avgPerEnd : '—' }}
        </p>
      </div>
    </div>

    <!-- ── Idle state ─────────────────────────────────────────────────────── -->
    <div v-if="session.status === 'idle'" class="flex flex-col items-center justify-center py-8 text-center">
      <svg class="mb-2 h-8 w-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
        <circle cx="12" cy="12" r="6"  stroke-width="1.5"/>
        <circle cx="12" cy="12" r="2"  stroke-width="1.5"/>
      </svg>
      <p class="text-xs text-gray-400">No active session</p>
    </div>

    <!-- ── Last end highlight ─────────────────────────────────────────────── -->
    <div v-if="lastEnd" class="border-b border-gray-50 px-4 py-3">
      <p class="mb-2 text-xs font-medium text-gray-400">End {{ lastEnd.end_number }}</p>
      <div class="flex items-center gap-2">
        <div class="flex flex-wrap gap-1">
          <span
            v-for="(arrow, i) in lastEnd.arrows"
            :key="i"
            :class="['flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold', arrowClass(arrow.score)]"
          >
            {{ arrow.score }}
          </span>
        </div>
        <div class="ml-auto text-right">
          <span class="text-base font-bold tabular-nums text-gray-900">{{ lastEnd.total_score }}</span>
          <span class="ml-1 text-xs text-gray-400">pts</span>
        </div>
      </div>
      <p v-if="lastEnd.notes" class="mt-2 text-xs italic text-gray-400">
        "{{ lastEnd.notes }}"
      </p>
    </div>

    <!-- ── End feed (collapsible) ─────────────────────────────────────────── -->
    <div v-if="session.ends.length > 0" class="p-4">
      <button
        class="mb-3 flex w-full items-center justify-between text-xs font-semibold uppercase tracking-widest text-gray-400 hover:text-gray-600"
        @click="showFeed = !showFeed"
      >
        All ends ({{ session.ends.length }})
        <svg
          :class="['h-3 w-3 transition-transform', showFeed ? 'rotate-180' : '']"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <LiveEndFeed v-if="showFeed" :ends="session.ends" :max-visible="6" />
    </div>

    <!-- ── Coach note input (active sessions only) ───────────────────────── -->
    <div v-if="session.status === 'active'" class="border-t border-gray-50 px-4 pb-4 pt-3">
      <div class="flex gap-2">
        <input
          v-model="coachNote"
          type="text"
          placeholder="Send a note to this archer…"
          maxlength="500"
          class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 placeholder-gray-300 focus:border-gray-400 focus:outline-none"
          @keyup.enter="sendNote"
        />
        <button
          :disabled="!coachNote.trim() || sendingNote"
          class="rounded-xl bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-700 disabled:opacity-40"
          @click="sendNote"
        >
          <span v-if="sendingNote">…</span>
          <span v-else>Send</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import LiveEndFeed from '@/Components/Live/LiveEndFeed.vue'

// ── Props ─────────────────────────────────────────────────────────────────────

/**
 * Flat card shape (matches CoachMonitorController::buildCards):
 * {
 *   id: number|null,
 *   status: 'active' | 'completed' | 'idle',
 *   archer: { id, name, category },
 *   round_type: string|null,
 *   distance_metres: number|null,
 *   arrows_per_end: number,
 *   total_score: number,
 *   x_count: number,
 *   average_per_end: number,
 *   started_at: string|null,
 *   ends: [{ id, end_number, total_score, x_count, ten_count, notes, arrows: [{ id, score }] }],
 * }
 */
const props = defineProps({
  session: { type: Object, required: true },
})

const emit = defineEmits(['note-sent'])

// ── Local state ───────────────────────────────────────────────────────────────

const showFeed    = ref(false)
const coachNote   = ref('')
const sendingNote = ref(false)

// ── Computed ──────────────────────────────────────────────────────────────────

const avgPerEnd = computed(() => {
  const count = props.session.ends.length
  return count > 0 ? (props.session.total_score / count).toFixed(1) : '—'
})

const lastEnd = computed(() => {
  const ends = props.session.ends
  return ends.length > 0 ? ends[ends.length - 1] : null
})

const sessionDuration = computed(() => {
  if (!props.session.started_at) return ''
  const mins = Math.floor((Date.now() - new Date(props.session.started_at)) / 60000)
  if (mins < 60) return `${mins}m`
  return `${Math.floor(mins / 60)}h ${mins % 60}m`
})

// ── Helpers ───────────────────────────────────────────────────────────────────

function initials(name) {
  return (name ?? '?')
    .split(' ')
    .slice(0, 2)
    .map((w) => w[0])
    .join('')
    .toUpperCase()
}

function arrowClass(score) {
  const s = String(score).toUpperCase()
  if (s === 'X')               return 'bg-yellow-400 text-yellow-900'
  if (s === '10')              return 'bg-yellow-300 text-yellow-900'
  if (s === '9' || s === '8') return 'bg-red-400 text-white'
  if (s === '7' || s === '6') return 'bg-sky-400 text-white'
  if (s === '5' || s === '4') return 'bg-sky-300 text-white'
  if (s === 'M')              return 'bg-gray-200 text-gray-400'
  return 'bg-gray-100 text-gray-700'
}

// ── Actions ───────────────────────────────────────────────────────────────────

async function sendNote() {
  if (!coachNote.value.trim() || sendingNote.value || !props.session.id) return
  sendingNote.value = true

  try {
    await axios.post(`/live/session/${props.session.id}/note`, {
      note: coachNote.value.trim(),
    })
    emit('note-sent', { sessionId: props.session.id, note: coachNote.value.trim() })
    coachNote.value = ''
  } catch (err) {
    console.error('Failed to send note:', err)
  } finally {
    sendingNote.value = false
  }
}
</script>
