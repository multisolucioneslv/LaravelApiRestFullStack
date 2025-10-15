<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Sistemas
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra los sistemas del proyecto
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <SistemasDataTable
        v-else
        :sistemas="sistemas"
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
        @open-logo-modal="handleOpenLogoModal"
      />

      <!-- Modal de Logo -->
      <LogoEditModal
        v-model:open="logoModalOpen"
        :sistema="selectedSistema"
        @logo-updated="handleLogoUpdated"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useSistemas } from '@/composables/useSistemas'
import SistemasDataTable from '@/components/sistemas/SistemasDataTable.vue'
import LogoEditModal from '@/components/sistemas/LogoEditModal.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  sistemas,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchSistemas,
  deleteSistema,
  deleteSistemasBulk,
  searchSistemas,
  changePage,
  goToCreate,
  goToEdit,
} = useSistemas()

// Cargar sistemas al montar
onMounted(() => {
  fetchSistemas()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchSistemas(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteSistema(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteSistemasBulk(ids)
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

// Modal de Logo
const logoModalOpen = ref(false)
const selectedSistema = ref(null)

const handleOpenLogoModal = (sistema) => {
  selectedSistema.value = sistema
  logoModalOpen.value = true
}

const handleLogoUpdated = async (updatedSistema) => {
  // Actualizar el sistema en la lista
  const index = sistemas.value.findIndex(s => s.id === updatedSistema.id)

  if (index !== -1) {
    // Agregar timestamp para evitar caché del navegador
    if (updatedSistema.logo) {
      updatedSistema.logo = `${updatedSistema.logo}?t=${Date.now()}`
    }

    // Crear un nuevo array completo para forzar reactividad profunda
    const newSistemas = [...sistemas.value]
    newSistemas[index] = { ...updatedSistema }
    sistemas.value = newSistemas

    // También actualizar selectedSistema si es el mismo
    if (selectedSistema.value && selectedSistema.value.id === updatedSistema.id) {
      selectedSistema.value = { ...updatedSistema }
    }

    // Forzar actualización en el siguiente tick
    await nextTick()
  }
}
</script>
