<script setup>
import { DialogTrigger, useForwardProps } from 'radix-vue'

const props = defineProps({
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
})

const forwarded = useForwardProps(props)
</script>

<template>
  <DialogTrigger v-bind="forwarded">
    <slot />
  </DialogTrigger>
</template>
