<template>
  <div class="flex h-screen overflow-hidden bg-gray-50">

    <!-- ── Desktop sidebar ───────────────────────────────────────────────── -->
    <aside class="hidden w-64 shrink-0 flex-col border-r border-gray-100 bg-white lg:flex">

      <!-- Logo + role badge -->
      <div class="flex h-16 items-center justify-between border-b border-gray-50 px-5">
        <div class="flex items-center gap-2.5">
          <svg class="h-7 w-7 text-gray-900" viewBox="0 0 32 32" fill="none">
            <circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2"/>
            <circle cx="16" cy="16" r="10" stroke="currentColor" stroke-width="2"/>
            <circle cx="16" cy="16" r="5"  stroke="currentColor" stroke-width="2"/>
            <circle cx="16" cy="16" r="2"  fill="currentColor"/>
          </svg>
          <span class="font-bold tracking-tight text-gray-900">Archery OS</span>
        </div>
        <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700">Admin</span>
      </div>

      <!-- Nav with section grouping -->
      <nav class="flex-1 overflow-y-auto px-3 py-4">

        <div v-for="group in navGroups" :key="group.label" class="mb-5">
          <p class="mb-1.5 px-3 text-xs font-semibold uppercase tracking-widest text-gray-400">
            {{ group.label }}
          </p>
          <ul class="space-y-0.5">
            <li v-for="item in group.items" :key="item.label">
              <Link
                :href="item.href"
                :class="[
                  'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors',
                  isActive(item.href)
                    ? 'bg-gray-900 text-white'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                ]"
              >
                <component
                  :is="item.icon"
                  :class="[
                    'h-5 w-5 shrink-0',
                    isActive(item.href) ? 'text-white' : 'text-gray-400 group-hover:text-gray-600',
                  ]"
                />
                {{ item.label }}
              </Link>
            </li>
          </ul>
        </div>
      </nav>

      <!-- User section -->
      <div class="border-t border-gray-50 p-3">
        <button
          class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left hover:bg-gray-50"
          @click="userMenuOpen = !userMenuOpen"
        >
          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-purple-600 text-xs font-bold text-white">
            {{ initials }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-900">{{ auth.user.name }}</p>
            <p class="truncate text-xs text-gray-400">Club Admin</p>
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
              href="/admin/settings"
              class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="userMenuOpen = false"
            >
              <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Settings
            </Link>
            <Link
              href="/billing"
              class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
              @click="userMenuOpen = false"
            >
              <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
              Billing
            </Link>
            <div class="my-1 border-t border-gray-100"/>
            <button
              class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
              @click="logout"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Sign out
            </button>
          </div>
        </Transition>
      </div>
    </aside>

    <!-- ── Mobile overlay ─────────────────────────────────────────────────── -->
    <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="mobileMenuOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="mobileMenuOpen = false"/>
    </Transition>

    <Transition enter-active-class="transition duration-200" enter-from-class="-translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition duration-150" leave-to-class="-translate-x-full">
      <aside v-if="mobileMenuOpen" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-100 bg-white lg:hidden">
        <div class="flex h-16 items-center justify-between border-b border-gray-50 px-5">
          <div class="flex items-center gap-2.5">
            <svg class="h-7 w-7 text-gray-900" viewBox="0 0 32 32" fill="none">
              <circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2"/>
              <circle cx="16" cy="16" r="10" stroke="currentColor" stroke-width="2"/>
              <circle cx="16" cy="16" r="5"  stroke="currentColor" stroke-width="2"/>
              <circle cx="16" cy="16" r="2"  fill="currentColor"/>
            </svg>
            <span class="font-bold text-gray-900">Archery OS</span>
          </div>
          <button class="rounded-lg p-1 hover:bg-gray-100" @click="mobileMenuOpen = false">
            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4">
          <div v-for="group in navGroups" :key="group.label" class="mb-4">
            <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ group.label }}</p>
            <ul class="space-y-0.5">
              <li v-for="item in group.items" :key="item.label">
                <Link
                  :href="item.href"
                  :class="[
                    'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                    isActive(item.href) ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50',
                  ]"
                  @click="mobileMenuOpen = false"
                >
                  <component :is="item.icon" class="h-5 w-5 shrink-0"/>
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </div>
        </nav>
        <div class="border-t border-gray-50 p-3">
          <div class="flex items-center gap-3 px-3 py-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-purple-600 text-xs font-bold text-white">{{ initials }}</div>
            <div>
              <p class="text-sm font-medium text-gray-900">{{ auth.user.name }}</p>
              <button class="text-xs text-red-500 hover:underline" @click="logout">Sign out</button>
            </div>
          </div>
        </div>
      </aside>
    </Transition>

    <!-- ── Main area ──────────────────────────────────────────────────────── -->
    <div class="flex flex-1 flex-col overflow-hidden">

      <!-- Mobile top bar -->
      <header class="flex h-16 items-center justify-between border-b border-gray-100 bg-white px-4 lg:hidden">
        <button class="rounded-xl p-2 hover:bg-gray-100" @click="mobileMenuOpen = true">
          <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <span class="font-bold text-gray-900">Archery OS</span>
        <span class="rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold text-purple-700">Admin</span>
      </header>

      <!-- Page slot -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, markRaw } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

// ── Icons ─────────────────────────────────────────────────────────────────────
const IconHome      = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>` })
const IconUsers     = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>` })
const IconUserGroup = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>` })
const IconMap       = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>` })
const IconCalendar  = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>` })
const IconChart     = markRaw({ template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>` })

// ── Page + auth ───────────────────────────────────────────────────────────────
const page = usePage()
const auth = computed(() => page.props.auth)

const initials = computed(() =>
  (auth.value?.user?.name ?? 'A').split(' ').slice(0, 2).map((w) => w[0]).join('').toUpperCase()
)

// ── Navigation groups ─────────────────────────────────────────────────────────
const navGroups = [
  {
    label: 'Overview',
    items: [
      { label: 'Dashboard', href: '/admin/dashboard', icon: IconHome },
    ],
  },
  {
    label: 'Club',
    items: [
      { label: 'Members',      href: '/admin/members',      icon: IconUsers },
      { label: 'Lanes',        href: '/admin/lanes',         icon: IconMap },
      { label: 'Bookings',     href: '/admin/bookings',      icon: IconCalendar },
      { label: 'Competitions', href: '/admin/competitions',  icon: IconUserGroup },
    ],
  },
  {
    label: 'Insights',
    items: [
      { label: 'Reports', href: '/reports/club', icon: IconChart },
    ],
  },
]

function isActive(href) { return page.url.startsWith(href) }

const mobileMenuOpen = ref(false)
const userMenuOpen   = ref(false)

function logout() { router.post('/logout') }
</script>
