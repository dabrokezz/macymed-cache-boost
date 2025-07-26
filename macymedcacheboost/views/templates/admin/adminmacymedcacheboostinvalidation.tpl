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
    <h3><i class="icon icon-eraser"></i> {l s='Granular Cache Invalidation' mod='macymedcacheboost'}</h3>
    <p>{l s='Invalidate cache for specific content types or URLs.' mod='macymedcacheboost'}</p>

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Invalidate Product Cache' mod='macymedcacheboost'}</label>
        <div class="col-lg-6">
            <input type="number" id="invalidate_product_id" class="form-control" placeholder="{l s='Product ID' mod='macymedcacheboost'}" />
        </div>
        <div class="col-lg-3">
            <button type="button" id="invalidate_product_btn" class="btn btn-default">{l s='Invalidate' mod='macymedcacheboost'}</button>
        </div>
        <div class="col-lg-9 col-lg-offset-3">
            <p id="invalidate_product_status" class="help-block"></p>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Invalidate Category Cache' mod='macymedcacheboost'}</label>
        <div class="col-lg-6">
            <input type="number" id="invalidate_category_id" class="form-control" placeholder="{l s='Category ID' mod='macymedcacheboost'}" />
        </div>
        <div class="col-lg-3">
            <button type="button" id="invalidate_category_btn" class="btn btn-default">{l s='Invalidate' mod='macymedcacheboost'}</button>
        </div>
        <div class="col-lg-9 col-lg-offset-3">
            <p id="invalidate_category_status" class="help-block"></p>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Invalidate CMS Page Cache' mod='macymedcacheboost'}</label>
        <div class="col-lg-6">
            <input type="number" id="invalidate_cms_id" class="form-control" placeholder="{l s='CMS ID' mod='macymedcacheboost'}" />
        </div>
        <div class="col-lg-3">
            <button type="button" id="invalidate_cms_btn" class="btn btn-default">{l s='Invalidate' mod='macymedcacheboost'}</button>
        </div>
        <div class="col-lg-9 col-lg-offset-3">
            <p id="invalidate_cms_status" class="help-block"></p>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Invalidate Specific URL' mod='macymedcacheboost'}</label>
        <div class="col-lg-6">
            <input type="text" id="invalidate_url" class="form-control" placeholder="{l s='Full URL (e.g., https://yourstore.com/product.html)' mod='macymedcacheboost'}" />
        </div>
        <div class="col-lg-3">
            <button type="button" id="invalidate_url_btn" class="btn btn-default">{l s='Invalidate' mod='macymedcacheboost'}</button>
        </div>
        <div class="col-lg-9 col-lg-offset-3">
            <p id="invalidate_url_status" class="help-block"></p>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        function setupInvalidationButton(buttonId, inputId, statusId, actionName) {
            $(buttonId).on('click', function() {
                var $btn = $(this);
                var $status = $(statusId);
                var id = $(inputId).val();
                var data = {};
                if (actionName === 'InvalidateUrl') {
                    data.url = id;
                } else {
                    data['id_' + actionName.replace('Invalidate', '').toLowerCase()] = id;
                }

                $btn.attr('disabled', true);
                $status.text('Processing...').removeClass('text-success text-danger');

                $.ajax({
                    url: '{$link->getAdminLink("AdminMacymedCacheBoostInvalidation")|escape:"htmlall":"UTF-8"}&ajax=1&action=' + actionName,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(data) {
                        if (data.success) {
                            $status.text(data.message).addClass('text-success');
                        } else {
                            $status.text(data.message).addClass('text-danger');
                        }
                    },
                    error: function() {
                        $status.text('An unexpected error occurred.').addClass('text-danger');
                    },
                    complete: function() {
                        $btn.attr('disabled', false);
                    }
                });
            });
        }

        setupInvalidationButton('#invalidate_product_btn', '#invalidate_product_id', '#invalidate_product_status', 'InvalidateProductCache');
        setupInvalidationButton('#invalidate_category_btn', '#invalidate_category_id', '#invalidate_category_status', 'InvalidateCategoryCache');
        setupInvalidationButton('#invalidate_cms_btn', '#invalidate_cms_id', '#invalidate_cms_status', 'InvalidateCmsCache');
        setupInvalidationButton('#invalidate_url_btn', '#invalidate_url', '#invalidate_url_status', 'InvalidateUrl');
    });
</script>
