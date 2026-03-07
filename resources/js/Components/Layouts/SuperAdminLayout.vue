<template>
  <div class="flex h-screen overflow-hidden bg-gray-50">

    <!-- ── Desktop sidebar ──────────────────────────────────────────────── -->
    <aside class="hidden w-56 shrink-0 flex-col border-r border-gray-100 bg-white lg:flex">

      <!-- Logo -->
      <div class="flex h-16 items-center gap-2.5 border-b border-gray-50 px-5">
        <svg class="h-7 w-7 text-gray-900" viewBox="0 0 32 32" fill="none">
          <circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2"/>
          <circle cx="16" cy="16" r="10" stroke="currentColor" stroke-width="2"/>
          <circle cx="16" cy="16" r="5"  stroke="currentColor" stroke-width="2"/>
          <circle cx="16" cy="16" r="2"  fill="currentColor"/>
        </svg>
        <div>
          <p class="text-sm font-bold tracking-tight text-gray-900">Archery OS</p>
          <p class="text-xs text-gray-400">Super Admin</p>
        </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto px-3 py-4">
        <ul class="space-y-0.5">
          <li v-for="item in navItems" :key="item.label">
            <Link
              :href="item.href"
              :class="[
                'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors',
                isActive(item.match)
                  ? 'bg-gray-900 text-white'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
              ]"
            >
              <component
                :is="item.icon"
                :class="['h-5 w-5 shrink-0', isActive(item.match) ? 'text-white' : 'text-gray-400 group-hover:text-gray-600']"
              />
              {{ item.label }}
            </Link>
          </li>
        </ul>
      </nav>

      <!-- User -->
      <div class="border-t border-gray-50 p-3">
        <button
          class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left hover:bg-gray-50"
          @click="userMenuOpen = !userMenuOpen"
        >
          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white">
            {{ initials }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-900">{{ auth?.user?.name }}</p>
            <p class="truncate text-xs text-gray-400">Super Admin</p>
          </div>
          <svg
            :class="['h-4 w-4 shrink-0 text-gray-400 transition-transform', userMenuOpen ? 'rotate-180' : '']"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>

        <Transition
          enter-active-class="transition duration-100 ease-out"
          enter-from-class="opacity-0 scale-95"
          leave-active-class="transition duration-75"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="userMenuOpen" class="mt-1 rounded-xl border border-gray-100 bg-white py-1 shadow-lg">
            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Sign out
            </Link>
          </div>
        </Transition>
      </div>
    </aside>

    <!-- ── Mobile overlay ───────────────────────────────────────────────── -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="mobileMenuOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="mobileMenuOpen = false"/>
    </Transition>

    <Transition enter-active-class="transition duration-200" enter-from-class="-translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition duration-150" leave-to-class="-translate-x-full">
      <aside v-if="mobileMenuOpen" class="fixed inset-y-0 left-0 z-50 flex w-56 flex-col border-r border-gray-100 bg-white lg:hidden">
        <div class="flex h-16 items-center justify-between border-b border-gray-50 px-5">
          <span class="font-bold text-gray-900">Archery OS</span>
          <button class="rounded-lg p-1 hover:bg-gray-100" @click="mobileMenuOpen = false">
            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <nav class="flex-1 px-3 py-4">
          <ul class="space-y-0.5">
            <li v-for="item in navItems" :key="item.label">
              <Link
                :href="item.href"
                :class="[
                  'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                  isActive(item.match) ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50',
                ]"
                @click="mobileMenuOpen = false"
              >
                <component :is="item.icon" class="h-5 w-5 shrink-0"/>
                {{ item.label }}
              </Link>
            </li>
          </ul>
        </nav>
        <div class="border-t border-gray-50 p-3">
          <div class="flex items-center gap-3 px-3 py-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white">{{ initials }}</div>
            <div>
              <p class="text-sm font-medium text-gray-900">{{ auth?.user?.name }}</p>
              <Link :href="route('logout')" method="post" as="button" class="text-xs text-red-500 hover:underline">Sign out</Link>
            </div>
          </div>
        </div>
      </aside>
    </Transition>

    <!-- ── Main area ─────────────────────────────────────────────────────── -->
    <div class="flex flex-1 flex-col overflow-hidden">

      <!-- Mobile top bar -->
      <header class="flex h-16 items-center justify-between border-b border-gray-100 bg-white px-4 lg:hidden">
        <button class="rounded-xl p-2 hover:bg-gray-100" @click="mobileMenuOpen = true">
          <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <span class="font-bold text-gray-900">Archery OS</span>
        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">Super Admin</span>
      </header>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="mx-6 mt-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700 ring-1 ring-green-200">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="mx-6 mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700 ring-1 ring-red-200">
        {{ $page.props.flash.error }}
      </div>

      <main class="flex-1 overflow-y-auto px-6 py-6">
        <slot />
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, markRaw } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

defineProps({
  title: { type: String, default: '' },
})

const page = usePage()
const auth = computed(() => page.props.auth)

const initials = computed(() =>
  (auth.value?.user?.name ?? 'S').split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
)

const IconGrid = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>` })
const IconBuilding = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>` })

const navItems = [
  { label: 'Overview', href: route('admin.dashboard'),       match: '/admin',         icon: IconGrid },
  { label: 'Tenants',  href: route('admin.tenants.index'),   match: '/admin/tenants', icon: IconBuilding },
]

function isActive(prefix) {
  if (prefix === '/admin') {
    return page.url === '/admin' || page.url === '/admin/'
  }
  return page.url.startsWith(prefix)
}

const mobileMenuOpen = ref(false)
const userMenuOpen   = ref(false)
</script>
