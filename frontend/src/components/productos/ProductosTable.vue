<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-900">
        <tr>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
          >
            Imagen
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
          >
            Código / Nombre
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
          >
            Categoría
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
          >
            Stock
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
          >
            Precio
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
          >
            Estado
          </th>
          <th
            scope="col"
            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
          >
            Acciones
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <tr
          v-for="producto in productos"
          :key="producto.id"
          class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
        >
          <!-- Imagen -->
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex-shrink-0 h-16 w-16">
              <img
                v-if="producto.imagen"
                :src="getImageUrl(producto.imagen)"
                :alt="producto.nombre"
                class="h-16 w-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700"
                @error="handleImageError"
              />
              <div
                v-else
                class="h-16 w-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-8 h-8 text-gray-400"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
                  />
                </svg>
              </div>
            </div>
          </td>

          <!-- Código / Nombre -->
          <td class="px-6 py-4">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ producto.nombre }}
              </span>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                Código: {{ producto.codigo }}
              </span>
            </div>
          </td>

          <!-- Categoría -->
          <td class="px-6 py-4 whitespace-nowrap">
            <span
              v-if="producto.categoria"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
              :style="{
                backgroundColor: producto.categoria.color + '20',
                color: producto.categoria.color
              }"
            >
              <span v-if="producto.categoria.icono">{{ producto.categoria.icono }}</span>
              {{ producto.categoria.nombre }}
            </span>
            <span v-else class="text-sm text-gray-500 dark:text-gray-400">Sin categoría</span>
          </td>

          <!-- Stock -->
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center gap-2">
              <StockBadge
                :stock="producto.stock"
                :stock-minimo="producto.stock_minimo"
              />
              <span class="text-sm text-gray-900 dark:text-white">
                {{ producto.stock }} / {{ producto.stock_minimo }}
              </span>
            </div>
            <button
              @click="$emit('update-stock', { id: producto.id, cantidad: 0, tipo: 'aumentar' })"
              class="mt-1 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:underline"
            >
              Ajustar stock
            </button>
          </td>

          <!-- Precio -->
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-gray-900 dark:text-white">
                ${{ formatPrice(producto.precio) }}
              </span>
              <span
                v-if="producto.descuento > 0"
                class="text-xs text-green-600 dark:text-green-400"
              >
                -{{ producto.descuento }}%
              </span>
            </div>
          </td>

          <!-- Estado -->
          <td class="px-6 py-4 whitespace-nowrap">
            <span
              class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
              :class="
                producto.activo
                  ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                  : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
              "
            >
              {{ producto.activo ? 'Activo' : 'Inactivo' }}
            </span>
            <span
              v-if="producto.deleted_at"
              class="ml-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"
            >
              Eliminado
            </span>
          </td>

          <!-- Acciones -->
          <td class="px-6 py-4 whitespace-nowrap text-right">
            <div class="flex items-center justify-end gap-2">
              <button
                @click="$emit('edit', producto.id)"
                class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                title="Editar"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-5 h-5"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"
                  />
                </svg>
              </button>

              <button
                v-if="!producto.deleted_at"
                @click="$emit('delete', producto.id)"
                class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                title="Eliminar"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-5 h-5"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
                  />
                </svg>
              </button>

              <button
                v-else
                @click="$emit('restore', producto.id)"
                class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                title="Restaurar"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-5 h-5"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"
                  />
                </svg>
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import StockBadge from './StockBadge.vue'

defineProps({
  productos: {
    type: Array,
    required: true,
  },
})

defineEmits(['edit', 'delete', 'restore', 'update-stock'])

const getImageUrl = (path) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
  const backendUrl = baseUrl.replace('/api', '')

  if (path.startsWith('http')) {
    return path
  }

  return `${backendUrl}${path.startsWith('/') ? '' : '/'}${path}`
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
  event.target.parentElement.innerHTML = `
    <div class="h-16 w-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-400">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
      </svg>
    </div>
  `
}

const formatPrice = (price) => {
  return Number(price).toFixed(2)
}
</script>
