<template>
  <AppLayout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Archer</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Equipment</h1>
          <p class="mt-0.5 text-sm text-gray-500">{{ setups.length }} setup{{ setups.length !== 1 ? 's' : '' }} saved</p>
        </div>
        <Button variant="primary" @click="openCreateModal">
          <template #icon-left>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </template>
          Add Setup
        </Button>
      </div>

      <!-- ── Equipment cards ─────────────────────────────────────────────────── -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div
          v-for="setup in setups"
          :key="setup.id"
          :class="[
            'relative rounded-2xl bg-white shadow-sm ring-1 transition',
            setup.is_current ? 'ring-gray-900' : 'ring-gray-100',
          ]"
        >
          <!-- Current badge -->
          <div v-if="setup.is_current" class="absolute -top-2.5 left-4">
            <span class="inline-flex items-center rounded-full bg-gray-900 px-2.5 py-0.5 text-xs font-semibold text-white">
              Current
            </span>
          </div>

          <div class="p-5">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-semibold text-gray-900">{{ setup.name }}</p>
                <p class="mt-0.5 text-xs text-gray-400">
                  Added {{ formatDate(setup.created_at) }}
                </p>
              </div>
              <div class="flex items-center gap-1">
                <button
                  type="button"
                  class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-50 hover:text-gray-600"
                  title="Edit"
                  @click="openEditModal(setup)"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button
                  type="button"
                  class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                  title="Delete"
                  @click="confirmDelete(setup)"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Specs -->
            <dl class="mt-4 grid grid-cols-2 gap-3">
              <div>
                <dt class="text-[11px] font-medium uppercase tracking-widest text-gray-400">Bow Type</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-800">{{ setup.bow_type }}</dd>
              </div>
              <div>
                <dt class="text-[11px] font-medium uppercase tracking-widest text-gray-400">Draw Weight</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-800">
                  {{ setup.draw_weight_lbs ? `${setup.draw_weight_lbs} lbs` : '—' }}
                </dd>
              </div>
              <div>
                <dt class="text-[11px] font-medium uppercase tracking-widest text-gray-400">Bow</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-800">
                  {{ [setup.bow_brand, setup.bow_model].filter(Boolean).join(' ') || '—' }}
                </dd>
              </div>
              <div>
                <dt class="text-[11px] font-medium uppercase tracking-widest text-gray-400">Draw Length</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-800">
                  {{ setup.draw_length_inches ? `${setup.draw_length_inches}"` : '—' }}
                </dd>
              </div>
              <div class="col-span-2">
                <dt class="text-[11px] font-medium uppercase tracking-widest text-gray-400">Arrows</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-800">
                  {{ [setup.arrow_brand, setup.arrow_model].filter(Boolean).join(' ') || '—' }}
                  <span v-if="setup.arrow_spine" class="ml-1 text-xs text-gray-400">spine {{ setup.arrow_spine }}</span>
                </dd>
              </div>
            </dl>

            <!-- Set as current -->
            <div v-if="!setup.is_current" class="mt-4 border-t border-gray-50 pt-4">
              <button
                type="button"
                class="text-xs font-semibold text-gray-500 hover:text-gray-900"
                @click="setAsCurrent(setup)"
              >
                Set as current →
              </button>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-if="setups.length === 0"
          class="col-span-full flex flex-col items-center justify-center rounded-2xl bg-white py-16 shadow-sm ring-1 ring-gray-100"
        >
          <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
          </svg>
          <p class="text-sm font-medium text-gray-600">No equipment setups</p>
          <p class="mt-1 text-xs text-gray-400">Add your bow and arrow configuration.</p>
          <Button variant="primary" class="mt-4" @click="openCreateModal">Add Setup</Button>
        </div>
      </div>

      <!-- ── Create / Edit Modal ─────────────────────────────────────────────── -->
      <Modal v-model="showModal" :title="editingSetup ? 'Edit Setup' : 'Add Equipment Setup'" size="lg">
        <form class="space-y-5" @submit.prevent="submitForm">

          <Input
            v-model="form.name"
            label="Setup Name"
            placeholder="e.g. Indoor recurve, Competition setup"
            :error="errors.name"
            required
          />

          <!-- Bow section -->
          <div class="space-y-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Bow</p>
            <div class="grid grid-cols-2 gap-4">
              <Select
                v-model="form.bow_type"
                label="Bow Type"
                :options="bowTypeOptions"
                :error="errors.bow_type"
                required
              />
              <Input
                v-model="form.draw_weight_lbs"
                label="Draw Weight (lbs)"
                type="number"
                placeholder="28"
                :error="errors.draw_weight_lbs"
              />
              <Input
                v-model="form.bow_brand"
                label="Brand"
                placeholder="e.g. Hoyt"
                :error="errors.bow_brand"
              />
              <Input
                v-model="form.bow_model"
                label="Model"
                placeholder="e.g. Formula Xi"
                :error="errors.bow_model"
              />
              <Input
                v-model="form.draw_length_inches"
                label="Draw Length (inches)"
                type="number"
                step="0.5"
                placeholder="28.5"
                :error="errors.draw_length_inches"
              />
            </div>
          </div>

          <!-- Arrow section -->
          <div class="space-y-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Arrows</p>
            <div class="grid grid-cols-2 gap-4">
              <Input
                v-model="form.arrow_brand"
                label="Brand"
                placeholder="e.g. Easton"
                :error="errors.arrow_brand"
              />
              <Input
                v-model="form.arrow_model"
                label="Model"
                placeholder="e.g. X10"
                :error="errors.arrow_model"
              />
              <Input
                v-model="form.arrow_spine"
                label="Spine"
                type="number"
                placeholder="500"
                :error="errors.arrow_spine"
              />
            </div>
          </div>

          <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
            <button
              type="button"
              :class="[
                'relative inline-flex h-5 w-9 shrink-0 rounded-full border-2 border-transparent transition-colors',
                form.is_current ? 'bg-gray-900' : 'bg-gray-200',
              ]"
              @click="form.is_current = !form.is_current"
            >
              <span
                :class="[
                  'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition',
                  form.is_current ? 'translate-x-4' : 'translate-x-0',
                ]"
              />
            </button>
            <p class="text-sm font-medium text-gray-700">Set as current setup</p>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <Button variant="secondary" type="button" @click="showModal = false">Cancel</Button>
            <Button variant="primary" type="submit" :loading="submitting">
              {{ editingSetup ? 'Save Changes' : 'Add Setup' }}
            </Button>
          </div>
        </form>
      </Modal>

      <!-- ── Delete confirm ──────────────────────────────────────────────────── -->
      <Modal v-model="showDeleteModal" title="Delete Setup" size="sm">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Delete <span class="font-semibold">{{ deletingSetup?.name }}</span>? This cannot be undone.
          </p>
          <div class="flex justify-end gap-3">
            <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
            <Button variant="danger" :loading="deleting" @click="deleteSetup">Delete</Button>
          </div>
        </div>
      </Modal>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  setups: { type: Array, default: () => [] },
})

// ── State ──────────────────────────────────────────────────────────────────────

const showModal      = ref(false)
const showDeleteModal = ref(false)
const editingSetup   = ref(null)
const deletingSetup  = ref(null)
const submitting     = ref(false)
const deleting       = ref(false)
const errors         = reactive({})

const bowTypeOptions = [
  { label: 'Recurve',  value: 'Recurve' },
  { label: 'Compound', value: 'Compound' },
  { label: 'Barebow',  value: 'Barebow' },
  { label: 'Longbow',  value: 'Longbow' },
]

const defaultForm = () => ({
  name:               '',
  bow_type:           '',
  bow_brand:          '',
  bow_model:          '',
  draw_weight_lbs:    '',
  draw_length_inches: '',
  arrow_brand:        '',
  arrow_model:        '',
  arrow_spine:        '',
  is_current:         false,
})

const form = reactive(defaultForm())

// ── Modals ─────────────────────────────────────────────────────────────────────

function openCreateModal() {
  editingSetup.value = null
  Object.assign(form, defaultForm())
  clearErrors()
  showModal.value = true
}

function openEditModal(setup) {
  editingSetup.value = setup
  Object.assign(form, {
    name:               setup.name,
    bow_type:           setup.bow_type,
    bow_brand:          setup.bow_brand ?? '',
    bow_model:          setup.bow_model ?? '',
    draw_weight_lbs:    setup.draw_weight_lbs ?? '',
    draw_length_inches: setup.draw_length_inches ?? '',
    arrow_brand:        setup.arrow_brand ?? '',
    arrow_model:        setup.arrow_model ?? '',
    arrow_spine:        setup.arrow_spine ?? '',
    is_current:         setup.is_current,
  })
  clearErrors()
  showModal.value = true
}

function confirmDelete(setup) {
  deletingSetup.value = setup
  showDeleteModal.value = true
}

function clearErrors() {
  Object.keys(errors).forEach((k) => delete errors[k])
}

// ── Actions ────────────────────────────────────────────────────────────────────

function submitForm() {
  submitting.value = true
  clearErrors()

  const isEdit = !!editingSetup.value
  const url    = isEdit ? `/archer/equipment/${editingSetup.value.id}` : '/archer/equipment'
  const method = isEdit ? 'put' : 'post'

  router[method](url, { ...form }, {
    preserveScroll: true,
    onSuccess: () => { showModal.value = false },
    onError: (err) => Object.assign(errors, err),
    onFinish: () => { submitting.value = false },
  })
}

function deleteSetup() {
  if (!deletingSetup.value) return
  deleting.value = true

  router.delete(`/archer/equipment/${deletingSetup.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { showDeleteModal.value = false; deletingSetup.value = null },
    onFinish: () => { deleting.value = false },
  })
}

function setAsCurrent(setup) {
  router.put(`/archer/equipment/${setup.id}/set-current`, {}, { preserveScroll: true })
}

// ── Helpers ────────────────────────────────────────────────────────────────────

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
