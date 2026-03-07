import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import * as echarts from 'echarts/core'
import { LineChart, BarChart } from 'echarts/charts'
import {
  GridComponent,
  TooltipComponent,
  LegendComponent,
  DataZoomComponent,
  MarkPointComponent,
  MarkLineComponent,
} from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

echarts.use([
  LineChart,
  BarChart,
  GridComponent,
  TooltipComponent,
  LegendComponent,
  DataZoomComponent,
  MarkPointComponent,
  MarkLineComponent,
  CanvasRenderer,
])

/**
 * Manages an ECharts instance tied to a container ref.
 * Handles resize observation and cleanup automatically.
 *
 * @param {Ref<HTMLElement|null>} containerRef - Template ref to the chart container
 * @param {Object} initialOption - Initial ECharts option object
 */
export function useCharts(containerRef, initialOption = {}) {
  const chart = ref(null)
  let resizeObserver = null

  function init() {
    if (!containerRef.value) return
    chart.value = echarts.init(containerRef.value, null, { renderer: 'canvas' })
    if (Object.keys(initialOption).length) {
      chart.value.setOption(initialOption)
    }

    resizeObserver = new ResizeObserver(() => {
      chart.value?.resize()
    })
    resizeObserver.observe(containerRef.value)
  }

  /**
   * Set or merge options into the chart.
   * @param {Object} option
   * @param {boolean} notMerge - replace instead of merge
   */
  function setOption(option, notMerge = false) {
    chart.value?.setOption(option, notMerge)
  }

  /**
   * Show loading spinner on chart.
   */
  function showLoading() {
    chart.value?.showLoading({ text: '', color: '#111827', maskColor: 'rgba(255,255,255,0.8)' })
  }

  function hideLoading() {
    chart.value?.hideLoading()
  }

  onMounted(init)

  onBeforeUnmount(() => {
    resizeObserver?.disconnect()
    chart.value?.dispose()
    chart.value = null
  })

  return { chart, setOption, showLoading, hideLoading }
}
