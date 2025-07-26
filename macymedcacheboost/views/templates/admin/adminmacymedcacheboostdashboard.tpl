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

<div class="panel">
    <h3><i class="icon icon-bar-chart"></i> {l s='Cache Statistics' mod='macymedcacheboost'}</h3>
    <div class="row">
        <div class="col-lg-4">
            <strong>{l s='Cache Engine:' mod='macymedcacheboost'}</strong> <span class="badge">{$cache_stats.engine|escape:'htmlall':'UTF-8'}</span>
        </div>
        <div class="col-lg-4">
            <strong>{l s='Cached Pages:' mod='macymedcacheboost'}</strong> <span class="badge">{$cache_stats.count|escape:'htmlall':'UTF-8'}</span>
        </div>
        <div class="col-lg-4">
            <strong>{l s='Cache Size:' mod='macymedcacheboost'}</strong> <span class="badge">{$cache_stats.size|escape:'htmlall':'UTF-8'}</span>
        </div>
    </div>
    <div class="row" style="margin-top: 10px;">
        <div class="col-lg-4">
            <strong>{l s='Cache Hits:' mod='macymedcacheboost'}</strong> <span class="badge">{$cache_stats.hits|escape:'htmlall':'UTF-8'}</span>
        </div>
        <div class="col-lg-4">
            <strong>{l s='Cache Misses:' mod='macymedcacheboost'}</strong> <span class="badge">{$cache_stats.misses|escape:'htmlall':'UTF-8'}</span>
        </div>
        <div class="col-lg-4">
            <strong>{l s='Last Flush:' mod='macymedcacheboost'}</strong> <span class="badge">{$cache_stats.last_flush|escape:'htmlall':'UTF-8'}</span>
        </div>
    </div>
</div>
