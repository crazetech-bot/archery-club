/**
 * Score formatting and display utilities.
 */

/**
 * Convert a raw arrow score string to its numeric value.
 * X = 10, M = 0, otherwise parse int.
 * @param {string} score
 * @returns {number}
 */
export function numericScore(score) {
  if (score === 'X') return 10
  if (score === 'M') return 0
  return parseInt(score, 10) || 0
}

/**
 * Format a score for display (null/undefined → '—').
 * @param {number|null|undefined} score
 */
export function displayScore(score) {
  return score != null ? String(score) : '—'
}

/**
 * Return Tailwind classes for a score circle background based on percentage of max.
 * @param {number|null} score
 * @param {number|null} max
 */
export function scoreCircleColor(score, max) {
  if (score == null || max == null || max === 0) return 'bg-gray-200 text-gray-500'
  const pct = score / max
  if (pct >= 0.90) return 'bg-green-600 text-white'
  if (pct >= 0.80) return 'bg-green-500 text-white'
  if (pct >= 0.70) return 'bg-sky-500 text-white'
  if (pct >= 0.55) return 'bg-amber-400 text-white'
  if (pct >= 0.40) return 'bg-orange-400 text-white'
  return 'bg-red-400 text-white'
}

/**
 * Return Tailwind badge classes for an arrow score value.
 * @param {string} score - 'X', '10', '9'…'1', 'M'
 */
export function arrowBadgeColor(score) {
  if (score === 'X')          return 'bg-yellow-400 text-gray-900'
  const n = numericScore(score)
  if (n === 10)               return 'bg-yellow-300 text-gray-900'
  if (n >= 8)                 return 'bg-red-500 text-white'
  if (n >= 6)                 return 'bg-red-400 text-white'
  if (n >= 4)                 return 'bg-sky-500 text-white'
  if (n >= 2)                 return 'bg-sky-300 text-white'
  if (n === 1)                return 'bg-gray-200 text-gray-700'
  return 'bg-white text-gray-400 ring-1 ring-gray-200' // M or unknown
}

/**
 * Return the zone label for a numeric score.
 * @param {string} score
 */
export function scoreZone(score) {
  if (score === 'X') return 'X-Ring'
  const n = numericScore(score)
  if (n >= 9)  return 'Yellow'
  if (n >= 7)  return 'Red'
  if (n >= 5)  return 'Blue'
  if (n >= 3)  return 'Black'
  if (n >= 1)  return 'White'
  return 'Miss'
}

/**
 * Format score with max, e.g. "540 / 600"
 * @param {number|null} score
 * @param {number|null} max
 */
export function scoreWithMax(score, max) {
  if (score == null) return '—'
  if (max == null) return String(score)
  return `${score} / ${max}`
}

/**
 * Format an improvement rate with sign and % symbol.
 * @param {number|null} rate
 */
export function formatImprovement(rate) {
  if (rate == null) return '—'
  const sign = rate >= 0 ? '+' : ''
  return `${sign}${rate}%`
}
