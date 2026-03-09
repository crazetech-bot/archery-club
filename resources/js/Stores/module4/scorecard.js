import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { scorecardsApi } from '@/api/module4/scorecards.js'
import { useEndsStore } from './ends'
import { useShotsStore } from './shots'
import { useMetricsStore } from './metrics'

export const useScorecardStore = defineStore('scorecard', () => {

  // ── State ─────────────────────────────────────────────────────────────────────
  const scorecard     = ref(null)
  const scorecardList = ref([])
  const loading       = ref(false)

  // ── Computed ──────────────────────────────────────────────────────────────────
  const isEditable  = computed(() => scorecard.value?.status === 'draft')
  const totalScore  = computed(() => scorecard.value?.total_score  ?? 0)
  const xCount      = computed(() => scorecard.value?.x_count      ?? 0)
  const arrowCount  = computed(() => scorecard.value?.arrow_count  ?? 0)

  // ── Actions ───────────────────────────────────────────────────────────────────

  /**
   * Fetch a full scorecard and hydrate all child stores.
   */
  async function fetchScorecard(id) {
    loading.value = true
    try {
      const { data } = await axios.get(`/api/scorecards/${id}`)
      scorecard.value = data
      useEndsStore().setEnds(data.ends    ?? [])
      useShotsStore().setShots(data.shots ?? [])
      useMetricsStore().setMetrics(data.metrics ?? null)
      return data
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a new scorecard. Returns the created model.
   */
  async function create(payload) {
    const { data } = await axios.post('/api/scorecards', payload)
    return data
  }

  /**
   * Transition status → submitted.
   * Merges the returned model into local state (avoids a full re-fetch).
   */
  async function submit(id) {
    const { data } = await axios.post(`/api/scorecards/${id}/submit`)
    if (scorecard.value) {
      scorecard.value = { ...scorecard.value, ...data }
    }
    return data
  }

  /**
   * Transition status → locked.
   */
  async function lock(id) {
    const { data } = await axios.post(`/api/scorecards/${id}/lock`)
    if (scorecard.value) {
      scorecard.value = { ...scorecard.value, ...data }
    }
    return data
  }

  /**
   * Lightweight local sync of derived totals after shots are recorded —
   * avoids a full re-fetch on every arrow.
   */
  function syncTotals({ totalScore: ts, xCount: xc, arrowCount: ac }) {
    if (!scorecard.value) return
    scorecard.value = {
      ...scorecard.value,
      total_score:  ts ?? scorecard.value.total_score,
      x_count:      xc ?? scorecard.value.x_count,
      arrow_count:  ac ?? scorecard.value.arrow_count,
    }
  }

  /**
   * Fetch the paginated scorecard list.
   * Uses scorecardsApi.list() so URLs stay in the API client layer.
   * Handles both a Laravel paginator ({ data: [] }) and a flat array.
   */
  async function fetchList() {
    loading.value = true
    try {
      const { data } = await scorecardsApi.list()
      scorecardList.value = data.data ?? data
      return scorecardList.value
    } finally {
      loading.value = false
    }
  }

  function reset() {
    scorecard.value     = null
    scorecardList.value = []
    loading.value       = false
  }

  // ── Expose ────────────────────────────────────────────────────────────────────
  return {
    scorecard,
    scorecardList,
    loading,
    isEditable,
    totalScore,
    xCount,
    arrowCount,
    fetchList,
    fetchScorecard,
    create,
    submit,
    lock,
    syncTotals,
    reset,
  }
})
