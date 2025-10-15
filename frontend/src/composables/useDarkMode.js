import { useDark, useToggle } from '@vueuse/core'

/**
 * Composable para manejar el Dark Mode de la aplicación
 * Usa VueUse para gestionar la persistencia en localStorage
 */
export function useDarkMode() {
  // useDark automáticamente:
  // 1. Lee la preferencia del localStorage (key: 'vueuse-color-scheme')
  // 2. Añade/quita la clase 'dark' al elemento <html>
  // 3. Persiste los cambios automáticamente
  const isDark = useDark({
    selector: 'html',
    attribute: 'class',
    valueDark: 'dark',
    valueLight: '',
  })

  // useToggle crea una función que alterna el valor booleano
  const toggleDark = useToggle(isDark)

  return {
    isDark,
    toggleDark,
  }
}
