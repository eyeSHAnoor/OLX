<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { Bell } from 'lucide-vue-next';

const page = usePage();
const auth = computed(() => page.props.auth.user);
const userId = computed(() => auth.value?.id || null);

const { toast } = useToast();

// console.log(page.props.notifications);


// Start with initial notifications from page props
const liveNotifications = ref([
    ...(page.props.notifications?.received || []),
    ...(page.props.notifications?.sent || []),
]);

// Derived lists for dropdown
const receivedNotifications = computed(() =>
    liveNotifications.value.filter(n => n.type === 'received')
);
const sentNotifications = computed(() =>
    liveNotifications.value.filter(n => n.type === 'sent')
);

// Unread count
const unreadCount = computed(() => liveNotifications.value.filter(n => !n.read_at).length);

// Listen to live Pusher notifications
onMounted(() => {
    if (!userId.value) return;

    if (!window.Echo) {
        console.warn('Echo not initialized!');
        return;
    }

    window.Echo.channel('notifications')
        .listen('.notification.created', (data: any) => {
            // console.log('Received notification:', data);

            // Push into liveNotifications
            liveNotifications.value.unshift({ ...data, read_at: null });

            // Optional toast
            toast({
                title: data.title,
                description: data.message,
                duration: 5000,
            });
        });
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative p-2 rounded-full">
                <Bell class="size-5" />
                <span v-if="unreadCount > 0" class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"></span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-72 p-2">
            <div class="px-2 py-1.5 font-semibold text-sm text-muted-foreground">
                Notifications ({{ unreadCount }})
            </div>
            <DropdownMenuSeparator />

            <div class="max-h-60 overflow-y-auto">
                <div class="text-xs text-muted-foreground">Received</div>
                <DropdownMenuItem v-for="note in receivedNotifications" :key="note.id"
                    class="flex flex-col items-start py-2 cursor-pointer" @click="$inertia.visit(note.url)">
                    <span class="font-medium">{{ note.title }}</span>
                    <!-- <span class="text-[11px] text-muted-foreground">{{ note.message }}</span> -->
                    <span class="text-xs text-muted-foreground">{{ new Date(note.created_at).toLocaleString() }}</span>
                </DropdownMenuItem>

                <div class="text-xs text-muted-foreground mt-2">Sent</div>
                <DropdownMenuItem v-for="note in sentNotifications" :key="note.id"
                    class="flex flex-col items-start py-2 cursor-pointer" @click="$inertia.visit(note.url)">
                    <span class="font-medium">{{ note.title }}</span>
                    <!-- <span class="text-[11px] text-muted-foreground">{{ note.message }}</span> -->
                    <span class="text-xs text-muted-foreground">{{ new Date(note.created_at).toLocaleString() }}</span>
                </DropdownMenuItem>
            </div>

            <DropdownMenuSeparator />
            <DropdownMenuItem class="justify-center text-center text-sm text-blue-600 hover:text-blue-700">
                <a :href="route('notification.index')">
                    View All
                </a>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
