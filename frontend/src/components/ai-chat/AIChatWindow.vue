<template>
  <!-- Overlay oscuro cuando está abierto -->
  <Transition name="fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black/50 z-40"
      @click="$emit('close')"
    ></div>
  </Transition>

  <!-- Ventana de chat -->
  <Transition name="slide-up">
    <div
      v-if="isOpen"
      class="fixed bottom-0 right-4 w-full max-w-2xl h-[600px] bg-white dark:bg-gray-800 shadow-2xl z-50 rounded-t-lg flex flex-col"
    >
      <!-- Header -->
      <div class="border-b border-gray-200 dark:border-gray-700 p-4 flex justify-between items-center rounded-t-lg bg-gradient-to-r from-blue-500 to-purple-600">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
            <span class="text-2xl">></span>
          </div>
          <div>
            <h2 class="font-semibold text-lg text-white">Asistente IA</h2>
            <p class="text-xs text-white/80">Con integración N8N</p>
          </div>
        </div>
        <button
          @click="$emit('close')"
          class="text-white/80 hover:text-white"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- Área principal -->
      <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar de conversaciones -->
        <div class="w-64 border-r border-gray-200 dark:border-gray-700 flex flex-col bg-gray-50 dark:bg-gray-900">
          <!-- Botón nueva conversación -->
          <div class="p-3">
            <button
              @click="handleNewConversation"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-colors"
            >
              <PlusIcon class="w-4 h-4" />
              Nueva conversación
            </button>
          </div>

          <!-- Lista de conversaciones -->
          <div class="flex-1 overflow-y-auto">
            <div
              v-for="conv in conversations"
              :key="conv.id"
              @click="handleSelectConversation(conv.id)"
              :class="[
                'p-3 border-b border-gray-200 dark:border-gray-700 cursor-pointer transition-colors',
                currentConversation?.id === conv.id
                  ? 'bg-blue-100 dark:bg-blue-900'
                  : 'hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                {{ conv.title || 'Nueva conversación' }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">
                {{ formatTime(conv.last_message_at || conv.created_at) }}
              </p>
            </div>
            <div
              v-if="conversations.length === 0"
              class="text-center text-gray-500 dark:text-gray-400 py-8 text-sm"
            >
              No hay conversaciones
            </div>
          </div>
        </div>

        <!-- Área de chat -->
        <div class="flex-1 flex flex-col">
          <!-- Mensajes -->
          <div
            ref="messagesContainer"
            class="flex-1 overflow-y-auto p-4 space-y-4"
          >
            <div v-if="!currentConversation" class="flex items-center justify-center h-full">
              <div class="text-center text-gray-500 dark:text-gray-400">
                <div class="text-6xl mb-4">💬</div>
                <p class="text-lg font-medium">Inicia una nueva conversación</p>
                <p class="text-sm mt-2">Pregúntame sobre usuarios, productos, empresas, etc.</p>
              </div>
            </div>

            <div
              v-for="msg in messages"
              :key="msg.id"
              :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']"
            >
              <div
                :class="[
                  'max-w-[80%] rounded-lg p-3 break-words',
                  msg.role === 'user'
                    ? 'bg-blue-600 text-white'
                    : msg.loading
                    ? 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 animate-pulse'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white'
                ]"
              >
                <div v-if="msg.loading" class="flex items-center gap-2">
                  <div class="flex gap-1">
                    <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></span>
                    <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                    <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                  </div>
                  <span class="text-sm">{{ msg.content }}</span>
                </div>
                <p v-else class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                <span
                  :class="[
                    'text-xs block mt-1',
                    msg.role === 'user' ? 'opacity-70' : 'opacity-60'
                  ]"
                >
                  {{ formatTime(msg.created_at) }}
                </span>
              </div>
            </div>

            <div
              v-if="messages.length === 0 && currentConversation"
              class="text-center text-gray-500 dark:text-gray-400 py-8"
            >
              Escribe tu primer mensaje
            </div>
          </div>

          <!-- Input de mensaje -->
          <div class="border-t border-gray-200 dark:border-gray-700 p-4">
            <div class="flex gap-2">
              <input
                v-model="newMessage"
                @keyup.enter="handleSendMessage"
                :disabled="!currentConversation || isSending"
                placeholder="Escribe tu mensaje... (ej: ¿Cuántos usuarios tengo?)"
                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 p-3 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              />
              <button
                @click="handleSendMessage"
                :disabled="!currentConversation || !newMessage.trim() || isSending"
                :class="[
                  'p-3 rounded-lg transition-colors',
                  currentConversation && newMessage.trim() && !isSending
                    ? 'bg-blue-600 hover:bg-blue-700 text-white'
                    : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
                ]"
              >
                <PaperAirplaneIcon class="w-5 h-5" />
              </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              Puedes preguntar sobre: usuarios, empresas, productos, proveedores, categorías
            </p>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from 'vue'
import { XMarkIcon, PaperAirplaneIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { useAIChat } from '@/composables/useAIChat'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close'])

// Composable
const {
  conversations,
  currentConversation,
  messages,
  isLoading,
  isSending,
  fetchConversations,
  openConversation,
  sendMessage,
  startNewConversation,
} = useAIChat()

// State
const newMessage = ref('')
const messagesContainer = ref(null)

// Methods
const handleNewConversation = async () => {
  try {
    await startNewConversation()
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error al crear conversación:', error)
    alert('Error al crear nueva conversación')
  }
}

const handleSelectConversation = async (conversationId) => {
  try {
    await openConversation(conversationId)
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error al abrir conversación:', error)
    alert('Error al abrir conversación')
  }
}

const handleSendMessage = async () => {
  if (!newMessage.value.trim() || !currentConversation.value || isSending.value) return

  const message = newMessage.value
  newMessage.value = ''

  try {
    await sendMessage(currentConversation.value.id, message)
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error al enviar mensaje:', error)
    alert('Error al enviar mensaje. Por favor, intenta de nuevo.')
  }
}

const formatTime = (date) => {
  if (!date) return ''

  const messageDate = new Date(date)
  const now = new Date()
  const diffInSeconds = Math.floor((now - messageDate) / 1000)

  if (diffInSeconds < 60) return 'Ahora'
  if (diffInSeconds < 3600) return `Hace ${Math.floor(diffInSeconds / 60)}m`
  if (diffInSeconds < 86400) return `Hace ${Math.floor(diffInSeconds / 3600)}h`

  return messageDate.toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

// Watchers
watch(() => props.isOpen, async (newVal) => {
  if (newVal) {
    await fetchConversations()
  }
})

watch(messages, async () => {
  await nextTick()
  scrollToBottom()
}, { deep: true })

// Lifecycle
onMounted(async () => {
  if (props.isOpen) {
    await fetchConversations()
  }
})
</script>

<style scoped>
/* Transiciones */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}

/* Scrollbar personalizado */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background: #4a5568;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #718096;
}
</style>
