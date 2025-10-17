<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCategoriasStore } from '@/stores/categorias'

const emit = defineEmits(['update:filters'])

const categoriasStore = useCategoriasStore()

// Estado local de filtros
const filters = ref({
  search: '',
  categoria_id: null,
  estado: 'activo',
  min_precio: null,
  max_precio: null,
  min_stock: null
})

// Computed
const categoriasOptions = computed(() => categoriasStore.categoriasOptions)

// Lifecycle
onMounted(async () => {
  await categoriasStore.fetchAllCategorias()
})

// Methods
function applyFilters() {
  emit('update:filters', { ...filters.value })
}

function clearFilters() {
  filters.value = {
    search: '',
    categoria_id: null,
    estado: 'activo',
    min_precio: null,
    max_precio: null,
    min_stock: null
  }
  applyFilters()
}

// Watch search input con debounce
let searchTimeout = null
function handleSearchInput() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}
</script>

<template>
  <div class="card">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      <!-- Búsqueda -->
      <div class="xl:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Buscar
        </label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            v-model="filters.search"
            @input="handleSearchInput"
            type="text"
            placeholder="Buscar por nombre o código..."
            class="input-field pl-10"
          />
        </div>
      </div>

      <!-- Categoría -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Categoría
        </label>
        <select
          v-model="filters.categoria_id"
          @change="applyFilters"
          class="input-field"
        >
          <option :value="null">Todas</option>
          <option
            v-for="categoria in categoriasOptions"
            :key="categoria.value"
            :value="categoria.value"
          >
            {{ categoria.label }}
          </option>
        </select>
      </div>

      <!-- Estado -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Estado
        </label>
        <select
          v-model="filters.estado"
          @change="applyFilters"
          class="input-field"
        >
          <option value="">Todos</option>
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
      </div>

      <!-- Precio Mínimo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Precio Mín.
        </label>
        <input
          v-model.number="filters.min_precio"
          @change="applyFilters"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
          class="input-field"
        />
      </div>

      <!-- Precio Máximo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Precio Máx.
        </label>
        <input
          v-model.number="filters.max_precio"
          @change="applyFilters"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
          class="input-field"
        />
      </div>
    </div>

    <!-- Botones de acción -->
    <div class="mt-4 flex gap-3">
      <button
        @click="applyFilters"
        class="btn-primary"
      >
        Aplicar Filtros
      </button>
      <button
        @click="clearFilters"
        class="btn-secondary"
      >
        Limpiar Filtros
      </button>
    </div>
  </div>
</template>
