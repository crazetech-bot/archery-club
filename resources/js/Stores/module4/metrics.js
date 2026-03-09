import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useMetricsStore = defineStore('metrics', () => {

  // ── State ─────────────────────────────────────────────────────────────────────
  const metrics = ref(null)
  const loading = ref(false)

  // ── Computed ──────────────────────────────────────────────────────────────────

  /** True when at least one recalculation has been run. */
  const hasMetrics = computed(() => metrics.value !== null)

  /** Convenience accessors — return null when no metrics exist yet. */
  const averageArrowScore = computed(() => metrics.value?.average_arrow_score ?? null)
  const hitRate           = computed(() => metrics.value?.hit_rate           ?? null)
  const xCount            = computed(() => metrics.value?.x_count            ?? null)
  const missCount         = computed(() => metrics.value?.miss_count          ?? null)

  // ── Actions ───────────────────────────────────────────────────────────────────

  /** Hydrate from Inertia page props or fetchScorecard. */
  function setMetrics(newMetrics) {
    metrics.value = newMetrics ?? null
  }

  /**
   * POST /api/scorecards/:id/metrics/recalculate
   * Returns the fresh ScorecardMetric resource.
   */
  async function recalculate(scorecardId) {
    loading.value = true
    try {
      const { data } = await axios.post(
        `/api/scorecards/${scorecardId}/metrics/recalculate`
      )
      metrics.value = data
      return data
    } finally {
      loading.value = false
    }
  }

  function reset() {
    metrics.value = null
    loading.value = false
  }

  // ── Expose ────────────────────────────────────────────────────────────────────
  return {
    metrics,
    loading,
    hasMetrics,
    averageArrowScore,
    hitRate,
    xCount,
    missCount,
    setMetrics,
    recalculate,
    reset,
  }
})
