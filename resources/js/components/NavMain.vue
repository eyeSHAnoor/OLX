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
                        :class="[item.isActive && '!bg-(--sidebar-item-active) font-semibold !text-primary-foreground']"
                        as-child>
                        <Link :href="item.href" class="flex flex-col items-center h-16 leading-none">
                            <Icon :icon="item.icon" class="!size-6.5" />
                            <span class="text-xs leading-none">{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </template>
    </SidebarGroup>
</template>
