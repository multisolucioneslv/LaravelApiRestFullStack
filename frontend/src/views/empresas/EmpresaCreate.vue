<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Crear Empresa
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Completa el formulario para crear una nueva empresa
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

      <!-- Formulario -->
      <form @submit.prevent="handleSubmit" class="card">
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

              <!-- Teléfono -->
              <div>
                <label for="telefono_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Teléfono
                </label>
                <select
                  id="telefono_id"
                  v-model="form.telefono_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione...</option>
                  <option
                    v-for="telefono in telefonos"
                    :key="telefono.id"
                    :value="telefono.id"
                  >
                    {{ telefono.telefono }}
                  </option>
                </select>
              </div>

              <!-- Moneda -->
              <div>
                <label for="moneda_id" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Moneda
                </label>
                <select
                  id="moneda_id"
                  v-model="form.moneda_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">Seleccione...</option>
                  <option
                    v-for="moneda in monedas"
                    :key="moneda.id"
                    :value="moneda.id"
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
              {{ loading ? 'Creando...' : 'Crear Empresa' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useEmpresas } from '@/composables/useEmpresas'
import { useTelefonos } from '@/composables/useTelefonos'
import { useMonedas } from '@/composables/useMonedas'
import { useAuthStore } from '@/stores/auth'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import FileUploadSection from '@/components/common/FileUploadSection.vue'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()

const { createEmpresa, loading, goToIndex } = useEmpresas()
const { telefonos, fetchTelefonos, cleanupTelefonos } = useTelefonos()
const { monedas, fetchMonedas, cleanupMonedas } = useMonedas()

// ==========================================
// SEGURIDAD: CARGA LAZY
// ==========================================

// Cargar datos SOLO si hay sesión activa
onMounted(async () => {
  if (authStore.isAuthenticated) {
    console.log('[SECURITY] EmpresaCreate: Inicializando con sesión activa')
    await Promise.all([
      fetchTelefonos(),
      fetchMonedas()
    ])
  } else {
    console.warn('[SECURITY] EmpresaCreate: No se puede cargar sin sesión')
  }
})

// Limpiar datos cuando se sale de la vista
onUnmounted(() => {
  console.log('[SECURITY] EmpresaCreate: Limpiando datos al desmontar componente')
  if (typeof cleanupTelefonos === 'function') cleanupTelefonos()
  if (typeof cleanupMonedas === 'function') cleanupMonedas()
})

// Vigilar cambios en autenticación
watch(() => authStore.isAuthenticated, async (isAuth) => {
  if (!isAuth) {
    console.warn('[SECURITY] EmpresaCreate: Sesión cerrada, limpiando datos')
    if (typeof cleanupTelefonos === 'function') cleanupTelefonos()
    if (typeof cleanupMonedas === 'function') cleanupMonedas()
  } else {
    console.log('[SECURITY] EmpresaCreate: Sesión iniciada, cargando datos')
    await Promise.all([
      fetchTelefonos(),
      fetchMonedas()
    ])
  }
})

// Estados para archivos
const logoPreview = ref(null)
const logoFileName = ref('')
const logoError = ref('')

const faviconPreview = ref(null)
const faviconFileName = ref('')
const faviconError = ref('')

const fondoPreview = ref(null)
const fondoFileName = ref('')
const fondoError = ref('')

const form = ref({
  nombre: '',
  email: '',
  telefono_id: '',
  moneda_id: '',
  direccion: '',
  zona_horaria: 'America/Los_Angeles',
  logo: null,
  favicon: null,
  fondo_login: null,
  activo: true,
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
    if (form.value.telefono_id) {
      formData.append('telefono_id', form.value.telefono_id)
    }
    if (form.value.moneda_id) {
      formData.append('moneda_id', form.value.moneda_id)
    }
    if (form.value.direccion) {
      formData.append('direccion', form.value.direccion)
    }
    if (form.value.zona_horaria) {
      formData.append('zona_horaria', form.value.zona_horaria)
    }
    if (form.value.logo) {
      formData.append('logo', form.value.logo)
    }
    if (form.value.favicon) {
      formData.append('favicon', form.value.favicon)
    }
    if (form.value.fondo_login) {
      formData.append('fondo_login', form.value.fondo_login)
    }

    await createEmpresa(formData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
