<template>
  <AuthLayout title="Reset your password" subtitle="Choose a strong new password">
    <form class="space-y-4" @submit.prevent="submit">
      <Input
        v-model="form.email"
        label="Email address"
        type="email"
        autocomplete="email"
        :error="form.errors.email"
        required
      />

      <Input
        v-model="form.password"
        label="New Password"
        type="password"
        autocomplete="new-password"
        placeholder="••••••••"
        :error="form.errors.password"
        required
      />

      <Input
        v-model="form.password_confirmation"
        label="Confirm Password"
        type="password"
        autocomplete="new-password"
        placeholder="••••••••"
        :error="form.errors.password_confirmation"
        required
      />

      <Button variant="primary" type="submit" :loading="form.processing" class="w-full">
        Reset Password
      </Button>
    </form>
  </AuthLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/Forms/Input.vue'

const props = defineProps({
  token: { type: String, required: true },
  email: { type: String, default: '' },
})

const form = useForm({
  token:                 props.token,
  email:                 props.email,
  password:              '',
  password_confirmation: '',
})

function submit() {
  form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
