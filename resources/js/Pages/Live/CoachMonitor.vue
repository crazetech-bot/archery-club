<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Header -->
    <header class="sticky top-0 z-10 border-b border-gray-100 bg-white/90 backdrop-blur">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <div>
          <h1 class="text-base font-bold text-gray-900">Coach Monitor</h1>
          <p class="text-xs text-gray-400">{{ coach.name }}</p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Active session count -->
          <span
            v-if="activeSessions.length > 0"
            class="flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700"
          >
            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500" />
            {{ activeSessions.length }} active
          </span>
          <span
            v-else
            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-400"
          >
            No active sessions
          </span>

          <!-- Connection indicator -->
          <div
            :title="connected ? 'Real-time connected' : 'Polling mode'"
            :class="[
              'flex h-7 w-7 items-center justify-center rounded-full',
              connected ? 'bg-green-50' : 'bg-yellow-50',
            ]"
          >
            <svg
              :class="['h-3.5 w-3.5', connected ? 'text-green-500' : 'text-yellow-500']"
              fill="currentColor"
              viewBox="0 0 24 24"
            >
              <path v-if="connected" d="M1.5 8.5a13 13 0 0121 0M5 12a10 10 0 0114 0M8.5 15.5a6 6 0 017 0M12 19h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none" />
              <path v-else d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Search / filter bar -->
      <div class="mx-auto max-w-6xl border-t border-gray-50 px-4 py-2">
        <div class="flex items-center gap-3">
          <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Search archer…"
              class="w-full rounded-xl border border-gray-100 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-300 focus:border-gray-300 focus:outline-none"
            />
          </div>

          <!-- Filter: active / all -->
          <div class="flex rounded-xl border border-gray-100 bg-gray-50">
            <button
              v-for="tab in ['all', 'active', 'done']"
              :key="tab"
              :class="[
                'px-3 py-1.5 text-xs font-medium capitalize transition-all first:rounded-l-xl last:rounded-r-xl',
                filter === tab ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-800',
              ]"
              @click="filter = tab"
            >
              {{ tab }}
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-5">

      <!-- Empty state -->
      <div
        v-if="filteredSessions.length === 0"
        class="flex flex-col items-center py-20 text-center"
      >
        <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
          <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-600">
          {{ search ? 'No archers match your search' : 'No sessions to monitor' }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
          {{ search ? 'Try a different name.' : 'Archers will appear here when they start a live session.' }}
        </p>
      </div>

      <!-- Session grid -->
      <div
        v-else
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
      >
        <TransitionGroup
          tag="div"
          class="contents"
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <ArcherSessionCard
            v-for="session in filteredSessions"
            :key="session.id"
            :session="session"
            @note-sent="handleNoteSent"
          />
        </TransitionGroup>
      </div>
    </main>

    <!-- Toast notification -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-2"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div
        v-if="toast"
        class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-2xl bg-gray-900 px-5 py-3 text-sm font-medium text-white shadow-lg"
      >
        {{ toast }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import ArcherSessionCard from '@/Components/Live/ArcherSessionCard.vue'

// ── Props (from Inertia controller) ──────────────────────────────────────────
const props = defineProps({
  /** Authenticated coach */
  coach: {
    type: Object,
    required: true,
  },
  /**
   * Initial list of live sessions for archers assigned to this coach.
   * Each session includes: archer, training_session, ends (with arrows), status, started_at.
   */
  initialSessions: {
    type: Array,
    default: () => [],
  },
  /** Current tenant ID for scoped Echo channel */
  tenantId: {
    type: [String, Number],
    required: true,
  },
})

// ── State ─────────────────────────────────────────────────────────────────────
const sessions  = ref(props.initialSessions)
const search    = ref('')
const filter    = ref('all')
const connected = ref(false)
const toast     = ref(null)

let pollInterval  = null
let echoChannel   = null
let toastTimeout  = null

// ── Computed ──────────────────────────────────────────────────────────────────

const activeSessions = computed(() =>
  sessions.value.filter((s) => s.status === 'active')
)

const filteredSessions = computed(() => {
  let list = sessions.value

  if (filter.value === 'active') list = list.filter((s) => s.status === 'active')
  if (filter.value === 'done')   list = list.filter((s) => s.status === 'completed')

  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter((s) => s.archer.name.toLowerCase().includes(q))
  }

  // Active sessions first
  return [...list].sort((a, b) => {
    if (a.status === b.status) return 0
    return a.status === 'active' ? -1 : 1
  })
})

// ── Real-time: Laravel Echo ───────────────────────────────────────────────────

function subscribeEcho() {
  if (typeof window.Echo === 'undefined') {
    // Echo not available — fall back to polling
    startPolling()
    return
  }

  echoChannel = window.Echo
    .private(`tenant.${props.tenantId}.live`)

    // A new live session was started by an archer
    .listen('.live.session.started', (data) => {
      const exists = sessions.value.find((s) => s.id === data.session.id)
      if (!exists) sessions.value.push(data.session)
      showToast(`${data.session.archer.name} started a session`)
    })

    // An end was submitted (archer scored)
    .listen('.live.end.submitted', (data) => {
      const session = sessions.value.find((s) => s.id === data.session_id)
      if (session) {
        session.ends.push(data.end)
      }
    })

    // A session was completed
    .listen('.live.session.completed', (data) => {
      const session = sessions.value.find((s) => s.id === data.session_id)
      if (session) {
        session.status = 'completed'
        showToast(`${session.archer.name} finished their session`)
      }
    })

    .subscribed(() => {
      connected.value = true
    })
    .error(() => {
      connected.value = false
      startPolling()
    })
}

// ── Polling fallback (30s) ────────────────────────────────────────────────────

function startPolling() {
  if (pollInterval) return
  pollInterval = setInterval(fetchSessions, 30_000)
}

async function fetchSessions() {
  try {
    const { data } = await axios.get('/live/coach/sessions')
    sessions.value = data.sessions
  } catch (err) {
    console.error('Polling failed:', err)
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function handleNoteSent({ sessionId, note }) {
  showToast('Note sent')
}

function showToast(message) {
  clearTimeout(toastTimeout)
  toast.value = message
  toastTimeout = setTimeout(() => {
    toast.value = null
  }, 3000)
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(() => {
  subscribeEcho()
})

onUnmounted(() => {
  if (echoChannel) window.Echo?.leave(`tenant.${props.tenantId}.live`)
  if (pollInterval) clearInterval(pollInterval)
  clearTimeout(toastTimeout)
})
</script>
