<template>
  <div
    class="card hover:shadow-lg transition-all duration-200"
    :class="clickable ? 'cursor-pointer' : ''"
    @click="handleClick"
  >
    <div class="flex items-center justify-between">
      <!-- Contenido principal -->
      <div class="flex-1">
        <!-- Título -->
        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
          {{ title }}
        </p>

        <!-- Valor principal -->
        <p
          class="mt-2 text-3xl font-bold"
          :class="valueColorClass"
        >
          {{ formattedValue }}
        </p>

        <!-- Valor secundario (opcional) -->
        <p
          v-if="secondaryValue"
          class="mt-2 text-sm text-gray-500 dark:text-gray-400"
        >
          {{ secondaryValue }}
        </p>

        <!-- Tendencia (opcional) -->
        <div v-if="trend" class="mt-2 flex items-center space-x-1">
          <component
            :is="trendIcon"
            class="w-4 h-4"
            :class="trendColorClass"
          />
          <span class="text-xs font-medium" :class="trendColorClass">
            {{ trend }}
          </span>
        </div>

        <!-- Link (opcional) -->
        <p
          v-if="linkText"
          class="mt-2 text-xs"
          :class="iconColorClass"
        >
          {{ linkText }} →
        </p>
      </div>

      <!-- Icono -->
      <div
        class="p-3 rounded-lg"
        :class="iconBgClass"
      >
        <component
          :is="icon"
          class="w-8 h-8"
          :class="iconColorClass"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  /**
   * Título del card
   */
  title: {
    type: String,
    required: true,
  },

  /**
   * Valor principal a mostrar
   */
  value: {
    type: [String, Number],
    required: true,
  },

  /**
   * Tipo de formato para el valor
   * - number: Formatea como número con separadores de miles
   * - currency: Formatea como moneda
   * - percent: Formatea como porcentaje
   * - custom: Usa el valor tal cual
   */
  valueType: {
    type: String,
    default: 'number',
    validator: (value) => ['number', 'currency', 'percent', 'custom'].includes(value),
  },

  /**
   * Valor secundario (opcional)
   */
  secondaryValue: {
    type: String,
    default: null,
  },

  /**
   * Tendencia (opcional): up, down, o null
   */
  trendDirection: {
    type: String,
    default: null,
    validator: (value) => !value || ['up', 'down'].includes(value),
  },

  /**
   * Texto de la tendencia
   */
  trend: {
    type: String,
    default: null,
  },

  /**
   * Icono del card (componente de heroicons)
   */
  icon: {
    type: Object,
    required: true,
  },

  /**
   * Color theme del card
   */
  color: {
    type: String,
    default: 'primary',
    validator: (value) =>
      ['primary', 'green', 'red', 'blue', 'yellow', 'purple', 'gray'].includes(value),
  },

  /**
   * Texto del link (opcional)
   */
  linkText: {
    type: String,
    default: null,
  },

  /**
   * Si el card es clickeable
   */
  clickable: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['click'])

/**
 * Formatea el valor según el tipo
 */
const formattedValue = computed(() => {
  if (props.valueType === 'custom') {
    return props.value
  }

  const numValue = typeof props.value === 'string' ? parseFloat(props.value) : props.value

  if (isNaN(numValue)) {
    return props.value
  }

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
})

/**
 * Color del valor principal
 */
const valueColorClass = computed(() => {
  return 'text-gray-900 dark:text-white'
})

/**
 * Icono de tendencia
 */
const trendIcon = computed(() => {
  return props.trendDirection === 'up' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon
})

/**
 * Color de la tendencia
 */
const trendColorClass = computed(() => {
  return props.trendDirection === 'up'
    ? 'text-green-600 dark:text-green-400'
    : 'text-red-600 dark:text-red-400'
})

/**
 * Background del icono
 */
const iconBgClass = computed(() => {
  const colorMap = {
    primary: 'bg-primary-100 dark:bg-primary-900/30',
    green: 'bg-green-100 dark:bg-green-900/30',
    red: 'bg-red-100 dark:bg-red-900/30',
    blue: 'bg-blue-100 dark:bg-blue-900/30',
    yellow: 'bg-yellow-100 dark:bg-yellow-900/30',
    purple: 'bg-purple-100 dark:bg-purple-900/30',
    gray: 'bg-gray-100 dark:bg-gray-700/30',
  }
  return colorMap[props.color] || colorMap.primary
})

/**
 * Color del icono
 */
const iconColorClass = computed(() => {
  const colorMap = {
    primary: 'text-primary-600 dark:text-primary-400',
    green: 'text-green-600 dark:text-green-400',
    red: 'text-red-600 dark:text-red-400',
    blue: 'text-blue-600 dark:text-blue-400',
    yellow: 'text-yellow-600 dark:text-yellow-400',
    purple: 'text-purple-600 dark:text-purple-400',
    gray: 'text-gray-600 dark:text-gray-400',
  }
  return colorMap[props.color] || colorMap.primary
})

/**
 * Maneja el click en el card
 */
const handleClick = () => {
  if (props.clickable) {
    emit('click')
  }
}
</script>
