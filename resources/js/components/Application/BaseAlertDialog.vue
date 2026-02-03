<script setup lang="ts">
const { isOpen, options, onConfirm, onCancel } = useAlertDialog()

</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogContent class="z-[60]">
            <AlertDialogHeader>
                <AlertDialogTitle>{{ options.title }}</AlertDialogTitle>
                <AlertDialogDescription>

<!--                   <div class="flex items-center justify-center text-center flex-col">-->
<!--                       <Icon icon="mdi:tick-circle-outline" class="size-18 text-green-500" />-->
<!--                   </div>-->

                    {{ options.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="onCancel">
                    {{ options.cancelText }}
                </AlertDialogCancel>
                <AlertDialogAction @click="onConfirm">
                    {{ options.confirmText }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
