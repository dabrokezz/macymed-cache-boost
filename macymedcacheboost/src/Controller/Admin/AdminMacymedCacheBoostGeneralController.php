<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Form\GeneralType;
use MacymedCacheBoost\Services\ConfigurationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMacymedCacheBoostGeneralController extends FrameworkBundleAdminController
{
    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(GeneralType::class, $this->get('macymedcacheboost.configuration.service')->getAllConfigValues());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->get('macymedcacheboost.configuration.service')->updateBulk($data);
            $this->addFlash('success', $this->trans('Settings updated', [], 'Admin.Notifications.Success'));
        }

        return $this->render('@Modules/macymedcacheboost/views/templates/admin/adminmacymedcacheboostgeneral.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}