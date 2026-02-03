<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import {
    CalendarDate,
    CalendarDateTime,
    DateFormatter,
    type DateValue,
    getLocalTimeZone,
    now,
    parseDateTime,
    toCalendarDate,
    toCalendarDateTime,
    today,
} from '@internationalized/date';
import type { HTMLAttributes } from 'vue';
import { computed, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        defaultValue?: string | DateValue | null;
        modelValue?: string | DateValue | null;
        class?: HTMLAttributes['class'];
        id?: string;
        type?: { type: String; default: 'text' };
        label?: string;
        error?: string;
        wrapperClass?: string;
        labelClass?: string;
        inputClass?: string;
        horizontal?: boolean;
        prependText?: string;
        help?: string;
        withTime?: boolean;
        disabled?: boolean;
        displayFormat?: string;
        timeStep?: number; // New: step for minutes selection
        showIcon?: boolean;
    }>(),
    { showIcon: true },
);

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string): void;
}>();

// track popover state
const isPopoverOpen = ref(false);

// Convert incoming string value to DateValue
const internalValue = computed<DateValue | null>({
    get() {
        if (!props.modelValue) return null;

        if (typeof props.modelValue !== 'string') {
            return props.modelValue;
        }

        try {
            if (props.modelValue.includes('T')) {
                return props.withTime ? parseDateTime(props.modelValue) : toCalendarDate(parseDateTime(props.modelValue));
            }

            const laravelFormat = props.modelValue.replace(' ', 'T');
            return props.withTime ? parseDateTime(laravelFormat) : toCalendarDate(parseDateTime(laravelFormat));
        } catch (e) {
            console.error('Failed to parse date:', props.modelValue, e);
            return null;
        }
    },
    set(value: DateValue | null) {
        if (!value) {
            emits('update:modelValue', '');
            return;
        }

        if (props.withTime) {
            const dt = value instanceof CalendarDate ? toCalendarDateTime(value) : value;
            emits('update:modelValue', dt.toString());
        } else {
            const date = toCalendarDate(value);
            emits('update:modelValue', date.toString());
            // Close popover when date is selected (without time)
            if (!props.withTime) {
                isPopoverOpen.value = false;
            }
        }
    },
});

// Time selection values
const hours = Array.from({ length: 24 }, (_, i) => i);
const minutes = Array.from({ length: 60 / (props.timeStep || 5) }, (_, i) => i * (props.timeStep || 5));

// Create the display formatter
const displayText = computed(() => {
    if (!internalValue.value) return 'Pick a date';

    const date = internalValue.value.toDate(getLocalTimeZone());

    if (props.displayFormat) {
        const pad = (num: number) => num.toString().padStart(2, '0');

        const replacements: Record<string, string> = {
            yyyy: date.getFullYear().toString(),
            yy: date.getFullYear().toString().slice(-2),
            MM: pad(date.getMonth() + 1),
            M: (date.getMonth() + 1).toString(),
            dd: pad(date.getDate()),
            d: date.getDate().toString(),
            HH: pad(date.getHours()),
            H: date.getHours().toString(),
            hh: pad(date.getHours() % 12 || 12),
            h: (date.getHours() % 12 || 12).toString(),
            mm: pad(date.getMinutes()),
            m: date.getMinutes().toString(),
            ss: pad(date.getSeconds()),
            s: date.getSeconds().toString(),
            a: date.getHours() < 12 ? 'AM' : 'PM',
        };

        let result = props.displayFormat;
        for (const [key, value] of Object.entries(replacements)) {
            result = result.replace(key, value);
        }
        return result;
    }

    return props.withTime
        ? new DateFormatter('en-US', { dateStyle: 'short', timeStyle: 'short' }).format(date)
        : new DateFormatter('en-US', { dateStyle: 'short' }).format(date);
});

// Update time values
const updateTime = (hours: number, minutes: number) => {
    if (!internalValue.value) {
        internalValue.value = now(getLocalTimeZone());
        return;
    }

    const current = internalValue.value;
    if (current instanceof CalendarDateTime || current instanceof ZonedDateTime) {
        internalValue.value = new CalendarDateTime(current.year, current.month, current.day, hours, minutes, current.second);
    } else {
        internalValue.value = new CalendarDateTime(current.year, current.month, current.day, hours, minutes, 0);
    }

    // Close popover after time selection
    isPopoverOpen.value = false;
};

// Handle initial default value
if (props.defaultValue && !props.modelValue) {
    internalValue.value = props.withTime ? now(getLocalTimeZone()) : today(getLocalTimeZone());
}
</script>

<template>
    <div :class="['flex', horizontal ? 'flex-row items-center gap-4' : 'flex-col', wrapperClass]">
        <Label v-if="label" :for="id" :class="[labelClass, horizontal ? 'mb-0 min-w-[100px]' : 'mb-2']">
            {{ label }}
        </Label>

        <div class="flex flex-1 flex-col">
            <div class="relative flex items-center">
                <Popover v-if="!disabled" v-model:open="isPopoverOpen">
                    <PopoverTrigger as-child>
                        <Button
                            variant="outline"
                            :class="cn('w-full justify-between text-left font-normal', !internalValue && 'text-muted-foreground', inputClass)"
                            :disabled="disabled"
                        >
                            {{ displayText }}
                            <div class="flex items-center gap-1" v-if="showIcon">
                                <Icon icon="lucide:calendar" class="size-3 text-muted-foreground" />
                            </div>
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0" :class="{ 'flex flex-col gap-1': withTime }">
                        <Calendar v-model="internalValue" initial-focus />

                        <!-- Time Picker -->
                        <div v-if="withTime" class="border-l p-3">
                            <div class="mb-2 flex items-center gap-2">
                                <div class="flex flex-col">
                                    <Label class="text-xs !font-normal">Hour</Label>
                                    <select
                                        :value="internalValue?.hour || 0"
                                        @change="updateTime(Number($event.target.value), internalValue?.minute || 0)"
                                        class="rounded border p-1"
                                    >
                                        <option v-for="h in hours" :key="h" :value="h">
                                            {{ h.toString().padStart(2, '0') }}
                                        </option>
                                    </select>
                                </div>
                                <span>:</span>
                                <div class="flex flex-col">
                                    <Label class="text-xs !font-normal">Minutes</Label>
                                    <select
                                        :value="internalValue?.minute || 0"
                                        @change="updateTime(internalValue?.hour || 0, Number($event.target.value))"
                                        class="rounded border p-1"
                                    >
                                        <option v-for="m in minutes" :key="m" :value="m">
                                            {{ m.toString().padStart(2, '0') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-between text-sm">
                                <button v-if="!internalValue" @click="internalValue = now(getLocalTimeZone())" class="text-primary">Now</button>
                                <button v-if="internalValue" @click="updateTime(0, 0)" class="text-primary">Reset</button>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>

                <Button v-else variant="outline" disabled :class="cn('w-full justify-between text-left font-normal text-muted-foreground', inputClass)">
                    {{ displayText }}
                    <div v-if="showIcon" class="flex items-center gap-1" >
                        <Icon icon="lucide:calendar" class="size-3" />
                    </div>
                </Button>
            </div>
        </div>

        <div v-if="help || error" class="flex flex-col">
            <InputHelpText class="mt-2" :message="help" />
            <InputError class="mt-2" :message="error" />
        </div>
    </div>
</template>
