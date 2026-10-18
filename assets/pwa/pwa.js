(function () {
    'use strict';
    var script = document.currentScript;
    var installButtons = Array.prototype.slice.call(document.querySelectorAll('[data-pwa-install]'));
    var status = document.querySelector('[data-pwa-status]');
    var promptEvent;
    var floatingButton;
    var standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;

    var ua = navigator.userAgent;
    var ios = /iPhone|iPad|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var macSafari = !ios && /Macintosh/.test(ua) && /Safari/.test(ua) && !/Chrome|Chromium|Edg|OPR/.test(ua);
    var help = document.querySelector('[data-pwa-help]');
    var macFirefox = /Macintosh/.test(ua) && /Firefox/.test(ua);
    var guidance = macFirefox
        ? 'Firefox on Mac does not support installing web apps. You can use CLMD Portal in this tab, bookmark it with Command+D, or open this address in Chrome or Safari to install.'
        : macSafari
        ? 'In Safari on macOS Sonoma 14 or later, choose File > Add to Dock, then click Add. Safari does not open an install prompt from this button.'
        : ios ? 'In Safari, tap Share > Add to Home Screen, then Add.'
        : /Chrome/.test(ua) && !/Edg|OPR/.test(ua)
            ? 'In Chrome, open the three-dot menu > Cast, save, and share > Install page as app. If already installed, open CLMD Portal from chrome://apps.'
            : 'Use your browser’s install option when available. In Edge, open the menu > Apps > Install this site as an app.';

    function update(message, available) {
        if (status) status.textContent = message;
        installButtons.forEach(function (button) {
            button.disabled = !!standalone;
            button.textContent = standalone ? 'App installed' : available ? 'Install CLMD Portal' : 'How to install';
        });
        if (floatingButton) floatingButton.hidden = !available;
    }

    async function install() {
        if (!promptEvent) {
            update(guidance, false);
            if (help) {
                help.hidden = false;
                help.focus();
            }
            return;
        }
        var pending = promptEvent;
        promptEvent = null;
        update('Follow the browser prompt to install CLMD Portal.', false);
        try {
            await pending.prompt();
            var choice = await pending.userChoice;
            update(choice.outcome === 'accepted'
                ? 'Installation requested. Check your home screen or apps list.'
                : 'Installation cancelled. You can still install from your browser menu.', false);
        } catch (error) {
            update('Use your browser menu to install, or follow the instructions below.', false);
        }
    }

    installButtons.forEach(function (button) { button.addEventListener('click', install); });
    update(guidance, false);
    if (standalone) update('CLMD Portal is running as an installed app.', false);
    if (!script || !('serviceWorker' in navigator) || !window.isSecureContext) {
        if (!standalone) update('Installation is unavailable here. Open the secure (HTTPS) portal in a supported browser.', false);
        return;
    }
    var root = new URL('../../', script.src);
    window.addEventListener('load', function () {
        navigator.serviceWorker.register(new URL('sw.js', root).href, { scope: root.pathname })
            .catch(function (error) { console.warn('PWA registration failed:', error); });
    });
    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        if (standalone) return;
        promptEvent = event;
        if (!installButtons.length && !floatingButton) {
            floatingButton = document.createElement('button');
            floatingButton.type = 'button';
            floatingButton.textContent = 'Install CLMD Portal';
            floatingButton.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:1050;padding:12px 18px;border:0;border-radius:8px;background:#163b65;color:white;box-shadow:0 2px 8px #0003;font:600 14px system-ui;cursor:pointer';
            floatingButton.addEventListener('click', install);
            document.body.appendChild(floatingButton);
        }
        update('Ready to install on this device.', true);
    });
    window.addEventListener('appinstalled', function () {
        promptEvent = null;
        standalone = true;
        update('CLMD Portal is installed. Open it from your home screen or apps list.', false);
    });
}());
