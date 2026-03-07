<template>
  <div class="relative">
    <!-- Loading overlay -->
    <Transition
      enter-active-class="transition duration-150"
      leave-active-class="transition duration-150"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="loading"
        class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/80"
      >
        <div class="h-6 w-6 animate-spin rounded-full border-2 border-gray-200 border-t-gray-900" />
      </div>
    </Transition>

    <!-- Chart canvas -->
    <div ref="chartEl" :style="{ height: height + 'px' }" class="w-full" />

    <!-- Empty state -->
    <div
      v-if="!loading && dataPoints.length === 0"
      class="absolute inset-0 flex flex-col items-center justify-center text-center"
    >
      <svg class="mb-3 h-10 w-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <p class="text-sm text-gray-400">No sessions in this date range</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import * as echarts from 'echarts/core'
import { LineChart }      from 'echarts/charts'
import { CanvasRenderer } from 'echarts/renderers'
import {
  GridComponent,
  TooltipComponent,
  LegendComponent,
  DataZoomComponent,
  MarkPointComponent,
} from 'echarts/components'

// Register only the ECharts modules we need (tree-shakeable)
echarts.use([
  LineChart,
  CanvasRenderer,
  GridComponent,
  TooltipComponent,
  LegendComponent,
  DataZoomComponent,
  MarkPointComponent,
])

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  /**
   * Array from ScoreProgressionService — each item has:
   *   date | period, total_score | avg_score, x_count, ten_count
   */
  dataPoints: { type: Array, default: () => [] },

  /**
   * Parallel array of moving-average values (one per data point).
   */
  trend: { type: Array, default: () => [] },

  /** 'week' | 'month' | null — changes x-axis label format */
  groupBy: { type: String, default: null },

  height: { type: Number, default: 340 },
  loading: { type: Boolean, default: false },
})

// ── State ─────────────────────────────────────────────────────────────────────
const chartEl   = ref(null)
let   chartInst = null
let   resizeObs = null

// ── Helpers ───────────────────────────────────────────────────────────────────

function xLabel(dp) {
  if (props.groupBy) return dp.label ?? dp.period
  return dp.date
}

function scoreValue(dp) {
  // Grouped data uses avg_score; per-session uses total_score
  return props.groupBy ? dp.avg_score : dp.total_score
}

function buildOption() {
  const labels      = props.dataPoints.map(xLabel)
  const scores      = props.dataPoints.map(scoreValue)
  const trendValues = props.trend

  // Find index of best and worst score for MarkPoints
  const maxIdx = scores.indexOf(Math.max(...scores))
  const minIdx = scores.indexOf(Math.min(...scores))

  return {
    backgroundColor: 'transparent',

    tooltip: {
      trigger: 'axis',
      backgroundColor: '#111827',
      borderColor: 'transparent',
      textStyle: { color: '#f9fafb', fontSize: 12 },
      formatter(params) {
        const dp = props.dataPoints[params[0]?.dataIndex]
        if (!dp) return ''

        const score = scoreValue(dp)
        const trend = trendValues[params[0]?.dataIndex] ?? '—'

        let html = `<div style="font-weight:600;margin-bottom:6px">${xLabel(dp)}</div>`
        html += `<div>Score: <b>${score}</b></div>`
        if (props.groupBy) html += `<div>Sessions: ${dp.sessions ?? 1}</div>`
        html += `<div>Trend (MA3): <b>${trend}</b></div>`
        html += `<div>X count: ${dp.x_count ?? 0}</div>`
        html += `<div>10 count: ${dp.ten_count ?? 0}</div>`
        return html
      },
    },

    legend: {
      top: 0,
      right: 0,
      itemWidth: 14,
      itemHeight: 3,
      textStyle: { color: '#9ca3af', fontSize: 11 },
      data: ['Score', 'Trend (MA3)'],
    },

    grid: { top: 36, right: 16, bottom: 56, left: 48, containLabel: false },

    xAxis: {
      type: 'category',
      data: labels,
      axisLine: { lineStyle: { color: '#f3f4f6' } },
      axisTick: { show: false },
      axisLabel: {
        color: '#9ca3af',
        fontSize: 11,
        rotate: labels.length > 10 ? 30 : 0,
        formatter: (val) => {
          // Truncate long labels on small screens
          return val.length > 10 ? val.slice(0, 10) + '…' : val
        },
      },
    },

    yAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: '#f3f4f6', type: 'dashed' } },
      axisLabel: { color: '#9ca3af', fontSize: 11 },
    },

    dataZoom: [
      {
        type: 'inside',
        start: labels.length > 12 ? Math.max(0, 100 - (12 / labels.length) * 100) : 0,
        end: 100,
      },
      {
        type: 'slider',
        height: 20,
        bottom: 4,
        borderColor: 'transparent',
        backgroundColor: '#f9fafb',
        fillerColor: '#e5e7eb',
        handleStyle: { color: '#6b7280' },
        textStyle: { color: '#9ca3af', fontSize: 10 },
        showDetail: false,
      },
    ],

    series: [
      {
        name: 'Score',
        type: 'line',
        data: scores,
        smooth: 0.3,
        symbol: 'circle',
        symbolSize: 6,
        lineStyle: { color: '#111827', width: 2.5 },
        itemStyle: { color: '#111827', borderWidth: 2, borderColor: '#fff' },
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: 'rgba(17,24,39,0.12)' },
            { offset: 1, color: 'rgba(17,24,39,0.01)' },
          ]),
        },
        markPoint: {
          symbolSize: 36,
          data: [
            ...(maxIdx >= 0
              ? [{ coord: [maxIdx, scores[maxIdx]], name: 'Best',
                  label: { formatter: '{c}', color: '#fff', fontSize: 10, fontWeight: 700 },
                  itemStyle: { color: '#eab308' } }]
              : []),
            ...(minIdx >= 0 && minIdx !== maxIdx
              ? [{ coord: [minIdx, scores[minIdx]], name: 'Worst',
                  label: { formatter: '{c}', color: '#fff', fontSize: 10, fontWeight: 700 },
                  itemStyle: { color: '#9ca3af' } }]
              : []),
          ],
        },
      },
      {
        name: 'Trend (MA3)',
        type: 'line',
        data: trendValues,
        smooth: 0.5,
        symbol: 'none',
        lineStyle: { color: '#f59e0b', width: 2, type: 'dashed' },
        itemStyle: { color: '#f59e0b' },
      },
    ],
  }
}

// ── Chart lifecycle ────────────────────────────────────────────────────────────

function initChart() {
  if (!chartEl.value) return
  chartInst = echarts.init(chartEl.value, null, { renderer: 'canvas' })
  chartInst.setOption(buildOption())

  resizeObs = new ResizeObserver(() => chartInst?.resize())
  resizeObs.observe(chartEl.value)
}

function updateChart() {
  if (!chartInst) return
  chartInst.setOption(buildOption(), { notMerge: true })
}

onMounted(async () => {
  await nextTick()
  initChart()
})

onUnmounted(() => {
  resizeObs?.disconnect()
  chartInst?.dispose()
})

// Re-render whenever data or grouping changes
watch(() => [props.dataPoints, props.trend, props.groupBy], updateChart, { deep: true })
</script>
