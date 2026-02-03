<script setup lang="ts">
import { Icon } from '@iconify/vue'

interface Props {
    size?: string | number
    color?: string
    position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'
    inline?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    size: '1.25rem',
    color: 'text-green-600',
    position: 'top-right',
    inline: false
})

const positionClasses = {
    'top-right': 'top-1 right-2',
    'top-left': 'top-1 left-2',
    'bottom-right': 'bottom-1 right-2',
    'bottom-left': 'bottom-1 left-2'
}
</script>

<template>
  <span
      :class="[
      inline ? 'inline-flex items-center' : 'absolute',
      !inline && positionClasses[position]
    ]"
  >
    <Icon icon="mdi:tick-circle" :class="['size-5', color]" :style="{ fontSize: size }" />
  </span>
</template>
