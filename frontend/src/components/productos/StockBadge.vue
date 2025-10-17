<template>
  <span
    class="inline-flex items-center justify-center w-3 h-3 rounded-full"
    :class="badgeClass"
    :title="badgeTitle"
  >
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  stock: {
    type: Number,
    required: true,
  },
  stockMinimo: {
    type: Number,
    required: true,
  },
})

const stockStatus = computed(() => {
  if (props.stock === 0) {
    return 'sin-stock'
  } else if (props.stock <= props.stockMinimo) {
    return 'bajo-stock'
  } else if (props.stock <= props.stockMinimo * 2) {
    return 'stock-medio'
  } else {
    return 'stock-alto'
  }
})

const badgeClass = computed(() => {
  const classes = {
    'sin-stock': 'bg-red-600 dark:bg-red-500',
    'bajo-stock': 'bg-yellow-500 dark:bg-yellow-400',
    'stock-medio': 'bg-blue-500 dark:bg-blue-400',
    'stock-alto': 'bg-green-600 dark:bg-green-500',
  }

  return classes[stockStatus.value] || 'bg-gray-400'
})

const badgeTitle = computed(() => {
  const titles = {
    'sin-stock': 'Sin stock',
    'bajo-stock': 'Stock bajo',
    'stock-medio': 'Stock medio',
    'stock-alto': 'Stock adecuado',
  }

  return titles[stockStatus.value] || 'Desconocido'
})
</script>
