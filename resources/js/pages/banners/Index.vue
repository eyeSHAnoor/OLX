<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage, router, Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import BannerForm from './_partials/BannerForm.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useBreadcrumb } from '@/composables/useBreadcrumb';
import useModal from '@/composables/useModal';
import { Icon } from '@iconify/vue';

defineOptions({ layout: Layout });

// Props and Page setup
const page = usePage<{
    banners: PaginatedData<App.Data.BannerData>;
    categories: App.Data.CategoryData[];
}>();

const banners = computed(() => page.props.banners);
const categories = computed(() => page.props.categories);

// Initialize search filter
const { form, reset, isFiltered } = useSearchFilter(route('banners.index'));

// Ensure form has proper structure
if (!form.value) {
    form.value = {
        filter: {
            global: ''
        },
        perPage: 10
    };
}

// Columns for data table
const columns = [
    { accessorKey: 'image', header: 'Image', sortable: false, mobileTitle: 'Image' },
    { accessorKey: 'title', header: 'Title', sortable: true, mobileTitle: 'Title' },
    { accessorKey: 'position', header: 'Position', sortable: true, mobileTitle: 'Position' },
    { accessorKey: 'category', header: 'Target Category', sortable: false, mobileTitle: 'Category' },
    { accessorKey: 'status', header: 'Status', sortable: true, mobileTitle: 'Status' },
    { accessorKey: 'date_range', header: 'Date Range', sortable: false, mobileTitle: 'Date Range' },
    { accessorKey: 'actions', header: '', sortable: false, mobileTitle: 'Actions' },
];

// Modal and breadcrumbs
const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Banners', href: route('banners.index') }
    ]);
});

// Helper functions
const getPositionBadgeClass = (position: string) => {
    const classes = {
        'homepage': 'bg-purple-100 text-purple-800',
        'category': 'bg-blue-100 text-blue-800',
        'sidebar': 'bg-green-100 text-green-800',
        'floating': 'bg-orange-100 text-orange-800'
    };
    return classes[position as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const getPositionIcon = (position: string) => {
    const icons = {
        'homepage': 'lucide:home',
        'category': 'lucide:layout-grid',
        'sidebar': 'lucide:sidebar',
        'floating': 'lucide:move'
    };
    return icons[position as keyof typeof icons] || 'lucide:image';
};

const formatDate = (date: string | null) => {
    if (!date) return 'No date';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const isBannerActive = (banner: any) => {
    const now = new Date();
    const startDate = banner.start_date ? new Date(banner.start_date) : null;
    const endDate = banner.end_date ? new Date(banner.end_date) : null;

    if (startDate && startDate > now) return 'upcoming';
    if (endDate && endDate < now) return 'expired';
    return banner.status ? 'active' : 'inactive';
};

// Delete handler
async function handleDeleteBanner(banner: App.Data.BannerData) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete Banner',
        description: `Are you sure you want to delete "${banner.title}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'danger'
    });

    if (confirmed) {
        router.delete(route('banners.destroy', banner.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: show success message
            }
        });
    }
}

// Toggle status handler
async function toggleStatus(banner: App.Data.BannerData) {
    router.patch(route('banners.toggle-status', banner.id), {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppContainer>

        <Head title="Banners" />

        <PageHeading>
            <template #title>Banners</template>
            <template #description>Manage your advertisement banners across the site</template>
            <template #links>
                <AppButton label="New Banner" icon="radix-icons:plus-circled" @click="handleShowModal({})" />
            </template>
        </PageHeading>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Banners</p>
                            <p class="text-2xl font-bold">{{ banners?.total || 0 }}</p>
                        </div>
                        <div class="p-3 bg-primary/10 rounded-full">
                            <Icon icon="lucide:images" class="size-6 text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Active</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{banners?.data?.filter(b => b.status && (!b.end_date || new Date(b.end_date) > new
                                    Date())).length || 0}}
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <Icon icon="lucide:check-circle" class="size-6 text-green-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Homepage</p>
                            <p class="text-2xl font-bold">{{banners?.data?.filter(b => b.position ===
                                'homepage').length || 0
                                }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <Icon icon="lucide:home" class="size-6 text-purple-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Category</p>
                            <p class="text-2xl font-bold">{{banners?.data?.filter(b => b.position ===
                                'category').length || 0
                                }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <Icon icon="lucide:layout-grid" class="size-6 text-blue-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="mt-6">
            <CardContent class="p-0">
                <AppDataTableNew :columns="columns" :data="banners?.data"
                    search-placeholder="Search by title, position..." v-model:search="form.filter.global"
                    :pagination-data="banners" v-model:perPage="form.perPage" @resetFilter="reset()"
                    :isFiltered="isFiltered">
                    <template #filters>
                        <!-- Position Filter -->
                        <SelectInput v-model="form.filter.position" placeholder="All Positions" class="w-40">
                            <SelectContent>
                                <SelectItem :value="null">All Positions</SelectItem>
                                <SelectItem value="homepage">Homepage</SelectItem>
                                <SelectItem value="category">Category</SelectItem>
                                <SelectItem value="sidebar">Sidebar</SelectItem>
                                <SelectItem value="floating">Floating</SelectItem>
                            </SelectContent>
                        </SelectInput>

                        <!-- Status Filter -->
                        <SelectInput v-model="form.filter.status" placeholder="All Status" class="w-36">
                            <SelectContent>
                                <SelectItem :value="null">All Status</SelectItem>
                                <SelectItem :value="true">Active</SelectItem>
                                <SelectItem :value="false">Inactive</SelectItem>
                            </SelectContent>
                        </SelectInput>
                    </template>

                    <!-- Image Column -->
                    <template #image-cell="{ row }">
                        <div class="flex items-center">
                            <div class="size-12 rounded-lg overflow-hidden border bg-gray-50">
                                <img v-if="row.original.image_url" :src="row.original.image_url"
                                    :alt="row.original.title" class="w-full h-full object-cover"
                                    @error="(e: any) => e.target.src = 'https://via.placeholder.com/48?text=No+Image'" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <Icon icon="lucide:image" class="size-6 text-gray-400" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Title Column -->
                    <template #title-cell="{ row }">
                        <div class="max-w-xs">
                            <p class="font-medium truncate">{{ row.original.title }}</p>
                            <p v-if="row.original.link" class="text-xs text-muted-foreground truncate">
                                <Icon icon="lucide:link" class="size-3 inline mr-1" />
                                {{ row.original.link }}
                            </p>
                        </div>
                    </template>

                    <!-- Position Column -->
                    <template #position-cell="{ row }">
                        <span
                            :class="['inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium', getPositionBadgeClass(row.original.position)]">
                            <Icon :icon="getPositionIcon(row.original.position)" class="size-3" />
                            {{ row.original.position.charAt(0).toUpperCase() + row.original.position.slice(1) }}
                        </span>
                    </template>

                    <!-- Category Column -->
                    <template #category-cell="{ row }">
                        <span v-if="row.original.target_category_id" class="text-sm">
                            {{ row.original.category?.name || 'Loading...' }}
                        </span>
                        <span v-else class="text-sm text-muted-foreground italic">All Categories</span>
                    </template>

                    <!-- Status Column -->
                    <template #status-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <button @click="toggleStatus(row.original)"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                :class="row.original.status ? 'bg-green-600' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                    :class="row.original.status ? 'translate-x-5' : 'translate-x-0.5'" />
                            </button>
                            <span class="text-sm" :class="row.original.status ? 'text-green-600' : 'text-gray-500'">
                                {{ row.original.status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </template>

                    <!-- Date Range Column -->
                    <template #date_range-cell="{ row }">
                        <div class="text-sm">
                            <div v-if="row.original.start_date || row.original.end_date">
                                <p v-if="row.original.start_date" class="text-xs">
                                    <span class="text-muted-foreground">From:</span> {{
                                        formatDate(row.original.start_date) }}
                                </p>
                                <p v-if="row.original.end_date" class="text-xs">
                                    <span class="text-muted-foreground">To:</span> {{ formatDate(row.original.end_date)
                                    }}
                                </p>
                            </div>
                            <span v-else class="text-xs text-muted-foreground">No date restrictions</span>

                            <!-- Status Badge -->
                            <span v-if="isBannerActive(row.original) !== 'active'"
                                class="inline-block mt-1 px-1.5 py-0.5 text-xs rounded-full" :class="{
                                    'bg-yellow-100 text-yellow-800': isBannerActive(row.original) === 'upcoming',
                                    'bg-red-100 text-red-800': isBannerActive(row.original) === 'expired',
                                    'bg-gray-100 text-gray-800': isBannerActive(row.original) === 'inactive'
                                }">
                                {{ isBannerActive(row.original) }}
                            </span>
                        </div>
                    </template>

                    <!-- Actions Column -->
                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:eye" tooltip="Preview"
                                @click="window.open(row.original.image_url, '_blank')" />
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit"
                                @click="handleShowModal(row.original)" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDeleteBanner(row.original)" />
                        </div>
                    </template>

                    <!-- Empty State -->
                    <template #empty>
                        <div class="text-center py-12">
                            <div class="flex justify-center mb-4">
                                <div class="p-4 bg-primary/10 rounded-full">
                                    <Icon icon="lucide:images" class="size-12 text-primary" />
                                </div>
                            </div>
                            <h3 class="text-lg font-medium mb-2">No banners found</h3>
                            <p class="text-muted-foreground mb-4">Get started by creating your first banner</p>
                            <AppButton label="Create Banner" icon="radix-icons:plus-circled"
                                @click="handleShowModal({})" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <!-- Banner Form Modal -->
        <BannerForm :banner="selectedItem" v-model="showModal" :categories="categories" />
    </AppContainer>
</template>