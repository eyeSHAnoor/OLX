import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
    const showOrderPopup = ref(false);
    const pendingOrder = ref(null);
    let timeoutId = null;

    const showNotification = (orderData) => {
        // Clear any existing timeout
        if (timeoutId) clearTimeout(timeoutId);

        pendingOrder.value = orderData;
        showOrderPopup.value = true;

        // Auto-hide after 30 seconds
        timeoutId = setTimeout(() => {
            closeNotification();
        }, 30000);
    };

    const closeNotification = () => {
        showOrderPopup.value = false;
        pendingOrder.value = null;
        if (timeoutId) clearTimeout(timeoutId);
    };

    return {
        showOrderPopup,
        pendingOrder,
        showNotification,
        closeNotification,
    };
});
