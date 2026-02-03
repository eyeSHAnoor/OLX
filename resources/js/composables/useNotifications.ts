import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

export const useNotifications = () => {
    const page = usePage();
    const { toast } = useToast();

    const userId = computed(() => page.props.auth.user?.id || null);
    const notifications = ref([
        ...(page.props.notifications?.received || []),
        ...(page.props.notifications?.sent || []),
    ]);

    const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length);

    onMounted(() => {
        if (!userId.value) return;

        // Wait for Echo to be initialized
        const waitForEcho = setInterval(() => {
            if (window.Echo) {
                clearInterval(waitForEcho);

                window.Echo.channel('notifications')
                    .listen('.notification.created', (data: any) => {
                        // console.log('Received:', data);

                        notifications.value.unshift({ ...data, read_at: null });
                        toast({ title: data.title, description: data.message, duration: 5000 });
                    });
            }
        }, 100);
    });

    return {
        notifications,
        unreadCount,
    };
};
