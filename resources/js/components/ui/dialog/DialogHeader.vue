<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div data-slot="dialog-header" :class="cn('flex justify-between mr-9 gap-2 text-center sm:text-left', props.class)">
    <slot />
  </div>
</template>
