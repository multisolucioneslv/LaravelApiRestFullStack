import Swal from 'sweetalert2'

/**
 * Composable para manejar alertas y notificaciones con SweetAlert2
 * Incluye soporte para dark mode automático
 */
export function useAlert() {
  /**
   * Configuración base de SweetAlert2 con soporte dark mode
   */
  const getBaseConfig = () => {
    const isDark = document.documentElement.classList.contains('dark')

    return {
      background: isDark ? '#1f2937' : '#ffffff',
      color: isDark ? '#f3f4f6' : '#1f2937',
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#6b7280',
    }
  }

  /**
   * Alerta de éxito
   */
  const success = (title, text = '') => {
    return Swal.fire({
      ...getBaseConfig(),
      icon: 'success',
      title,
      text,
      showConfirmButton: true,
      timer: 3000,
      timerProgressBar: true,
    })
  }

  /**
   * Alerta de error
   */
  const error = (title, text = '') => {
    return Swal.fire({
      ...getBaseConfig(),
      icon: 'error',
      title,
      text,
      showConfirmButton: true,
    })
  }

  /**
   * Alerta de advertencia
   */
  const warning = (title, text = '') => {
    return Swal.fire({
      ...getBaseConfig(),
      icon: 'warning',
      title,
      text,
      showConfirmButton: true,
    })
  }

  /**
   * Alerta de información
   */
  const info = (title, text = '') => {
    return Swal.fire({
      ...getBaseConfig(),
      icon: 'info',
      title,
      text,
      showConfirmButton: true,
    })
  }

  /**
   * Diálogo de confirmación
   */
  const confirm = (title, text = '', confirmButtonText = 'Sí, confirmar') => {
    return Swal.fire({
      ...getBaseConfig(),
      icon: 'question',
      title,
      text,
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText: 'Cancelar',
      reverseButtons: true,
      focusConfirm: false,
      // Configuraciones para evitar bloqueos
      allowOutsideClick: true,
      allowEscapeKey: true,
      buttonsStyling: true,
      backdrop: true, // Mantener backdrop pero sin bloquear
      showClass: {
        popup: '',  // Sin animación de entrada
        backdrop: '' // Sin animación de backdrop
      },
      hideClass: {
        popup: '',  // Sin animación de salida
        backdrop: ''
      },
      // Callback cuando el modal se renderiza completamente
      didRender: (popup) => {
        // Forzar estilos en los botones
        const confirmBtn = popup.querySelector('.swal2-confirm')
        const cancelBtn = popup.querySelector('.swal2-cancel')
        const backdrop = document.querySelector('.swal2-container')

        if (confirmBtn) {
          confirmBtn.style.cursor = 'pointer'
          confirmBtn.style.pointerEvents = 'auto'
          confirmBtn.focus()
        }

        if (cancelBtn) {
          cancelBtn.style.cursor = 'pointer'
          cancelBtn.style.pointerEvents = 'auto'
        }

        // Asegurar que el backdrop no bloquee clicks en los botones
        if (backdrop) {
          backdrop.style.pointerEvents = 'auto'
        }

        // Forzar que el popup esté por encima de todo
        if (popup) {
          popup.style.pointerEvents = 'auto'
          popup.style.zIndex = '10000'
        }
      }
    })
  }

  /**
   * Toast notification (notificación pequeña en esquina)
   */
  const toast = (message, icon = 'success') => {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    })

    return Toast.fire({
      ...getBaseConfig(),
      icon,
      title: message,
    })
  }

  /**
   * Alerta de carga (loading)
   */
  const loading = (title = 'Cargando...', text = 'Por favor espere') => {
    return Swal.fire({
      ...getBaseConfig(),
      title,
      text,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      willOpen: () => {
        Swal.showLoading()
      },
    })
  }

  /**
   * Cerrar alerta activa
   */
  const close = () => {
    Swal.close()
  }

  return {
    success,
    error,
    warning,
    info,
    confirm,
    toast,
    loading,
    close,
  }
}
