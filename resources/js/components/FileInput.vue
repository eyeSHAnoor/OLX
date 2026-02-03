<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';
import { ref, watch } from 'vue';

interface Props {
    defaultValue?: string | number | null;
    modelValue?: string | number | null;
    class?: HTMLAttributes['class'];
    id?: string;
    label?: string;
    error?: string;
    wrapperClass?: string;
    labelClass?: string;
    inputClass?: string;
    horizontal?: boolean;
    fileName?: string;
    icon?: string;
    help?: string;
    multiple?: boolean;
    accept?: string;
    showSelectedFiles?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    icon: 'akar-icons:attach',
    multiple: false,
    showSelectedFiles: true,
});

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void;
    (e: 'onFileSelected', payload: { files: File[], previews: string[] }): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});

const selectedFiles = ref<File[]>([]);
const previewUrls = ref<string[]>([]);
const file = ref<HTMLInputElement | null>(null);

const createImagePreview = (file: File): Promise<string> => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            resolve(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    });
};

const handleFileSelected = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFiles.value = Array.from(target.files);

        // Create previews for image files
        const previews: string[] = [];
        for (const file of selectedFiles.value) {
            if (file.type.startsWith('image/')) {
                const preview = await createImagePreview(file);
                previews.push(preview);
            } else {
                previews.push(''); // Empty string for non-image files
            }
        }
        previewUrls.value = previews;

        // Emit both files and previews
        emits('onFileSelected', {
            files: selectedFiles.value,
            previews: previewUrls.value
        });
    }
};

const dragover = (event: DragEvent) => {
    event.preventDefault();
    const target = event.currentTarget as HTMLElement;
    if (!target.classList.contains('bg-primary-300')) {
        target.classList.remove('bg-secondary');
        target.classList.add('bg-primary-foreground');
    }
};

const dragleave = (event: DragEvent) => {
    event.preventDefault();
    const target = event.currentTarget as HTMLElement;
    target.classList.add('bg-secondary');
    target.classList.remove('bg-primary-foreground');
};

const drop = async (event: DragEvent) => {
    event.preventDefault();
    const target = event.currentTarget as HTMLElement;
    if (event.dataTransfer && event.dataTransfer.files.length > 0) {
        selectedFiles.value = Array.from(event.dataTransfer.files);

        // Create previews for dropped files
        const previews: string[] = [];
        for (const file of selectedFiles.value) {
            if (file.type.startsWith('image/')) {
                const preview = await createImagePreview(file);
                previews.push(preview);
            } else {
                previews.push('');
            }
        }
        previewUrls.value = previews;

        // Emit both files and previews
        emits('onFileSelected', {
            files: selectedFiles.value,
            previews: previewUrls.value
        });
    }

    // Clean up
    target.classList.add('bg-secondary');
    target.classList.remove('bg-primary-foreground');
};
</script>

<template>
    <div
        :class="[
            'group min-h-14 cursor-pointer w-full text-3xl px-2 py-3 relative appearance-none border border-secondary border-solid rounded-md hover:shadow-outline-gray hover:bg-primary bg-muted flex items-center justify-center',
            wrapperClass,
        ]"
        @dragover="dragover"
        @dragleave="dragleave"
        @drop="drop">
        <Input
            type="file"
            v-bind="$attrs"
            :name="id"
            :id="id"
            :multiple="multiple"
            :accept="accept"
            class="absolute inset-0 z-50 m-0 p-0 w-full h-full outline-none opacity-0"
            @change="handleFileSelected"
            ref="file" />

        <div class="flex gap-x-4 items-center justify-center">
            <div v-if="selectedFiles?.length && showSelectedFiles" class="flex flex-col gap-1 divide-y-2">
                <div v-for="file in selectedFiles" class="flex items-center gap-x-2 py-1">
                    <Icon icon="radix-icons:file-text" class="size-5 text-green-600 group-hover:text-white" />
                    <p class="text-sm group-hover:text-white">{{ file.name }}</p>
                </div>
            </div>

            <div v-else class="flex items-center gap-x-2">
                <Icon :icon="icon" class="size-5 text-muted-foreground group-hover:text-white" />
                <p class="font-medium text-sm group-hover:text-white">{{ label }}</p>
            </div>
        </div>
    </div>
</template>
