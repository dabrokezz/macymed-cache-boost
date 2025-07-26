<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ModuleAdminController;
use MacymedCacheBoost\Services\AdminAjaxHandlerService;
use MacymedCacheBoost\Services\ConfigurationService;
use Tools;

class AdminMacymedCacheBoostInvalidationController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();
        $this->assignVariablesToSmartyTpl();
        $this->setTemplate('adminmacymedcacheboostinvalidation.tpl');
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
