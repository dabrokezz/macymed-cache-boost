<?php

namespace MacymedCacheBoost\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Tools;
use PrestaShopLogger;

class WarmingQueueService
{
    private static function getQueuePath()
    {
        return _PS_MODULE_DIR_ . 'macymedcacheboost/cache/warming_queue.txt';
    }

    public static function addToQueue($url)
    {
        if (empty($url)) {
            return false;
        }

        $path = self::getQueuePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                PrestaShopLogger::addLog('MacymedCacheBoost: Failed to create warming queue directory: ' . $dir, 3);
                return false;
            }
        }

        // Avoid adding duplicate URLs
        $current_queue = self::getQueue();
        if (!in_array($url, $current_queue)) {
            if (file_put_contents($path, $url . PHP_EOL, FILE_APPEND) === false) {
                PrestaShopLogger::addLog('MacymedCacheBoost: Failed to write to warming queue file: ' . $path, 3);
                return false;
            }
        }

        return true;
    }

    public static function getQueue()
    {
        $path = self::getQueuePath();
        if (!file_exists($path)) {
            return [];
        }
        $urls = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($urls === false) {
            PrestaShopLogger::addLog('MacymedCacheBoost: Failed to read warming queue file: ' . $path, 3);
            return [];
        }
        return $urls ? array_unique($urls) : [];
    }

    public static function getQueueCount()
    {
        return count(self::getQueue());
    }

    public static function processQueue()
    {
        $queue = self::getQueue();
        if (empty($queue)) {
            return ['success' => true, 'message' => 'Queue is empty.', 'processed_count' => 0];
        }

        $warmed_count = 0;
        $errors = [];
        foreach ($queue as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            // Set a specific user agent to ensure it's treated as a cacheable visit
            curl_setopt($ch, CURLOPT_USERAGENT, 'MacymedCacheBoost-Warmer/1.0');

            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error_message = "cURL Error for " . $url . ": " . curl_error($ch);
                PrestaShopLogger::addLog('MacymedCacheBoost: ' . $error_message, 3);
                $errors[] = $error_message;
            } elseif ($http_code == 200) {
                $warmed_count++;
            }
            curl_close($ch);
        }

        // Clear the queue after processing
        self::clearQueue();

        $message = sprintf('%d URLs from the queue have been warmed up.', $warmed_count);
        if (!empty($errors)) {
            $message .= sprintf(' %d errors occurred. First error: %s', count($errors), $errors[0]);
        }

        return ['success' => true, 'message' => $message, 'processed_count' => $warmed_count];
    }

    public static function clearQueue()
    {
        $path = self::getQueuePath();
        if (file_exists($path)) {
            if (file_put_contents($path, '') === false) {
                PrestaShopLogger::addLog('MacymedCacheBoost: Failed to clear warming queue file: ' . $path, 3);
                return false;
            }
        }
        return true;
    }
}
