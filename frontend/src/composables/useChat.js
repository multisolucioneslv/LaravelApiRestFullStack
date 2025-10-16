import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

export function useChat() {
  const authStore = useAuthStore();

  // Estado reactivo
  const conversations = ref([]);
  const currentConversation = ref(null);
  const messages = ref([]);
  const unreadCount = ref(0);
  const loading = ref(false);

  // Polling interval ID
  let pollingInterval = null;

  // Obtener ID del usuario actual
  const getCurrentUserId = () => {
    const user = localStorage.getItem('user');
    if (user) {
      try {
        return JSON.parse(user).id;
      } catch (e) {
        return null;
      }
    }
    return null;
  };

  // Helper para agregar propiedad is_mine a los mensajes
  const markMessagesAsMine = (msgs) => {
    const currentUserId = getCurrentUserId();
    return msgs.map(msg => ({
      ...msg,
      is_mine: msg.sender_id === currentUserId
    }));
  };

  /**
   * Obtener todas las conversaciones del usuario
   */
  const fetchConversations = async () => {
    try {
      loading.value = true;
      const response = await api.get('/chat/conversations');
      conversations.value = response.data.conversations || [];

      // Calcular mensajes no leídos totales
      calculateUnreadCount();

      return response.data;
    } catch (error) {
      console.error('Error al obtener conversaciones:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Abrir una conversación con un usuario específico
   * @param {number} userId - ID del usuario
   */
  const openConversation = async (userId) => {
    try {
      loading.value = true;
      const response = await api.get(`/chat/conversations/${userId}`);
      currentConversation.value = response.data.conversation;

      // Marcar mensajes con is_mine
      const rawMessages = response.data.conversation.messages || [];
      messages.value = markMessagesAsMine(rawMessages);

      // Marcar como leídos automáticamente al abrir
      if (currentConversation.value.id) {
        await markAsRead(currentConversation.value.id);
      }

      return response.data;
    } catch (error) {
      console.error('Error al abrir conversación:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Enviar un mensaje en una conversación
   * @param {number} conversationId - ID de la conversación
   * @param {string} message - Contenido del mensaje
   */
  const sendMessage = async (conversationId, message) => {
    try {
      const response = await api.post(
        `/chat/conversations/${conversationId}/messages`,
        { message }
      );

      // Agregar el mensaje nuevo a la lista actual con is_mine
      if (currentConversation.value && currentConversation.value.id === conversationId) {
        const newMessage = response.data.data;
        const markedMessage = markMessagesAsMine([newMessage])[0];
        messages.value.push(markedMessage);
      }

      // Actualizar la lista de conversaciones para reflejar el último mensaje
      await fetchConversations();

      return response.data;
    } catch (error) {
      console.error('Error al enviar mensaje:', error);
      throw error;
    }
  };

  /**
   * Marcar una conversación como leída
   * @param {number} conversationId - ID de la conversación
   */
  const markAsRead = async (conversationId) => {
    try {
      const response = await api.post(
        `/chat/conversations/${conversationId}/mark-read`
      );

      // Actualizar el contador de no leídos
      await fetchUnreadCount();

      // Actualizar la conversación actual
      if (currentConversation.value && currentConversation.value.id === conversationId) {
        currentConversation.value.unread_count = 0;
      }

      // Actualizar en la lista de conversaciones
      const index = conversations.value.findIndex(conv => conv.id === conversationId);
      if (index !== -1) {
        conversations.value[index].unread_count = 0;
      }

      return response.data;
    } catch (error) {
      console.error('Error al marcar como leído:', error);
      throw error;
    }
  };

  /**
   * Obtener el contador total de mensajes no leídos
   */
  const fetchUnreadCount = async () => {
    try {
      const response = await api.get('/chat/unread-count');
      unreadCount.value = response.data.unread_count || 0;
      return response.data;
    } catch (error) {
      console.error('Error al obtener contador de no leídos:', error);
      throw error;
    }
  };

  /**
   * Calcular mensajes no leídos totales desde las conversaciones cargadas
   */
  const calculateUnreadCount = () => {
    unreadCount.value = conversations.value.reduce(
      (total, conv) => total + (conv.unread_count || 0),
      0
    );
  };

  /**
   * Iniciar polling para actualizar conversaciones automáticamente
   * @param {number} interval - Intervalo en milisegundos (por defecto 5000ms = 5s)
   */
  const startPolling = (interval = 5000) => {
    // Detener polling previo si existe
    stopPolling();

    // Iniciar nuevo polling
    pollingInterval = setInterval(async () => {
      try {
        await fetchConversations();

        // Si hay una conversación abierta, actualizarla también
        if (currentConversation.value) {
          await openConversation(currentConversation.value.other_user.id);
        }
      } catch (error) {
        console.error('Error en polling:', error);
      }
    }, interval);
  };

  /**
   * Detener el polling
   */
  const stopPolling = () => {
    if (pollingInterval) {
      clearInterval(pollingInterval);
      pollingInterval = null;
    }
  };

  /**
   * Limpiar recursos al desmontar el componente
   */
  onUnmounted(() => {
    stopPolling();
  });

  // Computed properties útiles
  const hasUnreadMessages = computed(() => unreadCount.value > 0);

  const sortedConversations = computed(() => {
    return [...conversations.value].sort((a, b) => {
      // Ordenar por último mensaje (más reciente primero)
      const dateA = new Date(a.last_message?.created_at || a.updated_at);
      const dateB = new Date(b.last_message?.created_at || b.updated_at);
      return dateB - dateA;
    });
  });

  // ==========================================
  // SEGURIDAD: CARGA LAZY
  // ==========================================

  /**
   * Inicialización segura del composable
   * Solo se llama cuando el usuario está en la vista de chat
   * y tiene sesión activa
   */
  const initialize = async () => {
    // Verificar token ANTES de inicializar
    const token = localStorage.getItem('auth_token');
    if (!token) {
      console.warn('[SECURITY] No se puede inicializar useChat: sin token');
      return;
    }

    // Verificar que el usuario está autenticado
    if (!authStore.isAuthenticated) {
      console.warn('[SECURITY] No se puede inicializar useChat: usuario no autenticado');
      return;
    }

    console.log('[SECURITY] Inicializando useChat con sesión activa');

    // Cargar datos iniciales
    await fetchConversations();

    // Iniciar polling para actualizaciones en tiempo real
    startPolling();
  };

  /**
   * Limpieza de datos cuando se sale de la vista
   * IMPORTANTE: También detiene el polling para liberar recursos
   */
  const cleanup = () => {
    console.log('[SECURITY] Limpiando datos de useChat');

    // Detener polling primero
    stopPolling();

    // Limpiar todos los datos
    conversations.value = [];
    currentConversation.value = null;
    messages.value = [];
    unreadCount.value = 0;
  };

  // Retornar todo el estado y funciones
  return {
    // Estado
    conversations,
    currentConversation,
    messages,
    unreadCount,
    loading,

    // Computed
    hasUnreadMessages,
    sortedConversations,

    // Funciones
    fetchConversations,
    openConversation,
    sendMessage,
    markAsRead,
    fetchUnreadCount,
    startPolling,
    stopPolling,

    // Seguridad: Carga Lazy
    initialize,
    cleanup,
  };
}
