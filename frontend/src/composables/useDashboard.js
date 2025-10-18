import { ref, onMounted, onUnmounted } from 'vue'
import api from '@/services/api'

/**
 * Composable para gestionar estadísticas del dashboard
 *
 * Características:
 * - Carga estadísticas desde el backend
 * - Auto-refresco cada 5 minutos
 * - Manejo de estados: loading, error, data
 * - Limpieza automática de intervalos
 *
 * @returns {Object} statistics, loading, error, fetchStatistics
 */
export function useDashboard() {
  const statistics = ref(null)
  const loading = ref(false)
  const error = ref(null)

  /**
   * Obtiene estadísticas del dashboard desde el backend
   */
  const fetchStatistics = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/dashboard/statistics')
      statistics.value = response.data.data

      console.log('📊 [DASHBOARD] Estadísticas cargadas:', statistics.value)
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar estadísticas del dashboard'
      console.error('❌ [DASHBOARD] Error al cargar estadísticas:', err)
    } finally {
      loading.value = false
    }
  }

  // Auto-refrescar cada 5 minutos (300000 ms)
  let intervalId = null

  onMounted(() => {
    console.log('🚀 [DASHBOARD] Inicializando dashboard...')
    fetchStatistics()

    // Configurar auto-refresco
    intervalId = setInterval(() => {
      console.log('🔄 [DASHBOARD] Auto-refrescando estadísticas...')
      fetchStatistics()
    }, 300000) // 5 minutos
  })

  onUnmounted(() => {
    // Limpiar interval cuando se desmonte el componente
    if (intervalId) {
      clearInterval(intervalId)
      console.log('🧹 [DASHBOARD] Interval de auto-refresco detenido')
    }
  })

  return {
    statistics,
    loading,
    error,
    fetchStatistics,
  }
}
