<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Dashboard
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Bienvenido al panel de control
        </p>
      </div>

      <!-- Grid de tarjetas de estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 - Usuarios -->
        <router-link
          to="/users"
          class="card hover:shadow-lg transition-shadow duration-200 cursor-pointer"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Total Usuarios
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                1,234
              </p>
              <p class="mt-2 text-xs text-primary-600 dark:text-primary-400">
                Ver gestión de usuarios →
              </p>
            </div>
            <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
              <UsersIcon class="w-8 h-8 text-primary-600 dark:text-primary-400" />
            </div>
          </div>
        </router-link>

        <!-- Card - Configuraciones -->
        <router-link
          to="/settings"
          class="card hover:shadow-lg transition-shadow duration-200 cursor-pointer"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Configuraciones
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                Settings
              </p>
              <p class="mt-2 text-xs text-blue-600 dark:text-blue-400">
                Ver configuraciones →
              </p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
              <CogIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </router-link>

        <!-- Card 2 -->
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Ventas del Mes
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                $12,345
              </p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
              <CurrencyDollarIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Órdenes Pendientes
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                56
              </p>
            </div>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
              <ClockIcon class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
            </div>
          </div>
        </div>

        <!-- Card 4 - Productos -->
        <router-link
          to="/productos"
          class="card hover:shadow-lg transition-shadow duration-200 cursor-pointer"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Productos
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                789
              </p>
              <p class="mt-2 text-xs text-purple-600 dark:text-purple-400">
                Ver productos →
              </p>
            </div>
            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
              <CubeIcon class="w-8 h-8 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
        </router-link>
      </div>

      <!-- Contenido adicional -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Actividad reciente -->
        <div class="card">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            Actividad Reciente
          </h2>
          <div class="space-y-3">
            <div
              v-for="i in 5"
              :key="i"
              class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
            >
              <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
              <div class="flex-1">
                <p class="text-sm text-gray-900 dark:text-white">
                  Actividad de ejemplo #{{ i }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Hace {{ i }} horas
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tareas pendientes -->
        <div class="card">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            Tareas Pendientes
          </h2>
          <div class="space-y-3">
            <div
              v-for="i in 5"
              :key="i"
              class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
            >
              <input
                type="checkbox"
                class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500"
              />
              <div class="flex-1">
                <p class="text-sm text-gray-900 dark:text-white">
                  Tarea de ejemplo #{{ i }}
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
import AppLayout from '@/components/layout/AppLayout.vue'
import {
  UsersIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CubeIcon,
  CogIcon,
} from '@heroicons/vue/24/outline'
</script>
