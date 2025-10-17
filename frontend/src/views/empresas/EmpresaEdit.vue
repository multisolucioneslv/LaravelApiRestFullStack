<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Empresa
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Actualiza la información de la empresa
          </p>
        </div>
        <Button
          type="button"
          variant="outline"
          @click="handleCancel"
        >
          Volver
        </Button>
      </div>

      <!-- Loading -->
      <div v-if="loadingEmpresa" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <form v-else @submit.prevent="handleSubmit" class="card">
        <div class="space-y-8">
          <!-- Sección: Información Básica -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información Básica
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Nombre -->
              <div>
                <label for="nombre" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Nombre *
                </label>
                <Input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  placeholder="Ingrese el nombre de la empresa"
                />
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Email
                </label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="empresa@ejemplo.com"
                />
              </div>

              <!-- Teléfonos (múltiples) -->
              <div class="md:col-span-2">
                <PhoneInput v-model="form.phones" />
              </div>

              <!-- Moneda -->
              <div>
                <label for="currency_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Moneda
                </label>
                <select
                  id="currency_id"
                  v-model.number="form.currency_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option :value="null">Seleccione...</option>
                  <option
                    v-for="moneda in monedas"
                    :key="moneda.id"
                    :value="Number(moneda.id)"
                  >
                    {{ moneda.nombre }} ({{ moneda.simbolo }})
                  </option>
                </select>
              </div>

              <!-- Dirección -->
              <div class="md:col-span-2">
                <label for="direccion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Dirección
                </label>
                <textarea
                  id="direccion"
                  v-model="form.direccion"
                  rows="3"
                  placeholder="Dirección completa de la empresa"
                  class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                ></textarea>
              </div>

              <!-- Zona Horaria -->
              <div>
                <label for="zona_horaria" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Zona Horaria
                </label>
                <Input
                  id="zona_horaria"
                  v-model="form.zona_horaria"
                  type="text"
                  placeholder="America/Los_Angeles"
                />
              </div>
            </div>
          </div>

          <!-- Sección: Logo -->
          <FileUploadSection
            title="Logo de la Empresa"
            description="PNG, JPG, GIF, WEBP o SVG (máx. 5MB)"
            v-model:file="form.logo"
            v-model:preview="logoPreview"
            v-model:filename="logoFileName"
            v-model:error="logoError"
            :max-size="5"
            :current-file-url="currentLogo"
            accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml"
          />

          <!-- Sección: Favicon -->
          <FileUploadSection
            title="Favicon"
            description="PNG, JPG, GIF, WEBP, SVG o ICO (máx. 2MB)"
            v-model:file="form.favicon"
            v-model:preview="faviconPreview"
            v-model:filename="faviconFileName"
            v-model:error="faviconError"
            :max-size="2"
            :current-file-url="currentFavicon"
            accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml,image/x-icon"
          />

          <!-- Sección: Fondo de Login -->
          <FileUploadSection
            title="Fondo de Login"
            description="PNG, JPG, GIF o WEBP (máx. 10MB)"
            v-model:file="form.fondo_login"
            v-model:preview="fondoPreview"
            v-model:filename="fondoFileName"
            v-model:error="fondoError"
            :max-size="10"
            :current-file-url="currentFondo"
            accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
          />

          <!-- Sección: Configuración -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Configuración
            </h3>
            <div class="flex items-center space-x-3">
              <Checkbox
                :checked="form.activo"
                @update:checked="form.activo = $event"
              />
              <label class="text-sm font-medium text-gray-900 dark:text-white">
                Empresa activa
              </label>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <Button
              type="button"
              variant="outline"
              @click="handleCancel"
              :disabled="loading"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              :disabled="loading"
            >
              {{ loading ? 'Actualizando...' : 'Actualizar Empresa' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useEmpresas } from '@/composables/useEmpresas'
import { useMonedas } from '@/composables/useMonedas'
import { useAuthStore } from '@/stores/auth'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import PhoneInput from '@/components/forms/PhoneInput.vue'
import FileUploadSection from '@/components/common/FileUploadSection.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()

const route = useRoute()
const { fetchEmpresa, updateEmpresa, loading, goToIndex } = useEmpresas()
const { monedas, fetchMonedas, cleanupMonedas } = useMonedas()

const loadingEmpresa = ref(true)
const empresaId = ref(route.params.id)

// Estados para archivos
const logoPreview = ref(null)
const logoFileName = ref('')
const logoError = ref('')
const currentLogo = ref(null)

const faviconPreview = ref(null)
const faviconFileName = ref('')
const faviconError = ref('')
const currentFavicon = ref(null)

const fondoPreview = ref(null)
const fondoFileName = ref('')
const fondoError = ref('')
const currentFondo = ref(null)

const form = ref({
  nombre: '',
  email: '',
  telefono_id: null,
  phones: [{ telefono: '' }],
  currency_id: '',
  direccion: '',
  zona_horaria: '',
  logo: null,
  favicon: null,
  fondo_login: null,
  activo: true,
})

// ==========================================
// SEGURIDAD: CARGA LAZY
// ==========================================

// Cargar datos de la empresa SOLO si hay sesión activa
onMounted(async () => {
  if (authStore.isAuthenticated) {
    console.log('[SECURITY] EmpresaEdit: Inicializando con sesión activa')
    try {
      // Cargar datos en paralelo
      await Promise.all([
        fetchMonedas()
      ])

      const empresa = await fetchEmpresa(empresaId.value)

      // Llenar formulario con datos existentes
      form.value.nombre = empresa.nombre
      form.value.email = empresa.email || ''

      // Asignar telefono_id si existe
      form.value.telefono_id = empresa.telefono_id ? Number(empresa.telefono_id) : null

      // Convertir teléfonos a formato esperado por PhoneInput
      form.value.phones = empresa.phones && empresa.phones.length > 0
        ? empresa.phones.map(phone => ({ telefono: phone.telefono }))
        : [{ telefono: '' }]

      // Forzar conversión de currency_id a número para que el select funcione
      form.value.currency_id = empresa.currency_id ? Number(empresa.currency_id) : null

      form.value.direccion = empresa.direccion || ''
      form.value.zona_horaria = empresa.zona_horaria || ''
      form.value.activo = empresa.activo

      // Guardar URLs de archivos actuales
      currentLogo.value = empresa.logo
      currentFavicon.value = empresa.favicon
      currentFondo.value = empresa.fondo_login

    } catch (err) {
      console.error('Error al cargar empresa:', err)
    } finally {
      loadingEmpresa.value = false
    }
  } else {
    console.warn('[SECURITY] EmpresaEdit: No se puede cargar sin sesión')
    loadingEmpresa.value = false
  }
})

// Limpiar datos cuando se sale de la vista
onUnmounted(() => {
  console.log('[SECURITY] EmpresaEdit: Limpiando datos al desmontar componente')
  if (typeof cleanupMonedas === 'function') cleanupMonedas()
})

// Vigilar cambios en autenticación
watch(() => authStore.isAuthenticated, async (isAuth) => {
  if (!isAuth) {
    console.warn('[SECURITY] EmpresaEdit: Sesión cerrada, limpiando datos')
    if (typeof cleanupMonedas === 'function') cleanupMonedas()
  } else {
    console.log('[SECURITY] EmpresaEdit: Sesión iniciada, recargando datos')
    try {
      await Promise.all([
        fetchMonedas()
      ])
      const empresa = await fetchEmpresa(empresaId.value)
      // Actualizar formulario...
      form.value.nombre = empresa.nombre
      form.value.email = empresa.email || ''

      // Asignar telefono_id si existe
      form.value.telefono_id = empresa.telefono_id ? Number(empresa.telefono_id) : null

      // Convertir teléfonos
      form.value.phones = empresa.phones && empresa.phones.length > 0
        ? empresa.phones.map(phone => ({ telefono: phone.telefono }))
        : [{ telefono: '' }]

      // Convertir currency_id a número
      form.value.currency_id = empresa.currency_id ? Number(empresa.currency_id) : null

      form.value.direccion = empresa.direccion || ''
      form.value.zona_horaria = empresa.zona_horaria || ''
      form.value.activo = empresa.activo
      currentLogo.value = empresa.logo
      currentFavicon.value = empresa.favicon
      currentFondo.value = empresa.fondo_login
    } catch (err) {
      console.error('Error al recargar empresa:', err)
    }
  }
})

const handleSubmit = async () => {
  try {
    // Crear FormData para enviar archivos
    const formData = new FormData()
    formData.append('nombre', form.value.nombre)
    formData.append('activo', form.value.activo ? '1' : '0')

    if (form.value.email) {
      formData.append('email', form.value.email)
    }

    // Agregar teléfonos (filtrar vacíos)
    const validPhones = form.value.phones.filter(phone => phone.telefono && phone.telefono.trim() !== '')
    if (validPhones.length > 0) {
      // Enviar como JSON string para FormData
      formData.append('phones', JSON.stringify(validPhones))
    }

    if (form.value.currency_id) {
      formData.append('currency_id', form.value.currency_id)
    }
    if (form.value.direccion) {
      formData.append('direccion', form.value.direccion)
    }
    if (form.value.zona_horaria) {
      formData.append('zona_horaria', form.value.zona_horaria)
    }

    // Solo enviar archivos si se seleccionaron nuevos
    if (form.value.logo) {
      formData.append('logo', form.value.logo)
    }
    if (form.value.favicon) {
      formData.append('favicon', form.value.favicon)
    }
    if (form.value.fondo_login) {
      formData.append('fondo_login', form.value.fondo_login)
    }

    await updateEmpresa(empresaId.value, formData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
