import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Provides reactive access to the current tenant context.
 */
export function useTenant() {
  const page = usePage()

  const tenant = computed(() => page.props.tenant ?? null)

  const tenantId   = computed(() => tenant.value?.id ?? null)
  const tenantName = computed(() => tenant.value?.name ?? '')
  const tenantSlug = computed(() => tenant.value?.slug ?? '')
  const plan       = computed(() => tenant.value?.plan ?? 'free')

  const isProPlan      = computed(() => ['pro', 'elite'].includes(plan.value))
  const isElitePlan    = computed(() => plan.value === 'elite')
  const subscriptionStatus = computed(() => tenant.value?.subscription_status ?? null)
  const isSubscribed   = computed(() => subscriptionStatus.value === 'active' || subscriptionStatus.value === 'trialing')

  return {
    tenant,
    tenantId,
    tenantName,
    tenantSlug,
    plan,
    isProPlan,
    isElitePlan,
    subscriptionStatus,
    isSubscribed,
  }
}
