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
        $this->assignVariablesToSmartyTpl();

        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostinvalidation.html.twig', [
            // Passez ici les variables nécessaires à votre template Twig
        ]);
    }

    public function ajaxProcess($action = null)
    {
        if ($action === null) {
            $action = Tools::getValue('action');
        }
        AdminAjaxHandlerService::handleAjaxRequest($action, $this->context);
    }

    private function assignVariablesToSmartyTpl()
    {
        $this->context->smarty->assign(ConfigurationService::getAllConfigValues());
    }
}