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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Navbar from './Navbar.vue'
import Sidebar from './Sidebar.vue'
import ChatSidebar from '../chat/ChatSidebar.vue'

// Estado del chat sidebar
const isChatOpen = ref(false)

// Toggle del chat sidebar
const toggleChat = () => {
  isChatOpen.value = !isChatOpen.value
}
</script>
