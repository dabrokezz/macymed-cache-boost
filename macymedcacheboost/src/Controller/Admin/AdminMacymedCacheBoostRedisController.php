<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use MacymedCacheBoost\Form\RedisType;
use MacymedCacheBoost\Services\ConfigurationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMacymedCacheBoostRedisController extends FrameworkBundleAdminController
{
    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(RedisType::class, $this->get('macymedcacheboost.configuration.service')->getAllConfigValues());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->get('macymedcacheboost.configuration.service')->updateBulk($data);
            $this->addFlash('success', $this->trans('Settings updated',  'Admin.Notifications.Success'));
        }

        return $this->render('@Modules/macymedcacheboost/views/templates/admin/form.html.twig', [
            'form' => $form->createView(),
            'layoutTitle' => $this->trans('Redis Settings', 'Modules.Macymedcacheboost.Admin'),
        ]);
    }
}