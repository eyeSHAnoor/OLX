<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';

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
const businessLicense = computed(() => user.value?.files?.filter((f) => f.collection === 'business_license'));
// const mustVerifyEmail = computed(() => page.props.mustVerifyEmail);
// const status = computed(() => page.props.status);

const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Profile settings', href: route('profile.edit') },
    ]);
});

const form = useForm({
    // id: user.value?.profile?.id ?? '',
    // user_id: user.value?.profile?.user_id ?? '',
    company_name: user.value?.profile?.company_name ?? '',
    address: user.value?.profile?.address ?? '',
    phone_1: user.value?.profile?.phone_1 ?? '',
    phone_2: user.value?.profile?.phone_2 ?? '',
    contact_person: user.value?.profile?.contact_person ?? '',
    company_email: user.value?.profile?.company_email ?? '',

    business_license: null, // File input
});

const handleIconSelected = (data: { files: File[] }) => {
    if (data.files.length > 0) {
        form.business_license = data.files[0];
    }
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset('business_license'),
    });
};
</script>

<template>
    <AppContainer>

        <Head title="Profile Settings" />

        <PageHeading>
            <template #title>{{ $t("settings.profile.title") }}</template>
        </PageHeading>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title='$t("settings.profile.title")' :description='$t("settings.profile.description")' />

                <form @submit.prevent="submit">
                    <div class="grid gap-y-6">
                        <TextInput :label='$t("settings.profile.form.company_name")' id="company_name"
                            v-model="form.company_name" :error="form.errors.company_name" />
                        <TextInput :label='$t("settings.profile.form.address")' id="address" v-model="form.address"
                            :error="form.errors.address" />
                        <TextInput :label='$t("settings.profile.form.phone_1")' id="phone_1" v-model="form.phone_1"
                            :error="form.errors.phone_1" />
                        <TextInput :label='$t("settings.profile.form.phone_2")' id="phone_2" v-model="form.phone_2"
                            :error="form.errors.phone_2" />
                        <TextInput :label='$t("settings.profile.form.contact_person")' id="contact_person"
                            v-model="form.contact_person" :error="form.errors.contact_person" />
                        <TextInput :label='$t("settings.profile.form.company_email")' id="company_email"
                            v-model="form.company_email" :error="form.errors.company_email" />

                        <div class="grid gap-y-2">
                            <Label>{{ $t("settings.profile.form.business_license.label") }}</Label>
                            <div class="flex items-center gap-5">
                                <div class="w-1/2">
                                    <FileInput icon="lucide:file" @onFileSelected="handleIconSelected"
                                        :label='$t("settings.profile.form.business_license.upload_new")' />
                                </div>

                                <ul>
                                    <li v-for="file in businessLicense">
                                        <a class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            :href="file.file_url" target="_blank">
                                            {{ $t("settings.profile.form.business_license.uploaded") }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <AppButton :disabled="form.processing" :label='$t("settings.profile.actions.save")' />

                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                    {{ $t("settings.profile.actions.saved") }}</p>
                            </Transition>
                        </div>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppContainer>
</template>
