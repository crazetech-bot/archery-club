<template>
  <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">

    <!-- Header -->
    <div class="mb-4 flex items-center justify-between">
      <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Analytics</p>
      <BaseButton
        v-if="isEditable"
        variant="secondary"
        size="sm"
        :loading="recalculating"
        loading-text="Updating…"
        @click="recalculate"
      >
        Recalculate
      </BaseButton>
    </div>

    <!-- No metrics yet -->
    <div v-if="!localMetrics" class="py-6 text-center text-sm text-gray-400">
      No metrics yet. Record some shots and recalculate.
    </div>

    <!-- Metrics grid -->
    <div v-else class="grid grid-cols-2 gap-3 sm:grid-cols-4">
      <UiMetric label="Avg. Arrow">{{ localMetrics.average_arrow_score ?? '—' }}</UiMetric>
      <UiMetric label="Hit Rate"   color="emerald">{{ localMetrics.hit_rate ?? '—' }}%</UiMetric>
      <UiMetric label="X Count"   color="amber">{{ localMetrics.x_count ?? 0 }}</UiMetric>
      <UiMetric label="Misses"    color="red">{{ localMetrics.miss_count ?? 0 }}</UiMetric>
    </div>

  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import BaseButton from '@/Components/Base/BaseButton.vue'
import UiMetric   from '@/Components/Base/UiMetric.vue'

// ── Props / Emits ─────────────────────────────────────────────────────────────
const props = defineProps({
  scorecardId: { type: Number, required: true },
  metrics:     { type: Object, default: null },
  isEditable:  { type: Boolean, default: false },
})

const emit = defineEmits(['recalculated'])

// ── State ─────────────────────────────────────────────────────────────────────
const localMetrics  = ref(props.metrics)
const recalculating = ref(false)

watch(() => props.metrics, (val) => {
  localMetrics.value = val
})

// ── Recalculate ───────────────────────────────────────────────────────────────
async function recalculate() {
  recalculating.value = true

  try {
    const { data } = await axios.post(`/api/scorecards/${props.scorecardId}/metrics/recalculate`)
    localMetrics.value = data
    emit('recalculated', data)
  } catch (err) {
    console.error('Recalculate failed:', err)
  } finally {
    recalculating.value = false
  }
}
</script>
