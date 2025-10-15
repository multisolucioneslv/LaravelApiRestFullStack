<template>
  <tr
    class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
    :data-state="dataState"
  >
    <slot />
  </tr>
</template>

<script setup>
defineProps({
  dataState: {
    type: String,
    default: null,
  },
})
</script>
