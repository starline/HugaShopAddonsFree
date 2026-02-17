<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace HugaShop\Addons\CronAgent\EventListener;

use HugaShop\Addons\BaseExample\BaseExample;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class BaseExampleListener
{

    /**
     * Run agents on site requests when cron is disabled
     * @param RequestEvent $event
     */
    #[AsEventListener]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!BaseExample::isEnabled()) {
            return;
        }

        // Do somthing ... 
    }
}
