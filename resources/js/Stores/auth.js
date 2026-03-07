import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export const useAuthStore = defineStore('auth', () => {
  const page = usePage()

  const user        = computed(() => page.props.auth?.user ?? null)
  const role        = computed(() => page.props.auth?.role ?? null)
  const permissions = computed(() => page.props.auth?.permissions ?? [])

  const isAuthenticated = computed(() => !!user.value)
  const isArcher        = computed(() => role.value === 'archer')
  const isCoach         = computed(() => role.value === 'coach')
  const isAdmin         = computed(() => role.value === 'club_admin')
  const isSuperAdmin    = computed(() => !!user.value?.is_super_admin)

  function can(permission) {
    return permissions.value.includes(permission)
  }

  function hasRole(roles) {
    const list = Array.isArray(roles) ? roles : [roles]
    return list.includes(role.value)
  }

  return {
    user,
    role,
    permissions,
    isAuthenticated,
    isArcher,
    isCoach,
    isAdmin,
    isSuperAdmin,
    can,
    hasRole,
  }
})
