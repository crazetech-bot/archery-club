<template>
  <AppLayout>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Reports</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Leaderboard</h1>
          <p v-if="data.generated_at" class="mt-0.5 text-xs text-gray-400">
            Updated {{ formatRelative(data.generated_at) }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
            {{ data.total_archers }} archers
          </span>
        </div>
      </div>

      <!-- ── Filters ──────────────────────────────────────────────────────── -->
      <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
        <!-- Round type -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-500">Round</label>
          <select v-model="activeFilters.round_type"
            class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs text-gray-700 focus:border-gray-400 focus:outline-none"
            @change="fetchData">
            <option value="">All rounds</option>
            <option v-for="rt in roundTypes" :key="rt" :value="rt">{{ rt }}</option>
          </select>
        </div>

        <!-- Category -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-500">Category</label>
          <select v-model="activeFilters.category"
            class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs text-gray-700 focus:border-gray-400 focus:outline-none"
            @change="fetchData">
            <option value="">All categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>

        <!-- Period -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-medium text-gray-500">Period</label>
          <div class="flex rounded-xl border border-gray-200 overflow-hidden">
            <button
              v-for="p in periods"
              :key="p.value"
              :class="[
                'px-3 py-1.5 text-xs font-medium transition',
                activeFilters.period === p.value
                  ? 'bg-gray-900 text-white'
                  : 'text-gray-500 hover:bg-gray-50',
              ]"
              @click="activeFilters.period = p.value; fetchData()"
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <button
          v-if="activeFilters.round_type || activeFilters.category || activeFilters.period !== '90'"
          class="ml-auto text-xs font-medium text-gray-400 hover:text-gray-700"
          @click="resetFilters"
        >
          Clear filters
        </button>

        <div v-if="loading" class="ml-auto">
          <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
        </div>
      </div>

      <!-- ── Top 3 podium ─────────────────────────────────────────────── -->
      <div v-if="data.entries.length >= 3" class="grid grid-cols-3 gap-3">
        <!-- 2nd -->
        <div class="flex flex-col items-center justify-end rounded-2xl bg-white py-6 shadow-sm ring-1 ring-gray-100">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-200 text-lg font-bold text-gray-500">
            {{ initials(data.entries[1].name) }}
          </div>
          <div class="mt-3 text-center">
            <p class="text-xs font-semibold text-gray-400">2nd</p>
            <p class="text-sm font-bold text-gray-900">{{ data.entries[1].name }}</p>
            <p class="mt-0.5 text-xl font-bold tabular-nums text-gray-900">{{ data.entries[1].best_score }}</p>
            <p class="text-xs text-gray-400">best score</p>
          </div>
        </div>

        <!-- 1st -->
        <div class="flex flex-col items-center justify-end rounded-2xl bg-gray-900 py-6 shadow-lg shadow-gray-900/20">
          <div class="flex h-14 w-14 items-center justify-center rounded-full bg-yellow-400 text-lg font-bold text-yellow-900">
            {{ initials(data.entries[0].name) }}
          </div>
          <div class="mt-3 text-center">
            <p class="text-xs font-semibold text-yellow-400">1st</p>
            <p class="text-sm font-bold text-white">{{ data.entries[0].name }}</p>
            <p class="mt-0.5 text-2xl font-bold tabular-nums text-white">{{ data.entries[0].best_score }}</p>
            <p class="text-xs text-gray-400">best score</p>
          </div>
        </div>

        <!-- 3rd -->
        <div class="flex flex-col items-center justify-end rounded-2xl bg-white py-6 shadow-sm ring-1 ring-gray-100">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-200 text-lg font-bold text-gray-500">
            {{ initials(data.entries[2].name) }}
          </div>
          <div class="mt-3 text-center">
            <p class="text-xs font-semibold text-gray-400">3rd</p>
            <p class="text-sm font-bold text-gray-900">{{ data.entries[2].name }}</p>
            <p class="mt-0.5 text-xl font-bold tabular-nums text-gray-900">{{ data.entries[2].best_score }}</p>
            <p class="text-xs text-gray-400">best score</p>
          </div>
        </div>
      </div>

      <!-- ── Full rankings table ──────────────────────────────────────── -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-50 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400 w-12">#</th>
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Archer</th>
              <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Best</th>
              <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Avg</th>
              <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">X</th>
              <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Sessions</th>
              <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Trend</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr
              v-for="entry in data.entries"
              :key="entry.archer_id"
              class="transition hover:bg-gray-50"
            >
              <!-- Rank -->
              <td class="px-5 py-3">
                <span :class="rankClass(entry.rank)">{{ entry.rank }}</span>
              </td>

              <!-- Name -->
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <div :class="[
                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                    entry.rank === 1 ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-200 text-gray-600',
                  ]">
                    {{ initials(entry.name) }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900">{{ entry.name }}</p>
                    <p v-if="entry.category" class="text-xs text-gray-400">{{ entry.category }}</p>
                  </div>
                </div>
              </td>

              <!-- Best score -->
              <td class="px-5 py-3 text-right font-bold tabular-nums text-gray-900">{{ entry.best_score ?? '—' }}</td>

              <!-- Avg score -->
              <td class="px-5 py-3 text-right tabular-nums text-gray-600">{{ entry.avg_score ?? '—' }}</td>

              <!-- X count -->
              <td class="px-5 py-3 text-right tabular-nums font-semibold text-yellow-600">{{ entry.total_x }}</td>

              <!-- Sessions -->
              <td class="px-5 py-3 text-right tabular-nums text-gray-400">{{ entry.session_count }}</td>

              <!-- Improvement trend -->
              <td class="px-5 py-3 text-right">
                <span v-if="entry.improvement !== null"
                  :class="[
                    'inline-flex items-center gap-0.5 text-xs font-semibold',
                    entry.improvement >= 0 ? 'text-green-600' : 'text-red-500',
                  ]"
                >
                  <svg
                    :class="['h-3 w-3', entry.improvement < 0 ? 'rotate-180' : '']"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                  </svg>
                  {{ Math.abs(entry.improvement) }}%
                </span>
                <span v-else class="text-xs text-gray-300">—</span>
              </td>
            </tr>

            <tr v-if="data.entries.length === 0">
              <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">
                No data for the selected filters. Try a wider time period or different round type.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

const props = defineProps({
  initialData: { type: Object, required: true },
  filters:     { type: Object, default: () => ({}) },
  roundTypes:  { type: Array,  default: () => [] },
  categories:  { type: Array,  default: () => [] },
})

const data    = ref(props.initialData)
const loading = ref(false)

const activeFilters = reactive({
  round_type: props.filters.round_type ?? '',
  category:   props.filters.category   ?? '',
  period:     props.filters.period      ?? '90',
})

const periods = [
  { label: '30d',      value: '30' },
  { label: '90d',      value: '90' },
  { label: '1 year',   value: '365' },
  { label: 'All time', value: '0' },
]

async function fetchData() {
  loading.value = true
  try {
    const { data: result } = await axios.get('/reports/leaderboard/data', {
      params: {
        round_type: activeFilters.round_type || undefined,
        category:   activeFilters.category   || undefined,
        period:     activeFilters.period,
      },
    })
    data.value = result
  } catch (e) {
    console.error('Leaderboard fetch failed:', e)
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  activeFilters.round_type = ''
  activeFilters.category   = ''
  activeFilters.period     = '90'
  fetchData()
}

function initials(name) {
  return (name ?? '?').split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
}

function rankClass(rank) {
  if (rank === 1) return 'inline-flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 text-xs font-bold text-yellow-900'
  if (rank === 2) return 'inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-gray-700'
  if (rank === 3) return 'inline-flex h-6 w-6 items-center justify-center rounded-full bg-orange-200 text-xs font-bold text-orange-800'
  return 'text-xs font-semibold tabular-nums text-gray-400'
}

function formatRelative(iso) {
  const diff = Date.now() - new Date(iso).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1)  return 'just now'
  if (mins < 60) return `${mins}m ago`
  return `${Math.floor(mins / 60)}h ago`
}
</script>
