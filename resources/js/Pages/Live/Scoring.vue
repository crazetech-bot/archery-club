<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Top bar -->
    <header class="sticky top-0 z-10 border-b border-gray-100 bg-white/90 backdrop-blur">
      <div class="mx-auto flex max-w-2xl items-center justify-between px-4 py-3">
        <!-- Archer + session info -->
        <div>
          <p class="text-sm font-semibold text-gray-900">{{ archer.name }}</p>
          <p class="text-xs text-gray-400">
            {{ trainingSession.round_type ?? 'Training' }}
            <span v-if="trainingSession.distance"> · {{ trainingSession.distance }}m</span>
          </p>
        </div>

        <!-- Session status badge -->
        <div class="flex items-center gap-2">
          <span
            v-if="liveSession && liveSession.status === 'active'"
            class="flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700"
          >
            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500" />
            Live
          </span>
          <span
            v-else-if="liveSession && liveSession.status === 'completed'"
            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500"
          >
            Completed
          </span>

          <!-- Complete session button -->
          <button
            v-if="liveSession && liveSession.status === 'active'"
            class="rounded-full border border-gray-200 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50"
            @click="confirmComplete"
          >
            Finish
          </button>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-2xl space-y-5 px-4 py-5">

      <!-- ── Start screen (no live session yet) ── -->
      <div v-if="!liveSession" class="flex flex-col items-center py-16 text-center">
        <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gray-900">
          <svg class="h-9 w-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke-width="1.5" />
            <circle cx="12" cy="12" r="6" stroke-width="1.5" />
            <circle cx="12" cy="12" r="2" stroke-width="1.5" />
          </svg>
        </div>
        <h2 class="mb-1 text-xl font-bold text-gray-900">Ready to score?</h2>
        <p class="mb-8 text-sm text-gray-400">
          {{ trainingSession.round_type ?? 'Training session' }}
          <span v-if="trainingSession.distance"> — {{ trainingSession.distance }}m</span>
        </p>

        <!-- Arrows per end selector -->
        <div class="mb-6 flex items-center gap-3">
          <label class="text-sm text-gray-600">Arrows per end</label>
          <div class="flex rounded-xl border border-gray-200 bg-white">
            <button
              v-for="n in [3, 6]"
              :key="n"
              :class="[
                'px-4 py-2 text-sm font-medium transition-all first:rounded-l-xl last:rounded-r-xl',
                arrowsPerEnd === n
                  ? 'bg-gray-900 text-white'
                  : 'text-gray-600 hover:bg-gray-50',
              ]"
              @click="arrowsPerEnd = n"
            >
              {{ n }}
            </button>
          </div>
        </div>

        <button
          :disabled="starting"
          class="rounded-2xl bg-gray-900 px-10 py-4 text-base font-semibold text-white shadow-lg shadow-gray-900/20 transition-all hover:bg-gray-700 active:scale-95 disabled:opacity-60"
          @click="startSession"
        >
          {{ starting ? 'Starting…' : 'Start Session' }}
        </button>
      </div>

      <!-- ── Active / Completed session ── -->
      <template v-else>

        <!-- Stats bar -->
        <SessionStats :ends="completedEnds" :arrows-per-end="arrowsPerEnd" />

        <!-- Arrow pad (only when active) -->
        <div
          v-if="liveSession.status === 'active'"
          class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100"
        >
          <div class="mb-4 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">
              End {{ completedEnds.length + 1 }}
            </p>
            <p class="text-xs text-gray-400">{{ arrowsPerEnd }} arrows</p>
          </div>

          <ArrowPad
            :arrows-per-end="arrowsPerEnd"
            :disabled="submittingEnd"
            @submit-end="handleEndSubmit"
          />
        </div>

        <!-- Completed ends list -->
        <div v-if="completedEnds.length > 0">
          <h3 class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400">
            Ends
          </h3>
          <div class="space-y-3">
            <EndCard
              v-for="end in [...completedEnds].reverse()"
              :key="end.id"
              :end="end"
            />
          </div>
        </div>

        <!-- Empty state (session active, no ends yet) -->
        <div
          v-else-if="liveSession.status === 'active'"
          class="py-8 text-center text-sm text-gray-400"
        >
          Enter your first end above.
        </div>

        <!-- Completed session export -->
        <div
          v-if="liveSession.status === 'completed'"
          class="rounded-2xl border border-gray-200 bg-white p-5 text-center"
        >
          <p class="mb-1 text-sm font-semibold text-gray-700">Session complete</p>
          <p class="mb-4 text-xs text-gray-400">
            {{ completedEnds.length }} ends · {{ totalScore }} total score
          </p>
          <button
            class="rounded-xl bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 active:scale-95"
            @click="exportPdf"
          >
            Export PDF
          </button>
        </div>
      </template>
    </main>

    <!-- Complete session confirmation modal -->
    <Transition
      enter-active-class="transition duration-150"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showCompleteModal"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center"
        @click.self="showCompleteModal = false"
      >
        <div class="w-full max-w-sm rounded-t-3xl bg-white p-6 sm:rounded-2xl">
          <h3 class="mb-1 text-base font-bold text-gray-900">Finish session?</h3>
          <p class="mb-6 text-sm text-gray-500">
            You've shot {{ completedEnds.length }} end{{ completedEnds.length !== 1 ? 's' : '' }}
            for a total of <strong>{{ totalScore }}</strong> points. This cannot be undone.
          </p>
          <div class="flex gap-3">
            <button
              class="flex-1 rounded-xl border border-gray-200 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50"
              @click="showCompleteModal = false"
            >
              Cancel
            </button>
            <button
              :disabled="completing"
              class="flex-1 rounded-xl bg-gray-900 py-3 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-60"
              @click="completeSession"
            >
              {{ completing ? 'Finishing…' : 'Yes, finish' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import SessionStats from '@/Components/Live/SessionStats.vue'
import ArrowPad from '@/Components/Live/ArrowPad.vue'
import EndCard from '@/Components/Live/EndCard.vue'

// ── Props (passed by Inertia from the controller) ────────────────────────────
const props = defineProps({
  /** The Archer model */
  archer: {
    type: Object,
    required: true,
  },
  /** The TrainingSession model */
  trainingSession: {
    type: Object,
    required: true,
  },
  /**
   * The LiveSession model if one exists for this training session.
   * null if not yet started.
   */
  liveSession: {
    type: Object,
    default: null,
  },
  /**
   * Pre-loaded completed ends with nested arrows.
   * Populated by the controller when a live session already exists.
   */
  initialEnds: {
    type: Array,
    default: () => [],
  },
})

// ── State ────────────────────────────────────────────────────────────────────
const liveSession  = ref(props.liveSession)
const completedEnds = ref(props.initialEnds)
const arrowsPerEnd  = ref(6)

const starting      = ref(false)
const submittingEnd = ref(false)
const completing    = ref(false)
const showCompleteModal = ref(false)

// ── Computed ─────────────────────────────────────────────────────────────────
const totalScore = computed(() =>
  completedEnds.value.reduce((sum, end) => sum + (end.total_score ?? 0), 0)
)

// ── Actions ──────────────────────────────────────────────────────────────────

/**
 * Start a new live session for this training session.
 */
async function startSession() {
  starting.value = true
  try {
    const { data } = await axios.post(`/live/session/${props.trainingSession.id}/start`, {
      arrows_per_end: arrowsPerEnd.value,
    })
    liveSession.value = data.live_session
  } catch (err) {
    console.error('Failed to start session:', err)
    alert('Could not start session. Please try again.')
  } finally {
    starting.value = false
  }
}

/**
 * Submit a completed end.
 * Receives the arrows array from ArrowPad (e.g. ['X', '10', '9', '8', '7', '6']).
 */
async function handleEndSubmit(arrows) {
  if (!liveSession.value) return
  submittingEnd.value = true

  try {
    const { data } = await axios.post(`/live/session/${liveSession.value.id}/end`, {
      arrows,
      end_number: completedEnds.value.length + 1,
    })
    completedEnds.value.push(data.end)
  } catch (err) {
    console.error('Failed to submit end:', err)
    alert('Could not save end. Please try again.')
  } finally {
    submittingEnd.value = false
  }
}

/**
 * Open the confirm-complete modal.
 */
function confirmComplete() {
  showCompleteModal.value = true
}

/**
 * Complete (close) the live session.
 */
async function completeSession() {
  if (!liveSession.value) return
  completing.value = true

  try {
    const { data } = await axios.patch(`/live/session/${liveSession.value.id}/complete`)
    liveSession.value = data.live_session
    showCompleteModal.value = false
  } catch (err) {
    console.error('Failed to complete session:', err)
    alert('Could not finish session. Please try again.')
  } finally {
    completing.value = false
  }
}

/**
 * Export the session as a PDF.
 * Opens the server-generated PDF in a new tab.
 */
function exportPdf() {
  if (!liveSession.value) return
  window.open(`/live/session/${liveSession.value.id}/export`, '_blank')
}
</script>
