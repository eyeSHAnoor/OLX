<script setup lang="ts">
import { ref, computed } from 'vue'
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from '@/components/ui/select'

const props = defineProps<{
    id?: string
    label?: string
    error?: string
    modelValue: any
    items: any[]
    keyBy?: string
    searchableFields?: string[]
    placeholder?: string
    disabled?: boolean
    readonly?: boolean
    help?: string
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: any): void
}>()

const query = ref('')

const isObject = computed(() => typeof props.items[0] === 'object')

const selectedItem = computed(() => {
    if (!props.modelValue) return null
    if (!isObject.value) return props.modelValue
    return props.items.find(
        (item) => item[props.keyBy ?? ''] === props.modelValue
    )
})

const filteredItems = computed(() => {
    if (!query.value) return props.items

    return props.items.filter((item) => {
        const searchable = props.searchableFields?.length
            ? props.searchableFields
            : isObject.value
                ? Object.keys(item)
                : []

        const target = isObject.value
            ? searchable.map((f) => String(item[f] ?? '')).join(' ')
            : String(item)

        return target.toLowerCase().includes(query.value.toLowerCase())
    })
})

function select(value: any) {
    emit('update:modelValue', value)
}

function stopKeyboardPropagation(e: Event) {
    // stop events from bubbling to Radix/Select so typing doesn't trigger typeahead
    e.stopPropagation();
}
</script>


<template>
    <div class="flex flex-col gap-1 w-full">
        <Label v-if="label" :for="id">{{ label }}</Label>

        <Select
            :model-value="props.modelValue"
            :disabled="disabled"
            :name="id"
            @update:model-value="select"
        >
            <SelectTrigger class="w-full">
                <SelectValue :placeholder="placeholder">
                    <!-- custom selected slot -->
                    <slot name="selected" :item="selectedItem">
                        {{ isObject ? selectedItem?.label : selectedItem }}
                    </slot>
                </SelectValue>
            </SelectTrigger>

            <SelectContent>
                <!-- Search input -->
                <div class="px-2 py-1">
                    <input
                        v-model="query"
                        type="text"
                        placeholder="Search..."
                        class="w-full border rounded px-2 py-1 text-sm focus:outline-none"
                        @keydown="stopKeyboardPropagation"
                        @keyup="stopKeyboardPropagation"
                        @keypress="stopKeyboardPropagation"
                    />
                </div>

                <div v-if="filteredItems.length" class="max-h-60 overflow-y-auto">
                    <SelectItem
                        class=""
                        v-for="(item, i) in filteredItems"
                        :key="i"
                        :value="isObject ? item[props.keyBy ?? ''] : item"
                    >
                        <!-- put slot *inside* SelectItem, no as-child -->
                        <slot name="item" :item="item">
                            {{ isObject ? item.label : item }}
                        </slot>
                    </SelectItem>
                </div>
                <div v-else class="px-2 py-2 text-sm text-muted-foreground">
                    No results found
                </div>
            </SelectContent>
        </Select>

        <div v-if="help || error" class="flex flex-col">
            <InputHelpText class="mt-2" :message="help" />
            <InputError class="mt-2" :message="error" />
        </div>
    </div>
</template>

