<template>
  <div class="min-h-screen bg-gray-50">

    <!-- ── Header ─────────────────────────────────────────────────────────── -->
    <header class="border-b border-gray-100 bg-white">
      <div class="mx-auto max-w-6xl px-4 py-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Reports</p>
            <h1 class="mt-0.5 text-xl font-bold text-gray-900">Score Progression</h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ report.archer.name }}</p>
          </div>

          <button
            class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 active:scale-95"
            @click="exportPdf"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export PDF
          </button>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-6">

      <!-- ── Filter bar ──────────────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <div class="flex flex-wrap items-end gap-3">

          <!-- Date from -->
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">From</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none"
            />
          </div>

          <!-- Date to -->
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">To</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none"
            />
          </div>

          <!-- Round type -->
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Round type</label>
            <select
              v-model="filters.round_type"
              class="rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none"
            >
              <option :value="null">All rounds</option>
              <option v-for="rt in roundTypes" :key="rt" :value="rt">{{ rt }}</option>
            </select>
          </div>

          <!-- Group by -->
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Group by</label>
            <div class="flex rounded-xl border border-gray-200 bg-white">
              <button
                v-for="opt in groupByOptions"
                :key="opt.value ?? 'none'"
                :class="[
                  'px-3 py-2 text-xs font-medium capitalize transition-all first:rounded-l-xl last:rounded-r-xl',
                  filters.group_by === opt.value
                    ? 'bg-gray-900 text-white'
                    : 'text-gray-500 hover:text-gray-800',
                ]"
                @click="filters.group_by = opt.value"
              >
                {{ opt.label }}
              </button>
            </div>
          </div>

          <!-- Apply -->
          <button
            :disabled="loadingReport"
            class="rounded-xl bg-gray-900 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-700 active:scale-95 disabled:opacity-50"
            @click="applyFilters"
          >
            {{ loadingReport ? 'Loading…' : 'Apply' }}
          </button>

          <!-- Reset -->
          <button
            class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50"
            @click="resetFilters"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- ── Summary stat cards ──────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <StatCard
          label="Sessions"
          :value="summary.total_sessions"
          sub-label="completed with scoring"
        />
        <StatCard
          label="Avg Score"
          :value="summary.avg_score"
          :delta="summary.improvement_rate"
        />
        <StatCard
          label="Best Score"
          :value="summary.best_score"
          value-class="text-yellow-500"
          :sub-label="bestSession ? bestSession.date : null"
        />
        <StatCard
          label="X Count"
          :value="summary.total_x_count"
          value-class="text-yellow-500"
          sub-label="inner gold"
        />
        <StatCard
          label="Total Arrows"
          :value="summary.total_arrows"
        />
      </div>

      <!-- ── Chart ───────────────────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
        <div class="mb-4 flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-gray-900">Score over time</h2>
            <p class="text-xs text-gray-400">
              {{ filters.group_by ? `Grouped by ${filters.group_by}` : 'Per session' }}
              · Dashed line = 3-session moving average
            </p>
          </div>

          <!-- Chart type toggle (line vs bar — future) -->
          <div class="flex rounded-xl border border-gray-100 bg-gray-50">
            <button
              v-for="mode in ['line']"
              :key="mode"
              class="rounded-xl px-3 py-1.5 text-xs font-medium capitalize bg-gray-900 text-white"
            >
              Line
            </button>
          </div>
        </div>

        <ScoreChart
          :data-points="report.data_points"
          :trend="report.trend"
          :group-by="filters.group_by"
          :loading="loadingReport"
          :height="320"
        />
      </div>

      <!-- ── Best / Worst session highlight ─────────────────────────────── -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <!-- Best session -->
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-yellow-100">
          <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-yellow-500">Best session</p>
          <template v-if="bestSession">
            <p class="text-3xl font-bold tabular-nums text-gray-900">{{ bestSession.total_score }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ bestSession.date }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
              <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                {{ bestSession.round_type ?? 'Training' }}
              </span>
              <span v-if="bestSession.distance" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                {{ bestSession.distance }}m
              </span>
              <span v-if="bestSession.x_count > 0" class="rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-700">
                {{ bestSession.x_count }}X
              </span>
            </div>
            <p class="mt-2 text-xs text-gray-400">
              {{ bestSession.ends_count }} ends · avg {{ bestSession.avg_per_end }}/end
            </p>
          </template>
          <p v-else class="text-sm text-gray-400">No data</p>
        </div>

        <!-- Worst session -->
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Lowest session</p>
          <template v-if="worstSession">
            <p class="text-3xl font-bold tabular-nums text-gray-400">{{ worstSession.total_score }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ worstSession.date }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
              <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                {{ worstSession.round_type ?? 'Training' }}
              </span>
              <span v-if="worstSession.distance" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                {{ worstSession.distance }}m
              </span>
            </div>
            <p class="mt-2 text-xs text-gray-400">
              {{ worstSession.ends_count }} ends · avg {{ worstSession.avg_per_end }}/end
            </p>
          </template>
          <p v-else class="text-sm text-gray-400">No data</p>
        </div>
      </div>

      <!-- ── By round type breakdown ─────────────────────────────────────── -->
      <div v-if="report.by_round_type?.length > 0" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
        <h2 class="mb-4 text-sm font-semibold text-gray-900">By round type</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="pb-2 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Round</th>
                <th class="pb-2 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Sessions</th>
                <th class="pb-2 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Avg score</th>
                <th class="pb-2 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Best score</th>
                <th class="pb-2 pl-4 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Avg bar</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="row in report.by_round_type" :key="row.round_type" class="group">
                <td class="py-3 font-medium text-gray-800">{{ row.round_type }}</td>
                <td class="py-3 text-right tabular-nums text-gray-500">{{ row.sessions_count }}</td>
                <td class="py-3 text-right tabular-nums font-semibold text-gray-900">{{ row.avg_score }}</td>
                <td class="py-3 text-right tabular-nums text-yellow-600 font-semibold">{{ row.best_score }}</td>
                <td class="py-3 pl-4">
                  <!-- Relative bar showing avg vs max avg across all round types -->
                  <div class="h-2 w-full max-w-[120px] overflow-hidden rounded-full bg-gray-100">
                    <div
                      class="h-full rounded-full bg-gray-900 transition-all duration-500"
                      :style="{ width: relativeBarWidth(row.avg_score) + '%' }"
                    />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Sessions data table ─────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <button
          class="flex w-full items-center justify-between px-5 py-4"
          @click="showTable = !showTable"
        >
          <h2 class="text-sm font-semibold text-gray-900">
            All sessions
            <span class="ml-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">
              {{ report.data_points.length }}
            </span>
          </h2>
          <svg
            :class="['h-4 w-4 text-gray-400 transition-transform', showTable ? 'rotate-180' : '']"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <div v-if="showTable" class="overflow-x-auto border-t border-gray-50">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50">
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Date</th>
                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Round</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Dist</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Score</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Ends</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">Avg/end</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">X</th>
                <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-400">10s</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="dp in [...report.data_points].reverse()"
                :key="dp.session_id ?? dp.period"
                class="hover:bg-gray-50"
              >
                <td class="px-5 py-3 tabular-nums text-gray-600">{{ dp.date ?? dp.label }}</td>
                <td class="px-5 py-3 text-gray-700">{{ dp.round_type ?? '—' }}</td>
                <td class="px-5 py-3 text-right tabular-nums text-gray-500">
                  {{ dp.distance ? dp.distance + 'm' : '—' }}
                </td>
                <td class="px-5 py-3 text-right tabular-nums font-bold text-gray-900">
                  {{ dp.total_score ?? dp.avg_score }}
                </td>
                <td class="px-5 py-3 text-right tabular-nums text-gray-500">{{ dp.ends_count ?? '—' }}</td>
                <td class="px-5 py-3 text-right tabular-nums text-gray-500">{{ dp.avg_per_end ?? '—' }}</td>
                <td class="px-5 py-3 text-right tabular-nums font-semibold text-yellow-500">{{ dp.x_count ?? 0 }}</td>
                <td class="px-5 py-3 text-right tabular-nums text-gray-500">{{ dp.ten_count ?? 0 }}</td>
              </tr>

              <!-- Empty row -->
              <tr v-if="report.data_points.length === 0">
                <td colspan="8" class="px-5 py-8 text-center text-sm text-gray-400">
                  No sessions match the current filters.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import StatCard   from '@/Components/Reports/StatCard.vue'
import ScoreChart from '@/Components/Reports/ScoreChart.vue'

// ── Props (Inertia) ───────────────────────────────────────────────────────────
const props = defineProps({
  /** Full ScoreProgressionResource payload */
  report: { type: Object, required: true },
  /** Archer summary { id, name, category } */
  archer: { type: Object, required: true },
  /** Available round types for the filter dropdown */
  roundTypes: { type: Array, default: () => [] },
})

// ── State ─────────────────────────────────────────────────────────────────────
const report       = ref(props.report)
const loadingReport = ref(false)
const showTable    = ref(false)

const groupByOptions = [
  { label: 'Session', value: null },
  { label: 'Week',    value: 'week' },
  { label: 'Month',   value: 'month' },
]

// Initialise filters from what the controller sent
const filters = ref({
  date_from:  report.value.filters.date_from,
  date_to:    report.value.filters.date_to,
  round_type: report.value.filters.round_type ?? null,
  group_by:   report.value.filters.group_by ?? null,
})

// ── Computed ──────────────────────────────────────────────────────────────────

const summary      = computed(() => report.value.summary)
const bestSession  = computed(() => report.value.best_session)
const worstSession = computed(() => report.value.worst_session)

const maxAvgScore = computed(() => {
  const scores = report.value.by_round_type?.map((r) => r.avg_score) ?? []
  return scores.length > 0 ? Math.max(...scores) : 1
})

function relativeBarWidth(avgScore) {
  return maxAvgScore.value > 0
    ? Math.round((avgScore / maxAvgScore.value) * 100)
    : 0
}

// ── Actions ───────────────────────────────────────────────────────────────────

async function applyFilters() {
  loadingReport.value = true
  try {
    const { data } = await axios.get(
      `/reports/archer/${props.archer.id}/score-progression/data`,
      { params: filters.value }
    )
    report.value = data
  } catch (err) {
    console.error('Failed to load report:', err)
    alert('Could not load report data. Please try again.')
  } finally {
    loadingReport.value = false
  }
}

function resetFilters() {
  filters.value = {
    date_from:  null,
    date_to:    null,
    round_type: null,
    group_by:   null,
  }
  applyFilters()
}

function exportPdf() {
  const params = new URLSearchParams({
    ...filters.value,
    archer_id: props.archer.id,
  })
  window.open(`/reports/archer/${props.archer.id}/score-progression/pdf?${params}`, '_blank')
}
</script>
