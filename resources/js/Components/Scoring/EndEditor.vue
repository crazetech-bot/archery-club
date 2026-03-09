<template>
  <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">

    <!-- ── Section header ───────────────────────────────────────────────────── -->
    <div class="mb-4 flex items-center justify-between">
      <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Ends</p>
      <button
        v-if="isEditable"
        :disabled="addingEnd"
        class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 active:scale-95 disabled:opacity-50"
        @click="addEnd"
      >
        {{ addingEnd ? 'Adding…' : '+ Add End' }}
      </button>
    </div>

    <!-- ── Empty state ───────────────────────────────────────────────────────── -->
    <div
      v-if="localEnds.length === 0"
      class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-400"
    >
      No ends yet. Add the first end to start scoring.
    </div>

    <!-- ── Ends UI ───────────────────────────────────────────────────────────── -->
    <div v-else class="space-y-4">

      <!-- End pill selector -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="end in localEnds"
          :key="end.id"
          :class="end.id === selectedEndId
            ? 'bg-gray-900 text-white border-gray-900'
            : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
          class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-medium transition-all active:scale-95"
          @click="selectedEndId = end.id"
        >
          <span class="font-bold">{{ end.end_number }}</span>
          <span class="tabular-nums text-xs opacity-60">{{ endTotal(end) }}</span>
        </button>
      </div>

      <!-- Active end workspace -->
      <div v-if="activeEnd" class="rounded-xl border border-gray-100 bg-gray-50 p-4">

        <!-- Active end header -->
        <div class="mb-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white">
              {{ activeEnd.end_number }}
            </span>
            <span class="text-sm font-semibold text-gray-900">End {{ activeEnd.end_number }}</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-sm font-bold tabular-nums text-gray-900">
              {{ endTotal(activeEnd) }}
            </span>
            <button
              v-if="isEditable"
              :disabled="deletingEndId === activeEnd.id"
              class="text-xs text-gray-400 transition hover:text-red-500 disabled:opacity-40"
              @click="deleteEnd(activeEnd)"
            >
              {{ deletingEndId === activeEnd.id ? '…' : 'Remove' }}
            </button>
          </div>
        </div>

        <!-- Shot grid for the active end -->
        <ShotGrid
          :end="activeEnd"
          :scorecard-id="scorecardId"
          :is-editable="isEditable"
          @shot-recorded="(shot) => onShotRecorded(activeEnd, shot)"
        />

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import ShotGrid from '@/Components/Scoring/ShotGrid.vue'

// ── Props / Emits ─────────────────────────────────────────────────────────────
const props = defineProps({
  scorecardId: { type: Number, required: true },
  ends:        { type: Array,  default: () => [] },
  isEditable:  { type: Boolean, default: false },
})

const emit = defineEmits(['end-added', 'end-deleted', 'shot-recorded'])

// ── State ─────────────────────────────────────────────────────────────────────
const localEnds     = ref(props.ends.map(e => ({ ...e, shots: [...(e.shots ?? [])] })))
const selectedEndId = ref(props.ends.at(-1)?.id ?? null)
const addingEnd     = ref(false)
const deletingEndId = ref(null)

// ── Active end ────────────────────────────────────────────────────────────────

/** The currently selected end object, or null if no ends exist. */
const activeEnd = computed(() =>
  localEnds.value.find(e => e.id === selectedEndId.value) ?? null
)

// ── Sync from parent ──────────────────────────────────────────────────────────

/**
 * Keep localEnds in sync when the parent store updates (e.g. on mount fetch).
 * Preserve the selected end if it still exists; otherwise fall back to last.
 */
watch(() => props.ends, (val) => {
  localEnds.value = val.map(e => ({ ...e, shots: [...(e.shots ?? [])] }))
  if (!selectedEndId.value || !val.find(e => e.id === selectedEndId.value)) {
    selectedEndId.value = val.at(-1)?.id ?? null
  }
})

// ── Helpers ───────────────────────────────────────────────────────────────────

/** Compute end total from its shots array (reactive via localEnds ref). */
function endTotal(end) {
  return (end.shots ?? []).reduce((sum, s) => {
    if (s.is_miss) return sum
    if (s.is_x)   return sum + 10
    return sum + (s.score ?? 0)
  }, 0)
}

// ── Add end ───────────────────────────────────────────────────────────────────
async function addEnd() {
  addingEnd.value = true
  const nextNumber = localEnds.value.length + 1

  try {
    const { data } = await axios.post(`/api/scorecards/${props.scorecardId}/ends`, {
      end_number: nextNumber,
    })

    const newEnd = { ...data, shots: [] }
    localEnds.value.push(newEnd)
    selectedEndId.value = newEnd.id  // auto-focus the newly created end
    emit('end-added', newEnd)
  } catch (err) {
    console.error('Add end failed:', err)
  } finally {
    addingEnd.value = false
  }
}

// ── Delete end ────────────────────────────────────────────────────────────────
async function deleteEnd(end) {
  deletingEndId.value = end.id

  try {
    await axios.delete(`/api/scorecards/${props.scorecardId}/ends/${end.id}`)
    localEnds.value = localEnds.value.filter(e => e.id !== end.id)

    // If the deleted end was selected, fall back to the new last end.
    if (selectedEndId.value === end.id) {
      selectedEndId.value = localEnds.value.at(-1)?.id ?? null
    }

    emit('end-deleted', end.id)
  } catch (err) {
    console.error('Delete end failed:', err)
  } finally {
    deletingEndId.value = null
  }
}

// ── Shot recorded ─────────────────────────────────────────────────────────────

/**
 * Update the shot into the matching end's shots array so the pill total
 * updates live without a round-trip, then bubble to the page.
 */
function onShotRecorded(end, shot) {
  const target = localEnds.value.find(e => e.id === end.id)
  if (target) target.shots.push(shot)
  emit('shot-recorded', shot)
}
</script>
