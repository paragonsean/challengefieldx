<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Http\Requests\TicketResponseRequest;
use FluentSupport\App\Models\Conversation;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\FluentCRMServices;
use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\ProfileInfoService;
use FluentSupport\App\Services\TicketHelper;
use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\App\Services\Tickets\TicketService;

/**
 *  TicketController class for REST API related to ticket
 * This class is responsible for getting / inserting/ modifying data for all request related to ticket
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */
class TicketController extends Controller
{
    /**
     * This `me` method will return the current user profile info
     * @param Request $request
     * @param ProfileInfoService $profileInfoService
     * @return array
     */
    public function me(Request $request, ProfileInfoService $profileInfoService)
    {
        $user = wp_get_current_user();
        $settings = [
            'user_id'     => $user->ID,
            'email'       => $user->user_email,
            'person'      => Helper::getAgentByUserId($user->ID),
            'permissions' => PermissionManager::currentUserPermissions(),
            'request'     => $request->all()
        ];

        $withPortalSettings = $request->getSafe('with_portal_settings');

        return $profileInfoService->me( $settings, $withPortalSettings );
    }

    /**
     * index method will return the list of ticket based on the selected filter
     * @param Request $request
     * @return array
     */
    public function index(Request $request, TicketService $ticketService)
    {
        //Selected filter type, either simple or Advanced
        $filterType = $request->getSafe('filter_type', 'simple');
        $data = $request->all();
        return $ticketService->getTickets($data, $filterType);
    }

    /**
     * createTicket method will create new ticket as well as customer or WP user
     * @param Request $request
     * @param Ticket $ticket
     * @return array
     * @throws \Exception
     */
    public function createTicket(Request $request, Ticket $ticket)
    {
        $ticketData = $request->getSafe('ticket', [], 'wp_kses_post');

        $maybeNewCustomer = $request->getSafe('newCustomer');

        $this->validate($ticketData, [
            'title'   => 'required',
            'content' => 'required'
        ]);

        $createdTicket = $ticket->createTicket($ticketData, $maybeNewCustomer);

        if (is_wp_error($createdTicket)) {
            return $this->sendError([
                'message' => $createdTicket->get_error_message()
            ]);
        }

        return [
            'message' => 'Ticket has been successfully created',
            'ticket'  => $createdTicket
        ];
    }

    /**
     * getTicket method will return ticket information by ticket id
     * @param Request $request
     * @param $ticketId
     * @return array
     */
    public function getTicket(Request $request, Ticket $ticket, $ticketId)
    {
        try {
            $ticketWith = $request->getSafe('with', []);
            if (!$ticketWith) {
                $ticketWith = ['customer', 'agent', 'product', 'mailbox', 'tags', 'attachments' => function ($q) {
                    $q->whereIn('status', ['active', 'inline']);
                }];
            }
            $withCrmData = in_array('fluentcrm_profile', $request->query('with_data', []));

            return $ticket->getTicket($ticketWith, $withCrmData, $ticketId);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * createResponse method will create response by agent for the ticket
     * @param Request $request
     * @param Ticket $ticket
     * @param int $ticketId
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function createResponse(TicketResponseRequest $request, Ticket $ticket, $ticketId)
    {
        $data = $request->getSafe(null, [], 'wp_kses_post');

        try {
            return $ticket->createResponse($data, $ticketId);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * getTicketWidgets method generate additional information for a ticket by  customer
     * @param Ticket $ticket
     * @param $ticketId
     * @return array
     */
    public function getTicketWidgets(Ticket $ticket, $ticketId)
    {
        try {
            return $ticket->getTicketWidgets($ticketId);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * updateTicketProperty method will update ticket property
     * @param Request $request
     * @param Ticket $ticket
     * @param $ticketId
     * @return array
     */
    public function updateTicketProperty(Request $request, Ticket $ticket, $ticketId)
    {
        $propName = $request->getSafe('prop_name');
        $propValue = $request->getSafe('prop_value');

        try {
            return $ticket->updateTicketProperty($propName, $propValue, $ticketId);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * closeTicket method close the ticket by id
     * @param Ticket $ticket
     * @param int $ticketId
     * @return array
     */
    public function closeTicket(Ticket $ticket, $ticketId)
    {
        try {
            return $ticket->closeTicket($ticketId, $this->request->getSafe('close_ticket_silently'));
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * reOpenTicket method will reopen a closed ticket
     * @param Request $request
     * @param $ticketId
     * @return array
     */
    public function reOpenTicket(Ticket $ticket, $ticketId)
    {
        try {
            return $ticket->reOpenTicket($ticketId);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * doBulkActions method is responsible for bulk action
     * This function will get ticket ids and action as parameter and perform action based on the selection
     * @param Request $request
     * @param Ticket $ticket
     * @return array|string[]|void
     * @throws \Exception
     */
    public function doBulkActions(Request $request, Ticket $ticket)
    {
        $action = $request->getSafe('bulk_action');//get action
        $ticketIds = $request->getSafe('ticket_ids', [], 'intval');

        try {
            return $ticket->handleBulkActions($action, $ticketIds);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * doBulkReplies method will create response for bulk tickets
     * This function will get ticket ids, content, attachment etc and create response for tickets
     * @param Request $request
     * @param Conversation $conversation
     * @return array
     * @throws \Exception
     */
    public function doBulkReplies(Request $request, Conversation $conversation)
    {
        $data = $request->get();
        $this->validate($data, [
            'content'    => 'required',
            'ticket_ids' => 'required|array'
        ]);

        try {
            return $conversation->doBulkReplies($data);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * deleteResponse method will remove a response from ticket by ticket id and response id
     * @param Request $request
     * @param Conversation $conversation
     * @param $ticketId
     * @param $responseId
     * @return array
     */
    public function deleteResponse(Conversation $conversation, $ticketId, $responseId)
    {
        try {
            return $conversation->deleteResponse($ticketId, $responseId);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * updateResponse method will update ticket response using ticket and response id
     * @param Request $request
     * @param Conversation $conversation
     * @param int $ticketId
     * @param int $responseId
     * @return array
     * @throws \Exception
     */
    public function updateResponse(TicketResponseRequest $request, Conversation $conversation, $ticketId, $responseId)
    {
        $data = $request->get();

        try {
            return $conversation->updateResponse($data, $ticketId, $responseId);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * getLiveActivity method will return the activity in a ticket by agents
     * @param Request $request
     * @param $ticketId
     * @return array
     */
    public function getLiveActivity(Request $request, $ticketId)
    {
        $agent = Helper::getAgentByUserId();

        return [
            'live_activity' => TicketHelper::getActivity($ticketId, $agent->id)
        ];
    }

    /**
     * removeLiveActivity method will remove activities that
     * @param Request $request
     * @param $ticketId
     * @return array
     */
    public function removeLiveActivity(Request $request, $ticketId)
    {
        $agent = Helper::getAgentByUserId();

        return [
            'result'   => TicketHelper::removeFromActivities($ticketId, $agent->id),
            'agent_id' => $agent->id
        ];
    }

    /**
     * addTag method will add tag in ticket by ticket id
     * @param Request $request
     * @param $ticketId
     * @return array
     */
    public function addTag(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $ticket->applyTags($request->getSafe('tag_id', '', 'intval'));

        return [
            'message' => __('Tag has been added to this ticket', 'fluent-support'),
            'tags'    => $ticket->tags
        ];
    }

    /**
     * detachTag method will remove all tags from tickets
     * @param $ticketId
     * @param $tagId
     * @return array
     */
    public function detachTag($ticketId, $tagId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->detachTags($tagId);

        return [
            'message' => __('Tag has been removed from this ticket', 'fluent-support'),
            'tags'    => $ticket->tags
        ];
    }

    /**
     * changeTicketCustomer method will update customer in a ticket
     * This method will get ticket id and customer id as parameter, it will replace existing customer id with new
     * @param Request $request
     * @return array
     */
    public function changeTicketCustomer(Request $request)
    {
        $updateCustomer = Ticket::where('id', $request->getSafe('ticket_id', '', 'intval'))
            ->update(['customer_id' => $request->getSafe('customer')]);
        return [
            'message'         => __('Customer has been updated', 'fluent-support'),
            'updatedCustomer' => $updateCustomer
        ];
    }

    /**
     * getTicketCustomData method will return the custom data by ticket id
     * @param Request $request
     * @param $ticketId
     * @return array|array[]
     */
    public function getTicketCustomData(Request $request, $ticketId)
    {
        if (!defined('FLUENTSUPPORTPRO')) {
            return [
                'custom_data'     => [],
                'rendered_fields' => []
            ];
        }

        $ticket = Ticket::findOrFail($ticketId);

        return [
            'custom_data'     => (object)$ticket->customData(),
            'rendered_fields' => \FluentSupportPro\App\Services\CustomFieldsService::getRenderedPublicFields($ticket->customer)
        ];
    }

    /**
     * syncFluentCrmTags method will synchronize the tags with Fluent CRM by contact id
     *This function will get contact id and tags as parameter, get existing tags from crm and updated added/removed tags
     * @param Request $request
     * @param FluentCRMServices $fluentCRMServices
     * @return array
     */
    public function syncFluentCrmTags(Request $request, FluentCRMServices $fluentCRMServices)
    {
        $data = $request->only(['contact_id', 'tags']);
        try {
            return $fluentCRMServices->syncCrmTags($data);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }

    /**
     * This `syncFluentCrmLists` method will synchronize the lists with Fluent CRM by contact id
     *  This method will get contact id and lists as parameter, get existing lists from crm and updated added/removed lists
     * @param Request $request
     * @param FluentCRMServices $fluentCRMServices
     * @return array
     */

    public function syncFluentCrmLists(Request $request, FluentCRMServices $fluentCRMServices)
    {
        $data = $request->only(['contact_id', 'lists']);
        try {
            return $fluentCRMServices->syncCrmLists($data);
        } catch (\Exception $e) {
            return $this->sendError(__($e->getMessage(), 'fluent-support'));
        }
    }
}
