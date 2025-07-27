<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use MacymedCacheBoost\CacheManager;
use MacymedCacheBoost\Services\CacheService;
use MacymedCacheBoost\Services\ConfigurationService;
use Db;

class MacymedCacheBoost extends Module
{
    public function __construct()
    {
        $this->name = 'macymedcacheboost';
        $this->tab = 'IMPROVE';
        $this->version = '1.3.7';
        $this->author = 'Macymed';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Macymed CacheBoost');
        $this->description = $this->l('A powerful and safe cache module for PrestaShop 8.1+.');

        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        try {
            // Vérification des prérequis
            if (version_compare(_PS_VERSION_, '8.1.0', '<')) {
                $this->_errors[] = $this->l('This module requires PrestaShop 8.1.0 or higher.');
                return false;
            }

            $this->unregisterModuleHooks();

            // Création du répertoire cache
            $cacheDir = _PS_MODULE_DIR_ . $this->name . '/cache/';
            if (!$this->createCacheDirectory($cacheDir)) {
                return false;
            }

            // Configuration par défaut
            if (!$this->installConfiguration()) {
                return false;
            }

            // Enregistrement des hooks
            if (!$this->installHooks()) {
                return false;
            }

            // Installation des onglets
            if (!$this->installTabs()) {
                return false;
            }

            Tools::clearAllCache();
            return parent::install();

        } catch (\Exception $e) {
            PrestaShopLogger::addLog('[CacheBoost] Installation error: ' . $e->getMessage(), 3);
            $this->_errors[] = $this->l('Installation failed: ') . $e->getMessage();
            return false;
        }
    }

    private function createCacheDirectory($cacheDir)
    {
        if (!is_dir($cacheDir)) {
            if (!mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
                $this->_errors[] = $this->l('Could not create cache directory: ') . $cacheDir;
                return false;
            }
        }

        // Créer les sous-répertoires nécessaires
        $subDirs = ['html', 'assets'];
        foreach ($subDirs as $subDir) {
            $fullPath = $cacheDir . $subDir . '/';
            if (!is_dir($fullPath)) {
                if (!mkdir($fullPath, 0755, true) && !is_dir($fullPath)) {
                    $this->_errors[] = $this->l('Could not create cache subdirectory: ') . $fullPath;
                    return false;
                }
            }
        }

        // Créer le fichier .htaccess pour sécuriser le répertoire cache
        $htaccessContent = "Order deny,allow\nDeny from all\n";
        file_put_contents($cacheDir . '.htaccess', $htaccessContent);

        return true;
    }

    private function installConfiguration()
    {
        $configurationService = $this->get('macymedcacheboost.configuration.service');
        $configs = [
            'ENABLED' => true,
            'ENABLE_DEV_MODE' => false,
            'COMPRESSION_ENABLED' => true,
            'CACHE_AJAX' => false,
            'BOT_CACHE_ENABLED' => true,
            'BOT_USER_AGENTS' => 'Lighthouse,Googlebot,Bingbot,Slurp,DuckDuckBot,Baiduspider,YandexBot,AhrefsBot,SemrushBot,DotBot,Exabot,MJ12bot,Screaming Frog SEO Spider,Wget,curl',
            'ASSET_CACHE_ENABLED' => false,
            'ASSET_EXTENSIONS' => 'js,css,png,jpg,jpeg,gif,webp,svg',
            'ASSET_DURATION' => 86400,
            'CACHE_HOMEPAGE' => true,
            'CACHE_CATEGORY' => true,
            'CACHE_PRODUCT' => true,
            'CACHE_CMS' => true,
            'AUTO_WARMUP' => true,
            'PURGE_AGE' => 0,
            'PURGE_SIZE' => 0,
            'DURATION' => 3600,
            'EXCLUDE' => '',
            'ENGINE' => 'filesystem',
            'REDIS_IP' => '127.0.0.1',
            'REDIS_PORT' => 6379,
            'MEMCACHED_IP' => '127.0.0.1',
            'MEMCACHED_PORT' => 11211,
            'HITS' => 0,
            'MISSES' => 0,
            'LAST_FLUSH' => '',
            'WARMING_QUEUE' => '[]'
        ];

        foreach ($configs as $key => $value) {
            if (!$configurationService->update($key, $value)) {
                $this->_errors[] = $this->l('Failed to save configuration: ') . $key;
                return false;
            }
        }
        return true;
    }

    private function installHooks()
    {
        $hooks = [
            'actionDispatcherBefore',
            'actionFrontControllerInitBefore',
            'actionProductUpdate',
            'actionProductDelete',
            'actionCategoryUpdate',
            'actionCategoryDelete',
            'actionAfterUpdateCmsPageFormHandler'
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                $this->_errors[] = $this->l('Failed to register hook: ') . $hook;
                return false;
            }
        }
        return true;
    }

    private function installTabs()
    {
        $tabs = $this->getTabs();
        foreach ($tabs as $tabData) {
            $tab = new Tab();
            $tab->class_name = $tabData['class_name'];
            $tab->module = $this->name;
            $tab->active = 1;
            $tab->icon = $tabData['icon'] ?? '';

            // Définir le nom de l'onglet pour toutes les langues
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $tabData['name'];
            }

            // Définir le parent
            if (isset($tabData['parent_class_name'])) {
                $parentTabId = (int) Tab::getIdFromClassName($tabData['parent_class_name']);
                if (!$parentTabId) {
                    // Fallback pour les onglets parents si non trouvés (ex: IMPROVE)
                    if ($tabData['parent_class_name'] === 'IMPROVE') {
                        $parentTabId = (int) Tab::getIdFromClassName('AdminParentModulesSf');
                    }
                }
                $tab->id_parent = $parentTabId;
            } else {
                $tab->id_parent = 0; // Onglet racine si aucun parent n'est spécifié
            }

            // Définir la route Symfony si elle existe
            if (isset($tabData['route_name'])) {
                $tab->route_name = $tabData['route_name'];
            }

            if (!$tab->save()) {
                $this->_errors[] = $this->l('Failed to create tab: ') . $tabData['name'];
                return false;
            }
        }

        return true;
    }

    private function uninstallTabs()
    {
        $tabs = $this->getTabs();
        foreach ($tabs as $tabData) {
            $id_tab = (int) Tab::getIdFromClassName($tabData['class_name']);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                try {
                    $tab->delete();
                } catch (\Exception $e) {
                    PrestaShopLogger::addLog('[CacheBoost] Failed to delete tab ' . $tabData['class_name'] . ': ' . $e->getMessage(), 3);
                }
            }
        }
    }

    private function unregisterModuleHooks()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT h.name
             FROM ' . _DB_PREFIX_ . 'hook h
             INNER JOIN ' . _DB_PREFIX_ . 'hook_module hm
                 ON h.id_hook = hm.id_hook
             WHERE hm.id_module = ' . (int) $this->id
        );

        foreach ($rows as $row) {
            $this->unregisterHook($row['name']);
        }
    }

    public function getTabs()
    {
        return [
            [
                'class_name' => 'AdminMacymedCacheBoostDashboard',
                'route_name' => 'macymedcacheboost_dashboard',
                'name' => $this->l('CacheBoost'),
                'icon' => 'cached',
                'parent_class_name' => 'IMPROVE'
            ],
            [
                'class_name' => 'AdminMacymedCacheBoostGeneral',
                'route_name' => 'macymedcacheboost_general',
                'name' => $this->l('General Settings'),
                'parent_class_name' => 'AdminMacymedCacheBoostDashboard'
            ],
            [
                'class_name' => 'AdminMacymedCacheBoostPageTypes',
                'route_name' => 'macymedcacheboost_pagetypes',
                'name' => $this->l('Page Types'),
                'parent_class_name' => 'AdminMacymedCacheBoostDashboard'
            ],
            [
                'class_name' => 'AdminMacymedCacheBoostAssets',
                'route_name' => 'macymedcacheboost_assets',
                'name' => $this->l('Assets'),
                'parent_class_name' => 'AdminMacymedCacheBoostDashboard'
            ],
            [
                'class_name' => 'AdminMacymedCacheBoostBots',
                'route_name' => 'macymedcacheboost_bots',
                'name' => $this->l('Bots'),
                'parent_class_name' => 'AdminMacymedCacheBoostDashboard'
            ],
            [
                'class_name' => 'AdminMacymedCacheBoostInvalidation',
                'route_name' => 'macymedcacheboost_invalidation',
                'name' => $this->l('Invalidation'),
                'parent_class_name' => 'AdminMacymedCacheBoostDashboard'
            ],
            [
                'class_name' => 'AdminMacymedCacheBoostWarmer',
                'route_name' => 'macymedcacheboost_warmer',
                'name' => $this->l('Warmer'),
                'parent_class_name' => 'AdminMacymedCacheBoostDashboard'
            ],
            [
                'class_name' => 'AdminMacymedCacheBoostRedis',
                'route_name' => 'macymedcacheboost_redis',
                'name' => $this->l('Redis'),
                'parent_class_name' => 'AdminMacymedCacheBoostDashboard'
            ],
            [
                'class_name' => 'AdminMacymedCacheBoostMemcached',
                'route_name' => 'macymedcacheboost_memcached',
                'name' => $this->l('Memcached'),
                'parent_class_name' => 'AdminMacymedCacheBoostDashboard'
            ],
        ];
    }

    // Amélioration de la méthode uninstall() également
    public function uninstall()
    {
        try {
            $this->unregisterModuleHooks();
            $configurationService = $this->get('macymedcacheboost.configuration.service');

            // Suppression de toutes les configurations
            foreach ([
                'DURATION',
                'EXCLUDE',
                'ENGINE',
                'REDIS_IP',
                'REDIS_PORT',
                'MEMCACHED_IP',
                'MEMCACHED_PORT',
                'ENABLED',
                'ENABLE_DEV_MODE',
                'COMPRESSION_ENABLED',
                'CACHE_AJAX',
                'BOT_CACHE_ENABLED',
                'BOT_USER_AGENTS',
                'ASSET_CACHE_ENABLED',
                'ASSET_EXTENSIONS',
                'ASSET_DURATION',
                'CACHE_HOMEPAGE',
                'CACHE_CATEGORY',
                'CACHE_PRODUCT',
                'CACHE_CMS',
                'AUTO_WARMUP',
                'PURGE_AGE',
                'PURGE_SIZE',
                'HITS',
                'MISSES',
                'LAST_FLUSH',
                'WARMING_QUEUE'
            ] as $key) {
                $configurationService->delete($key);
            }

            // Nettoyage du cache
            $this->get('macymedcacheboost.cache_manager')->uninstallCache();

            // Suppression des onglets
            $this->uninstallTabs();

            // Nettoyage général
            Tools::clearAllCache();

            return parent::uninstall();

        } catch (\Exception $e) {
            PrestaShopLogger::addLog('[CacheBoost] Uninstallation error: ' . $e->getMessage(), 3);
            $this->_errors[] = $this->l('Uninstallation failed: ') . $e->getMessage();
            return false;
        }
    }

    // Amélioration de la méthode getContent() pour éviter les erreurs Symfony
    public function getContent()
    {
        // Vérifier si nous sommes en mode Symfony
        if (class_exists('Symfony\Component\Routing\Router')) {
            try {
                // Tentative de redirection Symfony
                if (method_exists($this, 'get')) {
                    $router = $this->get('router');
                    if ($router) {
                        $url = $router->generate('macymedcacheboost_dashboard');
                        Tools::redirectAdmin($url);
                        return;
                    }
                }
            } catch (\Exception $e) {
                // Log l'erreur mais continue avec le fallback
                PrestaShopLogger::addLog('[CacheBoost] Symfony routing error: ' . $e->getMessage(), 2);
            }
        }

        // Fallback: redirection vers le contrôleur admin classique
        try {
            $admin_link = Context::getContext()->link->getAdminLink('AdminMacymedCacheBoostDashboard');
            Tools::redirectAdmin($admin_link);
        } catch (\Exception $e) {
            // Si même le fallback échoue, afficher un message d'erreur
            $this->context->smarty->assign([
                'module_name' => $this->displayName,
                'error_message' => $this->l('Unable to access module configuration. Please check your installation.')
            ]);
            return $this->display(__FILE__, 'views/templates/admin/error.tpl');
        }
    }

    public function hookActionDispatcherBefore($params)
    {
        $this->get('macymedcacheboost.cache_manager')->checkAndServeCache();
    }

    
    public function hookActionFrontControllerInitBefore($params)

    {
        $this->get('macymedcacheboost.cache_manager')->checkAndServeCache();
    }

    public function hookActionProductUpdate($params)
    {
        if (isset($params['product'])) {
            CacheService::invalidateProductCache($params['product']->id);
        }
    }

    public function hookActionProductDelete($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateProductCache($params['object']->id);
        }
    }

    public function hookActionCategoryUpdate($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateCategoryCache($params['object']->id);
        }
    }

    public function hookActionCategoryDelete($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateCategoryCache($params['object']->id);
        }
    }

    public function hookActionAfterUpdateCmsPageFormHandler($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateCmsCache($params['object']->id);
        }
    }

    /* Hook removed in PrestaShop 8.1: actionObjectCmsDeleteAfter */

}
