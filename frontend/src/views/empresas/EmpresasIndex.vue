<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Empresas
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra las empresas del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <EmpresasDataTable
        v-else
        :empresas="empresas"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="total"
        :has-prev-page="hasPrevPage"
        :has-next-page="hasNextPage"
        @search="handleSearch"
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
import { useEmpresas } from '@/composables/useEmpresas'
import { useAuthStore } from '@/stores/auth'
import EmpresasDataTable from '@/components/empresas/EmpresasDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()

const {
  empresas,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchEmpresas,
  deleteEmpresa,
  deleteEmpresasBulk,
  searchEmpresas,
  changePage,
  goToCreate,
  goToEdit,
  initialize,
  cleanup,
} = useEmpresas()

// ==========================================
// SEGURIDAD: CARGA LAZY
// ==========================================

// Cargar empresas SOLO si hay sesión activa
onMounted(() => {
  if (authStore.isAuthenticated) {
    console.log('[SECURITY] EmpresasIndex: Inicializando con sesión activa')
    initialize()
  } else {
    console.warn('[SECURITY] EmpresasIndex: No se puede cargar sin sesión')
  }
})

// Limpiar datos cuando se sale de la vista
onUnmounted(() => {
  console.log('[SECURITY] EmpresasIndex: Limpiando datos al desmontar componente')
  cleanup()
})

// Vigilar cambios en autenticación
watch(() => authStore.isAuthenticated, (isAuth) => {
  if (!isAuth) {
    console.warn('[SECURITY] EmpresasIndex: Sesión cerrada, limpiando datos')
    cleanup()
  } else {
    console.log('[SECURITY] EmpresasIndex: Sesión iniciada, cargando datos')
    initialize()
  }
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchEmpresas(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteEmpresa(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteEmpresasBulk(ids)
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
