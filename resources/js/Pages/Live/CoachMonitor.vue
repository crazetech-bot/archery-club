<template>
  <div class="min-h-screen bg-gray-50">

    <!-- ── Sticky header ──────────────────────────────────────────────────── -->
    <header class="sticky top-0 z-10 border-b border-gray-100 bg-white/90 backdrop-blur">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <div>
          <h1 class="text-base font-bold text-gray-900">Coach Monitor</h1>
          <p class="text-xs text-gray-400">{{ coach.name }}</p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Active session count badge -->
          <span
            v-if="activeCount > 0"
            class="flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700"
          >
            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500" />
            {{ activeCount }} live
          </span>
          <span
            v-else
            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-400"
          >
            No active sessions
          </span>

          <!-- Connection indicator -->
          <div
            :title="connected ? 'Real-time connected' : 'Polling mode (30s)'"
            :class="[
              'flex h-7 w-7 items-center justify-center rounded-full',
              connected ? 'bg-green-50' : 'bg-yellow-50',
            ]"
          >
            <svg
              :class="['h-3.5 w-3.5', connected ? 'text-green-500' : 'text-yellow-500']"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                v-if="connected"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01M3.055 11A9 9 0 0112 5a9 9 0 018.945 6M6.5 14a7.5 7.5 0 0111 0"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- Filter / search bar -->
      <div class="mx-auto max-w-6xl border-t border-gray-50 px-4 py-2">
        <div class="flex items-center gap-3">
          <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Search archer…"
              class="w-full rounded-xl border border-gray-100 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-300 focus:border-gray-300 focus:outline-none"
            />
          </div>

          <div class="flex rounded-xl border border-gray-100 bg-gray-50">
            <button
              v-for="tab in ['all', 'active', 'done', 'idle']"
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
        v-if="filteredCards.length === 0"
        class="flex flex-col items-center py-20 text-center"
      >
        <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
          <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-600">
          {{ search ? 'No archers match your search' : 'No sessions to monitor' }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
          {{ search ? 'Try a different name.' : 'Archers will appear here when they start a live session.' }}
        </p>
      </div>

      <!-- Session cards grid -->
      <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
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
            v-for="card in filteredCards"
            :key="card.archer.id"
            :session="card"
            @note-sent="handleNoteSent"
          />
        </TransitionGroup>
      </div>
    </main>

    <!-- Toast -->
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

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps({
  coach:       { type: Object, required: true },
  archerCards: { type: Array,  default: () => [] },
  tenantId:    { type: [String, Number], required: true },
})

// ── State ─────────────────────────────────────────────────────────────────────

const cards     = ref(props.archerCards)
const search    = ref('')
const filter    = ref('all')
const connected = ref(false)
const toast     = ref(null)

let pollInterval = null
let echoChannel  = null
let toastTimeout = null

// ── Computed ──────────────────────────────────────────────────────────────────

const activeCount = computed(() => cards.value.filter((c) => c.status === 'active').length)

const filteredCards = computed(() => {
  let list = cards.value

  if (filter.value === 'active') list = list.filter((c) => c.status === 'active')
  if (filter.value === 'done')   list = list.filter((c) => c.status === 'completed')
  if (filter.value === 'idle')   list = list.filter((c) => c.status === 'idle')

  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter((c) => c.archer.name.toLowerCase().includes(q))
  }

  // Active first, then completed, then idle
  const order = { active: 0, completed: 1, idle: 2 }
  return [...list].sort((a, b) => (order[a.status] ?? 2) - (order[b.status] ?? 2))
})

// ── Real-time: Laravel Echo ───────────────────────────────────────────────────

function subscribeEcho() {
  if (typeof window.Echo === 'undefined') {
    startPolling()
    return
  }

  echoChannel = window.Echo
    .private(`tenant.${props.tenantId}.live`)

    // Archer started a new session — add card or update existing
    .listen('.live.session.started', ({ session }) => {
      const idx = cards.value.findIndex((c) => c.id === session.id)
      if (idx !== -1) {
        cards.value[idx] = session
      } else {
        cards.value.push(session)
      }
      showToast(`${session.archer.name} started a session`)
    })

    // An end was submitted — push into the matching card
    .listen('.live.end.submitted', ({ session_id, end }) => {
      const card = cards.value.find((c) => c.id === session_id)
      if (card) {
        card.ends.push(end)
        // Recalculate running totals client-side
        card.total_score += end.total_score
        card.x_count     += end.x_count
        const endCount = card.ends.length
        card.average_per_end = endCount > 0 ? +(card.total_score / endCount).toFixed(1) : 0
      }
    })

    // Session completed — update status on the card
    .listen('.live.session.completed', ({ session_id }) => {
      const card = cards.value.find((c) => c.id === session_id)
      if (card) {
        card.status = 'completed'
        showToast(`${card.archer.name} finished their session`)
      }
    })

    .subscribed(() => { connected.value = true })
    .error(() => {
      connected.value = false
      startPolling()
    })
}

// ── Polling fallback ──────────────────────────────────────────────────────────

function startPolling() {
  if (pollInterval) return
  pollInterval = setInterval(fetchCards, 30_000)
}

async function fetchCards() {
  try {
    const { data } = await axios.get('/live/coach/sessions')
    cards.value = data.archerCards
  } catch (err) {
    console.error('Polling failed:', err)
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function handleNoteSent() {
  showToast('Note sent')
}

function showToast(message) {
  clearTimeout(toastTimeout)
  toast.value = message
  toastTimeout = setTimeout(() => { toast.value = null }, 3000)
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(() => subscribeEcho())

onUnmounted(() => {
  if (echoChannel) window.Echo?.leave(`tenant.${props.tenantId}.live`)
  if (pollInterval) clearInterval(pollInterval)
  clearTimeout(toastTimeout)
})
</script>
