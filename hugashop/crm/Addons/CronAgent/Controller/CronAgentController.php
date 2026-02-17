<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace HugaShop\Addons\CronAgent\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Addons\BaseAddonTrait;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\CronAgent\Models\CronAgent as Agent;

final class CronAgentController extends BaseAdminController
{

    use BaseAddonTrait;

    /**
     * Agents list
     */
    #[Route('/CronAgent', name: 'AddonCronAgent', priority: 20)]
    public function index()
    {
        Design::assign('agents', Agent::getList());
        Design::assign('addon', $this->getAddon());
        return $this->fetchAddonResponse('agent_list.tpl');
    }

    /**
     * Agent add/edit
     * @param ?int $id
     */
    #[Route('/CronAgent/agent', name: 'AddonCronAgentNew', priority: 20)]
    #[Route('/CronAgent/agent/{id}', name: 'AddonCronAgentAgent', priority: 20)]
    public function agent(?int $id = null)
    {
        if (!empty($agent = Secure::getInputCheckEditAccess(Agent::class, $id))) {
            if (empty($agent->id)) {
                $agent = Design::setFlashMessage('add', Agent::createOne($agent));
            } else {
                Design::setFlashMessage('update', Agent::updateOne($agent->id, $agent));
            }
            
            Cache::cache(Agent::class)->clear();
            return $this->redirectToRoute('AddonCronAgentAgent', ['id' => $agent->id]);
        }

        if (!empty($id)) {
            $agent = Agent::getOne($id);
            if (empty($agent->id)) {
                return $this->redirectToRoute('AddonCronAgent');
            }
        }

        Design::assign('addon', $this->getAddon());
        Design::assign('agent', $agent);
        return $this->fetchAddonResponse('agent.tpl');
    }
}
