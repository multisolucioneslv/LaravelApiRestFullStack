<script setup>
import { DropdownMenuTrigger, useForwardProps } from 'radix-vue'
import { computed } from 'vue'

const props = defineProps({
  disabled: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
})

const forwarded = useForwardProps(props)
</script>

<template>
  <DropdownMenuTrigger v-bind="forwarded">
    <slot />
  </DropdownMenuTrigger>
</template>
