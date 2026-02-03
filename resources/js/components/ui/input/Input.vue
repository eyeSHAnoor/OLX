<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';

const props = defineProps<{
    defaultValue?: string | number
    modelValue?: string | number
    class?: HTMLAttributes['class']
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue
});
</script>

<template>
    <input
        v-model="modelValue"
        data-slot="input"
        :class="cn(
      'text-input-foreground text-xs file:text-foreground placeholder:text-muted-foreground/60 ' +
        'border bg-input dark:bg-input/30 px-3 py-1 text-base transition-[color,box-shadow] outline-none ',
       'selection:bg-primary selection:text-primary-foreground ' +
        ' border-muted-foreground/30 flex w-full min-w-0 rounded-md ' ,
         'file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium ' ,
          'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      'focus-visible:border-primary hover:border-primary',
      'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
      'read-only:bg-neutral-200',
      props.class,
    )"
    >
</template>
