<template>
  <div class="flex flex-col lg:flex-row items-center gap-6">
    <!-- Donut Chart (SVG) -->
    <div class="relative flex-shrink-0">
      <svg
        :width="size"
        :height="size"
        viewBox="0 0 100 100"
        class="transform -rotate-90"
      >
        <!-- Background circle -->
        <circle
          cx="50"
          cy="50"
          :r="radius"
          fill="none"
          stroke="currentColor"
          :stroke-width="strokeWidth"
          class="text-gray-200 dark:text-gray-700"
        />

        <!-- Segmentos -->
        <circle
          v-for="(segment, index) in segments"
          :key="index"
          cx="50"
          cy="50"
          :r="radius"
          fill="none"
          :stroke="segment.color"
          :stroke-width="strokeWidth"
          :stroke-dasharray="`${segment.percentage * circumference / 100} ${circumference}`"
          :stroke-dashoffset="segment.offset"
          class="transition-all duration-500"
        />
      </svg>

      <!-- Centro: Total -->
      <div class="absolute inset-0 flex flex-col items-center justify-center">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ total }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Total
        </p>
      </div>
    </div>

    <!-- Leyenda -->
    <div class="flex-1 space-y-3">
      <div
        v-for="(item, index) in chartData"
        :key="index"
        class="flex items-center justify-between"
      >
        <div class="flex items-center space-x-2">
          <div
            class="w-3 h-3 rounded-full"
            :style="{ backgroundColor: getColor(index) }"
          ></div>
          <span class="text-sm text-gray-600 dark:text-gray-400">
            {{ item.label }}
          </span>
        </div>
        <div class="text-right">
          <p class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ item.value }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ getPercentage(item.value) }}%
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  /**
   * Datos del gráfico
   * Formato: [{ label: 'Completadas', value: 45 }, ...]
   */
  data: {
    type: Array,
    required: true,
    default: () => [],
  },

  /**
   * Tamaño del donut (ancho y alto)
   */
  size: {
    type: Number,
    default: 200,
  },

  /**
   * Grosor del anillo
   */
  strokeWidth: {
    type: Number,
    default: 12,
  },
})

/**
 * Datos del gráfico
 */
const chartData = computed(() => {
  return props.data || []
})

/**
 * Radio del círculo
 */
const radius = computed(() => {
  return (100 - props.strokeWidth) / 2
})

/**
 * Circunferencia del círculo
 */
const circumference = computed(() => {
  return 2 * Math.PI * radius.value
})

/**
 * Total de valores
 */
const total = computed(() => {
  return chartData.value.reduce((sum, item) => sum + (item.value || 0), 0)
})

/**
 * Calcula el porcentaje de un valor
 */
const getPercentage = (value) => {
  if (!total.value) return 0
  return ((value / total.value) * 100).toFixed(1)
}

/**
 * Colores para los segmentos
 */
const colors = [
  '#3b82f6', // blue-500
  '#10b981', // green-500
  '#f59e0b', // yellow-500
  '#ef4444', // red-500
  '#8b5cf6', // purple-500
  '#ec4899', // pink-500
  '#06b6d4', // cyan-500
  '#f97316', // orange-500
]

/**
 * Obtiene el color para un índice
 */
const getColor = (index) => {
  return colors[index % colors.length]
}

/**
 * Calcula los segmentos del donut con offsets
 */
const segments = computed(() => {
  let cumulativePercentage = 0
  return chartData.value.map((item, index) => {
    const percentage = getPercentage(item.value)
    const offset = circumference.value - (cumulativePercentage * circumference.value / 100)
    cumulativePercentage += parseFloat(percentage)

    return {
      percentage: parseFloat(percentage),
      offset,
      color: getColor(index),
    }
  })
})
</script>
