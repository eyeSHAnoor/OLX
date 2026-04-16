<script setup lang="ts">
import { InertiaPageProps } from '@/types';
import { usePage, router } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useBreadcrumb } from '@/composables/useBreadcrumb';
import PageContentForm from './_partials/PageContentForm.vue';

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    pageContent?: App.Data.PageContentData;
}

const page = usePage<PageProps>();
const pageContent = computed(() => page.props.pageContent);
const isEditing = computed(() => !!pageContent.value);

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Page Contents', href: route('page-contents.index') },
        { label: isEditing.value ? 'Edit' : 'Create', href: '#' }
    ]);
});

// Delete handler (only for edit mode)
const alert = useAlertDialog();
const destroy = async () => {
    if (!pageContent.value) return;

    const confirmed = await alert.show({
        title: 'Delete Page Content',
        description: `Are you sure you want to delete "${pageContent.value.page_key}"? This action cannot be undone.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
        variant: 'danger'
    });

    if (confirmed) {
        router.delete(route('page-contents.destroy', pageContent.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('page-contents.index'));
            },
        });
    }
};
</script>

<template>
    <AppContainer>

        <Head :title="isEditing ? `Edit: ${pageContent.page_key}` : 'Create Page Content'" />

        <!-- Page Header with Actions -->
        <div class="my-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ isEditing ? `Edit: ${pageContent.page_key}` : 'Create New Page Content' }}
                    </h1>
                    <p class="text-muted-foreground mt-2">
                        {{ isEditing ?
                            'Update the content and settings for this page.' : 'Define a new dynamic page content block.' }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <AppButton label="Cancel" variant="outline" @click="router.visit(route('page-contents.index'))" />
                    <AppButton v-if="isEditing" label="Delete" variant="danger" icon="lucide:trash-2"
                        @click="destroy" />
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <Card>
            <CardContent class="p-6">
                <PageContentForm :pageContent="pageContent" />
            </CardContent>
        </Card>
    </AppContainer>
</template>