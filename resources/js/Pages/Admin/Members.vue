<template>
  <AppLayout title="Members">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Members</h1>
          <p class="text-sm text-gray-500 mt-1">{{ members.length }} members in this club</p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-gray-700 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add Archer
        </button>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-3 mb-6">
        <div class="relative flex-1 min-w-[200px]">
          <input
            v-model="search"
            type="text"
            placeholder="Search by name…"
            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gray-900"
            @input="applyFilters"
          />
          <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <select
          v-model="roleFilter"
          class="text-sm border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900"
          @change="applyFilters"
        >
          <option value="">All roles</option>
          <option value="archer">Archers</option>
          <option value="coach">Coaches</option>
        </select>
      </div>

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl">
        {{ $page.props.flash.success }}
      </div>

      <!-- Member table -->
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Name</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Email</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Role</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Category</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Coach</th>
              <th class="text-left px-5 py-3 font-medium text-gray-600">Joined</th>
              <th class="px-5 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="members.length === 0">
              <td colspan="7" class="text-center py-12 text-gray-400">No members found.</td>
            </tr>
            <tr
              v-for="member in members"
              :key="`${member.role}-${member.id}`"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-5 py-3 font-medium text-gray-900">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                    {{ initials(member.name) }}
                  </div>
                  {{ member.name }}
                </div>
              </td>
              <td class="px-5 py-3 text-gray-500">{{ member.email ?? '—' }}</td>
              <td class="px-5 py-3">
                <span
                  :class="member.role === 'coach'
                    ? 'bg-blue-100 text-blue-700'
                    : 'bg-gray-100 text-gray-700'"
                  class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                >
                  {{ member.role }}
                </span>
              </td>
              <td class="px-5 py-3 text-gray-500">{{ member.category ?? '—' }}</td>
              <td class="px-5 py-3 text-gray-500">{{ member.coach_name ?? '—' }}</td>
              <td class="px-5 py-3 text-gray-500">{{ formatDate(member.joined_at) }}</td>
              <td class="px-5 py-3 text-right">
                <div v-if="member.role === 'archer'" class="flex items-center justify-end gap-2">
                  <button
                    @click="openEditModal(member)"
                    class="text-xs text-gray-500 hover:text-gray-900 font-medium"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(member)"
                    class="text-xs text-red-500 hover:text-red-700 font-medium"
                  >
                    Remove
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Create / Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-5">
            {{ editingMember ? 'Edit Archer' : 'Add Archer' }}
          </h2>

          <form @submit.prevent="submitForm">
            <!-- Name (create only) -->
            <template v-if="!editingMember">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                />
                <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                />
                <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dominant Hand</label>
                <select
                  v-model="form.dominant_hand"
                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                >
                  <option value="">Not specified</option>
                  <option value="right">Right</option>
                  <option value="left">Left</option>
                </select>
              </div>
            </template>

            <!-- Category (always) -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select
                v-model="form.category"
                required
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
              >
                <option value="">Select category</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
              <p v-if="errors.category" class="text-red-500 text-xs mt-1">{{ errors.category }}</p>
            </div>

            <!-- Coach -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Assign Coach</label>
              <select
                v-model="form.coach_id"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
              >
                <option :value="null">No coach</option>
                <option v-for="coach in coaches" :key="coach.id" :value="coach.id">
                  {{ coach.name }}
                </option>
              </select>
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
                {{ editingMember ? 'Save Changes' : 'Add Archer' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete confirmation -->
      <div v-if="deletingMember" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
          <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-gray-900 mb-1">Remove Member</h3>
          <p class="text-sm text-gray-500 mb-6">
            Remove <strong>{{ deletingMember.name }}</strong> from the club? This cannot be undone.
          </p>
          <div class="flex gap-3">
            <button
              @click="deletingMember = null"
              class="flex-1 py-2 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="destroyMember"
              :disabled="submitting"
              class="flex-1 py-2 bg-red-600 text-white text-sm font-medium rounded-xl hover:bg-red-700 disabled:opacity-50"
            >
              Remove
            </button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  members: Array,
  coaches: Array,
  filters: Object,
})

const categories = ['U12', 'U15', 'U18', 'U21', 'Senior', 'Master']

// ── Filters ──────────────────────────────────────────────────────────────────
const search     = ref(props.filters?.search ?? '')
const roleFilter = ref(props.filters?.role ?? '')

function applyFilters() {
  router.get(route('admin.members.index'), {
    search: search.value || undefined,
    role:   roleFilter.value || undefined,
  }, { preserveState: true, replace: true })
}

// ── Modal state ───────────────────────────────────────────────────────────────
const showModal     = ref(false)
const editingMember = ref(null)
const submitting    = ref(false)
const errors        = ref({})

const form = reactive({
  name:           '',
  email:          '',
  date_of_birth:  '',
  dominant_hand:  '',
  category:       '',
  coach_id:       null,
})

function openCreateModal() {
  editingMember.value = null
  Object.assign(form, { name: '', email: '', date_of_birth: '', dominant_hand: '', category: '', coach_id: null })
  errors.value  = {}
  showModal.value = true
}

function openEditModal(member) {
  editingMember.value = member
  Object.assign(form, { category: member.category ?? '', coach_id: member.coach_id ?? null })
  errors.value  = {}
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingMember.value = null
}

function submitForm() {
  submitting.value = true
  errors.value = {}

  if (editingMember.value) {
    router.put(route('admin.members.archers.update', editingMember.value.id), {
      category: form.category,
      coach_id: form.coach_id,
    }, {
      onSuccess: () => closeModal(),
      onError:   (e) => { errors.value = e },
      onFinish:  () => { submitting.value = false },
    })
  } else {
    router.post(route('admin.members.archers.store'), { ...form }, {
      onSuccess: () => closeModal(),
      onError:   (e) => { errors.value = e },
      onFinish:  () => { submitting.value = false },
    })
  }
}

// ── Delete ────────────────────────────────────────────────────────────────────
const deletingMember = ref(null)

function confirmDelete(member) {
  deletingMember.value = member
}

function destroyMember() {
  submitting.value = true
  router.delete(route('admin.members.archers.destroy', deletingMember.value.id), {
    onSuccess: () => { deletingMember.value = null },
    onFinish:  () => { submitting.value = false },
  })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function initials(name) {
  return name
    .split(' ')
    .slice(0, 2)
    .map(w => w[0])
    .join('')
    .toUpperCase()
}

function formatDate(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
