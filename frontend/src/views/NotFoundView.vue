<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
    <div class="text-center">
      <!-- Número 404 -->
      <h1 class="text-9xl font-bold text-primary-600 dark:text-primary-400">
        404
      </h1>

      <!-- Mensaje -->
      <h2 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
        Página no encontrada
      </h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        Lo sentimos, la página que buscas no existe.
      </p>

      <!-- Botones -->
      <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
        <router-link
          to="/dashboard"
          class="btn-primary"
        >
          Ir al Dashboard
        </router-link>
        <button
          @click="$router.back()"
          class="btn-secondary"
        >
          Volver Atrás
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Componente simple sin lógica adicional
</script>
