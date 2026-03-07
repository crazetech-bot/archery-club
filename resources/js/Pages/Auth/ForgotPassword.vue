<template>
  <AuthLayout
    title="Forgot your password?"
    subtitle="Enter your email and we'll send you a reset link"
  >
    <form class="space-y-4" @submit.prevent="submit">
      <Input
        v-model="form.email"
        label="Email address"
        type="email"
        autocomplete="email"
        placeholder="you@example.com"
        :error="form.errors.email"
        required
      />

      <Alert v-if="status" type="success">{{ status }}</Alert>

      <Button variant="primary" type="submit" :loading="form.processing" class="w-full">
        Send Reset Link
      </Button>

      <div class="text-center">
        <Link href="/login" class="text-xs font-medium text-gray-400 hover:text-gray-700">
          ← Back to sign in
        </Link>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Components/Layouts/AuthLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Alert from '@/Components/UI/Alert.vue'
import Input from '@/Components/Forms/Input.vue'

defineProps({
  status: { type: String, default: null },
})

const form = useForm({ email: '' })

function submit() {
  form.post('/forgot-password')
}
</script>
