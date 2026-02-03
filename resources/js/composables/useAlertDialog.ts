import { ref } from 'vue'

type AlertType = 'confirm' | 'alert'

type AlertOptions = {
    type?: AlertType
    title: string
    description: string | VNode | (() => string | VNode)
    confirmText?: string
    cancelText?: string
    icon?: string
}

const isOpen = ref(false)
const options = ref<AlertOptions>({
    type: 'confirm',
    title: '',
    description: '',
    confirmText: 'Continue',
    cancelText: 'Cancel',
    icon: ''
})

let resolvePromise: ((value: boolean) => void) | null = null

export function useAlertDialog() {
    const show = async (alertOptions: AlertOptions): Promise<boolean> => {
        options.value = {
            ...options.value,
            ...alertOptions,
            type: alertOptions.type ?? 'confirm', // default to 'confirm'
            confirmText: alertOptions.confirmText ?? (alertOptions.type === 'alert' ? 'OK' : 'Continue'),
            cancelText: alertOptions.cancelText ?? 'Cancel',
        }

        isOpen.value = true

        return new Promise((resolve) => {
            resolvePromise = resolve
        })
    }

    const onConfirm = () => {
        isOpen.value = false
        if (resolvePromise) {
            resolvePromise(true)
            resolvePromise = null
        }
    }

    const onCancel = () => {
        isOpen.value = false
        if (resolvePromise) {
            resolvePromise(false)
            resolvePromise = null
        }
    }

    return {
        isOpen,
        options,
        show,
        onConfirm,
        onCancel
    }
}
