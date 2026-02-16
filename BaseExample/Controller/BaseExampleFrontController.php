<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 * 
 */

namespace HugaShop\Addons\BaseExample\Controller;

use HugaShop\Services\Design;
use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;

final class BaseExample extends BaseFrontController
{

    use BaseAddonTrait;

    /**
     * .../addon/BaseExample
     */
    #[Route('/BaseExample', name: 'AddonBaseExample', priority: 20)]
    public function index()
    {
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('index.tpl');
    }
}
