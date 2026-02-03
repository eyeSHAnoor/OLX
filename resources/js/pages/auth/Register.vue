<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
// import { useI18n } from 'vue-i18n';

// const { t } = useI18n();
const form = useForm({
    name: '',
    email: '',
    password: '',
    phone: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase :title="$t('auth.register.title')" :description="$t('auth.register.description')">

        <Head :title="$t('auth.register.head_title')" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Name -->
                <div class="grid gap-2">
                    <Label for="name">{{ $t('auth.register.fields.name.label') }}</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name"
                        v-model="form.name" :placeholder="$t('auth.register.fields.name.placeholder')" />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div class="grid gap-2">
                    <Label for="email">{{ $t('auth.register.fields.email.label') }}</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email"
                        :placeholder="$t('auth.register.fields.email.placeholder')" />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <Label for="password">{{ $t('auth.register.fields.password.label') }}</Label>
                    <Input id="password" type="password" required :tabindex="3" autocomplete="new-password"
                        v-model="form.password" :placeholder="$t('auth.register.fields.password.placeholder')" />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div class="grid gap-2">
                    <Label for="password_confirmation">{{ $t('auth.register.fields.password_confirmation.label')
                        }}</Label>
                    <Input id="password_confirmation" type="password" required :tabindex="4" autocomplete="new-password"
                        v-model="form.password_confirmation"
                        :placeholder="$t('auth.register.fields.password_confirmation.placeholder')" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- Button -->
                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ $t('auth.register.button') }}
                </Button>
            </div>

            <!-- Already have an account -->
            <div class="text-center text-sm text-muted-foreground">
                {{ $t('auth.register.already_have_account') }}
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">
                    {{ $t('auth.register.login') }}
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
