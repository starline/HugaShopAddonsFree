<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 * @link https://enhancedecommerce.appspot.com/
 *
 */

namespace HugaShop\Addons\GoogleDataLayer;

use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Addons\BaseAddon;

final class GoogleDataLayer extends BaseAddon
{

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate(): ?string
    {
        if (empty(self::getSettings()->enabled)) {
            return null;
        }

        if (empty(self::getSettings()->currency_code)) {
            self::getSettings()->currency_code = FinanceCurrency::getMainCurrency()->code;
        }

        return self::fetchTemplate('datalayer.tpl');
    }
}
