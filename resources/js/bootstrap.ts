// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';

// // Assign Pusher to window
// window.Pusher = Pusher;

// // Initialize Echo
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
//     forceTLS: true, // true for HTTPS
//     encrypted: true,
// });

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher', // IMPORTANT
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 8080,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 8080,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});
