<template>
  <!-- Overlay oscuro cuando está abierto -->
  <Transition name="fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 bg-black/50 z-40"
      @click="$emit('close')"
    ></div>
  </Transition>

  <!-- Sidebar derecho -->
  <Transition name="slide-left">
    <aside
      v-if="isOpen"
      class="fixed right-0 top-0 h-screen w-96 bg-white dark:bg-gray-800 shadow-2xl z-50 flex flex-col"
    >
      <!-- Header -->
      <div class="border-b border-gray-200 dark:border-gray-700 p-4 flex justify-between items-center">
        <h2 class="font-semibold text-lg text-gray-900 dark:text-white">Chat y Usuarios</h2>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- Tabs: Usuarios en línea / Conversaciones -->
      <div class="border-b border-gray-200 dark:border-gray-700 flex">
        <button
          @click="activeTab = 'online'"
          :class="[
            'flex-1 py-3 px-4 font-medium text-sm transition-colors',
            activeTab === 'online'
              ? 'text-primary-600 border-b-2 border-primary-600 dark:text-primary-400'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
          ]"
        >
          Usuarios ({{ onlineUsers.count || 0 }})
        </button>
        <button
          @click="activeTab = 'conversations'"
          :class="[
            'flex-1 py-3 px-4 font-medium text-sm transition-colors',
            activeTab === 'conversations'
              ? 'text-primary-600 border-b-2 border-primary-600 dark:text-primary-400'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
          ]"
        >
          Conversaciones ({{ unreadCount || 0 }})
        </button>
      </div>

      <!-- Tab Content: Lista de usuarios en línea -->
      <div
        v-if="activeTab === 'online' && !currentConversation"
        class="flex-1 overflow-y-auto p-4"
      >
        <div
          v-for="user in onlineUsers.onlineUsers"
          :key="user.id"
          @click="startChat(user.id)"
          class="flex items-center gap-3 p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg cursor-pointer transition-colors"
        >
          <div class="relative">
            <UserCircleIcon
              v-if="!user.avatar"
              class="w-10 h-10 text-gray-400 dark:text-gray-500"
            />
            <img
              v-else
              :src="user.avatar"
              :alt="user.name"
              class="w-10 h-10 rounded-full object-cover"
            />
            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"></span>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">En línea</p>
          </div>
        </div>
        <div
          v-if="onlineUsers.onlineUsers.length === 0"
          class="text-center text-gray-500 dark:text-gray-400 py-8"
        >
          No hay usuarios en línea
        </div>
      </div>

      <!-- Tab Content: Lista de conversaciones -->
      <div
        v-if="activeTab === 'conversations' && !currentConversation"
        class="flex-1 overflow-y-auto"
      >
        <div
          v-for="conv in conversations"
          :key="conv.id"
          @click="selectConversation(conv)"
          class="border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
        >
          <div class="flex items-center gap-3">
            <UserCircleIcon class="w-10 h-10 text-gray-400 dark:text-gray-500 flex-shrink-0" />
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 dark:text-white truncate">
                {{ conv.other_user.name }}
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                {{ conv.last_message?.message || 'Sin mensajes' }}
              </p>
            </div>
            <span
              v-if="conv.unread_count > 0"
              class="bg-red-500 text-white text-xs rounded-full px-2 py-1 flex-shrink-0"
            >
              {{ conv.unread_count }}
            </span>
          </div>
        </div>
        <div
          v-if="conversations.length === 0"
          class="text-center text-gray-500 dark:text-gray-400 py-8"
        >
          No hay conversaciones
        </div>
      </div>

      <!-- Vista de chat cuando hay conversación activa -->
      <div
        v-if="currentConversation"
        class="absolute inset-0 bg-white dark:bg-gray-800 flex flex-col"
      >
        <!-- Header del chat -->
        <div class="border-b border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
          <button
            @click="closeChat()"
            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-bold text-xl"
          >
            ←
          </button>
          <UserCircleIcon class="w-8 h-8 text-gray-400 dark:text-gray-500" />
          <h3 class="font-semibold text-gray-900 dark:text-white">
            {{ currentConversation.other_user.name }}
          </h3>
        </div>

        <!-- Mensajes -->
        <div
          ref="messagesContainer"
          class="flex-1 overflow-y-auto p-4 space-y-3"
        >
          <div
            v-for="msg in messages"
            :key="msg.id"
            :class="['flex', msg.is_mine ? 'justify-end' : 'justify-start']"
          >
            <div
              :class="[
                'max-w-xs rounded-lg p-3 break-words',
                msg.is_mine
                  ? 'bg-primary-600 text-white'
                  : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white'
              ]"
            >
              <p class="text-sm">{{ msg.message }}</p>
              <span
                :class="[
                  'text-xs block mt-1',
                  msg.is_mine ? 'opacity-70' : 'opacity-60'
                ]"
              >
                {{ formatTime(msg.created_at) }}
              </span>
            </div>
          </div>
          <div
            v-if="messages.length === 0"
            class="text-center text-gray-500 dark:text-gray-400 py-8"
          >
            No hay mensajes. Inicia la conversación.
          </div>
        </div>

        <!-- Input de mensaje -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-4 flex gap-2">
          <input
            v-model="newMessage"
            @keyup.enter="send()"
            placeholder="Escribe un mensaje..."
            class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
          <button
            @click="send()"
            :disabled="!newMessage.trim()"
            :class="[
              'p-2 rounded-lg transition-colors',
              newMessage.trim()
                ? 'bg-primary-600 hover:bg-primary-700 text-white'
                : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed'
            ]"
          >
            <PaperAirplaneIcon class="w-5 h-5" />
          </button>
        </div>
      </div>
    </aside>
  </Transition>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { XMarkIcon, PaperAirplaneIcon, UserCircleIcon } from '@heroicons/vue/24/outline'
import { useChat } from '@/composables/useChat'
import { useOnlineUsers } from '@/composables/useOnlineUsers'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close'])

// Composables
const chat = useChat()
const onlineUsers = useOnlineUsers()

// Destructure chat composable
const {
  conversations,
  currentConversation,
  messages,
  unreadCount,
  openConversation,
  sendMessage: sendMsg,
  fetchConversations
} = chat

// State
const activeTab = ref('online')
const newMessage = ref('')
const messagesContainer = ref(null)

// Methods
const startChat = async (userId) => {
  try {
    await openConversation(userId)
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error al abrir conversación:', error)
  }
}

const selectConversation = async (conv) => {
  try {
    await openConversation(conv.other_user.id)
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error al seleccionar conversación:', error)
  }
}

const closeChat = () => {
  currentConversation.value = null
  messages.value = []
}

const send = async () => {
  if (!newMessage.value.trim()) return
  if (!currentConversation.value) return

  try {
    await sendMsg(currentConversation.value.id, newMessage.value)
    newMessage.value = ''
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error al enviar mensaje:', error)
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
    // Cargar usuarios en línea y conversaciones cuando se abre el sidebar
    await Promise.all([
      onlineUsers.fetchOnlineUsers(),
      fetchConversations()
    ])
  }
})

watch(messages, async () => {
  await nextTick()
  scrollToBottom()
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

.slide-left-enter-active,
.slide-left-leave-active {
  transition: transform 0.3s ease;
}

.slide-left-enter-from,
.slide-left-leave-to {
  transform: translateX(100%);
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
