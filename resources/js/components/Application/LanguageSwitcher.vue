<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getInitials } from '@/composables/useInitials';
import { languages } from '@/lib/utils';
import { usePage, Link } from '@inertiajs/vue3';
import { DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuItem } from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { Icon } from '@iconify/vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const preferences = computed(() => user.value?.preferences);
const selectedLang = computed(() =>
    languages?.find(l => l.value === (preferences.value?.language ?? 'en'))
);
</script>


<template>
    <DropdownMenu>
        <DropdownMenuTrigger :as-child="true">
            <Button variant="ghost" size="icon"
                class="relative size-8 w-auto p-1 focus-within:ring-2 focus-within:ring-primary">
                <Icon :icon="selectedLang?.icon" class="size-6" />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-56">
            <DropdownMenuLabel class="p-0 font-normal">
                <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    Choose System Language
                </div>
            </DropdownMenuLabel>

            <DropdownMenuSeparator />


            <DropdownMenuItem :as-child="true" v-for="(lang, index) in languages" :key="index">
                <Link class="block w-full" method="post" :href="route('preferences.update-language', lang.value)"
                    as="button">
                <Icon :icon="lang.icon" class="size-4" />
                {{ lang.label }}
                </Link>
            </DropdownMenuItem>

        </DropdownMenuContent>
    </DropdownMenu>
</template>
