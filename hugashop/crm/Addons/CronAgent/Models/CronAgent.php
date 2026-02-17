<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Addons\CronAgent\Models;

use HugaShop\Addons\BaseAddonModel;

final class CronAgent extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'             => ['type' => 'int',      'auto_increment' => true],
        'name'           => ['type' => 'varchar',  'required' => true],
        'description'    => ['type' => 'text'],
        'start_at'       => ['type' => 'datetime'],
        'period_hours'   => ['type' => 'int',      'def' => 0],
        'period_minutes' => ['type' => 'int',      'def' => 0],
        'function'       => ['type' => 'varchar'],
        'enabled'        => ['type' => 'tinyint',  'def' => 0],
        'last_run_at'    => ['type' => 'datetime'],
    ];
}
