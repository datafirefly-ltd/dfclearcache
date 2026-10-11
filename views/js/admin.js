/**
 * DataFirefly Cache Clear
 *
 * @author    DataFirefly
 * @copyright DataFirefly Limited
 * @license   Proprietary
 */
(function () {
    'use strict';

    var BUTTON_ID = 'df-clear-cache-btn';
    var TOAST_ZONE_ID = 'df-ccc-toasts';

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    function labels() {
        return (typeof window.dfClearCacheLabels === 'object' && window.dfClearCacheLabels) || {};
    }

    function label(key, fallback) {
        var l = labels();
        return l[key] || fallback;
    }

    function toastZone() {
        var zone = document.getElementById(TOAST_ZONE_ID);
        if (!zone) {
            zone = document.createElement('div');
            zone.id = TOAST_ZONE_ID;
            zone.className = 'df-ccc-toasts';
            zone.setAttribute('role', 'status');
            zone.setAttribute('aria-live', 'polite');
            document.body.appendChild(zone);
        }
        return zone;
    }

    function showToast(message, type) {
        var toast = document.createElement('div');
        toast.className = 'df-ccc-toast df-ccc-toast--' + (type || 'info');
        toast.textContent = message;
        toastZone().appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add('df-ccc-toast--visible');
        });

        setTimeout(function () {
            toast.classList.remove('df-ccc-toast--visible');
            setTimeout(function () {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 350);
        }, type === 'error' ? 6000 : 3500);
    }

    function setLoading(btn, loading) {
        var labelEl = btn.querySelector('.df-ccc-label');
        btn.classList.toggle('df-ccc-loading', loading);
        btn.disabled = loading;
        btn.setAttribute('aria-busy', loading ? 'true' : 'false');
        if (labelEl) {
            labelEl.textContent = loading
                ? label('loading', 'Clearing...')
                : label('button', 'Clear cache');
        }
    }

    function isLoginPage(response) {
        return response.redirected && /controller=AdminLogin/i.test(response.url || '');
    }

    function clearCache(btn) {
        if (btn.classList.contains('df-ccc-loading')) {
            return;
        }

        setLoading(btn, true);

        fetch(window.dfClearCacheUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (response) {
                var type = response.headers.get('Content-Type') || '';

                if (isLoginPage(response)) {
                    throw new Error('session');
                }
                if (type.indexOf('application/json') === -1) {
                    throw new Error('http');
                }

                return response.json();
            })
            .then(function (data) {
                var detail = data && data.cleared && data.cleared.length
                    ? ' (' + data.cleared.join(', ') + ')'
                    : '';

                if (data && data.success) {
                    showToast((data.message || label('success', 'Cache cleared.')) + detail, 'success');
                } else {
                    showToast((data && data.message) || label('error', 'Error while clearing the cache.'), 'error');
                    if (data && data.errors && data.errors.length && window.console) {
                        window.console.warn('[dfclearcache]', data.errors);
                    }
                }
            })
            .catch(function (err) {
                showToast(
                    err && err.message === 'session'
                        ? label('session', 'Your session has expired. Reload the page and try again.')
                        : label('error', 'Error while clearing the cache.'),
                    'error'
                );
            })
            .then(function () {
                setLoading(btn, false);
            });
    }

    function buildButton() {
        var btn = document.createElement('button');
        var icon = document.createElement('i');
        var text = document.createElement('span');

        btn.id = BUTTON_ID;
        btn.type = 'button';
        btn.className = 'btn btn-outline-secondary df-ccc-btn';
        btn.title = label('button', 'Clear cache');

        icon.className = 'material-icons df-ccc-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = 'cached';

        text.className = 'df-ccc-label';
        text.textContent = label('button', 'Clear cache');

        btn.appendChild(icon);
        btn.appendChild(text);

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            clearCache(btn);
        });

        return btn;
    }

    function inject() {
        if (typeof window.dfClearCacheUrl === 'undefined' || document.getElementById(BUTTON_ID)) {
            return;
        }

        var btn = buildButton();

        // 1) Next to the page toolbar actions (help, add new...).
        var iconsBar = document.querySelector('.header-toolbar .toolbar-icons');
        if (iconsBar) {
            var wrapper = document.createElement('div');
            wrapper.className = 'df-ccc-wrapper';
            wrapper.appendChild(btn);
            iconsBar.classList.add('df-ccc-host');
            iconsBar.insertBefore(wrapper, iconsBar.firstChild);
            return;
        }

        // 2) Fallback: page toolbar.
        var toolbar = document.querySelector('.header-toolbar');
        if (toolbar) {
            btn.classList.add('df-ccc-btn--standalone');
            toolbar.appendChild(btn);
            return;
        }

        // 3) Last resort: back-office top bar.
        var topbar = document.querySelector('#header_infos, header.main-header, nav.main-header');
        if (topbar) {
            btn.classList.add('df-ccc-btn--top');
            topbar.appendChild(btn);
        }
    }

    ready(inject);
})();
