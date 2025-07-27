<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Services\ConfigurationService;
use PrestaShopLogger;
use Redis;
use Context;
use Symfony\Component\HttpFoundation\Response;

class AdminMacymedCacheBoostDashboardController extends FrameworkBundleAdminController
{
    public function indexAction(): Response
    {
        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostdashboard.html.twig', [
            'cache_stats' => $this->getCacheStatistics(),
        ]);
    }

    private function getCacheStatistics()
    {
        $engine = ConfigurationService::get('CACHEBOOST_ENGINE', 'filesystem');
        $stats = [
            'count' => 'N/A',
            'size' => 'N/A',
            'engine' => $engine,
            'hits' => (int) ConfigurationService::get('CACHEBOOST_HITS', 0),
            'misses' => (int) ConfigurationService::get('CACHEBOOST_MISSES', 0),
            'last_flush' => ConfigurationService::get('CACHEBOOST_LAST_FLUSH', $this->trans('Never', [], 'Modules.Macymedcacheboost.Admin')),
        ];

        if ($engine === 'redis') {
            if (class_exists('Redis')) {
                try {
                    $redis = new \Redis();
                    if (@$redis->connect(ConfigurationService::get('CACHEBOOST_REDIS_IP'), ConfigurationService::get('CACHEBOOST_REDIS_PORT'), 1)) {
                        $iterator = null;
                        $keys = [];
                        while ($scanned_keys = $redis->scan($iterator, 'cacheboost:*')) {
                            $keys = array_merge($keys, $scanned_keys);
                        }
                        $stats['count'] = count($keys);
                        $stats['size'] = $this->trans('Not applicable for Redis', [], 'Modules.Macymedcacheboost.Admin');
                        $redis->close();
                    } else {
                        $stats['count'] = $this->trans('Connection failed', [], 'Modules.Macymedcacheboost.Admin');
                    }
                } catch (\Exception $e) {
                    PrestaShopLogger::addLog('MacymedCacheBoost Redis statistics error: ' . $e->getMessage(), 3);
                    $stats['count'] = $this->trans('Connection error', [], 'Modules.Macymedcacheboost.Admin');
                }
            } else {
                $stats['count'] = $this->trans('Redis extension not installed', [], 'Modules.Macymedcacheboost.Admin');
            }
        } else { // Filesystem
            $files = glob(_PS_MODULE_DIR_ . 'macymedcacheboost/cache/html/*.html');
            if ($files === false)
                $files = [];
            $total_size = 0;
            foreach ($files as $file) {
                if (is_file($file))
                    $total_size += filesize($file);
            }
            $stats['count'] = count($files);
            $stats['size'] = $this->formatBytes($total_size);
        }

        return $stats;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}