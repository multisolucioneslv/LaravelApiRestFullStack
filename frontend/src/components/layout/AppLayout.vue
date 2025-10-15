<template>
  <div class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Navbar -->
    <Navbar />

    <!-- Contenedor principal -->
    <div class="flex flex-1">
      <!-- Sidebar -->
      <Sidebar />

      <!-- Contenido principal -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import Navbar from './Navbar.vue'
import Sidebar from './Sidebar.vue'
</script>
