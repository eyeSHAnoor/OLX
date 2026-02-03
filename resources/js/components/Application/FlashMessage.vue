<script setup lang="ts">
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner'
import 'vue-sonner/style.css' // vue-sonner v2 requires this import
import { type SharedData } from '@/types';

// const { toast } = useToast();

const page = usePage<SharedData>();

watch(
	() => [page.props.flash, page.props.error],
	() => {

        // console.log(newFlash);


		if (page.props.flash.success) {
            toast.success('Success', {
                description: page.props.flash.success,
            })
		}

		if (page.props.flash.error) {
            toast.error('Uh! Something went wrong.', {
                description: page.props.flash.error,
            })
		}

        if (page.props.errors && Object.keys(page.props.errors).length > 0) {
            const errorMessages = Object.values(page.props.errors).filter(Boolean);

            if (errorMessages.length === 1) {
                toast.error('Validation Error', {
                    description: String(errorMessages[0]),
                });
            } else if (errorMessages.length > 1) {
                toast.error('Multiple Validation Errors', {
                    description: 'More than one validation error occurred. Please check the form.',
                });
            }
        }

	},
	{ immediate: true }
);
</script>

<template>
	<Toaster position="top-right"  richColors  />
</template>
