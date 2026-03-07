<template>
  <div class="flex min-h-screen flex-col bg-gray-50">

    <!-- Top bar with logo -->
    <header class="flex h-16 items-center justify-center border-b border-gray-100 bg-white">
      <div class="flex items-center gap-2.5">
        <!-- Target / bullseye logo mark -->
        <svg class="h-8 w-8 text-gray-900" viewBox="0 0 32 32" fill="none">
          <circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2"/>
          <circle cx="16" cy="16" r="10" stroke="currentColor" stroke-width="2"/>
          <circle cx="16" cy="16" r="5"  stroke="currentColor" stroke-width="2"/>
          <circle cx="16" cy="16" r="2"  fill="currentColor"/>
        </svg>
        <span class="text-lg font-bold tracking-tight text-gray-900">Archery OS</span>
      </div>
    </header>

    <!-- Centred card slot -->
    <main class="flex flex-1 items-center justify-center px-4 py-12">
      <div class="w-full max-w-sm">

        <!-- Page heading passed via slot prop -->
        <div v-if="title || subtitle" class="mb-8 text-center">
          <h1 v-if="title" class="text-2xl font-bold text-gray-900">{{ title }}</h1>
          <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
          <slot />
        </div>

      </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-gray-400">
      &copy; {{ year }} Archery Performance OS. All rights reserved.
    </footer>

  </div>
</template>

<script setup>
defineProps({
  title:    { type: String, default: null },
  subtitle: { type: String, default: null },
})

const year = new Date().getFullYear()
</script>
