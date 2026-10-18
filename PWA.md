?# CLMD Portal PWA

The portal supports installation with its existing PHP backend. Public pages,
login/signup pages, and shared application headers load `templates/pwa.php`.
Print/PDF views are intentionally excluded.

## Deployment and installation

Deploy `manifest.webmanifest`, `sw.js`, `offline.html`, `assets/pwa/`, the changed
views, and `.htaccess` together. Serve over HTTPS in production. Localhost is
also supported; a phone opening an HTTP LAN IP address is not a secure context.
Set APP_BASE_URL to the actual application URL, including a trailing slash.
Both domain-root and subdirectory deployments are supported.

On supported browsers an Install CLMD Portal button appears when the browser
allows installation. The browser's installation menu can also be used. On
iPhone/iPad, open in Safari and use Share > Add to Home Screen.

## Offline behavior and updates

The worker stores only the offline page and public app icons. Page navigations
always use the network, falling back to a connection message on network failure.
Login responses, dashboards, reports, API responses, uploads, and submissions
are never stored by the service worker. Offline forms and submission syncing
are not implemented. Normal HTTP error responses are preserved.

Icons are resized from the existing site favicon. Replace these PNG files with
higher-resolution brand originals if available. Increment VERSION in sw.js
when updating precached files. Updates activate after existing app tabs close;
activation deletes only this application's old PWA caches.

## Browser verification

1. Open the deployed portal online. In DevTools > Application confirm the
   manifest, icons, and active service worker under the application's path.
2. Install the app and confirm it opens in a standalone window.
3. Open a dashboard online, then select Offline in DevTools and navigate to
   another page. Confirm the connection message and Try again button appear.
4. Reconnect and retry. Confirm the requested page loads normally.
5. Confirm Cache Storage contains only offline.html and the three app icons.
6. Check login/logout and a normal submission online; confirm submission requests
   never use the service worker cache. Repeat for a subdirectory deployment.
