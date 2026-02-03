<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';

import SettingsLayout from '@/layouts/settings/Layout.vue';

import { InertiaPageProps } from '@/types';
import { computed } from 'vue';

import Layout from '@/layouts/AppLayout.vue';
import AppButton from '@/components/Application/AppButton.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();
defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    user?: App.Data.UserData;
    config: [];
}

const page = usePage<PageProps>();
const user = computed(() => page.props.user);
const config = computed(() => page.props.config);

const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Account Settings', href: route('account.edit') },
        { label: 'Preference settings', href: route('notification-settings.edit') },
    ]);
});

const form = useForm({
    settings: user.value?.notificationSettings?.length
        ? page.props.user?.notificationSettings
        : config.value?.types.map((type) => ({
            type: type.key,
            methods: [],
            timing: config.value?.timings[0].key,
            frequency: config.value?.frequencies[0].key,
        })),
});

const toggleMethod = (rowIndex, methodKey) => {
    let row = form.settings[rowIndex];
    if (row.methods.includes(methodKey)) {
        row.methods = row.methods.filter((m) => m !== methodKey);
    } else {
        row.methods.push(methodKey);
    }
};

const submit = () => {
    form.post(route('notification-settings.update'), {
        preserveScroll: true,
        // onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <AppContainer>

        <Head title="Notification Setting" />

        <PageHeading>
            <template #title>{{ $t("settings.noti_setting.title") }}</template>
        </PageHeading>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title='$t("settings.noti_setting.title")'
                    :description='$t("settings.noti_setting.description")' />

                <form @submit.prevent="submit">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="">{{ $t("settings.noti_setting.table.type") }}</TableHead>
                                <TableHead class="">{{ $t("settings.noti_setting.table.methods") }}</TableHead>
                                <TableHead class="">{{ $t("settings.noti_setting.table.timing") }}</TableHead>
                                <TableHead class="">{{ $t("settings.noti_setting.table.frequency") }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(row, index) in form.settings" :key="row.type" class="border-t">
                                <!-- Type -->
                                <TableCell class="px-4 py-2 font-medium">
                                    {{config.types.find((t) => t.key === row.type)?.label}}
                                    <input type="hidden" v-model="row.type" />
                                </TableCell>

                                <!-- Methods -->
                                <TableCell class="px-4 py-2">
                                    <!--                                <label v-for="(method) in config.methods" :key="method.key" class="mr-3">-->
                                    <!--                                    <input-->
                                    <!--                                        type="checkbox"-->
                                    <!--                                        :value="method.key"-->
                                    <!--                                        :checked="row.methods.includes(method.key)"-->
                                    <!--                                        @change="toggleMethod(index, method.key)"-->
                                    <!--                                    />-->
                                    <!--                                    <span class="ml-1">{{ method.label }}</span>-->
                                    <!--                                </label>-->

                                    <div class="grid gap-y-2">
                                        <Label v-for="method in config.methods" :key="method.key"
                                            :for="`methods-${method.key}-${index}`" class="flex items-center gap-x-2">
                                            <Checkbox :id="`methods-${method.key}-${index}`"
                                                :model-value="row.methods.includes(method.key)"
                                                @update:model-value="toggleMethod(index, method.key)" />
                                            <span>{{ method.label }}</span>
                                        </Label>
                                    </div>
                                </TableCell>

                                <!-- Timing -->
                                <TableCell class="px-4 py-2">
                                    <SelectInput :id="`timing-${index}`" v-model="row.timing"
                                        placeholder="Select Timing">
                                        <SelectContent>
                                            <SelectItem v-for="t in config.timings" :value="t.key" :key="t.key">
                                                {{ t.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </SelectInput>
                                </TableCell>

                                <!-- Frequency -->
                                <TableCell class="px-4 py-2">
                                    <SelectInput :id="`frequency-${index}`" v-model="row.frequency"
                                        placeholder="Select Frequency">
                                        <SelectContent>
                                            <SelectItem v-for="f in config.frequencies" :value="f.key" :key="f.key">
                                                {{ f.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </SelectInput>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div class="mt-5 flex items-center gap-4">
                        <AppButton :disabled="form.processing" :label='$t("settings.noti_setting.actions.save")' />

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                {{ $t("settings.noti_setting.actions.saved") }}</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppContainer>
</template>
