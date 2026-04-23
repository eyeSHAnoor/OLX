self.addEventListener('push', function (event) {
    console.log('Push received ✔');

    if (!event.data) return;

    const data = event.data.json();
    console.log('Parsed data:', data);

    const promise = self.registration.showNotification(data.title || 'New Message', {
        body: data.body || '',
        icon: data.icon || '/icon.png',
        data: data.data || {},
        tag: 'chat-message-' + (data.data?.chat_id || ''),
        renotify: true,
        requireInteraction: true,
    });

    event.waitUntil(promise);
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const url = event.notification.data.url || '/';

    event.waitUntil(clients.openWindow(url));
});
