<script setup lang="ts">
import { ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: File | null; // only file object
        previewUrl?: string; // optional initial url (for already uploaded)
        placeholder?: string;
        allowSelectFile?: boolean;
        imgClass?: string;

    }>(),
    {
        allowSelectFile: true,
    },
);
const emit = defineEmits<{
    (e: 'update:modelValue', value: File | null): void;
    (e: 'cleared'): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const preview = ref<string>(props.previewUrl ?? '');

// update preview if modelValue changes
watch(
    () => props.modelValue,
    (val) => {
        if (val instanceof File) {
            const reader = new FileReader();
            reader.onload = () => (preview.value = reader.result as string);
            reader.readAsDataURL(val);
        } else if (!val && !props.previewUrl) {
            preview.value = '';
        }
    },
    { immediate: true },
);

const triggerFileDialog = () => fileInput.value?.click();

const handleFileInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files?.length) {
        const file = input.files[0];
        emit('update:modelValue', file);
    }
};

const clearFile = () => {
    emit('update:modelValue', null);
    emit('cleared');
};
</script>

<template>
    <div class="group relative size-32 border border-gray-300 overflow-hidden rounded-lg">
        <div>
            <img :src="preview || placeholder" :class="['h-full w-full object-contain', imgClass]" alt="Preview" />
        </div>

        <!--        hover only display -->
        <div class="absolute top-0 right-0">
            <div class="">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="secondary" size="xs" class="size-5 !rounded-full">
                            <Icon icon="lucide:ellipsis-vertical" class="size-5" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-32">
                        <DropdownMenuGroup>
                            <DropdownMenuItem v-if="allowSelectFile" @click="triggerFileDialog">
                                <Icon icon="lucide:image-up" class="size-3.5" />
                                <span class="text-xs">Select Image</span>
                                <!-- Hidden file input -->
                                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileInput" />
                            </DropdownMenuItem>

                            <DropdownMenuSeparator v-if="allowSelectFile" />

                            <DropdownMenuItem @click="clearFile">
                                <Icon icon="lucide:trash-2" class="size-3.5" />
                                <span class="text-xs">Delete</span>
                            </DropdownMenuItem>
                        </DropdownMenuGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

        </div>
    </div>
</template>
