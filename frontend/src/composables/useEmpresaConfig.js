import { ref } from 'vue'
import api from '@/services/api'
import { useAlert } from './useAlert'
import { useAuthStore } from '@/stores/auth'

export function useEmpresaConfig() {
  const alert = useAlert()
  const authStore = useAuthStore()

  const empresa = ref(null)
  const loading = ref(false)
  const updating = ref(false)

  /**
   * Obtener la configuración de la empresa del usuario autenticado
   */
  const fetchEmpresaConfig = async () => {
    loading.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Cargando configuración de empresa', 'Por favor espere...')
    }

    try {
      const response = await api.get('/empresa/configuracion')
      empresa.value = response.data.data

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      return empresa.value
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al obtener configuración de empresa:', error)
      const message = error.response?.data?.message || 'No se pudo cargar la configuración de la empresa'
      alert.error('Error', message)
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar la configuración de la empresa
   */
  const updateEmpresaConfig = async (formData) => {
    updating.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Actualizando configuración de empresa', 'Por favor espere...')
    }

    try {
      // Crear FormData para enviar archivos
      const data = new FormData()

      // Agregar campos de texto
      Object.keys(formData).forEach(key => {
        if (formData[key] !== null && formData[key] !== undefined) {
          // Si es un archivo (logo, favicon, fondo_login), agregarlo
          if (['logo', 'favicon', 'fondo_login'].includes(key) && formData[key] instanceof File) {
            data.append(key, formData[key])
          }
          // Si es horarios (array/objeto), convertir a JSON
          else if (key === 'horarios') {
            data.append('horarios', JSON.stringify(formData[key]))
          }
          // Si no es archivo, agregarlo como string
          else if (!['logo', 'favicon', 'fondo_login'].includes(key)) {
            data.append(key, formData[key])
          }
        }
      })

      const response = await api.post('/empresa/configuracion', data, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })

      empresa.value = response.data.data

      // Actualizar el usuario en authStore para reflejar cambios inmediatos
      await authStore.fetchUser()

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Éxito', 'Configuración actualizada correctamente')
      return response.data
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al actualizar configuración:', error)
      const message = error.response?.data?.message || 'No se pudo actualizar la configuración'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Eliminar el logo de la empresa
   */
  const deleteLogo = async () => {
    const result = await alert.confirm(
      '¿Eliminar logo?',
      '¿Estás seguro de que deseas eliminar el logo de la empresa?',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return

    updating.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando logo', 'Por favor espere...')
    }

    try {
      await api.delete('/empresa/configuracion/logo')

      // Actualizar el logo en el objeto empresa
      if (empresa.value) {
        empresa.value.logo = null
      }

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Éxito', 'Logo eliminado correctamente')
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al eliminar logo:', error)
      const message = error.response?.data?.message || 'No se pudo eliminar el logo'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Eliminar el favicon de la empresa
   */
  const deleteFavicon = async () => {
    const result = await alert.confirm(
      '¿Eliminar favicon?',
      '¿Estás seguro de que deseas eliminar el favicon de la empresa?',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return

    updating.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando favicon', 'Por favor espere...')
    }

    try {
      await api.delete('/empresa/configuracion/favicon')

      // Actualizar el favicon en el objeto empresa
      if (empresa.value) {
        empresa.value.favicon = null
      }

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Éxito', 'Favicon eliminado correctamente')
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al eliminar favicon:', error)
      const message = error.response?.data?.message || 'No se pudo eliminar el favicon'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Eliminar el fondo de login de la empresa
   */
  const deleteFondoLogin = async () => {
    const result = await alert.confirm(
      '¿Eliminar fondo de login?',
      '¿Estás seguro de que deseas eliminar el fondo de login de la empresa?',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return

    updating.value = true

    if (authStore.showLoadingEffect) {
      alert.loading('Eliminando fondo de login', 'Por favor espere...')
    }

    try {
      await api.delete('/empresa/configuracion/fondo-login')

      // Actualizar el fondo_login en el objeto empresa
      if (empresa.value) {
        empresa.value.fondo_login = null
      }

      if (authStore.showLoadingEffect) {
        alert.close()
      }

      alert.success('Éxito', 'Fondo de login eliminado correctamente')
    } catch (error) {
      if (authStore.showLoadingEffect) {
        alert.close()
      }

      console.error('Error al eliminar fondo de login:', error)
      const message = error.response?.data?.message || 'No se pudo eliminar el fondo de login'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  return {
    empresa,
    loading,
    updating,
    fetchEmpresaConfig,
    updateEmpresaConfig,
    deleteLogo,
    deleteFavicon,
    deleteFondoLogin
  }
}
