<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Label } from '@/components/ui/label';
import { ClockIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface TimeValue {
    hours: number;
    minutes: number;
    seconds?: number;
}

const props = defineProps<{
    defaultValue?: string | TimeValue | null;
    modelValue?: string | TimeValue | null;
    class?: HTMLAttributes['class'];
    id?: string;
    label?: string;
    error?: string;
    wrapperClass?: string;
    labelClass?: string;
    horizontal?: boolean;
    help?: string;
    disabled?: boolean;
    displayFormat?: string; // e.g., "HH:mm", "h:mm a", "HH:mm:ss"
    timeStep?: number; // Step for minutes selection (default: 5)
    withSeconds?: boolean; // Include seconds picker
    use12Hour?: boolean; // Use 12-hour format with AM/PM
    autoClose?: boolean; // Auto-close popover on time selection (default: false)
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string): void;
}>();

// Track popover state
const isPopoverOpen = ref(false);

// Parse time string to TimeValue
const parseTimeString = (timeStr: string): TimeValue | null => {
    if (!timeStr) return null;

    try {
        // Handle various time formats
        const time12HourRegex = /^(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)$/i;
        const time24HourRegex = /^(\d{1,2}):(\d{2})(?::(\d{2}))?$/;

        let match = timeStr.match(time12HourRegex);
        if (match) {
            let hours = parseInt(match[1]);
            const minutes = parseInt(match[2]);
            const seconds = match[3] ? parseInt(match[3]) : 0;
            const period = match[4].toUpperCase();

            if (period === 'PM' && hours !== 12) hours += 12;
            if (period === 'AM' && hours === 12) hours = 0;

            return { hours, minutes, seconds };
        }

        match = timeStr.match(time24HourRegex);
        if (match) {
            return {
                hours: parseInt(match[1]),
                minutes: parseInt(match[2]),
                seconds: match[3] ? parseInt(match[3]) : 0,
            };
        }

        return null;
    } catch (e) {
        console.error('Failed to parse time:', timeStr, e);
        return null;
    }
};

// Convert TimeValue to string
const timeValueToString = (timeValue: TimeValue | null): string => {
    if (!timeValue) return '';

    const { hours, minutes, seconds } = timeValue;

    // Ensure values are numbers
    const safeHours = typeof hours === 'number' ? hours : 0;
    const safeMinutes = typeof minutes === 'number' ? minutes : 0;
    const safeSeconds = typeof seconds === 'number' ? seconds : 0;

    if (props.use12Hour) {
        const period = safeHours >= 12 ? 'PM' : 'AM';
        const displayHours = safeHours === 0 ? 12 : safeHours > 12 ? safeHours - 12 : safeHours;
        const timeStr = `${displayHours}:${safeMinutes.toString().padStart(2, '0')}`;
        return props.withSeconds
            ? `${timeStr}:${safeSeconds.toString().padStart(2, '0')} ${period}`
            : `${timeStr} ${period}`;
    }

    const timeStr = `${safeHours.toString().padStart(2, '0')}:${safeMinutes.toString().padStart(2, '0')}`;
    return props.withSeconds
        ? `${timeStr}:${safeSeconds.toString().padStart(2, '0')}`
        : timeStr;
};

// Internal time value
const internalValue = computed<TimeValue | null>({
    get() {
        if (!props.modelValue) return null;

        if (typeof props.modelValue === 'string') {
            return parseTimeString(props.modelValue);
        }

        return props.modelValue;
    },
    set(value: TimeValue | null) {
        if (!value) {
            emits('update:modelValue', '');
            return;
        }

        const timeString = timeValueToString(value);
        emits('update:modelValue', timeString);

        // Only close if autoClose is enabled
        if (props.autoClose) {
            isPopoverOpen.value = false;
        }
    },
});

// Time selection options
const hours = props.use12Hour
    ? Array.from({ length: 12 }, (_, i) => i + 1)
    : Array.from({ length: 24 }, (_, i) => i);

const minutes = Array.from({ length: 60 / (props.timeStep || 5) }, (_, i) => i * (props.timeStep || 5));
const seconds = Array.from({ length: 60 }, (_, i) => i);

// Display text
const displayText = computed(() => {
    if (!internalValue.value) return 'Select time';

    if (props.displayFormat) {
        const { hours, minutes, seconds: secs } = internalValue.value;
        const pad = (num: number) => num.toString().padStart(2, '0');

        // Ensure values are numbers
        const safeHours = typeof hours === 'number' ? hours : 0;
        const safeMinutes = typeof minutes === 'number' ? minutes : 0;
        const safeSecs = typeof secs === 'number' ? secs : 0;

        const replacements: Record<string, string> = {
            HH: pad(safeHours),
            H: safeHours.toString(),
            hh: pad(safeHours === 0 ? 12 : safeHours > 12 ? safeHours - 12 : safeHours),
            h: (safeHours === 0 ? 12 : safeHours > 12 ? safeHours - 12 : safeHours).toString(),
            mm: pad(safeMinutes),
            m: safeMinutes.toString(),
            ss: pad(safeSecs),
            s: safeSecs.toString(),
            a: safeHours < 12 ? 'am' : 'pm',
            A: safeHours < 12 ? 'AM' : 'PM',
        };

        let result = props.displayFormat;
        for (const [key, value] of Object.entries(replacements)) {
            result = result.replace(key, value);
        }
        return result;
    }

    return timeValueToString(internalValue.value);
});

// Update time
const updateTime = (newHours?: number, newMinutes?: number, newSeconds?: number, period?: 'AM' | 'PM') => {
    const current = internalValue.value || { hours: 0, minutes: 0, seconds: 0 };

    let hours = newHours !== undefined ? newHours : current.hours;
    const minutes = newMinutes !== undefined ? newMinutes : current.minutes;
    const seconds = newSeconds !== undefined ? newSeconds : (current.seconds || 0);

    // Handle 12-hour format with AM/PM
    if (props.use12Hour && period) {
        if (period === 'PM' && hours !== 12) hours += 12;
        if (period === 'AM' && hours === 12) hours = 0;
    }

    internalValue.value = { hours, minutes, seconds };
};

// Set current time
const setCurrentTime = () => {
    const now = new Date();
    internalValue.value = {
        hours: now.getHours(),
        minutes: now.getMinutes(),
        seconds: props.withSeconds ? now.getSeconds() : 0,
    };

    // Close popover after setting current time if autoClose is enabled
    if (props.autoClose) {
        isPopoverOpen.value = false;
    }
};

// Reset time
const resetTime = () => {
    internalValue.value = { hours: 0, minutes: 0, seconds: 0 };

    // Close popover after reset if autoClose is enabled
    if (props.autoClose) {
        isPopoverOpen.value = false;
    }
};

// Handle initial default value
if (props.defaultValue && !props.modelValue) {
    const now = new Date();
    internalValue.value = {
        hours: now.getHours(),
        minutes: now.getMinutes(),
        seconds: props.withSeconds ? now.getSeconds() : 0,
    };
}
</script>

<template>
    <div :class="['flex', horizontal ? 'flex-row items-center gap-4' : 'flex-col', wrapperClass]">
        <Label v-if="label" :for="id" :class="[labelClass, horizontal ? 'mb-0 min-w-[100px]' : '']">
            {{ label }}
        </Label>

        <div class="flex-1 flex flex-col">
            <div class="relative flex items-center">
                <Popover v-if="!disabled" v-model:open="isPopoverOpen">
                    <PopoverTrigger as-child>
                        <Button
                            variant="outline"
                            :class="cn('w-full justify-between text-left font-normal', !internalValue && 'text-muted-foreground')"
                            :disabled="disabled">
                            {{ displayText }}
                            <ClockIcon class="size-3" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-4">
                        <div class="flex items-center gap-2 mb-4">
                            <!-- Hours -->
                            <div class="flex flex-col">
                                <Label class="text-xs mb-1">{{ props.use12Hour ? 'Hour' : 'Hours' }}</Label>
                                <select
                                    :value="props.use12Hour
                    ? (internalValue?.hours === 0 ? 12 : internalValue?.hours > 12 ? internalValue.hours - 12 : internalValue?.hours) || 12
                    : internalValue?.hours || 0"
                                    @change="updateTime(
                    props.use12Hour
                      ? (Number($event.target.value) === 12 ? 0 : Number($event.target.value))
                      : Number($event.target.value),
                    undefined,
                    undefined,
                    internalValue?.hours >= 12 ? 'PM' : 'AM'
                  )"
                                    class="p-2 border rounded min-w-[60px] text-sm">
                                    <option v-for="h in hours" :key="h" :value="h">
                                        {{ h.toString().padStart(2, '0') }}
                                    </option>
                                </select>
                            </div>

                            <span class="text-lg font-medium mt-5">:</span>

                            <!-- Minutes -->
                            <div class="flex flex-col">
                                <Label class="text-xs mb-1">Minutes</Label>
                                <select
                                    :value="internalValue?.minutes || 0"
                                    @change="updateTime(undefined, Number($event.target.value))"
                                    class="p-2 border rounded min-w-[60px] text-sm">
                                    <option v-for="m in minutes" :key="m" :value="m">
                                        {{ m.toString().padStart(2, '0') }}
                                    </option>
                                </select>
                            </div>

                            <!-- Seconds (if enabled) -->
                            <template v-if="withSeconds">
                                <span class="text-lg font-medium mt-5">:</span>
                                <div class="flex flex-col">
                                    <Label class="text-xs mb-1">Seconds</Label>
                                    <select
                                        :value="internalValue?.seconds || 0"
                                        @change="updateTime(undefined, undefined, Number($event.target.value))"
                                        class="p-2 border rounded min-w-[60px] text-sm">
                                        <option v-for="s in seconds" :key="s" :value="s">
                                            {{ s.toString().padStart(2, '0') }}
                                        </option>
                                    </select>
                                </div>
                            </template>

                            <!-- AM/PM (if 12-hour format) -->
                            <div v-if="use12Hour" class="flex flex-col">
                                <Label class="text-xs mb-1">Period</Label>
                                <select
                                    :value="(internalValue?.hours || 0) >= 12 ? 'PM' : 'AM'"
                                    @change="updateTime(undefined, undefined, undefined, $event.target.value as 'AM' | 'PM')"
                                    class="p-2 border rounded text-sm">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex justify-between text-sm">
                            <button @click="setCurrentTime" class="text-primary hover:underline">
                                Now
                            </button>
                            <button v-if="internalValue" @click="resetTime" class="text-primary hover:underline">
                                Reset
                            </button>
                        </div>
                    </PopoverContent>
                </Popover>

                <Button
                    v-else
                    variant="outline"
                    disabled
                    :class="cn('w-full justify-between text-left font-normal text-muted-foreground')">
                    {{ displayText }}
                    <ClockIcon class="size-4" />
                </Button>
            </div>

            <div v-if="help || error" class="flex flex-col">
                <InputHelpText class="mt-2" :message="help" />
                <InputError class="mt-2" :message="error" />
            </div>
        </div>


    </div>
</template>
