<template>
  <CoachLayout>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Back + header ───────────────────────────────────────────────────── -->
      <div>
        <Link href="/coach/archers" class="text-xs font-medium text-gray-400 hover:text-gray-700">
          ← My Archers
        </Link>
        <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gray-900 text-lg font-bold text-white">
              {{ initials(archer.name) }}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-900">{{ archer.name }}</h1>
                <span
                  v-if="archer.has_active_session"
                  class="flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700"
                >
                  <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500" />
                  Live Now
                </span>
              </div>
              <p class="mt-0.5 text-sm text-gray-400">{{ archer.category }}</p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <Link
              :href="`/reports/archer/${archer.id}`"
              class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50"
            >
              View Report
            </Link>
            <Link
              v-if="archer.has_active_session"
              href="/live/monitor"
              class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700"
            >
              Monitor Live
            </Link>
          </div>
        </div>
      </div>

      <!-- ── Stats row ───────────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Sessions</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ stats.total_sessions ?? 0 }}</p>
          <p class="mt-0.5 text-xs text-gray-400">total</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Avg Score</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ stats.avg_score ?? '—' }}</p>
          <p class="mt-0.5 text-xs text-gray-400">per session</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Best Score</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-gray-900">{{ stats.best_score ?? '—' }}</p>
          <p class="mt-0.5 text-xs text-gray-400">all time</p>
        </div>
        <div
          :class="[
            'rounded-2xl p-4 shadow-sm ring-1',
            stats.improvement_rate == null ? 'bg-white ring-gray-100' :
            stats.improvement_rate >= 0 ? 'bg-green-50 ring-green-100' : 'bg-red-50 ring-red-100',
          ]"
        >
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Trend</p>
          <p
            :class="[
              'mt-1 text-2xl font-bold tabular-nums',
              stats.improvement_rate == null ? 'text-gray-300' :
              stats.improvement_rate >= 0 ? 'text-green-600' : 'text-red-500',
            ]"
          >
            {{ stats.improvement_rate != null ? `${stats.improvement_rate >= 0 ? '+' : ''}${stats.improvement_rate}%` : '—' }}
          </p>
          <p class="mt-0.5 text-xs text-gray-400">recent trend</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- ── Recent sessions ────────────────────────────────────────────── -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Recent Sessions</h2>
            <p class="text-xs text-gray-400">Last {{ recentSessions.length }}</p>
          </div>

          <div class="space-y-2">
            <div
              v-for="session in recentSessions"
              :key="session.id"
              class="flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100"
            >
              <!-- Score -->
              <div
                :class="[
                  'flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white',
                  scoreColor(session.total_score, session.max_score),
                ]"
              >
                {{ session.total_score ?? '—' }}
              </div>

              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900">{{ session.round_type ?? 'Practice' }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(session.started_at) }}</p>
              </div>

              <div class="text-right">
                <p class="text-xs text-gray-400">{{ session.ends_count }} ends</p>
                <p v-if="session.x_count > 0" class="text-xs font-semibold text-yellow-500">
                  {{ session.x_count }}× X
                </p>
              </div>
            </div>

            <div v-if="recentSessions.length === 0" class="flex items-center justify-center rounded-2xl bg-white py-8 shadow-sm ring-1 ring-gray-100">
              <p class="text-sm text-gray-400">No sessions recorded yet.</p>
            </div>
          </div>

          <!-- Coach note form -->
          <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
            <h3 class="mb-3 text-sm font-semibold text-gray-900">Add Coach Note</h3>
            <form class="space-y-3" @submit.prevent="submitNote">
              <Textarea
                v-model="noteText"
                placeholder="Write a note about this archer's progress, technique, or goals…"
                :rows="3"
                :error="noteError"
              />
              <div class="flex justify-end">
                <Button variant="primary" type="submit" :loading="submittingNote" size="sm">
                  Add Note
                </Button>
              </div>
            </form>
          </div>

          <!-- Coach notes list -->
          <div v-if="notes.length > 0" class="space-y-2">
            <h3 class="text-sm font-semibold text-gray-900">Coach Notes</h3>
            <div
              v-for="note in notes"
              :key="note.id"
              class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100"
            >
              <p class="text-sm text-gray-700">{{ note.content }}</p>
              <p class="mt-2 text-xs text-gray-400">{{ formatDateTime(note.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- ── Right panel ─────────────────────────────────────────────────── -->
        <div class="space-y-4">

          <!-- Archer details -->
          <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-50 px-5 py-4">
              <h2 class="text-sm font-semibold text-gray-900">Details</h2>
            </div>
            <dl class="divide-y divide-gray-50">
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Category</dt>
                <dd class="text-xs font-medium text-gray-700">{{ archer.category }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Age</dt>
                <dd class="text-xs font-medium text-gray-700">{{ archer.age ?? '—' }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Hand</dt>
                <dd class="text-xs font-medium text-gray-700 capitalize">{{ archer.dominant_hand ?? '—' }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Member Since</dt>
                <dd class="text-xs font-medium text-gray-700">{{ formatDate(archer.created_at) }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Last Session</dt>
                <dd class="text-xs font-medium text-gray-700">
                  {{ archer.last_session_date ? formatDate(archer.last_session_date) : 'Never' }}
                </dd>
              </div>
            </dl>
          </div>

          <!-- Current equipment -->
          <div v-if="archer.current_equipment" class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-50 px-5 py-4">
              <h2 class="text-sm font-semibold text-gray-900">Current Equipment</h2>
            </div>
            <dl class="divide-y divide-gray-50">
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Bow</dt>
                <dd class="text-xs font-medium text-gray-700">{{ archer.current_equipment.bow_type }}</dd>
              </div>
              <div class="flex justify-between px-5 py-3">
                <dt class="text-xs text-gray-400">Draw Weight</dt>
                <dd class="text-xs font-medium text-gray-700">
                  {{ archer.current_equipment.draw_weight_lbs ? `${archer.current_equipment.draw_weight_lbs} lbs` : '—' }}
                </dd>
              </div>
            </dl>
          </div>

        </div>
      </div>

    </div>
  </CoachLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import CoachLayout from '@/Components/Layouts/CoachLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Textarea from '@/Components/Forms/Textarea.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  archer:         { type: Object, required: true },
  stats:          { type: Object, default: () => ({}) },
  recentSessions: { type: Array,  default: () => [] },
  notes:          { type: Array,  default: () => [] },
})

// ── Coach note ─────────────────────────────────────────────────────────────────

const noteText      = ref('')
const noteError     = ref(null)
const submittingNote = ref(false)

function submitNote() {
  if (!noteText.value.trim()) { noteError.value = 'Note cannot be empty.'; return }
  submittingNote.value = true
  noteError.value = null

  router.post(`/coach/archers/${props.archer.id}/notes`, { content: noteText.value }, {
    preserveScroll: true,
    onSuccess: () => { noteText.value = '' },
    onError: (err) => { noteError.value = err.content ?? 'Failed to save note.' },
    onFinish: () => { submittingNote.value = false },
  })
}

// ── Helpers ────────────────────────────────────────────────────────────────────

function initials(name) {
  return name.split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatDateTime(iso) {
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function scoreColor(score, max) {
  if (score == null || max == null) return 'bg-gray-300'
  const pct = score / max
  if (pct >= 0.85) return 'bg-green-500'
  if (pct >= 0.70) return 'bg-sky-500'
  if (pct >= 0.55) return 'bg-amber-400'
  return 'bg-gray-400'
}
</script>
