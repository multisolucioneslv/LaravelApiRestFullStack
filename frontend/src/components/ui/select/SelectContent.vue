<script setup>
import { inject, onMounted, onUnmounted, ref, provide } from 'vue'

const select = inject('select')
const contentRef = ref(null)

const items = ref([])

function registerItem(item) {
  items.value.push(item)
}

function getDisplayValue(value) {
  const item = items.value.find(i => i.value === value)
  return item ? item.label : value
}

provide('selectDisplayValue', getDisplayValue)
provide('selectRegisterItem', registerItem)

function handleClickOutside(event) {
  if (contentRef.value && !contentRef.value.contains(event.target)) {
    select.close()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div
    v-if="select.isOpen"
    ref="contentRef"
    class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg"
  >
    <div class="py-1">
      <slot />
    </div>
  </div>
</template>
