<template>
  <div
    ref="menuItemRef"
    class="relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
    role="menuitem"
    :tabindex="disabled ? -1 : 0"
    :data-disabled="disabled ? '' : undefined"
    @click="handleClick"
    @keydown.enter="handleClick"
    @keydown.space.prevent="handleClick"
  >
    <slot />
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const menuItemRef = ref(null)

const handleClick = (event) => {
  if (!props.disabled) {
    // Liberar el foco antes de emitir el evento para evitar warning de aria-hidden
    if (menuItemRef.value) {
      menuItemRef.value.blur()
    }
    emit('click', event)
  }
}
</script>
