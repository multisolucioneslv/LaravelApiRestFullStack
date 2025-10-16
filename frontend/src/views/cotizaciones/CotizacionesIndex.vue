<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Cotizaciones
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra las cotizaciones de ventas
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <CotizacionesDataTable
        v-else
        :cotizaciones="cotizaciones"
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
import { useRouter } from 'vue-router'
import { useCotizaciones } from '@/composables/useCotizaciones'
import { useAuthStore } from '@/stores/auth'
import CotizacionesDataTable from '@/components/cotizaciones/CotizacionesDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const router = useRouter()
const authStore = useAuthStore()

const {
  cotizaciones,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchCotizaciones,
  deleteCotizacion,
  deleteCotizacionesBulk,
  searchCotizaciones,
  changePage,
  goToCreate,
  goToEdit,
  goToShow,
} = useCotizaciones()

// Verificación de sesión y carga de datos
let sessionCheckInterval = null

onMounted(() => {
  // Verificar sesión antes de cargar datos
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }

  fetchCotizaciones()

  // Configurar verificación periódica de sesión (cada 30 segundos)
  sessionCheckInterval = setInterval(() => {
    if (!authStore.isAuthenticated) {
      router.push('/login')
    }
  }, 30000)
})

// Limpiar intervalo al desmontar
onUnmounted(() => {
  if (sessionCheckInterval) {
    clearInterval(sessionCheckInterval)
  }
})

// Watch para cambios en autenticación
watch(
  () => authStore.isAuthenticated,
  (newValue) => {
    if (!newValue) {
      router.push('/login')
    }
  }
)

// Manejadores
const handleSearch = (searchTerm) => {
  searchCotizaciones(searchTerm)
}

const handleShow = (id) => {
  goToShow(id)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteCotizacion(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteCotizacionesBulk(ids)
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
