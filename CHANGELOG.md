# Changelog

All notable changes to [DataFirefly Cache Clear for PrestaShop](https://www.datafirefly.com/en/product/datafirefly-cache-clear/) are documented here.

## [1.1.0]

### Added
- Object cache purge (Memcached, APCu...) when enabled in the shop parameters.
- PHP OPcache reset when enabled.
- The button is only shown to employees whose profile can use it.
- Every purge is logged in Advanced Parameters > Logs.
- Session-expiry detection with a dedicated message.
- Back-office translations: French, Spanish, German, Italian, Dutch, Polish, Portuguese.

### Changed
- Safer Symfony purge: native cache clearers first, then a deferred removal of `var/cache/{env}` at the end of the request.
- Each purge step runs independently and reports its own error.
- The AJAX endpoint only accepts POST requests.
- Stacked, accessible notifications (`aria-live`, `prefers-reduced-motion`).

## [1.0.1] - 2026-06-05

### Fixed
- Button alignment in the back-office toolbar.

## [1.0.0] - 2026-06-04

- First release: "Clear cache" button in the back-office toolbar, one-click purge of Smarty, XML, Media and Symfony.

[1.1.0]: https://github.com/datafirefly-ltd/dfclearcache/releases/tag/v1.1.0
[1.0.1]: https://www.datafirefly.com/en/product/datafirefly-cache-clear/
[1.0.0]: https://www.datafirefly.com/en/product/datafirefly-cache-clear/
