<?php
/**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2024 PrestaShop SA
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

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
        $this->version = '1.3.2'; // Version incrémentée
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
        $cacheDir = _PS_MODULE_DIR_ . $this->name . '/cache/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        if (
            !parent::install()
            || !ConfigurationService::update('CACHEBOOST_ENABLED', true)
            || !ConfigurationService::update('CACHEBOOST_ENABLE_DEV_MODE', false)
            || !ConfigurationService::update('CACHEBOOST_COMPRESSION_ENABLED', true)
            || !ConfigurationService::update('CACHEBOOST_CACHE_AJAX', false)
            || !ConfigurationService::update('CACHEBOOST_BOT_CACHE_ENABLED', true)
            || !ConfigurationService::update('CACHEBOOST_BOT_USER_AGENTS', 'Lighthouse,Googlebot,Bingbot,Slurp,DuckDuckBot,Baiduspider,YandexBot,AhrefsBot,SemrushBot,DotBot,Exabot,MJ12bot,Screaming Frog SEO Spider,Wget,curl')
            || !ConfigurationService::update('CACHEBOOST_ASSET_CACHE_ENABLED', false)
            || !ConfigurationService::update('CACHEBOOST_ASSET_EXTENSIONS', 'js,css,png,jpg,jpeg,gif,webp,svg')
            || !ConfigurationService::update('CACHEBOOST_ASSET_DURATION', 86400)
            || !ConfigurationService::update('CACHEBOOST_CACHE_HOMEPAGE', true)
            || !ConfigurationService::update('CACHEBOOST_CACHE_CATEGORY', true)
            || !ConfigurationService::update('CACHEBOOST_CACHE_PRODUCT', true)
            || !ConfigurationService::update('CACHEBOOST_CACHE_CMS', true)
            || !ConfigurationService::update('CACHEBOOST_AUTO_WARMUP', true)
            || !ConfigurationService::update('CACHEBOOST_PURGE_AGE', 0) // 0 means disabled
            || !ConfigurationService::update('CACHEBOOST_PURGE_SIZE', 0) // 0 means disabled
            || !$this->registerHook('actionDispatcherBefore')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('actionObjectProductDeleteAfter')
            || !$this->registerHook('actionObjectCategoryUpdateAfter')
            || !$this->registerHook('actionObjectCategoryDeleteAfter')
            || !$this->registerHook('actionAdminCmsPageUpdateAfter')
            || !$this->registerHook('actionObjectCmsDeleteAfter')
            || !$this->registerHook('displayAdminNavBarBeforeEnd')
        ) {
            return false;
        }

        // Clear all PrestaShop cache to ensure new tabs/controllers are recognized
        Tools::clearAllCache();

        return true;
    }

    public function uninstall()
    {
        ConfigurationService::delete('CACHEBOOST_DURATION');
        ConfigurationService::delete('CACHEBOOST_EXCLUDE');
        ConfigurationService::delete('CACHEBOOST_ENGINE');
        ConfigurationService::delete('CACHEBOOST_REDIS_IP');
        ConfigurationService::delete('CACHEBOOST_REDIS_PORT');
        ConfigurationService::delete('CACHEBOOST_MEMCACHED_IP');
        ConfigurationService::delete('CACHEBOOST_MEMCACHED_PORT');
        ConfigurationService::delete('CACHEBOOST_ENABLED');
        ConfigurationService::delete('CACHEBOOST_ENABLE_DEV_MODE');
        ConfigurationService::delete('CACHEBOOST_COMPRESSION_ENABLED');
        ConfigurationService::delete('CACHEBOOST_CACHE_AJAX');
        ConfigurationService::delete('CACHEBOOST_BOT_CACHE_ENABLED');
        ConfigurationService::delete('CACHEBOOST_BOT_USER_AGENTS');
        ConfigurationService::delete('CACHEBOOST_ASSET_CACHE_ENABLED');
        ConfigurationService::delete('CACHEBOOST_ASSET_EXTENSIONS');
        ConfigurationService::delete('CACHEBOOST_ASSET_DURATION');
        ConfigurationService::delete('CACHEBOOST_CACHE_HOMEPAGE');
        ConfigurationService::delete('CACHEBOOST_CACHE_CATEGORY');
        ConfigurationService::delete('CACHEBOOST_CACHE_PRODUCT');
        ConfigurationService::delete('CACHEBOOST_CACHE_CMS');
        ConfigurationService::delete('CACHEBOOST_AUTO_WARMUP');
        ConfigurationService::delete('CACHEBOOST_PURGE_AGE');
        ConfigurationService::delete('CACHEBOOST_PURGE_SIZE');

        CacheManager::uninstallCache();

        return parent::uninstall();
    }

    public function getTabs()
    {
        return [
            [
                'name' => 'Macymed CacheBoost',
                'class_name' => 'AdminMacymedCacheBoost',
                'visible' => true,
                'parent_class_name' => 'AdminParentImprove',
                'module' => $this->name,
                'icon' => 'tune',
            ],
            [
                'name' => 'Dashboard',
                'class_name' => 'AdminMacymedCacheBoostDashboard',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
            [
                'name' => 'General Settings',
                'class_name' => 'AdminMacymedCacheBoostGeneral',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
            [
                'name' => 'Bot Settings',
                'class_name' => 'AdminMacymedCacheBoostBots',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
            [
                'name' => 'Asset Cache',
                'class_name' => 'AdminMacymedCacheBoostAssets',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
            [
                'name' => 'Page Type Cache',
                'class_name' => 'AdminMacymedCacheBoostPageTypes',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
            [
                'name' => 'Redis Settings',
                'class_name' => 'AdminMacymedCacheBoostRedis',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
            [
                'name' => 'Memcached Settings',
                'class_name' => 'AdminMacymedCacheBoostMemcached',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
            [
                'name' => 'Granular Invalidation',
                'class_name' => 'AdminMacymedCacheBoostInvalidation',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
            [
                'name' => 'Cache Warmer',
                'class_name' => 'AdminMacymedCacheBoostWarmer',
                'visible' => true,
                'parent_class_name' => 'AdminMacymedCacheBoost',
            ],
        ];
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->get('router')->generate('macymedcacheboost_dashboard'));
    }

    public function hookActionDispatcherBefore($params)
    {
        CacheManager::checkAndServeCache();
    }

    // --- Hooks d'invalidation granulaire ---

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
        if (Context::getContext()->employee->isLoggedBack() && Configuration::get('PS_TOOLBAR_ACTIVE')) {
            $this->context->smarty->assign([
                'admin_link' => $this->context->link->getAdminLink('AdminMacymedCacheBoostDashboard'),
                'token' => Tools::getAdminTokenLite('AdminMacymedCacheBoostDashboard'),
            ]);
            return $this->display(__FILE__, 'views/templates/hook/flush_button.tpl');
        }
    }
}
