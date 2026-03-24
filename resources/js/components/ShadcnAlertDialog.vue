<!-- components/ShadcnAlertDialog.vue -->
<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ options.title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ options.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="onCancel">
                    {{ options.cancelText }}
                </AlertDialogCancel>
                <AlertDialogAction @click="onConfirm"
                    :class="options.type === 'destructive' ? 'bg-red-600 hover:bg-red-700' : ''">
                    {{ options.confirmText }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogCancel,
    AlertDialogAction,
} from '@/components/ui/alert-dialog'
import { useShadcnAlert } from '@/composables/useShadcnAlert'

const { isOpen, options, onConfirm, onCancel } = useShadcnAlert()
</script>