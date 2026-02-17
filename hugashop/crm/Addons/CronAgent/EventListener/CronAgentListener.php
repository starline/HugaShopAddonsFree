<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Addons\CronAgent\EventListener;

use HugaShop\Addons\CronAgent\CronAgent;
use HugaShop\Addons\CronAgent\Services\CronAgentService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class CronAgentListener
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

        if (!CronAgent::isEnabled() || CronAgent::getSettings('use_cron')) {
            return;
        }

        CronAgentService::run();
    }
}
