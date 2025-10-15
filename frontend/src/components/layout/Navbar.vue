<template>
  <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
    <div class="flex items-center justify-between">
      <!-- Logo y título -->
      <div class="flex items-center space-x-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
          BackendProfesional
        </h1>
      </div>

      <!-- Acciones del usuario -->
      <div class="flex items-center space-x-4">
        <!-- Toggle de Dark Mode -->
        <DarkModeToggle />

        <!-- Información del usuario -->
        <div v-if="authStore.isAuthenticated" class="flex items-center space-x-3">
          <div class="text-right">
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              {{ authStore.userName }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ authStore.userEmail }}
            </p>
          </div>

          <!-- Botón de logout -->
          <button
            @click="handleLogout"
            :disabled="authStore.loading"
            class="p-2 rounded-lg bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 transition-colors duration-200"
            title="Cerrar sesión"
          >
            <ArrowRightOnRectangleIcon class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import DarkModeToggle from './DarkModeToggle.vue'

const authStore = useAuthStore()

const handleLogout = async () => {
  // La confirmación ahora se maneja dentro del store con SweetAlert2
  await authStore.logout()
}
</script>
