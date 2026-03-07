<template>
  <AppLayout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Club Admin</p>
          <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Archers</h1>
          <p class="mt-0.5 text-sm text-gray-500">{{ archers.total }} registered</p>
        </div>
        <Link
          href="/admin/archers/create"
          class="inline-flex items-center gap-2 rounded-2xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-gray-900/20 transition hover:bg-gray-700 active:scale-95"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add Archer
        </Link>
      </div>

      <!-- Stats row -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Total</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.total }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Active</p>
          <p class="mt-1 text-2xl font-bold text-green-600">{{ stats.active }}</p>
        </div>
        <div v-for="(count, bow) in stats.by_bow" :key="bow" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
          <p class="text-xs font-medium uppercase tracking-widest text-gray-400">{{ bow }}</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ count }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-3">
        <Input
          v-model="form.search"
          placeholder="Search archers…"
          class="w-full sm:w-56"
          @input="applyFilters"
        />
        <Select
          v-model="form.bow_type"
          placeholder="All bow types"
          :options="bowTypeOptions"
          class="sm:w-44"
          @update:modelValue="applyFilters"
        />
        <Select
          v-model="form.experience_level"
          placeholder="All levels"
          :options="levelOptions"
          class="sm:w-44"
          @update:modelValue="applyFilters"
        />
        <Select
          v-model="form.is_active"
          placeholder="All status"
          :options="[{ label: 'Active', value: '1' }, { label: 'Inactive', value: '0' }]"
          class="sm:w-36"
          @update:modelValue="applyFilters"
        />
        <button
          v-if="hasFilters"
          type="button"
          class="text-xs font-medium text-gray-400 hover:text-gray-700"
          @click="clearFilters"
        >
          Clear
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Name</th>
              <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 sm:table-cell">Bow</th>
              <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 md:table-cell">Level</th>
              <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 md:table-cell">Category</th>
              <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 lg:table-cell">Coach</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="archers.data.length === 0">
              <td colspan="7" class="py-12 text-center text-sm text-gray-400">No archers found.</td>
            </tr>
            <tr
              v-for="archer in archers.data"
              :key="archer.id"
              class="group hover:bg-gray-50"
            >
              <td class="px-4 py-3">
                <Link :href="`/admin/archers/${archer.id}`" class="font-semibold text-gray-900 hover:text-gray-600">
                  {{ archer.name }}
                </Link>
                <p class="text-xs text-gray-400">{{ archer.gender ?? '—' }} · {{ archer.age ? archer.age + ' yrs' : '—' }}</p>
              </td>
              <td class="hidden px-4 py-3 text-sm text-gray-600 sm:table-cell">
                <span v-if="archer.bow_type" :class="bowBadge(archer.bow_type)" class="rounded-full px-2 py-0.5 text-xs font-medium">
                  {{ archer.bow_type }}
                </span>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="hidden px-4 py-3 text-sm text-gray-600 md:table-cell">{{ archer.experience_level ?? '—' }}</td>
              <td class="hidden px-4 py-3 text-sm text-gray-600 md:table-cell">{{ archer.category ?? '—' }}</td>
              <td class="hidden px-4 py-3 text-sm text-gray-600 lg:table-cell">{{ archer.coach_name ?? '—' }}</td>
              <td class="px-4 py-3">
                <span :class="archer.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'"
                      class="rounded-full px-2 py-0.5 text-xs font-medium">
                  {{ archer.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 transition group-hover:opacity-100">
                  <Link :href="`/admin/archers/${archer.id}/edit`"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">
                    Edit
                  </Link>
                  <button
                    type="button"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50"
                    @click="confirmDelete(archer)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="archers.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-4 py-3">
          <p class="text-xs text-gray-400">
            Showing {{ archers.from }}–{{ archers.to }} of {{ archers.total }}
          </p>
          <div class="flex gap-1">
            <Link
              v-for="link in archers.links"
              :key="link.label"
              :href="link.url ?? '#'"
              :class="[
                'rounded-lg px-3 py-1.5 text-xs font-medium',
                link.active ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100',
                !link.url ? 'pointer-events-none opacity-30' : '',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>

      <!-- Delete confirm modal -->
      <Modal :show="!!deleteTarget" @close="deleteTarget = null">
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900">Delete archer?</h3>
          <p class="mt-2 text-sm text-gray-500">
            This will permanently remove <strong>{{ deleteTarget?.name }}</strong> and all their data.
          </p>
          <div class="mt-6 flex justify-end gap-3">
            <button type="button"
                    class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100"
                    @click="deleteTarget = null">
              Cancel
            </button>
            <button type="button"
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                    @click="deleteArcher">
              Delete
            </button>
          </div>
        </div>
      </Modal>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  archers: { type: Object, required: true },
  coaches:  { type: Array,  default: () => [] },
  filters:  { type: Object, default: () => ({}) },
  stats:    { type: Object, default: () => ({}) },
})

const form = ref({
  search:           props.filters.search           ?? '',
  bow_type:         props.filters.bow_type         ?? '',
  experience_level: props.filters.experience_level ?? '',
  is_active:        props.filters.is_active        ?? '',
})

const deleteTarget = ref(null)

const hasFilters = computed(() =>
  Object.values(form.value).some(Boolean)
)

const bowTypeOptions = [
  { label: 'Recurve',  value: 'Recurve'  },
  { label: 'Compound', value: 'Compound' },
  { label: 'Barebow',  value: 'Barebow'  },
  { label: 'Longbow',  value: 'Longbow'  },
]

const levelOptions = [
  { label: 'Beginner',     value: 'Beginner'     },
  { label: 'Intermediate', value: 'Intermediate' },
  { label: 'Advanced',     value: 'Advanced'     },
  { label: 'Elite',        value: 'Elite'        },
]

function applyFilters() {
  router.get('/admin/archers', form.value, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

function clearFilters() {
  form.value = { search: '', bow_type: '', experience_level: '', is_active: '' }
  applyFilters()
}

function confirmDelete(archer) {
  deleteTarget.value = archer
}

function deleteArcher() {
  router.delete(`/admin/archers/${deleteTarget.value.id}`, {
    onSuccess: () => { deleteTarget.value = null },
  })
}

function bowBadge(bow) {
  const map = {
    Recurve:  'bg-sky-50 text-sky-700',
    Compound: 'bg-purple-50 text-purple-700',
    Barebow:  'bg-amber-50 text-amber-700',
    Longbow:  'bg-green-50 text-green-700',
  }
  return map[bow] ?? 'bg-gray-100 text-gray-600'
}
</script>
