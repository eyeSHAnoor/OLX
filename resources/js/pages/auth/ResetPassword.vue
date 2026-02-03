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
interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.reset_password.title')" :description="$t('auth.reset_password.description')">

        <Head :title="$t('auth.reset_password.head_title')" />

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <!-- Email -->
                <div class="grid gap-2">
                    <Label for="email">{{ $t('auth.reset_password.fields.email.label') }}</Label>
                    <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email"
                        class="mt-1 block w-full" readonly />
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <Label for="password">{{ $t('auth.reset_password.fields.password.label') }}</Label>
                    <Input id="password" type="password" name="password" autocomplete="new-password"
                        v-model="form.password" class="mt-1 block w-full" autofocus
                        :placeholder="$t('auth.reset_password.fields.password.placeholder')" />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div class="grid gap-2">
                    <Label for="password_confirmation">
                        {{ $t('auth.reset_password.fields.password_confirmation.label') }}
                    </Label>
                    <Input id="password_confirmation" type="password" name="password_confirmation"
                        autocomplete="new-password" v-model="form.password_confirmation" class="mt-1 block w-full"
                        :placeholder="$t('auth.reset_password.fields.password_confirmation.placeholder')" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Button -->
                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ $t('auth.reset_password.button') }}
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
