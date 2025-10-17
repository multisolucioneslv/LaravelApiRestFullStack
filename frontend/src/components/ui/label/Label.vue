<script setup>
defineProps({
  for: {
    type: String,
    default: null
  }
})
</script>

<template>
  <label
    :for="for"
    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-gray-700 dark:text-gray-300"
  >
    <slot />
  </label>
</template>
