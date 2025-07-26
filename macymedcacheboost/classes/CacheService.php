<?php

namespace MacymedCacheBoost\Services;

use Context;
use MacymedCacheBoost\Services\ConfigurationService;
use Tools;
use Redis;
use Memcached;
use SimpleXMLElement;
use MacymedCacheBoost\CacheManager;
use Product;
use Category;
use CMS;
use PrestaShopLogger;


class CacheService
{
    public static function testCacheConnection($engine, $ip, $port)
    {
        if ($engine === 'redis') {
            if (class_exists('Redis')) {
                try {
                    $redis = new \Redis();
                    if (@$redis->connect($ip, $port, 1)) {
                        return ['success' => true, 'message' => Context::getContext()->getTranslator()->trans('Redis connection successful!', [], 'Modules.Macymedcacheboost.Admin')];
                    } else {
                        return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('Redis connection failed. Check IP and Port.', [], 'Modules.Macymedcacheboost.Admin')];
                    }
                } catch (\Exception $e) {
                    PrestaShopLogger::addLog('MacymedCacheBoost Redis connection error: ' . $e->getMessage(), 3);
                    return ['success' => false, 'message' => sprintf(Context::getContext()->getTranslator()->trans('Redis connection error: %s', [], 'Modules.Macymedcacheboost.Admin'), $e->getMessage())];
                }
            }
        } elseif ($engine === 'memcached') {
            if (class_exists('Memcached')) {
                try {
                    $memcached = new \Memcached();
                    if ($memcached->addServer($ip, $port)) {
                        // Test connection by setting and getting a dummy key
                        $test_key = 'macymedcacheboost_test_connection';
                        $memcached->set($test_key, 'test', 1);
                        if ($memcached->get($test_key) === 'test') {
                            return ['success' => true, 'message' => Context::getContext()->getTranslator()->trans('Memcached connection successful!', [], 'Modules.Macymedcacheboost.Admin')];
                        } else {
                            return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('Memcached connection failed. Could not set/get data.', [], 'Modules.Macymedcacheboost.Admin')];
                        }
                    } else {
                        return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('Memcached connection failed. Check IP and Port.', [], 'Modules.Macymedcacheboost.Admin')];
                    }
                } catch (\Exception $e) {
                    PrestaShopLogger::addLog('MacymedCacheBoost Memcached connection error: ' . $e->getMessage(), 3);
                    return ['success' => false, 'message' => sprintf(Context::getContext()->getTranslator()->trans('Memcached connection error: %s', [], 'Modules.Macymedcacheboost.Admin'), $e->getMessage())];
                }
            }
        }
        return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('Unknown cache engine.', [], 'Modules.Macymedcacheboost.Admin')];
    }

    public static function warmUpCache($link)
    {
        @set_time_limit(3600);

        $sitemap_url = $link->getPageLink('sitemap');
        $xml_content = Tools::file_get_contents($sitemap_url);

        if (!$xml_content) {
            return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('Could not fetch sitemap.xml. Please ensure it is enabled in PrestaShop.', [], 'Modules.Macymedcacheboost.Admin')];
        }

        $urls = [];
        $xml = new \SimpleXMLElement($xml_content);
        foreach ($xml->url as $url_node) {
            $urls[] = (string) $url_node->loc;
        }

        $warmed_count = 0;
        $errors = [];
        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);

            if ($response === false) {
                $errors[] = sprintf(Context::getContext()->getTranslator()->trans('cURL Error for %s: %s', [], 'Modules.Macymedcacheboost.Admin'), $url, curl_error($ch));
                curl_close($ch);
                continue;
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 200) {
                $warmed_count++;
            }
        }

        $message = sprintf(Context::getContext()->getTranslator()->trans('%d URLs processed.', [], 'Modules.Macymedcacheboost.Admin'), $warmed_count);
        if (!empty($errors)) {
            $message .= ' ' . sprintf(Context::getContext()->getTranslator()->trans('%d errors occurred. First error: %s', [], 'Modules.Macymedcacheboost.Admin'), count($errors), $errors[0]);
        }

        return ['success' => true, 'message' => $message];
    }

    public static function invalidateAll()
    {
        CacheManager::invalidateAll();
        return ['success' => true, 'message' => Context::getContext()->getTranslator()->trans('Full cache invalidated.', [], 'Modules.Macymedcacheboost.Admin')];
    }

    public static function invalidateProductCache($id_product)
    {
        if (!$id_product) {
            return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('Invalid product ID.', [], 'Modules.Macymedcacheboost.Admin')];
        }
        $product = new Product($id_product);
        if (is_object($product) && isset($product->id)) {
            $link = Context::getContext()->link;
            $product_url = $link->getProductLink($product);
            CacheManager::invalidateUrl($product_url);
            if (ConfigurationService::get('CACHEBOOST_AUTO_WARMUP')) {
                WarmingQueueService::addToQueue($product_url);
            }

            if ($product instanceof Product) {
                foreach ($product->getCategories() as $id_category) {
                    $category_url = $link->getCategoryLink($id_category);
                    CacheManager::invalidateUrl($category_url);
                    if (ConfigurationService::get('CACHEBOOST_AUTO_WARMUP')) {
                        WarmingQueueService::addToQueue($category_url);
                    }
                }
            }
        }

        return ['success' => true, 'message' => Context::getContext()->getTranslator()->trans('Product cache invalidated.', [], 'Modules.Macymedcacheboost.Admin')];
    }

    public static function invalidateCategoryCache($id_category)
    {
        if (!$id_category) {
            return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('Invalid category ID.', [], 'Modules.Macymedcacheboost.Admin')];
        }
        $category = new Category($id_category);
        if (is_object($category) && isset($category->id)) {
            $link = Context::getContext()->link;
            $category_url = $link->getCategoryLink($category);
            CacheManager::invalidateUrl($category_url);
            if (ConfigurationService::get('CACHEBOOST_AUTO_WARMUP')) {
                WarmingQueueService::addToQueue($category_url);
            }

            $index_url = $link->getPageLink('index');
            CacheManager::invalidateUrl($index_url);
            if (ConfigurationService::get('CACHEBOOST_AUTO_WARMUP')) {
                WarmingQueueService::addToQueue($index_url);
            }
        }

        return ['success' => true, 'message' => Context::getContext()->getTranslator()->trans('Category cache invalidated.', [], 'Modules.Macymedcacheboost.Admin')];
    }

    public static function invalidateCmsCache($id_cms)
    {
        if (!$id_cms) {
            return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('Invalid CMS ID.', [], 'Modules.Macymedcacheboost.Admin')];
        }
        $cms = new CMS($id_cms);
        if (is_object($cms) && isset($cms->id)) {
            $cms_url = Context::getContext()->link->getCMSLink($cms);
            CacheManager::invalidateUrl($cms_url);
            if (ConfigurationService::get('CACHEBOOST_AUTO_WARMUP')) {
                WarmingQueueService::addToQueue($cms_url);
            }
        }

        return ['success' => true, 'message' => Context::getContext()->getTranslator()->trans('CMS page cache invalidated.', [], 'Modules.Macymedcacheboost.Admin')];
    }

    public static function invalidateUrl($url)
    {
        if (empty($url)) {
            return ['success' => false, 'message' => Context::getContext()->getTranslator()->trans('URL cannot be empty.', [], 'Modules.Macymedcacheboost.Admin')];
        }
        CacheManager::invalidateUrl($url);
        if (ConfigurationService::get('CACHEBOOST_AUTO_WARMUP')) {
            WarmingQueueService::addToQueue($url);
        }

        return ['success' => true, 'message' => Context::getContext()->getTranslator()->trans('URL cache invalidated.', [], 'Modules.Macymedcacheboost.Admin')];
    }
}
