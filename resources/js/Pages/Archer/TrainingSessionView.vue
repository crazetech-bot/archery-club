<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Back + header ───────────────────────────────────────────────────── -->
      <div>
        <Link href="/archer/sessions" class="text-xs font-medium text-gray-400 hover:text-gray-700">
          ← Training Sessions
        </Link>
        <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">
              {{ session.round_type ?? 'Practice Session' }}
            </h1>
            <p class="mt-0.5 text-sm text-gray-500">
              {{ formatDate(session.started_at) }}
              <span v-if="session.distance_metres"> · {{ session.distance_metres }}m</span>
              <span v-if="session.coach_name"> · Coach: {{ session.coach_name }}</span>
            </p>
          </div>

          <div class="flex items-center gap-2">
            <a
              :href="`/archer/sessions/${session.id}/export`"
              class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Export PDF
            </a>
          </div>
        </div>
      </div>

      <!-- ── Score summary cards ─────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Total Score</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">
            {{ liveSession?.total_score ?? '—' }}
          </p>
          <p v-if="session.max_score" class="mt-0.5 text-xs text-gray-400">/ {{ session.max_score }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Avg / End</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">
            {{ liveSession?.average_per_end?.toFixed(1) ?? '—' }}
          </p>
          <p class="mt-0.5 text-xs text-gray-400">points</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">X Count</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-yellow-500">
            {{ liveSession?.x_count ?? '—' }}
          </p>
          <p class="mt-0.5 text-xs text-gray-400">perfect arrows</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Duration</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">
            {{ formatDuration(session.started_at, session.ended_at) }}
          </p>
          <p class="mt-0.5 text-xs text-gray-400">total time</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- ── Ends list ──────────────────────────────────────────────────── -->
        <div class="lg:col-span-2 space-y-3">
          <h2 class="text-sm font-semibold text-gray-900">Ends</h2>

          <div
            v-for="end in ends"
            :key="end.id"
            class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">End {{ end.end_number }}</p>
              <div class="flex items-center gap-2">
                <span v-if="end.x_count > 0" class="rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-semibold text-yellow-600">
                  {{ end.x_count }}× X
                </span>
                <span v-if="end.ten_count > 0" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">
                  {{ end.ten_count }}× 10
                </span>
                <span class="text-sm font-bold tabular-nums text-gray-900">{{ end.total_score }}</span>
              </div>
            </div>

            <!-- Arrow scores -->
            <div class="mt-3 flex flex-wrap gap-2">
              <span
                v-for="(arrow, idx) in end.arrows"
                :key="idx"
                :class="[
                  'flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold',
                  arrowColor(arrow.score),
                ]"
              >
                {{ arrow.score }}
              </span>
            </div>

            <!-- Notes -->
            <p v-if="end.notes" class="mt-3 text-xs text-gray-500 italic">{{ end.notes }}</p>
          </div>

          <div
            v-if="ends.length === 0"
            class="flex flex-col items-center justify-center rounded-2xl bg-white py-12 shadow-sm ring-1 ring-gray-100"
          >
            <p class="text-sm text-gray-400">No ends recorded for this session.</p>
          </div>
        </div>

        <!-- ── Right panel ─────────────────────────────────────────────────── -->
        <div class="space-y-4">

          <!-- Session details -->
          <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-50 px-5 py-4">
              <h2 class="text-sm font-semibold text-gray-900">Session Details</h2>
            </div>
            <dl class="divide-y divide-gray-50">
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Round</dt>
                <dd class="text-xs font-medium text-gray-700">{{ session.round_type ?? 'Practice' }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Distance</dt>
                <dd class="text-xs font-medium text-gray-700">{{ session.distance_metres ? `${session.distance_metres}m` : '—' }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Arrows/End</dt>
                <dd class="text-xs font-medium text-gray-700">{{ liveSession?.arrows_per_end ?? '—' }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Ends Shot</dt>
                <dd class="text-xs font-medium text-gray-700">{{ ends.length }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Coach</dt>
                <dd class="text-xs font-medium text-gray-700">{{ session.coach_name ?? '—' }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Started</dt>
                <dd class="text-xs font-medium text-gray-700">{{ formatTime(session.started_at) }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Finished</dt>
                <dd class="text-xs font-medium text-gray-700">{{ session.ended_at ? formatTime(session.ended_at) : 'Ongoing' }}</dd>
              </div>
            </dl>
          </div>

          <!-- Score by end chart (simple bars) -->
          <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <h2 class="mb-4 text-sm font-semibold text-gray-900">Score per End</h2>
            <div class="flex items-end gap-1.5">
              <div
                v-for="end in ends"
                :key="end.id"
                class="group relative flex-1"
                style="min-width: 0"
              >
                <!-- Bar -->
                <div
                  :style="{ height: `${barHeight(end.total_score)}px` }"
                  :class="['w-full rounded-t-md transition-all', barColor(end.total_score)]"
                />
                <!-- Tooltip -->
                <div class="absolute bottom-full left-1/2 mb-1 hidden -translate-x-1/2 rounded-lg bg-gray-900 px-2 py-1 text-xs text-white group-hover:block whitespace-nowrap">
                  End {{ end.end_number }}: {{ end.total_score }}
                </div>
                <!-- Label -->
                <p class="mt-1 text-center text-[10px] text-gray-400">{{ end.end_number }}</p>
              </div>
            </div>
          </div>

          <!-- Equipment used -->
          <div v-if="session.equipment" class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-50 px-5 py-4">
              <h2 class="text-sm font-semibold text-gray-900">Equipment Used</h2>
            </div>
            <dl class="divide-y divide-gray-50">
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Bow</dt>
                <dd class="text-xs font-medium text-gray-700">{{ session.equipment.bow_type }} — {{ session.equipment.bow_brand }} {{ session.equipment.bow_model }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Arrows</dt>
                <dd class="text-xs font-medium text-gray-700">{{ session.equipment.arrow_brand }} {{ session.equipment.arrow_model }}</dd>
              </div>
              <div v-if="session.equipment.draw_weight_lbs" class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Draw Weight</dt>
                <dd class="text-xs font-medium text-gray-700">{{ session.equipment.draw_weight_lbs }} lbs</dd>
              </div>
            </dl>
          </div>

        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  session:     { type: Object, required: true },
  liveSession: { type: Object, default: null },
  ends:        { type: Array,  default: () => [] },
})

// ── Chart helpers ──────────────────────────────────────────────────────────────

const maxEndScore = computed(() =>
  props.ends.reduce((m, e) => Math.max(m, e.total_score ?? 0), 0) || 1
)

function barHeight(score) {
  return Math.round((score / maxEndScore.value) * 80) + 4
}

function barColor(score) {
  const pct = score / maxEndScore.value
  if (pct >= 0.85) return 'bg-green-400'
  if (pct >= 0.65) return 'bg-sky-400'
  if (pct >= 0.45) return 'bg-amber-400'
  return 'bg-red-300'
}

// ── Arrow colour ───────────────────────────────────────────────────────────────

function arrowColor(score) {
  if (score === 'X') return 'bg-yellow-400 text-white'
  const n = parseInt(score, 10)
  if (n === 10) return 'bg-yellow-300 text-gray-900'
  if (n >= 8)   return 'bg-red-500 text-white'
  if (n >= 6)   return 'bg-red-400 text-white'
  if (n >= 4)   return 'bg-sky-500 text-white'
  if (n >= 2)   return 'bg-sky-300 text-white'
  if (n === 1)  return 'bg-gray-200 text-gray-700'
  return 'bg-white text-gray-400 ring-1 ring-gray-200' // M
}

// ── Helpers ────────────────────────────────────────────────────────────────────

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  })
}

function formatTime(iso) {
  return new Date(iso).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
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
