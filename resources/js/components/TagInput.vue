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
    placeholder?: string;
}>();


const model = defineModel()

</script>

<template>
    <div :class="['flex', horizontal ? 'flex-row items-center gap-4' : 'flex-col', wrapperClass]">
        <Label v-if="label" :for="id" :class="['mb-2', labelClass, horizontal ? 'min-w-[100px]' : '']">
            {{ label }}
        </Label>

        <div class="flex-1 flex flex-col">
            <div class="relative flex items-center">
                <TagsInput :disabled="disabled"  v-model="model" :class="['w-full', inputClass]">
                    <slot />
                    <TagsInputInput :placeholder="placeholder" />
                </TagsInput>
            </div>

            <div v-if="help || error" class="flex flex-col">
                <InputHelpText class="mt-2" :message="help" />
                <InputError class="mt-2" :message="error" />
            </div>
        </div>
    </div>
</template>
