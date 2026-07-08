<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import usePermissions from '@/composables/usePermissions';

const { hasRole } = usePermissions();

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user as App.Data.UserData);

// Single array of groups + items
const navItems = computed(() => [
    {
        label: 'System',
        items: [
            {
                visible: true,
                title: 'Dashboard',
                href: '/dashboard',
                icon: 'material-symbols:dashboard-outline',
                isActive: page.url === '/dashboard' || route().current('home') || route().current('dashboard'),
            },
            {
                visible: true,
                title: 'Users',
                href: '/users',
                icon: 'material-symbols:group-outline',
                isActive: page.url === '/users' || route().current('users.*'),
            },
            {
                visible: true,
                title: 'Broadcast Messages',
                href: '/broadcast-messages',
                icon: 'material-symbols:campaign',
                isActive: page.url === '/broadcast-messages' || route().current('broadcast-messages.*'),
            },
            {
                visible: true,
                title: 'Plans',
                href: '/plans',
                icon: 'material-symbols:inventory-2-outline',
                isActive: page.url === '/plans' || route().current('plans.*'),
            },
            {
                visible: true,
                title: 'Category',
                href: '/categories',
                icon: 'material-symbols:category-outline',
                isActive: page.url === '/categories' || route().current('categories.*'),
            },
            {
                visible: true,
                title: 'Brands',
                href: '/brands',
                icon: 'material-symbols:badge-outline',
                isActive: page.url === '/brands' || route().current('brands.*'),
            },
            {
                visible: true,
                title: 'Banners',
                href: '/banners',
                icon: 'material-symbols:view-carousel-outline',
                isActive: page.url === '/banners' || route().current('banners.*'),
            },
            {
                visible: true,
                title: 'Ads',
                href: '/ads',
                icon: 'material-symbols:inventory-2-outline',
                isActive: page.url === '/ads' || route().current('ads.*'),
            },
            {
                visible: true,
                title: 'Reports',
                href: '/reports',
                icon: 'material-symbols:analytics-outline',
                isActive: page.url === '/reports' || route().current('reports.*'),
            },
            {
                visible: true,
                title: 'Pages',
                href: '/page-contents',
                icon: 'material-symbols:docs-outline',
                isActive: page.url === '/page-contents' || route().current('page-contents.*'),
            },
            {
                visible: true,
                title: 'Campaigns',
                href: '/gift-campaigns',
                icon: 'material-symbols:celebration-outline',
                isActive: page.url === '/gift-campaigns' || route().current('gift-campaigns.*'),
            },
            {
                visible: true,
                title: 'Referrals',
                href: '/referrals',
                icon: 'material-symbols:group-outline',
                isActive: page.url === '/referrals' || route().current('referrals.*'),
            },
        ],
    },
]);
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <template v-for="(group, gIndex) in navItems" :key="gIndex">
            <!--            <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>-->
            <SidebarMenu>
                <SidebarMenuItem v-for="(item, index) in group.items" :key="index">
                    <SidebarMenuButton v-if="item.visible" :is-active="item.isActive" :tooltip="item.title"
                        :class="[item.isActive && '!bg-brand-blue font-semibold !text-white']" as-child>
                        <Link :href="item.href" class="flex items-center h-10 leading-none gap-2">
                            <Icon :icon="item.icon" class="!size-6.5 text-brand-blue"
                                :class="[item.isActive && 'text-white  font-bold']" />
                            <span class="text-sm leading-none text-gray-600" :class="[item.isActive && 'text-white']">{{
                                item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </template>
    </SidebarGroup>
</template>
