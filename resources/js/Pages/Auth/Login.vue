<template>
  <AuthLayout title="Sign in to your club" subtitle="Enter your credentials to continue">
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

      <div>
        <Input
          v-model="form.password"
          label="Password"
          type="password"
          autocomplete="current-password"
          placeholder="••••••••"
          :error="form.errors.password"
          required
        />
        <div class="mt-1.5 flex justify-end">
          <Link href="/forgot-password" class="text-xs font-medium text-gray-400 hover:text-gray-700">
            Forgot password?
          </Link>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          :class="[
            'relative inline-flex h-5 w-9 shrink-0 rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2',
            form.remember ? 'bg-gray-900' : 'bg-gray-200',
          ]"
          @click="form.remember = !form.remember"
        >
          <span
            :class="[
              'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition',
              form.remember ? 'translate-x-4' : 'translate-x-0',
            ]"
          />
        </button>
        <span class="text-sm text-gray-600">Remember me</span>
      </div>

      <Alert v-if="status" type="success">{{ status }}</Alert>

      <Button
        variant="primary"
        type="submit"
        :loading="form.processing"
        class="w-full"
      >
        Sign in
      </Button>
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

const form = useForm({
  email:    '',
  password: '',
  remember: false,
})

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>
