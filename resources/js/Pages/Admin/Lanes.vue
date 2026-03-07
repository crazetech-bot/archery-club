<template>
  <AdminLayout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Admin</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Lanes</h1>
          <p class="mt-0.5 text-sm text-gray-500">{{ lanes.length }} lane{{ lanes.length !== 1 ? 's' : '' }} configured</p>
        </div>
        <Button variant="primary" @click="openCreateModal">
          <template #icon-left>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </template>
          Add Lane
        </Button>
      </div>

      <!-- ── Lane grid ───────────────────────────────────────────────────────── -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="lane in lanes"
          :key="lane.id"
          class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100"
        >
          <!-- Lane header -->
          <div class="flex items-start justify-between p-5">
            <div class="flex items-center gap-3">
              <!-- Status indicator -->
              <div
                :class="[
                  'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold',
                  lane.is_active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-400',
                ]"
              >
                {{ lane.number }}
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-900">{{ lane.name }}</p>
                <p class="text-xs text-gray-400">{{ lane.distance_metres }}m · {{ lane.target_face }}</p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1">
              <button
                type="button"
                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-50 hover:text-gray-600"
                title="Edit lane"
                @click="openEditModal(lane)"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button
                type="button"
                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                title="Delete lane"
                @click="confirmDelete(lane)"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Status + booking info -->
          <div class="border-t border-gray-50 px-5 py-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span
                  :class="[
                    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold',
                    lane.is_active
                      ? 'bg-green-100 text-green-700'
                      : 'bg-gray-100 text-gray-500',
                  ]"
                >
                  <span
                    :class="['h-1.5 w-1.5 rounded-full', lane.is_active ? 'bg-green-500' : 'bg-gray-400']"
                  />
                  {{ lane.is_active ? 'Active' : 'Inactive' }}
                </span>

                <span
                  v-if="lane.current_booking"
                  class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700"
                >
                  <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500" />
                  In use
                </span>
              </div>

              <Link
                :href="`/admin/lanes/${lane.id}/bookings`"
                class="text-xs font-medium text-gray-400 hover:text-gray-700"
              >
                Bookings →
              </Link>
            </div>

            <!-- Current booking info -->
            <div v-if="lane.current_booking" class="mt-3 rounded-xl bg-amber-50 p-3">
              <p class="text-xs font-medium text-amber-800">{{ lane.current_booking.archer_name }}</p>
              <p class="mt-0.5 text-xs text-amber-600">
                Until {{ formatTime(lane.current_booking.end_time) }}
              </p>
            </div>

            <!-- Today's upcoming bookings count -->
            <p v-else class="mt-2 text-xs text-gray-400">
              {{ lane.todays_bookings_count ?? 0 }} booking{{ lane.todays_bookings_count !== 1 ? 's' : '' }} today
            </p>
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-if="lanes.length === 0"
          class="col-span-full flex flex-col items-center justify-center rounded-2xl bg-white py-16 shadow-sm ring-1 ring-gray-100"
        >
          <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
          </svg>
          <p class="text-sm font-medium text-gray-600">No lanes configured</p>
          <p class="mt-1 text-xs text-gray-400">Add your first lane to get started.</p>
          <Button variant="primary" class="mt-4" @click="openCreateModal">Add Lane</Button>
        </div>
      </div>

      <!-- ── Create / Edit Modal ─────────────────────────────────────────────── -->
      <Modal v-model="showModal" :title="editingLane ? 'Edit Lane' : 'Add Lane'" size="md">
        <form class="space-y-4" @submit.prevent="submitForm">
          <div class="grid grid-cols-2 gap-4">
            <Input
              v-model="form.number"
              label="Lane Number"
              type="number"
              placeholder="1"
              :error="errors.number"
              required
            />
            <Input
              v-model="form.name"
              label="Lane Name"
              placeholder="Lane 1"
              :error="errors.name"
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <Input
              v-model="form.distance_metres"
              label="Distance (metres)"
              type="number"
              placeholder="18"
              :error="errors.distance_metres"
              required
            />
            <Select
              v-model="form.target_face"
              label="Target Face"
              :options="targetFaceOptions"
              :error="errors.target_face"
              required
            />
          </div>

          <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
            <button
              type="button"
              :class="[
                'relative inline-flex h-5 w-9 shrink-0 rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2',
                form.is_active ? 'bg-gray-900' : 'bg-gray-200',
              ]"
              @click="form.is_active = !form.is_active"
            >
              <span
                :class="[
                  'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition',
                  form.is_active ? 'translate-x-4' : 'translate-x-0',
                ]"
              />
            </button>
            <div>
              <p class="text-sm font-medium text-gray-700">Active</p>
              <p class="text-xs text-gray-400">Archers can book this lane when active</p>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <Button variant="secondary" type="button" @click="showModal = false">Cancel</Button>
            <Button variant="primary" type="submit" :loading="submitting">
              {{ editingLane ? 'Save Changes' : 'Add Lane' }}
            </Button>
          </div>
        </form>
      </Modal>

      <!-- ── Delete Confirm Modal ────────────────────────────────────────────── -->
      <Modal v-model="showDeleteModal" title="Delete Lane" size="sm">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Are you sure you want to delete <span class="font-semibold">{{ deletingLane?.name }}</span>?
            All bookings for this lane will also be removed. This cannot be undone.
          </p>
          <div class="flex justify-end gap-3">
            <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
            <Button variant="danger" :loading="deleting" @click="deleteLane">Delete</Button>
          </div>
        </div>
      </Modal>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  lanes: { type: Array, default: () => [] },
})

// ── Modal state ────────────────────────────────────────────────────────────────

const showModal      = ref(false)
const showDeleteModal = ref(false)
const editingLane    = ref(null)
const deletingLane   = ref(null)
const submitting     = ref(false)
const deleting       = ref(false)
const errors         = reactive({})

const defaultForm = () => ({
  number:          '',
  name:            '',
  distance_metres: '',
  target_face:     '',
  is_active:       true,
})

const form = reactive(defaultForm())

const targetFaceOptions = [
  { label: '40cm',  value: '40cm' },
  { label: '60cm',  value: '60cm' },
  { label: '80cm',  value: '80cm' },
  { label: '122cm', value: '122cm' },
]

// ── Open modals ────────────────────────────────────────────────────────────────

function openCreateModal() {
  editingLane.value = null
  Object.assign(form, defaultForm())
  clearErrors()
  showModal.value = true
}

function openEditModal(lane) {
  editingLane.value = lane
  Object.assign(form, {
    number:          lane.number,
    name:            lane.name,
    distance_metres: lane.distance_metres,
    target_face:     lane.target_face,
    is_active:       lane.is_active,
  })
  clearErrors()
  showModal.value = true
}

function confirmDelete(lane) {
  deletingLane.value = lane
  showDeleteModal.value = true
}

function clearErrors() {
  Object.keys(errors).forEach((k) => delete errors[k])
}

// ── Submit form ────────────────────────────────────────────────────────────────

function submitForm() {
  submitting.value = true
  clearErrors()

  const isEdit = !!editingLane.value
  const url    = isEdit ? `/admin/lanes/${editingLane.value.id}` : '/admin/lanes'
  const method = isEdit ? 'put' : 'post'

  router[method](url, { ...form }, {
    preserveScroll: true,
    onSuccess: () => { showModal.value = false },
    onError:   (err) => Object.assign(errors, err),
    onFinish:  () => { submitting.value = false },
  })
}

// ── Delete lane ────────────────────────────────────────────────────────────────

function deleteLane() {
  if (!deletingLane.value) return
  deleting.value = true

  router.delete(`/admin/lanes/${deletingLane.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { showDeleteModal.value = false; deletingLane.value = null },
    onFinish:  () => { deleting.value = false },
  })
}

// ── Helpers ────────────────────────────────────────────────────────────────────

function formatTime(iso) {
  return new Date(iso).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}
</script>
