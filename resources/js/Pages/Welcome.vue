<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4">
    <div class="text-center max-w-lg">

      <!-- Logo -->
      <div class="w-16 h-16 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-8">
        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="2"/>
          <circle cx="12" cy="12" r="5" fill="none" stroke="currentColor" stroke-width="1.5"/>
          <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5"/>
        </svg>
      </div>

      <h1 class="text-4xl font-bold text-gray-900 mb-3">Archery Performance OS</h1>
      <p class="text-gray-500 text-lg mb-10">
        Multi-tenant SaaS for archery clubs — training, live scoring, coaching, and competition management.
      </p>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a
          href="/login"
          class="inline-flex items-center justify-center px-6 py-3 bg-gray-900 text-white font-medium rounded-xl hover:bg-gray-700 transition"
        >
          Sign in to your club
        </a>
        <a
          href="#"
          class="inline-flex items-center justify-center px-6 py-3 border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition"
        >
          Learn more
        </a>
      </div>

      <p class="text-xs text-gray-400 mt-12">
        Club admins access via your club subdomain (e.g. yourclub.mynetdns.info)
      </p>
    </div>
  </div>
</template>
