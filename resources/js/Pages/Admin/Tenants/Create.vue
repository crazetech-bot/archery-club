<template>
  <SuperAdminLayout title="New Tenant">
    <div class="mx-auto max-w-xl space-y-6">

      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 text-sm text-gray-500">
        <Link :href="route('admin.tenants.index')" class="hover:text-gray-900">Tenants</Link>
        <span>/</span>
        <span class="text-gray-900 font-medium">New Tenant</span>
      </div>

      <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <h1 class="text-lg font-bold text-gray-900">Create Tenant</h1>
        <p class="mt-1 text-sm text-gray-500">Set up a new archery club on the platform.</p>

        <form class="mt-6 space-y-5" @submit.prevent="submit">

          <div>
            <label class="block text-sm font-medium text-gray-700">Club Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Riverside Archery Club"
              class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-gray-400 focus:outline-none focus:ring-0"
              @input="autoSlug"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Slug <span class="font-normal text-gray-400">(subdomain)</span></label>
            <div class="mt-1 flex rounded-xl border border-gray-200 overflow-hidden focus-within:border-gray-400">
              <input
                v-model="form.slug"
                type="text"
                placeholder="riverside"
                class="flex-1 px-4 py-2.5 text-sm focus:outline-none"
              />
              <span class="flex items-center bg-gray-50 px-3 text-xs text-gray-400 border-l border-gray-200">.{{ $page.props.tenantDomain }}</span>
            </div>
            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-500">{{ form.errors.slug }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Plan</label>
            <select
              v-model="form.plan"
              class="mt-1 block w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-gray-400 focus:outline-none focus:ring-0 bg-white"
            >
              <option value="free">Free</option>
              <option value="starter">Starter</option>
              <option value="pro">Pro</option>
              <option value="enterprise">Enterprise</option>
            </select>
            <p v-if="form.errors.plan" class="mt-1 text-xs text-red-500">{{ form.errors.plan }}</p>
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <Link
              :href="route('admin.tenants.index')"
              class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-700 transition disabled:opacity-50"
            >
              Create Tenant
            </button>
          </div>

        </form>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import SuperAdminLayout from '@/Components/Layouts/SuperAdminLayout.vue'

const form = useForm({
  name: '',
  slug: '',
  plan: 'free',
})

function autoSlug() {
  form.slug = form.name
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
}

function submit() {
  form.post(route('admin.tenants.store'))
}
</script>
