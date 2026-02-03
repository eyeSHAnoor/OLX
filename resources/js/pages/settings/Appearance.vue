<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import SettingsLayout from '@/layouts/settings/Layout.vue';
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
        { label: 'Appearance', href: route('appearance') },
    ]);
});


</script>

<template>
    <AppContainer>

        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall :title='$t("settings.app_setting.title")'
                    :description='$t("settings.app_setting.description")' />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppContainer>
</template>
