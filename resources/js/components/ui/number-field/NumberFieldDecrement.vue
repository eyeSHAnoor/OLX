<script setup lang="ts">
import type { NumberFieldDecrementProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { Minus } from 'lucide-vue-next'
import { NumberFieldDecrement, useForwardProps } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps<NumberFieldDecrementProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardProps(delegatedProps)
</script>

<template>
  <NumberFieldDecrement data-slot="decrement" v-bind="forwarded" :class="cn('disabled:cursor-not-allowed disabled:opacity-20', props.class)">
    <slot>
        <Icon icon="mdi:caret-up" class="size-4 text-muted-foreground" />
    </slot>
  </NumberFieldDecrement>
</template>
