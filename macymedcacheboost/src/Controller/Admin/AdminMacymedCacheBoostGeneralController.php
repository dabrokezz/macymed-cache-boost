<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Form\GeneralType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMacymedCacheBoostGeneralController extends FrameworkBundleAdminController
{
    public function indexAction(Request $request): Response
    {
        $configurationService = $this->get('macymedcacheboost.configuration.service');
        $form = $this->createForm(GeneralType::class, $configurationService->getAllConfigValues());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $configurationService->updateBulk($data);
            $this->addFlash('success', $this->trans('Settings updated', [], 'Admin.Notifications.Success'));
        }

        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostgeneral.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}