<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';
import { Switch } from '@/components/ui/switch'

const props = defineProps<{
	defaultValue?: boolean | string | number | null;
	modelValue?: boolean | string | number | null;
	class?: HTMLAttributes['class'];
	id?: string;
	label?: string;
	error?: string;
	wrapperClass?: string;
	labelClass?: string;
	inputClass?: string;
	horizontal?: boolean;
	help?: string;
    checkedColor?: string;
    uncheckedColor?: string;
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
	<div :class="['flex', horizontal ? 'flex-row' : 'flex-col', wrapperClass]">
		<div class="flex items-center space-x-2">
			<Switch :id="id" v-bind="$attrs" v-model="modelValue" :checkedColor :uncheckedColor />
			<Label :for="id" :class="['!text-input-foreground !font-normal leading-none', labelClass]">{{ label }}</Label>
		</div>
		<p v-if="help" class="text-light text-xs mt-2 ml-1">{{ help }}</p>
		<InputError class="mt-2" :message="error" />
	</div>
</template>
