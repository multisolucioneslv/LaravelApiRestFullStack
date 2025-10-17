<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useCategoriasStore } from '@/stores/categorias'

const props = defineProps({
  producto: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'cancel'])

const categoriasStore = useCategoriasStore()

// Estado del formulario
const form = reactive({
  nombre: '',
  descripcion: '',
  codigo: '',
  codigo_barras: '',
  precio_compra: '',
  precio_venta: '',
  precio_mayoreo: '',
  stock_minimo: '',
  stock_actual: '',
  unidad_medida: 'unidad',
  categorias: [],
  imagen: null,
  estado: 'activo'
})

// Preview de imagen
const imagenPreview = ref(null)

// Errores de validación
const errors = reactive({})

// Estado de carga de imagen
const uploadingImage = ref(false)

// Computed
const categoriasOptions = computed(() => categoriasStore.categoriasOptions)

const isEditMode = computed(() => !!props.producto)

const formTitle = computed(() => isEditMode.value ? 'Editar Producto' : 'Nuevo Producto')

// Unidades de medida disponibles
const unidadesMedida = [
  { value: 'unidad', label: 'Unidad' },
  { value: 'kg', label: 'Kilogramo' },
  { value: 'lb', label: 'Libra' },
  { value: 'gr', label: 'Gramo' },
  { value: 'litro', label: 'Litro' },
  { value: 'ml', label: 'Mililitro' },
  { value: 'metro', label: 'Metro' },
  { value: 'cm', label: 'Centímetro' },
  { value: 'caja', label: 'Caja' },
  { value: 'paquete', label: 'Paquete' }
]

// Lifecycle
onMounted(async () => {
  await categoriasStore.fetchAllCategorias()

  if (props.producto) {
    loadProductoData()
  }
})

// Watch producto changes
watch(() => props.producto, (newProducto) => {
  if (newProducto) {
    loadProductoData()
  }
}, { deep: true })

// Methods
function loadProductoData() {
  if (!props.producto) return

  form.nombre = props.producto.nombre || ''
  form.descripcion = props.producto.descripcion || ''
  form.codigo = props.producto.codigo || ''
  form.codigo_barras = props.producto.codigo_barras || ''
  form.precio_compra = props.producto.precio_compra || ''
  form.precio_venta = props.producto.precio_venta || ''
  form.precio_mayoreo = props.producto.precio_mayoreo || ''
  form.stock_minimo = props.producto.stock_minimo || ''
  form.stock_actual = props.producto.stock_actual || ''
  form.unidad_medida = props.producto.unidad_medida || 'unidad'
  form.estado = props.producto.estado || 'activo'

  // Cargar categorías
  if (props.producto.categorias && Array.isArray(props.producto.categorias)) {
    form.categorias = props.producto.categorias.map(c => c.id)
  }

  // Cargar imagen preview
  if (props.producto.imagen) {
    imagenPreview.value = props.producto.imagen
  }
}

function handleImageUpload(event) {
  const file = event.target.files[0]

  if (!file) return

  // Validar tipo de archivo
  if (!file.type.startsWith('image/')) {
    errors.imagen = 'El archivo debe ser una imagen'
    return
  }

  // Validar tamaño (máximo 5MB)
  if (file.size > 5 * 1024 * 1024) {
    errors.imagen = 'La imagen no debe superar los 5MB'
    return
  }

  form.imagen = file
  errors.imagen = null

  // Crear preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagenPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

function removeImage() {
  form.imagen = null
  imagenPreview.value = null
  errors.imagen = null
}

function toggleCategoria(categoriaId) {
  const index = form.categorias.indexOf(categoriaId)
  if (index > -1) {
    form.categorias.splice(index, 1)
  } else {
    form.categorias.push(categoriaId)
  }
}

function validateForm() {
  const newErrors = {}

  // Nombre requerido
  if (!form.nombre.trim()) {
    newErrors.nombre = 'El nombre es requerido'
  } else if (form.nombre.length < 3) {
    newErrors.nombre = 'El nombre debe tener al menos 3 caracteres'
  }

  // Código requerido
  if (!form.codigo.trim()) {
    newErrors.codigo = 'El código/SKU es requerido'
  }

  // Validar precios
  if (form.precio_venta && parseFloat(form.precio_venta) <= 0) {
    newErrors.precio_venta = 'El precio de venta debe ser mayor a 0'
  }

  if (form.precio_compra && form.precio_venta) {
    if (parseFloat(form.precio_compra) >= parseFloat(form.precio_venta)) {
      newErrors.precio_venta = 'El precio de venta debe ser mayor al precio de compra'
    }
  }

  // Validar stocks
  if (form.stock_actual && parseInt(form.stock_actual) < 0) {
    newErrors.stock_actual = 'El stock no puede ser negativo'
  }

  if (form.stock_minimo && parseInt(form.stock_minimo) < 0) {
    newErrors.stock_minimo = 'El stock mínimo no puede ser negativo'
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

  // Preparar datos para enviar
  const formData = new FormData()

  formData.append('nombre', form.nombre)
  formData.append('codigo', form.codigo)
  formData.append('estado', form.estado)
  formData.append('unidad_medida', form.unidad_medida)

  if (form.descripcion) formData.append('descripcion', form.descripcion)
  if (form.codigo_barras) formData.append('codigo_barras', form.codigo_barras)
  if (form.precio_compra) formData.append('precio_compra', form.precio_compra)
  if (form.precio_venta) formData.append('precio_venta', form.precio_venta)
  if (form.precio_mayoreo) formData.append('precio_mayoreo', form.precio_mayoreo)
  if (form.stock_minimo) formData.append('stock_minimo', form.stock_minimo)
  if (form.stock_actual) formData.append('stock_actual', form.stock_actual)

  // Agregar categorías
  form.categorias.forEach(catId => {
    formData.append('categorias[]', catId)
  })

  // Agregar imagen si existe
  if (form.imagen && form.imagen instanceof File) {
    formData.append('imagen', form.imagen)
  }

  emit('submit', formData)
}

function handleCancel() {
  emit('cancel')
}
</script>

<template>
  <div class="producto-form">
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          {{ formTitle }}
        </h3>
      </div>

      <!-- Información Básica -->
      <div class="card">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Información Básica
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Nombre -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Nombre del Producto <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nombre"
              type="text"
              :class="[
                'input-field',
                errors.nombre ? 'border-red-500 dark:border-red-500' : ''
              ]"
              placeholder="Ingrese el nombre del producto"
            />
            <p v-if="errors.nombre" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.nombre }}
            </p>
          </div>

          <!-- Descripción -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Descripción
            </label>
            <textarea
              v-model="form.descripcion"
              rows="3"
              class="input-field"
              placeholder="Descripción del producto (opcional)"
            ></textarea>
          </div>

          <!-- Código/SKU -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Código/SKU <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.codigo"
              type="text"
              :class="[
                'input-field',
                errors.codigo ? 'border-red-500 dark:border-red-500' : ''
              ]"
              placeholder="Ej: PROD-001"
            />
            <p v-if="errors.codigo" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.codigo }}
            </p>
          </div>

          <!-- Código de Barras -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Código de Barras
            </label>
            <input
              v-model="form.codigo_barras"
              type="text"
              class="input-field"
              placeholder="Código de barras (opcional)"
            />
          </div>

          <!-- Unidad de Medida -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Unidad de Medida
            </label>
            <select v-model="form.unidad_medida" class="input-field">
              <option
                v-for="unidad in unidadesMedida"
                :key="unidad.value"
                :value="unidad.value"
              >
                {{ unidad.label }}
              </option>
            </select>
          </div>

          <!-- Estado -->
          <div class="flex items-center gap-3 md:col-span-1">
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

      <!-- Precios -->
      <div class="card">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Precios
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Precio Compra -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Precio de Compra
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 dark:text-gray-400">
                $
              </span>
              <input
                v-model="form.precio_compra"
                type="number"
                step="0.01"
                min="0"
                class="input-field pl-7"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Precio Venta -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Precio de Venta <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 dark:text-gray-400">
                $
              </span>
              <input
                v-model="form.precio_venta"
                type="number"
                step="0.01"
                min="0"
                :class="[
                  'input-field pl-7',
                  errors.precio_venta ? 'border-red-500 dark:border-red-500' : ''
                ]"
                placeholder="0.00"
              />
            </div>
            <p v-if="errors.precio_venta" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.precio_venta }}
            </p>
          </div>

          <!-- Precio Mayoreo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Precio de Mayoreo
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 dark:text-gray-400">
                $
              </span>
              <input
                v-model="form.precio_mayoreo"
                type="number"
                step="0.01"
                min="0"
                class="input-field pl-7"
                placeholder="0.00"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Stock -->
      <div class="card">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Inventario
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Stock Mínimo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Stock Mínimo
            </label>
            <input
              v-model="form.stock_minimo"
              type="number"
              min="0"
              :class="[
                'input-field',
                errors.stock_minimo ? 'border-red-500 dark:border-red-500' : ''
              ]"
              placeholder="0"
            />
            <p v-if="errors.stock_minimo" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.stock_minimo }}
            </p>
          </div>

          <!-- Stock Actual -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Stock Actual
            </label>
            <input
              v-model="form.stock_actual"
              type="number"
              min="0"
              :class="[
                'input-field',
                errors.stock_actual ? 'border-red-500 dark:border-red-500' : ''
              ]"
              placeholder="0"
            />
            <p v-if="errors.stock_actual" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.stock_actual }}
            </p>
          </div>
        </div>
      </div>

      <!-- Categorías -->
      <div class="card">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Categorías
        </h4>

        <div v-if="categoriasOptions.length > 0" class="flex flex-wrap gap-2">
          <button
            v-for="categoria in categoriasOptions"
            :key="categoria.value"
            type="button"
            @click="toggleCategoria(categoria.value)"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
              form.categorias.includes(categoria.value)
                ? 'bg-primary-600 text-white hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            {{ categoria.label }}
          </button>
        </div>

        <p v-else class="text-sm text-gray-500 dark:text-gray-400">
          No hay categorías disponibles.
          <router-link to="/categorias" class="text-primary-600 hover:text-primary-700">
            Crear categoría
          </router-link>
        </p>
      </div>

      <!-- Imagen -->
      <div class="card">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Imagen del Producto
        </h4>

        <div class="space-y-4">
          <!-- Preview de imagen -->
          <div v-if="imagenPreview" class="relative inline-block">
            <img
              :src="imagenPreview"
              alt="Preview"
              class="w-48 h-48 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-700"
            />
            <button
              type="button"
              @click="removeImage"
              class="absolute -top-2 -right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Input de archivo -->
          <div>
            <label class="block">
              <span class="btn-secondary cursor-pointer inline-block">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Seleccionar Imagen
              </span>
              <input
                type="file"
                accept="image/*"
                @change="handleImageUpload"
                class="hidden"
              />
            </label>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
              Formatos: JPG, PNG, GIF. Máximo 5MB.
            </p>
            <p v-if="errors.imagen" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.imagen }}
            </p>
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
            {{ isEditMode ? 'Actualizar Producto' : 'Crear Producto' }}
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
