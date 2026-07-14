/**
 * mod_popup – Delayed display; closes via button, click on the
 * dimmed background, or the Esc key. Display can be set to appear once per
 * session (sessionStorage), repeatedly (on every page view), or suppressed for
 * a specific number of hours via a cookie. Deliberately implemented without
 * jQuery, since jQuery is no longer a core component in Joomla 6.
 */
(function () {
    'use strict';

    function setCookie(name, value, hours) {
        var expires = '';
        if (hours) {
            var date = new Date();
            date.setTime(date.getTime() + hours * 60 * 60 * 1000);
            expires = '; expires=' + date.toUTCString();
        }
        try {
            document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
        } catch (e) {
            // ignore, if Cookies are blocked
        }
    }

    function getCookie(name) {
        var match = document.cookie.match('(^|;\\s*)' + name + '=([^;]*)');
        return match ? decodeURIComponent(match[2]) : null;
    }

    function initPopup(overlay) {
        var uid         = overlay.id;
        var storageKey  = 'hkpopup_closed_' + uid;
        var delay       = parseInt(overlay.getAttribute('data-delay'), 10) || 0;
        var repeatMode  = overlay.getAttribute('data-repeat') || 'once';
        var cookieHours = parseInt(overlay.getAttribute('data-cookie-hours'), 10) || 24;

        var alreadyClosed = false;

        if (repeatMode === 'once') {
            try {
                alreadyClosed = window.sessionStorage && sessionStorage.getItem(storageKey) === '1';
            } catch (e) {
                // sessionStorage may be blocked, for example, in the incognito mode of some browsers.
            }
        } else if (repeatMode === 'cookie') {
            alreadyClosed = getCookie(storageKey) === '1';
        }

        if (alreadyClosed) {
            overlay.parentNode && overlay.parentNode.removeChild(overlay);
            return;
        }

        function open() {
            overlay.classList.add('is-visible');
            document.body.classList.add('hkpopup-open');
        }

        function close() {
            overlay.classList.remove('is-visible');
            document.body.classList.remove('hkpopup-open');

            if (repeatMode === 'once') {
                try {
                    sessionStorage.setItem(storageKey, '1');
                } catch (e) {
                    // Ignore if sessionStorage is not available
                }
            } else if (repeatMode === 'cookie') {
                setCookie(storageKey, '1', cookieHours);
            }

            window.setTimeout(function () {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 500);
        }

        window.setTimeout(open, delay);

        var closeBtn = overlay.querySelector('.hkpopup-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', close);
        }

        // Clicking on the darkened background (not on the box) also closes it.
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                close();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && overlay.classList.contains('is-visible')) {
                close();
            }
        });
    }

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        var overlays = document.querySelectorAll('.hkpopup-overlay');
        for (var i = 0; i < overlays.length; i++) {
            initPopup(overlays[i]);
        }
    });
})();
