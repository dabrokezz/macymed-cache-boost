<?php
namespace MacymedCacheBoost;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Context;
use Tools;
use Redis;
use Memcached;
use Product;
use Dispatcher;
use MacymedCacheBoost\Services\ConfigurationService;
use PrestaShopLogger;
use Customer;
use Employee;

class CacheManager
{
    private $configurationService;
    private $core_excluded_controllers = [
        'cart',
        'order',
        'authentication',
        'my-account',
        'password',
        'history',
        'order-slip',
        'identity',
        'addresses',
        'address',
        'discount',
        'search',
        'apajax',
    ];

    private $token_patterns = [
        '/token=/',
        '/secure_key=/',
        '/id_token=/',
        '/access_token=/',
        '/code=/',
        '/state=/',
        '/csrf_token=/',
        '/gclid=/',
        '/fbclid=/',
        '/msclkid=/',
        '/utm_source=/',
        '/utm_medium=/',
        '/utm_campaign=/',
        '/utm_term=/',
        '/utm_content=',
    ];

    private $bot_user_agents = [
        'Lighthouse',
        'Googlebot',
        'Bingbot',
        'Slurp',
        'DuckDuckBot',
        'Baiduspider',
        'YandexBot',
        'AhrefsBot',
        'SemrushBot',
        'DotBot',
        'Exabot',
        'MJ12bot',
        'Screaming Frog SEO Spider',
        'Wget',
        'curl',
    ];

    private $cache_engine;
    private $redis_client;
    private $memcached_client;
    private $isAjaxJsonRequest = false;
    private $is_bot = false;

    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    public function getEngine()
    {
        if (null === $this->cache_engine) {
            $this->cache_engine = $this->configurationService->get('ENGINE') ?? 'filesystem';
        }
        return $this->cache_engine;
    }

    public function getRedisClient()
    {
        if ($this->redis_client !== null)
            return $this->redis_client;
        if ($this->getEngine() !== 'redis' || !class_exists('Redis'))
            return false;

        try {
            $redis = new Redis();
            $ip = $this->configurationService->get('REDIS_IP') ?? '127.0.0.1';
            $port = (int) ($this->configurationService->get('REDIS_PORT') ?? 6379);
            if ($redis->connect($ip, $port, 1)) {
                return $this->redis_client = $redis;
            }
        } catch (\Exception $e) {
            PrestaShopLogger::addLog('MacymedCacheBoost Redis connection error: ' . $e->getMessage(), 3);
        }
        return false;
    }

    public function getMemcachedClient()
    {
        if ($this->memcached_client !== null)
            return $this->memcached_client;
        if ($this->getEngine() !== 'memcached' || !class_exists('Memcached'))
            return false;

        try {
            $memcached = new Memcached();
            $ip = $this->configurationService->get('MEMCACHED_IP') ?? '127.0.0.1';
            $port = (int) ($this->configurationService->get('MEMCACHED_PORT') ?? 11211);
            if ($memcached->addServer($ip, $port)) {
                return $this->memcached_client = $memcached;
            }
        } catch (\Exception $e) {
            PrestaShopLogger::addLog('MacymedCacheBoost Memcached connection error: ' . $e->getMessage(), 3);
        }
        return false;
    }

    private function getCacheKey($uri = null)
    {
        $key = 'macymedcacheboost:' . md5($uri ?? $_SERVER['REQUEST_URI']);
        if ($this->isAjaxJsonRequest) {
            $key .= '_json';
        }
        return $key;
    }

    private function shouldBypassCache()
    {
        // Always exclude admin pages
        if (defined('_PS_ADMIN_DIR_') || strpos($_SERVER['REQUEST_URI'], '/admin') !== false) {
            return true;
        }

        // Always exclude POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }

        // Always exclude URLs with common token patterns
        foreach ($this->token_patterns as $pattern) {
            if (strpos($_SERVER['REQUEST_URI'], $pattern) !== false) {
                return true;
            }
        }

        // Always exclude user-defined patterns
        $user_excluded = $this->configurationService->get('EXCLUDE');
        if ($user_excluded) {
            foreach (explode(',', $user_excluded) as $pattern) {
                if (preg_match('#' . trim($pattern) . '#', $_SERVER['REQUEST_URI'])) {
                    return true;
                }
            }
        }

        $is_ajax_request = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        $is_from_xhr = isset($_GET['from-xhr']);

        // Handle AJAX requests
        if ($is_ajax_request) {
            if (!$this->configurationService->get('CACHE_AJAX')) { // If AJAX caching is disabled
                return true;
            }
            if ($is_from_xhr && $this->configurationService->get('CACHE_AJAX')) {
                $this->isAjaxJsonRequest = true;
            }
        }

        // Check if the request is from a known bot
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            foreach ($this->bot_user_agents as $bot_ua) {
                if (stripos($user_agent, $bot_ua) !== false) {
                    $this->is_bot = true;
                    break;
                }
            }
        }

        // Check for asset caching
        if ($this->configurationService->get('ASSET_CACHE_ENABLED')) {
            $request_uri = $_SERVER['REQUEST_URI'];
            $path_info = pathinfo($request_uri);
            $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

            $allowed_extensions_str = $this->configurationService->get('ASSET_EXTENSIONS');
            $allowed_extensions = array_map('trim', explode(',', $allowed_extensions_str));

            if (in_array($extension, $allowed_extensions)) {
                $this->serveAssetCache();
                return true; // Asset served or will be captured
            }
        }

        // Main cache bypass logic
        if (!$this->configurationService->get('ENABLED')) {
            return true;
        }

        $context = Context::getContext();

        // Apply these bypasses only for non-bot requests
        if (!$this->is_bot) {
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ && !$this->configurationService->get('ENABLE_DEV_MODE')) {
                return true;
            }
            if ($context->customer->isLogged() || ($context->employee && $context->employee->isLoggedBack())) {
                return true;
            }
            if (isset($_COOKIE['nocache']) || isset($_GET['nocache'])) {
                return true;
            }
            if (isset($_COOKIE['PrestaShop-' . $context->shop->id])) {
                return true;
            }
        } else { // If it's a bot, force guest context
            $context->customer = new Customer(); // Create a new empty customer object
            $context->employee = new Employee(); // Ensure no employee is logged in
            $context->employee->id = null;
        }

        return false;
    }

    public function checkAndServeCache()
    {
        try {
            if ($this->shouldBypassCache()) {
                return;
            }

            $context = Context::getContext();
            $controller = strtolower(Dispatcher::getInstance()->getController());

            // Page type specific caching
            $cache_page = false;
            switch ($controller) {
                case 'index': // Homepage
                    $cache_page = (bool) $this->configurationService->get('CACHE_HOMEPAGE');
                    break;
                case 'category':
                    $cache_page = (bool) $this->configurationService->get('CACHE_CATEGORY');
                    break;
                case 'product':
                    $cache_page = (bool) $this->configurationService->get('CACHE_PRODUCT');
                    break;
                case 'cms':
                    $cache_page = (bool) $this->configurationService->get('CACHE_CMS');
                    break;
                default:
                    // If controller is not explicitly enabled for caching, bypass
                    return;
            }

            if (!$cache_page) {
                return;
            }

            $key = $this->getCacheKey();
            $cache_content = $this->get($key);

            if ($cache_content) {
                header('X-CacheBoost: HIT - ' . $this->getEngine());
                $this->incrementHit();

                // GZIP check (actual binary header, not literal string)
                $is_gzipped_content = (substr($cache_content, 0, 2) === "\x1f\x8b");
                $client_accepts_gzip = (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false);

                if ($this->configurationService->get('COMPRESSION_ENABLED') && function_exists('gzdecode') && $is_gzipped_content) {
                    // Content was stored gzipped, now decide how to serve it
                    if ($client_accepts_gzip) {
                        header('Content-Encoding: gzip');
                        if ($this->isAjaxJsonRequest) {
                            header('Content-Type: application/json');
                        }
                        echo $cache_content; // Serve as is, browser will decompress
                    } else {
                        if ($this->isAjaxJsonRequest) {
                            header('Content-Type: application/json');
                        }
                        echo gzdecode($cache_content); // Decompress for client
                    }
                } else {
                    // Content was stored uncompressed, or compression is disabled
                    if ($this->isAjaxJsonRequest) {
                        header('Content-Type: application/json');
                    }
                    echo $cache_content;
                }
                exit;
            }

            $this->incrementMiss();
            ob_start([$this, 'storeCache']);

            // Periodically purge old/large cache files
            $this->purgeOldAndLargeCache();
        } catch (\Exception $e) {
            PrestaShopLogger::addLog('MacymedCacheBoost error in checkAndServeCache: ' . $e->getMessage(), 3);
        }
    }

    public function storeCache($buffer)
    {
        if (http_response_code() >= 400 || empty($buffer))
            return $buffer;

        $key = $this->getCacheKey();
        $comment = '<!-- Cached by Macymed CacheBoost -->';

        $buffer_to_store = $buffer;
        if (!$this->isAjaxJsonRequest) {
            $buffer_to_store .= $comment;
        }

        // Only compress if compression is enabled AND zlib is loaded
        if ($this->configurationService->get('COMPRESSION_ENABLED') && extension_loaded('zlib')) {
            $buffer_to_store = gzencode($buffer_to_store, 9);
        }

        $this->set($key, $buffer_to_store);
        return $buffer;
    }

    public function get($key)
    {
        $engine = $this->getEngine();
        $duration = $this->getDuration($key);

        if ($engine === 'redis') {
            $redis = $this->getRedisClient();
            return $redis ? $redis->get($key) : false;
        }

        if ($engine === 'memcached') {
            $memcached = $this->getMemcachedClient();
            return $memcached ? $memcached->get($key) : false;
        }

        $path = _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html/' . str_replace('macymedcacheboost:', '', $key) . '.html';
        return (file_exists($path) && (time() - filemtime($path)) < $duration) ? file_get_contents($path) : false;
    }

    public function set($key, $value)
    {
        $engine = $this->getEngine();
        $duration = $this->getDuration($key);

        if ($engine === 'redis') {
            $redis = $this->getRedisClient();
            if ($redis)
                $redis->setex($key, $duration, $value);
            return;
        }

        if ($engine === 'memcached') {
            $memcached = $this->getMemcachedClient();
            if ($memcached)
                $memcached->set($key, $value, $duration);
            return;
        }

        $path = _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html/' . str_replace('macymedcacheboost:', '', $key) . '.html';
        $dir = dirname($path);
        if (!is_dir($dir))
            mkdir($dir, 0755, true);
        if (is_writable($dir))
            file_put_contents($path, $value);
    }

    private function getDuration($key)
    {
        if ($this->is_bot) {
            return (int) ($this->configurationService->get('BOT_CACHE_DURATION') ?? 86400);
        }
        if (strpos($key, '_asset') !== false) {
            return (int) ($this->configurationService->get('ASSET_DURATION') ?? 86400);
        }
        return (int) ($this->configurationService->get('DURATION') ?? 3600);
    }

    private function getFilesystemCachePath($key)
    {
        return _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html/' . str_replace('macymedcacheboost:', '', $key) . '.html';
    }

    private function purgeOldAndLargeCache()
    {
        $purgeAge = (int) $this->configurationService->get('PURGE_AGE', 0);
        $purgeSize = (int) $this->configurationService->get('PURGE_SIZE', 0);

        if ($purgeAge === 0 && $purgeSize === 0) {
            return; // Purging is disabled
        }

        $cache_dir = _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html';
        if (!is_dir($cache_dir)) {
            return;
        }

        $files = glob($cache_dir . '/*.html');
        if ($files === false) {
            return;
        }

        $current_size = 0;
        $files_to_delete = [];

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $file_size = filesize($file);
            $file_mtime = filemtime($file);

            // Check for age
            if ($purgeAge > 0 && (time() - $file_mtime) > $purgeAge) {
                $files_to_delete[] = $file;
            } else {
                $current_size += $file_size;
            }
        }

        // Check for size (delete oldest first if over limit)
        if ($purgeSize > 0 && $current_size > ($purgeSize * 1024 * 1024)) { // Convert MB to bytes
            // Sort files by modification time (oldest first)
            usort($files, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            foreach ($files as $file) {
                if ($current_size <= ($purgeSize * 1024 * 1024)) {
                    break; // Size limit reached
                }
                if (!in_array($file, $files_to_delete)) {
                    $files_to_delete[] = $file;
                    $current_size -= filesize($file);
                }
            }
        }

        foreach ($files_to_delete as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function invalidateAll()
    {
        $engine = $this->getEngine();

        if ($engine === 'redis') {
            $redis = $this->getRedisClient();
            if ($redis) {
                $redis->flushDB();
            }
        } elseif ($engine === 'memcached') {
            $memcached = $this->getMemcachedClient();
            if ($memcached) {
                $memcached->flush();
            }
        } else {
            $cache_dir = _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html';
            if (is_dir($cache_dir)) {
                $this->rrmdir($cache_dir);
            }
        }
        $this->setLastFlushTime();
    }

    public function invalidateUrl($url)
    {
        $uri = parse_url($url, PHP_URL_PATH);
        if (parse_url($url, PHP_URL_QUERY)) {
            $uri .= '?' . parse_url($url, PHP_URL_QUERY);
        }
        $key = $this->getCacheKey($uri);
        $engine = $this->getEngine();

        if ($engine === 'redis') {
            $redis = $this->getRedisClient();
            if ($redis)
                $redis->del($key);
            return;
        }

        if ($engine === 'memcached') {
            $memcached = $this->getMemcachedClient();
            if ($memcached)
                $memcached->delete($key);
            return;
        }

        $path = _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html/' . str_replace('macymedcacheboost:', '', $key) . '.html';
        if (file_exists($path))
            unlink($path);
    }

    public function incrementHit()
    {
        $this->configurationService->update('HITS', (int) $this->configurationService->get('HITS') + 1);
    }

    public function incrementMiss()
    {
        $this->configurationService->update('MISSES', (int) $this->configurationService->get('MISSES') + 1);
    }

    public function setLastFlushTime()
    {
        $this->configurationService->update('LAST_FLUSH', date('Y-m-d H:i:s'));
    }

    public function uninstallCache()
    {
        $this->invalidateAll();
        $cache_dir = _PS_MODULE_DIR_ . 'macymedcacheboost/cache';
        if (is_dir($cache_dir))
            $this->rrmdir($cache_dir);
    }

    public function rrmdir($dir)
    {
        if (!is_dir($dir))
            return;
        foreach (scandir($dir) as $object) {
            if ($object === '.' || $object === '..')
                continue;
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            is_dir($path) ? $this->rrmdir($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function isAssetCacheEnabledAndAllowed(&$extension)
    {
        if (!$this->configurationService->get('ASSET_CACHE_ENABLED')) {
            return false;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $path_info = pathinfo($request_uri);
        $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

        $allowed_extensions_str = $this->configurationService->get('ASSET_EXTENSIONS');
        $allowed_extensions = array_map('trim', explode(',', $allowed_extensions_str));

        return in_array($extension, $allowed_extensions);
    }

    private function serveCachedAsset($cached_asset, $extension)
    {
        header('X-CacheBoost-Asset: HIT');
        header('Content-Type: ' . $this->getMimeType($extension));
        header('Cache-Control: public, max-age=' . (int) $this->configurationService->get('ASSET_DURATION'));
        echo $cached_asset;
        exit;
    }

    public function serveAssetCache()
    {
        $extension = '';
        if (!$this->isAssetCacheEnabledAndAllowed($extension)) {
            return false;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $asset_key = 'macymedcacheboost_asset:' . md5($request_uri);
        $cached_asset = $this->get($asset_key);

        if ($cached_asset) {
            $this->serveCachedAsset($cached_asset, $extension);
        }

        // If not in cache, capture output and store it
        ob_start(function ($buffer) use ($asset_key, $extension) {
            if (http_response_code() >= 400 || empty($buffer)) {
                return $buffer;
            }
            $this->set($asset_key, $buffer);
            header('Content-Type: ' . $this->getMimeType($extension));
            header('Cache-Control: public, max-age=' . (int) $this->configurationService->get('ASSET_DURATION'));
            return $buffer;
        });

        return true;
    }

    public function getMimeType($extension)
    {
        switch ($extension) {
            case 'css': return 'text/css';
            case 'js': return 'application/javascript';
            case 'png': return 'image/png';
            case 'jpg':
            case 'jpeg': return 'image/jpeg';
            case 'gif': return 'image/gif';
            case 'webp': return 'image/webp';
            case 'svg': return 'image/svg+xml';
            default: return 'application/octet-stream';
        }
    }
}
