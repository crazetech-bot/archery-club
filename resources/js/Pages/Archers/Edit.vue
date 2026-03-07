<template>
  <AppLayout>
    <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6">

      <div class="mb-6">
        <Link href="/admin/archers" class="text-xs font-medium text-gray-400 hover:text-gray-700">
          &larr; Back to Archers
        </Link>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit Archer</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ form.name }}</p>
      </div>

      <form class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100" @submit.prevent="submit">

        <!-- Name -->
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
          <Input v-model="form.name" placeholder="e.g. Sarah Johnson" :error="errors.name" />
          <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
        </div>

        <!-- Gender + DOB -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Gender</label>
            <Select v-model="form.gender" placeholder="Select…"
              :options="[{ label: 'Male', value: 'male' }, { label: 'Female', value: 'female' }, { label: 'Other', value: 'other' }]"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Date of Birth</label>
            <Input v-model="form.date_of_birth" type="date" :error="errors.date_of_birth" />
          </div>
        </div>

        <!-- Bow type + Experience -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Bow Type</label>
            <Select v-model="form.bow_type" placeholder="Select…"
              :options="bowTypes.map(b => ({ label: b, value: b }))"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Experience Level</label>
            <Select v-model="form.experience_level" placeholder="Select…"
              :options="experienceLevels.map(l => ({ label: l, value: l }))"
            />
          </div>
        </div>

        <!-- Category + Dominant hand -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
            <Select v-model="form.category" placeholder="Select…"
              :options="categories.map(c => ({ label: c, value: c }))"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Dominant Hand</label>
            <Select v-model="form.dominant_hand" placeholder="Select…"
              :options="[{ label: 'Right', value: 'right' }, { label: 'Left', value: 'left' }]"
            />
          </div>
        </div>

        <!-- Phone -->
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
          <Input v-model="form.phone" type="tel" placeholder="+44 7700 000000" />
        </div>

        <!-- Coach -->
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Assigned Coach</label>
          <Select v-model="form.coach_id" placeholder="No coach assigned"
            :options="coaches.map(c => ({ label: c.name, value: c.id }))"
          />
        </div>

        <!-- Active toggle -->
        <div class="flex items-center gap-3">
          <button
            type="button"
            :class="form.is_active ? 'bg-gray-900' : 'bg-gray-200'"
            class="relative inline-flex h-6 w-11 rounded-full transition-colors"
            @click="form.is_active = !form.is_active"
          >
            <span
              :class="form.is_active ? 'translate-x-5' : 'translate-x-1'"
              class="inline-block h-4 w-4 translate-y-1 rounded-full bg-white transition-transform"
            />
          </button>
          <label class="text-sm font-medium text-gray-700">Active member</label>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
          <button
            type="button"
            class="text-sm font-medium text-red-500 hover:text-red-700"
            @click="confirmDelete = true"
          >
            Delete archer
          </button>
          <div class="flex gap-3">
            <Link href="/admin/archers"
                  class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">
              Cancel
            </Link>
            <button type="submit"
                    :disabled="submitting"
                    class="rounded-xl bg-gray-900 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:opacity-50">
              {{ submitting ? 'Saving…' : 'Save Changes' }}
            </button>
          </div>
        </div>

      </form>

      <!-- Delete modal -->
      <Modal :show="confirmDelete" @close="confirmDelete = false">
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900">Delete archer?</h3>
          <p class="mt-2 text-sm text-gray-500">
            This will permanently remove <strong>{{ form.name }}</strong> and all associated data. This cannot be undone.
          </p>
          <div class="mt-6 flex justify-end gap-3">
            <button type="button"
                    class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100"
                    @click="confirmDelete = false">
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layouts/AppLayout.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  archer:           { type: Object, required: true },
  coaches:          { type: Array,  default: () => [] },
  bowTypes:         { type: Array,  default: () => [] },
  experienceLevels: { type: Array,  default: () => [] },
  categories:       { type: Array,  default: () => [] },
})

const submitting   = ref(false)
const confirmDelete = ref(false)
const errors       = ref({})

const form = ref({ ...props.archer })

function submit() {
  submitting.value = true
  errors.value     = {}

  router.put(`/admin/archers/${props.archer.id}`, form.value, {
    onError:  (e) => { errors.value = e },
    onFinish: () => { submitting.value = false },
  })
}

function deleteArcher() {
  router.delete(`/admin/archers/${props.archer.id}`)
}
</script>
