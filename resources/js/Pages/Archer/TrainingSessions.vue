<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Archer</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Training Sessions</h1>
          <p class="mt-0.5 text-sm text-gray-500">{{ sessions.total ?? sessions.data?.length ?? 0 }} sessions recorded</p>
        </div>
        <Link
          href="/live"
          class="inline-flex items-center gap-2 rounded-2xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-gray-900/20 transition hover:bg-gray-700 active:scale-95"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          New Session
        </Link>
      </div>

      <!-- ── Filters ─────────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <Input
          v-model="search"
          placeholder="Search by round type…"
          clearable
          class="sm:w-64"
        >
          <template #icon-left>
            <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </template>
        </Input>

        <Select
          v-model="filterRound"
          placeholder="All rounds"
          :options="roundTypeOptions"
          class="sm:w-44"
        />

        <Select
          v-model="filterDistance"
          placeholder="Any distance"
          :options="distanceOptions"
          class="sm:w-40"
        />

        <button
          v-if="search || filterRound || filterDistance"
          type="button"
          class="text-xs font-medium text-gray-400 hover:text-gray-700"
          @click="search = ''; filterRound = ''; filterDistance = ''"
        >
          Clear
        </button>
      </div>

      <!-- ── Summary stats ───────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Total</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ stats.total_sessions ?? 0 }}</p>
          <p class="mt-0.5 text-xs text-gray-400">sessions</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Avg Score</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ stats.avg_score ?? '—' }}</p>
          <p class="mt-0.5 text-xs text-gray-400">per session</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Best</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ stats.best_score ?? '—' }}</p>
          <p class="mt-0.5 text-xs text-gray-400">highest score</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">This Month</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ stats.sessions_this_month ?? 0 }}</p>
          <p class="mt-0.5 text-xs text-gray-400">sessions</p>
        </div>
      </div>

      <!-- ── Session list ────────────────────────────────────────────────────── -->
      <div class="space-y-3">
        <Link
          v-for="session in displayedSessions"
          :key="session.id"
          :href="`/archer/sessions/${session.id}`"
          class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 transition hover:ring-gray-200"
        >
          <!-- Score circle -->
          <div
            :class="[
              'flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-full text-white',
              scoreColor(session.total_score, session.max_score),
            ]"
          >
            <span class="text-sm font-bold leading-none tabular-nums">{{ session.total_score ?? '—' }}</span>
            <span v-if="session.max_score" class="text-[10px] leading-none opacity-75">/ {{ session.max_score }}</span>
          </div>

          <!-- Info -->
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <p class="text-sm font-semibold text-gray-900">{{ session.round_type ?? 'Practice' }}</p>
              <span v-if="session.distance_metres" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
                {{ session.distance_metres }}m
              </span>
            </div>
            <p class="mt-0.5 text-xs text-gray-400">
              {{ formatDate(session.started_at) }} · {{ formatDuration(session.started_at, session.ended_at) }}
            </p>
          </div>

          <!-- Ends + arrows -->
          <div class="hidden shrink-0 text-right sm:block">
            <p class="text-sm font-semibold tabular-nums text-gray-700">{{ session.ends_count ?? 0 }} ends</p>
            <p class="text-xs text-gray-400">{{ session.arrows_per_end ?? '?' }} arrows/end</p>
          </div>

          <!-- X count -->
          <div v-if="session.x_count > 0" class="shrink-0">
            <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-semibold text-yellow-700">
              {{ session.x_count }}× X
            </span>
          </div>

          <svg class="h-4 w-4 shrink-0 text-gray-300 transition group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </Link>

        <!-- Empty state -->
        <div
          v-if="displayedSessions.length === 0"
          class="flex flex-col items-center justify-center rounded-2xl bg-white py-16 shadow-sm ring-1 ring-gray-100"
        >
          <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <p class="text-sm font-medium text-gray-600">No sessions found</p>
          <p class="mt-1 text-xs text-gray-400">
            {{ search || filterRound || filterDistance ? 'Try adjusting your filters.' : 'Start your first session to see it here.' }}
          </p>
        </div>
      </div>

      <!-- ── Load more (server-side pagination) ─────────────────────────────── -->
      <div v-if="sessions.next_page_url" class="flex justify-center">
        <Button variant="secondary" :loading="loadingMore" @click="loadMore">
          Load more
        </Button>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  sessions: { type: Object, required: true }, // Laravel paginator
  stats:    { type: Object, default: () => ({}) },
})

// ── Filters (client-side on loaded data) ──────────────────────────────────────

const search         = ref('')
const filterRound    = ref('')
const filterDistance = ref('')
const loadingMore    = ref(false)

const roundTypeOptions = [
  { label: 'WA 18', value: 'WA 18' },
  { label: 'WA 25', value: 'WA 25' },
  { label: 'Portsmouth', value: 'Portsmouth' },
  { label: 'Practice', value: 'Practice' },
]

const distanceOptions = [
  { label: '18m', value: '18' },
  { label: '25m', value: '25' },
  { label: '50m', value: '50' },
  { label: '70m', value: '70' },
]

const allSessions = computed(() => props.sessions.data ?? [])

const displayedSessions = computed(() => {
  return allSessions.value.filter((s) => {
    if (search.value && !(s.round_type ?? '').toLowerCase().includes(search.value.toLowerCase()))
      return false
    if (filterRound.value && s.round_type !== filterRound.value)
      return false
    if (filterDistance.value && String(s.distance_metres) !== filterDistance.value)
      return false
    return true
  })
})

function loadMore() {
  if (!props.sessions.next_page_url) return
  loadingMore.value = true
  router.get(props.sessions.next_page_url, {}, {
    preserveState: true,
    preserveScroll: true,
    only: ['sessions'],
    onFinish: () => { loadingMore.value = false },
  })
}

// ── Helpers ────────────────────────────────────────────────────────────────────

function scoreColor(score, max) {
  if (score == null || max == null) return 'bg-gray-300'
  const pct = score / max
  if (pct >= 0.85) return 'bg-green-500'
  if (pct >= 0.70) return 'bg-sky-500'
  if (pct >= 0.55) return 'bg-amber-400'
  return 'bg-gray-400'
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', {
    weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
  })
}

function formatDuration(start, end) {
  if (!end) return 'Ongoing'
  const mins = Math.round((new Date(end) - new Date(start)) / 60000)
  if (mins < 60) return `${mins}m`
  const h = Math.floor(mins / 60)
  const m = mins % 60
  return m ? `${h}h ${m}m` : `${h}h`
}
</script>
