<template>
  <div class="space-y-3">
    <div
      v-for="(item, index) in chartData"
      :key="index"
      class="space-y-1"
    >
      <!-- Label y valor -->
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600 dark:text-gray-400">{{ item.label }}</span>
        <span class="font-semibold text-gray-900 dark:text-white">
          {{ formatValue(item.value) }}
        </span>
      </div>

      <!-- Barra de progreso -->
      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
        <div
          class="h-2.5 rounded-full transition-all duration-500 ease-out"
          :class="getBarColor(index)"
          :style="{ width: `${getPercentage(item.value)}%` }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  /**
   * Datos del gráfico
   * Formato: [{ label: 'Enero', value: 1000 }, ...]
   */
  data: {
    type: Array,
    required: true,
    default: () => [],
  },

  /**
   * Tipo de formato para los valores
   */
  valueType: {
    type: String,
    default: 'number',
    validator: (value) => ['number', 'currency', 'percent'].includes(value),
  },

  /**
   * Color de las barras
   */
  color: {
    type: String,
    default: 'primary',
    validator: (value) =>
      ['primary', 'green', 'red', 'blue', 'yellow', 'purple'].includes(value),
  },

  /**
   * Usar colores diferentes para cada barra
   */
  multiColor: {
    type: Boolean,
    default: false,
  },
})

/**
 * Datos ordenados del gráfico
 */
const chartData = computed(() => {
  return props.data || []
})

/**
 * Valor máximo para calcular porcentajes
 */
const maxValue = computed(() => {
  if (!chartData.value.length) return 0
  return Math.max(...chartData.value.map((item) => item.value || 0))
})

/**
 * Calcula el porcentaje de la barra
 */
const getPercentage = (value) => {
  if (!maxValue.value) return 0
  return (value / maxValue.value) * 100
}

/**
 * Formatea el valor según el tipo
 */
const formatValue = (value) => {
  const numValue = typeof value === 'string' ? parseFloat(value) : value

  if (isNaN(numValue)) return value

  switch (props.valueType) {
    case 'currency':
      return `$${numValue.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })}`
    case 'percent':
      return `${numValue.toLocaleString('en-US')}%`
    case 'number':
    default:
      return numValue.toLocaleString('en-US')
  }
}

/**
 * Obtiene el color de la barra
 */
const getBarColor = (index) => {
  if (!props.multiColor) {
    const colorMap = {
      primary: 'bg-primary-600 dark:bg-primary-500',
      green: 'bg-green-600 dark:bg-green-500',
      red: 'bg-red-600 dark:bg-red-500',
      blue: 'bg-blue-600 dark:bg-blue-500',
      yellow: 'bg-yellow-600 dark:bg-yellow-500',
      purple: 'bg-purple-600 dark:bg-purple-500',
    }
    return colorMap[props.color] || colorMap.primary
  }

  // Multi-color: alterna entre colores
  const colors = [
    'bg-blue-600 dark:bg-blue-500',
    'bg-green-600 dark:bg-green-500',
    'bg-purple-600 dark:bg-purple-500',
    'bg-yellow-600 dark:bg-yellow-500',
    'bg-red-600 dark:bg-red-500',
    'bg-indigo-600 dark:bg-indigo-500',
  ]
  return colors[index % colors.length]
}
</script>
