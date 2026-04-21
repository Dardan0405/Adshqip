/**
 * Service Worker for Push Notifications
 * AdShqip Admin Panel
 */

const CACHE_NAME = 'adshqip-admin-v1';

// Install event
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activated');
    event.waitUntil(clients.claim());
});

// Push event - handle incoming push notifications
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push received');

    let data = {
        title: 'AdShqip Notification',
        body: 'You have a new notification',
        icon: '/images/logo-icon.png',
        badge: '/images/badge.png',
        url: '/admin/notifications',
    };

    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch (e) {
            console.error('[Service Worker] Error parsing push data:', e);
        }
    }

    const options = {
        body: data.body,
        icon: data.icon || '/images/logo-icon.png',
        badge: data.badge || '/images/badge.png',
        vibrate: [100, 50, 100],
        data: {
            url: data.url || '/admin/notifications',
            timestamp: data.timestamp || Date.now(),
        },
        actions: [
            {
                action: 'open',
                title: 'View',
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
            },
        ],
        requireInteraction: true,
        tag: 'adshqip-notification-' + (data.timestamp || Date.now()),
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');

    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    const url = event.notification.data?.url || '/admin/notifications';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Check if there's already a window open
                for (const client of clientList) {
                    if (client.url.includes('/admin') && 'focus' in client) {
                        client.navigate(url);
                        return client.focus();
                    }
                }
                // If no window found, open a new one
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

// Notification close event
self.addEventListener('notificationclose', (event) => {
    console.log('[Service Worker] Notification closed');
});

// Background sync (for offline support)
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);
});

// Fetch event (for caching - optional)
self.addEventListener('fetch', (event) => {
    // Pass through all requests (no caching for admin panel)
    event.respondWith(fetch(event.request));
});
