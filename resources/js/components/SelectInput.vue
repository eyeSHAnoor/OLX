<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    id?: string;
    label?: string;
    wrapperClass?: string;
    labelClass?: string;
    inputClass?: string;
    error?: string;
    help?: string;
    horizontal?: boolean;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    prependText?: string;
    appendText?: string;
    multiple?: boolean;
}>();

const model = defineModel();
</script>

<template>
    <div :class="['flex', horizontal ? 'flex-row items-center gap-4' : 'flex-col', wrapperClass]">
        <Label v-if="label" :for="id" :class="[labelClass, horizontal ? 'mb-0 min-w-[100px]' : '']">
            {{ label }}
        </Label>

        <div class="flex flex-1 flex-col">
            <div class="relative flex items-center">
                <!-- Prepend text -->
                <span v-if="prependText" class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-muted-foreground">
                    {{ prependText }}
                </span>

                <div :class="['mt-2 w-full', inputClass]">
                    <Select v-model="model" :name="id" :multiple="multiple">
                        <SelectTrigger
                            :class="['w-full', prependText ? 'pl-9' : '', appendText ? 'pr-9' : '']"
                            :disabled="disabled"
                            :readonly="readonly"
                            @click.prevent="readonly ? null : undefined"
                        >
                            <SelectValue :placeholder="placeholder" />
                        </SelectTrigger>
                        <slot />
                    </Select>
                </div>

                <!-- Append text -->
                <span v-if="appendText" class="pointer-events-none absolute inset-y-0 right-0 z-10 flex items-center pr-3 text-muted-foreground">
                    {{ appendText }}
                </span>
            </div>

            <div v-if="help || error" class="flex flex-col">
                <InputHelpText class="mt-2" :message="help" />
                <InputError class="mt-2" :message="error" />
            </div>
        </div>
    </div>
</template>
