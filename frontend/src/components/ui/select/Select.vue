<script setup>
import { ref, provide, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const selectedValue = ref(props.modelValue)

watch(() => props.modelValue, (newVal) => {
  selectedValue.value = newVal
})

function toggle() {
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

function selectItem(value) {
  selectedValue.value = value
  emit('update:modelValue', value)
  close()
}

provide('select', {
  isOpen,
  selectedValue,
  toggle,
  close,
  selectItem
})
</script>

<template>
  <div class="relative">
    <slot />
  </div>
</template>
