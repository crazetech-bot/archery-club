<template>
  <AppLayout title="Competitions">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Competitions</h1>
          <p class="text-sm text-gray-500 mt-1">{{ competitions.length }} total</p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-gray-700 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add Competition
        </button>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl">
        {{ $page.props.flash.success }}
      </div>

      <!-- Tabs: upcoming / past -->
      <div class="flex gap-1 mb-6 bg-gray-100 rounded-xl p-1 w-fit">
        <button
          v-for="tab in ['all', 'upcoming', 'past']"
          :key="tab"
          @click="activeTab = tab"
          :class="activeTab === tab ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
          class="text-sm font-medium px-4 py-1.5 rounded-lg capitalize transition"
        >
          {{ tab }}
        </button>
      </div>

      <!-- Competition grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="comp in filteredCompetitions"
          :key="comp.id"
          class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-md transition cursor-pointer"
          @click="goToDetail(comp)"
        >
          <!-- Status badge -->
          <div class="flex items-start justify-between mb-3">
            <span
              :class="comp.is_upcoming ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
              class="text-xs font-medium px-2 py-0.5 rounded-full"
            >
              {{ comp.is_upcoming ? 'Upcoming' : 'Completed' }}
            </span>
            <div class="flex gap-2" @click.stop>
              <button @click="openEditModal(comp)" class="text-xs text-gray-400 hover:text-gray-700">Edit</button>
              <button @click="confirmDelete(comp)" class="text-xs text-red-400 hover:text-red-600">Delete</button>
            </div>
          </div>

          <!-- Name & date -->
          <h3 class="font-semibold text-gray-900 text-base mb-1">{{ comp.name }}</h3>
          <p class="text-sm text-gray-500 mb-3">
            {{ formatDate(comp.date) }}
            <span v-if="comp.location"> · {{ comp.location }}</span>
          </p>

          <!-- Meta chips -->
          <div class="flex flex-wrap gap-2">
            <span v-if="comp.round_type" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
              {{ comp.round_type }}
            </span>
            <span v-if="comp.distance_metres" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
              {{ comp.distance_metres }}m
            </span>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
              {{ comp.results_count }} result{{ comp.results_count !== 1 ? 's' : '' }}
            </span>
          </div>
        </div>

        <div v-if="filteredCompetitions.length === 0" class="col-span-full text-center py-16 text-gray-400">
          No competitions found.
        </div>
      </div>

      <!-- Create / Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-5">
            {{ editingComp ? 'Edit Competition' : 'Add Competition' }}
          </h2>

          <form @submit.prevent="submitForm">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
              />
              <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
              <input
                v-model="form.date"
                type="date"
                required
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
              />
              <p v-if="errors.date" class="text-red-500 text-xs mt-1">{{ errors.date }}</p>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
              <input
                v-model="form.location"
                type="text"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
              />
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Round Type</label>
                <input
                  v-model="form.round_type"
                  type="text"
                  placeholder="e.g. WA 18m"
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Distance (m)</label>
                <input
                  v-model.number="form.distance_metres"
                  type="number"
                  min="1"
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                />
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 font-medium"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-700 disabled:opacity-50 transition"
              >
                {{ editingComp ? 'Save Changes' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete confirm -->
      <div v-if="deletingComp" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
          <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-gray-900 mb-1">Delete Competition</h3>
          <p class="text-sm text-gray-500 mb-6">
            Delete <strong>{{ deletingComp.name }}</strong> and all its results? This cannot be undone.
          </p>
          <div class="flex gap-3">
            <button @click="deletingComp = null" class="flex-1 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50">
              Cancel
            </button>
            <button
              @click="destroyComp"
              :disabled="submitting"
              class="flex-1 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 disabled:opacity-50"
            >
              Delete
            </button>
          </div>
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
  competitions: Array,
})

// ── Tabs ──────────────────────────────────────────────────────────────────────
const activeTab = ref('all')

const filteredCompetitions = computed(() => {
  if (activeTab.value === 'upcoming') return props.competitions.filter(c => c.is_upcoming)
  if (activeTab.value === 'past')     return props.competitions.filter(c => !c.is_upcoming)
  return props.competitions
})

// ── Navigation ────────────────────────────────────────────────────────────────
function goToDetail(comp) {
  router.get(route('admin.competitions.show', comp.id))
}

// ── Modal ─────────────────────────────────────────────────────────────────────
const showModal  = ref(false)
const editingComp = ref(null)
const submitting  = ref(false)
const errors      = ref({})
const deletingComp = ref(null)

const form = reactive({
  name:             '',
  date:             '',
  location:         '',
  round_type:       '',
  distance_metres:  null,
})

function openCreateModal() {
  editingComp.value = null
  Object.assign(form, { name: '', date: '', location: '', round_type: '', distance_metres: null })
  errors.value  = {}
  showModal.value = true
}

function openEditModal(comp) {
  editingComp.value = comp
  Object.assign(form, {
    name:            comp.name,
    date:            comp.date,
    location:        comp.location ?? '',
    round_type:      comp.round_type ?? '',
    distance_metres: comp.distance_metres ?? null,
  })
  errors.value  = {}
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingComp.value = null
}

function submitForm() {
  submitting.value = true
  errors.value = {}

  if (editingComp.value) {
    router.put(route('admin.competitions.update', editingComp.value.id), { ...form }, {
      onSuccess: () => closeModal(),
      onError:   (e) => { errors.value = e },
      onFinish:  () => { submitting.value = false },
    })
  } else {
    router.post(route('admin.competitions.store'), { ...form }, {
      onSuccess: () => closeModal(),
      onError:   (e) => { errors.value = e },
      onFinish:  () => { submitting.value = false },
    })
  }
}

function confirmDelete(comp) {
  deletingComp.value = comp
}

function destroyComp() {
  submitting.value = true
  router.delete(route('admin.competitions.destroy', deletingComp.value.id), {
    onSuccess: () => { deletingComp.value = null },
    onFinish:  () => { submitting.value = false },
  })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
