<?php
/**
 * DataFirefly Cache Clear
 *
 * Adds a "Clear cache" button to the back-office toolbar that purges
 * the Smarty, XML, Media, Symfony, object and OPcache caches in one click.
 *
 * @author    DataFirefly
 * @copyright DataFirefly Limited
 * @license   Proprietary
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Dfclearcache extends Module
{
    public const CONTROLLER = 'AdminDfClearCache';

    public function __construct()
    {
        $this->name = 'dfclearcache';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'DataFirefly';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('DataFirefly - Clear cache');
        $this->description = $this->l('Adds a "Clear cache" button to the back-office toolbar that purges the Smarty, XML, Media, Symfony and OPcache caches in one click.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        return parent::install()
            && $this->installTab()
            && $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        return $this->uninstallTab()
            && parent::uninstall();
    }

    /**
     * Hidden tab hosting the AJAX controller.
     */
    private function installTab()
    {
        if ((int) Tab::getIdFromClassName(self::CONTROLLER)) {
            return true;
        }

        $tab = new Tab();
        $tab->class_name = self::CONTROLLER;
        $tab->module = $this->name;
        $tab->active = true;
        $tab->id_parent = -1; // hidden from the menu

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = 'DataFirefly Cache Clear';
        }

        return (bool) $tab->add();
    }

    private function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName(self::CONTROLLER);
        if ($idTab) {
            $tab = new Tab($idTab);

            return (bool) $tab->delete();
        }

        return true;
    }

    /**
     * Only show the button to employees whose profile can reach the AJAX
     * controller: otherwise the click would end on an "access denied" page.
     */
    private function employeeCanClearCache()
    {
        $employee = $this->context->employee;
        if (!Validate::isLoadedObject($employee)) {
            return false;
        }

        if (defined('_PS_ADMIN_PROFILE_') && (int) $employee->id_profile === (int) _PS_ADMIN_PROFILE_) {
            return true;
        }

        $idTab = (int) Tab::getIdFromClassName(self::CONTROLLER);
        if (!$idTab || !method_exists('Profile', 'getProfileAccess')) {
            return false;
        }

        $access = Profile::getProfileAccess((int) $employee->id_profile, $idTab);

        return is_array($access) && !empty($access['view']) && (string) $access['view'] !== '0';
    }

    /**
     * Injects the assets and the button into the back-office.
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        $controller = $this->context->controller;
        $controllerName = isset($controller->controller_name)
            ? $controller->controller_name
            : Tools::getValue('controller');

        if ($controllerName === 'AdminLogin' || !$this->employeeCanClearCache()) {
            return;
        }

        $controller->addCSS($this->_path . 'views/css/admin.css?v=' . $this->version);

        Media::addJsDef([
            'dfClearCacheUrl' => $this->context->link->getAdminLink(
                self::CONTROLLER,
                true,
                [],
                ['ajax' => 1, 'action' => 'ClearCache']
            ),
            'dfClearCacheLabels' => [
                'button' => $this->l('Clear cache'),
                'loading' => $this->l('Clearing...'),
                'success' => $this->l('Cache cleared.'),
                'error' => $this->l('Error while clearing the cache.'),
                'session' => $this->l('Your session has expired. Reload the page and try again.'),
            ],
        ]);

        $controller->addJS($this->_path . 'views/js/admin.js?v=' . $this->version);
    }
}
