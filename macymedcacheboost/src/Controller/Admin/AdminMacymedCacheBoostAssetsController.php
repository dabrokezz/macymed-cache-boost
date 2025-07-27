<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Services\AdminConfigurationHandlerService;
use MacymedCacheBoost\Services\ConfigurationService;
use Symfony\Component\HttpFoundation\Response;

class AdminMacymedCacheBoostAssetsController extends FrameworkBundleAdminController
{
    public function indexAction(): Response
    {
        // Gérer le postProcess si le formulaire est soumis
        if (\Tools::isSubmit('submit_cacheboost_config')) {
            AdminConfigurationHandlerService::handleForm($this->token, $this);
        }

        $this->assignVariablesToSmartyTpl();

        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostassets.html.twig', [
            // Passez ici les variables nécessaires à votre template Twig
            // Exemple: 'some_variable' => $this->someService->getData(),
        ]);
    }

    private function assignVariablesToSmartyTpl()
    {
        // Cette méthode est conservée pour la compatibilité si des templates Smarty sont encore utilisés
        // ou si des variables doivent être assignées au contexte Smarty global.
        $this->context->smarty->assign(ConfigurationService::getAllConfigValues());
    }
}