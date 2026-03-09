<template>
  <AppLayout :title="`Scorecard #${scorecardId}`">

    <!-- ── Loading ───────────────────────────────────────────────────────────── -->
    <div
      v-if="loading"
      class="flex min-h-[50vh] items-center justify-center"
    >
      <p class="text-sm text-gray-400">Loading scorecard…</p>
    </div>

    <!-- ── Not found ─────────────────────────────────────────────────────────── -->
    <div
      v-else-if="!scorecard"
      class="flex min-h-[50vh] items-center justify-center"
    >
      <p class="text-sm text-gray-400">Scorecard not found.</p>
    </div>

    <!-- ── Main content ───────────────────────────────────────────────────────── -->
    <div v-else class="animate-fade-in mx-auto max-w-3xl space-y-6 p-6">

      <!-- ── Back + header ─────────────────────────────────────────────────── -->
      <div>
        <a
          href="/scoring/scorecards"
          class="mb-4 inline-flex items-center gap-1 text-sm text-gray-400 hover:text-gray-700"
        >
          ← Scorecards
        </a>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-bold text-gray-900">
              {{ scorecard.archer?.user?.name ?? 'Scorecard' }}
            </h2>
            <p class="mt-0.5 text-sm text-gray-400">
              {{ scorecard.session?.title ?? '—' }}
              <span v-if="scorecard.template" class="ml-1 text-gray-300">·</span>
              <span v-if="scorecard.template" class="ml-1">{{ scorecard.template.name }}</span>
            </p>
          </div>

          <!-- Status actions -->
          <div class="flex items-center gap-2">
            <BaseButton
              v-if="scorecard.status === 'draft'"
              variant="secondary"
              :loading="submitting"
              loading-text="Submitting…"
              @click="submitScorecard"
            >
              Submit
            </BaseButton>
            <BaseButton
              v-if="scorecard.status === 'submitted'"
              variant="warning"
              :loading="locking"
              loading-text="Locking…"
              @click="lockScorecard"
            >
              Lock
            </BaseButton>
            <StatusBadge
              v-if="scorecard.status === 'locked'"
              status="locked"
            />
          </div>
        </div>
      </div>

      <!-- ── Score summary ──────────────────────────────────────────────────── -->
      <ScoreSummary :scorecard="scorecard" />

      <!-- ── End-based scoring ──────────────────────────────────────────────── -->
      <EndEditor
        v-if="isEndBased"
        :scorecard-id="scorecardId"
        :ends="ends"
        :is-editable="isEditable"
        @end-added="onEndAdded"
        @end-deleted="onEndDeleted"
        @shot-recorded="onShotRecorded"
      />

      <!-- ── Shot-based scoring (flat — no ends grouping) ───────────────────── -->
      <BaseCard v-else title="Shots" padding="p-5">
        <ShotGrid
          :end="{ id: null, shots: shots }"
          :scorecard-id="scorecardId"
          :is-editable="isEditable"
          @shot-recorded="onShotRecorded"
        />
      </BaseCard>

      <!-- ── Analytics ──────────────────────────────────────────────────────── -->
      <MetricsPanel
        :scorecard-id="scorecardId"
        :metrics="metrics"
        :is-editable="isEditable"
        @recalculated="onRecalculated"
      />

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import AppLayout    from '@/Layouts/AppLayout.vue'
import BaseButton   from '@/Components/Base/BaseButton.vue'
import BaseCard     from '@/Components/Base/BaseCard.vue'
import StatusBadge  from '@/Components/Base/StatusBadge.vue'
import ScoreSummary from '@/Components/Scoring/ScoreSummary.vue'
import EndEditor    from '@/Components/Scoring/EndEditor.vue'
import ShotGrid     from '@/Components/Scoring/ShotGrid.vue'
import MetricsPanel from '@/Components/Scoring/MetricsPanel.vue'
import { useScorecardStore } from '@/Stores/module4/scorecard.js'
import { useEndsStore }      from '@/Stores/module4/ends.js'
import { useShotsStore }     from '@/Stores/module4/shots.js'
import { useMetricsStore }   from '@/Stores/module4/metrics.js'

// ── Props (Inertia — replaces Vue Router's useRoute()) ────────────────────────
const props = defineProps({
  scorecard: { type: Object, required: true },
})

const scorecardId = props.scorecard.id

// ── Stores ────────────────────────────────────────────────────────────────────
const scorecardStore = useScorecardStore()
const endsStore      = useEndsStore()
const shotsStore     = useShotsStore()
const metricsStore   = useMetricsStore()

// ── Store-derived reactive data ───────────────────────────────────────────────
const loading   = computed(() => scorecardStore.loading)
const scorecard = computed(() => scorecardStore.scorecard)
const ends      = computed(() => endsStore.ends)
const shots     = computed(() => shotsStore.shots)
const metrics   = computed(() => metricsStore.metrics)

// ── Derived flags ─────────────────────────────────────────────────────────────
const isEditable = computed(() => scorecard.value?.status === 'draft')

/**
 * Default to end-based unless the template explicitly declares 'shot_based'.
 * This matches the EndEditor + ShotGrid component architecture.
 */
const isEndBased = computed(() =>
  scorecard.value?.template?.type !== 'shot_based'
)

// ── Local UI state ────────────────────────────────────────────────────────────
const submitting = ref(false)
const locking    = ref(false)

// ── Lifecycle ─────────────────────────────────────────────────────────────────

/**
 * Fetch fresh data from the API on mount.
 * scorecardStore.fetchScorecard() hydrates all four stores in one call.
 */
onMounted(async () => {
  await scorecardStore.fetchScorecard(scorecardId)
})

/** Clean up all store state when navigating away. */
onUnmounted(() => {
  scorecardStore.reset()
  endsStore.reset()
  shotsStore.reset()
  metricsStore.reset()
})

// ── Scorecard lifecycle ───────────────────────────────────────────────────────

async function submitScorecard() {
  submitting.value = true
  try {
    await scorecardStore.submit(scorecardId)
  } finally {
    submitting.value = false
  }
}

async function lockScorecard() {
  locking.value = true
  try {
    await scorecardStore.lock(scorecardId)
  } finally {
    locking.value = false
  }
}

// ── End events (from EndEditor) ───────────────────────────────────────────────

/**
 * EndEditor made the POST internally; mirror the result into the ends store
 * so the store is the single source of truth for downstream consumers.
 */
function onEndAdded(end) {
  endsStore.ends.push(end)
  syncSummary()
}

/**
 * EndEditor made the DELETE internally; remove from store via splice so
 * Vue 3's Proxy reactivity tracks the mutation.
 */
function onEndDeleted(endId) {
  const idx = endsStore.ends.findIndex(e => e.id === endId)
  if (idx !== -1) endsStore.ends.splice(idx, 1)
  syncSummary()
}

// ── Shot events (from EndEditor → ShotGrid or flat ShotGrid) ─────────────────

/**
 * ShotGrid made the POST internally; sync the shot into the ends store's
 * nested shots array (end-based) or the shots store (flat mode), then
 * recompute summary totals without a round-trip.
 */
function onShotRecorded(shot) {
  if (shot.scorecard_end_id) {
    // End-based: append to the correct end's shots array in the ends store.
    endsStore.addShotToEnd(shot.scorecard_end_id, shot)
  } else {
    // Flat mode: append to the shots store, guarding against duplicates.
    const exists = shotsStore.shots.some(s => s.id === shot.id)
    if (!exists) shotsStore.shots.push(shot)
  }
  syncSummary()
}

// ── Metrics event (from MetricsPanel) ────────────────────────────────────────

/**
 * Server recalculated metrics; update the metrics store and sync the
 * authoritative totals back onto the scorecard store.
 */
function onRecalculated(fresh) {
  metricsStore.setMetrics(fresh)
  scorecardStore.syncTotals({
    totalScore: fresh.total_score ?? scorecard.value?.total_score,
    xCount:     fresh.x_count    ?? scorecard.value?.x_count,
    arrowCount: scorecard.value?.arrow_count,
  })
}

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Recompute scorecard summary totals from the ends store's live shots data.
 * Called after every end/shot mutation to keep the summary card reactive
 * without a full server round-trip.
 */
function syncSummary() {
  const allShots = endsStore.ends.flatMap(e => e.shots ?? [])
  const totalScore = allShots.reduce((sum, shot) => {
    if (shot.is_miss) return sum
    if (shot.is_x)   return sum + 10
    return sum + (shot.score ?? 0)
  }, 0)
  scorecardStore.syncTotals({
    totalScore,
    xCount:     allShots.filter(s => s.is_x).length,
    arrowCount: allShots.length,
  })
}
</script>
