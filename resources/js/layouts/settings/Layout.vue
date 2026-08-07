<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Account',
        href: route('account.edit'),
    },
    {
        title: 'Profile',
        href: route('profile.edit'),
    },
    // {
    //     title: 'Role and Permission',
    //     href: route('roles-permissions.index'),
    //     isActive: route().current('roles-permissions.*'),
    //     permission: 'view_roles', // only show if has permission
    // },
    // {
    //     title: 'Team management',
    //     href: route('user-roles.index'),
    //     isActive: route().current('user-roles.*'),
    //     permission: 'assign_roles',
    // },
    // {
    //     title: 'Preferences',
    //     href: route('preferences.edit'),
    // },
    // {
    //     title: 'Notification Settings',
    //     href: route('notification-settings.edit'),
    // },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
];


const page = usePage();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-y-0 lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button v-for="item in sidebarNavItems.filter(i => !i.permission || hasPermissions(i.permission))"
                        :key="item.href" variant="ghost" :class="[
                            'w-full justify-start underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500',
                            { 'bg-muted': currentPath === item.href },
                        ]" as-child>
                        <Link :href="item.href">{{ item.title }}</Link>
                    </Button>

                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1">
                <section class="space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
