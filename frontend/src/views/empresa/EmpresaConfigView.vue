<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configuración de Empresa</h1>
        <p class="text-gray-600 dark:text-gray-400">Gestiona la información y personalización de tu empresa</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Configuration Form -->
      <div v-else-if="empresa" class="space-y-6">
        <!-- Información General -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h3>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Nombre de la Empresa -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Nombre de la Empresa *
                </label>
                <input
                  v-model="form.nombre"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                />
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Email
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                />
              </div>

              <!-- Dirección -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Dirección
                </label>
                <textarea
                  v-model="form.direccion"
                  rows="2"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                ></textarea>
              </div>

              <!-- Zona Horaria -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Zona Horaria *
                </label>
                <select
                  v-model="form.zona_horaria"
                  required
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option value="America/Los_Angeles">América/Los Ángeles (PST/PDT)</option>
                  <option value="America/Denver">América/Denver (MST/MDT)</option>
                  <option value="America/Chicago">América/Chicago (CST/CDT)</option>
                  <option value="America/New_York">América/Nueva York (EST/EDT)</option>
                  <option value="America/Mexico_City">América/Ciudad de México (CST)</option>
                  <option value="America/Guatemala">América/Guatemala (CST)</option>
                  <option value="America/El_Salvador">América/El Salvador (CST)</option>
                  <option value="America/Tegucigalpa">América/Tegucigalpa (CST)</option>
                  <option value="America/Managua">América/Managua (CST)</option>
                  <option value="America/Costa_Rica">América/Costa Rica (CST)</option>
                  <option value="America/Panama">América/Panamá (EST)</option>
                </select>
              </div>

              <!-- Moneda -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Moneda
                </label>
                <select
                  v-model="form.moneda_id"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option :value="null">Seleccionar...</option>
                  <option v-for="moneda in monedas" :key="moneda.id" :value="moneda.id">
                    {{ moneda.codigo }} - {{ moneda.nombre }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Botón Guardar -->
            <div class="flex justify-end pt-4">
              <button
                type="submit"
                :disabled="updating"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center space-x-2"
              >
                <span v-if="updating" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                <span>{{ updating ? 'Guardando...' : 'Guardar Cambios' }}</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Horarios de Atención -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Horarios de Atención</h3>

          <div class="space-y-3">
            <div
              v-for="(horario, index) in form.horarios"
              :key="index"
              class="flex items-center space-x-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
            >
              <!-- Día -->
              <div class="w-32">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
                  {{ horario.dia }}
                </span>
              </div>

              <!-- Abierto/Cerrado -->
              <div class="flex items-center">
                <input
                  v-model="horario.abierto"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                />
                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                  Abierto
                </label>
              </div>

              <!-- Horario de Apertura -->
              <div v-if="horario.abierto" class="flex-1">
                <input
                  v-model="horario.apertura"
                  type="time"
                  class="w-full px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white text-sm"
                />
              </div>

              <!-- Horario de Cierre -->
              <div v-if="horario.abierto" class="flex-1">
                <input
                  v-model="horario.cierre"
                  type="time"
                  class="w-full px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white text-sm"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Personalización Visual -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personalización Visual</h3>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Logo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Logo de la Empresa
              </label>

              <!-- Preview del Logo -->
              <div class="mb-3">
                <div v-if="logoPreview || logoURL" class="w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                  <img :src="logoPreview || logoURL" alt="Logo" class="max-w-full max-h-full object-contain" />
                </div>
                <div v-else class="w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                  <span class="text-gray-400 text-sm">Sin logo</span>
                </div>
              </div>

              <!-- Botones -->
              <div class="space-y-2">
                <label class="w-full cursor-pointer">
                  <input
                    type="file"
                    @change="(e) => handleFileChange(e, 'logo')"
                    accept="image/*"
                    class="hidden"
                  />
                  <div class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-center text-sm">
                    Cambiar Logo
                  </div>
                </label>

                <button
                  v-if="logoURL"
                  @click="handleDeleteLogo"
                  :disabled="updating"
                  class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm disabled:opacity-50"
                >
                  Eliminar Logo
                </button>
              </div>

              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                JPG, PNG, GIF, WEBP (MAX. 2MB)
              </p>
            </div>

            <!-- Favicon -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Favicon
              </label>

              <!-- Preview del Favicon -->
              <div class="mb-3">
                <div v-if="faviconPreview || faviconURL" class="w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                  <img :src="faviconPreview || faviconURL" alt="Favicon" class="max-w-full max-h-full object-contain" />
                </div>
                <div v-else class="w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                  <span class="text-gray-400 text-sm">Sin favicon</span>
                </div>
              </div>

              <!-- Botones -->
              <div class="space-y-2">
                <label class="w-full cursor-pointer">
                  <input
                    type="file"
                    @change="(e) => handleFileChange(e, 'favicon')"
                    accept="image/*,.ico"
                    class="hidden"
                  />
                  <div class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-center text-sm">
                    Cambiar Favicon
                  </div>
                </label>

                <button
                  v-if="faviconURL"
                  @click="handleDeleteFavicon"
                  :disabled="updating"
                  class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm disabled:opacity-50"
                >
                  Eliminar Favicon
                </button>
              </div>

              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                ICO, PNG, GIF (MAX. 1MB)
              </p>
            </div>

            <!-- Fondo de Login -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Fondo de Login
              </label>

              <!-- Preview del Fondo -->
              <div class="mb-3">
                <div v-if="fondoPreview || fondoURL" class="w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-700">
                  <img :src="fondoPreview || fondoURL" alt="Fondo Login" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                  <span class="text-gray-400 text-sm">Sin fondo</span>
                </div>
              </div>

              <!-- Botones -->
              <div class="space-y-2">
                <label class="w-full cursor-pointer">
                  <input
                    type="file"
                    @change="(e) => handleFileChange(e, 'fondo_login')"
                    accept="image/*"
                    class="hidden"
                  />
                  <div class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-center text-sm">
                    Cambiar Fondo
                  </div>
                </label>

                <button
                  v-if="fondoURL"
                  @click="handleDeleteFondoLogin"
                  :disabled="updating"
                  class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm disabled:opacity-50"
                >
                  Eliminar Fondo
                </button>
              </div>

              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                JPG, PNG, GIF, WEBP (MAX. 5MB)
              </p>
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
import { useEmpresaConfig } from '@/composables/useEmpresaConfig'
import { useMonedas } from '@/composables/useMonedas'

const { empresa, loading, updating, fetchEmpresaConfig, updateEmpresaConfig, deleteLogo, deleteFavicon, deleteFondoLogin } = useEmpresaConfig()
const { monedas, fetchMonedas } = useMonedas()

const apiURL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

// Formulario
const form = ref({
  nombre: '',
  email: '',
  direccion: '',
  zona_horaria: 'America/Los_Angeles',
  moneda_id: null,
  horarios: [
    { dia: 'lunes', abierto: true, apertura: '08:00', cierre: '18:00' },
    { dia: 'martes', abierto: true, apertura: '08:00', cierre: '18:00' },
    { dia: 'miercoles', abierto: true, apertura: '08:00', cierre: '18:00' },
    { dia: 'jueves', abierto: true, apertura: '08:00', cierre: '18:00' },
    { dia: 'viernes', abierto: true, apertura: '08:00', cierre: '18:00' },
    { dia: 'sabado', abierto: false, apertura: '', cierre: '' },
    { dia: 'domingo', abierto: false, apertura: '', cierre: '' },
  ]
})

// Archivos e imágenes
const logoFile = ref(null)
const logoPreview = ref(null)
const faviconFile = ref(null)
const faviconPreview = ref(null)
const fondoFile = ref(null)
const fondoPreview = ref(null)

// URLs de imágenes existentes
const logoURL = computed(() => {
  if (empresa.value?.logo) {
    return `${apiURL}/storage/${empresa.value.logo}?v=${Date.now()}`
  }
  return null
})

const faviconURL = computed(() => {
  if (empresa.value?.favicon) {
    return `${apiURL}/storage/${empresa.value.favicon}?v=${Date.now()}`
  }
  return null
})

const fondoURL = computed(() => {
  if (empresa.value?.fondo_login) {
    return `${apiURL}/storage/${empresa.value.fondo_login}?v=${Date.now()}`
  }
  return null
})

// Manejo de cambio de archivos
const handleFileChange = (event, type) => {
  const file = event.target.files[0]
  if (!file) return

  // Validaciones de tamaño
  const maxSizes = {
    logo: 2 * 1024 * 1024, // 2MB
    favicon: 1 * 1024 * 1024, // 1MB
    fondo_login: 5 * 1024 * 1024 // 5MB
  }

  if (file.size > maxSizes[type]) {
    alert(`El archivo es demasiado grande. Tamaño máximo: ${maxSizes[type] / 1024 / 1024}MB`)
    return
  }

  // Guardar archivo y crear preview
  if (type === 'logo') {
    logoFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else if (type === 'favicon') {
    faviconFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      faviconPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else if (type === 'fondo_login') {
    fondoFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      fondoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Eliminar imágenes
const handleDeleteLogo = async () => {
  await deleteLogo()
  logoFile.value = null
  logoPreview.value = null
}

const handleDeleteFavicon = async () => {
  await deleteFavicon()
  faviconFile.value = null
  faviconPreview.value = null
}

const handleDeleteFondoLogin = async () => {
  await deleteFondoLogin()
  fondoFile.value = null
  fondoPreview.value = null
}

// Enviar formulario
const handleSubmit = async () => {
  const formData = { ...form.value }

  // Agregar archivos si existen
  if (logoFile.value) {
    formData.logo = logoFile.value
  }
  if (faviconFile.value) {
    formData.favicon = faviconFile.value
  }
  if (fondoFile.value) {
    formData.fondo_login = fondoFile.value
  }

  await updateEmpresaConfig(formData)

  // Limpiar previews después de guardar
  logoFile.value = null
  logoPreview.value = null
  faviconFile.value = null
  faviconPreview.value = null
  fondoFile.value = null
  fondoPreview.value = null
}

// Inicialización
onMounted(async () => {
  await Promise.all([
    fetchEmpresaConfig(),
    fetchMonedas()
  ])

  // Llenar formulario con datos actuales
  if (empresa.value) {
    form.value = {
      nombre: empresa.value.nombre || '',
      email: empresa.value.email || '',
      direccion: empresa.value.direccion || '',
      zona_horaria: empresa.value.zona_horaria || 'America/Los_Angeles',
      moneda_id: empresa.value.moneda?.id || null,
      horarios: empresa.value.horarios || form.value.horarios
    }
  }
})
</script>
