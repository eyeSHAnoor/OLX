<script setup lang="ts">
import { type HTMLAttributes, ref, watch } from 'vue';

const props = defineProps<{
    defaultValue?: string | null;
    modelValue?: string | null;
    class?: HTMLAttributes['class'];
    id?: string;
    label?: string;
    error?: string;
    wrapperClass?: string;
    labelClass?: string;
    inputClass?: string;
    horizontal?: boolean;
    prependText?: string;
    placeholder?: string;
    help?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const daysInMonth = (month: number): number => {
    const date = new Date(2000, month + 1, 0); // year is irrelevant
    return date.getDate();
};

const selectedMonth = ref<number | null>(null); // 0-based
const selectedDay = ref<number | null>(null);
const showPicker = ref(false);

// Step control: true = picking month, false = picking day
const isMonthStep = ref(true);

// Handle picker open
watch(showPicker, (val) => {
    if (val) {
        // Always go to month picker first
        isMonthStep.value = true;
        selectedDay.value = null;
    }
});

// Pre-populate from modelValue (e.g., '15-08')
watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            const [day, month] = val.split('-').map(Number);
            selectedMonth.value = (month || 1) - 1;
            selectedDay.value = day || 1;
        }
    },
    { immediate: true },
);

// Emit final formatted value
watch([selectedMonth, selectedDay], ([month, day]) => {
    if (month !== null && day !== null) {
        const formatted = `${String(day).padStart(2, '0')}-${String(month + 1).padStart(2, '0')}`;
        emit('update:modelValue', formatted);
        showPicker.value = false;
    }
});
</script>

<template>
    <div :class="['flex', horizontal ? 'flex-row items-center gap-4' : 'flex-col', wrapperClass]">
        <Label v-if="label" :for="id" :class="[labelClass, horizontal ? 'mb-0 min-w-[100px]' : 'mb-2']">
            {{ label }}
        </Label>

        <div class="relative inline-block">
            <!-- Trigger Button -->
            <button
                type="button"
                :class="['w-full rounded border bg-white px-4 py-2 text-left shadow-sm', inputClass]"
                @click="showPicker = !showPicker"
                :disabled="disabled"
            >
                <span class="flex items-center justify-between">
                    <span v-if="modelValue">{{ modelValue }}</span>
                    <span v-else class="text-gray-400">{{ placeholder }}</span>
                    <Icon icon="lucide:calendar" class="size-3" />
                </span>
            </button>

            <!-- Dropdown -->
            <div v-if="showPicker" class="absolute z-10 mt-2 w-48 space-y-2 rounded border bg-white p-2 shadow-lg">
                <!-- Month Picker -->
                <div v-if="isMonthStep">
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="(month, index) in months"
                            :key="index"
                            class="rounded border px-2 py-1 text-sm hover:bg-blue-100"
                            :class="{ 'bg-primary text-white': selectedMonth === index }"
                            @click="
                                () => {
                                    selectedMonth = index;
                                    isMonthStep = false;
                                }
                            "
                        >
                            {{ month.slice(0, 3) }}
                        </button>
                    </div>
                </div>

                <!-- Day Picker -->
                <div v-else>
                    <div class="mb-2 flex items-center justify-between">
                        <button class="text-sm text-blue-600" @click="isMonthStep = true">← Back</button>
                        <span class="text-sm font-semibold">{{ months[selectedMonth!] }}</span>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        <button
                            v-for="day in daysInMonth(selectedMonth!)"
                            :key="day"
                            class="flex h-8 w-8 items-center justify-center rounded border text-sm hover:bg-blue-100"
                            :class="{ 'bg-primary text-white': selectedDay === day }"
                            @click="selectedDay = day"
                        >
                            {{ day }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="help || error" class="flex flex-col">
            <InputHelpText class="mt-2" :message="help" />
            <InputError class="mt-2" :message="error" />
        </div>
    </div>
</template>
