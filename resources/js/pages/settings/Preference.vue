<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';

import SettingsLayout from '@/layouts/settings/Layout.vue';

import { InertiaPageProps } from '@/types';
import { computed } from 'vue';
// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();

import { wTrans } from 'laravel-vue-i18n';
import Layout from '@/layouts/AppLayout.vue';
defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    mustVerifyEmail: boolean;
    status?: string;
    user?: User.Data.UserData;
}

const page = usePage<PageProps>();
const user = computed(() => page.props.user);
// const businessLicense = computed(() => user.value?.files?.filter((f) => f.collection === 'business_license'));
// const mustVerifyEmail = computed(() => page.props.mustVerifyEmail);
// const status = computed(() => page.props.status);

const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Account Settings', href: route('account.edit') },
        { label: 'Preference settings', href: route('preferences.edit') },
    ]);
});

const form = useForm({
    language: user.value?.preferences?.language ?? '',
    timezone: user.value?.preferences?.timezone ?? '',
    date_format: user.value?.preferences?.date_format ?? '',
    currency: user.value?.preferences?.currency ?? '',
});


const submit = () => {
    form.post(route('preferences.update'), {
        preserveScroll: true,
        // onSuccess: () => form.reset(),
    });
};

const languages = [
    { value: 'en', label: wTrans("settings.preferences.language.en"), icon: 'twemoji:flag-us-outlying-islands' },
    { value: 'zh_CN', label: wTrans("settings.preferences.language.zh_CN"), icon: 'twemoji:flag-china' },
    { value: 'zh_TW', label: wTrans("settings.preferences.language.zh_TW"), icon: 'twemoji:flag-china' },
    { value: 'ja', label: wTrans("settings.preferences.language.ja"), icon: 'twemoji:flag-japan' },
]

</script>

<template>
    <AppContainer>

        <Head title="Preference Settings" />

        <PageHeading>
            <template #title>{{ $t("settings.preferences.title") }}</template>
        </PageHeading>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title='$t("settings.preferences.title")'
                    :description='$t("settings.preferences.description")' />

                <form @submit.prevent="submit">
                    <div class="grid gap-y-6">

                        <SelectInput :label='$t("settings.preferences.language.label")' id="language"
                            v-model="form.language" :error="form.errors.language"
                            :placeholder='$t("settings.preferences.languages.placeholder")'>
                            <SelectContent>
                                <SelectItem v-for="(item, index) in languages" :key="index" :value="item.value">
                                    <div class="flex items-center gap-1">
                                        <Icon :icon="item.icon" class="size-4" />
                                        <span>{{ item.label }}</span>
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </SelectInput>

                        <TextInput :label='$t("settings.preferences.timezone")' id="timezone" v-model="form.timezone"
                            :error="form.errors.timezone" />
                        <TextInput :label='$t("settings.preferences.date_format")' id="date_format"
                            v-model="form.date_format" :error="form.errors.date_format" />
                        <TextInput :label='$t("settings.preferences.currency")' id="currency" v-model="form.currency"
                            :error="form.errors.currency" />


                        <div class="flex items-center gap-4">
                            <AppButton :disabled="form.processing" :label='$t("settings.preferences.actions.save")' />

                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                    {{ $t("settings.preferences.actions.saved") }}</p>
                            </Transition>
                        </div>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppContainer>
</template>
