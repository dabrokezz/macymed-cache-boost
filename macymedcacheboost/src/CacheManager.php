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
    private static $core_excluded_controllers = [
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

    private static $token_patterns = [
        '/token=',
        '/secure_key=',
        '/id_token=',
        '/access_token=',
        '/code=',
        '/state=',
        '/csrf_token=',
        '/gclid=',
        '/fbclid=',
        '/msclkid=',
        '/utm_source=',
        '/utm_medium=',
        '/utm_campaign=',
        '/utm_term=',
        '/utm_content=',
    ];

    private static $bot_user_agents = [
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

    

    private static $cache_engine;
    private static $redis_client;
    private static $memcached_client;
    private static $isAjaxJsonRequest = false;

    public static function getEngine()
    {
        if (null === self::$cache_engine) {
            self::$cache_engine = ConfigurationService::get('CACHEBOOST_ENGINE') ?? 'filesystem';
        }
        return self::$cache_engine;
    }

    public static function getRedisClient()
    {
        if (self::$redis_client !== null)
            return self::$redis_client;
        if (self::getEngine() !== 'redis' || !class_exists('Redis'))
            return false;

        try {
            $redis = new Redis();
            $ip = ConfigurationService::get('CACHEBOOST_REDIS_IP') ?? '127.0.0.1';
            $port = (int) (ConfigurationService::get('CACHEBOOST_REDIS_PORT') ?? 6379);
            if ($redis->connect($ip, $port, 1)) {
                return self::$redis_client = $redis;
            }
        } catch (\Exception $e) {
            PrestaShopLogger::addLog('MacymedCacheBoost Redis connection error: ' . $e->getMessage(), 3);
        }
        return false;
    }

    public static function getMemcachedClient()
    {
        if (self::$memcached_client !== null)
            return self::$memcached_client;
        if (self::getEngine() !== 'memcached' || !class_exists('Memcached'))
            return false;

        try {
            $memcached = new Memcached();
            $ip = ConfigurationService::get('CACHEBOOST_MEMCACHED_IP') ?? '127.0.0.1';
            $port = (int) (ConfigurationService::get('CACHEBOOST_MEMCACHED_PORT') ?? 11211);
            if ($memcached->addServer($ip, $port)) {
                return self::$memcached_client = $memcached;
            }
        } catch (\Exception $e) {
            PrestaShopLogger::addLog('MacymedCacheBoost Memcached connection error: ' . $e->getMessage(), 3);
        }
        return false;
    }

    private static function getCacheKey($uri = null)
    {
        $key = 'macymedcacheboost:' . md5($uri ?? $_SERVER['REQUEST_URI']);
        if (self::$isAjaxJsonRequest) {
            $key .= '_json';
        }
        return $key;
    }

    private static function shouldBypassCache()
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
        foreach (self::$token_patterns as $pattern) {
            if (strpos($_SERVER['REQUEST_URI'], $pattern) !== false) {
                return true;
            }
        }

        // Always exclude user-defined patterns
        $user_excluded = ConfigurationService::get('CACHEBOOST_EXCLUDE');
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
            if (!ConfigurationService::get('CACHEBOOST_CACHE_AJAX')) { // If AJAX caching is disabled
                return true;
            }
            if ($is_from_xhr && ConfigurationService::get('CACHEBOOST_CACHE_AJAX')) {
                self::$isAjaxJsonRequest = true;
            }
        }

        // Check if the request is from a known bot
        $is_bot = false;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            foreach (self::$bot_user_agents as $bot_ua) {
                if (stripos($user_agent, $bot_ua) !== false) {
                    $is_bot = true;
                    break;
                }
            }
        }

        // Check for asset caching
        if (ConfigurationService::get('CACHEBOOST_ASSET_CACHE_ENABLED')) {
            $request_uri = $_SERVER['REQUEST_URI'];
            $path_info = pathinfo($request_uri);
            $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

            $allowed_extensions_str = ConfigurationService::get('CACHEBOOST_ASSET_EXTENSIONS');
            $allowed_extensions = array_map('trim', explode(',', $allowed_extensions_str));

            if (in_array($extension, $allowed_extensions)) {
                self::serveAssetCache();
                return true; // Asset served or will be captured
            }
        }

        // Main cache bypass logic
        if (!ConfigurationService::get('CACHEBOOST_ENABLED')) {
            return true;
        }

        $context = Context::getContext();

        // Apply these bypasses only for non-bot requests
        if (!$is_bot) {
            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ && !ConfigurationService::get('CACHEBOOST_ENABLE_DEV_MODE')) {
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

    public static function checkAndServeCache()
    {
        try {
            if (self::shouldBypassCache()) {
                return;
            }

            $context = Context::getContext();
            $controller = strtolower(Dispatcher::getInstance()->getController());

            // Page type specific caching
            $cache_page = false;
            switch ($controller) {
                case 'index': // Homepage
                    $cache_page = (bool) ConfigurationService::get('CACHEBOOST_CACHE_HOMEPAGE');
                    break;
                case 'category':
                    $cache_page = (bool) ConfigurationService::get('CACHEBOOST_CACHE_CATEGORY');
                    break;
                case 'product':
                    $cache_page = (bool) ConfigurationService::get('CACHEBOOST_CACHE_PRODUCT');
                    break;
                case 'cms':
                    $cache_page = (bool) ConfigurationService::get('CACHEBOOST_CACHE_CMS');
                    break;
                default:
                    // If controller is not explicitly enabled for caching, bypass
                    return;
            }

            if (!$cache_page) {
                return;
            }

            $key = self::getCacheKey();
            $cache_content = self::get($key);

            if ($cache_content) {
                header('X-CacheBoost: HIT - ' . self::getEngine());
                self::incrementHit();

                // GZIP check (actual binary header, not literal string)
                $is_gzipped_content = (substr($cache_content, 0, 2) === "\x1f\x8b");
                $client_accepts_gzip = (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false);

                if (ConfigurationService::get('CACHEBOOST_COMPRESSION_ENABLED') && function_exists('gzdecode') && $is_gzipped_content) {
                    // Content was stored gzipped, now decide how to serve it
                    if ($client_accepts_gzip) {
                        header('Content-Encoding: gzip');
                        if (self::$isAjaxJsonRequest) {
                            header('Content-Type: application/json');
                        }
                        echo $cache_content; // Serve as is, browser will decompress
                    } else {
                        if (self::$isAjaxJsonRequest) {
                            header('Content-Type: application/json');
                        }
                        echo gzdecode($cache_content); // Decompress for client
                    }
                } else {
                    // Content was stored uncompressed, or compression is disabled
                    if (self::$isAjaxJsonRequest) {
                        header('Content-Type: application/json');
                    }
                    echo $cache_content;
                }
                exit;
            }

            self::incrementMiss();
            ob_start([self::class, 'storeCache']);

            // Periodically purge old/large cache files
            self::purgeOldAndLargeCache();
        } catch (\Exception $e) {
            PrestaShopLogger::addLog('MacymedCacheBoost error in checkAndServeCache: ' . $e->getMessage(), 3);
        }
    }

    public static function storeCache($buffer)
    {
        if (http_response_code() >= 400 || empty($buffer))
            return $buffer;

        $key = self::getCacheKey();
        $comment = '<!-- Cached by Macymed CacheBoost -->';

        $buffer_to_store = $buffer;
        if (!self::$isAjaxJsonRequest) {
            $buffer_to_store .= $comment;
        }

        // Only compress if compression is enabled AND zlib is loaded
        if (ConfigurationService::get('CACHEBOOST_COMPRESSION_ENABLED') && extension_loaded('zlib')) {
            $buffer_to_store = gzencode($buffer_to_store, 9);
        }

        self::set($key, $buffer_to_store);
        return $buffer;
    }

    public static function get($key)
    {
        $engine = self::getEngine();
        $duration = (int) (ConfigurationService::get('CACHEBOOST_DURATION') ?? 3600);

        if ($engine === 'redis') {
            $redis = self::getRedisClient();
            return $redis ? $redis->get($key) : false;
        }

        if ($engine === 'memcached') {
            $memcached = self::getMemcachedClient();
            return $memcached ? $memcached->get($key) : false;
        }

        $path = _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html/' . str_replace('macymedcacheboost:', '', $key) . '.html';
        return (file_exists($path) && (time() - filemtime($path)) < $duration) ? file_get_contents($path) : false;
    }

    public static function set($key, $value)
    {
        $engine = self::getEngine();
        $duration = (int) (ConfigurationService::get('CACHEBOOST_DURATION') ?? 3600);

        if ($engine === 'redis') {
            $redis = self::getRedisClient();
            if ($redis)
                $redis->setex($key, $duration, $value);
            return;
        }

        if ($engine === 'memcached') {
            $memcached = self::getMemcachedClient();
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

    

    private static function getFilesystemCachePath($key)
    {
        return _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html/' . str_replace('macymedcacheboost:', '', $key) . '.html';
    }

    private static function purgeOldAndLargeCache()
    {
        $purgeAge = (int) ConfigurationService::get('CACHEBOOST_PURGE_AGE', 0);
        $purgeSize = (int) ConfigurationService::get('CACHEBOOST_PURGE_SIZE', 0);

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

    public static function invalidateAll()
    {
        $engine = self::getEngine();

        if ($engine === 'redis') {
            $redis = self::getRedisClient();
            if ($redis) {
                $redis->flushDB();
            }
        } elseif ($engine === 'memcached') {
            $memcached = self::getMemcachedClient();
            if ($memcached) {
                $memcached->flush();
            }
        } else {
            $cache_dir = _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html';
            if (is_dir($cache_dir)) {
                self::rrmdir($cache_dir);
            }
        }
        self::setLastFlushTime();
    }

    public static function invalidateUrl($url)
    {
        $uri = parse_url($url, PHP_URL_PATH);
        if (parse_url($url, PHP_URL_QUERY)) {
            $uri .= '?' . parse_url($url, PHP_URL_QUERY);
        }
        $key = self::getCacheKey($uri);
        $engine = self::getEngine();

        if ($engine === 'redis') {
            $redis = self::getRedisClient();
            if ($redis)
                $redis->del($key);
            return;
        }

        if ($engine === 'memcached') {
            $memcached = self::getMemcachedClient();
            if ($memcached)
                $memcached->delete($key);
            return;
        }

        $path = _PS_MODULE_DIR_ . 'macymedcacheboost/cache/html/' . str_replace('macymedcacheboost:', '', $key) . '.html';
        if (file_exists($path))
            unlink($path);
    }

    public static function incrementHit()
    {
        ConfigurationService::update('CACHEBOOST_HITS', (int) ConfigurationService::get('CACHEBOOST_HITS') + 1);
    }

    public static function incrementMiss()
    {
        ConfigurationService::update('CACHEBOOST_MISSES', (int) ConfigurationService::get('CACHEBOOST_MISSES') + 1);
    }

    public static function setLastFlushTime()
    {
        ConfigurationService::update('CACHEBOOST_LAST_FLUSH', date('Y-m-d H:i:s'));
    }

    public static function uninstallCache()
    {
        self::invalidateAll();
        $cache_dir = _PS_MODULE_DIR_ . 'macymedcacheboost/cache';
        if (is_dir($cache_dir))
            self::rrmdir($cache_dir);
    }

    public static function rrmdir($dir)
    {
        if (!is_dir($dir))
            return;
        foreach (scandir($dir) as $object) {
            if ($object === '.' || $object === '..')
                continue;
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            is_dir($path) ? self::rrmdir($path) : unlink($path);
        }
        rmdir($dir);
    }

    private static function isAssetCacheEnabledAndAllowed(&$extension)
    {
        if (!ConfigurationService::get('CACHEBOOST_ASSET_CACHE_ENABLED')) {
            return false;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $path_info = pathinfo($request_uri);
        $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

        $allowed_extensions_str = ConfigurationService::get('CACHEBOOST_ASSET_EXTENSIONS');
        $allowed_extensions = array_map('trim', explode(',', $allowed_extensions_str));

        return in_array($extension, $allowed_extensions);
    }

    private static function serveCachedAsset($cached_asset, $extension)
    {
        header('X-CacheBoost-Asset: HIT');
        header('Content-Type: ' . self::getMimeType($extension));
        header('Cache-Control: public, max-age=' . (int) ConfigurationService::get('CACHEBOOST_ASSET_DURATION'));
        echo $cached_asset;
        exit;
    }

    public static function serveAssetCache()
    {
        $extension = '';
        if (!self::isAssetCacheEnabledAndAllowed($extension)) {
            return false;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $asset_key = 'macymedcacheboost_asset:' . md5($request_uri);
        $cached_asset = self::get($asset_key);

        if ($cached_asset) {
            self::serveCachedAsset($cached_asset, $extension);
        }

        // If not in cache, capture output and store it
        ob_start(function ($buffer) use ($asset_key, $extension) {
            if (http_response_code() >= 400 || empty($buffer)) {
                return $buffer;
            }
            self::set($asset_key, $buffer);
            header('Content-Type: ' . self::getMimeType($extension));
            header('Cache-Control: public, max-age=' . (int) ConfigurationService::get('CACHEBOOST_ASSET_DURATION'));
            return $buffer;
        });

        return true;
    }

    public static function getMimeType($extension)
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
