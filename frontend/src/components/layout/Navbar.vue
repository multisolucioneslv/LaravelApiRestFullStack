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

        <!-- Área de usuario con dropdown -->
        <div v-if="authStore.isAuthenticated">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <button class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <!-- Avatar del usuario -->
                <div v-if="userAvatar" class="w-10 h-10 rounded-full overflow-hidden shadow-md border-2 border-gray-200 dark:border-gray-600">
                  <img :src="userAvatar" :alt="authStore.userName" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold shadow-md">
                  {{ getUserInitials }}
                </div>

                <!-- Información del usuario -->
                <div class="text-left">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                    {{ authStore.userName }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 leading-tight">
                    {{ authStore.userRole || 'Usuario' }}
                  </p>
                </div>

                <!-- Icono de chevron -->
                <ChevronDownIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
              </button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" class="w-56">
              <!-- Información del usuario en el dropdown -->
              <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ authStore.userName }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ authStore.userEmail }}
                </p>
              </div>

              <!-- Opciones del menú -->
              <DropdownMenuItem @click="goToProfile" class="cursor-pointer">
                <UserIcon class="w-4 h-4 mr-2" />
                Mi Perfil
              </DropdownMenuItem>

              <DropdownMenuItem @click="goToInbox" class="cursor-pointer">
                <EnvelopeIcon class="w-4 h-4 mr-2" />
                Inbox
              </DropdownMenuItem>

              <DropdownMenuItem @click="goToTasks" class="cursor-pointer">
                <ClipboardDocumentListIcon class="w-4 h-4 mr-2" />
                Tareas
              </DropdownMenuItem>

              <DropdownMenuSeparator />

              <DropdownMenuItem @click="handleLogout" class="cursor-pointer text-red-600 dark:text-red-400 focus:text-red-700 dark:focus:text-red-300">
                <ArrowRightOnRectangleIcon class="w-4 h-4 mr-2" />
                Cerrar Sesión
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import {
  ArrowRightOnRectangleIcon,
  UserIcon,
  EnvelopeIcon,
  ClipboardDocumentListIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import DarkModeToggle from './DarkModeToggle.vue'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const authStore = useAuthStore()
const router = useRouter()

// URL del avatar del usuario
const userAvatar = computed(() => {
  // Priorizar el avatar temporal (preview) sobre el avatar guardado
  if (authStore.user?._tempAvatar) {
    return authStore.user._tempAvatar
  }

  if (authStore.user?.avatar) {
    // El avatar se guarda en storage/app/public/avatars/
    // En desarrollo: http://localhost:8000/storage/avatars/filename.jpg
    const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    // Usar updated_at del usuario como cache-busting para evitar cache del navegador
    const cacheBuster = authStore.user?.updated_at
      ? new Date(authStore.user.updated_at).getTime()
      : Date.now()
    return `${baseURL}/storage/${authStore.user.avatar}?v=${cacheBuster}`
  }
  return null
})

// Obtener iniciales del nombre del usuario para el avatar
const getUserInitials = computed(() => {
  const name = authStore.userName || 'U'
  const nameParts = name.split(' ')

  if (nameParts.length >= 2) {
    return `${nameParts[0][0]}${nameParts[1][0]}`.toUpperCase()
  }

  return name.substring(0, 2).toUpperCase()
})

// Navegación a diferentes secciones
const goToProfile = () => {
  router.push({ name: 'profile' })
}

const goToInbox = () => {
  // TODO: Implementar ruta de inbox cuando esté disponible
  console.log('Navegar a Inbox')
}

const goToTasks = () => {
  // TODO: Implementar ruta de tareas cuando esté disponible
  console.log('Navegar a Tareas')
}

const handleLogout = async () => {
  await authStore.logout()
}
</script>
