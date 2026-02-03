// composables/useFileInput.ts
import { ref, type Ref } from 'vue';

export function useFileInput(options?: {
    multiple?: boolean;
    onChange?: (files: FileList) => void;
}) {
    const fileInput = ref<HTMLInputElement | null>(null);
    const previewUrls = ref<string[]>([]);
    const selectedFiles = ref<FileList | null>(null);

    const trigger = () => fileInput.value?.click();

    const clear = () => {
        previewUrls.value = [];
        selectedFiles.value = null;
    };

    const handleChange = (e: Event) => {
        const input = e.target as HTMLInputElement;
        const files = input.files;
        if (!files || files.length === 0) return;

        selectedFiles.value = files;
        previewUrls.value = [];

        Array.from(files).forEach((file) => {
            const reader = new FileReader();
            reader.onload = () => {
                previewUrls.value.push(reader.result as string);
            };
            reader.readAsDataURL(file);
        });

        options?.onChange?.(files);
    };

    return {
        fileInput,
        trigger,
        clear,
        handleChange,
        previewUrls,
        selectedFiles,
    };
}
