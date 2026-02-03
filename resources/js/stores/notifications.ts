// src/stores/notifications.ts
import { ref } from 'vue';

export const notifications = ref<Array<any>>([]);
export const unreadCount = ref(0);

export function addNotification(n: any) {
    notifications.value.unshift({ ...n, read_at: null });
    unreadCount.value = notifications.value.filter(n => !n.read_at).length;
}
