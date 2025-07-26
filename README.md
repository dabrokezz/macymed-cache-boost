# Macymed CacheBoost

Macymed CacheBoost is a PrestaShop module that provides an advanced caching system with support for filesystem, Redis and Memcached backends. It aims to improve page loading speed while giving control over what is cached and when caches are flushed.

## Requirements

- **PrestaShop**: version 8.1.0 or higher
- **PHP**: 7.2 or higher

## Installation

1. Navigate to the `macymedcacheboost` directory and install dependencies:

   ```bash
   composer install
   ```

2. Upload the entire `macymedcacheboost` folder to the `modules/` directory of your PrestaShop installation.
3. From the PrestaShop back‑office, go to the *Modules* page and install **Macymed CacheBoost**.

## Configuration

After installation, a new entry appears in the **Improve** section. The configuration pages let you:

- Enable or disable CacheBoost and development mode.
- Choose the caching engine (filesystem, Redis or Memcached) and provide connection settings.
- Set cache duration, exclusions and purge rules.
- Control caching of AJAX requests, assets and page types (home, category, product, CMS, …).
- Manage bots, invalidation and cache warming.

## Symfony integration

Symfony controllers are registered as services in `config/services.yml` and their routes are defined in `config/routes.yml`. PrestaShop loads these files automatically, allowing the module to add its own admin controllers with URLs such as `/macymedcacheboost/dashboard` or `/macymedcacheboost/general`.


## License

Macymed CacheBoost is distributed under the [Open Software License 3.0](LICENSE).
