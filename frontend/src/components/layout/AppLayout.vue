<template>
  <div class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Navbar -->
    <Navbar @toggle-chat="toggleChat" />

    <!-- Contenedor principal -->
    <div class="flex flex-1">
      <!-- Sidebar -->
      <Sidebar />

      <!-- Contenido principal -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>

    <!-- Chat Sidebar (derecha) -->
    <ChatSidebar :is-open="isChatOpen" @close="isChatOpen = false" />

    <!-- AI Chat Window -->
    <AIChatWindow :is-open="isAIChatOpen" @close="isAIChatOpen = false" />

    <!-- Botón flotante para abrir AI Chat -->
    <button
      v-if="!isAIChatOpen"
      @click="isAIChatOpen = true"
      class="fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center z-40 hover:scale-110"
      title="Asistente IA"
    >
      <span class="text-2xl">🤖</span>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Navbar from './Navbar.vue'
import Sidebar from './Sidebar.vue'
import ChatSidebar from '../chat/ChatSidebar.vue'
import AIChatWindow from '../ai-chat/AIChatWindow.vue'

// Estado del chat sidebar
const isChatOpen = ref(false)

// Estado del AI chat
const isAIChatOpen = ref(false)

// Toggle del chat sidebar
const toggleChat = () => {
  isChatOpen.value = !isChatOpen.value
}
</script>
