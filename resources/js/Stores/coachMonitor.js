import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCoachMonitorStore = defineStore('coachMonitor', () => {
  // ── State ────────────────────────────────────────────────────────────────────

  const archerSessions = ref([])  // Array of { archer, live_session, ends }
  const connected      = ref(false)
  const lastUpdated    = ref(null)

  // ── Computed ─────────────────────────────────────────────────────────────────

  const activeSessions = computed(() =>
    archerSessions.value.filter((s) => s.live_session?.status === 'active')
  )

  const activeCount = computed(() => activeSessions.value.length)

  // ── Actions ──────────────────────────────────────────────────────────────────

  function hydrate(sessions) {
    archerSessions.value = sessions ?? []
    lastUpdated.value    = Date.now()
  }

  function setConnected(value) {
    connected.value = value
  }

  /**
   * Handle a live.session.started event from Echo.
   */
  function handleSessionStarted(event) {
    const existing = archerSessions.value.find(
      (s) => s.archer?.id === event.archer_id
    )
    if (existing) {
      existing.live_session = event.live_session
      existing.ends         = []
    } else {
      archerSessions.value.push({
        archer:       event.archer,
        live_session: event.live_session,
        ends:         [],
      })
    }
    lastUpdated.value = Date.now()
  }

  /**
   * Handle a live.end.submitted event from Echo.
   */
  function handleEndSubmitted(event) {
    const session = archerSessions.value.find(
      (s) => s.live_session?.id === event.live_session_id
    )
    if (session) {
      session.ends = session.ends ?? []
      const idx = session.ends.findIndex((e) => e.id === event.end?.id)
      if (idx !== -1) {
        session.ends[idx] = event.end
      } else {
        session.ends.push(event.end)
      }
      if (session.live_session) {
        session.live_session.total_score  = event.total_score
        session.live_session.x_count      = event.x_count
      }
    }
    lastUpdated.value = Date.now()
  }

  /**
   * Handle a live.session.completed event from Echo.
   */
  function handleSessionCompleted(event) {
    const session = archerSessions.value.find(
      (s) => s.live_session?.id === event.session_id
    )
    if (session?.live_session) {
      session.live_session.status     = 'completed'
      session.live_session.ended_at   = event.ended_at
    }
    lastUpdated.value = Date.now()
  }

  function reset() {
    archerSessions.value = []
    connected.value      = false
    lastUpdated.value    = null
  }

  return {
    archerSessions,
    connected,
    lastUpdated,
    activeSessions,
    activeCount,
    hydrate,
    setConnected,
    handleSessionStarted,
    handleEndSubmitted,
    handleSessionCompleted,
    reset,
  }
})
