<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Categoría
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica la información de la categoría
          </p>
        </div>
        <Button variant="outline" @click="handleCancel">
          Volver
        </Button>
      </div>

      <!-- Loading -->
      <div v-if="loadingCategoria" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <form v-else-if="categoria" @submit.prevent="handleSubmit" class="card">
        <div class="space-y-6">
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
              placeholder="Ej: Electrónica"
            />
          </div>

          <!-- Descripción -->
          <div>
            <label for="descripcion" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
              Descripción
            </label>
            <textarea
              id="descripcion"
              v-model="form.descripcion"
              rows="3"
              placeholder="Descripción de la categoría..."
              class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
            ></textarea>
          </div>

          <!-- Icono y Color -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Icono (Emoji) -->
            <div>
              <label for="icono" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                Icono (Emoji)
              </label>
              <Input
                id="icono"
                v-model="form.icono"
                type="text"
                maxlength="2"
                placeholder="📦"
              />
              <p class="text-xs text-gray-500 mt-1">Usa un emoji para representar la categoría</p>
            </div>

            <!-- Color -->
            <div>
              <label for="color" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                Color
              </label>
              <div class="flex gap-2">
                <Input
                  id="color"
                  v-model="form.color"
                  type="color"
                  class="w-20 h-10 cursor-pointer"
                />
                <Input
                  v-model="form.color"
                  type="text"
                  placeholder="#000000"
                  class="flex-1"
                />
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Vista Previa:</p>
            <div class="flex items-center gap-3">
              <div
                class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl font-bold shadow-md"
                :style="{ backgroundColor: form.color || '#6b7280' }"
              >
                {{ form.icono || '📦' }}
              </div>
              <span class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ form.nombre || 'Nombre de categoría' }}
              </span>
            </div>
          </div>

          <!-- Activo -->
          <div class="flex items-center space-x-3">
            <Checkbox
              :checked="form.activo"
              @update:checked="form.activo = $event"
            />
            <label class="text-sm font-medium text-gray-900 dark:text-white">
              Categoría activa
            </label>
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
              {{ loading ? 'Actualizando...' : 'Actualizar Categoría' }}
            </Button>
          </div>
        </div>
      </form>

      <!-- Error -->
      <div v-else class="text-center text-red-600 dark:text-red-400">
        No se pudo cargar la categoría
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCategorias } from '@/composables/useCategorias'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchCategoria, updateCategoria, loading, goToIndex } = useCategorias()

const categoria = ref(null)
const loadingCategoria = ref(true)

const form = ref({
  nombre: '',
  descripcion: '',
  icono: '📦',
  color: '#3b82f6',
  activo: true,
})

onMounted(async () => {
  try {
    categoria.value = await fetchCategoria(route.params.id)
    // Cargar datos al formulario
    form.value = {
      nombre: categoria.value.nombre || '',
      descripcion: categoria.value.descripcion || '',
      icono: categoria.value.icono || '📦',
      color: categoria.value.color || '#3b82f6',
      activo: categoria.value.activo !== undefined ? categoria.value.activo : true,
    }
  } catch (err) {
    // Error ya manejado en el composable
  } finally {
    loadingCategoria.value = false
  }
})

const handleSubmit = async () => {
  try {
    await updateCategoria(route.params.id, form.value)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
