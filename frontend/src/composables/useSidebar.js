import { ref, onMounted } from 'vue'

/**
 * Composable para manejar el estado del Sidebar (colapsado/expandido)
 * Persiste el estado en localStorage
 */
export function useSidebar() {
  const isCollapsed = ref(false)
  const STORAGE_KEY = 'sidebar_collapsed'

  /**
   * Cargar estado desde localStorage
   */
  const loadState = () => {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved !== null) {
      isCollapsed.value = JSON.parse(saved)
    }
  }

  /**
   * Guardar estado en localStorage
   */
  const saveState = () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(isCollapsed.value))
  }

  /**
   * Alternar estado colapsado/expandido
   */
  const toggle = () => {
    isCollapsed.value = !isCollapsed.value
    saveState()
  }

  /**
   * Colapsar sidebar
   */
  const collapse = () => {
    isCollapsed.value = true
    saveState()
  }

  /**
   * Expandir sidebar
   */
  const expand = () => {
    isCollapsed.value = false
    saveState()
  }

  // Cargar estado al crear el composable
  onMounted(() => {
    loadState()
  })

  return {
    isCollapsed,
    toggle,
    collapse,
    expand,
  }
}
