import { numericScore } from './formatScore.js'

/**
 * Calculate the mean of an array of numbers.
 * @param {number[]} values
 * @returns {number|null}
 */
export function mean(values) {
  const nums = values.filter((v) => v != null && !isNaN(v))
  if (nums.length === 0) return null
  return nums.reduce((s, v) => s + v, 0) / nums.length
}

/**
 * Calculate average score per end from an array of end objects.
 * @param {{ total_score: number }[]} ends
 * @returns {number|null}
 */
export function avgPerEnd(ends) {
  return mean(ends.map((e) => e.total_score))
}

/**
 * Calculate average end score for a given number of arrow scores.
 * @param {string[]} arrows - Array of score strings ('X','10','9'…'M')
 * @returns {number}
 */
export function endTotal(arrows) {
  return arrows.reduce((sum, s) => sum + numericScore(s), 0)
}

/**
 * Count X's in an array of arrow score strings.
 * @param {string[]} arrows
 */
export function countX(arrows) {
  return arrows.filter((s) => s === 'X').length
}

/**
 * Count 10s (including X) in an array of arrow score strings.
 * @param {string[]} arrows
 */
export function countTens(arrows) {
  return arrows.filter((s) => s === 'X' || s === '10').length
}

/**
 * Count misses in an array of arrow score strings.
 * @param {string[]} arrows
 */
export function countMisses(arrows) {
  return arrows.filter((s) => s === 'M').length
}

/**
 * Calculate a simple N-point moving average over a numeric series.
 * @param {number[]} series
 * @param {number} window - window size
 * @returns {(number|null)[]}
 */
export function movingAverage(series, window = 3) {
  return series.map((_, idx) => {
    if (idx < window - 1) return null
    const slice = series.slice(idx - window + 1, idx + 1)
    return mean(slice)
  })
}

/**
 * Calculate improvement rate as percentage change between two score windows.
 * Compares the mean of the first `window` sessions to the mean of the last `window`.
 * @param {number[]} scores - Chronological score array
 * @param {number} window
 * @returns {number|null} - Percentage change (positive = improvement)
 */
export function improvementRate(scores, window = 3) {
  if (scores.length < window * 2) return null
  const first = mean(scores.slice(0, window))
  const last  = mean(scores.slice(-window))
  if (!first || first === 0) return null
  return +((((last - first) / first) * 100).toFixed(1))
}
