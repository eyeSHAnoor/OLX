<script setup lang="ts">
import { InertiaPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppButtonSave from '@/components/Application/AppButtonSave.vue';
import Layout from '@/layouts/AppLayout.vue';
// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();
defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    settings: [];
}

const page = usePage<PageProps>();
const settings = computed(() => page.props.settings);

const form = useForm({
    enabled_inactive_session_logout: settings.value?.enabled_inactive_session_logout ?? null,
    inactive_session_time: settings.value?.inactive_session_time ?? null,
});

const submit = () => {
    form.post(route('app-settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Card class="sm:col-span-2">
        <CardHeader>
            <CardTitle>{{ $t("settings.inactive_session.title") }}</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="grid">
                <div class="flex items-center justify-between gap-x-4 py-3">
                    <Label class="!text-lg !font-normal" for="sms_due_payment_reminder">{{
                        $t("settings.inactive_session.enabled_label") }}</Label>
                    <ToggleInput id="sms_due_payment_reminder" v-model="form.enabled_inactive_session_logout" />
                </div>

                <TextInput v-if="form.enabled_inactive_session_logout"
                    :label='$t("settings.inactive_session.time_label")' id="inactive_session_time"
                    :placeholder='$t("settings.inactive_session.time_placeholder")' v-model="form.inactive_session_time"
                    type="number" step="1" wrapper-class="mt-3" />
            </div>
        </CardContent>
        <CardFooter class="flex justify-end px-6 pb-6">
            <AppButton @click="submit" :processing="form.processing" />
        </CardFooter>
    </Card>
</template>
