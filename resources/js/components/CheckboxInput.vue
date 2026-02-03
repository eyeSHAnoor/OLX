<script setup lang="ts">
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    defaultValue?: boolean | string | number | null;
    modelValue?: boolean | string | number | null;
    class?: HTMLAttributes['class'];
    id?: string;
    label?: string;
    error?: string;
    wrapperClass?: string;
    labelClass?: string;
    inputClass?: string;
    horizontal?: boolean;
    help?: string;
    checkedColor?: string;
    uncheckedColor?: string;
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});
</script>

<template>
    <div :class="['flex', horizontal ? 'flex-row' : 'flex-col', wrapperClass]">
        <div class="flex items-center space-x-2">
            <Checkbox :id="id" v-bind="$attrs" v-model="modelValue" :checkedColor :uncheckedColor />

            <label
                :for="id"
                :class="
                    cn(
                        'flex items-center gap-2 text-sm leading-none select-none group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:opacity-50',
                        props.class,
                    )
                "
            >
                {{ label }}
            </label>
        </div>
        <InputHelpText class="mt-2" :message="help" />
        <InputError class="mt-1" :message="error" />
    </div>
</template>
