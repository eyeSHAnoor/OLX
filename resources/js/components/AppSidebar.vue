<script setup lang="ts">
import NavMain from "@/components/NavMain.vue";
import NavUser from "@/components/NavUser.vue";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";
import { Link } from "@inertiajs/vue3";
import AppLogo from "./AppLogo.vue";
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="items-start py-8">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="p-0">
                        <Link :href="route('dashboard')" class="p-0">
                            <div class="w-30 h-30 flex items-center justify-center overflow-visible">
                                <AppLogo class="w-20 h-20 object-contain block" />
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="overflow-y-auto">
            <NavMain />
        </SidebarContent>

        <!--        <SidebarFooter>-->
        <!--            <NavUser />-->
        <!--        </SidebarFooter>-->
    </Sidebar>
    <slot />
</template>
