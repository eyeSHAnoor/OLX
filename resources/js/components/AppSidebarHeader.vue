<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { getInitials } from '@/composables/useInitials';
import { Button } from '@/components/ui/button';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuItem,
    DropdownMenuSeparator
} from '@/components/ui/dropdown-menu';
import { Bell } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import LanguageSwitcher from '@/components/Application/LanguageSwitcher.vue';
import UseNotificationDropdown from './useNotificationDropdown.vue';

const page = usePage();
const auth = computed(() => page.props.auth);

// use real notifications from Inertia share
const notifications = computed(() => page.props.notifications || []);

// console.log(notifications)

// unread count
const unreadCount = computed(() => notifications.value.received.length + notifications.value.sent.length);
</script>

<template>
    <header
        class="flex h-14 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
        <div class="flex items-center justify-between gap-2 w-full">
            <div class="flex items-center gap-2">
                <SidebarTrigger class="-ml-1" />
                <Breadcrumbs />
            </div>

            <!-- Right side -->
            <div class="ml-auto flex items-center gap-3">
                <!-- <LanguageSwitcher /> -->

                <!--  Notification Dropdown -->
                <!-- <UseNotificationDropdown :notifications="notifications" :auth="auth" /> -->

                <!-- User Dropdown -->
                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <Button variant="ghost" size="icon"
                            class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary">
                            <Avatar class="size-8 overflow-hidden rounded-full">
                                <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                <AvatarFallback
                                    class="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ getInitials(auth.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
</template>
