<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 py-12">
    <!-- Toggle de Dark Mode en esquina superior derecha -->
    <div class="absolute top-4 right-4">
      <DarkModeToggle />
    </div>

    <LoginForm />
  </div>
</template>

<script setup>
import LoginForm from '@/components/auth/LoginForm.vue'
import DarkModeToggle from '@/components/layout/DarkModeToggle.vue'
</script>
