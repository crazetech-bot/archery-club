<template>
  <SuperAdminLayout title="Tenants">
    <div class="space-y-6">

      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Tenants</h1>
          <p class="mt-1 text-sm text-gray-500">{{ tenants.length }} club{{ tenants.length !== 1 ? 's' : '' }} registered</p>
        </div>
        <Link
          :href="route('admin.tenants.create')"
          class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-700 transition"
        >
          + New Tenant
        </Link>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700 ring-1 ring-green-200">
        {{ $page.props.flash.success }}
      </div>

      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Club</th>
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Plan</th>
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Created</th>
              <th class="px-5 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="tenant in tenants" :key="tenant.id" class="hover:bg-gray-50 transition">
              <td class="px-5 py-3">
                <p class="font-medium text-gray-900">{{ tenant.name }}</p>
                <p class="text-xs text-gray-400">{{ tenant.slug }}</p>
              </td>
              <td class="px-5 py-3 capitalize text-gray-600">{{ tenant.plan }}</td>
              <td class="px-5 py-3">
                <span :class="statusBadge(tenant.status)">{{ tenant.status }}</span>
              </td>
              <td class="px-5 py-3 text-xs text-gray-400">{{ formatDate(tenant.created_at) }}</td>
              <td class="px-5 py-3 text-right">
                <Link :href="route('admin.tenants.show', tenant.id)" class="text-xs font-medium text-gray-500 hover:text-gray-900">
                  View →
                </Link>
              </td>
            </tr>
            <tr v-if="tenants.length === 0">
              <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">No tenants yet</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import SuperAdminLayout from '@/Components/Layouts/SuperAdminLayout.vue'

defineProps({
  tenants: { type: Array, default: () => [] },
})

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function statusBadge(status) {
  const map = {
    active:    'rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700',
    suspended: 'rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-600',
    trial:     'rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700',
  }
  return map[status] ?? 'rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500'
}
</script>
