const SW_VERSION = '1.0.1';

// ===============================
//  INSTALL (activate immediately)
// ===============================
self.addEventListener('install', (event) => {
    // console.log(' Service Worker installing...');
    self.skipWaiting();
});

// ===============================
//  ACTIVATE (take control)
// ===============================
self.addEventListener('activate', (event) => {
    // console.log(' Service Worker activated');
    event.waitUntil(self.clients.claim());
});

// ===============================
//  PUSH EVENT
// ===============================
self.addEventListener('push', function (event) {
    // console.log('📩 Push received');

    let data = {};

    //  Safe payload parsing
    if (event.data) {
        try {
            data = event.data.json();
            // console.log('📦 Parsed JSON:', data);
        } catch (e) {
            console.warn('⚠️ JSON parse failed, using text');
            data = {
                title: 'New Notification',
                body: event.data.text(),
            };
        }
    } else {
        console.warn('⚠️ No payload received');
        data = {
            title: 'New Notification',
            body: 'You have a new update',
        };
    }

    const title = data.title || 'Notification';

    const options = {
        body: data.body || '',
        icon: data.icon || '/icon.png',
        badge: '/icon.png', // small icon (recommended)
        data: data.data || {},
        tag: 'chat-message-' + (data.data?.chat_id || ''),
        renotify: true,
        requireInteraction: true,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// ===============================
//  NOTIFICATION CLICK
// ===============================
self.addEventListener('notificationclick', function (event) {
    // console.log('🔔 Notification clicked');

    event.notification.close();

    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(url);
        }),
    );
});

// ===============================
//  OPTIONAL: HANDLE CLOSE EVENT
// ===============================
self.addEventListener('notificationclose', function () {
    // console.log('❌ Notification dismissed');
});
