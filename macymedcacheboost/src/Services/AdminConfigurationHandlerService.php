<?php

namespace MacymedCacheBoost\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Tools;
use MacymedCacheBoost\Services\ConfigurationService;
use MacymedCacheBoost\CacheManager;

class AdminConfigurationHandlerService
{
    public static function handleForm(
        $token,
        $controller
    )
    {
        if (Tools::isSubmit('submit_cacheboost_config')) {

            if (!Tools::getValue('token') || Tools::getValue('token') !== $token) {
                $controller->errors[] = $controller->trans('Invalid security token', [], 'Admin.Notifications.Error');
                return;
            }

            ConfigurationService::update('CACHEBOOST_DURATION', (int) Tools::getValue('CACHEBOOST_DURATION'));
            ConfigurationService::update('CACHEBOOST_EXCLUDE', Tools::getValue('CACHEBOOST_EXCLUDE'));
            ConfigurationService::update('CACHEBOOST_ENGINE', Tools::getValue('CACHEBOOST_ENGINE'));
            ConfigurationService::update('CACHEBOOST_REDIS_IP', Tools::getValue('CACHEBOOST_REDIS_IP'));
            ConfigurationService::update('CACHEBOOST_REDIS_PORT', Tools::getValue('CACHEBOOST_REDIS_PORT'));
            ConfigurationService::update('CACHEBOOST_MEMCACHED_IP', Tools::getValue('CACHEBOOST_MEMCACHED_IP'));
            ConfigurationService::update('CACHEBOOST_MEMCACHED_PORT', Tools::getValue('CACHEBOOST_MEMCACHED_PORT'));
            ConfigurationService::update('CACHEBOOST_ENABLED', (bool) Tools::getValue('CACHEBOOST_ENABLED'));
            ConfigurationService::update('CACHEBOOST_ENABLE_DEV_MODE', (bool) Tools::getValue('CACHEBOOST_ENABLE_DEV_MODE'));
            $old_compression_enabled = (bool) ConfigurationService::get('CACHEBOOST_COMPRESSION_ENABLED');
            ConfigurationService::update('CACHEBOOST_COMPRESSION_ENABLED', (bool) Tools::getValue('CACHEBOOST_COMPRESSION_ENABLED'));
            ConfigurationService::update('CACHEBOOST_CACHE_AJAX', (bool) Tools::getValue('CACHEBOOST_CACHE_AJAX'));
            ConfigurationService::update('CACHEBOOST_BOT_CACHE_ENABLED', (bool) Tools::getValue('CACHEBOOST_BOT_CACHE_ENABLED'));
            ConfigurationService::update('CACHEBOOST_BOT_USER_AGENTS', Tools::getValue('CACHEBOOST_BOT_USER_AGENTS'));
            ConfigurationService::update('CACHEBOOST_ASSET_CACHE_ENABLED', (bool) Tools::getValue('CACHEBOOST_ASSET_CACHE_ENABLED'));
            ConfigurationService::update('CACHEBOOST_ASSET_EXTENSIONS', Tools::getValue('CACHEBOOST_ASSET_EXTENSIONS'));
            ConfigurationService::update('CACHEBOOST_ASSET_DURATION', (int) Tools::getValue('CACHEBOOST_ASSET_DURATION'));
            ConfigurationService::update('CACHEBOOST_CACHE_HOMEPAGE', (bool) Tools::getValue('CACHEBOOST_CACHE_HOMEPAGE'));
            ConfigurationService::update('CACHEBOOST_CACHE_CATEGORY', (bool) Tools::getValue('CACHEBOOST_CACHE_CATEGORY'));
            ConfigurationService::update('CACHEBOOST_CACHE_PRODUCT', (bool) Tools::getValue('CACHEBOOST_CACHE_PRODUCT'));
            ConfigurationService::update('CACHEBOOST_CACHE_CMS', (bool) Tools::getValue('CACHEBOOST_CACHE_CMS'));
            ConfigurationService::update('CACHEBOOST_PURGE_AGE', (int) Tools::getValue('CACHEBOOST_PURGE_AGE'));
            ConfigurationService::update('CACHEBOOST_PURGE_SIZE', (int) Tools::getValue('CACHEBOOST_PURGE_SIZE'));

            if ($old_compression_enabled !== (bool) Tools::getValue('CACHEBOOST_COMPRESSION_ENABLED')) {
                CacheManager::invalidateAll();
                $controller->context->controller->confirmations[] = $controller->context->getContext()->getTranslator()->trans('Cache flushed due to compression setting change.', [], 'Modules.Macymedcacheboost.Admin');
            }
            $controller->context->controller->confirmations[] = $controller->context->getContext()->getTranslator()->trans('Configuration saved successfully', [], 'Modules.Macymedcacheboost.Admin');
        }

        if (Tools::isSubmit('flush_cache')) {
            try {
                if (CacheManager::invalidateAll()) {
                    $controller->context->controller->confirmations[] = $controller->context->getContext()->getTranslator()->trans('Cache flushed successfully', [], 'Modules.Macymedcacheboost.Admin');
                } else {
                    $controller->context->controller->errors[] = $controller->context->getContext()->getTranslator()->trans('Failed to flush cache. Check connection or permissions.', [], 'Modules.Macymedcacheboost.Admin');
                }
            } catch (\Exception $e) {
                $controller->context->controller->errors[] = $controller->context->getContext()->getTranslator()->trans('An error occurred while flushing cache: ', [], 'Modules.Macymedcacheboost.Admin') . $e->getMessage();
            }
        }
    }
}
