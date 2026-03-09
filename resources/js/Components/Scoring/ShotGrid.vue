<template>
  <div class="space-y-4">

    <!-- ── Recorded shots ──────────────────────────────────────────────────── -->
    <div v-if="end.shots && end.shots.length > 0" class="flex flex-wrap gap-3">
      <div
        v-for="shot in sortedShots"
        :key="shot.id"
        class="flex flex-col items-center gap-1"
      >
        <UiCircle
          :score="shot.score"
          :is-x="shot.is_x"
          :is-miss="shot.is_miss"
          :title="`Shot ${shot.shot_number}`"
        />
        <span class="text-[10px] tabular-nums text-gray-400">{{ shot.shot_number }}</span>
      </div>
    </div>
    <p v-else class="text-xs italic text-gray-400">No shots recorded yet.</p>

    <!-- ── Add shot panel (editable only) ────────────────────────────────── -->
    <div v-if="isEditable" class="space-y-3 rounded-xl border border-gray-100 bg-gray-50 p-4">

      <!-- Panel header + keyboard help toggle -->
      <div class="flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Add shot</p>
        <button
          class="rounded px-1.5 py-0.5 text-xs text-gray-300 transition hover:bg-gray-200 hover:text-gray-600"
          title="Keyboard shortcuts (?)"
          type="button"
          @click="showKeyHelp = !showKeyHelp"
        >
          ?
        </button>
      </div>

      <!-- Keyboard shortcut reference (toggled by ? key or button) -->
      <Transition
        enter-from-class="opacity-0 -translate-y-1"
        enter-active-class="transition duration-150"
        leave-to-class="opacity-0 -translate-y-1"
        leave-active-class="transition duration-100"
      >
        <div
          v-if="showKeyHelp"
          class="rounded-lg border border-gray-200 bg-white p-3"
        >
          <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
            Keyboard shortcuts
          </p>
          <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-xs text-gray-600">
            <div class="flex items-center gap-2">
              <kbd class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-700 ring-1 ring-gray-300">1</kbd>–<kbd class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-700 ring-1 ring-gray-300">9</kbd>
              <span>Score 1–9</span>
            </div>
            <div class="flex items-center gap-2">
              <kbd class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-700 ring-1 ring-gray-300">0</kbd>
              <span>Score 10</span>
            </div>
            <div class="flex items-center gap-2">
              <kbd class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-700 ring-1 ring-gray-300">X</kbd>
              <span>Toggle X</span>
            </div>
            <div class="flex items-center gap-2">
              <kbd class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-700 ring-1 ring-gray-300">M</kbd>
              <span>Toggle miss</span>
            </div>
            <div class="flex items-center gap-2">
              <kbd class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-700 ring-1 ring-gray-300">Enter</kbd>
              <span>Record shot</span>
            </div>
            <div class="flex items-center gap-2">
              <kbd class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-700 ring-1 ring-gray-300">Esc</kbd>
              <span>Clear pending</span>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Score buttons 0–10 -->
      <div class="flex flex-wrap gap-1.5">
        <UiPill
          v-for="n in scoreOptions"
          :key="n"
          square
          :active="newScore === n && !newIsX && !newIsMiss"
          active-variant="gray"
          @click="selectScore(n)"
        >
          {{ n }}
        </UiPill>
      </div>

      <!-- X / Miss toggles + pending preview + Record -->
      <div class="flex items-center gap-2">

        <UiPill
          :active="newIsX"
          active-variant="amber"
          class="font-bold"
          @click="toggleX"
        >
          X
        </UiPill>

        <UiPill
          :active="newIsMiss"
          active-variant="red"
          @click="toggleMiss"
        >
          M
        </UiPill>

        <!-- Pending preview — same colours as recorded shots for instant confirmation -->
        <div
          v-if="newScore !== null"
          class="ml-1 flex items-center gap-1.5"
          title="Pending — press Enter or tap Record to commit"
        >
          <span class="text-[10px] text-gray-400">→</span>
          <UiCircle
            :score="newScore"
            :is-x="newIsX"
            :is-miss="newIsMiss"
            size="sm"
          />
        </div>

        <div class="flex-1" />

        <BaseButton
          :disabled="newScore === null"
          :loading="submitting"
          loading-text="…"
          size="sm"
          @click="addShot"
        >
          Record
        </BaseButton>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import BaseButton from '@/Components/Base/BaseButton.vue'
import UiCircle   from '@/Components/Base/UiCircle.vue'
import UiPill     from '@/Components/Base/UiPill.vue'

// ── Props / Emits ─────────────────────────────────────────────────────────────
const props = defineProps({
  end:         { type: Object, required: true },
  scorecardId: { type: Number, required: true },
  isEditable:  { type: Boolean, default: false },
})

const emit = defineEmits(['shot-recorded'])

// ── State ─────────────────────────────────────────────────────────────────────
const newScore    = ref(null)
const newIsX      = ref(false)
const newIsMiss   = ref(false)
const submitting  = ref(false)
const showKeyHelp = ref(false)

const scoreOptions = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]

// ── Computed ──────────────────────────────────────────────────────────────────
const sortedShots = computed(() =>
  [...(props.end.shots ?? [])].sort((a, b) => a.shot_number - b.shot_number)
)

// ── Score selection ───────────────────────────────────────────────────────────
function selectScore(n) {
  newScore.value  = n
  newIsMiss.value = false
}

function toggleX() {
  newIsX.value    = !newIsX.value
  newIsMiss.value = false
  if (newIsX.value) newScore.value = 10
}

function toggleMiss() {
  newIsMiss.value = !newIsMiss.value
  newIsX.value    = false
  if (newIsMiss.value) newScore.value = 0
}

function clearPending() {
  newScore.value  = null
  newIsX.value    = false
  newIsMiss.value = false
}

// ── Keyboard shortcuts ────────────────────────────────────────────────────────
/**
 * Scoped keydown handler — active only when isEditable is true.
 *
 * Key map:
 *   1–9        → score 1–9
 *   0          → score 10  (score 0 = miss; use M instead)
 *   X          → toggle X modifier
 *   M          → toggle Miss
 *   Backspace  → clear pending score + flags
 *   Escape     → close help panel (if open), otherwise clear pending
 *   Enter      → commit (Record) if a score is pending
 *   ?          → toggle keyboard help panel
 *
 * Guards:
 *   • Skips when !isEditable (read-only scorecard)
 *   • Skips when submitting (POST in flight)
 *   • Skips when focus is inside INPUT / TEXTAREA / SELECT
 */
function handleKey(e) {
  if (!props.isEditable) return
  if (submitting.value) return

  const tag = e.target?.tagName
  if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return

  const key = e.key

  switch (key) {
    case '1': case '2': case '3': case '4': case '5':
    case '6': case '7': case '8': case '9':
      selectScore(Number(key))
      e.preventDefault()
      break

    // 0 on keyboard → score 10 (numeric 0 = miss, handled via M)
    case '0':
      selectScore(10)
      e.preventDefault()
      break

    case 'x': case 'X':
      toggleX()
      e.preventDefault()
      break

    case 'm': case 'M':
      toggleMiss()
      e.preventDefault()
      break

    case 'Backspace':
      clearPending()
      e.preventDefault()
      break

    case 'Escape':
      if (showKeyHelp.value) {
        // First Escape closes the help panel
        showKeyHelp.value = false
      } else {
        // Second Escape (or Escape with panel closed) clears pending
        clearPending()
      }
      e.preventDefault()
      break

    case 'Enter':
      if (newScore.value !== null) addShot()
      e.preventDefault()
      break

    case '?':
      showKeyHelp.value = !showKeyHelp.value
      e.preventDefault()
      break
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKey)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKey)
})

// ── Add shot ──────────────────────────────────────────────────────────────────
async function addShot() {
  if (newScore.value === null) return
  submitting.value = true

  const nextNumber = (props.end.shots?.length ?? 0) + 1

  try {
    const { data } = await axios.post(`/api/scorecards/${props.scorecardId}/shots`, {
      scorecard_end_id: props.end.id,
      shot_number:      nextNumber,
      score:            newScore.value,
      is_x:             newIsX.value,
      is_miss:          newIsMiss.value,
    })

    emit('shot-recorded', data)
    clearPending()
  } catch (err) {
    console.error('Shot record failed:', err)
  } finally {
    submitting.value = false
  }
}
</script>
