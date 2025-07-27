<?php

namespace MacymedCacheBoost\Services;

use Configuration;

class ConfigurationService
{
    const CONFIG_PREFIX = 'CACHEBOOST_';

    public function get($key, $default = null)
    {
        return Configuration::get(self::CONFIG_PREFIX . $key, $default);
    }

    public function update($key, $value)
    {
        return Configuration::updateValue(self::CONFIG_PREFIX . $key, $value);
    }

    public function delete($key)
    {
        return Configuration::deleteByName(self::CONFIG_PREFIX . $key);
    }

    public function getAllConfigValues()
    {
        $keys = $this->getConfigKeys();
        $values = [];
        foreach ($keys as $key) {
            $values[self::CONFIG_PREFIX . $key] = $this->get($key);
        }
        return $values;
    }

    public function updateBulk(array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array(str_replace(self::CONFIG_PREFIX, '', $key), $this->getConfigKeys())) {
                Configuration::updateValue($key, $value);
            }
        }
    }

    private function getConfigKeys()
    {
        return [
            'ENABLED',
            'ENABLE_DEV_MODE',
            'DURATION',
            'EXCLUDE',
            'ENGINE',
            'REDIS_IP',
            'REDIS_PORT',
            'MEMCACHED_IP',
            'MEMCACHED_PORT',
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
            'PURGE_AGE',
            'PURGE_SIZE',
            'AUTO_WARMUP',
        ];
    }
}
