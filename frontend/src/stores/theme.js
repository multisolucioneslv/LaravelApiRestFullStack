import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useDarkMode } from '@/composables/useDarkMode'

/**
 * Store de Pinia para gestionar el tema (Dark Mode)
 * Usa el composable useDarkMode internamente
 */
export const useThemeStore = defineStore('theme', () => {
  // Estado usando el composable de VueUse
  const { isDark, toggleDark } = useDarkMode()

  // Computed para obtener el nombre del tema actual
  const currentTheme = computed(() => isDark.value ? 'dark' : 'light')

  // Acción para cambiar el tema
  const toggle = () => {
    toggleDark()
  }

  // Acción para establecer un tema específico
  const setTheme = (theme) => {
    if (theme === 'dark' && !isDark.value) {
      toggleDark()
    } else if (theme === 'light' && isDark.value) {
      toggleDark()
    }
  }

  return {
    isDark,
    currentTheme,
    toggle,
    setTheme,
  }
})
