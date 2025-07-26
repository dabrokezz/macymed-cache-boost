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

    <div id="memcached_options" class="panel">
        <h4>{l s='Memcached Connection Settings' mod='macymedcacheboost'}</h4>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Memcached Server IP' mod='macymedcacheboost'}</label>
            <div class="col-lg-3">
                <input type="text" name="CACHEBOOST_MEMCACHED_IP" id="memcached_ip" value="{$memcached_ip|escape:'htmlall':'UTF-8'}" class="form-control" />
            </div>
            <label class="control-label col-lg-2">{l s='Memcached Server Port' mod='macymedcacheboost'}</label>
            <div class="col-lg-2">
                <input type="number" name="CACHEBOOST_MEMCACHED_PORT" id="memcached_port" value="{$memcached_port|escape:'htmlall':'UTF-8'}" class="form-control" />
            </div>
            <div class="col-lg-2">
                <button type="button" id="test_memcached_connection" class="btn btn-info">{l s='Test Connection' mod='macymedcacheboost'}</button>
            </div>
            <div class="col-lg-9 col-lg-offset-3">
                <p id="memcached_test_result" class="help-block"></p>
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

<script type="text/javascript">
    $(document).ready(function(){
        // Test Memcached connection via AJAX
        $('#test_memcached_connection').on('click', function(){
            var $resultP = $('#memcached_test_result');
            $resultP.text('Testing...').removeClass('text-success text-danger');

            $.ajax({
                url: '{$link->getAdminLink("AdminMacymedCacheBoostMemcached")|escape:"htmlall":"UTF-8"}&ajax=1&action=TestCacheConnection',
                type: 'POST',
                dataType: 'json',
                data: {
                    engine: 'memcached',
                    ip: $('#memcached_ip').val(),
                    port: $('#memcached_port').val()
                },
                success: function(data) {
                    $resultP.text(data.message);
                    if (data.success) {
                        $resultP.addClass('text-success');
                    } else {
                        $resultP.addClass('text-danger');
                    }
                },
                error: function() {
                    $resultP.text('AJAX error').addClass('text-danger');
                }
            });
        });
    });
</script>
