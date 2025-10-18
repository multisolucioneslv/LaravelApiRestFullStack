<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gestión Global de AI
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Administra el sistema de AI Chat para todas las empresas
        </p>
      </div>

      <!-- Loading General -->
      <div v-if="loading && !globalStats" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else>
        <!-- Sección: Estadísticas Globales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Empresas</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                  {{ globalStats?.total_empresas || 0 }}
                </p>
              </div>
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">AI Habilitado</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                  {{ globalStats?.empresas_ai_enabled || 0 }}
                </p>
              </div>
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">API Key Propia</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                  {{ globalStats?.empresas_api_key_propia || 0 }}
                </p>
              </div>
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Queries del Mes</p>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">
                  {{ globalStats?.total_queries_mes || 0 }}
                </p>
              </div>
              <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Uso y Presupuesto -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Uso Mensual Total</h3>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
              ${{ (globalStats?.uso_total_mensual || 0).toFixed(2) }}
            </p>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Presupuesto Total</h3>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400">
              ${{ (globalStats?.presupuesto_total_mensual || 0).toFixed(2) }}
            </p>
          </div>
        </div>

        <!-- Sección: Planes Disponibles -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Planes Disponibles</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Plan Básico -->
            <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:border-blue-500 transition-colors">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Básico</h3>
                <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-sm font-medium">
                  Regex
                </span>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                $0.002 - $0.005
                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">/query</span>
              </p>
              <ul class="space-y-2 mb-4">
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Rápido y económico</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Patrones predefinidos</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Limitado en precisión</span>
                </li>
              </ul>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Recomendado para:</strong> Empresas con consultas estándar y presupuesto limitado
              </p>
            </div>

            <!-- Plan Intermedio -->
            <div class="border-2 border-blue-500 rounded-lg p-6 relative">
              <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                <span class="px-4 py-1 bg-blue-500 text-white rounded-full text-xs font-bold">RECOMENDADO</span>
              </div>
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Intermedio</h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-sm font-medium">
                  Function Calling
                </span>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                $0.005 - $0.010
                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">/query</span>
              </p>
              <ul class="space-y-2 mb-4">
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Balance costo/inteligencia</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Detección inteligente</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Mayor precisión</span>
                </li>
              </ul>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Recomendado para:</strong> Balance entre costo e inteligencia
              </p>
            </div>

            <!-- Plan Premium -->
            <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:border-purple-500 transition-colors">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Premium</h3>
                <span class="px-3 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded-full text-sm font-medium">
                  Double Call
                </span>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                $0.010 - $0.020
                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">/query</span>
              </p>
              <ul class="space-y-2 mb-4">
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Máxima precisión</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Doble verificación</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-sm text-gray-700 dark:text-gray-300">Mayor costo</span>
                </li>
              </ul>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Recomendado para:</strong> Máxima precisión sin límite de presupuesto
              </p>
            </div>
          </div>
        </div>

        <!-- Sección: Gestión de Empresas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gestión de Empresas</h2>
          </div>

          <!-- Filtros -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Búsqueda -->
              <div class="md:col-span-2">
                <input
                  v-model="searchInput"
                  @keyup.enter="handleSearch"
                  type="text"
                  placeholder="Buscar por nombre o email..."
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                />
              </div>

              <!-- Filtro AI Habilitado -->
              <div>
                <select
                  v-model="filterAI"
                  @change="applyFilters"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option :value="null">Todos</option>
                  <option :value="true">AI Habilitado</option>
                  <option :value="false">AI Deshabilitado</option>
                </select>
              </div>

              <!-- Filtro Modo Detección -->
              <div>
                <select
                  v-model="filterMode"
                  @change="applyFilters"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option :value="null">Todos los modos</option>
                  <option value="regex">Regex</option>
                  <option value="function_calling">Function Calling</option>
                  <option value="double_call">Double Call</option>
                </select>
              </div>
            </div>

            <!-- Botón limpiar filtros -->
            <div class="mt-3 flex justify-end">
              <button
                @click="handleClearFilters"
                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
              >
                Limpiar filtros
              </button>
            </div>
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-100 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Empresa
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    AI Habilitado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Modo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Uso / Presupuesto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Queries
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="empresa in empresas" :key="empresa.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4">
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ empresa.nombre }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ empresa.email }}</p>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <button
                      @click="handleToggleAI(empresa.id, !empresa.ai_chat_enabled)"
                      :class="[
                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                        empresa.ai_chat_enabled ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'
                      ]"
                      :disabled="updating"
                    >
                      <span
                        :class="[
                          'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                          empresa.ai_chat_enabled ? 'translate-x-6' : 'translate-x-1'
                        ]"
                      />
                    </button>
                  </td>
                  <td class="px-6 py-4">
                    <span
                      v-if="empresa.ai_detection_mode"
                      :class="[
                        'px-3 py-1 rounded-full text-xs font-medium',
                        empresa.ai_detection_mode === 'regex' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '',
                        empresa.ai_detection_mode === 'function_calling' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '',
                        empresa.ai_detection_mode === 'double_call' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : ''
                      ]"
                    >
                      {{ getModeLabel(empresa.ai_detection_mode) }}
                    </span>
                    <span v-else class="text-gray-400 dark:text-gray-500 text-sm">N/A</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="w-48">
                      <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-900 dark:text-white">${{ (empresa.monthly_usage || 0).toFixed(2) }}</span>
                        <span class="text-gray-500 dark:text-gray-400">/ ${{ (empresa.ai_monthly_budget || 0).toFixed(2) }}</span>
                      </div>
                      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div
                          :class="[
                            'h-2 rounded-full transition-all',
                            getUsagePercentage(empresa) >= 90 ? 'bg-red-600' : '',
                            getUsagePercentage(empresa) >= 70 && getUsagePercentage(empresa) < 90 ? 'bg-yellow-600' : '',
                            getUsagePercentage(empresa) < 70 ? 'bg-green-600' : ''
                          ]"
                          :style="{ width: `${Math.min(getUsagePercentage(empresa), 100)}%` }"
                        ></div>
                      </div>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ getUsagePercentage(empresa) }}% usado
                      </p>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-gray-900 dark:text-white font-medium">{{ empresa.total_queries || 0 }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                      <button
                        @click="handleViewDetail(empresa.id)"
                        class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                      >
                        Ver
                      </button>
                      <button
                        @click="handleResetUsage(empresa.id)"
                        :disabled="updating"
                        class="px-3 py-1 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors disabled:opacity-50"
                      >
                        Reset
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="empresas.length === 0">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    No se encontraron empresas
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div v-if="lastPage > 1" class="p-6 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-600 dark:text-gray-400">
                Mostrando página {{ currentPage }} de {{ lastPage }} ({{ total }} empresas)
              </div>
              <div class="flex space-x-2">
                <button
                  @click="changePage(currentPage - 1)"
                  :disabled="!hasPrevPage"
                  class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Anterior
                </button>
                <button
                  @click="changePage(currentPage + 1)"
                  :disabled="!hasNextPage"
                  class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Siguiente
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal: Detalles de Empresa -->
      <div
        v-if="showDetailModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="showDetailModal = false"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-black opacity-50"></div>

          <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-3xl w-full p-6 shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                Detalles de {{ empresaDetail?.nombre }}
              </h3>
              <button
                @click="showDetailModal = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex justify-center py-12">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>

            <!-- Contenido -->
            <div v-else-if="empresaDetail" class="space-y-6">
              <!-- Configuración AI -->
              <div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Configuración AI</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Modo de Detección
                    </label>
                    <select
                      v-model="editForm.ai_detection_mode"
                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    >
                      <option value="regex">Regex</option>
                      <option value="function_calling">Function Calling</option>
                      <option value="double_call">Double Call</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Presupuesto Mensual (USD)
                    </label>
                    <input
                      v-model.number="editForm.ai_monthly_budget"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                </div>
              </div>

              <!-- Estadísticas -->
              <div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Estadísticas</h4>
                <div class="grid grid-cols-3 gap-4">
                  <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Uso Mensual</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                      ${{ (empresaDetail.monthly_usage || 0).toFixed(2) }}
                    </p>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Queries</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                      {{ empresaDetail.total_queries || 0 }}
                    </p>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Última Consulta</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                      {{ empresaDetail.last_query_at || 'N/A' }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Usuarios con acceso AI -->
              <div v-if="empresaDetail.users_with_ai_access">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                  Usuarios con Acceso AI ({{ empresaDetail.users_with_ai_access.length }})
                </h4>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 max-h-48 overflow-y-auto">
                  <div v-for="user in empresaDetail.users_with_ai_access" :key="user.id" class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                    </div>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded text-xs">
                      {{ user.role || 'Usuario' }}
                    </span>
                  </div>

                  <p v-if="empresaDetail.users_with_ai_access.length === 0" class="text-center text-gray-500 dark:text-gray-400">
                    No hay usuarios con acceso AI
                  </p>
                </div>
              </div>

              <!-- Botones -->
              <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                  @click="showDetailModal = false"
                  class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                >
                  Cancelar
                </button>
                <button
                  @click="handleSaveConfig"
                  :disabled="updating"
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50"
                >
                  {{ updating ? 'Guardando...' : 'Guardar Cambios' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useAIManagement } from '@/composables/useAIManagement'

const {
  globalStats,
  empresas,
  empresaDetail,
  loading,
  updating,
  currentPage,
  lastPage,
  total,
  hasPrevPage,
  hasNextPage,
  fetchGlobalStats,
  fetchEmpresas,
  fetchEmpresaDetail,
  toggleAI,
  updateEmpresaConfig,
  resetUsage,
  changePage,
  searchEmpresas,
  applyFilters,
  clearFilters
} = useAIManagement()

// Estado local
const searchInput = ref('')
const filterAI = ref(null)
const filterMode = ref(null)
const showDetailModal = ref(false)
const editForm = ref({
  ai_detection_mode: 'regex',
  ai_monthly_budget: 0
})

// Cargar datos al montar
onMounted(async () => {
  await Promise.all([
    fetchGlobalStats(),
    fetchEmpresas()
  ])
})

// Métodos
const handleSearch = () => {
  searchEmpresas(searchInput.value)
}

const handleClearFilters = () => {
  searchInput.value = ''
  filterAI.value = null
  filterMode.value = null
  clearFilters()
}

const handleToggleAI = async (empresaId, enabled) => {
  await toggleAI(empresaId, enabled)
}

const handleResetUsage = async (empresaId) => {
  await resetUsage(empresaId)
}

const handleViewDetail = async (empresaId) => {
  await fetchEmpresaDetail(empresaId)

  if (empresaDetail.value) {
    editForm.value = {
      ai_detection_mode: empresaDetail.value.ai_detection_mode || 'regex',
      ai_monthly_budget: empresaDetail.value.ai_monthly_budget || 0
    }
    showDetailModal.value = true
  }
}

const handleSaveConfig = async () => {
  if (!empresaDetail.value) return

  await updateEmpresaConfig(empresaDetail.value.id, editForm.value)
  showDetailModal.value = false
}

const getModeLabel = (mode) => {
  const labels = {
    regex: 'Regex',
    function_calling: 'Function Calling',
    double_call: 'Double Call'
  }
  return labels[mode] || mode
}

const getUsagePercentage = (empresa) => {
  if (!empresa.ai_monthly_budget) return 0
  return Math.round((empresa.monthly_usage / empresa.ai_monthly_budget) * 100)
}

const applyFiltersHandler = () => {
  applyFilters({
    ai_enabled: filterAI.value,
    detection_mode: filterMode.value
  })
}
</script>
