<template>
  <SuperAdminLayout :title="tenant.name">
    <div class="space-y-6">

      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 text-sm text-gray-500">
        <Link :href="route('admin.tenants.index')" class="hover:text-gray-900">Tenants</Link>
        <span>/</span>
        <span class="text-gray-900 font-medium">{{ tenant.name }}</span>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700 ring-1 ring-green-200">
        {{ $page.props.flash.success }}
      </div>

      <!-- Header card -->
      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ tenant.name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Slug: <span class="font-mono">{{ tenant.slug }}</span></p>
            <div class="mt-2 flex items-center gap-2">
              <span :class="statusBadge(tenant.status)">{{ tenant.status }}</span>
              <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 capitalize">{{ tenant.plan }}</span>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <Link
              :href="route('admin.tenants.edit', tenant.id)"
              class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
            >
              Edit
            </Link>
            <Link
              :href="route('admin.tenants.impersonate', tenant.id)"
              method="post"
              as="button"
              class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 transition"
            >
              Open Club App
            </Link>
          </div>
        </div>
      </div>

      <!-- Details grid -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400">Details</h2>
          <dl class="mt-3 space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-gray-500">Created</dt>
              <dd class="font-medium text-gray-900">{{ formatDate(tenant.created_at) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Plan</dt>
              <dd class="font-medium text-gray-900 capitalize">{{ tenant.plan }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Status</dt>
              <dd><span :class="statusBadge(tenant.status)">{{ tenant.status }}</span></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Subdomain</dt>
              <dd class="font-mono text-xs text-gray-700">{{ tenant.slug }}.{{ $page.props.tenantDomain }}</dd>
            </div>
          </dl>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-400">Status Control</h2>
          <p class="mt-2 text-sm text-gray-500">Change the tenant's access status.</p>
          <div class="mt-4 flex flex-col gap-2">
            <Link
              v-if="tenant.status !== 'active'"
              :href="route('admin.tenants.status', tenant.id)"
              method="patch"
              as="button"
              :data="{ status: 'active' }"
              class="w-full rounded-xl bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700 transition text-center"
            >
              Activate Tenant
            </Link>
            <Link
              v-if="tenant.status !== 'suspended'"
              :href="route('admin.tenants.status', tenant.id)"
              method="patch"
              as="button"
              :data="{ status: 'suspended' }"
              class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 transition text-center"
            >
              Suspend Tenant
            </Link>
            <Link
              v-if="tenant.status !== 'trial'"
              :href="route('admin.tenants.status', tenant.id)"
              method="patch"
              as="button"
              :data="{ status: 'trial' }"
              class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition text-center"
            >
              Set to Trial
            </Link>
          </div>
        </div>
      </div>

      <!-- Danger zone -->
      <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
        <h2 class="text-xs font-semibold uppercase tracking-widest text-red-400">Danger Zone</h2>
        <div class="mt-3 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-900">Delete this tenant</p>
            <p class="text-xs text-gray-500 mt-0.5">This cannot be undone. All tenant data will be lost.</p>
          </div>
          <Link
            :href="route('admin.tenants.destroy', tenant.id)"
            method="delete"
            as="button"
            class="rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition"
            @click.prevent="confirmDelete"
          >
            Delete
          </Link>
        </div>
      </div>

    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import SuperAdminLayout from '@/Components/Layouts/SuperAdminLayout.vue'

const props = defineProps({
  tenant: { type: Object, required: true },
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

function confirmDelete() {
  if (confirm(`Delete "${props.tenant.name}"? This cannot be undone.`)) {
    router.delete(route('admin.tenants.destroy', props.tenant.id))
  }
}
</script>
