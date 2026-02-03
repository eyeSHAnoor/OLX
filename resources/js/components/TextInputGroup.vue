<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    defaultValue?: string | number | null;
    modelValue?: string | number | null;
    class?: HTMLAttributes['class'];
    id?: string;
    type?: string | null;
    label?: string;
    error?: string;
    wrapperClass?: string;
    labelClass?: string;
    inputClass?: string;
    horizontal?: boolean;
    disabled?: boolean;
    prependText?: string;
    appendText?: string;
    help?: string;
    autofocus?: boolean;
    required?: boolean;
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
                <!-- Prepend text -->
                <span
                    v-if="prependText"
                    class="mt-2 inline-flex h-9 items-center rounded-s-md border border-gray-300 bg-muted/50 px-3 text-sm whitespace-nowrap text-gray-900 shadow-xs dark:border-gray-600 dark:bg-gray-600 dark:text-gray-400"
                >
                    {{ prependText }}
                </span>

                <Input
                    v-if="disabled"
                    :disabled="disabled"
                    :type="type ?? 'text'"
                    ref="input"
                    v-bind="$attrs"
                    :id="id"
                    :name="id"
                    :class="['mt-2', inputClass]"
                    :model-value="modelValue"
                />

                <Input
                    v-else
                    :type="type ?? 'text'"
                    ref="input"
                    :required="required"
                    :autofocus="autofocus"
                    v-bind="$attrs"
                    :id="id"
                    :name="id"
                    :class="['mt-2', prependText ? 'rounded-l-none' : '', appendText ? 'pr-9' : '', inputClass]"
                    v-model="modelValue"
                />

                <!-- Append text -->
                <span
                    v-if="appendText || $slots.appendText"
                    class="absolute inset-y-6.5 right-0 flex items-center pr-3 text-sm text-muted-foreground"
                >
                    <template v-if="appendText">{{ appendText }}</template>
                    <slot name="appendText" />
                </span>
            </div>

            <div v-if="help || error" class="flex flex-col">
                <InputHelpText class="mt-2" :message="help" />
                <InputError class="mt-2" :message="error" />
            </div>
        </div>
    </div>
</template>
