import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useNotificationsStore = defineStore('notifications', () => {
  const items = ref([])
  let nextId = 1

  const unreadCount = computed(() => items.value.filter((n) => !n.read).length)

  function push({ message, type = 'info', duration = 4000, persistent = false }) {
    const id = nextId++
    items.value.push({ id, message, type, read: false, persistent, createdAt: Date.now() })

    if (!persistent && duration > 0) {
      setTimeout(() => dismiss(id), duration)
    }

    return id
  }

  function success(message, opts = {}) { return push({ message, type: 'success', ...opts }) }
  function error(message, opts = {})   { return push({ message, type: 'error',   duration: 6000, ...opts }) }
  function warning(message, opts = {}) { return push({ message, type: 'warning', duration: 5000, ...opts }) }
  function info(message, opts = {})    { return push({ message, type: 'info',    ...opts }) }

  function dismiss(id) {
    const idx = items.value.findIndex((n) => n.id === id)
    if (idx !== -1) items.value.splice(idx, 1)
  }

  function markRead(id) {
    const item = items.value.find((n) => n.id === id)
    if (item) item.read = true
  }

  function markAllRead() {
    items.value.forEach((n) => { n.read = true })
  }

  function dismissAll() {
    items.value = items.value.filter((n) => n.persistent)
  }

  return {
    items,
    unreadCount,
    push,
    success,
    error,
    warning,
    info,
    dismiss,
    markRead,
    markAllRead,
    dismissAll,
  }
})
