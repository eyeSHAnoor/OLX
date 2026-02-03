<script setup lang="ts">
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(
    defineProps<{
        defaultValue?: string | number | Date | null;
        modelValue?: string | number | Date | null;
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
        displayFormat?: string;
        enableTimePicker?: boolean;
        range?: boolean;
    }>(),
    {
        enableTimePicker: true,
        range: false,
    },
);

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number | Date | null): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});

const model = defineModel();

import { useVModel } from '@vueuse/core';

// watch(model, (val) => {
//     if (val instanceof Date) {
//         console.log('Formatted:', format(val, 'dd-MM-yyyy'));
//     }
// });

// const shadcnInputBase = [
//     'w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background',
//     'placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
//     'disabled:cursor-not-allowed disabled:opacity-50',
// ];
</script>
<template>
    <div :class="['flex', horizontal ? 'flex-row items-center gap-4' : 'flex-col', wrapperClass]">
        <Label v-if="label" :for="id" :class="[labelClass, horizontal ? 'mb-0 min-w-[100px]' : 'mb-2.5']">
            {{ label }}
        </Label>

        <div class="flex flex-1 flex-col">
            <div class="relative flex items-center">
                <!-- Prepend text -->
                <span
                    v-if="prependText"
                    :class="[
                        'pointer-events-none absolute left-0 flex items-center pl-3 text-sm text-muted-foreground',
                        horizontal ? 'inset-y-0' : 'inset-y-6.5',
                    ]"
                >
                    {{ prependText }}
                </span>

                <VueDatePicker
                    :required="required"
                    :disabled="disabled"
                    ref="input"
                    v-bind="$attrs"
                    :id="id"
                    :name="id"
                    :input-class="['mt-2', prependText ? 'pl-9' : '', appendText ? 'pr-9' : '', inputClass]"
                    v-model="model"
                    :format="displayFormat"
                    :enableTimePicker
                    :range
                ></VueDatePicker>

                <!-- Append text -->
                <span
                    v-if="appendText"
                    :class="[
                        'pointer-events-none absolute right-0 flex items-center pr-3 text-sm text-muted-foreground',
                        horizontal ? 'inset-y-0' : 'inset-y-6.5',
                    ]"
                >
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

<style>
:root {
    /*General*/
    --dp-font-family: var(--font-sans);
    --dp-border-radius: 4px; /*Configurable border-radius*/
    --dp-cell-border-radius: 4px; /*Specific border radius for the calendar cell*/
    --dp-common-transition: all 0.1s ease-in; /*Generic transition applied on buttons and calendar cells*/

    /*Sizing*/
    --dp-button-height: 35px; /*Size for buttons in overlays*/
    --dp-month-year-row-height: 35px; /*Height of the month-year select row*/
    --dp-month-year-row-button-size: 35px; /*Specific height for the next/previous buttons*/
    --dp-button-icon-height: 20px; /*Icon sizing in buttons*/
    --dp-cell-size: 35px; /*Width and height of calendar cell*/
    --dp-cell-padding: 5px; /*Padding in the cell*/
    --dp-common-padding: 10px; /*Common padding used*/
    --dp-input-icon-padding: 35px; /*Padding on the left side of the input if icon is present*/
    --dp-input-padding: 5.5px 30px 5.5px 12px; /*Padding in the input*/
    --dp-menu-min-width: 260px; /*Adjust the min width of the menu*/
    --dp-action-buttons-padding: 2px 5px; /*Adjust padding for the action buttons in action row*/
    --dp-row-margin: 5px 0; /*Adjust the spacing between rows in the calendar*/
    --dp-calendar-header-cell-padding: 0.5rem; /*Adjust padding in calendar header cells*/
    --dp-two-calendars-spacing: 10px; /*Space between multiple calendars*/
    --dp-overlay-col-padding: 3px; /*Padding in the overlay column*/
    --dp-time-inc-dec-button-size: 32px; /*Sizing for arrow buttons in the time picker*/
    --dp-menu-padding: 6px 8px; /*Menu padding*/

    /*Font sizes*/
    --dp-font-size: 0.8rem; /*Default font-size*/
    --dp-preview-font-size: 0.8rem; /*Font size of the date preview in the action row*/
    --dp-time-font-size: 0.8rem; /*Font size in the time picker*/

    /*Transitions*/
    --dp-animation-duration: 0.1s; /*Transition duration*/
    --dp-menu-appear-transition-timing: cubic-bezier(0.4, 0, 1, 1); /*Timing on menu appear animation*/
    --dp-transition-timing: ease-out; /*Timing on slide animations*/
}

.dp__theme_light {
    --dp-background-color: #fff;
    --dp-text-color: #212121;
    --dp-hover-color: #f3f3f3;
    --dp-hover-text-color: #212121;
    --dp-hover-icon-color: #959595;
    --dp-primary-color: var(--primary);
    --dp-primary-disabled-color: #6bacea;
    --dp-primary-text-color: #f8f5f5;
    --dp-secondary-color: #c0c4cc;
    --dp-border-color: #ddd;
    --dp-menu-border-color: #ddd;
    --dp-border-color-hover: #aaaeb7;
    --dp-border-color-focus: #aaaeb7;
    --dp-disabled-color: #f6f6f6;
    --dp-scroll-bar-background: #f3f3f3;
    --dp-scroll-bar-color: #959595;
    --dp-success-color: #76d275;
    --dp-success-color-disabled: #a3d9b1;
    --dp-icon-color: #959595;
    --dp-danger-color: #ff6f60;
    --dp-marker-color: #ff6f60;
    --dp-tooltip-color: #fafafa;
    --dp-disabled-color-text: #8e8e8e;
    --dp-highlight-color: rgb(25 118 210 / 10%);
    --dp-range-between-dates-background-color: var(--dp-hover-color, #f3f3f3);
    --dp-range-between-dates-text-color: var(--dp-hover-text-color, #212121);
    --dp-range-between-border-color: var(--dp-hover-color, #f3f3f3);
}

.dp__theme_dark {
    --dp-background-color: #212121;
    --dp-text-color: #fff;
    --dp-hover-color: #484848;
    --dp-hover-text-color: #fff;
    --dp-hover-icon-color: #959595;
    --dp-primary-color: #005cb2;
    --dp-primary-disabled-color: #61a8ea;
    --dp-primary-text-color: #fff;
    --dp-secondary-color: #a9a9a9;
    --dp-border-color: #2d2d2d;
    --dp-menu-border-color: #2d2d2d;
    --dp-border-color-hover: #aaaeb7;
    --dp-border-color-focus: #aaaeb7;
    --dp-disabled-color: #737373;
    --dp-disabled-color-text: #d0d0d0;
    --dp-scroll-bar-background: #212121;
    --dp-scroll-bar-color: #484848;
    --dp-success-color: #00701a;
    --dp-success-color-disabled: #428f59;
    --dp-icon-color: #959595;
    --dp-danger-color: #e53935;
    --dp-marker-color: #e53935;
    --dp-tooltip-color: #3e3e3e;
    --dp-highlight-color: rgb(0 92 178 / 20%);
    --dp-range-between-dates-background-color: var(--dp-hover-color, #484848);
    --dp-range-between-dates-text-color: var(--dp-hover-text-color, #fff);
    --dp-range-between-border-color: var(--dp-hover-color, #fff);
}
</style>
