<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
    tag?: 'a' | 'button' | 'link'
    href?: string
    method?: string
    as?: string
    type?: 'button' | 'submit' | 'reset'
    preserveScroll?: boolean
    preserveState?: boolean
}>()

const emit = defineEmits(['click'])

const tagType = computed(() => {
    if (props.tag === 'link' && props.href) return 'link'
    if (props.tag === 'a' && props.href) return 'a'
    return 'button'
})
</script>

<template>
    <component
        :is="tagType === 'link' ? Link : tagType"
        :href="href"
        :type="tagType === 'button' ? (type || 'button') : undefined"
        :method="tagType === 'link' ? method : undefined"
        :preserve-scroll="tagType === 'link' ? preserveScroll : undefined"
        :preserve-state="tagType === 'link' ? preserveState : undefined"
        class="block w-full px-4 py-2 text-left border-b border-gray-200 cursor-pointer hover:bg-gray-100 hover:text-blue-700 focus:outline-none dark:border-gray-600 dark:hover:bg-gray-600 dark:hover:text-white"
        @click="$emit('click', $event)"
    >
        <slot />
    </component>
</template>
