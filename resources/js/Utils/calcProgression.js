import { mean, movingAverage, improvementRate } from './calcAverages.js'

/**
 * Build a score progression dataset from an array of session objects.
 * @param {{ started_at: string, total_score: number, round_type?: string }[]} sessions
 * @param {{ groupBy?: 'session'|'week'|'month', movingAvgWindow?: number }} options
 * @returns {{
 *   labels: string[],
 *   scores: number[],
 *   trend: (number|null)[],
 *   summary: { avg: number|null, best: number|null, worst: number|null, improvement: number|null }
 * }}
 */
export function buildProgression(sessions, options = {}) {
  const { groupBy = 'session', movingAvgWindow = 3 } = options

  if (!sessions.length) {
    return { labels: [], scores: [], trend: [], summary: { avg: null, best: null, worst: null, improvement: null } }
  }

  let groups

  if (groupBy === 'week') {
    groups = groupByPeriod(sessions, weekKey)
  } else if (groupBy === 'month') {
    groups = groupByPeriod(sessions, monthKey)
  } else {
    // Per session
    groups = sessions.map((s) => ({
      label: new Date(s.started_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }),
      score: s.total_score,
    }))
  }

  const labels = groups.map((g) => g.label)
  const scores = groups.map((g) => g.score ?? g.avg ?? 0)
  const trend  = movingAverage(scores, movingAvgWindow)

  const summary = {
    avg:         mean(scores),
    best:        scores.length ? Math.max(...scores) : null,
    worst:       scores.length ? Math.min(...scores) : null,
    improvement: improvementRate(scores, movingAvgWindow),
  }

  return { labels, scores, trend, summary }
}

// ── Helpers ────────────────────────────────────────────────────────────────────

function weekKey(iso) {
  const d = new Date(iso)
  const jan1 = new Date(d.getFullYear(), 0, 1)
  const week = Math.ceil(((d - jan1) / 86400000 + jan1.getDay() + 1) / 7)
  return `${d.getFullYear()}-W${String(week).padStart(2, '0')}`
}

function monthKey(iso) {
  const d = new Date(iso)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

function groupByPeriod(sessions, keyFn) {
  const map = new Map()

  for (const s of sessions) {
    const key = keyFn(s.started_at)
    if (!map.has(key)) map.set(key, { label: formatPeriodLabel(s.started_at, keyFn), scores: [] })
    map.get(key).scores.push(s.total_score)
  }

  return [...map.values()].map((g) => ({
    label: g.label,
    avg:   mean(g.scores),
    count: g.scores.length,
  }))
}

function formatPeriodLabel(iso, keyFn) {
  const d = new Date(iso)
  if (keyFn === weekKey) {
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
  }
  return d.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' })
}

/**
 * Find the best and worst sessions by score.
 * @param {{ started_at: string, total_score: number }[]} sessions
 */
export function bestAndWorst(sessions) {
  if (!sessions.length) return { best: null, worst: null }

  let best  = sessions[0]
  let worst = sessions[0]

  for (const s of sessions) {
    if (s.total_score > best.total_score)  best  = s
    if (s.total_score < worst.total_score) worst = s
  }

  return { best, worst }
}

/**
 * Group sessions by round type and compute per-type stats.
 * @param {{ round_type?: string, total_score: number }[]} sessions
 */
export function byRoundType(sessions) {
  const map = {}

  for (const s of sessions) {
    const rt = s.round_type ?? 'Practice'
    if (!map[rt]) map[rt] = []
    map[rt].push(s.total_score)
  }

  return Object.entries(map).map(([roundType, scores]) => ({
    round_type:  roundType,
    count:       scores.length,
    avg:         mean(scores),
    best:        Math.max(...scores),
    worst:       Math.min(...scores),
    improvement: improvementRate(scores),
  }))
}
