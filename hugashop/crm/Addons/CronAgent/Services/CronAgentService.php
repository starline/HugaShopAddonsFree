<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Addons\CronAgent\Services;

use HugaShop\Services\Cache;
use HugaShop\Services\Helper;
use HugaShop\Addons\CronAgent\Models\CronAgent;

final class CronAgentService
{

    /**
     * Run active agents
     */
    public static function run(): void
    {

        $agents = CronAgent::getList(['enabled' => 1], cache: null);
        foreach ($agents as $agent) {
            if (self::isDue($agent)) {
                self::execute($agent);
            }
        }
    }


    /**
     * Check if is agent due
     * @param object $agent
     * @param string $now
     */
    private static function isDue(object $agent): bool
    {
        if (empty($agent->start_at)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        if (empty($agent->last_run_at)) {
            return $now >= $agent->start_at;
        }

        $next = date(
            'Y-m-d H:i:s',
            strtotime($agent->last_run_at . " +{$agent->period_hours} hours +{$agent->period_minutes} minutes")
        );

        return $now >= $next;
    }


    /**
     * Execute agent function
     * @param object $agent
     */
    private static function execute(object $agent): void
    {
        $callable = str_replace('()', '', (string) $agent->function);
        if (str_contains($callable, '::')) {
            [$class, $method] = explode('::', $callable);

            if (class_exists($class) && method_exists($class, $method)) {
                try {
                    $class::$method();
                } catch (\Throwable) {

                    // Ignore errors
                    Helper::log('CronAgentService: execute ' . $agent->function . ' failed.');
                }
            }
        }

        CronAgent::updateOne($agent->id, ['last_run_at' => date('Y-m-d H:i:s')]);
        Cache::cache(CronAgent::class)->clear();
    }
}
