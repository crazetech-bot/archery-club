<template>
  <AppLayout>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Welcome header ──────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Welcome back</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">{{ archer.name }}</h1>
          <p class="mt-0.5 text-sm text-gray-500">
            {{ archer.category }}
            <span v-if="archer.age"> · {{ archer.age }} yrs</span>
            <span v-if="archer.coach_name" class="text-gray-400"> · Coach: {{ archer.coach_name }}</span>
          </p>
        </div>

        <Link
          href="/archer/sessions"
          class="inline-flex items-center gap-2 rounded-2xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-gray-900/20 transition hover:bg-gray-700 active:scale-95"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke-width="2"/>
            <circle cx="12" cy="12" r="6"  stroke-width="2"/>
            <circle cx="12" cy="12" r="2"  fill="currentColor" stroke-width="0"/>
          </svg>
          Start Scoring
        </Link>
      </div>

      <!-- ── Stats row ───────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div
          v-for="stat in statCards"
          :key="stat.label"
          class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100"
        >
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">{{ stat.label }}</p>
          <p :class="['mt-1 text-3xl font-bold tabular-nums', stat.color ?? 'text-gray-900']">
            {{ stat.value ?? '—' }}
          </p>
          <p v-if="stat.sub" class="mt-0.5 text-xs text-gray-400">{{ stat.sub }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- ── Recent sessions (2/3 width) ──────────────────────────────── -->
        <div class="space-y-4 lg:col-span-2">

          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Recent Sessions</h2>
            <Link href="/archer/sessions" class="text-xs font-medium text-gray-400 hover:text-gray-700">
              View all →
            </Link>
          </div>

          <div v-if="recentSessions.length > 0" class="space-y-3">
            <Link
              v-for="session in recentSessions"
              :key="session.id"
              :href="`/archer/sessions/${session.id}`"
              class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 transition hover:ring-gray-200"
            >
              <!-- Score circle -->
              <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-gray-50">
                <p class="text-lg font-bold tabular-nums leading-none text-gray-900">
                  {{ session.total_score || '—' }}
                </p>
                <p v-if="session.max_score" class="text-[10px] text-gray-400">/ {{ session.max_score }}</p>
              </div>

              <!-- Session info -->
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900">
                  {{ session.round_type ?? 'Practice' }}
                  <span v-if="session.distance_metres" class="font-normal text-gray-400">
                    · {{ session.distance_metres }}m
                  </span>
                </p>
                <p class="mt-0.5 text-xs text-gray-400">{{ formatDate(session.started_at) }}</p>
              </div>

              <!-- Badges -->
              <div class="flex shrink-0 flex-col items-end gap-1.5">
                <span
                  v-if="session.x_count > 0"
                  class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700"
                >
                  {{ session.x_count }}X
                </span>
                <span class="text-xs text-gray-400">
                  {{ session.ends_count }} end{{ session.ends_count !== 1 ? 's' : '' }}
                </span>
              </div>

              <!-- Chevron -->
              <svg class="h-4 w-4 shrink-0 text-gray-300 transition group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>

          <!-- Empty state -->
          <div
            v-else
            class="flex flex-col items-center justify-center rounded-2xl bg-white py-12 shadow-sm ring-1 ring-gray-100"
          >
            <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
              <circle cx="12" cy="12" r="6"  stroke-width="1.5"/>
              <circle cx="12" cy="12" r="2"  stroke-width="1.5"/>
            </svg>
            <p class="text-sm font-medium text-gray-600">No sessions yet</p>
            <p class="mt-1 text-xs text-gray-400">Start your first live scoring session.</p>
            <Link
              href="/archer/sessions"
              class="mt-4 rounded-xl bg-gray-900 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-700"
            >
              Start now
            </Link>
          </div>
        </div>

        <!-- ── Right column ───────────────────────────────────────────────── -->
        <div class="space-y-4">

          <!-- Upcoming bookings -->
          <div>
            <div class="mb-3 flex items-center justify-between">
              <h2 class="text-sm font-semibold text-gray-900">Upcoming Bookings</h2>
            </div>

            <div v-if="upcomingBookings.length > 0" class="space-y-2">
              <div
                v-for="booking in upcomingBookings"
                :key="booking.id"
                class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100"
              >
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <p class="text-sm font-semibold text-gray-900">{{ booking.lane.name }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">{{ formatDateTime(booking.start_time) }}</p>
                    <p class="text-xs text-gray-400">{{ formatDuration(booking.start_time, booking.end_time) }}</p>
                  </div>
                  <span
                    v-if="booking.purpose"
                    class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700"
                  >
                    {{ booking.purpose }}
                  </span>
                </div>
              </div>
            </div>

            <div
              v-else
              class="rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-gray-100"
            >
              <p class="text-xs text-gray-400">No upcoming bookings</p>
            </div>
          </div>

          <!-- Current equipment -->
          <div v-if="currentEquipment">
            <div class="mb-3 flex items-center justify-between">
              <h2 class="text-sm font-semibold text-gray-900">Current Equipment</h2>
              <Link href="/archer/equipment" class="text-xs font-medium text-gray-400 hover:text-gray-700">
                Manage →
              </Link>
            </div>

            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
              <div class="mb-3 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100">
                  <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-semibold text-gray-900">
                    {{ currentEquipment.bow_brand }} {{ currentEquipment.bow_model }}
                  </p>
                  <p class="text-xs capitalize text-gray-400">{{ currentEquipment.bow_type }}</p>
                </div>
              </div>
              <dl class="space-y-1.5">
                <div v-if="currentEquipment.draw_weight_lbs" class="flex justify-between">
                  <dt class="text-xs text-gray-400">Draw weight</dt>
                  <dd class="text-xs font-medium text-gray-700">{{ currentEquipment.draw_weight_lbs }} lbs</dd>
                </div>
                <div v-if="currentEquipment.draw_length_inches" class="flex justify-between">
                  <dt class="text-xs text-gray-400">Draw length</dt>
                  <dd class="text-xs font-medium text-gray-700">{{ currentEquipment.draw_length_inches }}"</dd>
                </div>
                <div v-if="currentEquipment.arrow_brand || currentEquipment.arrow_model" class="flex justify-between">
                  <dt class="text-xs text-gray-400">Arrows</dt>
                  <dd class="max-w-[120px] truncate text-xs font-medium text-gray-700">
                    {{ currentEquipment.arrow_brand }} {{ currentEquipment.arrow_model }}
                  </dd>
                </div>
                <div v-if="currentEquipment.arrow_spine" class="flex justify-between">
                  <dt class="text-xs text-gray-400">Spine</dt>
                  <dd class="text-xs font-medium text-gray-700">{{ currentEquipment.arrow_spine }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Quick links -->
          <div>
            <h2 class="mb-3 text-sm font-semibold text-gray-900">Quick Links</h2>
            <div class="grid grid-cols-2 gap-2">
              <Link
                v-for="link in quickLinks"
                :key="link.label"
                :href="link.href"
                class="flex flex-col items-center gap-2 rounded-2xl bg-white p-4 text-center shadow-sm ring-1 ring-gray-100 transition hover:ring-gray-200"
              >
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-50">
                  <component :is="link.icon" class="h-5 w-5 text-gray-500" />
                </div>
                <span class="text-xs font-medium text-gray-700">{{ link.label }}</span>
              </Link>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, markRaw } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps({
  archer:           { type: Object, required: true },
  stats:            { type: Object, required: true },
  recentSessions:   { type: Array,  default: () => [] },
  upcomingBookings: { type: Array,  default: () => [] },
  currentEquipment: { type: Object, default: null },
})

// ── Stat cards ────────────────────────────────────────────────────────────────

const statCards = computed(() => [
  {
    label: 'Sessions',
    value: props.stats.total_sessions,
    sub:   'all time',
  },
  {
    label: 'Avg Score',
    value: props.stats.avg_score,
    sub:   'per session',
  },
  {
    label: 'Best Score',
    value: props.stats.best_score,
    color: 'text-yellow-500',
    sub:   'personal best',
  },
  {
    label: 'Total X',
    value: props.stats.total_x_count,
    color: 'text-yellow-500',
    sub:   'all time',
  },
])

// ── Icons ─────────────────────────────────────────────────────────────────────

const IconTarget = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><circle cx="12" cy="12" r="6" stroke-width="2"/><circle cx="12" cy="12" r="2" fill="currentColor" stroke-width="0"/></svg>` })
const IconChart  = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>` })
const IconTrophy = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>` })
const IconWrench = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>` })

// ── Quick links ───────────────────────────────────────────────────────────────

const quickLinks = [
  { label: 'Sessions',     href: '/archer/sessions',     icon: IconTarget },
  { label: 'Reports',      href: '/reports/archer',      icon: IconChart },
  { label: 'Competitions', href: '/archer/competitions', icon: IconTrophy },
  { label: 'Equipment',    href: '/archer/equipment',    icon: IconWrench },
]

// ── Formatters ────────────────────────────────────────────────────────────────

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatDateTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-GB', {
    weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
  })
}

function formatDuration(start, end) {
  const mins = Math.round((new Date(end) - new Date(start)) / 60000)
  if (mins >= 60) {
    const h = Math.floor(mins / 60)
    const m = mins % 60
    return m > 0 ? `${h}h ${m}m` : `${h}h`
  }
  return `${mins} min`
}
</script>
