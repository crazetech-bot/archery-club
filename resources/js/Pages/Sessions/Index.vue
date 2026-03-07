<template>
  <AdminLayout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Club Admin</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Group Sessions</h1>
        </div>
        <button
          class="inline-flex items-center gap-2 rounded-2xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-gray-900/20 transition hover:bg-gray-700 active:scale-95"
          @click="showCreateModal = true"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          New Session
        </button>
      </div>

      <!-- ── Stats ────────────────────────────────────────────────────────── -->
      <div class="grid grid-cols-3 gap-3">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Total</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">{{ sessions.total }}</p>
          <p class="mt-0.5 text-xs text-gray-400">sessions scheduled</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-green-100">
          <p class="text-xs font-medium uppercase tracking-widest text-green-600">Completed</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-green-600">
            {{ sessions.data.filter(s => s.status === 'completed').length }}
          </p>
          <p class="mt-0.5 text-xs text-gray-400">this page</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Upcoming</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-gray-900">
            {{ sessions.data.filter(s => s.status === 'scheduled').length }}
          </p>
          <p class="mt-0.5 text-xs text-gray-400">this page</p>
        </div>
      </div>

      <!-- ── Table ────────────────────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-50 bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Session</th>
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Coach</th>
              <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Date</th>
              <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-400">Attended</th>
              <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-400">Status</th>
              <th class="px-5 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr
              v-for="session in sessions.data"
              :key="session.id"
              class="transition hover:bg-gray-50"
            >
              <td class="px-5 py-3">
                <p class="font-medium text-gray-900">{{ session.title }}</p>
                <p class="text-xs text-gray-400 capitalize">{{ session.type.replace('_', ' ') }}</p>
              </td>
              <td class="px-5 py-3 text-gray-600">{{ session.coach_name ?? '—' }}</td>
              <td class="px-5 py-3 text-gray-600">
                {{ formatDateTime(session.scheduled_at) }}
                <span class="ml-1 text-xs text-gray-400">{{ session.duration_minutes }}min</span>
              </td>
              <td class="px-5 py-3 text-center tabular-nums text-gray-700">
                <span class="font-semibold text-green-600">{{ session.present_count }}</span>
                <span class="text-gray-400"> / {{ session.attendances_count }}</span>
              </td>
              <td class="px-5 py-3 text-center">
                <span :class="statusClass(session.status)">{{ session.status }}</span>
              </td>
              <td class="px-5 py-3 text-right">
                <Link
                  :href="`/admin/sessions/${session.id}/attendance`"
                  class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition"
                >
                  Mark attendance
                </Link>
              </td>
            </tr>

            <tr v-if="sessions.data.length === 0">
              <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">
                No sessions yet. Create one to get started.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="sessions.last_page > 1" class="flex items-center justify-between border-t border-gray-50 px-5 py-3">
          <p class="text-xs text-gray-400">
            Showing {{ sessions.from }}–{{ sessions.to }} of {{ sessions.total }}
          </p>
          <div class="flex gap-1">
            <Link
              v-for="link in sessions.links"
              :key="link.label"
              :href="link.url ?? '#'"
              :class="[
                'rounded-lg px-3 py-1.5 text-xs font-medium transition',
                link.active ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100',
                !link.url ? 'pointer-events-none opacity-40' : '',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- ── Create session modal ─────────────────────────────────────────── -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0"
    >
      <div v-if="showCreateModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" @click.self="showCreateModal = false">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
          <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="text-base font-bold text-gray-900">New Group Session</h2>
            <button class="rounded-lg p-1.5 hover:bg-gray-100" @click="showCreateModal = false">
              <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <form class="space-y-4 p-6" @submit.prevent="createSession">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-700">Title</label>
                <input v-model="form.title" type="text" required
                  class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700">Type</label>
                <select v-model="form.type"
                  class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none">
                  <option value="general">General</option>
                  <option value="technique">Technique</option>
                  <option value="fitness">Fitness</option>
                  <option value="competition_prep">Competition Prep</option>
                  <option value="beginner">Beginner</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700">Coach</label>
                <select v-model="form.coach_id"
                  class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none">
                  <option value="">None</option>
                  <option v-for="coach in coaches" :key="coach.id" :value="coach.id">{{ coach.name }}</option>
                </select>
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700">Date &amp; Time</label>
                <input v-model="form.scheduled_at" type="datetime-local" required
                  class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-medium text-gray-700">Duration (min)</label>
                <input v-model.number="form.duration_minutes" type="number" min="15" max="480"
                  class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none" />
              </div>
              <div class="col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-700">Location</label>
                <input v-model="form.location" type="text"
                  class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none" />
              </div>
              <div class="col-span-2">
                <label class="mb-1 block text-xs font-medium text-gray-700">Notes</label>
                <textarea v-model="form.notes" rows="2"
                  class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none" />
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button type="button" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="showCreateModal = false">Cancel</button>
              <button type="submit" :disabled="creating"
                class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-50">
                {{ creating ? 'Creating…' : 'Create Session' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'

const props = defineProps({
  sessions: { type: Object, required: true },
  coaches:  { type: Array,  default: () => [] },
})

const showCreateModal = ref(false)
const creating        = ref(false)

const form = ref({
  title:            '',
  type:             'general',
  coach_id:         '',
  scheduled_at:     '',
  duration_minutes: 90,
  location:         '',
  notes:            '',
})

function createSession() {
  creating.value = true
  router.post('/admin/sessions', form.value, {
    onSuccess: () => {
      showCreateModal.value = false
      form.value = { title: '', type: 'general', coach_id: '', scheduled_at: '', duration_minutes: 90, location: '', notes: '' }
    },
    onFinish: () => { creating.value = false },
  })
}

function statusClass(status) {
  return {
    scheduled:  'rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700',
    completed:  'rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700',
    cancelled:  'rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-600',
  }[status] ?? 'rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500'
}

function formatDateTime(iso) {
  return new Date(iso).toLocaleString('en-GB', {
    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
  })
}
</script>
