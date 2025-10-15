<template>
  <button
    type="button"
    role="checkbox"
    :aria-checked="checked"
    :class="cn(
      'peer h-4 w-4 shrink-0 rounded-sm border border-primary ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      checked ? 'bg-primary text-primary-foreground' : 'bg-background',
      $attrs.class
    )"
    :disabled="disabled"
    @click="handleClick"
  >
    <svg
      v-if="checked || indeterminate"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="3"
      stroke-linecap="round"
      stroke-linejoin="round"
      class="h-4 w-4"
    >
      <polyline v-if="!indeterminate" points="20 6 9 17 4 12" />
      <line v-else x1="5" y1="12" x2="19" y2="12" />
    </svg>
  </button>
</template>

<script setup>
import { cn } from '@/lib/utils'

const props = defineProps({
  checked: {
    type: Boolean,
    default: false,
  },
  indeterminate: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:checked'])

const handleClick = () => {
  if (!props.disabled) {
    emit('update:checked', !props.checked)
  }
}
</script>
