<template>
  <SuperAdminLayout title="Platform Overview">
    <div class="space-y-6">

      <!-- Page title -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Platform Overview</h1>
        <p class="mt-1 text-sm text-gray-500">Manage all tenants and platform settings.</p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Total Tenants</p>
          <p class="mt-1 text-3xl font-bold text-gray-900">{{ stats.total_tenants }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Active</p>
          <p class="mt-1 text-3xl font-bold text-green-600">{{ stats.active_tenants }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Suspended</p>
          <p class="mt-1 text-3xl font-bold text-red-500">{{ stats.suspended_tenants }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Trial</p>
          <p class="mt-1 text-3xl font-bold text-yellow-500">{{ stats.trial_tenants }}</p>
        </div>
      </div>

      <!-- Tenants table -->
      <div>
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-900">Tenants</h2>
          <Link
            :href="route('admin.tenants.create')"
            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
          >
            + New Tenant
          </Link>
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
                <td class="px-5 py-3 text-gray-600 capitalize">{{ tenant.plan }}</td>
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
                <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">No tenants yet</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import SuperAdminLayout from '@/Components/Layouts/SuperAdminLayout.vue'

defineProps({
  tenants: { type: Array, default: () => [] },
  stats:   { type: Object, default: () => ({ total_tenants: 0, active_tenants: 0, suspended_tenants: 0, trial_tenants: 0 }) },
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
