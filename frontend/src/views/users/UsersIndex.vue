<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión de Usuarios
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra los usuarios del sistema
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- DataTable -->
      <UsersDataTable
        v-else
        :users="users"
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
        @open-avatar-modal="handleOpenAvatarModal"
        @change-account-status="handleOpenAccountStatusModal"
      />

      <!-- Modal de Avatar -->
      <AvatarEditModal
        v-model:open="avatarModalOpen"
        :user="selectedUser"
        @avatar-updated="handleAvatarUpdated"
      />

      <!-- Modal de Cambio de Estado de Cuenta -->
      <AccountStatusModal
        :is-open="accountStatusModalOpen"
        :user="selectedUserForStatus"
        :loading="loading"
        @close="handleCloseAccountStatusModal"
        @submit="handleAccountStatusSubmit"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useUsers } from '@/composables/useUsers'
import { useAuthStore } from '@/stores/auth'
import UsersDataTable from '@/components/users/UsersDataTable.vue'
import AvatarEditModal from '@/components/users/AvatarEditModal.vue'
import AccountStatusModal from '@/components/users/AccountStatusModal.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()

const {
  users,
  loading,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchUsers,
  deleteUser,
  deleteUsersBulk,
  updateAccountStatus,
  searchUsers,
  changePage,
  goToCreate,
  goToEdit,
} = useUsers()

// ✅ Carga lazy segura: Solo cargar cuando hay sesión activa
onMounted(() => {
  // Verificar token JWT antes de cargar datos
  const token = localStorage.getItem('auth_token')

  if (authStore.isAuthenticated && token) {
    fetchUsers()
  } else {
    console.warn('🔒 [SECURITY] No se cargaron usuarios: sesión no activa')
  }
})

// ✅ Limpiar recursos al salir de la vista
onUnmounted(() => {
  console.log('🧹 [CLEANUP] Vista de usuarios desmontada')
  // No hay polling que detener en este composable
})

// ✅ Reaccionar a cambios en autenticación
watch(() => authStore.isAuthenticated, (isAuth) => {
  if (!isAuth) {
    console.warn('🔒 [SECURITY] Sesión cerrada - limpiando datos de usuarios')
    // Limpiar datos si se cierra sesión
    users.value = []
  } else if (users.value.length === 0) {
    // Recargar datos si se vuelve a autenticar y no hay datos
    fetchUsers()
  }
})

// Manejadores
const handleSearch = (searchTerm) => {
  searchUsers(searchTerm)
}

const handleEdit = (id) => {
  goToEdit(id)
}

const handleDelete = async (id) => {
  const deleted = await deleteUser(id)
  if (deleted) {
    // La lista se recarga automáticamente en el composable
  }
}

const handleBulkDelete = async (ids) => {
  const deleted = await deleteUsersBulk(ids)
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

// Modal de Avatar
const avatarModalOpen = ref(false)
const selectedUser = ref(null)

const handleOpenAvatarModal = (user) => {
  selectedUser.value = user
  avatarModalOpen.value = true
}

const handleAvatarUpdated = async (updatedUser) => {
  // Actualizar el usuario en la lista
  const index = users.value.findIndex(u => u.id === updatedUser.id)

  if (index !== -1) {
    // Agregar timestamp para evitar caché del navegador
    if (updatedUser.avatar) {
      updatedUser.avatar = `${updatedUser.avatar}?t=${Date.now()}`
    }

    // Crear un nuevo array completo para forzar reactividad profunda
    const newUsers = [...users.value]
    newUsers[index] = { ...updatedUser }
    users.value = newUsers

    // También actualizar selectedUser si es el mismo
    if (selectedUser.value && selectedUser.value.id === updatedUser.id) {
      selectedUser.value = { ...updatedUser }
    }

    // Forzar actualización en el siguiente tick
    await nextTick()
  }
}

// Modal de Cambio de Estado de Cuenta
const accountStatusModalOpen = ref(false)
const selectedUserForStatus = ref(null)

const handleOpenAccountStatusModal = (user) => {
  selectedUserForStatus.value = user
  accountStatusModalOpen.value = true
}

const handleCloseAccountStatusModal = () => {
  accountStatusModalOpen.value = false
  selectedUserForStatus.value = null
}

const handleAccountStatusSubmit = async (data) => {
  try {
    await updateAccountStatus(data.userId, {
      cuenta: data.cuenta,
      razon_suspendida: data.razon_suspendida,
    })

    // Cerrar modal
    handleCloseAccountStatusModal()
  } catch (error) {
    // El error ya se maneja en el composable
  }
}
</script>
