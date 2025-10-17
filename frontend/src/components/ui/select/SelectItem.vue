<script setup>
import { inject, onMounted, computed } from 'vue'

const props = defineProps({
  value: {
    type: [String, Number],
    required: true
  }
})

const slots = defineSlots()
const select = inject('select')
const registerItem = inject('selectRegisterItem', null)

const isSelected = computed(() => select.selectedValue === props.value)

const label = computed(() => {
  return slots.default ? slots.default()[0].children : String(props.value)
})

onMounted(() => {
  if (registerItem) {
    registerItem({ value: props.value, label: label.value })
  }
})

function handleClick() {
  select.selectItem(props.value)
}
</script>

<template>
  <button
    type="button"
    @click="handleClick"
    :class="[
      'relative w-full cursor-pointer select-none px-3 py-2 text-sm text-left hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors',
      isSelected ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-900 dark:text-white'
    ]"
  >
    <slot />
  </button>
</template>
