import { ref, computed } from 'vue'

/**
 * Client-side pagination composable.
 *
 * @param {Ref<Array>} items - Reactive array of items to paginate
 * @param {number} defaultPageSize - Items per page
 */
export function usePagination(items, defaultPageSize = 15) {
  const currentPage = ref(1)
  const pageSize    = ref(defaultPageSize)

  const totalItems = computed(() => items.value.length)
  const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / pageSize.value)))

  const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return items.value.slice(start, start + pageSize.value)
  })

  const from = computed(() => totalItems.value === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
  const to   = computed(() => Math.min(currentPage.value * pageSize.value, totalItems.value))

  const hasPrev = computed(() => currentPage.value > 1)
  const hasNext = computed(() => currentPage.value < totalPages.value)

  /** Pages to display around current page (up to 5 visible) */
  const visiblePages = computed(() => {
    const pages = []
    const start = Math.max(1, currentPage.value - 2)
    const end   = Math.min(totalPages.value, start + 4)
    for (let i = start; i <= end; i++) pages.push(i)
    return pages
  })

  function goTo(page) {
    currentPage.value = Math.max(1, Math.min(page, totalPages.value))
  }

  function prev() { if (hasPrev.value) currentPage.value-- }
  function next() { if (hasNext.value) currentPage.value++ }

  function reset() { currentPage.value = 1 }

  return {
    currentPage,
    pageSize,
    totalItems,
    totalPages,
    paginatedItems,
    from,
    to,
    hasPrev,
    hasNext,
    visiblePages,
    goTo,
    prev,
    next,
    reset,
  }
}
