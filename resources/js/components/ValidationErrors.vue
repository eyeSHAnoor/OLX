<script setup>
const errors = computed(() => usePage().props.errors);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);

const show = ref(false);

watch(
    () => hasErrors,
    async () => {
        show.value = true;
        // setTimeout(() => {
        // 	show.value = false;
        // }, 5000);
    },
    { deep: true },
);
</script>

<template>
    <transition leave-active-class="duration-300" leave-to-class="opacity-0">
        <Alert v-if="show && hasErrors" variant="destructive">
            <Icon icon="lucide:triangle-alert" class="size-4" />
            <AlertTitle>Oh! Something went wrong.</AlertTitle>
            <AlertDescription>
                <ul class="mt-3 list-inside list-disc text-sm">
                    <li v-for="(error, key) in errors" :key="key">
                        {{ error }}
                    </li>
                </ul>
            </AlertDescription>
        </Alert>
    </transition>
</template>
