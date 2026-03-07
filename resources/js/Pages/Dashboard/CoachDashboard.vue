<template>
  <CoachLayout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Welcome header ──────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Coach Dashboard</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">{{ coach.name }}</h1>
          <p class="mt-0.5 text-sm text-gray-500">Level {{ coach.level }} · {{ archers.length }} archer{{ archers.length !== 1 ? 's' : '' }} assigned</p>
        </div>

        <Link
          href="/live/monitor"
          class="inline-flex items-center gap-2 rounded-2xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-gray-900/20 transition hover:bg-gray-700 active:scale-95"
        >
          <span
            v-if="activeSessions > 0"
            class="flex h-2 w-2 rounded-full bg-green-400"
          />
          <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
          {{ activeSessions > 0 ? `Monitor (${activeSessions} live)` : 'Live Monitor' }}
        </Link>
      </div>

      <!-- ── Stats row ───────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Archers</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">{{ archers.length }}</p>
          <p class="mt-0.5 text-xs text-gray-400">assigned to you</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-green-100">
          <p class="text-xs font-medium uppercase tracking-widest text-green-600">Live Now</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-green-600">{{ activeSessions }}</p>
          <p class="mt-0.5 text-xs text-gray-400">active sessions</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">This Week</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">{{ stats.sessions_this_week }}</p>
          <p class="mt-0.5 text-xs text-gray-400">training sessions</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Avg Score</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">{{ stats.group_avg_score ?? '—' }}</p>
          <p class="mt-0.5 text-xs text-gray-400">across all archers</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- ── Archer list (2/3 width) ──────────────────────────────────── -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">My Archers</h2>
            <Link href="/coach/archers" class="text-xs font-medium text-gray-400 hover:text-gray-700">
              View all →
            </Link>
          </div>

          <div v-if="archers.length > 0" class="space-y-3">
            <Link
              v-for="archer in archers"
              :key="archer.id"
              :href="`/coach/archers/${archer.id}`"
              class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 transition hover:ring-gray-200"
            >
              <!-- Avatar -->
              <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-900 text-sm font-bold text-white">
                {{ initials(archer.name) }}
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <p class="text-sm font-semibold text-gray-900">{{ archer.name }}</p>
                  <!-- Live indicator -->
                  <span
                    v-if="archer.has_active_session"
                    class="flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700"
                  >
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500"/>
                    Live
                  </span>
                </div>
                <p class="mt-0.5 text-xs text-gray-400">
                  {{ archer.category }} · Last session: {{ archer.last_session_date ? formatDate(archer.last_session_date) : 'Never' }}
                </p>
              </div>

              <!-- Score progression indicator -->
              <div class="flex shrink-0 flex-col items-end gap-1">
                <p class="text-sm font-bold tabular-nums text-gray-900">{{ archer.avg_score ?? '—' }}</p>
                <p class="text-xs text-gray-400">avg score</p>
              </div>

              <!-- Improvement delta -->
              <div
                v-if="archer.improvement_rate !== null"
                :class="[
                  'flex shrink-0 items-center gap-0.5 text-xs font-semibold',
                  archer.improvement_rate >= 0 ? 'text-green-600' : 'text-red-500',
                ]"
              >
                <svg
                  :class="['h-3 w-3', archer.improvement_rate < 0 ? 'rotate-180' : '']"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                </svg>
                {{ Math.abs(archer.improvement_rate) }}%
              </div>

              <svg class="h-4 w-4 shrink-0 text-gray-300 transition group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>

          <div
            v-else
            class="flex flex-col items-center justify-center rounded-2xl bg-white py-12 shadow-sm ring-1 ring-gray-100"
          >
            <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-sm font-medium text-gray-600">No archers assigned yet</p>
            <p class="mt-1 text-xs text-gray-400">Ask your club admin to assign archers to you.</p>
          </div>
        </div>

        <!-- ── Right column ───────────────────────────────────────────────── -->
        <div class="space-y-4">

          <!-- Recent activity feed -->
          <div>
            <h2 class="mb-3 text-sm font-semibold text-gray-900">Recent Activity</h2>
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 divide-y divide-gray-50">
              <div
                v-for="event in recentActivity"
                :key="event.id"
                class="flex items-start gap-3 p-4"
              >
                <!-- Activity dot -->
                <div :class="['mt-0.5 h-2 w-2 shrink-0 rounded-full', activityDotColor(event.type)]" />

                <div class="min-w-0 flex-1">
                  <p class="text-xs font-medium text-gray-800">{{ event.archer_name }}</p>
                  <p class="text-xs text-gray-500">{{ event.description }}</p>
                  <p class="mt-0.5 text-xs text-gray-400">{{ formatRelative(event.occurred_at) }}</p>
                </div>
              </div>

              <div v-if="recentActivity.length === 0" class="p-6 text-center">
                <p class="text-xs text-gray-400">No recent activity</p>
              </div>
            </div>
          </div>

          <!-- Quick links -->
          <div>
            <h2 class="mb-3 text-sm font-semibold text-gray-900">Quick Links</h2>
            <div class="space-y-2">
              <Link
                v-for="link in quickLinks"
                :key="link.label"
                :href="link.href"
                class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100 transition hover:ring-gray-200"
              >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-50">
                  <component :is="link.icon" class="h-4 w-4 text-gray-500" />
                </div>
                <span class="text-sm font-medium text-gray-700">{{ link.label }}</span>
                <svg class="ml-auto h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </Link>
            </div>
          </div>

        </div>
      </div>
    </div>
  </CoachLayout>
</template>

<script setup>
import { computed, markRaw } from 'vue'
import { Link } from '@inertiajs/vue3'
import CoachLayout from '@/Components/Layouts/CoachLayout.vue'

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps({
  coach:          { type: Object, required: true },
  archers:        { type: Array,  default: () => [] },
  stats:          { type: Object, default: () => ({}) },
  recentActivity: { type: Array,  default: () => [] },
})

const activeSessions = computed(() =>
  props.archers.filter((a) => a.has_active_session).length
)

// ── Icons ─────────────────────────────────────────────────────────────────────

const IconEye       = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>` })
const IconChart     = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>` })
const IconClipboard = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>` })

const quickLinks = [
  { label: 'Live Monitor',   href: '/live/monitor',   icon: IconEye },
  { label: 'Reports',        href: '/reports/coach',  icon: IconChart },
  { label: 'Training Plans', href: '/coach/plans',    icon: IconClipboard },
]

// ── Helpers ───────────────────────────────────────────────────────────────────

function initials(name) {
  return name.split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
}

function formatRelative(iso) {
  const diff = Date.now() - new Date(iso).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 60)   return `${mins}m ago`
  if (mins < 1440) return `${Math.floor(mins / 60)}h ago`
  return `${Math.floor(mins / 1440)}d ago`
}

function activityDotColor(type) {
  return {
    session_started:   'bg-green-500',
    session_completed: 'bg-gray-400',
    end_submitted:     'bg-sky-400',
    competition:       'bg-yellow-400',
  }[type] ?? 'bg-gray-300'
}
</script>
