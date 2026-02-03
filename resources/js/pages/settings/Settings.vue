<script setup lang="ts">
import { InertiaPageProps } from '@/types';
import { InertiaForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppButtonSave from '@/components/Application/AppButtonSave.vue';
import Layout from '@/layouts/AppLayout.vue';
import InactveSessionLogout from '@/pages/settings/_partials/InactveSessionLogout.vue';
// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();
defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    settings: [];
}

const page = usePage<PageProps>();
const settings = computed(() => page.props.settings);

const taxForm = useForm({
    tax: settings.value?.tax ?? null,
});

const extraFeeLabelForm = useForm({
    extra_fee_labels: settings.value?.extra_fee_labels ?? [],
});

// const smsServiceForm = useForm({
//     sms_due_payment_reminder: settings.value?.sms_due_payment_reminder ?? false,
//     smd_otp: settings.value?.smd_otp ?? false,
//     sms_registration_confirmation: settings.value?.sms_registration_confirmation ?? false,
//     sms_login_credentials: settings.value?.sms_login_credentials ?? false,
//     sms_registration_status: settings.value?.sms_registration_status ?? false,
// });

const submit = (form: InertiaForm<any>) => {
    form.post(route('app-settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppContainer>

        <Head :title='$t("settings.app_settings.title")' />

        <PageHeading>{{ $t("settings.app_settings.heading") }}</PageHeading>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-7 lg:grid-cols-3">
            <Card class="">
                <CardHeader>
                    <CardTitle>{{ $t("settings.app_settings.tax.card_title") }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid">
                        <TextInput :label='$t("settings.app_settings.tax.label")'
                            :placeholder='$t("settings.app_settings.tax.placeholder")' v-model="taxForm.tax"
                            append-text="%" type="number" step="0.1" />
                    </div>
                </CardContent>
                <CardFooter class="flex justify-end px-6 pb-6">
                    <AppButton @click="submit(taxForm)" :processing="taxForm.processing" />
                </CardFooter>
            </Card>

            <Card class="sm:col-span-2">
                <CardHeader>
                    <CardTitle>{{ $t("settings.app_settings.extra_fee_labels.card_title") }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid">
                        <TagInput :label='$t("settings.app_settings.extra_fee_labels.label")'
                            :placeholder='$t("settings.app_settings.extra_fee_labels.placeholder")'
                            v-model="extraFeeLabelForm.extra_fee_labels">
                            <TagsInputItem v-for="(item, index) in extraFeeLabelForm.extra_fee_labels" :key="index"
                                :value="item">
                                <TagsInputItemText />
                                <TagsInputItemDelete />
                            </TagsInputItem>
                        </TagInput>
                    </div>
                </CardContent>
                <CardFooter class="flex justify-end px-6 pb-6">
                    <AppButton @click="submit(extraFeeLabelForm)" :processing="extraFeeLabelForm.processing" />
                </CardFooter>
            </Card>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-7 lg:grid-cols-4">
            <InactveSessionLogout />
        </div>
        <!--        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-7 lg:grid-cols-3 mt-5">-->
        <!--            <Card class="">-->
        <!--                <CardHeader>-->
        <!--                    <CardTitle>SMS Service</CardTitle>-->
        <!--                </CardHeader>-->
        <!--                <CardContent>-->

        <!--                    <div class="grid divide-y-2 divide-muted">-->
        <!--                        <div class="flex items-center justify-between gap-x-4 py-3">-->
        <!--                            <Label class="!text-lg !font-normal" for="sms_due_payment_reminder">Payment Due Alert</Label>-->
        <!--                            <ToggleInput id="sms_due_payment_reminder" v-model="smsServiceForm.sms_due_payment_reminder" />-->
        <!--                        </div>-->

        <!--                        <div class="flex items-center justify-between gap-x-4 py-3">-->
        <!--                            <Label class="!text-lg !font-normal" for="smd_otp">Send OTP</Label>-->
        <!--                            <ToggleInput id="smd_otp" v-model="smsServiceForm.smd_otp" />-->
        <!--                        </div>-->

        <!--                        <div class="flex items-center justify-between gap-x-4 py-3">-->
        <!--                            <Label class="!text-lg !font-normal" for="sms_registration_confirmation">Confirm Registration</Label>-->
        <!--                            <ToggleInput id="sms_registration_confirmation" v-model="smsServiceForm.sms_registration_confirmation" />-->
        <!--                        </div>-->

        <!--                        <div class="flex items-center justify-between gap-x-4 py-3">-->
        <!--                            <Label class="!text-lg !font-normal" for="sms_login_credentials">LMS Login Info</Label>-->
        <!--                            <ToggleInput id="sms_login_credentials" v-model="smsServiceForm.sms_login_credentials" />-->
        <!--                        </div>-->

        <!--                        <div class="flex items-center justify-between gap-x-4 py-3">-->
        <!--                            <Label class="!text-lg !font-normal" for="sms_registration_status">Registration Status</Label>-->
        <!--                            <ToggleInput id="sms_registration_status" v-model="smsServiceForm.sms_registration_status" />-->
        <!--                        </div>-->
        <!--                    </div>-->

        <!--                </CardContent>-->
        <!--                <CardFooter class="flex justify-end px-6 pb-6">-->
        <!--                    <AppButton @click="submit(smsServiceForm)" :processing="smsServiceForm.processing" />-->
        <!--                </CardFooter>-->
        <!--            </Card>-->
        <!--        </div>-->
    </AppContainer>
</template>
