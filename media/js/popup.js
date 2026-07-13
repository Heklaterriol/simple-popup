/**
 * mod_popup – Verzögertes Einblenden, Schließen per Button / Klick auf den
 * abgedunkelten Hintergrund / Esc-Taste. Optional einmalig (pro Sitzung)
 * oder wiederholt (bei jedem Seitenaufruf) einblenden. Bewusst ohne jQuery
 * umgesetzt, da jQuery in Joomla 6 kein Kernbestandteil mehr ist.
 */
(function () {
    'use strict';

    function initPopup(overlay) {
        var uid = overlay.id;
        var storageKey = 'hkpopup_closed_' + uid;
        var delay = parseInt(overlay.getAttribute('data-delay'), 10) || 0;
        var repeatMode = overlay.getAttribute('data-repeat') || 'once';

        if (repeatMode === 'once') {
            var alreadyClosed = false;
            try {
                alreadyClosed = window.sessionStorage && sessionStorage.getItem(storageKey) === '1';
            } catch (e) {
                // sessionStorage kann z.B. im privaten Modus mancher Browser blockiert sein.
            }

            if (alreadyClosed) {
                overlay.parentNode && overlay.parentNode.removeChild(overlay);
                return;
            }
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
                    // ignorieren, wenn sessionStorage nicht verfügbar ist
                }
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

        // Klick auf den abgedunkelten Hintergrund (nicht auf die Box) schließt ebenfalls.
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
