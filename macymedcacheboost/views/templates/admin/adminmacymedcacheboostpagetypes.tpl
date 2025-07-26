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
        <h4>{l s='Page Type Cache Settings' mod='macymedcacheboost'}</h4>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Cache Homepage' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_CACHE_HOMEPAGE" id="cache_homepage_on" value="1" {if $cache_homepage}checked="checked"{/if} />
                    <label for="cache_homepage_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_CACHE_HOMEPAGE" id="cache_homepage_off" value="0" {if !$cache_homepage}checked="checked"{/if} />
                    <label for="cache_homepage_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Cache Category Pages' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_CACHE_CATEGORY" id="cache_category_on" value="1" {if $cache_category}checked="checked"{/if} />
                    <label for="cache_category_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_CACHE_CATEGORY" id="cache_category_off" value="0" {if !$cache_category}checked="checked"{/if} />
                    <label for="cache_category_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Cache Product Pages' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_CACHE_PRODUCT" id="cache_product_on" value="1" {if $cache_product}checked="checked"{/if} />
                    <label for="cache_product_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_CACHE_PRODUCT" id="cache_product_off" value="0" {if !$cache_product}checked="checked"{/if} />
                    <label for="cache_product_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Cache CMS Pages' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_CACHE_CMS" id="cache_cms_on" value="1" {if $cache_cms}checked="checked"{/if} />
                    <label for="cache_cms_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_CACHE_CMS" id="cache_cms_off" value="0" {if !$cache_cms}checked="checked"{/if} />
                    <label for="cache_cms_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
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
