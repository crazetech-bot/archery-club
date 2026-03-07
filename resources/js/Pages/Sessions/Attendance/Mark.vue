<template>
  <AdminLayout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────── -->
      <div class="flex items-center gap-3">
        <Link href="/admin/sessions" class="rounded-xl border border-gray-200 p-2 hover:bg-gray-50 transition">
          <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Attendance</p>
          <h1 class="mt-0.5 text-xl font-bold text-gray-900">{{ session.title }}</h1>
          <p class="text-xs text-gray-400">
            {{ formatDateTime(session.scheduled_at) }}
            <span v-if="session.location"> · {{ session.location }}</span>
            <span v-if="session.coach_name"> · {{ session.coach_name }}</span>
          </p>
        </div>
      </div>

      <!-- ── Summary chips ─────────────────────────────────────────────── -->
      <div class="flex flex-wrap gap-2">
        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
          {{ counts.present }} present
        </span>
        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-600">
          {{ counts.absent }} absent
        </span>
        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
          {{ counts.late }} late
        </span>
        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
          {{ counts.excused }} excused
        </span>
        <span class="ml-auto rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">
          {{ counts.unmarked }} unmarked
        </span>
      </div>

      <!-- ── Quick-mark all ────────────────────────────────────────────── -->
      <div class="flex items-center gap-2">
        <span class="text-xs font-medium text-gray-500">Mark all as:</span>
        <button
          v-for="status in statuses"
          :key="status"
          :class="quickMarkClass(status)"
          @click="markAll(status)"
        >
          {{ statusLabel(status) }}
        </button>
      </div>

      <!-- ── Archer list ───────────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 divide-y divide-gray-50">
        <div
          v-for="archer in records"
          :key="archer.id"
          class="flex items-center gap-4 px-5 py-4"
        >
          <!-- Avatar -->
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-900 text-sm font-bold text-white">
            {{ initials(archer.name) }}
          </div>

          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ archer.name }}</p>
            <p class="text-xs text-gray-400">{{ archer.category }}</p>
          </div>

          <!-- Status toggle buttons -->
          <div class="flex gap-1">
            <button
              v-for="status in statuses"
              :key="status"
              :class="[
                'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                archer.status === status
                  ? activeStatusClass(status)
                  : 'border border-gray-200 text-gray-500 hover:bg-gray-50',
              ]"
              @click="setStatus(archer, status)"
            >
              {{ statusLabel(status) }}
            </button>
          </div>

          <!-- Notes input (shown when status is set) -->
          <input
            v-if="archer.status"
            v-model="archer.notes"
            type="text"
            placeholder="Note…"
            maxlength="200"
            class="w-40 rounded-xl border border-gray-200 px-3 py-1.5 text-xs text-gray-700 placeholder-gray-300 focus:border-gray-400 focus:outline-none"
          />
        </div>

        <div v-if="archers.length === 0" class="px-5 py-12 text-center text-sm text-gray-400">
          No archers in the system yet.
        </div>
      </div>

      <!-- ── Save ──────────────────────────────────────────────────────── -->
      <div class="flex justify-end gap-3">
        <Link href="/admin/sessions"
          class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
          Cancel
        </Link>
        <button
          :disabled="saving || markedCount === 0"
          class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-40 transition"
          @click="save"
        >
          {{ saving ? 'Saving…' : `Save Attendance (${markedCount})` }}
        </button>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'

const props = defineProps({
  session: { type: Object, required: true },
  archers: { type: Array,  default: () => [] },
})

const statuses = ['present', 'absent', 'late', 'excused']
const saving   = ref(false)

// Local mutable copy of archers with status + notes
const records = ref(props.archers.map((a) => ({ ...a })))

const counts = computed(() => {
  const c = { present: 0, absent: 0, late: 0, excused: 0, unmarked: 0 }
  for (const r of records.value) {
    if (r.status) c[r.status]++
    else c.unmarked++
  }
  return c
})

const markedCount = computed(() => records.value.filter((r) => r.status).length)

function setStatus(archer, status) {
  archer.status = archer.status === status ? null : status
}

function markAll(status) {
  records.value.forEach((r) => { r.status = status })
}

function save() {
  saving.value = true
  const markedRecords = records.value
    .filter((r) => r.status)
    .map((r) => ({ archer_id: r.id, status: r.status, notes: r.notes ?? null }))

  router.post(`/admin/sessions/${props.session.id}/attendance`, { records: markedRecords }, {
    onFinish: () => { saving.value = false },
  })
}

function statusLabel(status) {
  return { present: 'Present', absent: 'Absent', late: 'Late', excused: 'Excused' }[status]
}

function activeStatusClass(status) {
  return {
    present: 'bg-green-600 text-white',
    absent:  'bg-red-500 text-white',
    late:    'bg-yellow-500 text-white',
    excused: 'bg-gray-400 text-white',
  }[status]
}

function quickMarkClass(status) {
  return [
    'rounded-lg px-3 py-1.5 text-xs font-semibold border transition',
    {
      present: 'border-green-200 text-green-700 hover:bg-green-50',
      absent:  'border-red-200 text-red-600 hover:bg-red-50',
      late:    'border-yellow-200 text-yellow-700 hover:bg-yellow-50',
      excused: 'border-gray-200 text-gray-600 hover:bg-gray-50',
    }[status],
  ]
}

function initials(name) {
  return (name ?? '?').split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
}

function formatDateTime(iso) {
  return new Date(iso).toLocaleString('en-GB', {
    weekday: 'short', day: 'numeric', month: 'short',
    hour: '2-digit', minute: '2-digit',
  })
}
</script>
