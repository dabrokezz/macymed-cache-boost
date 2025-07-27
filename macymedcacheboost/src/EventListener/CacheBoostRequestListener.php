<?php

namespace MacymedCacheBoost\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use MacymedCacheBoost\CacheManager;

class CacheBoostRequestListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 100]], // Priorité élevée pour s'exécuter tôt
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return; // Ne traiter que la requête principale
        }

        // Appeler la logique de cache
        CacheManager::checkAndServeCache();
    }
}
