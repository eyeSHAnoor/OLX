<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { onMounted, ref, HTMLAttributes } from 'vue';

const props = defineProps<{
	defaultValue?: string | number | null;
	modelValue?: string | number | null;
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
		<Label :for="id" :label-class="labelClass">{{ label }}</Label>

		<div class="relative">
			<Input ref="input"  v-bind="$attrs" :id="id" :name="id" class="!pl-9" v-model="modelValue" />

			<div class="absolute top-1.5 left-0 inline-flex items-center px-3">
				<svg
					xmlns="http://www.w3.org/2000/svg"
					class="size-4 text-muted-foreground"
					viewBox="0 0 24 24"
					stroke-width="2"
					stroke="currentColor"
					fill="none"
					stroke-linecap="round"
					stroke-linejoin="round">
					<rect x="0" y="0" width="24" height="24" stroke="none"></rect>
					<circle cx="10" cy="10" r="7"></circle>
					<line x1="21" y1="21" x2="15" y2="15"></line>
				</svg>
			</div>
		</div>

        <div v-if="help || error" class="flex flex-col">
            <InputHelpText class="mt-2" :message="help" />
            <InputError class="mt-2" :message="error" />
        </div>
	</div>
</template>
