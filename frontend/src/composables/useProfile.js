import { ref, computed } from 'vue'
import api, { apiService } from '@/services/api'
import { useAlert } from './useAlert'
import { useAuthStore } from '@/stores/auth'

export function useProfile() {
  const authStore = useAuthStore()
  const alert = useAlert()

  const profile = ref(null)
  const loading = ref(false)
  const updating = ref(false)

  /**
   * Obtener datos del perfil del usuario autenticado
   */
  const fetchProfile = async () => {
    loading.value = true
    try {
      const response = await apiService.me()
      profile.value = response.data.user
      return profile.value
    } catch (error) {
      console.error('Error al obtener perfil:', error)
      alert.error('Error', 'No se pudo cargar el perfil del usuario')
      throw error
    } finally {
      loading.value = false
    }
  }

  /**
   * Actualizar perfil del usuario
   */
  const updateProfile = async (formData) => {
    updating.value = true
    try {
      const userId = authStore.user.id

      // Crear FormData para enviar archivos
      const data = new FormData()

      // Agregar campos de texto
      Object.keys(formData).forEach(key => {
        if (formData[key] !== null && formData[key] !== undefined) {
          // Si es el campo avatar y es un File, agregarlo
          if (key === 'avatar' && formData[key] instanceof File) {
            data.append('avatar', formData[key])
          }
          // Si es el campo activo (boolean), convertir a 1 o 0 para Laravel
          else if (key === 'activo') {
            data.append('activo', formData[key] ? '1' : '0')
          }
          // Si no es avatar, agregarlo como string
          else if (key !== 'avatar') {
            data.append(key, formData[key])
          }
        }
      })

      const response = await api.post(
        `/users/${userId}`,
        data,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
          params: {
            _method: 'PUT' // Laravel method spoofing
          }
        }
      )

      profile.value = response.data.data

      // Actualizar el usuario en el authStore
      await authStore.fetchUser()

      alert.success('Éxito', 'Perfil actualizado correctamente')
      return response.data
    } catch (error) {
      console.error('Error al actualizar perfil:', error)
      const message = error.response?.data?.message || 'No se pudo actualizar el perfil'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Cambiar contraseña del usuario
   */
  const changePassword = async (passwordData) => {
    updating.value = true
    try {
      const userId = authStore.user.id

      await api.put(`/users/${userId}/password`, {
        current_password: passwordData.currentPassword,
        password: passwordData.newPassword,
        password_confirmation: passwordData.confirmPassword
      })

      alert.success('Éxito', 'Contraseña cambiada correctamente')
    } catch (error) {
      console.error('Error al cambiar contraseña:', error)
      const message = error.response?.data?.message || 'No se pudo cambiar la contraseña'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  /**
   * Eliminar avatar del usuario
   */
  const deleteAvatar = async () => {
    const result = await alert.confirm(
      '¿Eliminar avatar?',
      '¿Estás seguro de que deseas eliminar tu foto de perfil?',
      'Sí, eliminar'
    )

    if (!result.isConfirmed) return

    updating.value = true
    try {
      const userId = authStore.user.id

      await api.delete(`/users/${userId}/avatar`)

      // Actualizar el usuario en el authStore
      await authStore.fetchUser()

      alert.success('Éxito', 'Avatar eliminado correctamente')
    } catch (error) {
      console.error('Error al eliminar avatar:', error)
      const message = error.response?.data?.message || 'No se pudo eliminar el avatar'
      alert.error('Error', message)
      throw error
    } finally {
      updating.value = false
    }
  }

  return {
    profile,
    loading,
    updating,
    fetchProfile,
    updateProfile,
    changePassword,
    deleteAvatar
  }
}
