<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Services\AdminAjaxHandlerService;
use MacymedCacheBoost\Services\ConfigurationService;
use Symfony\Component\HttpFoundation\Response;
use Tools;

class AdminMacymedCacheBoostInvalidationController extends FrameworkBundleAdminController
{
    public function indexAction(): Response
    {
        $ajax_url = $this->getContext()->link->getAdminLink('AdminMacymedCacheBoostInvalidation', true, [], ['ajax' => 1]);
        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostinvalidation.html.twig', [
            'config_values' => $this->get('macymedcacheboost.configuration.service')->getAllConfigValues(),
            'ajax_url' => $ajax_url,
        ]);
    }

    public function ajaxProcess($action = null)
    {
        if ($action === null) {
            $action = Tools::getValue('action');
        }
        $this->get('macymedcacheboost.admin_ajax_handler.service')->handleAjaxRequest($action, $this->context);
    }

}