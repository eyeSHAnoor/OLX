<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { InertiaPageProps } from '@/types';
import Layout from '@/layouts/AppLayout.vue';
import CategoryAccordion from './_partials/CategoryAccordion.vue';
import CategoryDetailsModal from './_partials/CategoryDetailsModal.vue';

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    categories: App.Data.CategoryData[];
    allCategories: App.Data.CategoryData[];
}

const page = usePage<PageProps>();
console.log(page.props);

const categories = computed(() => page.props.categories);
const allCategories = computed(() => page.props.allCategories);

const { handleShowModal, showModal, selectedItem } = useModal();

const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Categories', href: route('categories.index') },
    ]);
});
</script>

<template>
    <AppContainer>

        <Head title="Categories" />

        <PageHeading>
            <template #title>Categories</template>
            <template #subtitle>Manage your product categories hierarchy</template>
            <template #links>
                <div class="flex items-center gap-2">
                    <AppButton label="New Category" icon="radix-icons:plus-circled" size="sm"
                        @click="handleShowModal({})" />
                </div>
            </template>
        </PageHeading>

        <!-- Main Categories Table/List -->
        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Categories List</CardTitle>
                <CardDescription>
                    Total: {{ categories.length }} main categories
                </CardDescription>
            </CardHeader>
            <CardContent>
                <!-- Top-level categories accordion -->
                <div v-if="categories.length > 0" class="border rounded-lg">
                    <CategoryAccordion :categories="categories" :allCategories="allCategories" />
                </div>

                <!-- Empty state -->
                <div v-else class="py-12 text-center">
                    <div class="mx-auto w-24 h-24 rounded-full bg-muted flex items-center justify-center mb-4">
                        <Icon icon="lucide:folder" class="size-10 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-medium mb-2">No categories yet</h3>
                    <p class="text-sm text-muted-foreground mb-4">
                        Get started by creating your first category
                    </p>
                    <AppButton label="Create Category" icon="radix-icons:plus-circled" @click="handleShowModal({})" />
                </div>
            </CardContent>
        </Card>

        <!-- Additional Info Card -->
        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Category Management</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-blue-500"></div>
                            <span class="text-sm font-medium">Main Categories</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Top-level categories without parent
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-green-500"></div>
                            <span class="text-sm font-medium">Subcategories</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Categories with parent categories
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-purple-500"></div>
                            <span class="text-sm font-medium">Hierarchy</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Unlimited nesting levels supported
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Details Modal -->
        <CategoryDetailsModal v-model="showModal" :category="selectedItem" :allCategories="allCategories" />
    </AppContainer>
</template>