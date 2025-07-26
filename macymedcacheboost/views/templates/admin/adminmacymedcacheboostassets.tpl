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
        <h4>{l s='Asset Cache Settings' mod='macymedcacheboost'}</h4>
        <!-- Asset Cache Enabled Toggle -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Enable Asset Caching' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_ASSET_CACHE_ENABLED" id="asset_cache_on" value="1" {if $asset_cache_enabled}checked="checked"{/if} />
                    <label for="asset_cache_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_ASSET_CACHE_ENABLED" id="asset_cache_off" value="0" {if !$asset_cache_enabled}checked="checked"{/if} />
                    <label for="asset_cache_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">{l s='Cache static assets (JS, CSS, Images) to improve loading times.' mod='macymedcacheboost'}</p>
            </div>
        </div>

        <!-- Asset Extensions -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Asset File Extensions (comma-separated)' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <input type="text" name="CACHEBOOST_ASSET_EXTENSIONS" value="{$asset_extensions|escape:'htmlall':'UTF-8'}" class="form-control" />
                <p class="help-block">{l s='List of file extensions to cache as assets (e.g., js,css,png,jpg).' mod='macymedcacheboost'}</p>
            </div>
        </div>

        <!-- Asset Cache Duration -->
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Asset Cache Duration (seconds)' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <input type="number" name="CACHEBOOST_ASSET_DURATION" value="{$asset_duration|escape:'htmlall':'UTF-8'}" class="form-control" />
                <p class="help-block">{l s='How long assets should be cached by the browser and proxy servers.' mod='macymedcacheboost'}</p>
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
