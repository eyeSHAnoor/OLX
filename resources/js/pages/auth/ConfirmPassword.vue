<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
// import { useI18n } from 'vue-i18n';

// const { t } = useI18n();
const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.confirm_password.title')" :description="$t('auth.confirm_password.description')">

        <Head :title="$t('auth.confirm_password.head_title')" />

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label for="password">{{ $t('auth.confirm_password.password.label') }}</Label>
                    <Input id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                        autocomplete="current-password" autofocus
                        :placeholder="$t('auth.confirm_password.password.placeholder')" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ $t('auth.confirm_password.button') }}
                    </Button>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
