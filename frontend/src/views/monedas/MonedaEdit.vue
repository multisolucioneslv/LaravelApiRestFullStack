<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Moneda
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica los datos de la moneda
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

      <!-- Loading inicial -->
      <div v-if="loadingCurrency" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <form v-else @submit.prevent="handleSubmit" class="card">
        <div class="space-y-8">
          <!-- Sección: Información de la Moneda -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
              Información de la Moneda
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Código -->
              <div>
                <label for="codigo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Código *
                </label>
                <Input
                  id="codigo"
                  v-model="form.codigo"
                  type="text"
                  required
                  placeholder="Ej: USD, MXN, EUR"
                  maxlength="10"
                  @input="form.codigo = form.codigo.toUpperCase()"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Código único de la moneda (máx. 10 caracteres)
                </p>
              </div>

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
                  placeholder="Ej: Dólar Estadounidense"
                  maxlength="100"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Nombre completo de la moneda
                </p>
              </div>

              <!-- Símbolo -->
              <div>
                <label for="simbolo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Símbolo *
                </label>
                <Input
                  id="simbolo"
                  v-model="form.simbolo"
                  type="text"
                  required
                  placeholder="Ej: $, €, £"
                  maxlength="10"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Símbolo que representa la moneda
                </p>
              </div>

              <!-- Tasa de Cambio -->
              <div>
                <label for="tasa_cambio" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                  Tasa de Cambio *
                </label>
                <Input
                  id="tasa_cambio"
                  v-model="form.tasa_cambio"
                  type="number"
                  step="0.0001"
                  min="0"
                  required
                  placeholder="1.0000"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Tasa de cambio con respecto a la moneda base (4 decimales)
                </p>
              </div>
            </div>
          </div>

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
                Moneda activa
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
              {{ loading ? 'Actualizando...' : 'Actualizar Moneda' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCurrencies } from '@/composables/useCurrencies'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()
const { fetchCurrency, updateCurrency, loading, goToIndex } = useCurrencies()

const loadingCurrency = ref(true)

const form = ref({
  codigo: '',
  nombre: '',
  simbolo: '',
  tasa_cambio: '1.0000',
  activo: true,
})

onMounted(async () => {
  try {
    // Cargar datos de la moneda
    const currencyId = route.params.id
    const currency = await fetchCurrency(currencyId)

    // Llenar formulario
    form.value = {
      codigo: currency.codigo,
      nombre: currency.nombre,
      simbolo: currency.simbolo,
      tasa_cambio: currency.tasa_cambio,
      activo: currency.activo,
    }
  } catch (err) {
    // El error se maneja en el composable
    goToIndex()
  } finally {
    loadingCurrency.value = false
  }
})

const handleSubmit = async () => {
  try {
    const currencyId = route.params.id

    // Preparar datos para enviar
    const currencyData = {
      codigo: form.value.codigo,
      nombre: form.value.nombre,
      simbolo: form.value.simbolo,
      tasa_cambio: parseFloat(form.value.tasa_cambio),
      activo: form.value.activo,
    }

    await updateCurrency(currencyId, currencyData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
