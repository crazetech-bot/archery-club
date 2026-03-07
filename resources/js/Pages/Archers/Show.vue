<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <Link href="/admin/archers" class="text-xs font-medium text-gray-400 hover:text-gray-700">
            &larr; Back to Archers
          </Link>
          <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ archer.name }}</h1>
          <div class="mt-1 flex flex-wrap items-center gap-2">
            <span v-if="archer.bow_type" :class="bowBadge(archer.bow_type)" class="rounded-full px-2 py-0.5 text-xs font-medium">
              {{ archer.bow_type }}
            </span>
            <span v-if="archer.experience_level" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
              {{ archer.experience_level }}
            </span>
            <span v-if="archer.category" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
              {{ archer.category }}
            </span>
            <span :class="archer.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'"
                  class="rounded-full px-2 py-0.5 text-xs font-medium">
              {{ archer.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
        <Link :href="`/admin/archers/${archer.id}/edit`"
              class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
          Edit
        </Link>
      </div>

      <!-- Profile details -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Personal</h2>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500">Gender</dt><dd class="font-medium text-gray-900 capitalize">{{ archer.gender ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Age</dt><dd class="font-medium text-gray-900">{{ archer.age ? archer.age + ' yrs' : '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Dominant Hand</dt><dd class="font-medium text-gray-900 capitalize">{{ archer.dominant_hand ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ archer.phone ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Coach</dt><dd class="font-medium text-gray-900">{{ archer.coach_name ?? '—' }}</dd></div>
          </dl>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
          <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Current Equipment</h2>
          <template v-if="archer.equipment">
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between"><dt class="text-gray-500">Bow Type</dt><dd class="font-medium text-gray-900">{{ archer.equipment.bow_type }}</dd></div>
              <div class="flex justify-between"><dt class="text-gray-500">Brand</dt><dd class="font-medium text-gray-900">{{ archer.equipment.bow_brand ?? '—' }}</dd></div>
              <div class="flex justify-between"><dt class="text-gray-500">Model</dt><dd class="font-medium text-gray-900">{{ archer.equipment.bow_model ?? '—' }}</dd></div>
              <div class="flex justify-between"><dt class="text-gray-500">Draw Weight</dt><dd class="font-medium text-gray-900">{{ archer.equipment.draw_weight_lbs ? archer.equipment.draw_weight_lbs + ' lbs' : '—' }}</dd></div>
            </dl>
          </template>
          <p v-else class="text-sm text-gray-400">No equipment setup recorded.</p>
        </div>
      </div>

      <!-- Recent sessions -->
      <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Recent Sessions</h2>
        <div v-if="recentSessions.length" class="divide-y divide-gray-50">
          <div v-for="s in recentSessions" :key="s.id" class="flex items-center justify-between py-2.5">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ s.round_type ?? 'Practice' }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(s.started_at) }} · {{ s.distance_metres ? s.distance_metres + 'm' : '' }}</p>
            </div>
            <span class="text-sm font-semibold tabular-nums text-gray-700">
              {{ s.total_score ?? '—' }}<span v-if="s.max_score" class="font-normal text-gray-400"> / {{ s.max_score }}</span>
            </span>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400">No sessions recorded.</p>
      </div>

      <!-- Competition results -->
      <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Competition Results</h2>
        <div v-if="competitionResults.length" class="divide-y divide-gray-50">
          <div v-for="r in competitionResults" :key="r.competed_at" class="flex items-center justify-between py-2.5">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ r.competition_name }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(r.competed_at) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold tabular-nums text-gray-700">{{ r.score }} / {{ r.max_score }}</p>
              <p v-if="r.placing" class="text-xs text-gray-400">{{ ordinal(r.placing) }} place</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400">No competition results.</p>
      </div>

      <!-- Coach notes -->
      <div v-if="archer.coach_notes?.length" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400">Coach Notes</h2>
        <div class="space-y-3">
          <div v-for="note in archer.coach_notes" :key="note.id" class="rounded-xl bg-gray-50 p-3">
            <p class="text-sm text-gray-700">{{ note.note }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ note.coach_name }} · {{ formatDate(note.created_at) }}</p>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

defineProps({
  archer:              { type: Object, required: true },
  recentSessions:      { type: Array,  default: () => [] },
  competitionResults:  { type: Array,  default: () => [] },
})

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function ordinal(n) {
  const s = ['th','st','nd','rd'], v = n % 100
  return n + (s[(v - 20) % 10] || s[v] || s[0])
}

function bowBadge(bow) {
  const map = {
    Recurve:  'bg-sky-50 text-sky-700',
    Compound: 'bg-purple-50 text-purple-700',
    Barebow:  'bg-amber-50 text-amber-700',
    Longbow:  'bg-green-50 text-green-700',
  }
  return map[bow] ?? 'bg-gray-100 text-gray-600'
}
</script>
