<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();

import Layout from '@/layouts/AppLayout.vue';
defineOptions({ layout: Layout });

const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Account Settings', href: route('account.edit') },
        { label: 'Password Management', href: route('password.edit') },
    ]);
});




const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};
</script>

<template>
    <AppContainer>

        <Head :title='$t("settings.password_setting.title")' />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall :title='$t("settings.password_setting.heading")'
                    :description='$t("settings.password_setting.description")' />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password">{{ $t("settings.password_setting.fields.current_password")
                            }}</Label>
                        <Input id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                            type="password" class="mt-1 block w-full" autocomplete="current-password"
                            placeholder="Current password" />
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">{{ $t("settings.password_setting.fields.new_password") }}</Label>
                        <Input id="password" ref="passwordInput" v-model="form.password" type="password"
                            class="mt-1 block w-full" autocomplete="new-password" placeholder="New password" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">{{ $t("settings.password_setting.fields.confirm_password")
                            }}</Label>
                        <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                            class="mt-1 block w-full" autocomplete="new-password" placeholder="Confirm password" />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ $t("settings.password_setting.actions.save") }}</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                {{ $t("settings.password_setting.actions.saved") }}</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppContainer>
</template>
