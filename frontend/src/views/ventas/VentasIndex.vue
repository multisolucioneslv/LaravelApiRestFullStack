<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Ventas
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra las ventas del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <VentasDataTable
        v-else
        :ventas="ventas"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="total"
        :has-prev-page="hasPrevPage"
        :has-next-page="hasNextPage"
        @search="handleSearch"
        @show="handleShow"
        @edit="handleEdit"
        @delete="handleDelete"
        @bulk-delete="handleBulkDelete"
        @create="handleCreate"
        @previous-page="handlePreviousPage"
        @next-page="handleNextPage"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import { useVentas } from '@/composables/useVentas'
import { useAuthStore } from '@/stores/auth'
import VentasDataTable from '@/components/ventas/VentasDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()

const {
  ventas,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchVentas,
  deleteVenta,
  deleteVentasBulk,
  searchVentas,
  changePage,
  goToCreate,
  goToEdit,
  goToShow,
  initialize,
  cleanup,
} = useVentas()

// ==========================================
// SEGURIDAD: CARGA LAZY
// ==========================================

// Cargar ventas SOLO si hay sesión activa
onMounted(() => {
  if (authStore.isAuthenticated) {
    console.log('[SECURITY] VentasIndex: Inicializando con sesión activa')
    initialize()
  } else {
    console.warn('[SECURITY] VentasIndex: No se puede cargar sin sesión')
  }
})

// Limpiar datos cuando se sale de la vista
onUnmounted(() => {
  console.log('[SECURITY] VentasIndex: Limpiando datos al desmontar componente')
  cleanup()
})

// Vigilar cambios en autenticación
watch(() => authStore.isAuthenticated, (isAuth) => {
  if (!isAuth) {
    console.warn('[SECURITY] VentasIndex: Sesión cerrada, limpiando datos')
    cleanup()
  } else {
    console.log('[SECURITY] VentasIndex: Sesión iniciada, cargando datos')
    initialize()
  }
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchVentas(searchTerm)
}

const handleShow = (id) => {
  goToShow(id)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteVenta(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteVentasBulk(ids)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleCreate = () => {
  goToCreate()
}

const handlePreviousPage = () => {
  changePage(currentPage.value - 1)
}

const handleNextPage = () => {
  changePage(currentPage.value + 1)
}
</script>
