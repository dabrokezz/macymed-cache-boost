<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use MacymedCacheBoost\Services\AdminConfigurationHandlerService;
use MacymedCacheBoost\Services\ConfigurationService;

class AdminMacymedCacheBoostRedisController extends ModuleAdminController
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
        $this->setTemplate('adminmacymedcacheboostredis.tpl');
    }

    public function postProcess()
    {
        AdminConfigurationHandlerService::handleForm($this->token, $this);
        return parent::postProcess();
    }

    private function assignVariablesToSmartyTpl()
    {
        $this->context->smarty->assign(ConfigurationService::getAllConfigValues());
    }
}
