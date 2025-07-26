<?php

namespace MacymedCacheBoost\Services;

use Configuration;

class ConfigurationService
{
    public static function get($key, $default = null)
    {
        return Configuration::get($key, null, null, $default);
    }

    public static function update($key, $value)
    {
        return Configuration::updateValue($key, $value);
    }

    public static function delete($key)
    {
        return Configuration::deleteByName($key);
    }

    public static function getAllConfigValues()
    {
        return [
            'duration' => self::get('CACHEBOOST_DURATION', 3600),
            'exclude' => self::get('CACHEBOOST_EXCLUDE', ''),
            'engine' => self::get('CACHEBOOST_ENGINE', 'filesystem'),
            'redis_ip' => self::get('CACHEBOOST_REDIS_IP', '127.0.0.1'),
            'redis_port' => self::get('CACHEBOOST_REDIS_PORT', 6379),
            'memcached_ip' => self::get('CACHEBOOST_MEMCACHED_IP', '127.0.0.1'),
            'memcached_port' => self::get('CACHEBOOST_MEMCACHED_PORT', 11211),
            'enabled' => (bool) self::get('CACHEBOOST_ENABLED', true),
            'enable_dev_mode' => (bool) self::get('CACHEBOOST_ENABLE_DEV_MODE', false),
            'compression_enabled' => (bool) self::get('CACHEBOOST_COMPRESSION_ENABLED', true),
            'cache_ajax' => (bool) self::get('CACHEBOOST_CACHE_AJAX', false),
            'bot_cache_enabled' => (bool) self::get('CACHEBOOST_BOT_CACHE_ENABLED', true),
            'bot_user_agents' => self::get('CACHEBOOST_BOT_USER_AGENTS', 'Lighthouse,Googlebot,Bingbot,Slurp,DuckDuckBot,Baiduspider,YandexBot,AhrefsBot,SemrushBot,DotBot,Exabot,MJ12bot,Screaming Frog SEO Spider,Wget,curl'),
            'asset_cache_enabled' => (bool) self::get('CACHEBOOST_ASSET_CACHE_ENABLED', false),
            'asset_extensions' => self::get('CACHEBOOST_ASSET_EXTENSIONS', 'js,css,png,jpg,jpeg,gif,webp,svg'),
            'asset_duration' => (int) self::get('CACHEBOOST_ASSET_DURATION', 86400),
            'cache_homepage' => (bool) self::get('CACHEBOOST_CACHE_HOMEPAGE', true),
            'cache_category' => (bool) self::get('CACHEBOOST_CACHE_CATEGORY', true),
            'cache_product' => (bool) self::get('CACHEBOOST_CACHE_PRODUCT', true),
            'cache_cms' => (bool) self::get('CACHEBOOST_CACHE_CMS', true),
            'CACHEBOOST_AUTO_WARMUP' => (bool) self::get('CACHEBOOST_AUTO_WARMUP', true),
            'purge_age' => (int) self::get('CACHEBOOST_PURGE_AGE', 0),
            'purge_size' => (int) self::get('CACHEBOOST_PURGE_SIZE', 0),
        ];
    }
}
