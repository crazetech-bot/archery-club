<template>
  <AppLayout title="Scorecards">
    <div class="mx-auto max-w-5xl space-y-6 p-6">

      <!-- ── Header ────────────────────────────────────────────────────────── -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Scorecards</h2>
          <p class="mt-0.5 text-sm text-gray-400">All scoring records</p>
        </div>
        <BaseButton @click="createScorecard">New Scorecard</BaseButton>
      </div>

      <!-- ── Search + Filter Bar ───────────────────────────────────────────── -->
      <div class="flex flex-col gap-3 sm:flex-row">

        <!-- Text search -->
        <div class="relative flex-1">
          <svg
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Search by archer or session…"
            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:border-gray-400 focus:ring-2 focus:ring-gray-100"
          />
        </div>

        <!-- Status filter -->
        <select
          v-model="statusFilter"
          class="rounded-xl border border-gray-200 bg-white py-2.5 pl-4 pr-8 text-sm text-gray-700 outline-none transition focus:border-gray-400 focus:ring-2 focus:ring-gray-100 sm:w-44"
        >
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="submitted">Submitted</option>
          <option value="locked">Locked</option>
        </select>

      </div>

      <!-- ── Loading ────────────────────────────────────────────────────────── -->
      <div v-if="loading" class="flex justify-center py-20">
        <p class="text-sm text-gray-400">Loading scorecards…</p>
      </div>

      <!-- ── Content ────────────────────────────────────────────────────────── -->
      <template v-else>

        <!-- Empty state: no scorecards exist at all -->
        <div
          v-if="scorecards.length === 0"
          class="flex flex-col items-center py-24 text-center"
        >
          <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="9" stroke-width="1.5" />
              <circle cx="12" cy="12" r="5" stroke-width="1.5" />
              <circle cx="12" cy="12" r="1.5" fill="currentColor" />
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-500">No scorecards yet</p>
          <p class="mt-1 text-xs text-gray-400">Create one to start recording scores.</p>
        </div>

        <!-- Empty state: search / filter returned no matches -->
        <div
          v-else-if="filteredScorecards.length === 0"
          class="flex flex-col items-center py-24 text-center"
        >
          <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-500">No scorecards match your search</p>
          <button
            class="mt-3 text-xs font-medium text-indigo-600 hover:text-indigo-800"
            @click="clearFilters"
          >
            Clear filters
          </button>
        </div>

        <!-- ── Card list ──────────────────────────────────────────────────── -->
        <div v-else class="animate-fade-in space-y-3">
          <div
            v-for="sc in filteredScorecards"
            :key="sc.id"
            class="group cursor-pointer rounded-2xl border border-gray-200 bg-white p-5 transition-all duration-150 hover:scale-[1.005] hover:border-gray-300 hover:shadow-md"
            @click="openScorecard(sc.id)"
          >
            <div class="flex items-start justify-between gap-4">

              <!-- Left: Archer + Session info -->
              <div class="min-w-0 flex-1 space-y-1">
                <p class="truncate font-semibold text-gray-900">
                  {{ sc.archer?.user?.name ?? '—' }}
                </p>
                <p class="truncate text-sm text-gray-600">
                  {{ sc.session?.title ?? '—' }}
                </p>
                <p class="flex items-center gap-1 text-xs text-gray-400">
                  <span v-if="sc.session?.started_at">
                    {{ formatDate(sc.session.started_at) }}
                  </span>
                  <span
                    v-if="sc.session?.started_at && sc.template"
                    class="text-gray-300"
                  >·</span>
                  <span v-if="sc.template">{{ sc.template.name }}</span>
                  <span
                    v-if="!sc.session?.started_at && !sc.template"
                    class="italic text-gray-300"
                  >No metadata</span>
                </p>
              </div>

              <!-- Right: Status badge + Score -->
              <div class="flex shrink-0 flex-col items-end gap-2">
                <StatusBadge :status="sc.status" />
                <div class="text-right">
                  <p class="text-xl font-bold tabular-nums leading-none text-gray-900">
                    {{ sc.total_score ?? '—' }}
                  </p>
                  <p class="mt-0.5 text-xs tabular-nums text-gray-400">
                    {{ sc.arrow_count != null ? `${sc.arrow_count} arrows` : '' }}
                  </p>
                </div>
              </div>

            </div>

            <!-- View indicator -->
            <div class="mt-4 flex items-center justify-end border-t border-gray-50 pt-3">
              <span class="text-xs font-medium text-gray-400 transition group-hover:text-indigo-600">
                View scorecard →
              </span>
            </div>

          </div>
        </div>

      </template>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout   from '@/Layouts/AppLayout.vue'
import BaseButton  from '@/Components/Base/BaseButton.vue'
import StatusBadge from '@/Components/Base/StatusBadge.vue'
import { useScorecardStore } from '@/Stores/module4/scorecard.js'

// ── Store ──────────────────────────────────────────────────────────────────────
const scorecardStore = useScorecardStore()

const loading    = computed(() => scorecardStore.loading)
const scorecards = computed(() => scorecardStore.scorecardList)

// ── Filter state ───────────────────────────────────────────────────────────────
const search       = ref('')
const statusFilter = ref('')

// ── Filtered list (client-side, zero backend changes) ─────────────────────────
const filteredScorecards = computed(() => {
  const q = search.value.trim().toLowerCase()
  return scorecards.value
    .filter(sc => {
      if (!q) return true
      const haystack = [
        sc.archer?.user?.name ?? '',
        sc.session?.title     ?? '',
      ].join(' ').toLowerCase()
      return haystack.includes(q)
    })
    .filter(sc =>
      statusFilter.value ? sc.status === statusFilter.value : true
    )
})

// ── Lifecycle ──────────────────────────────────────────────────────────────────
onMounted(async () => {
  await scorecardStore.fetchList()
})

// ── Helpers ────────────────────────────────────────────────────────────────────
function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
  })
}

function clearFilters() {
  search.value       = ''
  statusFilter.value = ''
}

// ── Navigation ─────────────────────────────────────────────────────────────────
function openScorecard(id) {
  router.visit(`/scoring/scorecards/${id}`)
}

function createScorecard() {
  router.visit('/scoring/scorecards/create')
}
</script>
