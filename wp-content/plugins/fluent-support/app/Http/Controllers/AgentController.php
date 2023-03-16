<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Modules\StatModule;
use FluentSupport\App\Services\AvatarUploder;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\App\Http\Requests\AgentCreateRequest;

/**
 *  AgentController class for REST API
 * This class is responsible for getting data for all request related to agent
 *
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */
class AgentController extends Controller
{
    public function index(Request $request, Agent $agent)
    {
        return [
            'agents' => $agent->getAgents($request->getSafe('search')),
            'permissions' => PermissionManager::getReadablePermissionGroups()
        ];
    }

    /**
     * addAgent method will add new agent in person table
     * @param AgentCreateRequest $request
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function addAgent(AgentCreateRequest $request, Agent $agent)
    {
        try {
            return [
                'message' => __('Support Staff has been added', 'fluent-support'),
                'agent'   => $agent->createAgent($request->getSafe())
            ];

        } catch (\Exception $e) {
            return $this->sendError([
                'message' => __($e->getMessage(), 'fluent-support')
            ]);
        }
    }

    /**
     * updateAgent method will update the information of an exiting agent
     * @param AgentCreateRequest $request
     * @param Agent $agent
     * @param $agentId
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function updateAgent(AgentCreateRequest $request, Agent $agent, $agentId)
    {
        $agent = $agent::findOrFail($agentId);

        if ($agent) {
            try {
                return [
                    'message' => __('Support Staff has been updated', 'fluent-support'),
                    'agent'   => $agent->updateAgent($request->getSafe(), $agent)
                ];
            } catch (\Exception $e) {
                return $this->sendError([
                    'message' => __($e->getMessage(), 'fluent-support')
                ]);
            }
        }

    }

    /**
     * deleteAgent will delete an exiting agent and add an alternative agent as replacement
     * @param Request $request
     * @param Agent $agent
     * @param $agentId
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function deleteAgent(Request $request, Agent $agent, $agentId)
    {
        try {
            $agent->deleteAgent($request->getSafe('fallback_agent_id', '', 'intval'), $agentId);

            return [
                'message' => __('Support Staff has been deleted', 'fluent-support')
            ];

        } catch (\Exception $e) {
            return $this->sendError([
                'message' => __($e->getMessage(), 'fluent-support')
            ]);
        }

    }

    /**
     * @param Request $request
     * @return array
     */
    public function myStats(Request $request)
    {
        $agent = Helper::getAgentByUserId();//Get logged in agent information

        try {
            $stats = StatModule::getAgentStat($agent->id); //Get ticket statistics

            $with = $request->getSafe('with', []);

            $response = (new Agent())->getAgentStat($stats, $with, $agent->id);

            if (defined('FLUENTSUPPORTPRO')) {
                $response['dashboard_notice'] = apply_filters('fluent_support/dashboard_notice', '', $agent);
            }
            return $response;
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => __($e->getMessage(), 'fluent-support')
            ]);
        }
    }

    // /**
    //  * getAgentStat method will return ticket statistics by an agent id
    //  *
    //  * @param Request $request
    //  * @param $agentId
    //  * @return array
    //  */
    // public function getAgentStat(Request $request, $agentId)
    // {

    //     $stats = StatModule::getAgentStat($agentId); //Get ticket statistics

    //     $with = $request->getSafe('with', []);

    //     return (new Agent())->getAgentStat($stats, $with, $agentId);
    // }

    /**
     * addOrUpdateProfileImage method will upload profile picture for a given agent id
     * For a successful upload it's required to send file object, agent id and the user type(agent)
     * @param Request $request
     * @param AvatarUploder $avatarUploder
     * @return array
     */
    public function addOrUpdateProfileImage(Request $request, AvatarUploder $avatarUploder)
    {
        try {
            return $avatarUploder->addOrUpdateProfileImage( $request->files(), $request->getSafe('agent_id', '', 'intval'), 'agent');
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => __($e->getMessage(), 'fluent-support')
            ]);
        }
    }

    /**
     * resetAvatar method will restore a Support Staff avatar
     * For a successful upload it's required to send file object, Support Staff id and the user type(Support Staff)
     * @param Request $request
     * @param $id
     * @return array
     */
    public function resetAvatar(Agent $agent, $id){
        try {
            $agent->restoreAvatar($agent, $id);

            return [
                'message'  => __('Support Staff avatar reset to gravatar default', 'fluent-support')
            ];
        } catch (\Exception $e) {
            return [
                'message'  => __($e->getMessage(), 'fluent-support')
            ];
        }
    }
}
