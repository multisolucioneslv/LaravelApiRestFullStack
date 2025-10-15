<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Crear Sistema
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Completa el formulario para crear un nuevo sistema
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
                  Nombre del Sistema *
                </label>
                <Input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  placeholder="Ingrese el nombre del sistema"
                />
              </div>

              <!-- Versión -->
              <div>
                <label for="version" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Versión
                </label>
                <Input
                  id="version"
                  v-model="form.version"
                  type="text"
                  placeholder="1.0.0"
                />
              </div>
            </div>
          </div>

          <!-- Sección: Configuración -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Configuración
            </h3>
            <div class="space-y-4">
              <!-- Configuración JSON -->
              <div>
                <label for="configuracion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Configuración (JSON)
                </label>
                <textarea
                  id="configuracion"
                  v-model="form.configuracion"
                  rows="6"
                  placeholder='{"key": "value"}'
                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                ></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Ingrese un objeto JSON válido (opcional)
                </p>
              </div>
            </div>
          </div>

          <!-- Sección: Logo -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Logo del Sistema
            </h3>
            <div class="space-y-4">
              <!-- Zona de Drag & Drop -->
              <div
                @drop.prevent="handleDrop"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                :class="[
                  'border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
                  isDragging
                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-950'
                    : 'border-gray-300 dark:border-gray-600 hover:border-primary-400'
                ]"
                @click="$refs.logoInput.click()"
              >
                <input
                  ref="logoInput"
                  type="file"
                  accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                  class="hidden"
                  @change="handleFileSelect"
                />

                <div v-if="!logoPreview" class="space-y-3">
                  <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                  </svg>
                  <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold text-primary-600 dark:text-primary-400">Haz clic para subir</span>
                    o arrastra y suelta
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-500">
                    PNG, JPG, GIF o WEBP (máx. 5MB)
                  </p>
                </div>

                <!-- Vista previa de la imagen -->
                <div v-else class="relative">
                  <img :src="logoPreview" alt="Logo preview" class="mx-auto h-32 w-32 rounded-lg object-cover" />
                  <button
                    type="button"
                    @click.stop="removeLogo"
                    class="absolute top-0 right-1/2 translate-x-16 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 transition-colors"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    {{ logoFileName }}
                  </p>
                </div>
              </div>

              <!-- Mensaje de error -->
              <p v-if="logoError" class="text-sm text-red-600 dark:text-red-400">
                {{ logoError }}
              </p>
            </div>
          </div>

          <!-- Sección: Estado -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Estado
            </h3>
            <div class="flex items-center space-x-3">
              <Checkbox
                :checked="form.activo"
                @update:checked="form.activo = $event"
              />
              <label class="text-sm font-medium text-gray-900 dark:text-white">
                Sistema activo
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
              {{ loading ? 'Creando...' : 'Crear Sistema' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useSistemas } from '@/composables/useSistemas'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const { createSistema, loading, goToIndex } = useSistemas()

const isDragging = ref(false)
const logoPreview = ref(null)
const logoFileName = ref('')
const logoError = ref('')
const logoInput = ref(null)

const form = ref({
  nombre: '',
  version: '1.0.0',
  configuracion: '',
  logo: null, // Archivo de imagen
  activo: true,
})

// Validar archivo de imagen
const validateImage = (file) => {
  logoError.value = ''

  // Validar tipo de archivo
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
  if (!validTypes.includes(file.type)) {
    logoError.value = 'Formato no válido. Solo se permiten imágenes JPG, PNG, GIF o WEBP.'
    return false
  }

  // Validar tamaño (5MB máximo)
  const maxSize = 5 * 1024 * 1024 // 5MB en bytes
  if (file.size > maxSize) {
    logoError.value = 'La imagen excede el tamaño máximo de 5MB.'
    return false
  }

  return true
}

// Manejar selección de archivo
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (validateImage(file)) {
    form.value.logo = file
    logoFileName.value = file.name

    // Crear vista previa
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Manejar drop de archivo
const handleDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (!file) return

  if (validateImage(file)) {
    form.value.logo = file
    logoFileName.value = file.name

    // Crear vista previa
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Remover logo
const removeLogo = () => {
  form.value.logo = null
  logoPreview.value = null
  logoFileName.value = ''
  logoError.value = ''
  if (logoInput.value) {
    logoInput.value.value = ''
  }
}

const handleSubmit = async () => {
  try {
    // Crear FormData para enviar archivo
    const formData = new FormData()
    formData.append('nombre', form.value.nombre)
    formData.append('activo', form.value.activo ? '1' : '0')

    if (form.value.version) {
      formData.append('version', form.value.version)
    }
    if (form.value.configuracion) {
      formData.append('configuracion', form.value.configuracion)
    }
    if (form.value.logo) {
      formData.append('logo', form.value.logo)
    }

    await createSistema(formData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
