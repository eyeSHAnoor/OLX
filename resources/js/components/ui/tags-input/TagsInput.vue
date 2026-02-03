<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { TagsInputRoot, type TagsInputRootEmits, type TagsInputRootProps, useForwardPropsEmits } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<TagsInputRootProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<TagsInputRootEmits>();

const delegatedProps = reactiveOmit(props, 'class');

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <TagsInputRoot v-bind="forwarded" :class="cn(
        'flex  flex-wrap gap-2 items-center rounded-md border border-muted-foreground/40 text-input-foreground bg-input px-3 py-1 text-sm',
        'group group-hover:border-primary focus-visible:border-primary ',
        props.class)"
    >
        <slot />
    </TagsInputRoot>
</template>
