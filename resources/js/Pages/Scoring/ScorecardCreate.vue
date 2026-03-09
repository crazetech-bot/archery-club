<template>
  <AppLayout title="New Scorecard">
    <div class="mx-auto max-w-xl space-y-6 p-6">

      <!-- ── Header ──────────────────────────────────────────────────────────── -->
      <div>
        <a
          href="/scoring/scorecards"
          class="mb-4 inline-flex items-center gap-1 text-sm text-gray-400 hover:text-gray-700"
        >
          ← Scorecards
        </a>
        <h2 class="text-lg font-bold text-gray-900">New Scorecard</h2>
        <p class="mt-0.5 text-sm text-gray-400">Select a session, archer, and scoring template.</p>
      </div>

      <!-- ── Form ────────────────────────────────────────────────────────────── -->
      <BaseCard padding="p-6" class="animate-fade-in">
        <form class="space-y-5" @submit.prevent="submit">

          <!-- Session -->
          <BaseSelect
            v-model="form.training_session_id"
            label="Training Session"
            placeholder="Select a session…"
            :error="errors.training_session_id"
            required
          >
            <option v-for="s in sessions" :key="s.id" :value="s.id">
              {{ s.title }} — {{ formatDate(s.start_time) }}
            </option>
          </BaseSelect>

          <!-- Archer -->
          <BaseSelect
            v-model="form.archer_id"
            label="Archer"
            placeholder="Select an archer…"
            :error="errors.archer_id"
            required
          >
            <option v-for="a in archers" :key="a.id" :value="a.id">
              {{ a.name }}
            </option>
          </BaseSelect>

          <!-- Template -->
          <BaseSelect
            v-model="form.scoring_template_id"
            label="Scoring Template"
            placeholder="Select a template…"
            :error="errors.scoring_template_id"
            required
          >
            <option v-for="t in templates" :key="t.id" :value="t.id">
              {{ t.name }}<template v-if="t.type"> ({{ t.type }})</template>
            </option>
          </BaseSelect>

          <!-- Global error -->
          <p v-if="globalError" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ globalError }}
          </p>

          <!-- Actions -->
          <div class="flex items-center justify-between pt-2">
            <a
              href="/scoring/scorecards"
              class="text-sm text-gray-400 hover:text-gray-700"
            >
              Cancel
            </a>
            <BaseButton
              type="submit"
              size="lg"
              :loading="submitting"
              loading-text="Creating…"
            >
              Create Scorecard
            </BaseButton>
          </div>

        </form>
      </BaseCard>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout  from '@/Layouts/AppLayout.vue'
import BaseButton from '@/Components/Base/BaseButton.vue'
import BaseCard   from '@/Components/Base/BaseCard.vue'
import BaseSelect from '@/Components/Base/BaseSelect.vue'
import { useScorecardStore } from '@/Stores/module4/scorecard.js'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  sessions:  { type: Array, required: true },
  archers:   { type: Array, required: true },
  templates: { type: Array, required: true },
})

// ── Store ─────────────────────────────────────────────────────────────────────
const scorecardStore = useScorecardStore()

// ── State ─────────────────────────────────────────────────────────────────────
const form = reactive({
  training_session_id: '',
  archer_id:           '',
  scoring_template_id: '',
})

const errors      = reactive({})
const globalError = ref(null)
const submitting  = ref(false)

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
  })
}

// ── Submit ────────────────────────────────────────────────────────────────────
async function submit() {
  submitting.value  = true
  globalError.value = null
  Object.keys(errors).forEach(k => delete errors[k])

  try {
    const scorecard = await scorecardStore.create({
      training_session_id: form.training_session_id,
      archer_id:           form.archer_id,
      scoring_template_id: form.scoring_template_id,
    })

    router.visit(`/scoring/scorecards/${scorecard.id}`)
  } catch (err) {
    if (err.response?.status === 422) {
      const e = err.response.data.errors ?? {}
      Object.assign(errors, e)
    } else {
      globalError.value = 'Could not create scorecard. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}
</script>
