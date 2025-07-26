<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use MacymedCacheBoost\CacheManager;
use MacymedCacheBoost\Services\CacheService;
use MacymedCacheBoost\Services\ConfigurationService;
use Language;
use Tab;
use Tools;
use Context;
use Configuration;

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
        $hook_success = false;
        $config_success = false;
        try {
        $cacheDir = _PS_MODULE_DIR_ . $this->name . '/cache/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $config_success = ConfigurationService::update('CACHEBOOST_ENABLED', true)
            && ConfigurationService::update('CACHEBOOST_ENABLE_DEV_MODE', false)
            && ConfigurationService::update('CACHEBOOST_COMPRESSION_ENABLED', true)
            && ConfigurationService::update('CACHEBOOST_CACHE_AJAX', false)
            && ConfigurationService::update('CACHEBOOST_BOT_CACHE_ENABLED', true)
            && ConfigurationService::update('CACHEBOOST_BOT_USER_AGENTS', 'Lighthouse,Googlebot,Bingbot,Slurp,DuckDuckBot,Baiduspider,YandexBot,AhrefsBot,SemrushBot,DotBot,Exabot,MJ12bot,Screaming Frog SEO Spider,Wget,curl')
            && ConfigurationService::update('CACHEBOOST_ASSET_CACHE_ENABLED', false)
            && ConfigurationService::update('CACHEBOOST_ASSET_EXTENSIONS', 'js,css,png,jpg,jpeg,gif,webp,svg')
            && ConfigurationService::update('CACHEBOOST_ASSET_DURATION', 86400)
            && ConfigurationService::update('CACHEBOOST_CACHE_HOMEPAGE', true)
            && ConfigurationService::update('CACHEBOOST_CACHE_CATEGORY', true)
            && ConfigurationService::update('CACHEBOOST_CACHE_PRODUCT', true)
            && ConfigurationService::update('CACHEBOOST_CACHE_CMS', true)
            && ConfigurationService::update('CACHEBOOST_AUTO_WARMUP', true)
            && ConfigurationService::update('CACHEBOOST_PURGE_AGE', 0)
            && ConfigurationService::update('CACHEBOOST_PURGE_SIZE', 0);

        $hook_success = $this->registerHook('actionDispatcherBefore')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('actionObjectProductDeleteAfter')
            && $this->registerHook('actionObjectCategoryUpdateAfter')
            && $this->registerHook('actionObjectCategoryDeleteAfter')
            && $this->registerHook('actionAdminCmsPageUpdateAfter')
            && $this->registerHook('actionObjectCmsDeleteAfter')
            && $this->registerHook('displayAdminNavBarBeforeEnd');
             } catch (\Throwable $e) {
        PrestaShopLogger::addLog('[CacheBoost] Erreur install(): ' . $e->getMessage());
        return false;
    }

        $parentTabId = (int) Tab::getIdFromClassName('IMPROVE');
        if (!$parentTabId) {
            $parentTabId = (int) Tab::getIdFromClassName('AdminParentModulesSf');
        }

        $mainTab = new Tab();
        $mainTab->class_name = 'AdminMacymedCacheBoost';
        $mainTab->id_parent = $parentTabId;
        $mainTab->module = $this->name;
        $mainTab->active = 1;
        foreach (Language::getLanguages(true) as $lang) {
            $mainTab->name[$lang['id_lang']] = 'Macymed CacheBoost';
        }

        $tab_success = $mainTab->save();

        foreach ($this->getTabs() as $tabData) {
            if ($tabData['class_name'] === 'AdminMacymedCacheBoost') {
                continue;
            }
            $tab = new Tab();
            $tab->class_name = $tabData['class_name'];
            $tab->id_parent = (int) Tab::getIdFromClassName('AdminMacymedCacheBoost');
            $tab->module = $this->name;
            $tab->active = 1;
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $tabData['name'];
            }
            $tab_success = $tab_success && $tab->save();
        }

        Tools::clearAllCache();

        return parent::install() && $config_success && $hook_success && $tab_success;
    }

    public function uninstall()
    {
        foreach ([
            'CACHEBOOST_DURATION', 'CACHEBOOST_EXCLUDE', 'CACHEBOOST_ENGINE', 'CACHEBOOST_REDIS_IP', 'CACHEBOOST_REDIS_PORT',
            'CACHEBOOST_MEMCACHED_IP', 'CACHEBOOST_MEMCACHED_PORT', 'CACHEBOOST_ENABLED', 'CACHEBOOST_ENABLE_DEV_MODE',
            'CACHEBOOST_COMPRESSION_ENABLED', 'CACHEBOOST_CACHE_AJAX', 'CACHEBOOST_BOT_CACHE_ENABLED', 'CACHEBOOST_BOT_USER_AGENTS',
            'CACHEBOOST_ASSET_CACHE_ENABLED', 'CACHEBOOST_ASSET_EXTENSIONS', 'CACHEBOOST_ASSET_DURATION',
            'CACHEBOOST_CACHE_HOMEPAGE', 'CACHEBOOST_CACHE_CATEGORY', 'CACHEBOOST_CACHE_PRODUCT',
            'CACHEBOOST_CACHE_CMS', 'CACHEBOOST_AUTO_WARMUP', 'CACHEBOOST_PURGE_AGE', 'CACHEBOOST_PURGE_SIZE'
        ] as $key) {
            ConfigurationService::delete($key);
        }

        CacheManager::uninstallCache();

        $tabClasses = array_merge(
            ['AdminMacymedCacheBoost'],
            array_column($this->getTabs(), 'class_name')
        );

        foreach ($tabClasses as $tabClass) {
            $id_tab = (int) Tab::getIdFromClassName($tabClass);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }

        Tools::clearAllCache();

        return parent::uninstall();
    }

    public function getTabs()
    {
        return [
            ['name' => 'Macymed CacheBoost', 'class_name' => 'AdminMacymedCacheBoost'],
            ['name' => 'Dashboard', 'class_name' => 'AdminMacymedCacheBoostDashboard'],
            ['name' => 'General Settings', 'class_name' => 'AdminMacymedCacheBoostGeneral'],
            ['name' => 'Bot Settings', 'class_name' => 'AdminMacymedCacheBoostBots'],
            ['name' => 'Asset Cache', 'class_name' => 'AdminMacymedCacheBoostAssets'],
            ['name' => 'Page Type Cache', 'class_name' => 'AdminMacymedCacheBoostPageTypes'],
            ['name' => 'Redis Settings', 'class_name' => 'AdminMacymedCacheBoostRedis'],
            ['name' => 'Memcached Settings', 'class_name' => 'AdminMacymedCacheBoostMemcached'],
            ['name' => 'Granular Invalidation', 'class_name' => 'AdminMacymedCacheBoostInvalidation'],
            ['name' => 'Cache Warmer', 'class_name' => 'AdminMacymedCacheBoostWarmer'],
        ];
    }

    public function getContent()
    {
        if (method_exists($this, 'get')) {
            Tools::redirectAdmin($this->get('router')->generate('macymedcacheboost_dashboard'));
        }
    }

    public function hookActionDispatcherBefore($params)
    {
        CacheManager::checkAndServeCache();
    }

    public function hookActionProductUpdate($params)
    {
        if (isset($params['product'])) {
            CacheService::invalidateProductCache($params['product']->id);
        }
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateProductCache($params['object']->id);
        }
    }

    public function hookActionObjectCategoryUpdateAfter($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateCategoryCache($params['object']->id);
        }
    }

    public function hookActionObjectCategoryDeleteAfter($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateCategoryCache($params['object']->id);
        }
    }

    public function hookActionAdminCmsPageUpdateAfter($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateCmsCache($params['object']->id);
        }
    }

    public function hookActionObjectCmsDeleteAfter($params)
    {
        if (isset($params['object'])) {
            CacheService::invalidateCmsCache($params['object']->id);
        }
    }

    public function hookDisplayAdminNavBarBeforeEnd($params)
    {
        $context = Context::getContext();
        if ($context && $context->employee && $context->employee->isLoggedBack() && Configuration::get('PS_TOOLBAR_ACTIVE')) {
            $this->context->smarty->assign([
                'admin_link' => $this->context->link->getAdminLink('AdminMacymedCacheBoostDashboard'),
                'token' => Tools::getAdminTokenLite('AdminMacymedCacheBoostDashboard'),
            ]);
            return $this->display(__FILE__, 'views/templates/hook/flush_button.tpl');
        }
    }
}
