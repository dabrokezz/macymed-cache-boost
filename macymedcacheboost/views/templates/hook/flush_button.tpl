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

<style>
    #header_cache_flush_button {
        background-color: #dc3545; /* Red color */
        color: white;
        border: none;
        padding: 5px 10px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        margin: 0 5px;
        cursor: pointer;
        border-radius: 5px;
    }
    #header_cache_flush_button:hover {
        background-color: #c82333;
    }
</style>

<script type="text/javascript">
    $(document).ready(function(){
        $('#header_cache_flush_button').on('click', function(e){
            e.preventDefault();
            if (confirm('{l s='Are you sure you want to flush the entire cache?' mod='macymedcacheboost' js=1}')) {
                $.ajax({
                    url: '{$admin_link|escape:'htmlall':'UTF-8'}&ajax=1&action=FlushAllCache&token={$token|escape:'htmlall':'UTF-8'}',
                    type: 'POST',
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            alert('{l s='Cache flushed successfully!' mod='macymedcacheboost' js=1}');
                        } else {
                            alert('{l s='Failed to flush cache: ' mod='macymedcacheboost' js=1}' + data.message);
                        }
                    },
                    error: function() {
                        alert('{l s='An unexpected error occurred.' mod='macymedcacheboost' js=1}');
                    }
                });
            }
        });
    });
</script>

<a href="#" id="header_cache_flush_button" title="{l s='Flush Cache' mod='macymedcacheboost'}">
    <i class="icon-eraser"></i> {l s='Flush Cache' mod='macymedcacheboost'}
</a>
