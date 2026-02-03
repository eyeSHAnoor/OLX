<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import SettingsLayout from '@/layouts/settings/Layout.vue';

import Layout from '@/layouts/AppLayout.vue';
import { InertiaPageProps } from '@/types';
import { computed } from 'vue';
// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();
defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    mustVerifyEmail: boolean;
    status?: string;
    user?: User.Data.UserData;
}

const page = usePage<PageProps>();
const user = computed(() => page.props.user);
const mustVerifyEmail = computed(() => page.props.mustVerifyEmail);
const status = computed(() => page.props.status);

const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Account settings', href: route('account.edit') },
    ]);
});

const form = useForm({
    name: user.value?.name,
    email: user.value?.email,
});

const submit = () => {
    form.patch(route('account.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppContainer>

        <Head title="Account Settings" />

        <PageHeading>
            <template #title>{{ $t("settings.acc_setting.heading") }}</template>
        </PageHeading>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title='$t("settings.acc_setting.title")'
                    :description='$t("settings.acc_setting.desc")' />

                <form @submit.prevent="submit" class="space-y-6">
                    <TextInput :label='$t("settings.acc_setting.labels.name")' id="name" v-model="form.name"
                        :error="form.errors.name" />

                    <TextInput :label='$t("settings.acc_setting.labels.email")' id="email" v-model="form.email"
                        :error="form.errors.email" />

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            {{ $t("settings.acc_setting.messages.err") }}
                            <Link :href="route('verification.send')" method="post" as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                            {{ $t("settings.acc_setting.messages.sol") }}
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            {{ $t("settings.acc_setting.messages.new") }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <AppButton :disabled="form.processing" :label='$t("settings.acc_setting.labels.save")' />

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppContainer>
</template>
