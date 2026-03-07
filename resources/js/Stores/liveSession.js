import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { numericScore } from '@/Utils/formatScore.js'

export const useLiveSessionStore = defineStore('liveSession', () => {
  // ── State ────────────────────────────────────────────────────────────────────

  const sessionId     = ref(null)
  const arrowsPerEnd  = ref(6)
  const status        = ref('pending') // 'pending' | 'active' | 'completed'
  const ends          = ref([])

  // ── Computed ─────────────────────────────────────────────────────────────────

  const isActive    = computed(() => status.value === 'active')
  const isCompleted = computed(() => status.value === 'completed')
  const isPending   = computed(() => status.value === 'pending')

  const totalScore = computed(() =>
    ends.value.reduce((sum, end) => sum + (end.total_score ?? 0), 0)
  )

  const xCount = computed(() =>
    ends.value.reduce((sum, end) => sum + (end.x_count ?? 0), 0)
  )

  const tenCount = computed(() =>
    ends.value.reduce((sum, end) => sum + (end.ten_count ?? 0), 0)
  )

  const averagePerEnd = computed(() => {
    if (ends.value.length === 0) return null
    return +(totalScore.value / ends.value.length).toFixed(1)
  })

  const nextEndNumber = computed(() => ends.value.length + 1)

  const lastEnd = computed(() =>
    ends.value.length > 0 ? ends.value[ends.value.length - 1] : null
  )

  // ── Actions ──────────────────────────────────────────────────────────────────

  function hydrate(liveSession) {
    if (!liveSession) return
    sessionId.value    = liveSession.id
    arrowsPerEnd.value = liveSession.arrows_per_end ?? 6
    status.value       = liveSession.status ?? 'active'
    ends.value         = liveSession.ends ?? []
  }

  function addEnd(end) {
    ends.value.push(end)
  }

  function setStatus(newStatus) {
    status.value = newStatus
  }

  function reset() {
    sessionId.value    = null
    arrowsPerEnd.value = 6
    status.value       = 'pending'
    ends.value         = []
  }

  return {
    sessionId,
    arrowsPerEnd,
    status,
    ends,
    isActive,
    isCompleted,
    isPending,
    totalScore,
    xCount,
    tenCount,
    averagePerEnd,
    nextEndNumber,
    lastEnd,
    hydrate,
    addEnd,
    setStatus,
    reset,
  }
})
