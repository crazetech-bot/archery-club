import { numericScore, arrowBadgeColor } from './formatScore.js'
import { countX, countTens, countMisses, endTotal } from './calcAverages.js'

/**
 * Derive display stats for a single end's arrow array.
 * @param {string[]} arrows - Score strings, e.g. ['X','9','8','7','6','M']
 * @returns {{
 *   total: number,
 *   xCount: number,
 *   tenCount: number,
 *   missCount: number,
 *   highest: number,
 *   lowest: number,
 *   arrows: { score: string, numeric: number, color: string }[]
 * }}
 */
export function calcEndStats(arrows) {
  const enriched = arrows.map((score) => ({
    score,
    numeric: numericScore(score),
    color:   arrowBadgeColor(score),
  }))

  const numerics = enriched.map((a) => a.numeric)

  return {
    total:     endTotal(arrows),
    xCount:    countX(arrows),
    tenCount:  countTens(arrows),
    missCount: countMisses(arrows),
    highest:   numerics.length ? Math.max(...numerics) : 0,
    lowest:    numerics.length ? Math.min(...numerics) : 0,
    arrows:    enriched,
  }
}

/**
 * Build a frequency distribution of scores for an array of ends.
 * @param {{ arrows: { score: string }[] }[]} ends
 * @returns {Record<string, number>} e.g. { X: 5, '10': 12, '9': 8, … }
 */
export function scoreFrequency(ends) {
  const freq = {}
  for (const end of ends) {
    for (const arrow of end.arrows ?? []) {
      const s = arrow.score ?? arrow
      freq[s] = (freq[s] ?? 0) + 1
    }
  }
  return freq
}

/**
 * Calculate end-by-end cumulative totals.
 * @param {{ total_score: number }[]} ends
 * @returns {number[]}
 */
export function cumulativeTotals(ends) {
  let running = 0
  return ends.map((e) => {
    running += e.total_score ?? 0
    return running
  })
}
