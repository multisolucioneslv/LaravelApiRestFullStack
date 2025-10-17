<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Encabezado -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Crear Producto
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Completa el formulario para agregar un nuevo producto al catálogo
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
      <ProductoForm
        :producto="null"
        :loading="loading"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { useProductos } from '@/composables/useProductos'
import { Button } from '@/components/ui/button'
import AppLayout from '@/components/layout/AppLayout.vue'
import ProductoForm from '@/components/productos/ProductoForm.vue'

const { createProducto, loading, goToIndex } = useProductos()

const handleSubmit = async (formData) => {
  try {
    await createProducto(formData)
    goToIndex()
  } catch (err) {
    // Los errores se manejan en el composable con SweetAlert2
  }
}

const handleCancel = () => {
  goToIndex()
}
</script>
