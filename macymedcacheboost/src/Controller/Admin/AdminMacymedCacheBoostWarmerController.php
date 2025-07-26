<?php

namespace MacymedCacheBoost\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ModuleAdminController;
use MacymedCacheBoost\Services\AdminAjaxHandlerService;
use MacymedCacheBoost\Services\ConfigurationService;
use MacymedCacheBoost\Services\WarmingQueueService;
use Tools;

class AdminMacymedCacheBoostWarmerController extends ModuleAdminController
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
        $this->setTemplate('adminmacymedcacheboostwarmer.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit_cacheboost_warmer_config')) {
            ConfigurationService::update('CACHEBOOST_AUTO_WARMUP', (bool) Tools::getValue('CACHEBOOST_AUTO_WARMUP'));
            $this->confirmations[] = $this->trans('Settings updated', [], 'Admin.Notifications.Success');
        }
        return parent::postProcess();
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
        $this->context->smarty->assign('warming_queue_count', WarmingQueueService::getQueueCount());
    }
}
