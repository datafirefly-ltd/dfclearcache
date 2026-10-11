<div align="center">

<a href="https://www.datafirefly.com/en/product/datafirefly-cache-clear/"><img src="logo.png" alt="Clear PrestaShop cache module - DataFirefly" width="96" height="96"></a>

# Clear Cache Button for PrestaShop 8 & 9

**Free PrestaShop module that adds a "Clear cache" button to the back-office toolbar: one click purges the Smarty, XML, Media, Symfony, object cache and OPcache — without going to *Advanced Parameters > Performance* and without reloading the page.**

[![PrestaShop 8 & 9](https://img.shields.io/badge/PrestaShop-8.0%20%E2%86%92%209.x-DF0067?logo=prestashop&logoColor=white)](https://www.datafirefly.com/en/product/datafirefly-cache-clear/)
[![PHP 7.4+ / 8.x](https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.x-777BB4?logo=php&logoColor=white)](#requirements)
[![Version 1.1.0](https://img.shields.io/badge/version-1.1.0-2ea44f)](CHANGELOG.md)
[![Price: Free](https://img.shields.io/badge/price-free-brightgreen)](https://www.datafirefly.com/en/product/datafirefly-cache-clear/)
[![Multistore](https://img.shields.io/badge/multistore-yes-0a7bbb)](#features)

[**Product page**](https://www.datafirefly.com/en/product/datafirefly-cache-clear/) ·
[**Documentation**](https://www.datafirefly.com/en/documentation/datafirefly-cache-clear/) ·
[**Download the latest release**](https://github.com/datafirefly-ltd/dfclearcache/releases/latest) ·
[**More PrestaShop modules by DataFirefly**](https://www.datafirefly.com/en/)

</div>

---

<a href="https://www.datafirefly.com/en/product/datafirefly-cache-clear/"><img src="https://www.datafirefly.com/wp-content/uploads/2026/06/vider-le-cache-prestashop-purge-auto-ps-8-9-df-cacheclear-fr-3d1296.webp" alt="One-click PrestaShop cache purge from the back-office toolbar with DataFirefly Cache Clear" width="100%"></a>

## Why this module?

When you build, update or debug a PrestaShop store, you **clear the cache dozens of times a day**: after editing a template, a translation, a module, a CSS file or a hook. In PrestaShop 8 and 9 that means opening *Advanced Parameters > Performance*, clicking *Clear cache*, waiting for a full page reload, then navigating back to where you were.

**DataFirefly Cache Clear** (`dfclearcache`) by [DataFirefly](https://www.datafirefly.com/en/) puts a **Clear cache** button directly in the back-office toolbar of every admin page. One click, an AJAX call, a notification — and you stay on your page.

## Features

- **"Clear cache" button in the back-office toolbar**, on every admin page, next to the native page actions.
- **One-click AJAX purge**: no page reload, animated icon while it runs, success or error notification with the list of caches cleared.
- **Every PrestaShop cache at once**:
  - Smarty template cache (front office and back office)
  - XML cache
  - Media cache (CCC — combined, compressed CSS/JS)
  - Symfony container and kernel cache (`var/cache/prod` or `var/cache/dev`)
  - Object cache (Memcached, APCu…) when enabled in your parameters
  - PHP **OPcache**, when enabled and allowed by your host
- **Safe Symfony purge**: native PrestaShop cache clearers first, then deferred removal at the end of the request — the running container is never deleted under its own feet, and concurrent visitors rebuild a fresh cache instead of reading a half-deleted one.
- **Permission aware**: the button is only shown to employees whose profile can use it.
- **Logged**: every purge is written to *Advanced Parameters > Logs* with the employee and the caches cleared.
- **POST only**, session-expiry detection, accessible notifications (`aria-live`), `prefers-reduced-motion` respected.
- **Zero configuration**: install, enable, the button appears.
- **No front-office impact**: the module only loads in the back office.
- **Multistore** compatible.
- **Back office in 8 languages**: English, French, Spanish, German, Italian, Dutch, Polish, Portuguese.

## When to use it

| Situation | Why clear the cache |
| --- | --- |
| You edited a `.tpl` file of your theme or of a module | Smarty serves the compiled template until the cache is cleared |
| You changed CSS or JS with *Combine, Compress and Cache* (CCC) enabled | The combined files are served from the media cache |
| You installed, upgraded or overrode a module | The Symfony container and class index must be rebuilt |
| You updated PHP files on a server with OPcache | PHP keeps executing the old bytecode |
| A change "does not show up" on the store | The fastest first troubleshooting step |

## Requirements

- PrestaShop **8.0 to 9.x** (single store and multistore)
- PHP **7.4 to 8.x**
- Not compatible with PrestaShop 1.7

## Installation

**From the back office (recommended)**

1. Download `dfclearcache-x.y.z.zip` from the [latest release](https://github.com/datafirefly-ltd/dfclearcache/releases/latest) or from the [DataFirefly product page](https://www.datafirefly.com/en/product/datafirefly-cache-clear/).
2. Go to **Modules > Module Manager > Upload a module** and drop the zip.
3. The **Clear cache** button appears in the back-office toolbar. Nothing to configure.

**With Git**

```bash
cd /path/to/prestashop/modules
git clone https://github.com/datafirefly-ltd/dfclearcache.git
```

Then install **DataFirefly - Clear cache** from **Modules > Module Manager**.

## How it works

| Step | PrestaShop API |
| --- | --- |
| Smarty | `Tools::clearSmartyCache()` |
| XML | `Tools::clearXMLCache()` |
| Media (CCC) | `Media::clearCache()` |
| Symfony | `cache_clearer_chain` / `symfony_cache_clearer` services, then `Tools::clearSf2Cache()`, then deferred purge of `var/cache/{env}` |
| Object cache | `Cache::getInstance()->flush()` when `_PS_CACHE_ENABLED_` |
| OPcache | `opcache_reset()` when OPcache is enabled |

Each step runs independently: if one fails, the others still run and the error is reported. The button is injected through the `displayBackOfficeHeader` hook and calls the hidden `AdminDfClearCache` AJAX controller.

## FAQ

**Where does the button appear?**
In the back-office toolbar, next to the page actions and the help button, on every admin page.

**Which caches are cleared?**
Smarty, XML, Media (CSS/JS), the Symfony container, the object cache when enabled, and OPcache when enabled.

**Is any configuration needed?**
No. Install and enable the module: the button appears immediately.

**Can every employee clear the cache?**
Only employees whose profile has access to the module's controller. SuperAdmins always see the button.

**Is it compatible with PrestaShop 9 and multistore?**
Yes, the module is natively compatible with PrestaShop 8 and 9, in single store and multistore.

**Is it really free?**
Yes, this module is offered free of charge by [DataFirefly](https://www.datafirefly.com/en/).

## Available in your language

| Language | Product page | Documentation |
| --- | --- | --- |
| 🇬🇧 English | [Clear PrestaShop Cache — One-Click Purge](https://www.datafirefly.com/en/product/datafirefly-cache-clear/) | [Docs](https://www.datafirefly.com/en/documentation/datafirefly-cache-clear/) |
| 🇫🇷 Français | [Vider le cache PrestaShop en un clic](https://www.datafirefly.com/product/datafirefly-cache-clear/) | [Documentation](https://www.datafirefly.com/documentation/datafirefly-cache-clear/) |
| 🇪🇸 Español | [Vaciar la caché de PrestaShop en 1 clic](https://www.datafirefly.com/es/producto/datafirefly-cache-clear/) | [Documentación](https://www.datafirefly.com/es/documentation/datafirefly-cache-clear/) |
| 🇩🇪 Deutsch | [PrestaShop-Cache leeren mit einem Klick](https://www.datafirefly.com/de/produkt/datafirefly-cache-clear/) | [Dokumentation](https://www.datafirefly.com/de/documentation/datafirefly-cache-clear/) |
| 🇮🇹 Italiano | [Svuota la cache PrestaShop in 1 clic](https://www.datafirefly.com/it/product/datafirefly-cache-clear/) | [Documentazione](https://www.datafirefly.com/it/documentation/datafirefly-cache-clear/) |
| 🇳🇱 Nederlands | [PrestaShop cache legen met één klik](https://www.datafirefly.com/nl/product/datafirefly-cache-clear/) | [Documentatie](https://www.datafirefly.com/nl/documentation/datafirefly-cache-clear/) |
| 🇵🇱 Polski | [Czyszczenie pamięci podręcznej PrestaShop](https://www.datafirefly.com/pl/produkt/datafirefly-cache-clear/) | [Dokumentacja](https://www.datafirefly.com/pl/documentation/datafirefly-cache-clear/) |
| 🇵🇹 Português | [Limpar a cache do PrestaShop num clique](https://www.datafirefly.com/pt/produto/limpar-cache-prestashop/) | [Documentação](https://www.datafirefly.com/pt/documentation/datafirefly-cache-clear/) |

> 🇫🇷 **En bref** — Module PrestaShop 8 et 9 gratuit qui ajoute un bouton « Vider le cache » dans la barre d'outils du back-office : un clic purge les caches Smarty, XML, Media (CSS/JS), Symfony, cache objet et OPcache, sans passer par *Paramètres avancés > Performances* et sans recharger la page. Aucune configuration, multiboutique. [Voir la fiche produit](https://www.datafirefly.com/product/datafirefly-cache-clear/).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Support

- Documentation: <https://www.datafirefly.com/en/documentation/datafirefly-cache-clear/>
- Bug reports and feature requests: [GitHub issues](https://github.com/datafirefly-ltd/dfclearcache/issues)
- Other modules for PrestaShop, WooCommerce and Shopware: <https://www.datafirefly.com/en/>

## License

Free module, © [DataFirefly Limited](https://www.datafirefly.com/en/). All rights reserved.

---

<div align="center">

Made by <a href="https://www.datafirefly.com/en/"><b>DataFirefly</b></a> — PrestaShop, WooCommerce and Shopware modules.

</div>
