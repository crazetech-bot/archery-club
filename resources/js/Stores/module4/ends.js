import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useEndsStore = defineStore('ends', () => {

  // ── State ─────────────────────────────────────────────────────────────────────
  const ends    = ref([])
  const loading = ref(false)

  // ── Computed ──────────────────────────────────────────────────────────────────

  /** The end_number to use when adding the next end. */
  const nextEndNumber = computed(() => ends.value.length + 1)

  /** Running total across all ends (derived from nested shots). */
  const totalScore = computed(() =>
    ends.value.reduce((sum, end) => {
      return sum + (end.shots ?? []).reduce((s, shot) => {
        if (shot.is_miss) return s
        if (shot.is_x)   return s + 10
        return s + (shot.score ?? 0)
      }, 0)
    }, 0)
  )

  // ── Actions ───────────────────────────────────────────────────────────────────

  /** Hydrate from Inertia page props or fetchScorecard. */
  function setEnds(newEnds) {
    ends.value = newEnds.map(e => ({ ...e, shots: [...(e.shots ?? [])] }))
  }

  /** POST /api/scorecards/:id/ends */
  async function addEnd(scorecardId, endNumber) {
    const { data } = await axios.post(`/api/scorecards/${scorecardId}/ends`, {
      end_number: endNumber,
    })
    const newEnd = { ...data, shots: [] }
    ends.value.push(newEnd)
    return newEnd
  }

  /** PUT /api/scorecards/:id/ends/:endId */
  async function updateEnd(scorecardId, endId, payload) {
    const { data } = await axios.put(
      `/api/scorecards/${scorecardId}/ends/${endId}`,
      payload
    )
    const idx = ends.value.findIndex(e => e.id === endId)
    if (idx !== -1) {
      ends.value[idx] = { ...ends.value[idx], ...data }
    }
    return data
  }

  /** DELETE /api/scorecards/:id/ends/:endId — also purges nested shots. */
  async function deleteEnd(scorecardId, endId) {
    await axios.delete(`/api/scorecards/${scorecardId}/ends/${endId}`)
    ends.value = ends.value.filter(e => e.id !== endId)
  }

  /**
   * Append a newly recorded shot into the correct end's shots array.
   * Called by useShotsStore after addShot() resolves.
   */
  function addShotToEnd(endId, shot) {
    const end = ends.value.find(e => e.id === endId)
    if (end) {
      end.shots = [...(end.shots ?? []), shot]
    }
  }

  function reset() {
    ends.value  = []
    loading.value = false
  }

  // ── Expose ────────────────────────────────────────────────────────────────────
  return {
    ends,
    loading,
    nextEndNumber,
    totalScore,
    setEnds,
    addEnd,
    updateEnd,
    deleteEnd,
    addShotToEnd,
    reset,
  }
})
