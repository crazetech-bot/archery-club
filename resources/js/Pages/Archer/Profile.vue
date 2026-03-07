<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6">

      <!-- ── Header ──────────────────────────────────────────────────────────── -->
      <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Archer</p>
        <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Profile</h1>
      </div>

      <!-- ── Profile card ────────────────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <div class="flex items-center gap-5 border-b border-gray-50 p-6">
          <!-- Avatar -->
          <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xl font-bold text-white">
            {{ initials(archer.name) }}
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ archer.name }}</h2>
            <p class="text-sm text-gray-400">
              {{ archer.category }}
              <span v-if="archer.age"> · {{ archer.age }} years old</span>
            </p>
            <p class="text-xs text-gray-400">Member since {{ formatDate(archer.created_at) }}</p>
          </div>
        </div>

        <!-- Personal info form -->
        <form class="divide-y divide-gray-50" @submit.prevent="saveProfile">
          <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
            <Input
              v-model="profileForm.name"
              label="Full Name"
              :error="profileErrors.name"
              required
            />
            <Input
              v-model="profileForm.email"
              label="Email Address"
              type="email"
              :error="profileErrors.email"
              required
            />
            <Input
              v-model="profileForm.date_of_birth"
              label="Date of Birth"
              type="date"
              :error="profileErrors.date_of_birth"
            />
            <Select
              v-model="profileForm.category"
              label="Category"
              :options="categoryOptions"
              :error="profileErrors.category"
            />
            <Input
              v-model="profileForm.phone"
              label="Phone"
              type="tel"
              :error="profileErrors.phone"
            />
            <Select
              v-model="profileForm.dominant_hand"
              label="Dominant Hand"
              :options="handOptions"
              :error="profileErrors.dominant_hand"
            />
          </div>

          <div class="flex items-center justify-between px-6 py-4">
            <Alert v-if="profileSaved" type="success" class="flex-1 mr-4">Profile updated successfully.</Alert>
            <div class="ml-auto">
              <Button variant="primary" type="submit" :loading="savingProfile">Save Changes</Button>
            </div>
          </div>
        </form>
      </div>

      <!-- ── Change password ─────────────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <div class="border-b border-gray-50 px-6 py-4">
          <h2 class="text-sm font-semibold text-gray-900">Change Password</h2>
        </div>
        <form class="space-y-4 p-6" @submit.prevent="savePassword">
          <Input
            v-model="passwordForm.current_password"
            label="Current Password"
            type="password"
            autocomplete="current-password"
            :error="passwordErrors.current_password"
            required
          />
          <Input
            v-model="passwordForm.password"
            label="New Password"
            type="password"
            autocomplete="new-password"
            :error="passwordErrors.password"
            required
          />
          <Input
            v-model="passwordForm.password_confirmation"
            label="Confirm New Password"
            type="password"
            autocomplete="new-password"
            :error="passwordErrors.password_confirmation"
            required
          />
          <div class="flex items-center justify-between">
            <Alert v-if="passwordSaved" type="success">Password updated.</Alert>
            <div class="ml-auto">
              <Button variant="primary" type="submit" :loading="savingPassword">Update Password</Button>
            </div>
          </div>
        </form>
      </div>

      <!-- ── Danger zone ─────────────────────────────────────────────────────── -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-red-100">
        <div class="border-b border-red-50 px-6 py-4">
          <h2 class="text-sm font-semibold text-red-600">Danger Zone</h2>
        </div>
        <div class="flex items-center justify-between p-6">
          <div>
            <p class="text-sm font-medium text-gray-700">Delete Account</p>
            <p class="mt-0.5 text-xs text-gray-400">Permanently remove your account and all associated data.</p>
          </div>
          <Button variant="danger" @click="showDeleteModal = true">Delete Account</Button>
        </div>
      </div>

      <!-- ── Delete account modal ────────────────────────────────────────────── -->
      <Modal v-model="showDeleteModal" title="Delete Account" size="sm">
        <div class="space-y-4">
          <Alert type="error">This action is irreversible. All your data will be permanently deleted.</Alert>
          <Input
            v-model="deleteConfirmText"
            :label="`Type your name to confirm: ${archer.name}`"
            placeholder="Your full name"
          />
          <div class="flex justify-end gap-3">
            <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
            <Button
              variant="danger"
              :disabled="deleteConfirmText !== archer.name"
              :loading="deleting"
              @click="deleteAccount"
            >
              Delete My Account
            </Button>
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
import Alert from '@/Components/UI/Alert.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'

// ── Props ──────────────────────────────────────────────────────────────────────

const props = defineProps({
  archer: { type: Object, required: true },
  user:   { type: Object, required: true },
})

// ── Profile form ───────────────────────────────────────────────────────────────

const savingProfile = ref(false)
const profileSaved  = ref(false)
const profileErrors = reactive({})

const profileForm = reactive({
  name:          props.user.name,
  email:         props.user.email,
  date_of_birth: props.archer.date_of_birth ?? '',
  category:      props.archer.category ?? '',
  phone:         props.archer.phone ?? '',
  dominant_hand: props.archer.dominant_hand ?? '',
})

const categoryOptions = [
  { label: 'U12',    value: 'U12' },
  { label: 'U15',    value: 'U15' },
  { label: 'U18',    value: 'U18' },
  { label: 'U21',    value: 'U21' },
  { label: 'Senior', value: 'Senior' },
  { label: 'Master', value: 'Master' },
]

const handOptions = [
  { label: 'Right', value: 'right' },
  { label: 'Left',  value: 'left' },
]

function saveProfile() {
  savingProfile.value = true
  profileSaved.value  = false
  Object.keys(profileErrors).forEach((k) => delete profileErrors[k])

  router.put('/archer/profile', { ...profileForm }, {
    preserveScroll: true,
    onSuccess: () => { profileSaved.value = true; setTimeout(() => { profileSaved.value = false }, 3000) },
    onError: (err) => Object.assign(profileErrors, err),
    onFinish: () => { savingProfile.value = false },
  })
}

// ── Password form ──────────────────────────────────────────────────────────────

const savingPassword = ref(false)
const passwordSaved  = ref(false)
const passwordErrors = reactive({})

const passwordForm = reactive({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

function savePassword() {
  savingPassword.value = true
  passwordSaved.value  = false
  Object.keys(passwordErrors).forEach((k) => delete passwordErrors[k])

  router.put('/archer/password', { ...passwordForm }, {
    preserveScroll: true,
    onSuccess: () => {
      passwordSaved.value = true
      passwordForm.current_password = ''
      passwordForm.password = ''
      passwordForm.password_confirmation = ''
      setTimeout(() => { passwordSaved.value = false }, 3000)
    },
    onError: (err) => Object.assign(passwordErrors, err),
    onFinish: () => { savingPassword.value = false },
  })
}

// ── Delete account ─────────────────────────────────────────────────────────────

const showDeleteModal   = ref(false)
const deleteConfirmText = ref('')
const deleting          = ref(false)

function deleteAccount() {
  if (deleteConfirmText.value !== props.archer.name) return
  deleting.value = true

  router.delete('/archer/account', {
    onFinish: () => { deleting.value = false },
  })
}

// ── Helpers ────────────────────────────────────────────────────────────────────

function initials(name) {
  return name.split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })
}
</script>
