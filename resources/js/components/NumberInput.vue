<script setup lang="ts">
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

const model = defineModel();
</script>

<template>
    <NumberField v-model="model" :class="['flex', horizontal ? 'flex-row items-center gap-4' : 'flex-col', wrapperClass]">
        <Label v-if="label" :for="id" :class="[labelClass, horizontal ? 'mb-0 min-w-[100px]' : '']">
            {{ label }}
        </Label>

        <div class="flex flex-1 flex-col">
            <div class="relative flex w-full items-center">
                <NumberFieldContent class=" w-full relative">
                    <NumberFieldInput />
                    <NumberFieldDecrement class="absolute top-0.5  right-1 " />
                    <NumberFieldIncrement class="absolute bottom-0.5 right-1  " />
                </NumberFieldContent>
            </div>

            <div v-if="help || error" class="flex flex-col">
                <InputHelpText class="mt-2" :message="help" />
                <InputError class="mt-2" :message="error" />
            </div>
        </div>
    </NumberField>
</template>
