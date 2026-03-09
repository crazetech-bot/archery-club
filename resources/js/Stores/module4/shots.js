import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useEndsStore } from '@/Stores/module4/ends.js'

export const useShotsStore = defineStore('shots', () => {

  // ── State ─────────────────────────────────────────────────────────────────────
  const shots   = ref([])
  const loading = ref(false)

  // ── Computed ──────────────────────────────────────────────────────────────────

  /** Running total across all shots in the store. */
  const totalScore = computed(() =>
    shots.value.reduce((sum, shot) => {
      if (shot.is_miss) return sum
      if (shot.is_x)   return sum + 10
      return sum + (shot.score ?? 0)
    }, 0)
  )

  /** Count of X arrows. */
  const xCount = computed(() =>
    shots.value.filter(s => s.is_x).length
  )

  /** Count of miss arrows. */
  const missCount = computed(() =>
    shots.value.filter(s => s.is_miss).length
  )

  /** Total number of arrows recorded. */
  const arrowCount = computed(() => shots.value.length)

  // ── Actions ───────────────────────────────────────────────────────────────────

  /** Hydrate from Inertia page props or fetchScorecard. */
  function setShots(newShots) {
    shots.value = [...newShots]
  }

  /**
   * POST /api/scorecards/:id/shots
   * Also appends the shot into the correct end via useEndsStore.
   */
  async function addShot(scorecardId, payload) {
    loading.value = true
    try {
      const { data } = await axios.post(
        `/api/scorecards/${scorecardId}/shots`,
        payload
      )
      shots.value.push(data)

      // Keep the ends store's nested shots array in sync.
      if (payload.scorecard_end_id) {
        useEndsStore().addShotToEnd(payload.scorecard_end_id, data)
      }

      return data
    } finally {
      loading.value = false
    }
  }

  /** PUT /api/scorecards/:id/shots/:shotId */
  async function updateShot(scorecardId, shotId, payload) {
    loading.value = true
    try {
      const { data } = await axios.put(
        `/api/scorecards/${scorecardId}/shots/${shotId}`,
        payload
      )
      const idx = shots.value.findIndex(s => s.id === shotId)
      if (idx !== -1) {
        shots.value[idx] = { ...shots.value[idx], ...data }
      }
      return data
    } finally {
      loading.value = false
    }
  }

  /** DELETE /api/scorecards/:id/shots/:shotId */
  async function deleteShot(scorecardId, shotId) {
    loading.value = true
    try {
      await axios.delete(`/api/scorecards/${scorecardId}/shots/${shotId}`)
      shots.value = shots.value.filter(s => s.id !== shotId)
    } finally {
      loading.value = false
    }
  }

  function reset() {
    shots.value  = []
    loading.value = false
  }

  // ── Expose ────────────────────────────────────────────────────────────────────
  return {
    shots,
    loading,
    totalScore,
    xCount,
    missCount,
    arrowCount,
    setShots,
    addShot,
    updateShot,
    deleteShot,
    reset,
  }
})
