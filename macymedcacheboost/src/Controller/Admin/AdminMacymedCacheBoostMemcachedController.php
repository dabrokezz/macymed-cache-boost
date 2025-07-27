<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Services\AdminConfigurationHandlerService;
use MacymedCacheBoost\Services\ConfigurationService;
use Symfony\Component\HttpFoundation\Response;

class AdminMacymedCacheBoostMemcachedController extends FrameworkBundleAdminController
{
    public function indexAction(): Response
    {
        // Gérer le postProcess si le formulaire est soumis
        if (\Tools::isSubmit('submit_cacheboost_config')) {
            AdminConfigurationHandlerService::handleForm($this->token, $this);
        }

        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostmemcached.html.twig', [
            'config_values' => ConfigurationService::getAllConfigValues(),
        ]);
    }
}