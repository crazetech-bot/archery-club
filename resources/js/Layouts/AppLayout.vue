<template>
  <div class="min-h-screen bg-gray-50 flex">

    <!-- Sidebar -->
    <aside
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-200 lg:translate-x-0 lg:static lg:z-auto"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center px-6 border-b border-gray-100 flex-shrink-0">
        <a :href="homeUrl" class="flex items-center gap-2">
          <div class="w-7 h-7 bg-gray-900 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="2"/>
              <circle cx="12" cy="12" r="5" fill="none" stroke="currentColor" stroke-width="1.5"/>
              <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5"/>
            </svg>
          </div>
          <span class="font-bold text-gray-900 text-sm">Archery OS</span>
        </a>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-4 px-3">
        <div v-for="section in navigation" :key="section.label" class="mb-6">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-2">
            {{ section.label }}
          </p>
          <ul class="space-y-0.5">
            <li v-for="item in section.items" :key="item.name">
              <a
                :href="item.href"
                :class="isActive(item.href)
                  ? 'bg-gray-100 text-gray-900 font-medium'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition"
              >
                <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                {{ item.name }}
                <span
                  v-if="item.badge"
                  class="ml-auto text-xs bg-gray-900 text-white rounded-full px-1.5 py-0.5 leading-none"
                >
                  {{ item.badge }}
                </span>
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- User footer -->
      <div class="border-t border-gray-100 p-3 flex-shrink-0">
        <div class="flex items-center gap-3 px-2 py-2">
          <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 flex-shrink-0">
            {{ userInitials }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
            <p class="text-xs text-gray-500 capitalize truncate">{{ role }}</p>
          </div>
          <form method="POST" :action="logoutAction">
            <input type="hidden" name="_token" :value="csrfToken" />
            <button
              type="submit"
              title="Log out"
              class="text-gray-400 hover:text-gray-700 transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
            </button>
          </form>
        </div>
      </div>
    </aside>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-black/40 lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-w-0">

      <!-- Top bar -->
      <header class="h-16 bg-white border-b border-gray-200 flex items-center px-4 sm:px-6 flex-shrink-0">
        <!-- Mobile hamburger -->
        <button
          class="lg:hidden mr-4 text-gray-500 hover:text-gray-900"
          @click="sidebarOpen = !sidebarOpen"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <!-- Page title -->
        <h1 class="text-base font-semibold text-gray-900 flex-1">{{ title }}</h1>

        <!-- Flash messages in top bar -->
        <div v-if="$page.props.flash?.success" class="hidden sm:flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-xl mr-4">
          <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          {{ $page.props.flash.success }}
        </div>
      </header>

      <!-- Page slot -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  title: {
    type: String,
    default: 'Archery OS',
  },
})

const page       = usePage()
const user       = computed(() => page.props.auth?.user)
const role       = computed(() => page.props.auth?.role ?? '')
const csrfToken  = computed(() => page.props.csrf_token ?? document.querySelector('meta[name="csrf-token"]')?.content ?? '')
const logoutAction = '/logout'
const sidebarOpen  = ref(false)

// ── User initials ─────────────────────────────────────────────────────────────
const userInitials = computed(() => {
  const name = user.value?.name ?? ''
  return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase()
})

// ── Current URL ───────────────────────────────────────────────────────────────
function isActive(href) {
  return window.location.pathname.startsWith(href)
}

// ── Home URL by role ──────────────────────────────────────────────────────────
const homeUrl = computed(() => {
  if (role.value === 'club_admin') return '/admin/dashboard'
  if (role.value === 'coach')      return '/coach/dashboard'
  return '/archer/dashboard'
})

// ── Icon components ───────────────────────────────────────────────────────────
const HomeIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' }),
  ]),
}

const SessionIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' }),
  ]),
}

const EquipmentIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }),
    h('circle', { cx: '12', cy: '12', r: '3', 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2' }),
  ]),
}

const TrophyIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z' }),
  ]),
}

const UserIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' }),
  ]),
}

const LiveIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('circle', { cx: '12', cy: '12', r: '3', fill: 'currentColor' }),
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8.111 16.404a5.5 5.5 0 010-8.808m7.778 8.808a5.5 5.5 0 000-8.808M5.27 19.248a10 10 0 010-14.496m13.46 14.496a10 10 0 000-14.496' }),
  ]),
}

const UsersIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' }),
  ]),
}

const LaneIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' }),
  ]),
}

const CalendarIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' }),
  ]),
}

const ChartIcon = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' }),
  ]),
}

// ── Role-aware navigation ─────────────────────────────────────────────────────
const navigation = computed(() => {
  const r = role.value

  if (r === 'club_admin') {
    return [
      {
        label: 'Overview',
        items: [
          { name: 'Dashboard',    href: '/admin/dashboard',    icon: HomeIcon },
          { name: 'Live Monitor', href: '/live/monitor',       icon: LiveIcon },
        ],
      },
      {
        label: 'Club',
        items: [
          { name: 'Members',      href: '/admin/members',      icon: UsersIcon },
          { name: 'Lanes',        href: '/admin/lanes',        icon: LaneIcon },
          { name: 'Bookings',     href: '/admin/bookings',     icon: CalendarIcon },
          { name: 'Competitions', href: '/admin/competitions', icon: TrophyIcon },
        ],
      },
    ]
  }

  if (r === 'coach') {
    return [
      {
        label: 'Overview',
        items: [
          { name: 'Dashboard',    href: '/coach/dashboard',    icon: HomeIcon },
          { name: 'Live Monitor', href: '/live/monitor',       icon: LiveIcon },
        ],
      },
      {
        label: 'Coaching',
        items: [
          { name: 'My Archers',   href: '/coach/archers',      icon: UsersIcon },
        ],
      },
    ]
  }

  // Default: archer
  return [
    {
      label: 'Overview',
      items: [
        { name: 'Dashboard',    href: '/archer/dashboard',    icon: HomeIcon },
      ],
    },
    {
      label: 'Training',
      items: [
        { name: 'Sessions',     href: '/archer/sessions',     icon: SessionIcon },
        { name: 'Equipment',    href: '/archer/equipment',    icon: EquipmentIcon },
        { name: 'Competitions', href: '/archer/competitions', icon: TrophyIcon },
      ],
    },
    {
      label: 'Account',
      items: [
        { name: 'Profile',      href: '/archer/profile',      icon: UserIcon },
      ],
    },
  ]
})
</script>
