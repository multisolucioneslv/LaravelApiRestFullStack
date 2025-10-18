<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <!-- Encabezado -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Configuración de AI Chat
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
          Gestiona la configuración del sistema de inteligencia artificial para tu empresa
        </p>
      </div>

      <!-- Loading General -->
      <div v-if="loading && !config" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else>
        <!-- Estado del Servicio -->
        <div
          :class="[
            'rounded-lg p-4 flex items-start space-x-3',
            isAIEnabled ? 'bg-green-100 dark:bg-green-900 border border-green-500' : 'bg-yellow-100 dark:bg-yellow-900 border border-yellow-500'
          ]"
        >
          <svg
            v-if="isAIEnabled"
            class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg
            v-else
            class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <h3
              :class="[
                'font-semibold',
                isAIEnabled ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200'
              ]"
            >
              {{ isAIEnabled ? 'Servicio AI Chat Activo' : 'Servicio AI Chat Deshabilitado' }}
            </h3>
            <p
              :class="[
                'text-sm mt-1',
                isAIEnabled ? 'text-green-700 dark:text-green-300' : 'text-yellow-700 dark:text-yellow-300'
              ]"
            >
              {{
                isAIEnabled
                  ? 'El servicio de AI Chat está habilitado para tu empresa. Puedes configurar los parámetros a continuación.'
                  : 'El servicio de AI Chat no está habilitado para tu empresa. Contacta al administrador del sistema para habilitarlo.'
              }}
            </p>
          </div>
        </div>

        <!-- Plan Actual -->
        <div v-if="isAIEnabled" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Plan Actual</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Info del Plan -->
            <div>
              <div class="border-2 border-blue-500 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ getModeLabel(currentDetectionMode) }}
                  </h3>
                  <span
                    :class="[
                      'px-3 py-1 rounded-full text-xs font-medium',
                      currentDetectionMode === 'regex' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '',
                      currentDetectionMode === 'function_calling' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '',
                      currentDetectionMode === 'double_call' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : ''
                    ]"
                  >
                    {{ currentDetectionMode }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ getModeDescription(currentDetectionMode) }}
                </p>
              </div>

              <!-- Selector de Plan -->
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Cambiar Plan
                </label>
                <select
                  v-model="formPlan.ai_detection_mode"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option value="regex">Regex - Básico (Económico)</option>
                  <option value="function_calling">Function Calling - Intermedio (Recomendado)</option>
                  <option value="double_call">Double Call - Premium (Máxima Precisión)</option>
                </select>
              </div>

              <button
                @click="handleUpdatePlan"
                :disabled="updating || formPlan.ai_detection_mode === currentDetectionMode"
                class="mt-4 w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ updating ? 'Guardando...' : 'Guardar Cambios de Plan' }}
              </button>
            </div>

            <!-- Costos Estimados -->
            <div>
              <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Costo Estimado por Consulta</h4>

                <div class="space-y-3">
                  <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center space-x-2">
                      <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                      <span class="text-sm text-gray-700 dark:text-gray-300">Regex</span>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">$0.002 - $0.005</span>
                  </div>

                  <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center space-x-2">
                      <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                      <span class="text-sm text-gray-700 dark:text-gray-300">Function Calling</span>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">$0.005 - $0.010</span>
                  </div>

                  <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center space-x-2">
                      <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                      <span class="text-sm text-gray-700 dark:text-gray-300">Double Call</span>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">$0.010 - $0.020</span>
                  </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                  Los costos varían según la longitud de la consulta y la respuesta generada
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Credenciales OpenAI (Opcional) -->
        <div v-if="isAIEnabled" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
            Credenciales OpenAI (Opcional)
          </h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Por defecto se usan credenciales compartidas. Configura las tuyas para tener control total y evitar límites compartidos.
          </p>

          <div class="space-y-4">
            <!-- Checkbox: Usar credenciales propias -->
            <div class="flex items-center">
              <input
                v-model="useOwnCredentials"
                type="checkbox"
                id="use-own-credentials"
                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
              />
              <label for="use-own-credentials" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Usar mis propias credenciales de OpenAI
              </label>
            </div>

            <!-- Formulario de Credenciales -->
            <div v-if="useOwnCredentials" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <!-- API Key -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  API Key de OpenAI *
                </label>
                <input
                  v-model="formCredentials.openai_api_key"
                  type="password"
                  placeholder="sk-..."
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-500': apiKeyError }"
                />
                <p v-if="apiKeyError" class="text-red-500 text-sm mt-1">{{ apiKeyError }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Obtén tu API key en <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 hover:underline">platform.openai.com</a>
                </p>
              </div>

              <!-- Modelo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Modelo
                </label>
                <select
                  v-model="formCredentials.openai_model"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option value="gpt-3.5-turbo">GPT-3.5 Turbo (Rápido y económico)</option>
                  <option value="gpt-4">GPT-4 (Mayor precisión)</option>
                  <option value="gpt-4-turbo">GPT-4 Turbo (Recomendado)</option>
                </select>
              </div>

              <!-- Max Tokens -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Max Tokens
                </label>
                <input
                  v-model.number="formCredentials.openai_max_tokens"
                  type="number"
                  min="100"
                  max="4000"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-500': maxTokensError }"
                />
                <p v-if="maxTokensError" class="text-red-500 text-sm mt-1">{{ maxTokensError }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Entre 100 y 4000</p>
              </div>

              <!-- Temperature -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Temperature: {{ formCredentials.openai_temperature }}
                </label>
                <input
                  v-model.number="formCredentials.openai_temperature"
                  type="range"
                  min="0"
                  max="2"
                  step="0.1"
                  class="w-full"
                />
                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                  <span>0 (Preciso)</span>
                  <span>1 (Balanceado)</span>
                  <span>2 (Creativo)</span>
                </div>
              </div>
            </div>

            <button
              v-if="useOwnCredentials"
              @click="handleUpdateCredentials"
              :disabled="updating || !isCredentialsValid"
              class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ updating ? 'Guardando...' : 'Guardar Credenciales' }}
            </button>
          </div>
        </div>

        <!-- Presupuesto Mensual -->
        <div v-if="isAIEnabled" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Presupuesto Mensual</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Formulario -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Presupuesto Mensual (USD)
              </label>
              <input
                v-model.number="formBudget.ai_monthly_budget"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': budgetError }"
              />
              <p v-if="budgetError" class="text-red-500 text-sm mt-1">{{ budgetError }}</p>

              <button
                @click="handleUpdateBudget"
                :disabled="updating || !isBudgetValid"
                class="mt-4 w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ updating ? 'Guardando...' : 'Actualizar Presupuesto' }}
              </button>
            </div>

            <!-- Visualización -->
            <div>
              <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <div class="space-y-4">
                  <!-- Uso Actual -->
                  <div>
                    <div class="flex items-center justify-between mb-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">Uso actual del mes</span>
                      <span class="font-semibold text-gray-900 dark:text-white">
                        ${{ currentUsage.toFixed(2) }}
                      </span>
                    </div>

                    <!-- Barra de Progreso -->
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                      <div
                        :class="[
                          'h-4 rounded-full transition-all flex items-center justify-end pr-2',
                          usagePercentage >= 90 ? 'bg-red-600' : '',
                          usagePercentage >= 70 && usagePercentage < 90 ? 'bg-yellow-600' : '',
                          usagePercentage < 70 ? 'bg-green-600' : ''
                        ]"
                        :style="{ width: `${Math.min(usagePercentage, 100)}%` }"
                      >
                        <span v-if="usagePercentage > 10" class="text-xs font-bold text-white">
                          {{ usagePercentage }}%
                        </span>
                      </div>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      De ${{ monthlyBudget.toFixed(2) }} presupuestados
                    </p>
                  </div>

                  <!-- Fecha de Reset -->
                  <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      Próximo reset: <span class="font-medium text-gray-900 dark:text-white">{{ getNextResetDate() }}</span>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estadísticas de Uso -->
        <div v-if="isAIEnabled && usageStats" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Estadísticas de Uso</h2>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">Total de Consultas</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ usageStats.total_queries || 0 }}
              </p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">Uso Monetario</p>
              <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                ${{ (usageStats.monthly_cost || 0).toFixed(2) }}
              </p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">Última Consulta</p>
              <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                {{ usageStats.last_query_at || 'N/A' }}
              </p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">Usuarios con Acceso</p>
              <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                {{ usageStats.users_with_access || 0 }}
              </p>
            </div>
          </div>
        </div>

        <!-- Gestión de Permisos de Usuarios -->
        <div v-if="isAIEnabled" class="bg-white dark:bg-gray-800 rounded-lg shadow">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
              Gestión de Permisos de Usuarios
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              Controla qué usuarios tienen acceso al sistema de AI Chat
            </p>
          </div>

          <!-- Loading Usuarios -->
          <div v-if="loading && users.length === 0" class="p-6">
            <div class="flex justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
          </div>

          <!-- Tabla de Usuarios -->
          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-100 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Usuario
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Rol
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Acceso AI
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4">
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 rounded text-sm">
                      {{ user.role || 'Usuario' }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <button
                      @click="handleToggleUserPermission(user.id, !user.has_ai_permission)"
                      :class="[
                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                        user.has_ai_permission ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'
                      ]"
                      :disabled="updating"
                    >
                      <span
                        :class="[
                          'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                          user.has_ai_permission ? 'translate-x-6' : 'translate-x-1'
                        ]"
                      />
                    </button>
                  </td>
                </tr>

                <tr v-if="users.length === 0">
                  <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    No hay usuarios disponibles
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useAIConfig } from '@/composables/useAIConfig'

const {
  config,
  usageStats,
  users,
  loading,
  updating,
  isAIEnabled,
  currentDetectionMode,
  monthlyBudget,
  currentUsage,
  usagePercentage,
  fetchConfig,
  updateConfig,
  fetchUsageStats,
  fetchUsers,
  updateUserPermission,
  updateDetectionMode,
  updateOpenAICredentials,
  updateMonthlyBudget,
  validateApiKey,
  validateTemperature,
  validateMaxTokens,
  validateBudget
} = useAIConfig()

// Estado local
const useOwnCredentials = ref(false)

// Formularios
const formPlan = ref({
  ai_detection_mode: 'regex'
})

const formCredentials = ref({
  openai_api_key: '',
  openai_model: 'gpt-4-turbo',
  openai_max_tokens: 1500,
  openai_temperature: 0.7
})

const formBudget = ref({
  ai_monthly_budget: 0
})

// Errores de validación
const apiKeyError = ref('')
const maxTokensError = ref('')
const temperatureError = ref('')
const budgetError = ref('')

// Computed
const isCredentialsValid = computed(() => {
  if (!useOwnCredentials.value) return true

  apiKeyError.value = ''
  maxTokensError.value = ''
  temperatureError.value = ''

  const apiKeyValidation = validateApiKey(formCredentials.value.openai_api_key)
  if (apiKeyValidation !== true) {
    apiKeyError.value = apiKeyValidation
    return false
  }

  const maxTokensValidation = validateMaxTokens(formCredentials.value.openai_max_tokens)
  if (maxTokensValidation !== true) {
    maxTokensError.value = maxTokensValidation
    return false
  }

  const temperatureValidation = validateTemperature(formCredentials.value.openai_temperature)
  if (temperatureValidation !== true) {
    temperatureError.value = temperatureValidation
    return false
  }

  return true
})

const isBudgetValid = computed(() => {
  budgetError.value = ''

  const budgetValidation = validateBudget(formBudget.value.ai_monthly_budget)
  if (budgetValidation !== true) {
    budgetError.value = budgetValidation
    return false
  }

  return true
})

// Métodos
const getModeLabel = (mode) => {
  const labels = {
    regex: 'Plan Básico - Regex',
    function_calling: 'Plan Intermedio - Function Calling',
    double_call: 'Plan Premium - Double Call'
  }
  return labels[mode] || mode
}

const getModeDescription = (mode) => {
  const descriptions = {
    regex: 'Detección basada en patrones predefinidos. Rápido y económico, ideal para consultas estándar.',
    function_calling: 'Detección inteligente mediante function calling. Balance óptimo entre costo y precisión.',
    double_call: 'Máxima precisión con doble verificación. Recomendado para casos que requieren alta exactitud.'
  }
  return descriptions[mode] || ''
}

const getNextResetDate = () => {
  const now = new Date()
  const nextMonth = new Date(now.getFullYear(), now.getMonth() + 1, 1)
  return nextMonth.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })
}

const handleUpdatePlan = async () => {
  await updateDetectionMode(formPlan.value.ai_detection_mode)
}

const handleUpdateCredentials = async () => {
  if (!isCredentialsValid.value) return

  const credentials = useOwnCredentials.value ? formCredentials.value : {
    openai_api_key: null,
    openai_model: null,
    openai_max_tokens: null,
    openai_temperature: null
  }

  await updateOpenAICredentials(credentials)
}

const handleUpdateBudget = async () => {
  if (!isBudgetValid.value) return

  await updateMonthlyBudget(formBudget.value.ai_monthly_budget)
}

const handleToggleUserPermission = async (userId, grantPermission) => {
  await updateUserPermission(userId, grantPermission)
}

// Watch para sincronizar formularios con config
watch(config, (newConfig) => {
  if (newConfig) {
    formPlan.value.ai_detection_mode = newConfig.ai_detection_mode || 'regex'
    formBudget.value.ai_monthly_budget = newConfig.ai_monthly_budget || 0

    // Si tiene credenciales propias configuradas
    if (newConfig.openai_api_key) {
      useOwnCredentials.value = true
      formCredentials.value = {
        openai_api_key: newConfig.openai_api_key || '',
        openai_model: newConfig.openai_model || 'gpt-4-turbo',
        openai_max_tokens: newConfig.openai_max_tokens || 1500,
        openai_temperature: newConfig.openai_temperature || 0.7
      }
    }
  }
}, { immediate: true })

// Cargar datos al montar
onMounted(async () => {
  await fetchConfig()

  if (isAIEnabled.value) {
    await Promise.all([
      fetchUsageStats(),
      fetchUsers()
    ])
  }
})
</script>
