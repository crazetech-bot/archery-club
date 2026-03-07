import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Provides reactive access to the authenticated user and role helpers.
 */
export function useAuth() {
  const page = usePage()

  const user = computed(() => page.props.auth?.user ?? null)

  const isAuthenticated = computed(() => !!user.value)

  const role = computed(() => page.props.auth?.role ?? null)

  const isArcher    = computed(() => role.value === 'archer')
  const isCoach     = computed(() => role.value === 'coach')
  const isAdmin     = computed(() => role.value === 'club_admin')
  const isSuperAdmin = computed(() => user.value?.is_super_admin === true)

  /**
   * Check if the current user has a specific role.
   * @param {string|string[]} roles
   */
  function hasRole(roles) {
    const list = Array.isArray(roles) ? roles : [roles]
    return list.includes(role.value)
  }

  /**
   * Check if the current user has a permission (passed from backend via props).
   * @param {string} permission
   */
  function can(permission) {
    const permissions = page.props.auth?.permissions ?? []
    return permissions.includes(permission)
  }

  return {
    user,
    role,
    isAuthenticated,
    isArcher,
    isCoach,
    isAdmin,
    isSuperAdmin,
    hasRole,
    can,
  }
}
