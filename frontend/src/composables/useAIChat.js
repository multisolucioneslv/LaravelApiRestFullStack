import { ref, computed } from 'vue'
import axios from '@/lib/axios'

const conversations = ref([])
const currentConversation = ref(null)
const messages = ref([])
const isLoading = ref(false)
const isSending = ref(false)

export function useAIChat() {
  /**
   * Obtener todas las conversaciones
   */
  const fetchConversations = async () => {
    try {
      isLoading.value = true
      const response = await axios.get('/ai-chat/conversations')

      if (response.data.success) {
        conversations.value = response.data.conversations
      }
    } catch (error) {
      console.error('Error al obtener conversaciones:', error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Crear una nueva conversaci�n
   */
  const createConversation = async (title = null) => {
    try {
      const response = await axios.post('/ai-chat/conversations', { title })

      if (response.data.success) {
        const newConversation = response.data.conversation
        conversations.value.unshift(newConversation)
        return newConversation
      }
    } catch (error) {
      console.error('Error al crear conversaci�n:', error)
      throw error
    }
  }

  /**
   * Abrir una conversaci�n existente
   */
  const openConversation = async (conversationId) => {
    try {
      isLoading.value = true
      const response = await axios.get(`/ai-chat/conversations/${conversationId}`)

      if (response.data.success) {
        currentConversation.value = response.data.conversation
        messages.value = response.data.conversation.messages || []
      }
    } catch (error) {
      console.error('Error al abrir conversaci�n:', error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Enviar mensaje y obtener respuesta de IA
   */
  const sendMessage = async (conversationId, message) => {
    if (!message.trim()) return

    try {
      isSending.value = true

      // Agregar mensaje del usuario inmediatamente a la UI
      const tempUserMessage = {
        id: Date.now(),
        role: 'user',
        content: message,
        created_at: new Date().toISOString(),
        temp: true,
      }
      messages.value.push(tempUserMessage)

      // Agregar mensaje "escribiendo..." mientras espera respuesta
      const tempAssistantMessage = {
        id: Date.now() + 1,
        role: 'assistant',
        content: 'Escribiendo...',
        created_at: new Date().toISOString(),
        temp: true,
        loading: true,
      }
      messages.value.push(tempAssistantMessage)

      const response = await axios.post(
        `/ai-chat/conversations/${conversationId}/messages`,
        { message }
      )

      if (response.data.success) {
        // Eliminar mensajes temporales
        messages.value = messages.value.filter(msg => !msg.temp)

        // Agregar mensajes reales
        messages.value.push(response.data.data.user_message)
        messages.value.push(response.data.data.assistant_message)

        // Actualizar la conversaci�n actual
        if (currentConversation.value) {
          currentConversation.value.title =
            response.data.data.assistant_message.content.substring(0, 50) + '...'
        }

        // Recargar lista de conversaciones para actualizar el �ltimo mensaje
        await fetchConversations()
      }
    } catch (error) {
      console.error('Error al enviar mensaje:', error)
      // Eliminar mensajes temporales en caso de error
      messages.value = messages.value.filter(msg => !msg.temp)
      throw error
    } finally {
      isSending.value = false
    }
  }

  /**
   * Eliminar una conversaci�n
   */
  const deleteConversation = async (conversationId) => {
    try {
      const response = await axios.delete(`/ai-chat/conversations/${conversationId}`)

      if (response.data.success) {
        // Eliminar de la lista
        conversations.value = conversations.value.filter(c => c.id !== conversationId)

        // Si era la conversaci�n actual, cerrarla
        if (currentConversation.value?.id === conversationId) {
          closeConversation()
        }
      }
    } catch (error) {
      console.error('Error al eliminar conversaci�n:', error)
      throw error
    }
  }

  /**
   * Cerrar conversaci�n actual
   */
  const closeConversation = () => {
    currentConversation.value = null
    messages.value = []
  }

  /**
   * Crear nueva conversaci�n y abrirla
   */
  const startNewConversation = async () => {
    try {
      const newConversation = await createConversation()
      await openConversation(newConversation.id)
      return newConversation
    } catch (error) {
      console.error('Error al iniciar nueva conversaci�n:', error)
      throw error
    }
  }

  return {
    // Estado
    conversations,
    currentConversation,
    messages,
    isLoading,
    isSending,

    // M�todos
    fetchConversations,
    createConversation,
    openConversation,
    sendMessage,
    deleteConversation,
    closeConversation,
    startNewConversation,
  }
}
