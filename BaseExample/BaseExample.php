<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace HugaShop\Addons\BasseExample;

use HugaShop\Addons\BaseAddon;

final class BasseExample extends BaseAddon
{

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate(): ?string
    {
        if (empty(self::getSettings()->enabled)) {
            return null;
        }

        return self::fetchTemplate('front.tpl');
    }
}
