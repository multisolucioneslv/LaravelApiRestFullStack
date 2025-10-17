<script setup>
import { inject, computed } from 'vue'

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Seleccionar...'
  }
})

const select = inject('select')
const displayValue = inject('selectDisplayValue', null)

const text = computed(() => {
  if (displayValue && select.selectedValue) {
    return displayValue(select.selectedValue)
  }
  return select.selectedValue || props.placeholder
})
</script>

<template>
  <span>{{ text }}</span>
</template>
