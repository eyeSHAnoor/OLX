<script setup lang="ts">
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { Link } from '@inertiajs/vue3';

// interface BreadcrumbItemType {
//     title: string;
//     href?: string;
// }
//
// defineProps<{
//     breadcrumbs: BreadcrumbItemType[];
// }>();

import { useBreadcrumb } from '@/composables/useBreadcrumb'

const { breadcrumbs } = useBreadcrumb()

</script>

<template>
    <Breadcrumb v-if="breadcrumbs && breadcrumbs.length > 0">
        <BreadcrumbList>
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <BreadcrumbItem>
                    <template v-if="index === breadcrumbs.length - 1">
                        <BreadcrumbPage>{{ item.label }}</BreadcrumbPage>
                    </template>
                    <template v-else>
                        <BreadcrumbLink as-child>
                            <Link :href="item.href ?? '#'">{{ item.label }}</Link>
                        </BreadcrumbLink>
                    </template>
                </BreadcrumbItem>
                <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" />
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
