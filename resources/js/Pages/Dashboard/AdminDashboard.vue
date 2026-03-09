<template>
  <AdminLayout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Welcome header ──────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Club Admin</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">{{ club.name }}</h1>
        </div>

        <div class="flex flex-wrap gap-2">
          <Link
            href="/admin/members"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Member
          </Link>
          <Link
            href="/scoring/scorecards"
            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700 active:scale-95"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Scorecards
          </Link>
        </div>
      </div>

      <!-- ── KPI stats ───────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div
          v-for="kpi in kpis"
          :key="kpi.label"
          class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100"
        >
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">{{ kpi.label }}</p>
          <p :class="['mt-1 text-3xl font-bold tabular-nums', kpi.color ?? 'text-gray-900']">
            {{ kpi.value ?? '—' }}
          </p>
          <p v-if="kpi.sub" class="mt-0.5 text-xs text-gray-400">{{ kpi.sub }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- ── Recent members (2/3 width) ──────────────────────────────── -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Members</h2>
            <Link href="/admin/members" class="text-xs font-medium text-gray-400 hover:text-gray-700">
              All members →
            </Link>
          </div>

          <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-50 bg-gray-50">
                  <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Member</th>
                  <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Role</th>
                  <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Sessions</th>
                  <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Last Active</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr
                  v-for="member in recentMembers"
                  :key="member.id"
                  class="hover:bg-gray-50 transition"
                >
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600">
                        {{ initials(member.name) }}
                      </div>
                      <div>
                        <p class="font-medium text-gray-900">{{ member.name }}</p>
                        <p class="text-xs text-gray-400">{{ member.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3">
                    <span :class="roleBadgeClass(member.role)">{{ member.role }}</span>
                  </td>
                  <td class="px-5 py-3 text-right tabular-nums text-gray-600">{{ member.session_count ?? '—' }}</td>
                  <td class="px-5 py-3 text-right text-xs text-gray-400">
                    {{ member.last_active ? formatRelative(member.last_active) : 'Never' }}
                  </td>
                </tr>
                <tr v-if="recentMembers.length === 0">
                  <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400">No members yet</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ── Quick actions ──────────────────────────────────────────────── -->
        <div class="space-y-4">
          <h2 class="text-sm font-semibold text-gray-900">Quick Actions</h2>
          <div class="space-y-2">
            <Link
              v-for="action in quickActions"
              :key="action.label"
              :href="action.href"
              class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100 transition hover:ring-gray-200"
            >
              <div :class="['flex h-8 w-8 shrink-0 items-center justify-center rounded-lg', action.iconBg]">
                <component :is="action.icon" :class="['h-4 w-4', action.iconColor]" />
              </div>
              <span class="text-sm font-medium text-gray-700">{{ action.label }}</span>
              <svg class="ml-auto h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, markRaw } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'

const props = defineProps({
  club:          { type: Object, default: () => ({ name: 'Club' }) },
  clubStats:     { type: Object, default: () => ({}) },
  recentMembers: { type: Array,  default: () => [] },
})

const kpis = computed(() => [
  { label: 'Members',  value: props.clubStats.total_members,      sub: 'registered' },
  { label: 'Archers',  value: props.clubStats.total_archers,      sub: 'profiles' },
  { label: 'Coaches',  value: props.clubStats.total_coaches,      sub: 'profiles' },
  { label: 'Sessions', value: props.clubStats.sessions_this_month, sub: 'this month' },
])

const IconUserPlus = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>` })
const IconScoring  = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>` })
const IconCog      = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>` })

const quickActions = [
  { label: 'Add Member',     href: '/admin/members',          icon: IconUserPlus, iconBg: 'bg-blue-50',  iconColor: 'text-blue-500' },
  { label: 'Scorecards',     href: '/scoring/scorecards',     icon: IconScoring,  iconBg: 'bg-indigo-50', iconColor: 'text-indigo-500' },
  { label: 'Club Settings',  href: '/admin/club',             icon: IconCog,      iconBg: 'bg-gray-100', iconColor: 'text-gray-500' },
]

function initials(name) {
  return name.split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatRelative(iso) {
  const diff = Date.now() - new Date(iso).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 60)   return `${mins}m ago`
  if (mins < 1440) return `${Math.floor(mins / 60)}h ago`
  return `${Math.floor(mins / 1440)}d ago`
}

function roleBadgeClass(role) {
  return {
    club_admin: 'rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700',
    coach:      'rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700',
    archer:     'rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600',
  }[role] ?? 'rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500'
}
</script>
