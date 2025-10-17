<script setup>
import { computed } from 'vue'

const props = defineProps({
  producto: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['edit', 'delete', 'view'])

const stockStatus = computed(() => {
  if (props.producto.stock_actual === 0) {
    return { label: 'Agotado', class: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }
  } else if (props.producto.stock_actual <= props.producto.stock_minimo) {
    return { label: 'Stock Bajo', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' }
  }
  return { label: 'En Stock', class: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' }
})
</script>

<template>
  <div
    class="card hover:shadow-lg transition-shadow duration-200 cursor-pointer group"
    @click="emit('view', producto)"
  >
    <!-- Imagen -->
    <div class="aspect-square rounded-lg bg-gray-100 dark:bg-gray-700 mb-4 overflow-hidden">
      <img
        v-if="producto.imagen"
        :src="producto.imagen"
        :alt="producto.nombre"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
      />
      <div
        v-else
        class="w-full h-full flex items-center justify-center"
      >
        <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
      </div>
    </div>

    <!-- Contenido -->
    <div class="space-y-3">
      <!-- Header -->
      <div>
        <div class="flex items-start justify-between gap-2">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2">
            {{ producto.nombre }}
          </h3>
          <span
            :class="[
              'px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap',
              producto.estado === 'activo'
                ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
            ]"
          >
            {{ producto.estado }}
          </span>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          {{ producto.codigo }}
        </p>
      </div>

      <!-- Categoría -->
      <div
        v-if="producto.categoria"
        class="inline-flex items-center gap-1 px-2 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 rounded-md text-xs font-medium"
      >
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        {{ producto.categoria.nombre }}
      </div>

      <!-- Precio -->
      <div class="flex items-baseline gap-2">
        <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
          ${{ Number(producto.precio_venta).toFixed(2) }}
        </span>
        <span
          v-if="producto.precio_compra"
          class="text-sm text-gray-500 dark:text-gray-400 line-through"
        >
          ${{ Number(producto.precio_compra).toFixed(2) }}
        </span>
      </div>

      <!-- Stock -->
      <div class="flex items-center justify-between">
        <div class="text-sm">
          <span class="text-gray-600 dark:text-gray-400">Stock:</span>
          <span
            :class="[
              'font-semibold ml-1',
              producto.stock_actual <= producto.stock_minimo
                ? 'text-red-600 dark:text-red-400'
                : 'text-gray-900 dark:text-white'
            ]"
          >
            {{ producto.stock_actual }}
          </span>
        </div>
        <span
          :class="[
            'px-2 py-1 text-xs font-medium rounded-full',
            stockStatus.class
          ]"
        >
          {{ stockStatus.label }}
        </span>
      </div>

      <!-- Descripción -->
      <p
        v-if="producto.descripcion"
        class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2"
      >
        {{ producto.descripcion }}
      </p>
    </div>

    <!-- Acciones -->
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex gap-2">
      <button
        @click.stop="emit('edit', producto)"
        class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
      >
        Editar
      </button>
      <button
        @click.stop="emit('delete', producto)"
        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </div>
  </div>
</template>
