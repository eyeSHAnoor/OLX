<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { NavigationMenuItem, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

// const { hasRole } = usePermissions();

const isCurrentRoute = computed(() => (url: string) => page.url === url);

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : ''),
);

const mainNavItems: NavItem[] = computed(() => [
    {
        visible: true,
        title: 'All List',
        href: route('attendances.index'),
        icon: 'lucide:map-pin',
        isActive: route().current('attendances.index') || page.url === '/student-attendances',
    },

    {
        // visible: hasRole(['admin']),
        visible: true,
        title: 'Add Attendance',
        href: route('attendances.create'),
        icon: 'lucide:map-pin',
        isActive: route().current('attendances.create')|| page.url === '/student-attendances/create',
    },

]);


</script>

<template>
    <div>
        <div class="bg-primary text-white">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-base font-medium"
                                        :class="activeItemStyles(item.href)"
                                    >
                                        <Icon :icon="item.icon" class="size-4" />
                                        <span>{{ item.title }}</span>
                                    </Link>
                                </nav>
                                <!--                                <div class="flex flex-col space-y-4">-->
                                <!--                                    <a-->
                                <!--                                        v-for="item in rightNavItems"-->
                                <!--                                        :key="item.title"-->
                                <!--                                        :href="item.href"-->
                                <!--                                        target="_blank"-->
                                <!--                                        rel="noopener noreferrer"-->
                                <!--                                        class="flex items-center space-x-2 text-base font-medium"-->
                                <!--                                    >-->
                                <!--                                        <Icon :icon="item.icon" class="size-5" />-->
                                <!--                                        <span>{{ item.title }}</span>-->
                                <!--                                    </a>-->
                                <!--                                </div>-->
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="route('dashboard')" class="flex items-center gap-x-2">
                    <AppLogo fullLogo />
                </Link>

                <!-- Desktop Menu -->
                <!-- Right Side Menu -->
                <div class="ml-auto flex items-center space-x-2">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch">
                            <NavigationMenuItem v-for="(item, index) in mainNavItems" :key="index" class="relative flex h-full items-center">
                                <!-- has children -->
                                <template v-if="item.visible && item.children?.length">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                :class="[
                                                    'flex h-16 items-center space-x-2 px-4 !font-normal',
                                                    item.isActive
                                                        ? '!rounded-none bg-white text-foreground dark:bg-neutral-800 dark:text-neutral-100'
                                                        : '!bg-primary !text-white',
                                                ]"
                                            >
                                                <span>{{ item.title }}</span>
                                                <Icon icon="lucide:chevron-down" class="size-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent :sideOffset="0">
                                            <template v-for="child in item.children" :key="child.href">
                                                <Link
                                                    v-if="child.visible"
                                                    :href="child.href"
                                                    class="block bg-transparent px-4 py-2 text-base text-sm hover:text-primary"
                                                >
                                                    {{ child.title }}
                                                </Link>
                                            </template>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </template>

                                <!-- no children -->
                                <template v-else-if="item.visible">
                                    <Link
                                        :href="item.href"
                                        :class="[
                                            navigationMenuTriggerStyle(),
                                            'relative flex h-16 cursor-pointer items-center gap-x-1 px-4 !font-normal',
                                            item.isActive ? '!rounded-none bg-white text-foreground' : '!bg-primary !text-white',
                                            ,
                                        ]"
                                    >
                                        <Icon v-if="item.icon && !item.title" :icon="item.icon" class="size-4" />
                                        <span v-else>{{ item.title }}</span>
                                        <span v-if="item.badge > 0" class="absolute end-3 top-4">
                                            <span class="relative flex size-2.5">
                                                <span
                                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75"
                                                ></span>
                                                <span class="relative inline-flex size-2.5 rounded-full bg-sky-500"></span>
                                            </span>
                                        </span>
                                    </Link>
                                </template>

                                <!-- underline -->
                                <div v-if="item.isActive" class="absolute bottom-0 left-0 h-1 w-full translate-y-px bg-white dark:bg-white" />
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                    <AvatarFallback class="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
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
                <!-- end:Desktop Menu -->
            </div>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="flex w-full border-b border-sidebar-border/70">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
