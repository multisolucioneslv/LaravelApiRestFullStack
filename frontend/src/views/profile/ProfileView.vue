<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mi Perfil</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestiona tu información personal y preferencias</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Profile Content -->
      <div v-else-if="profile" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tarjeta de Avatar -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex flex-col items-center">
              <!-- Avatar -->
              <div class="relative mb-4">
                <div v-if="avatarURL" class="w-32 h-32 rounded-full overflow-hidden shadow-lg border-4 border-gray-200 dark:border-gray-600">
                  <img :src="avatarURL" :alt="profile.name" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                  {{ getInitials(profile.name) }}
                </div>
              </div>

              <!-- Nombre y Email -->
              <h2 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-1">
                {{ profile.name }}
              </h2>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                @{{ profile.usuario }}
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">
                {{ profile.email }}
              </p>

              <!-- Roles/Badges -->
              <div v-if="profile.roles && profile.roles.length > 0" class="flex flex-wrap gap-2 justify-center mb-4">
                <span
                  v-for="role in profile.roles"
                  :key="role.id"
                  class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300"
                >
                  {{ role.name }}
                </span>
              </div>

              <!-- Botón Editar -->
              <button
                @click="$router.push({ name: 'profile.edit' })"
                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium"
              >
                Editar Perfil
              </button>
            </div>
          </div>
        </div>

        <!-- Información Detallada -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información Personal -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Información Personal</h3>
            </div>
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Nombre Completo
                  </label>
                  <p class="text-gray-900 dark:text-white">{{ profile.name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Usuario
                  </label>
                  <p class="text-gray-900 dark:text-white">@{{ profile.usuario }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Email
                  </label>
                  <p class="text-gray-900 dark:text-white">{{ profile.email }}</p>
                </div>

                <div v-if="profile.telefono">
                  <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Teléfono
                  </label>
                  <p class="text-gray-900 dark:text-white">{{ profile.telefono.telefono }}</p>
                </div>

                <div v-if="profile.sex">
                  <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Sexo
                  </label>
                  <p class="text-gray-900 dark:text-white">{{ profile.sex.sexo }}</p>
                </div>

                <div v-if="profile.chatid">
                  <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Chat ID (Telegram)
                  </label>
                  <p class="text-gray-900 dark:text-white">{{ profile.chatid.idtelegram }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Información de la Empresa -->
          <div v-if="profile.empresa" class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Empresa</h3>
            </div>
            <div class="p-6">
              <div class="flex items-center space-x-4">
                <div v-if="profile.empresa.logo" class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                  <img :src="getEmpresaLogo(profile.empresa.logo)" :alt="profile.empresa.razon_social" class="w-full h-full object-cover" />
                </div>
                <div>
                  <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ profile.empresa.razon_social }}
                  </h4>
                  <p v-if="profile.empresa.nit" class="text-sm text-gray-600 dark:text-gray-400">
                    NIT: {{ profile.empresa.nit }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Estado de Cuenta -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Estado de Cuenta</h3>
            </div>
            <div class="p-6">
              <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Estado:</span>
                <span v-if="profile.activo" class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                  Activo
                </span>
                <span v-else class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                  Inactivo
                </span>
              </div>

              <div v-if="profile.created_at" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Miembro desde: <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(profile.created_at) }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useProfile } from '@/composables/useProfile'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const { profile, loading, fetchProfile } = useProfile()

const apiURL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

const avatarURL = computed(() => {
  if (profile.value?.avatar) {
    // Usar updated_at como cache-busting para evitar cache del navegador
    const cacheBuster = profile.value?.updated_at
      ? new Date(profile.value.updated_at).getTime()
      : Date.now()
    return `${apiURL}/storage/${profile.value.avatar}?v=${cacheBuster}`
  }
  return null
})

const getInitials = (name) => {
  if (!name) return 'U'
  const nameParts = name.split(' ')
  if (nameParts.length >= 2) {
    return `${nameParts[0][0]}${nameParts[1][0]}`.toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const getEmpresaLogo = (logo) => {
  return `${apiURL}/storage/${logo}`
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })
}

onMounted(async () => {
  await fetchProfile()
})
</script>
