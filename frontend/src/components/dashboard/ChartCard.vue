<template>
  <div class="card">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ title }}
      </h3>
      <slot name="actions"></slot>
    </div>

    <!-- Contenido -->
    <div>
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  /**
   * Título del card
   */
  title: {
    type: String,
    required: true,
  },
})
</script>
