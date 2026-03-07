/**
 * Date formatting utilities (en-GB locale, no dependencies).
 */

/**
 * Format a date as "1 Jan 2024"
 * @param {string|Date} iso
 */
export function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
  })
}

/**
 * Format a date as "Mon, 1 Jan 2024"
 * @param {string|Date} iso
 */
export function formatDateFull(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', {
    weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
  })
}

/**
 * Format a date as "January 2024"
 * @param {string|Date} iso
 */
export function formatMonthYear(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })
}

/**
 * Format time only as "14:30"
 * @param {string|Date} iso
 */
export function formatTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}

/**
 * Format as "1 Jan 2024, 14:30"
 * @param {string|Date} iso
 */
export function formatDateTime(iso) {
  if (!iso) return '—'
  const d = new Date(iso)
  return `${formatDate(d)}, ${formatTime(d)}`
}

/**
 * Format a duration between two dates as "1h 30m" or "45m"
 * @param {string|Date} start
 * @param {string|Date} end
 */
export function formatDuration(start, end) {
  if (!start || !end) return '—'
  const mins = Math.round((new Date(end) - new Date(start)) / 60000)
  if (mins < 1) return '<1m'
  if (mins < 60) return `${mins}m`
  const h = Math.floor(mins / 60)
  const m = mins % 60
  return m ? `${h}h ${m}m` : `${h}h`
}

/**
 * Format a relative time from now as "5m ago", "2h ago", "3d ago"
 * @param {string|Date} iso
 */
export function formatRelative(iso) {
  if (!iso) return '—'
  const diffMs  = Date.now() - new Date(iso).getTime()
  const mins    = Math.floor(diffMs / 60000)
  if (mins < 1)    return 'just now'
  if (mins < 60)   return `${mins}m ago`
  if (mins < 1440) return `${Math.floor(mins / 60)}h ago`
  const days = Math.floor(mins / 1440)
  if (days < 30)   return `${days}d ago`
  return formatDate(iso)
}

/**
 * Format a date as "YYYY-MM-DD" for input[type=date]
 * @param {string|Date} iso
 */
export function toDateInput(iso) {
  if (!iso) return ''
  return new Date(iso).toISOString().slice(0, 10)
}

/**
 * Format as "YYYY-MM-DDTHH:MM" for input[type=datetime-local]
 * @param {string|Date} iso
 */
export function toDatetimeLocalInput(iso) {
  if (!iso) return ''
  return new Date(iso).toISOString().slice(0, 16)
}
