<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────────── -->
      <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Archer</p>
        <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Competitions</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ results.length }} result{{ results.length !== 1 ? 's' : '' }} recorded</p>
      </div>

      <!-- ── Summary stats ───────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Competitions</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ results.length }}</p>
          <p class="mt-0.5 text-xs text-gray-400">entered</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-yellow-50">
          <p class="text-xs font-medium uppercase tracking-widest text-yellow-600">Gold Medals</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-yellow-500">{{ medalCount('gold') }}</p>
          <p class="mt-0.5 text-xs text-gray-400">1st place</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Podium</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ podiumCount }}</p>
          <p class="mt-0.5 text-xs text-gray-400">top 3 finishes</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Best Score</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ bestScore }}</p>
          <p class="mt-0.5 text-xs text-gray-400">personal best</p>
        </div>
      </div>

      <!-- ── Tabs ────────────────────────────────────────────────────────────── -->
      <div class="flex gap-1 rounded-xl bg-gray-100 p-1">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          :class="[
            'flex-1 rounded-lg py-2 text-xs font-semibold transition',
            activeTab === tab.key
              ? 'bg-white text-gray-900 shadow-sm'
              : 'text-gray-500 hover:text-gray-700',
          ]"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- ── Results list ────────────────────────────────────────────────────── -->
      <div class="space-y-3">
        <div
          v-for="result in filteredResults"
          :key="result.id"
          class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100"
        >
          <div class="flex items-start gap-4 p-5">
            <!-- Medal / placing -->
            <div
              :class="[
                'flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-full text-sm font-bold',
                placingColor(result.placing),
              ]"
            >
              <span>{{ result.placing ? `#${result.placing}` : '—' }}</span>
            </div>

            <!-- Info -->
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-gray-900">{{ result.competition_name }}</p>
                <span v-if="result.placing && result.placing <= 3" :class="medalBadgeClass(result.placing)">
                  {{ medalLabel(result.placing) }}
                </span>
              </div>
              <p class="mt-0.5 text-xs text-gray-400">
                {{ formatDate(result.competed_at) }} · {{ result.round_type }}
                <span v-if="result.category"> · {{ result.category }}</span>
              </p>
            </div>

            <!-- Score -->
            <div class="shrink-0 text-right">
              <p class="text-lg font-bold tabular-nums text-gray-900">{{ result.score ?? '—' }}</p>
              <p v-if="result.max_score" class="text-xs text-gray-400">/ {{ result.max_score }}</p>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="result.notes" class="border-t border-gray-50 px-5 py-3">
            <p class="text-xs text-gray-500 italic">{{ result.notes }}</p>
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-if="filteredResults.length === 0"
          class="flex flex-col items-center justify-center rounded-2xl bg-white py-16 shadow-sm ring-1 ring-gray-100"
        >
          <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
          </svg>
          <p class="text-sm font-medium text-gray-600">No competition results</p>
          <p class="mt-1 text-xs text-gray-400">
            {{ activeTab === 'all' ? 'Your results will appear here after competitions.' : 'No results in this category.' }}
          </p>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  results: { type: Array, default: () => [] },
})

// ── Tabs ───────────────────────────────────────────────────────────────────────

const activeTab = ref('all')

const tabs = [
  { key: 'all',     label: 'All' },
  { key: 'podium',  label: 'Podium' },
  { key: 'indoor',  label: 'Indoor' },
  { key: 'outdoor', label: 'Outdoor' },
]

const filteredResults = computed(() => {
  return props.results.filter((r) => {
    if (activeTab.value === 'podium') return r.placing && r.placing <= 3
    if (activeTab.value === 'indoor') return (r.round_type ?? '').toLowerCase().includes('indoor') || (r.distance_metres ?? 0) <= 25
    if (activeTab.value === 'outdoor') return (r.round_type ?? '').toLowerCase().includes('outdoor') || (r.distance_metres ?? 0) > 25
    return true
  })
})

// ── Computed stats ─────────────────────────────────────────────────────────────

function medalCount(type) {
  const pos = { gold: 1, silver: 2, bronze: 3 }[type]
  return props.results.filter((r) => r.placing === pos).length
}

const podiumCount = computed(() =>
  props.results.filter((r) => r.placing && r.placing <= 3).length
)

const bestScore = computed(() =>
  props.results.reduce((best, r) => (r.score > best ? r.score : best), '—')
)

// ── Style helpers ──────────────────────────────────────────────────────────────

function placingColor(placing) {
  if (!placing) return 'bg-gray-100 text-gray-500'
  if (placing === 1) return 'bg-yellow-400 text-white'
  if (placing === 2) return 'bg-gray-300 text-gray-700'
  if (placing === 3) return 'bg-amber-600 text-white'
  return 'bg-gray-100 text-gray-700'
}

function medalLabel(placing) {
  return { 1: 'Gold', 2: 'Silver', 3: 'Bronze' }[placing] ?? ''
}

function medalBadgeClass(placing) {
  const base = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold'
  if (placing === 1) return `${base} bg-yellow-100 text-yellow-700`
  if (placing === 2) return `${base} bg-gray-100 text-gray-600`
  if (placing === 3) return `${base} bg-amber-100 text-amber-700`
  return base
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
