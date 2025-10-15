<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Roles y Permisos
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra los roles y sus permisos del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <RolesDataTable
        v-else
        :roles="roles"
        :current-page="currentPage"
        :last-page="lastPage"
        :total="total"
        :has-prev-page="hasPrevPage"
        :has-next-page="hasNextPage"
        @search="handleSearch"
        @edit="handleEdit"
        @delete="handleDelete"
        @create="handleCreate"
        @previous-page="handlePreviousPage"
        @next-page="handleNextPage"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoles } from '@/composables/useRoles'
import RolesDataTable from '@/components/roles/RolesDataTable.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const {
  roles,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchRoles,
  deleteRole,
  searchRoles,
  changePage,
  goToCreate,
  goToEdit,
} = useRoles()

// Cargar roles al montar
onMounted(() => {
  fetchRoles()
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchRoles(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteRole(id)
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
