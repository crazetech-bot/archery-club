import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Encapsulates live scoring state and API interactions for an archer's session.
 * @param {number} sessionId - The training_session ID
 * @param {number} liveSessionId - The live_session ID (null if not started)
 */
export function useLiveScoring(sessionId, liveSessionId) {
  // ── State ────────────────────────────────────────────────────────────────────

  const ends        = ref([])
  const isStarted   = ref(!!liveSessionId)
  const isCompleted = ref(false)
  const loading     = ref(false)
  const error       = ref(null)

  const currentLiveSessionId = ref(liveSessionId)

  // ── Computed ─────────────────────────────────────────────────────────────────

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

  // ── Actions ──────────────────────────────────────────────────────────────────

  async function startSession(arrowsPerEnd) {
    loading.value = true
    error.value   = null

    return new Promise((resolve, reject) => {
      router.post(
        `/live/session/${sessionId}/start`,
        { arrows_per_end: arrowsPerEnd },
        {
          preserveState: true,
          preserveScroll: true,
          only: ['liveSession'],
          onSuccess: (page) => {
            isStarted.value = true
            currentLiveSessionId.value = page.props.liveSession?.id
            resolve(page.props.liveSession)
          },
          onError: (err) => { error.value = err; reject(err) },
          onFinish: () => { loading.value = false },
        }
      )
    })
  }

  async function submitEnd(arrows) {
    if (!currentLiveSessionId.value) return
    loading.value = true
    error.value   = null

    return new Promise((resolve, reject) => {
      router.post(
        `/live/session/${currentLiveSessionId.value}/end`,
        { arrows, end_number: nextEndNumber.value },
        {
          preserveState: true,
          preserveScroll: true,
          only: ['liveSession'],
          onSuccess: (page) => {
            // Expect the controller to push the new end into liveSession.ends
            const liveSession = page.props.liveSession
            if (liveSession?.ends) ends.value = liveSession.ends
            resolve(liveSession)
          },
          onError: (err) => { error.value = err; reject(err) },
          onFinish: () => { loading.value = false },
        }
      )
    })
  }

  async function completeSession() {
    if (!currentLiveSessionId.value) return
    loading.value = true
    error.value   = null

    return new Promise((resolve, reject) => {
      router.patch(
        `/live/session/${currentLiveSessionId.value}/complete`,
        {},
        {
          preserveState: true,
          preserveScroll: true,
          onSuccess: (page) => {
            isCompleted.value = true
            resolve(page)
          },
          onError: (err) => { error.value = err; reject(err) },
          onFinish: () => { loading.value = false },
        }
      )
    })
  }

  function hydrateEnds(initialEnds) {
    ends.value = initialEnds ?? []
  }

  return {
    ends,
    isStarted,
    isCompleted,
    loading,
    error,
    totalScore,
    xCount,
    tenCount,
    averagePerEnd,
    nextEndNumber,
    startSession,
    submitEnd,
    completeSession,
    hydrateEnds,
  }
}
