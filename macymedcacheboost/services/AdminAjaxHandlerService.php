<?php

namespace MacymedCacheBoost\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Tools;
use MacymedCacheBoost\Services\CacheService;
use MacymedCacheBoost\Services\WarmingQueueService;

class AdminAjaxHandlerService
{
    public static function handleAjaxRequest($action, $context)
    {
        switch ($action) {
            case 'WarmUpCache':
                try {
                    $result = CacheService::warmUpCache($context->link);
                } catch (\Exception $e) {
                    $result = ['success' => false, 'message' => 'Error warming cache: ' . $e->getMessage()];
                }
                break;
            case 'TestCacheConnection':
                try {
                    $engine = Tools::getValue('engine');
                    $ip = Tools::getValue('ip');
                    $port = Tools::getValue('port');
                    $result = CacheService::testCacheConnection($engine, $ip, $port);
                } catch (\Exception $e) {
                    $result = ['success' => false, 'message' => 'Error testing connection: ' . $e->getMessage()];
                }
                break;
            case 'InvalidateProductCache':
                try {
                    $id_product = (int) Tools::getValue('id_product');
                    $result = CacheService::invalidateProductCache($id_product);
                } catch (\Exception $e) {
                    $result = ['success' => false, 'message' => 'Error invalidating product cache: ' . $e->getMessage()];
                }
                break;
            case 'InvalidateCategoryCache':
                try {
                    $id_category = (int) Tools::getValue('id_category');
                    $result = CacheService::invalidateCategoryCache($id_category);
                } catch (\Exception $e) {
                    $result = ['success' => false, 'message' => 'Error invalidating category cache: ' . $e->getMessage()];
                }
                break;
            case 'InvalidateCmsCache':
                try {
                    $id_cms = (int) Tools::getValue('id_cms');
                    $result = CacheService::invalidateCmsCache($id_cms);
                } catch (\Exception $e) {
                    $result = ['success' => false, 'message' => 'Error invalidating CMS cache: ' . $e->getMessage()];
                }
                break;
            case 'InvalidateUrl':
                try {
                    $url = Tools::getValue('url');
                    $result = CacheService::invalidateUrl($url);
                } catch (\Exception $e) {
                    $result = ['success' => false, 'message' => 'Error invalidating URL cache: ' . $e->getMessage()];
                }
                break;
            case 'ProcessWarmingQueue':
                try {
                    $result = WarmingQueueService::processQueue();
                } catch (\Exception $e) {
                    $result = ['success' => false, 'message' => 'Error processing warming queue: ' . $e->getMessage()];
                }
                break;
            case 'FlushAllCache':
                try {
                    $result = CacheService::invalidateAll();
                } catch (\Exception $e) {
                    $result = ['success' => false, 'message' => 'Error flushing all cache: ' . $e->getMessage()];
                }
                break;
            default:
                $result = ['success' => false, 'message' => 'Unknown AJAX action'];
        }
        die(json_encode($result));
    }
}
