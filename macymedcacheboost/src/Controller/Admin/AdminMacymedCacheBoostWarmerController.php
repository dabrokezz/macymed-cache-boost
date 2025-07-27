<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Services\AdminAjaxHandlerService;
use MacymedCacheBoost\Services\ConfigurationService;
use MacymedCacheBoost\Services\WarmingQueueService;
use Symfony\Component\HttpFoundation\Response;
use Tools;

class AdminMacymedCacheBoostWarmerController extends FrameworkBundleAdminController
{
    public function indexAction(): Response
    {
        // Gérer le postProcess si le formulaire est soumis
        if (\Tools::isSubmit('submit_cacheboost_warmer_config')) {
            ConfigurationService::update('CACHEBOOST_AUTO_WARMUP', (bool) Tools::getValue('CACHEBOOST_AUTO_WARMUP'));
            $this->addFlash('success', $this->trans('Settings updated', [], 'Admin.Notifications.Success'));
        }

        $this->assignVariablesToSmartyTpl();

        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostwarmer.html.twig', [
            'warming_queue_count' => WarmingQueueService::getQueueCount(),
        ]);
    }

    public function ajaxProcess($action = null)
    {
        if ($action === null) {
            $action = Tools::getValue('action');
        }
        if ($action === 'ProcessWarmingQueue') {
            $result = WarmingQueueService::processQueue();
            die(json_encode($result));
        }
        AdminAjaxHandlerService::handleAjaxRequest($action, $this->context);
    }

    private function assignVariablesToSmartyTpl()
    {
        $this->context->smarty->assign(ConfigurationService::getAllConfigValues());
    }
}