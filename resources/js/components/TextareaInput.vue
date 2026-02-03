<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    defaultValue?: string | number | null;
    modelValue?: string | number | null;
    class?: HTMLAttributes['class'];
    id?: string;
    type?: { type: String; default: 'text' };
    label?: string;
    error?: string;
    wrapperClass?: string;
    labelClass?: string;
    inputClass?: string;
    horizontal?: boolean;
    disabled?: boolean;
    help?: string;
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
    <div :class="['flex', horizontal ? 'flex-row items-center gap-4' : 'flex-col', wrapperClass]">
        <Label v-if="label" :for="id" :class="[labelClass, horizontal ? 'mb-0 min-w-[100px]' : '']">
            {{ label }}
        </Label>
        <div class="flex flex-1 flex-col">
            <div class="relative flex items-center">
                <Textarea
                    v-if="disabled"
                    :disabled="disabled"
                    ref="input"
                    v-bind="$attrs"
                    :id="id"
                    :name="id"
                    class="mt-2"
                    :model-value="modelValue"
                />
                <Textarea v-else ref="input" v-bind="$attrs" :id="id" :name="id" class="mt-2" v-model="modelValue" />
            </div>

            <div v-if="help || error" class="flex flex-col">
                <InputHelpText class="mt-2" :message="help" />
                <InputError class="mt-2" :message="error" />
            </div>
        </div>
    </div>
</template>
