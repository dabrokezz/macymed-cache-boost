<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Form\WarmerType;
use MacymedCacheBoost\Services\ConfigurationService;
use MacymedCacheBoost\Services\WarmingQueueService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tools;

class AdminMacymedCacheBoostWarmerController extends FrameworkBundleAdminController
{
    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(WarmerType::class, $this->get('macymedcacheboost.configuration.service')->getAllConfigValues());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->get('macymedcacheboost.configuration.service')->updateBulk($data);
            $this->addFlash('success', $this->trans('Settings updated', [], 'Admin.Notifications.Success'));
        }

        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostwarmer.html.twig', [
            'form' => $form->createView(),
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
}