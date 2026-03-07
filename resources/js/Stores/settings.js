import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

const STORAGE_KEY = 'archery_settings'

function loadFromStorage() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : {}
  } catch {
    return {}
  }
}

export const useSettingsStore = defineStore('settings', () => {
  const saved = loadFromStorage()

  // ── Preferences ───────────────────────────────────────────────────────────────

  /** Score input mode: 'pad' = ArrowPad buttons, 'keyboard' = numeric keyboard */
  const scoreInputMode = ref(saved.scoreInputMode ?? 'pad')

  /** Show end notes input after each end */
  const showEndNotes = ref(saved.showEndNotes ?? false)

  /** Auto-advance to next end after submitting */
  const autoAdvance = ref(saved.autoAdvance ?? true)

  /** Default arrows per end */
  const defaultArrowsPerEnd = ref(saved.defaultArrowsPerEnd ?? 6)

  /** Preferred report date range in days */
  const reportDateRange = ref(saved.reportDateRange ?? 90)

  /** Chart theme: 'default' | 'high-contrast' */
  const chartTheme = ref(saved.chartTheme ?? 'default')

  // ── Persist on change ─────────────────────────────────────────────────────────

  watch(
    [scoreInputMode, showEndNotes, autoAdvance, defaultArrowsPerEnd, reportDateRange, chartTheme],
    () => {
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
          scoreInputMode:     scoreInputMode.value,
          showEndNotes:       showEndNotes.value,
          autoAdvance:        autoAdvance.value,
          defaultArrowsPerEnd: defaultArrowsPerEnd.value,
          reportDateRange:    reportDateRange.value,
          chartTheme:         chartTheme.value,
        }))
      } catch {}
    },
    { deep: true }
  )

  function reset() {
    scoreInputMode.value      = 'pad'
    showEndNotes.value        = false
    autoAdvance.value         = true
    defaultArrowsPerEnd.value = 6
    reportDateRange.value     = 90
    chartTheme.value          = 'default'
  }

  return {
    scoreInputMode,
    showEndNotes,
    autoAdvance,
    defaultArrowsPerEnd,
    reportDateRange,
    chartTheme,
    reset,
  }
})
