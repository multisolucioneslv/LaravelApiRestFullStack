<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Crear Galería
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Completa el formulario para crear una nueva galería de imágenes
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
            <div class="grid grid-cols-1 gap-6">
              <!-- Nombre -->
              <div>
                <label for="nombre" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Nombre de la Galería *
                </label>
                <Input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  placeholder="Ej: Galería de Productos 2025"
                />
              </div>
            </div>
          </div>

          <!-- Sección: Imágenes -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Imágenes
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
              Arrastra y suelta múltiples imágenes o haz clic para seleccionar. La primera imagen será la imagen principal.
            </p>

            <!-- Zona de Drag & Drop -->
            <div
              @drop.prevent="handleDrop"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @click="triggerFileInput"
              :class="[
                'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-all',
                isDragging
                  ? 'border-primary bg-primary/5'
                  : 'border-gray-300 dark:border-gray-600 hover:border-primary hover:bg-gray-50 dark:hover:bg-gray-800/50'
              ]"
            >
              <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                @change="handleFileSelect"
                class="hidden"
              />
              <div class="space-y-2">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  <span class="font-medium text-primary">Haz clic para seleccionar</span> o arrastra las imágenes aquí
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-500">
                  PNG, JPG, GIF o WEBP (máx. 5MB cada una)
                </p>
              </div>
            </div>

            <!-- Error de archivos -->
            <p v-if="fileError" class="text-sm text-red-600 dark:text-red-400 mt-2">
              {{ fileError }}
            </p>

            <!-- Preview de imágenes seleccionadas -->
            <div v-if="imagePreviews.length > 0" class="mt-6">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
                Imágenes seleccionadas ({{ imagePreviews.length }})
              </h4>
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <div
                  v-for="(preview, index) in imagePreviews"
                  :key="index"
                  class="relative group"
                >
                  <!-- Imagen preview -->
                  <div class="aspect-square rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-700">
                    <img
                      :src="preview.url"
                      :alt="`Imagen ${index + 1}`"
                      class="w-full h-full object-cover"
                    />
                  </div>

                  <!-- Badge de imagen principal -->
                  <div
                    v-if="index === 0"
                    class="absolute top-2 left-2 bg-primary text-white text-xs font-medium px-2 py-1 rounded"
                  >
                    Principal
                  </div>

                  <!-- Botón eliminar -->
                  <button
                    type="button"
                    @click="removeImage(index)"
                    class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>

                  <!-- Número de orden -->
                  <div class="mt-1 text-xs text-center text-gray-500 dark:text-gray-400">
                    Orden: {{ index }}
                  </div>
                </div>
              </div>
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
              :disabled="loading || imagePreviews.length === 0"
            >
              {{ loading ? 'Creando...' : 'Crear Galería' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useGalerias } from '@/composables/useGalerias'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import AppLayout from '@/components/layout/AppLayout.vue'

const { createGaleria, loading, goToIndex } = useGalerias()

const form = ref({
  nombre: '',
})

const fileInput = ref(null)
const isDragging = ref(false)
const fileError = ref('')
const selectedFiles = ref([])
const imagePreviews = ref([])

const triggerFileInput = () => {
  fileInput.value.click()
}

const validateFiles = (files) => {
  const maxSize = 5 * 1024 * 1024 // 5MB
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']

  for (const file of files) {
    if (!allowedTypes.includes(file.type)) {
      return `El archivo "${file.name}" no es un tipo de imagen válido.`
    }
    if (file.size > maxSize) {
      return `El archivo "${file.name}" excede el tamaño máximo de 5MB.`
    }
  }
  return null
}

const processFiles = (files) => {
  fileError.value = ''

  const error = validateFiles(files)
  if (error) {
    fileError.value = error
    return
  }

  // Agregar archivos a la lista
  selectedFiles.value = [...files]

  // Crear previews
  imagePreviews.value = []
  files.forEach((file, index) => {
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreviews.value.push({
        url: e.target.result,
        file: file,
        orden: index
      })
    }
    reader.readAsDataURL(file)
  })
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  if (files.length > 0) {
    processFiles(files)
  }
}

const handleDrop = (event) => {
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files)
  if (files.length > 0) {
    processFiles(files)
  }
}

const removeImage = (index) => {
  imagePreviews.value.splice(index, 1)
  selectedFiles.value.splice(index, 1)

  // Actualizar orden
  imagePreviews.value.forEach((preview, idx) => {
    preview.orden = idx
  })
}

const handleSubmit = async () => {
  if (selectedFiles.value.length === 0) {
    fileError.value = 'Debes seleccionar al menos una imagen'
    return
  }

  try {
    // Crear FormData para enviar archivos
    const formData = new FormData()
    formData.append('nombre', form.value.nombre)

    // Agregar todas las imágenes
    selectedFiles.value.forEach((file) => {
      formData.append('imagenes[]', file)
    })

    await createGaleria(formData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
