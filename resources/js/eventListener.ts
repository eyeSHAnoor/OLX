import { useNotificationStore } from '@/stores/notificationStore';
import Echo from 'laravel-echo';

let echo = null;

export function initializeRealtimeListener(userId) {
    if (!userId) return;

    // Initialize Pusher
    if (!window.Pusher) {
        window.Pusher = require('pusher-js');
    }

    // Initialize Echo if not already
    if (!window.Echo) {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true,
        });
    }

    echo = window.Echo;

    // Listen for order accepted events
    echo.channel(`orders.${userId}`).listen('.order.accepted', (data) => {
        const notificationStore = useNotificationStore();
        notificationStore.showNotification(data);
    });
}

export function disconnectRealtimeListener(userId) {
    if (echo) {
        echo.leaveChannel(`orders.${userId}`);
    }
}
