<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────── -->
      <div class="flex items-center gap-3">
        <Link href="/archer/equipment" class="rounded-xl border border-gray-200 p-2 hover:bg-gray-50 transition">
          <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Equipment</p>
          <h1 class="mt-0.5 text-xl font-bold text-gray-900">{{ setup.name }}</h1>
          <p class="text-xs text-gray-400 capitalize">{{ setup.bow_brand }} {{ setup.bow_model }} · {{ setup.bow_type }}</p>
        </div>
        <span v-if="setup.is_current" class="ml-2 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">
          Current
        </span>
      </div>

      <!-- ── Next due alert ────────────────────────────────────────────── -->
      <div
        v-if="nextDue"
        :class="[
          'flex items-start gap-3 rounded-2xl border p-4',
          nextDue.is_overdue
            ? 'border-red-200 bg-red-50'
            : 'border-yellow-200 bg-yellow-50',
        ]"
      >
        <svg
          :class="['mt-0.5 h-5 w-5 shrink-0', nextDue.is_overdue ? 'text-red-500' : 'text-yellow-500']"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <p :class="['text-sm font-semibold', nextDue.is_overdue ? 'text-red-800' : 'text-yellow-800']">
            {{ nextDue.is_overdue ? 'Maintenance overdue' : 'Maintenance due soon' }}
          </p>
          <p :class="['text-xs mt-0.5', nextDue.is_overdue ? 'text-red-600' : 'text-yellow-700']">
            {{ nextDue.description }} — due {{ formatDate(nextDue.next_due_at) }}
          </p>
        </div>
      </div>

      <!-- ── Log a new entry ───────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <button
          class="flex w-full items-center justify-between px-5 py-4 text-sm font-semibold text-gray-900"
          @click="showForm = !showForm"
        >
          Log Maintenance
          <svg :class="['h-4 w-4 text-gray-400 transition-transform', showForm ? 'rotate-180' : '']"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>

        <form v-if="showForm" class="border-t border-gray-50 p-5 space-y-4" @submit.prevent="submit">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-700">Type</label>
              <select v-model="form.type"
                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-gray-400 focus:outline-none">
                <option value="check">Check</option>
                <option value="repair">Repair</option>
                <option value="replacement">Replacement</option>
                <option value="tuning">Tuning</option>
                <option value="cleaning">Cleaning</option>
              </select>
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-700">Performed at</label>
              <input v-model="form.performed_at" type="date" required
                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-gray-400 focus:outline-none" />
            </div>
            <div class="col-span-2">
              <label class="mb-1 block text-xs font-medium text-gray-700">Description</label>
              <input v-model="form.description" type="text" required maxlength="255"
                placeholder="e.g. Replaced string, tuned limb bolts"
                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-gray-400 focus:outline-none" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-700">Performed by</label>
              <input v-model="form.performed_by" type="text" maxlength="255"
                placeholder="Name or shop"
                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-gray-400 focus:outline-none" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-700">Cost (£)</label>
              <input v-model.number="form.cost" type="number" min="0" step="0.01"
                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-gray-400 focus:outline-none" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-700">Next due</label>
              <input v-model="form.next_due_at" type="date"
                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-gray-400 focus:outline-none" />
            </div>
            <div>
              <!-- spacer -->
            </div>
            <div class="col-span-2">
              <label class="mb-1 block text-xs font-medium text-gray-700">Additional details</label>
              <textarea v-model="form.details" rows="2" maxlength="2000"
                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-gray-400 focus:outline-none" />
            </div>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="showForm = false">Cancel</button>
            <button type="submit" :disabled="submitting"
              class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-50">
              {{ submitting ? 'Saving…' : 'Add Record' }}
            </button>
          </div>
        </form>
      </div>

      <!-- ── Log history ───────────────────────────────────────────────── -->
      <div>
        <h2 class="mb-3 text-sm font-semibold text-gray-900">Maintenance History</h2>

        <div v-if="logs.length > 0" class="space-y-3">
          <div
            v-for="log in logs"
            :key="log.id"
            :class="[
              'rounded-2xl bg-white p-4 shadow-sm ring-1 transition',
              log.is_overdue ? 'ring-red-200' : 'ring-gray-100',
            ]"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <span :class="typeClass(log.type)">{{ log.type }}</span>
                  <p class="text-sm font-semibold text-gray-900">{{ log.description }}</p>
                </div>
                <p v-if="log.details" class="mt-1 text-xs text-gray-500">{{ log.details }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-400">
                  <span>{{ formatDate(log.performed_at) }}</span>
                  <span v-if="log.performed_by">by {{ log.performed_by }}</span>
                  <span v-if="log.cost" class="font-medium text-gray-600">£{{ log.cost.toFixed(2) }}</span>
                  <span
                    v-if="log.next_due_at"
                    :class="log.is_overdue ? 'text-red-500 font-semibold' : 'text-gray-400'"
                  >
                    Next: {{ formatDate(log.next_due_at) }}{{ log.is_overdue ? ' (overdue)' : '' }}
                  </span>
                </div>
              </div>

              <button
                class="shrink-0 rounded-lg p-1.5 text-gray-300 transition hover:bg-red-50 hover:text-red-500"
                @click="deleteLog(log.id)"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center rounded-2xl bg-white py-14 shadow-sm ring-1 ring-gray-100">
          <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <p class="text-sm font-medium text-gray-600">No maintenance records yet</p>
          <p class="mt-1 text-xs text-gray-400">Log a check or repair above to start tracking.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layouts/AppLayout.vue'

const props = defineProps({
  setup:   { type: Object, required: true },
  logs:    { type: Array,  default: () => [] },
  nextDue: { type: Object, default: null },
})

const showForm  = ref(false)
const submitting = ref(false)

const form = ref({
  type:         'check',
  description:  '',
  details:      '',
  cost:         '',
  performed_at: new Date().toISOString().slice(0, 10),
  next_due_at:  '',
  performed_by: '',
})

function submit() {
  submitting.value = true
  router.post(`/archer/equipment/${props.setup.id}/maintenance`, form.value, {
    onSuccess: () => {
      showForm.value = false
      form.value = { type: 'check', description: '', details: '', cost: '', performed_at: new Date().toISOString().slice(0, 10), next_due_at: '', performed_by: '' }
    },
    onFinish: () => { submitting.value = false },
  })
}

function deleteLog(id) {
  if (!confirm('Delete this maintenance record?')) return
  router.delete(`/archer/equipment/${props.setup.id}/maintenance/${id}`)
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function typeClass(type) {
  const map = {
    check:       'rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700',
    repair:      'rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-700',
    replacement: 'rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-600',
    tuning:      'rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700',
    cleaning:    'rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600',
  }
  return map[type] ?? 'rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500'
}
</script>
