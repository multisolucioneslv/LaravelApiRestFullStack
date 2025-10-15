<template>
  <button
    @click="themeStore.toggle()"
    class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
    :title="themeStore.isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
  >
    <!-- Icono Sol (modo claro) -->
    <SunIcon v-if="themeStore.isDark" class="w-5 h-5 text-yellow-400" />
    <!-- Icono Luna (modo oscuro) -->
    <MoonIcon v-else class="w-5 h-5 text-gray-700" />
  </button>
</template>

<script setup>
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline'
import { useThemeStore } from '@/stores/theme'

const themeStore = useThemeStore()
</script>
