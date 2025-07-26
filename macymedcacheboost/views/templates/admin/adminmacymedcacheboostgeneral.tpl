{**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *}

<form id="module_form" class="defaultForm form-horizontal" action="{$current|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
    <input type="hidden" name="token" value="{$token}" />

    <div class="panel">
        <h4>{l s='General Settings' mod='macymedcacheboost'}</h4>

        <!-- Cache Enabled Toggle -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Enable CacheBoost' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_ENABLED" id="cache_on" value="1" {if $enabled}checked="checked"{/if} />
                    <label for="cache_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_ENABLED" id="cache_off" value="0" {if !$enabled}checked="checked"{/if} />
                    <label for="cache_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">{l s='Globally enable or disable the CacheBoost module.' mod='macymedcacheboost'}</p>
            </div>
        </div>

        <!-- Enable Cache in Dev Mode Toggle -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Enable Cache in Dev Mode' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_ENABLE_DEV_MODE" id="dev_mode_cache_on" value="1" {if $enable_dev_mode}checked="checked"{/if} />
                    <label for="dev_mode_cache_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_ENABLE_DEV_MODE" id="dev_mode_cache_off" value="0" {if !$enable_dev_mode}checked="checked"{/if} />
                    <label for="dev_mode_cache_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">{l s='Allow caching even when _PS_MODE_DEV_ is enabled. Use with caution on production.' mod='macymedcacheboost'}</p>
            </div>
        </div>

        <!-- Enable Compression Toggle -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Enable Cache Compression' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_COMPRESSION_ENABLED" id="compression_on" value="1" {if $compression_enabled}checked="checked"{/if} />
                    <label for="compression_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_COMPRESSION_ENABLED" id="compression_off" value="0" {if !$compression_enabled}checked="checked"{/if} />
                    <label for="compression_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">{l s='Enable GZIP compression for cached content. Requires zlib extension.' mod='macymedcacheboost'}</p>
            </div>
        </div>

        <!-- Cache AJAX Requests Toggle -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Cache AJAX Requests' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_CACHE_AJAX" id="cache_ajax_on" value="1" {if $cache_ajax}checked="checked"{/if} />
                    <label for="cache_ajax_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_CACHE_AJAX" id="cache_ajax_off" value="0" {if !$cache_ajax}checked="checked"{/if} />
                    <label for="cache_ajax_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">{l s='Enable caching for AJAX requests (e.g., from-xhr). This will cache JSON responses.' mod='macymedcacheboost'}</p>
            </div>
        </div>

        <!-- Cache Duration -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Cache duration (seconds)' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <input type="number" name="CACHEBOOST_DURATION" value="{$duration|escape:'htmlall':'UTF-8'}" class="form-control" />
            </div>
        </div>

        <!-- Exclusions -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Exclusions (comma-separated regex patterns)' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <input type="text" name="CACHEBOOST_EXCLUDE" value="{$exclude|escape:'htmlall':'UTF-8'}" class="form-control" />
                <p class="help-block">{l s='Example: cart, order, my-account' mod='macymedcacheboost'}</p>
            </div>
        </div>

        <!-- Purge Age -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Purge cache older than (seconds, 0 for disabled)' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <input type="number" name="CACHEBOOST_PURGE_AGE" value="{$purge_age|escape:'htmlall':'UTF-8'}" class="form-control" />
                <p class="help-block">{l s='Automatically delete cache files older than this many seconds. Set to 0 to disable.' mod='macymedcacheboost'}</p>
            </div>
        </div>

        <!-- Purge Size -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Purge cache if total size exceeds (MB, 0 for disabled)' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <input type="number" name="CACHEBOOST_PURGE_SIZE" value="{$purge_size|escape:'htmlall':'UTF-8'}" class="form-control" />
                <p class="help-block">{l s='Automatically delete oldest cache files if total cache size exceeds this limit (in Megabytes). Set to 0 to disable.' mod='macymedcacheboost'}</p>
            </div>
        </div>
    </div>

    <!-- Form Footer -->
    <div class="panel-footer">
        <button type="submit" value="1" id="module_form_submit_btn" name="submit_cacheboost_config" class="btn btn-success pull-right">
            <i class="process-icon-save"></i> {l s='Save' mod='macymedcacheboost'}
        </button>
    </div>
</form>
