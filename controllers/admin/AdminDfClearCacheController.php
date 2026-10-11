<?php
/**
 * AJAX controller that purges the PrestaShop caches.
 *
 * PS 8 / PS 9 compatibility notes:
 *  - no ajaxRender() override (signature changed in PS 9): dedicated renderJson()
 *  - no $this->l() in the controller (removed in PS 9): $this->module->l()
 *
 * @author    DataFirefly
 * @copyright DataFirefly Limited
 * @license   Proprietary
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminDfClearCacheController extends ModuleAdminController
{
    public const TRANSLATION_SOURCE = 'admindfclearcachecontroller';

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function ajaxProcessClearCache()
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
            $this->renderJson([
                'success' => false,
                'cleared' => [],
                'errors' => [],
                'message' => $this->module->l('Invalid request.', self::TRANSLATION_SOURCE),
            ], 405);
        }

        $cleared = [];
        $errors = [];

        // Legacy caches (front and back office templates, XML, CCC media).
        $this->runStep('Smarty', function () {
            Tools::clearSmartyCache();

            return true;
        }, $cleared, $errors);

        $this->runStep('XML', function () {
            Tools::clearXMLCache();

            return true;
        }, $cleared, $errors);

        $this->runStep('Media', function () {
            Media::clearCache();

            return true;
        }, $cleared, $errors);

        // Symfony container and kernel caches.
        $this->runStep('Symfony', function () {
            return $this->clearSymfonyCache();
        }, $cleared, $errors);

        // Object cache (Memcached, APCu...), only when enabled in parameters.
        if (defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_ && class_exists('Cache')) {
            $this->runStep('Object cache', function () {
                Cache::getInstance()->flush();

                return true;
            }, $cleared, $errors);
        }

        // PHP OPcache: silently skipped when disabled or restricted by the host.
        if ($this->resetOpcache()) {
            $cleared[] = 'OPcache';
        }

        $success = empty($errors);

        if (class_exists('PrestaShopLogger')) {
            PrestaShopLogger::addLog(
                'DataFirefly Cache Clear: ' . ($success ? 'cache cleared' : 'cache partially cleared')
                . ' (' . implode(', ', $cleared) . ')',
                $success ? 1 : 2,
                null,
                null,
                null,
                true,
                (int) $this->context->employee->id
            );
        }

        $this->renderJson([
            'success' => $success,
            'cleared' => $cleared,
            'errors' => $errors,
            'message' => $success
                ? $this->module->l('Cache cleared successfully.', self::TRANSLATION_SOURCE)
                : $this->module->l('Cache partially cleared.', self::TRANSLATION_SOURCE),
        ]);
    }

    /**
     * Runs one purge step and records its outcome without stopping the others.
     *
     * @param string $label
     * @param callable $step returns true when something was cleared
     * @param array $cleared
     * @param array $errors
     */
    private function runStep($label, callable $step, array &$cleared, array &$errors)
    {
        try {
            if ($step()) {
                $cleared[] = $label;
            }
        } catch (Throwable $e) {
            $errors[] = $label . ': ' . $e->getMessage();
        }
    }

    /**
     * Clears the Symfony cache, from the safest strategy to the last resort.
     * Every strategy is deferred to the end of the request, so the container
     * currently running is never deleted under its own feet.
     *
     * @return bool
     */
    private function clearSymfonyCache()
    {
        // 1) Native clearers from the container (PS 8, and PS 9 when public).
        if (class_exists('\PrestaShop\PrestaShop\Adapter\SymfonyContainer')) {
            $container = PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();

            if ($container !== null) {
                $serviceIds = [
                    'prestashop.core.cache.clearer.cache_clearer_chain',
                    'prestashop.adapter.cache.clearer.symfony_cache_clearer',
                ];

                foreach ($serviceIds as $serviceId) {
                    try {
                        if (!$container->has($serviceId)) {
                            continue;
                        }
                        $clearer = $container->get($serviceId);
                        if (is_object($clearer) && method_exists($clearer, 'clear')) {
                            $clearer->clear();

                            return true;
                        }
                    } catch (Throwable $e) {
                        // Private or missing service: try the next strategy.
                    }
                }
            }
        }

        // 2) Legacy native helper (deferred removal at shutdown).
        if (method_exists('Tools', 'clearSf2Cache')) {
            Tools::clearSf2Cache();

            return true;
        }

        // 3) Last resort: our own deferred removal.
        return $this->scheduleCacheDirectoryPurge();
    }

    /**
     * Schedules the removal of var/cache/{env} at shutdown. The directory is
     * renamed first, so concurrent requests rebuild a fresh cache instead of
     * reading a half-deleted one.
     *
     * @return bool
     */
    private function scheduleCacheDirectoryPurge()
    {
        if (!defined('_PS_ROOT_DIR_')) {
            return false;
        }

        if (defined('_PS_ENV_')) {
            $env = (string) _PS_ENV_;
        } else {
            $env = (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) ? 'dev' : 'prod';
        }

        if (!preg_match('/^[a-z0-9_-]+$/i', $env)) {
            return false;
        }

        $baseDir = realpath(_PS_ROOT_DIR_ . '/var/cache');
        $dir = $baseDir ? realpath($baseDir . DIRECTORY_SEPARATOR . $env) : false;

        if (!$dir || !is_dir($dir) || dirname($dir) !== $baseDir) {
            return false;
        }

        register_shutdown_function(function () use ($dir) {
            $trash = $dir . '_dfclearcache_' . uniqid();

            if (@rename($dir, $trash)) {
                AdminDfClearCacheController::removeTree($trash);
                @rmdir($trash);
            } else {
                AdminDfClearCacheController::removeTree($dir);
            }
        });

        return true;
    }

    /**
     * Recursively deletes the contents of a directory (not the directory itself).
     *
     * @param string $dir
     */
    public static function removeTree($dir)
    {
        $items = @scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path) && !is_link($path)) {
                self::removeTree($path);
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    }

    /**
     * @return bool true when OPcache was actually reset
     */
    private function resetOpcache()
    {
        if (!function_exists('opcache_reset')) {
            return false;
        }

        $enabled = ini_get('opcache.enable');
        if ($enabled === false || $enabled === '' || $enabled === '0') {
            return false;
        }

        return (bool) @opcache_reset();
    }

    /**
     * Dedicated JSON output (avoids overriding ajaxRender(), incompatible with PS 9).
     *
     * @param array $data
     * @param int $status
     */
    private function renderJson(array $data, $status = 200)
    {
        if (!headers_sent()) {
            http_response_code((int) $status);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
        }

        echo json_encode($data);
        exit;
    }
}
