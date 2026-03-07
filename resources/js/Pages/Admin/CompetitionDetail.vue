<template>
  <AppLayout :title="competition.name">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Back link -->
      <a
        href="#"
        @click.prevent="router.get(route('admin.competitions.index'))"
        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900 mb-6"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Competitions
      </a>

      <!-- Competition header -->
      <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ competition.name }}</h1>
            <p class="text-sm text-gray-500">
              {{ formatDate(competition.date) }}
              <span v-if="competition.location"> · {{ competition.location }}</span>
            </p>
          </div>
          <div class="flex flex-wrap gap-2">
            <span v-if="competition.round_type" class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full">
              {{ competition.round_type }}
            </span>
            <span v-if="competition.distance_metres" class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full">
              {{ competition.distance_metres }}m
            </span>
          </div>
        </div>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl">
        {{ $page.props.flash.success }}
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Results table -->
        <div class="lg:col-span-2">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-900">Results</h2>
            <button
              @click="showAddResult = true"
              class="text-sm text-gray-900 font-medium hover:underline"
            >
              + Add result
            </button>
          </div>

          <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="text-left px-5 py-3 font-medium text-gray-600 w-12">#</th>
                  <th class="text-left px-5 py-3 font-medium text-gray-600">Archer</th>
                  <th class="text-left px-5 py-3 font-medium text-gray-600">Category</th>
                  <th class="text-right px-5 py-3 font-medium text-gray-600">Score</th>
                  <th class="text-right px-5 py-3 font-medium text-gray-600">%</th>
                  <th class="px-5 py-3"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-if="results.length === 0">
                  <td colspan="6" class="text-center py-10 text-gray-400">No results recorded yet.</td>
                </tr>
                <tr
                  v-for="result in results"
                  :key="result.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-5 py-3">
                    <!-- Medal -->
                    <span v-if="result.placing === 1" class="text-lg">🥇</span>
                    <span v-else-if="result.placing === 2" class="text-lg">🥈</span>
                    <span v-else-if="result.placing === 3" class="text-lg">🥉</span>
                    <span v-else class="text-gray-500 font-medium">{{ result.placing ?? '—' }}</span>
                  </td>
                  <td class="px-5 py-3 font-medium text-gray-900">{{ result.archer_name }}</td>
                  <td class="px-5 py-3 text-gray-500">{{ result.category ?? '—' }}</td>
                  <td class="px-5 py-3 text-right font-medium text-gray-900">
                    {{ result.score }}
                    <span v-if="result.max_score" class="text-gray-400 font-normal">/ {{ result.max_score }}</span>
                  </td>
                  <td class="px-5 py-3 text-right text-gray-500">
                    {{ result.max_score ? Math.round((result.score / result.max_score) * 100) + '%' : '—' }}
                  </td>
                  <td class="px-5 py-3 text-right">
                    <button
                      @click="openEditResult(result)"
                      class="text-xs text-gray-400 hover:text-gray-700 font-medium"
                    >
                      Edit
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Stats sidebar -->
        <div class="space-y-4">
          <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Summary</h3>
            <dl class="space-y-3">
              <div class="flex justify-between text-sm">
                <dt class="text-gray-500">Participants</dt>
                <dd class="font-medium text-gray-900">{{ results.length }}</dd>
              </div>
              <div v-if="results.length" class="flex justify-between text-sm">
                <dt class="text-gray-500">Top Score</dt>
                <dd class="font-medium text-gray-900">{{ topResult?.score }}</dd>
              </div>
              <div v-if="results.length" class="flex justify-between text-sm">
                <dt class="text-gray-500">Average</dt>
                <dd class="font-medium text-gray-900">{{ averageScore }}</dd>
              </div>
              <div v-if="results.length" class="flex justify-between text-sm">
                <dt class="text-gray-500">Winner</dt>
                <dd class="font-medium text-gray-900">{{ topResult?.archer_name }}</dd>
              </div>
            </dl>
          </div>

          <!-- Score distribution bar chart -->
          <div v-if="results.length >= 2" class="bg-white border border-gray-200 rounded-2xl p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Score Distribution</h3>
            <div class="space-y-2">
              <div v-for="result in sortedResults" :key="result.id">
                <div class="flex justify-between text-xs text-gray-500 mb-0.5">
                  <span class="truncate max-w-[100px]">{{ result.archer_name.split(' ')[0] }}</span>
                  <span>{{ result.score }}</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-gray-900 rounded-full"
                    :style="{ width: barWidth(result.score) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add / Edit result modal -->
      <div v-if="showAddResult || editingResult" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-5">
            {{ editingResult ? 'Edit Result' : 'Add Result' }}
          </h2>

          <form @submit.prevent="submitResult">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Archer</label>
              <select
                v-model="resultForm.archer_id"
                required
                :disabled="!!editingResult"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 disabled:bg-gray-50"
              >
                <option :value="null">Select archer</option>
                <option v-for="a in archers" :key="a.id" :value="a.id">{{ a.name }}</option>
              </select>
              <p v-if="resultErrors.archer_id" class="text-red-500 text-xs mt-1">{{ resultErrors.archer_id }}</p>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <input
                v-model="resultForm.category"
                type="text"
                placeholder="e.g. Senior Recurve"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
              />
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Score</label>
                <input
                  v-model.number="resultForm.score"
                  type="number"
                  min="0"
                  required
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                />
                <p v-if="resultErrors.score" class="text-red-500 text-xs mt-1">{{ resultErrors.score }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Score</label>
                <input
                  v-model.number="resultForm.max_score"
                  type="number"
                  min="1"
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                />
              </div>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Placing</label>
              <input
                v-model.number="resultForm.placing"
                type="number"
                min="1"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
              />
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea
                v-model="resultForm.notes"
                rows="2"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"
              ></textarea>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="closeResultModal" class="px-4 py-2 text-sm text-gray-600 font-medium hover:text-gray-900">
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-700 disabled:opacity-50 transition"
              >
                Save Result
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  competition: Object,
  results:     Array,
  archers:     Array,
})

// ── Stats ─────────────────────────────────────────────────────────────────────
const sortedResults = computed(() =>
  [...props.results].sort((a, b) => (b.score ?? 0) - (a.score ?? 0))
)

const topResult = computed(() => sortedResults.value[0] ?? null)

const averageScore = computed(() => {
  if (!props.results.length) return '—'
  const avg = props.results.reduce((sum, r) => sum + (r.score ?? 0), 0) / props.results.length
  return Math.round(avg)
})

function barWidth(score) {
  const max = topResult.value?.score ?? 1
  return max > 0 ? Math.round((score / max) * 100) : 0
}

// ── Add / Edit result ─────────────────────────────────────────────────────────
const showAddResult = ref(false)
const editingResult = ref(null)
const submitting    = ref(false)
const resultErrors  = ref({})

const resultForm = reactive({
  archer_id: null,
  category:  '',
  score:     null,
  max_score: 600,
  placing:   null,
  notes:     '',
})

function openEditResult(result) {
  editingResult.value = result
  Object.assign(resultForm, {
    archer_id: result.archer_id,
    category:  result.category ?? '',
    score:     result.score,
    max_score: result.max_score ?? 600,
    placing:   result.placing,
    notes:     result.notes ?? '',
  })
  resultErrors.value = {}
}

function closeResultModal() {
  showAddResult.value = false
  editingResult.value = null
  Object.assign(resultForm, { archer_id: null, category: '', score: null, max_score: 600, placing: null, notes: '' })
}

function submitResult() {
  submitting.value = true
  resultErrors.value = {}

  router.post(route('admin.competitions.results.store', props.competition.id), { ...resultForm }, {
    onSuccess: () => closeResultModal(),
    onError:   (e) => { resultErrors.value = e },
    onFinish:  () => { submitting.value = false },
  })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
