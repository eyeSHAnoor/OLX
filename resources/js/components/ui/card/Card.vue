<script setup lang="ts">
import { cva } from 'class-variance-authority'
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

// Define variants without relying on cva's complex types
const cardVariants = {
    base: 'flex flex-col gap-6 rounded-xl border py-6 shadow-sm',
    variants: {
        default: 'bg-card text-card-foreground',
        success: 'bg-green-100 text-green-700 border-green-200',
        warning: 'bg-amber-100 text-amber-700 border-amber-200',
        info: 'bg-blue-100 text-blue-700 border-blue-200',
        danger: 'bg-red-100 text-red-700 border-red-200',
        secondary: 'bg-accent'
    }
}

const props = defineProps<{
    variant?: keyof typeof cardVariants.variants
    class?: HTMLAttributes['class']
}>()

const variantClasses = computed(() =>
    props.variant ? cardVariants.variants[props.variant] : cardVariants.variants.default
)
</script>

<template>
    <div
        data-slot="card"
        :class="cn(cardVariants.base, variantClasses, props.class)"
    >
        <slot />
    </div>
</template>
