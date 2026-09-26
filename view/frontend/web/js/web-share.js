/**
 * Share button: the device's native share sheet where the Web Share API exists,
 * otherwise the server-rendered share links. Without JavaScript the links are simply visible.
 * Every share fires a "webshare:share" event on document ({ method, url }) for analytics.
 */
define([], function () {
    'use strict';

    function emit(method, url) {
        document.dispatchEvent(new CustomEvent('webshare:share', { detail: { method: method, url: url } }));
    }

    function init(config, root) {
        const toggle = root.querySelector('.brocode-web-share-toggle');
        const list = root.querySelector('.brocode-web-share-links');
        const copy = root.querySelector('.brocode-web-share-copy');
        const status = root.querySelector('.brocode-web-share-status');
        const payload = { url: config.url, title: config.title };
        const native = typeof navigator.share === 'function'
            && (typeof navigator.canShare !== 'function' || navigator.canShare(payload));

        function setOpen(open) {
            list.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
            if (!open && status) {
                status.textContent = '';
            }
            if (open) {
                // Open towards the left when the panel would run past the right edge of the viewport.
                root.classList.remove('is-flipped');
                if (list.getBoundingClientRect().right > document.documentElement.clientWidth - 8) {
                    root.classList.add('is-flipped');
                }
            }
        }

        root.classList.add('is-enhanced'); // the list becomes a dropdown only once JS runs
        setOpen(false);
        toggle.hidden = false;
        if (copy && navigator.clipboard && window.isSecureContext) {
            copy.hidden = false;
        }

        toggle.addEventListener('click', function () {
            if (!native) {
                setOpen(list.hidden);
                return;
            }
            navigator.share(payload).then(function () {
                emit('native', config.url);
            }).catch(function (err) {
                // AbortError = the customer closed the share sheet; anything else = show the links.
                if (err && err.name !== 'AbortError') {
                    setOpen(true);
                }
            });
        });

        list.addEventListener('click', function (event) {
            const link = event.target.closest('a[data-share]');
            if (link) {
                emit(link.dataset.share, config.url);
            }
        });

        if (copy) {
            copy.addEventListener('click', function () {
                navigator.clipboard.writeText(config.url).then(function () {
                    status.textContent = root.dataset.copied;
                    emit('copy', config.url);
                });
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !list.hidden) {
                setOpen(false);
                toggle.focus();
            }
        });
        document.addEventListener('click', function (event) {
            if (!list.hidden && !root.contains(event.target)) {
                setOpen(false);
            }
        });
    }

    return function (config, element) {
        init(config, element);
    };
});
