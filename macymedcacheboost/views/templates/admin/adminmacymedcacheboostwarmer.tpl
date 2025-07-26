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
        <h4>{l s='Automatic Warming Settings' mod='macymedcacheboost'}</h4>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Warm cache automatically after invalidation' mod='macymedcacheboost'}</label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="CACHEBOOST_AUTO_WARMUP" id="auto_warmup_on" value="1" {if $CACHEBOOST_AUTO_WARMUP}checked="checked"{/if} />
                    <label for="auto_warmup_on" class="radioCheck">{l s='Yes' mod='macymedcacheboost'}</label>
                    <input type="radio" name="CACHEBOOST_AUTO_WARMUP" id="auto_warmup_off" value="0" {if !$CACHEBOOST_AUTO_WARMUP}checked="checked"{/if} />
                    <label for="auto_warmup_off" class="radioCheck">{l s='No' mod='macymedcacheboost'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">{l s='When content is invalidated, automatically add its URL to a queue to be re-warmed.' mod='macymedcacheboost'}</p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" name="submit_cacheboost_warmer_config" class="btn btn-success pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='macymedcacheboost'}
            </button>
        </div>
    </div>
</form>

<div class="panel">
    <h3><i class="icon icon-rocket"></i> {l s='Sitemap Cache Warmer' mod='macymedcacheboost'}</h3>
    <p>{l s='Pre-load the cache for all your site URLs based on your sitemap. This ensures visitors always get a fast experience.' mod='macymedcacheboost'}</p>
    <button type="button" id="warmup_cache_btn" class="btn btn-primary">{l s='Start Sitemap Warming' mod='macymedcacheboost'}</button>
    <div id="warmup_status" class="help-block" style="margin-top: 10px;"></div>
    <hr>
    <h4>{l s='Automatic Sitemap Warming (Cron Job)' mod='macymedcacheboost'}</h4>
    <p>{l s='To automate cache warming, set up a cron job on your server to run the following URL periodically:' mod='macymedcacheboost'}</p>
    <code>{$link->getAdminLink("AdminMacymedCacheBoostWarmer")|escape:'htmlall':'UTF-8'}&action=WarmUpCache&ajax=1&token={getAdminToken tab='AdminMacymedCacheBoostWarmer'}</code>
</div>

<div class="panel">
    <h3><i class="icon icon-list-alt"></i> {l s='Invalidation Queue' mod='macymedcacheboost'}</h3>
    <p>{l s='There are currently %s URLs in the warming queue.'|sprintf:$warming_queue_count mod='macymedcacheboost'}</p>
    <button type="button" id="process_queue_btn" class="btn btn-primary" {if $warming_queue_count == 0}disabled="disabled"{/if}>{l s='Process Queue Now' mod='macymedcacheboost'}</button>
    <div id="queue_status" class="help-block" style="margin-top: 10px;"></div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // Sitemap Warmer
        $('#warmup_cache_btn').on('click', function(){
            var $btn = $(this);
            var $status = $('#warmup_status');

            $btn.attr('disabled', true);
            $status.text('Warming up... This may take a while. Please do not close this page.').removeClass('text-success text-danger');

            $.ajax({
                url: '{$link->getAdminLink("AdminMacymedCacheBoostWarmer")|escape:"htmlall":"UTF-8"}&ajax=1&action=WarmUpCache',
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $status.text(data.message).addClass('text-success');
                    } else {
                        $status.text(data.message).addClass('text-danger');
                    }
                },
                error: function() {
                    $status.text('An unexpected error occurred during warm-up.').addClass('text-danger');
                },
                complete: function() {
                    $btn.attr('disabled', false);
                }
            });
        });

        // Queue Processor
        $('#process_queue_btn').on('click', function(){
            var $btn = $(this);
            var $status = $('#queue_status');

            $btn.attr('disabled', true);
            $status.text('Processing queue...').removeClass('text-success text-danger');

            $.ajax({
                url: '{$link->getAdminLink("AdminMacymedCacheBoostWarmer")|escape:"htmlall":"UTF-8"}&ajax=1&action=ProcessWarmingQueue',
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $status.text(data.message).addClass('text-success');
                        // Update queue count on success
                        $('#process_queue_btn').prop('disabled', true).parent().find('p').text('There are currently 0 URLs in the warming queue.');
                    } else {
                        $status.text(data.message).addClass('text-danger');
                    }
                },
                error: function() {
                    $status.text('An unexpected error occurred while processing the queue.').addClass('text-danger');
                },
                complete: function() {
                    // Re-enable button if there was an error, as the queue might still have items
                    if (!$status.hasClass('text-success')) {
                        $btn.attr('disabled', false);
                    }
                }
            });
        });
    });
</script>
