<template>
  <AdminLayout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link href="/admin/lanes" class="text-xs font-medium text-gray-400 hover:text-gray-700">
              ← Lanes
            </Link>
          </div>
          <h1 class="mt-1 text-2xl font-bold text-gray-900">
            {{ lane ? lane.name : 'All' }} Bookings
          </h1>
          <p class="mt-0.5 text-sm text-gray-500">
            {{ filteredBookings.length }} booking{{ filteredBookings.length !== 1 ? 's' : '' }}
            <span v-if="!lane"> across all lanes</span>
          </p>
        </div>

        <Button variant="primary" @click="openCreateModal">
          <template #icon-left>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </template>
          New Booking
        </Button>
      </div>

      <!-- ── Filters ─────────────────────────────────────────────────────────── -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <Input
          v-model="search"
          placeholder="Search archer name…"
          clearable
          class="sm:w-64"
        >
          <template #icon-left>
            <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </template>
        </Input>

        <Select
          v-if="!lane"
          v-model="filterLane"
          placeholder="All lanes"
          :options="laneOptions"
          class="sm:w-44"
        />

        <Select
          v-model="filterStatus"
          placeholder="All statuses"
          :options="statusOptions"
          class="sm:w-44"
        />

        <Input
          v-model="filterDate"
          type="date"
          class="sm:w-44"
        />

        <button
          v-if="hasActiveFilters"
          type="button"
          class="text-xs font-medium text-gray-400 hover:text-gray-700"
          @click="clearFilters"
        >
          Clear filters
        </button>
      </div>

      <!-- ── Bookings table ──────────────────────────────────────────────────── -->
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="min-w-full divide-y divide-gray-100">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Archer</th>
              <th v-if="!lane" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Lane</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Date</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Time</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Duration</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
              <th class="px-5 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr
              v-for="booking in paginatedBookings"
              :key="booking.id"
              class="hover:bg-gray-50/50"
            >
              <!-- Archer -->
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white">
                    {{ initials(booking.archer_name) }}
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ booking.archer_name }}</p>
                    <p class="text-xs text-gray-400">{{ booking.archer_category }}</p>
                  </div>
                </div>
              </td>

              <!-- Lane (only when showing all) -->
              <td v-if="!lane" class="px-5 py-4">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-700">
                  {{ booking.lane_number }}
                </span>
              </td>

              <!-- Date -->
              <td class="px-5 py-4">
                <p class="text-sm text-gray-700">{{ formatDate(booking.start_time) }}</p>
              </td>

              <!-- Time -->
              <td class="px-5 py-4">
                <p class="text-sm text-gray-700">
                  {{ formatTime(booking.start_time) }} – {{ formatTime(booking.end_time) }}
                </p>
              </td>

              <!-- Duration -->
              <td class="px-5 py-4">
                <p class="text-sm text-gray-500">{{ duration(booking.start_time, booking.end_time) }}</p>
              </td>

              <!-- Status -->
              <td class="px-5 py-4">
                <span :class="statusBadgeClass(booking)">
                  {{ statusLabel(booking) }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button
                    v-if="canEdit(booking)"
                    type="button"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                    title="Edit booking"
                    @click="openEditModal(booking)"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button
                    type="button"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                    title="Cancel booking"
                    @click="confirmCancel(booking)"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="filteredBookings.length === 0">
              <td :colspan="lane ? 6 : 7" class="px-5 py-12 text-center">
                <svg class="mx-auto mb-3 h-8 w-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">No bookings found</p>
                <p class="mt-0.5 text-xs text-gray-400">Try adjusting your filters</p>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div
          v-if="totalPages > 1"
          class="flex items-center justify-between border-t border-gray-100 px-5 py-3"
        >
          <p class="text-xs text-gray-400">
            Showing {{ (currentPage - 1) * pageSize + 1 }}–{{ Math.min(currentPage * pageSize, filteredBookings.length) }}
            of {{ filteredBookings.length }}
          </p>
          <div class="flex items-center gap-1">
            <button
              type="button"
              :disabled="currentPage === 1"
              class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
              @click="currentPage--"
            >
              ← Prev
            </button>
            <button
              v-for="page in visiblePages"
              :key="page"
              type="button"
              :class="[
                'rounded-lg px-2.5 py-1.5 text-xs font-medium transition',
                page === currentPage
                  ? 'bg-gray-900 text-white'
                  : 'text-gray-500 hover:bg-gray-50',
              ]"
              @click="currentPage = page"
            >
              {{ page }}
            </button>
            <button
              type="button"
              :disabled="currentPage === totalPages"
              class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
              @click="currentPage++"
            >
              Next →
            </button>
          </div>
        </div>
      </div>

      <!-- ── Create / Edit Booking Modal ────────────────────────────────────── -->
      <Modal v-model="showModal" :title="editingBooking ? 'Edit Booking' : 'New Booking'" size="md">
        <form class="space-y-4" @submit.prevent="submitForm">
          <Select
            v-model="form.archer_id"
            label="Archer"
            placeholder="Select archer…"
            :options="archerOptions"
            :error="errors.archer_id"
            required
          />

          <Select
            v-if="!lane"
            v-model="form.lane_id"
            label="Lane"
            placeholder="Select lane…"
            :options="laneOptions"
            :error="errors.lane_id"
            required
          />

          <div class="grid grid-cols-2 gap-4">
            <Input
              v-model="form.start_time"
              label="Start Time"
              type="datetime-local"
              :error="errors.start_time"
              required
            />
            <Input
              v-model="form.end_time"
              label="End Time"
              type="datetime-local"
              :error="errors.end_time"
              required
            />
          </div>

          <Alert v-if="overlapWarning" type="warning">
            {{ overlapWarning }}
          </Alert>

          <div class="flex justify-end gap-3 pt-2">
            <Button variant="secondary" type="button" @click="showModal = false">Cancel</Button>
            <Button variant="primary" type="submit" :loading="submitting">
              {{ editingBooking ? 'Save Changes' : 'Create Booking' }}
            </Button>
          </div>
        </form>
      </Modal>

      <!-- ── Cancel Confirm Modal ────────────────────────────────────────────── -->
      <Modal v-model="showCancelModal" title="Cancel Booking" size="sm">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Cancel the booking for <span class="font-semibold">{{ cancellingBooking?.archer_name }}</span>
            on {{ cancellingBooking ? formatDate(cancellingBooking.start_time) : '' }}?
          </p>
          <div class="flex justify-end gap-3">
            <Button variant="secondary" @click="showCancelModal = false">Keep Booking</Button>
            <Button variant="danger" :loading="cancelling" @click="cancelBooking">Cancel Booking</Button>
          </div>
        </div>
      </Modal>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/Layouts/AdminLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import Alert from '@/Components/UI/Alert.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  lane:     { type: Object, default: null },   // null = all lanes view
  lanes:    { type: Array,  default: () => [] },
  bookings: { type: Array,  default: () => [] },
  archers:  { type: Array,  default: () => [] },
})

// ── Filters ────────────────────────────────────────────────────────────────────

const search       = ref('')
const filterLane   = ref('')
const filterStatus = ref('')
const filterDate   = ref('')
const currentPage  = ref(1)
const pageSize     = 15

const statusOptions = [
  { label: 'Upcoming',  value: 'upcoming' },
  { label: 'Ongoing',   value: 'ongoing' },
  { label: 'Completed', value: 'completed' },
]

const laneOptions = computed(() =>
  props.lanes.map((l) => ({ label: l.name, value: String(l.id) }))
)

const archerOptions = computed(() =>
  props.archers.map((a) => ({ label: a.name, value: String(a.id) }))
)

const hasActiveFilters = computed(() =>
  search.value || filterLane.value || filterStatus.value || filterDate.value
)

function clearFilters() {
  search.value       = ''
  filterLane.value   = ''
  filterStatus.value = ''
  filterDate.value   = ''
}

// Reset page on filter change
watch([search, filterLane, filterStatus, filterDate], () => { currentPage.value = 1 })

// ── Filtered / paginated data ──────────────────────────────────────────────────

const filteredBookings = computed(() => {
  const now = Date.now()

  return props.bookings.filter((b) => {
    const start = new Date(b.start_time).getTime()
    const end   = new Date(b.end_time).getTime()

    // Search
    if (search.value && !b.archer_name.toLowerCase().includes(search.value.toLowerCase()))
      return false

    // Lane filter
    if (filterLane.value && String(b.lane_id) !== filterLane.value)
      return false

    // Status filter
    if (filterStatus.value) {
      const s = bookingStatus(b, now, start, end)
      if (s !== filterStatus.value) return false
    }

    // Date filter
    if (filterDate.value) {
      const bDate = new Date(b.start_time).toISOString().slice(0, 10)
      if (bDate !== filterDate.value) return false
    }

    return true
  })
})

const totalPages = computed(() => Math.ceil(filteredBookings.value.length / pageSize))

const paginatedBookings = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredBookings.value.slice(start, start + pageSize)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end   = Math.min(totalPages.value, start + 4)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

// ── Booking status helpers ─────────────────────────────────────────────────────

function bookingStatus(booking, now = Date.now(), start, end) {
  start = start ?? new Date(booking.start_time).getTime()
  end   = end   ?? new Date(booking.end_time).getTime()
  if (now < start) return 'upcoming'
  if (now >= start && now < end) return 'ongoing'
  return 'completed'
}

function statusLabel(booking) {
  return { upcoming: 'Upcoming', ongoing: 'Ongoing', completed: 'Completed' }[bookingStatus(booking)] ?? ''
}

function statusBadgeClass(booking) {
  const s = bookingStatus(booking)
  return {
    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold': true,
    'bg-blue-100 text-blue-700':   s === 'upcoming',
    'bg-green-100 text-green-700': s === 'ongoing',
    'bg-gray-100 text-gray-500':   s === 'completed',
  }
}

function canEdit(booking) {
  return bookingStatus(booking) === 'upcoming'
}

// ── Modal state ────────────────────────────────────────────────────────────────

const showModal       = ref(false)
const showCancelModal = ref(false)
const editingBooking  = ref(null)
const cancellingBooking = ref(null)
const submitting      = ref(false)
const cancelling      = ref(false)
const errors          = reactive({})
const overlapWarning  = ref(null)

const defaultForm = () => ({
  archer_id:  '',
  lane_id:    props.lane ? String(props.lane.id) : '',
  start_time: '',
  end_time:   '',
})

const form = reactive(defaultForm())

function openCreateModal() {
  editingBooking.value = null
  Object.assign(form, defaultForm())
  clearErrors()
  overlapWarning.value = null
  showModal.value = true
}

function openEditModal(booking) {
  editingBooking.value = booking
  Object.assign(form, {
    archer_id:  String(booking.archer_id),
    lane_id:    String(booking.lane_id),
    start_time: toDatetimeLocal(booking.start_time),
    end_time:   toDatetimeLocal(booking.end_time),
  })
  clearErrors()
  overlapWarning.value = null
  showModal.value = true
}

function confirmCancel(booking) {
  cancellingBooking.value = booking
  showCancelModal.value   = true
}

function clearErrors() {
  Object.keys(errors).forEach((k) => delete errors[k])
}

// ── Submit / cancel ────────────────────────────────────────────────────────────

function submitForm() {
  submitting.value = true
  clearErrors()
  overlapWarning.value = null

  const isEdit = !!editingBooking.value
  const url    = isEdit
    ? `/admin/lanes/${form.lane_id}/bookings/${editingBooking.value.id}`
    : `/admin/lanes/${form.lane_id}/bookings`
  const method = isEdit ? 'put' : 'post'

  router[method](url, { ...form }, {
    preserveScroll: true,
    onSuccess: () => { showModal.value = false },
    onError: (err) => {
      Object.assign(errors, err)
      if (err.overlap) overlapWarning.value = err.overlap
    },
    onFinish: () => { submitting.value = false },
  })
}

function cancelBooking() {
  if (!cancellingBooking.value) return
  cancelling.value = true

  router.delete(`/admin/lanes/${cancellingBooking.value.lane_id}/bookings/${cancellingBooking.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { showCancelModal.value = false; cancellingBooking.value = null },
    onFinish:  () => { cancelling.value = false },
  })
}

// ── Helpers ────────────────────────────────────────────────────────────────────

function initials(name) {
  return name.split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}

function formatTime(iso) {
  return new Date(iso).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}

function duration(start, end) {
  const mins = Math.round((new Date(end) - new Date(start)) / 60000)
  if (mins < 60) return `${mins}m`
  const h = Math.floor(mins / 60)
  const m = mins % 60
  return m ? `${h}h ${m}m` : `${h}h`
}

function toDatetimeLocal(iso) {
  const d = new Date(iso)
  return d.toISOString().slice(0, 16)
}
</script>
