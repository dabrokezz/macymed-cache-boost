<?php
require_once __DIR__ . '/../src/Services/ConfigurationService.php';

use MacymedCacheBoost\Services\ConfigurationService;

// Simple stub for Configuration class used by ConfigurationService
class Configuration
{
    private static $data = [];

    public static function get($key)
    {
        return self::$data[$key] ?? false;
    }

    public static function updateValue($key, $value)
    {
        self::$data[$key] = $value;
    }

    public static function deleteByName($key)
    {
        unset(self::$data[$key]);
    }
}

Configuration::updateValue('TEST_KEY', 'stored value');

// Should output 'stored value'
var_dump(ConfigurationService::get('TEST_KEY', 'default'));

Configuration::updateValue('TEST_KEY', false);
// Should output 'default' because stored value is false
var_dump(ConfigurationService::get('TEST_KEY', 'default'));

Configuration::deleteByName('TEST_KEY');
// Should output 'default' because value is null
var_dump(ConfigurationService::get('TEST_KEY', 'default'));
