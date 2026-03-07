import { ref } from 'vue'

const notifications = ref([])
let nextId = 1

/**
 * Global notification/toast composable.
 * Usage: const { notify, dismiss } = useNotifications()
 */
export function useNotifications() {
  /**
   * Show a toast notification.
   * @param {{ message: string, type?: 'info'|'success'|'warning'|'error', duration?: number }} options
   */
  function notify({ message, type = 'info', duration = 4000 }) {
    const id = nextId++
    notifications.value.push({ id, message, type })

    if (duration > 0) {
      setTimeout(() => dismiss(id), duration)
    }

    return id
  }

  function success(message, duration = 4000) {
    return notify({ message, type: 'success', duration })
  }

  function error(message, duration = 6000) {
    return notify({ message, type: 'error', duration })
  }

  function warning(message, duration = 5000) {
    return notify({ message, type: 'warning', duration })
  }

  function info(message, duration = 4000) {
    return notify({ message, type: 'info', duration })
  }

  function dismiss(id) {
    const idx = notifications.value.findIndex((n) => n.id === id)
    if (idx !== -1) notifications.value.splice(idx, 1)
  }

  function dismissAll() {
    notifications.value = []
  }

  return {
    notifications,
    notify,
    success,
    error,
    warning,
    info,
    dismiss,
    dismissAll,
  }
}
