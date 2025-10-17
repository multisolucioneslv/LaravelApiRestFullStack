<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Editar Producto
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Modifica la información del producto
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
      <div v-if="loadingProducto" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Formulario -->
      <ProductoForm
        v-else-if="producto"
        :producto="producto"
        :loading="loading"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />

      <!-- Error -->
      <div v-else class="text-center text-red-600 dark:text-red-400">
        No se pudo cargar el producto
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProductos } from '@/composables/useProductos'
import { Button } from '@/components/ui/button'
import AppLayout from '@/components/layout/AppLayout.vue'
import ProductoForm from '@/components/productos/ProductoForm.vue'

const route = useRoute()
const { fetchProducto, updateProducto, loading, goToIndex } = useProductos()

const producto = ref(null)
const loadingProducto = ref(true)

onMounted(async () => {
  try {
    producto.value = await fetchProducto(route.params.id)
  } catch (err) {
    // Error ya manejado en el composable
  } finally {
    loadingProducto.value = false
  }
})

const handleSubmit = async (formData) => {
  try {
    await updateProducto(route.params.id, formData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
