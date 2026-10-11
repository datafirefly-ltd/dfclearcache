<?php
/**
 * DataFirefly Cache Clear - upgrade to 1.1.0
 *
 * Ships translations, the access check and the new purge steps.
 * Resets OPcache so the new controller code is served immediately.
 *
 * @author    DataFirefly
 * @copyright DataFirefly Limited
 * @license   Proprietary
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }

    return true;
}
