<template>
  <div
    :class="[
      'flex flex-col rounded-2xl bg-white shadow-sm ring-1 transition-all duration-300',
      session.status === 'active' ? 'ring-green-200' : 'ring-gray-100',
    ]"
  >
    <!-- Card header -->
    <div class="flex items-start justify-between border-b border-gray-50 p-4">
      <div class="flex items-center gap-3">
        <!-- Avatar -->
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-900 text-sm font-bold text-white">
          {{ initials(session.archer.name) }}
        </div>

        <div>
          <p class="font-semibold text-gray-900">{{ session.archer.name }}</p>
          <p class="text-xs text-gray-400">
            {{ session.training_session.round_type ?? 'Training' }}
            <span v-if="session.training_session.distance">
              · {{ session.training_session.distance }}m
            </span>
          </p>
        </div>
      </div>

      <!-- Status pill -->
      <div class="flex flex-col items-end gap-1">
        <span
          v-if="session.status === 'active'"
          class="flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700"
        >
          <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500" />
          Live
        </span>
        <span
          v-else
          class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500"
        >
          Done
        </span>
        <span class="text-xs text-gray-400">{{ sessionDuration }}</span>
      </div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-4 divide-x divide-gray-50 border-b border-gray-50">
      <div class="px-3 py-3 text-center">
        <p class="text-xs text-gray-400">Total</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ totalScore }}</p>
      </div>
      <div class="px-3 py-3 text-center">
        <p class="text-xs text-gray-400">Ends</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ session.ends.length }}</p>
      </div>
      <div class="px-3 py-3 text-center">
        <p class="text-xs text-yellow-500">X</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums text-yellow-500">{{ totalX }}</p>
      </div>
      <div class="px-3 py-3 text-center">
        <p class="text-xs text-gray-400">Avg</p>
        <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ avgPerEnd }}</p>
      </div>
    </div>

    <!-- Last end highlight (most recent completed end) -->
    <div v-if="lastEnd" class="border-b border-gray-50 px-4 py-3">
      <p class="mb-2 text-xs font-medium text-gray-400">Last end</p>
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
    </div>

    <!-- End feed (collapsed by default, expandable) -->
    <div class="p-4">
      <button
        class="mb-3 flex w-full items-center justify-between text-xs font-semibold uppercase tracking-widest text-gray-400 hover:text-gray-600"
        @click="showFeed = !showFeed"
      >
        All ends
        <svg
          :class="['h-3 w-3 transition-transform', showFeed ? 'rotate-180' : '']"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <LiveEndFeed v-if="showFeed" :ends="session.ends" :max-visible="6" />
    </div>

    <!-- Coach note input -->
    <div v-if="session.status === 'active'" class="border-t border-gray-50 px-4 pb-4">
      <div class="flex gap-2">
        <input
          v-model="coachNote"
          type="text"
          placeholder="Add a note for this archer…"
          class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 placeholder-gray-300 focus:border-gray-400 focus:outline-none"
          @keyup.enter="sendNote"
        />
        <button
          :disabled="!coachNote.trim() || sendingNote"
          class="rounded-xl bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-700 disabled:opacity-40"
          @click="sendNote"
        >
          Send
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import LiveEndFeed from '@/Components/Live/LiveEndFeed.vue'

const props = defineProps({
  /**
   * A live session object with nested:
   *  - archer: { id, name }
   *  - training_session: { round_type, distance }
   *  - ends: LiveEnd[] (each with arrows[])
   *  - status: 'active' | 'completed'
   *  - started_at: ISO string
   */
  session: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['note-sent'])

const showFeed   = ref(false)
const coachNote  = ref('')
const sendingNote = ref(false)

// ── Computed ─────────────────────────────────────────────────────────────────

const totalScore = computed(() =>
  props.session.ends.reduce((sum, end) => sum + (end.total_score ?? 0), 0)
)

const totalX = computed(() =>
  props.session.ends.reduce((sum, end) => sum + (end.x_count ?? 0), 0)
)

const avgPerEnd = computed(() => {
  const count = props.session.ends.length
  return count > 0 ? (totalScore.value / count).toFixed(1) : '—'
})

const lastEnd = computed(() => {
  const ends = props.session.ends
  return ends.length > 0 ? ends[ends.length - 1] : null
})

const sessionDuration = computed(() => {
  const start = new Date(props.session.started_at)
  const now   = new Date()
  const mins  = Math.floor((now - start) / 60000)
  if (mins < 60) return `${mins}m`
  return `${Math.floor(mins / 60)}h ${mins % 60}m`
})

// ── Helpers ───────────────────────────────────────────────────────────────────

function initials(name) {
  return name
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
  if (s === 'M')              return 'bg-gray-200 text-gray-400'
  return 'bg-gray-100 text-gray-700'
}

// ── Actions ───────────────────────────────────────────────────────────────────

async function sendNote() {
  if (!coachNote.value.trim() || sendingNote.value) return
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
