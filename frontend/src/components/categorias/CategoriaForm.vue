<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'

const props = defineProps({
  categoria: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'cancel'])

// Estado del formulario
const form = reactive({
  nombre: '',
  descripcion: '',
  icono: '',
  color: '#3b82f6',
  estado: 'activo'
})

// Errores de validación
const errors = reactive({})

// Iconos disponibles (iconos comunes para categorías)
const iconosDisponibles = [
  { value: 'tag', label: 'Etiqueta', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
  { value: 'cube', label: 'Cubo', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
  { value: 'shopping-bag', label: 'Bolsa', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
  { value: 'desktop', label: 'Computadora', icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
  { value: 'device-mobile', label: 'Móvil', icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z' },
  { value: 'book', label: 'Libro', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
  { value: 'home', label: 'Casa', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { value: 'gift', label: 'Regalo', icon: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7' },
  { value: 'sparkles', label: 'Estrellas', icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z' },
  { value: 'lightning', label: 'Rayo', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
  { value: 'heart', label: 'Corazón', icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' },
  { value: 'star', label: 'Estrella', icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' }
]

// Colores predefinidos
const coloresPredefinidos = [
  '#3b82f6', // blue
  '#ef4444', // red
  '#10b981', // green
  '#f59e0b', // yellow
  '#8b5cf6', // purple
  '#ec4899', // pink
  '#06b6d4', // cyan
  '#84cc16', // lime
  '#f97316', // orange
  '#6366f1', // indigo
  '#14b8a6', // teal
  '#a855f7'  // violet
]

// Computed
const isEditMode = computed(() => !!props.categoria)

const formTitle = computed(() => isEditMode.value ? 'Editar Categoría' : 'Nueva Categoría')

const iconoSeleccionado = computed(() => {
  return iconosDisponibles.find(i => i.value === form.icono)
})

// Lifecycle
onMounted(() => {
  if (props.categoria) {
    loadCategoriaData()
  }
})

// Watch categoria changes
watch(() => props.categoria, (newCategoria) => {
  if (newCategoria) {
    loadCategoriaData()
  }
}, { deep: true })

// Methods
function loadCategoriaData() {
  if (!props.categoria) return

  form.nombre = props.categoria.nombre || ''
  form.descripcion = props.categoria.descripcion || ''
  form.icono = props.categoria.icono || ''
  form.color = props.categoria.color || '#3b82f6'
  form.estado = props.categoria.estado || 'activo'
}

function selectIcono(icono) {
  form.icono = icono.value
}

function selectColor(color) {
  form.color = color
}

function validateForm() {
  const newErrors = {}

  // Nombre requerido
  if (!form.nombre.trim()) {
    newErrors.nombre = 'El nombre es requerido'
  } else if (form.nombre.length < 3) {
    newErrors.nombre = 'El nombre debe tener al menos 3 caracteres'
  }

  Object.assign(errors, newErrors)
  return Object.keys(newErrors).length === 0
}

function handleSubmit() {
  // Limpiar errores previos
  Object.keys(errors).forEach(key => delete errors[key])

  // Validar
  if (!validateForm()) {
    return
  }

  // Emitir datos
  emit('submit', { ...form })
}

function handleCancel() {
  emit('cancel')
}
</script>

<template>
  <div class="categoria-form">
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          {{ formTitle }}
        </h3>
      </div>

      <!-- Información Básica -->
      <div class="card">
        <div class="space-y-4">
          <!-- Nombre -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Nombre de la Categoría <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nombre"
              type="text"
              :class="[
                'input-field',
                errors.nombre ? 'border-red-500 dark:border-red-500' : ''
              ]"
              placeholder="Ingrese el nombre de la categoría"
            />
            <p v-if="errors.nombre" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.nombre }}
            </p>
          </div>

          <!-- Descripción -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Descripción
            </label>
            <textarea
              v-model="form.descripcion"
              rows="3"
              class="input-field"
              placeholder="Descripción de la categoría (opcional)"
            ></textarea>
          </div>

          <!-- Estado -->
          <div class="flex items-center gap-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Estado
            </label>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="form.estado"
                type="checkbox"
                true-value="activo"
                false-value="inactivo"
                class="sr-only peer"
              />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
              <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                {{ form.estado === 'activo' ? 'Activo' : 'Inactivo' }}
              </span>
            </label>
          </div>
        </div>
      </div>

      <!-- Icono -->
      <div class="card">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Icono
        </h4>

        <!-- Preview del icono seleccionado -->
        <div v-if="iconoSeleccionado" class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg flex items-center gap-3">
          <div :style="{ backgroundColor: form.color }" class="w-12 h-12 rounded-lg flex items-center justify-center">
            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconoSeleccionado.icon" />
            </svg>
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">{{ iconoSeleccionado.label }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Vista previa del icono</p>
          </div>
        </div>

        <!-- Selector de iconos -->
        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
          <button
            v-for="icono in iconosDisponibles"
            :key="icono.value"
            type="button"
            @click="selectIcono(icono)"
            :class="[
              'p-3 rounded-lg border-2 transition-all hover:scale-110',
              form.icono === icono.value
                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
            ]"
            :title="icono.label"
          >
            <svg class="w-6 h-6 mx-auto text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icono.icon" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Color -->
      <div class="card">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Color
        </h4>

        <!-- Color picker HTML5 -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Seleccionar color personalizado
          </label>
          <div class="flex items-center gap-3">
            <input
              v-model="form.color"
              type="color"
              class="h-10 w-20 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer"
            />
            <input
              v-model="form.color"
              type="text"
              class="input-field flex-1"
              placeholder="#3b82f6"
            />
          </div>
        </div>

        <!-- Colores predefinidos -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Colores predefinidos
          </label>
          <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-12 gap-2">
            <button
              v-for="color in coloresPredefinidos"
              :key="color"
              type="button"
              @click="selectColor(color)"
              :style="{ backgroundColor: color }"
              :class="[
                'w-10 h-10 rounded-lg border-2 transition-all hover:scale-110',
                form.color === color
                  ? 'border-gray-900 dark:border-white ring-2 ring-offset-2 ring-gray-400'
                  : 'border-transparent'
              ]"
              :title="color"
            >
            </button>
          </div>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="flex gap-3 justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
        <button
          type="button"
          @click="handleCancel"
          :disabled="loading"
          class="btn-secondary"
        >
          Cancelar
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="btn-primary relative"
        >
          <span v-if="!loading">
            {{ isEditMode ? 'Actualizar Categoría' : 'Crear Categoría' }}
          </span>
          <span v-else class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Guardando...
          </span>
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
