<template>
  <CoachLayout>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Coach</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">My Archers</h1>
          <p class="mt-0.5 text-sm text-gray-500">{{ archers.length }} archer{{ archers.length !== 1 ? 's' : '' }} assigned</p>
        </div>
        <Link
          href="/live/monitor"
          class="inline-flex items-center gap-2 rounded-2xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-gray-900/20 transition hover:bg-gray-700"
        >
          <span v-if="activeSessions > 0" class="flex h-2 w-2 rounded-full bg-green-400" />
          {{ activeSessions > 0 ? `Monitor (${activeSessions} live)` : 'Live Monitor' }}
        </Link>
      </div>

      <!-- ── Filter + sort bar ───────────────────────────────────────────────── -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <Input
          v-model="search"
          placeholder="Search archers…"
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
          v-model="filterCategory"
          placeholder="All categories"
          :options="categoryOptions"
          class="sm:w-44"
        />

        <Select
          v-model="sortBy"
          :options="sortOptions"
          class="sm:w-48"
        />
      </div>

      <!-- ── Archers grid ────────────────────────────────────────────────────── -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="archer in filteredArchers"
          :key="archer.id"
          :href="`/coach/archers/${archer.id}`"
          class="group relative rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:ring-gray-200"
        >
          <!-- Live badge -->
          <div v-if="archer.has_active_session" class="absolute -top-2 right-3">
            <span class="flex items-center gap-1 rounded-full bg-green-500 px-2.5 py-0.5 text-xs font-semibold text-white shadow">
              <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white" />
              Live
            </span>
          </div>

          <div class="p-5">
            <div class="flex items-center gap-3">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-900 text-sm font-bold text-white">
                {{ initials(archer.name) }}
              </div>
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-gray-900">{{ archer.name }}</p>
                <p class="text-xs text-gray-400">{{ archer.category }}</p>
              </div>
              <svg class="ml-auto h-4 w-4 shrink-0 text-gray-300 transition group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>

            <!-- Stats row -->
            <div class="mt-4 grid grid-cols-3 gap-2 border-t border-gray-50 pt-4">
              <div class="text-center">
                <p class="text-xs text-gray-400">Avg</p>
                <p class="mt-0.5 text-sm font-bold tabular-nums text-gray-900">{{ archer.avg_score ?? '—' }}</p>
              </div>
              <div class="text-center">
                <p class="text-xs text-gray-400">Sessions</p>
                <p class="mt-0.5 text-sm font-bold tabular-nums text-gray-900">{{ archer.sessions_count ?? 0 }}</p>
              </div>
              <div class="text-center">
                <p class="text-xs text-gray-400">Trend</p>
                <p
                  v-if="archer.improvement_rate !== null"
                  :class="[
                    'mt-0.5 text-sm font-bold tabular-nums',
                    archer.improvement_rate >= 0 ? 'text-green-600' : 'text-red-500',
                  ]"
                >
                  {{ archer.improvement_rate >= 0 ? '+' : '' }}{{ archer.improvement_rate }}%
                </p>
                <p v-else class="mt-0.5 text-sm font-bold text-gray-300">—</p>
              </div>
            </div>

            <!-- Last session -->
            <p class="mt-3 text-xs text-gray-400">
              Last session:
              {{ archer.last_session_date ? formatDate(archer.last_session_date) : 'Never' }}
            </p>
          </div>
        </Link>

        <!-- Empty state -->
        <div
          v-if="filteredArchers.length === 0"
          class="col-span-full flex flex-col items-center justify-center rounded-2xl bg-white py-16 shadow-sm ring-1 ring-gray-100"
        >
          <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          <p class="text-sm font-medium text-gray-600">
            {{ search || filterCategory ? 'No archers match your filters' : 'No archers assigned yet' }}
          </p>
          <p v-if="!search && !filterCategory" class="mt-1 text-xs text-gray-400">
            Ask your club admin to assign archers to you.
          </p>
        </div>
      </div>

    </div>
  </CoachLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import CoachLayout from '@/Components/Layouts/CoachLayout.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  archers: { type: Array, default: () => [] },
})

// ── Filters ────────────────────────────────────────────────────────────────────

const search         = ref('')
const filterCategory = ref('')
const sortBy         = ref('name')

const categoryOptions = [
  { label: 'U12',    value: 'U12' },
  { label: 'U15',    value: 'U15' },
  { label: 'U18',    value: 'U18' },
  { label: 'U21',    value: 'U21' },
  { label: 'Senior', value: 'Senior' },
  { label: 'Master', value: 'Master' },
]

const sortOptions = [
  { label: 'Name (A–Z)',        value: 'name' },
  { label: 'Avg Score (High)',  value: 'avg_desc' },
  { label: 'Avg Score (Low)',   value: 'avg_asc' },
  { label: 'Last Session',      value: 'recent' },
  { label: 'Improvement',       value: 'improvement' },
]

const activeSessions = computed(() =>
  props.archers.filter((a) => a.has_active_session).length
)

const filteredArchers = computed(() => {
  let list = [...props.archers]

  // Filter
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter((a) => a.name.toLowerCase().includes(q))
  }
  if (filterCategory.value) {
    list = list.filter((a) => a.category === filterCategory.value)
  }

  // Sort
  switch (sortBy.value) {
    case 'avg_desc':
      list.sort((a, b) => (b.avg_score ?? 0) - (a.avg_score ?? 0)); break
    case 'avg_asc':
      list.sort((a, b) => (a.avg_score ?? 0) - (b.avg_score ?? 0)); break
    case 'recent':
      list.sort((a, b) => {
        const da = a.last_session_date ? new Date(a.last_session_date) : 0
        const db = b.last_session_date ? new Date(b.last_session_date) : 0
        return db - da
      }); break
    case 'improvement':
      list.sort((a, b) => (b.improvement_rate ?? -Infinity) - (a.improvement_rate ?? -Infinity)); break
    default:
      list.sort((a, b) => a.name.localeCompare(b.name))
  }

  return list
})

// ── Helpers ────────────────────────────────────────────────────────────────────

function initials(name) {
  return name.split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
}
</script>
