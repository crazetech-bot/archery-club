<template>
  <SuperAdminLayout title="Club Overview">
    <div class="space-y-6">

      <div>
        <h1 class="text-2xl font-bold text-gray-900">Club Overview</h1>
        <p class="mt-1 text-sm text-gray-500">System stats and administration.</p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Members</p>
          <p class="mt-1 text-3xl font-bold text-gray-900">{{ stats.total_members ?? '—' }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Archers</p>
          <p class="mt-1 text-3xl font-bold text-indigo-600">{{ stats.total_archers ?? '—' }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Coaches</p>
          <p class="mt-1 text-3xl font-bold text-sky-600">{{ stats.total_coaches ?? '—' }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Sessions</p>
          <p class="mt-1 text-3xl font-bold text-emerald-600">{{ stats.total_sessions ?? '—' }}</p>
        </div>
      </div>

      <!-- Members table -->
      <div>
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-900">All Members</h2>
          <Link
            href="/admin/members"
            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
          >
            Manage Members
          </Link>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Name</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Email</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Role</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Joined</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="member in members" :key="member.id" class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 font-medium text-gray-900">{{ member.name }}</td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ member.email }}</td>
                <td class="px-5 py-3">
                  <span :class="roleBadge(member.role)">{{ member.role }}</span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-400">{{ formatDate(member.created_at) }}</td>
              </tr>
              <tr v-if="members.length === 0">
                <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400">No members yet</td>
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
  members: { type: Array,  default: () => [] },
  stats:   { type: Object, default: () => ({}) },
})

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function roleBadge(role) {
  const map = {
    super_admin: 'rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700',
    club_admin:  'rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700',
    coach:       'rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700',
    archer:      'rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600',
  }
  return map[role] ?? 'rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500'
}
</script>
