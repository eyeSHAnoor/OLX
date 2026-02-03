import { ref } from 'vue';

interface ToastOptions {
    title: string;
    description?: string;
    duration?: number; // in ms
}

interface ToastItem extends ToastOptions {
    id: number;
}

const toasts = ref<ToastItem[]>([]);

let toastId = 0;

export function useToast() {
    function toast(options: ToastOptions) {
        const id = toastId++;
        toasts.value.push({ id, ...options });

        // Remove automatically after duration (default 3s)
        setTimeout(() => {
            const index = toasts.value.findIndex(t => t.id === id);
            if (index !== -1) toasts.value.splice(index, 1);
        }, options.duration || 3000);
    }

    return {
        toasts,
        toast,
    };
}
