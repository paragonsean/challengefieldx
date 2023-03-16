<?php

namespace FluentSupport\App\Http\Controllers;

use Exception;
use FluentSupport\App\Http\Requests\TicketResponseRequest;
use FluentSupport\App\Models\Product;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\CustomerPortalService;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Request\Request;

/**
 * CustomerPortalController class for REST API
 * This class is responsible for getting data for all request related to customer and customer portal
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */
class CustomerPortalController extends Controller
{
    /**
     * getTickets will generate ticket information with customer and agents by customer
     * @param Request $request
     * @param CustomerPortalService $customerPortalService
     * @return array
     * @throws Exception
     */
    public function getTickets(Request $request, CustomerPortalService $customerPortalService)
    {
        $onBehalf = $request->getSafe('on_behalf', '', 'intval');
        $userIP = $request->getIp();

        try {
            $customer = $customerPortalService->resolveCustomer($onBehalf, $userIP);
            return [
                'tickets' => $customerPortalService->getTickets($customer, $request->getSafe('filter_type', ''))
            ];
        } catch (Exception $e) {
            return $this->sendError([
                'message'    => $e->getMessage(),
                'error_type' => $e->getCode()
            ]);
        }
    }

    /**
     * createTicket method will create ticket submitted by customers
     * @param Request $request
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function createTicket(Request $request, CustomerPortalService $customerPortalService)
    {
        $data = $this->validate($request->get(), [
            'title'   => 'required',
            'content' => 'required'
        ]);

        $data['title'] = sanitize_text_field($data['title']);
        $data['content'] = wp_kses_post($data['content']);

        $onBehalf = $request->getSafe('on_behalf', '', 'intval');
        $userIP = $request->getIp();

        try {
            $customer = $customerPortalService->resolveCustomer($onBehalf, $userIP, true);

            $canCreateTicket = apply_filters('fluent_support/can_customer_create_ticket', true, $customer, $data);

            if (!$canCreateTicket || is_wp_error($canCreateTicket)) {
                $isWpError = is_wp_error($canCreateTicket);

                $message = ($isWpError) ? $canCreateTicket->get_error_message() : __('Sorry you can not create ticket', 'fluent-support');
                $errorCode = ($isWpError) ? $canCreateTicket->get_error_code() : 'general_error';


                throw new \Exception($message, $errorCode);
            }

            if ($customer && $messageId = Helper::generateMessageID($customer->email)) {
                $data['message_id'] = $messageId;
            }
            $defaultMailbox = Helper::getDefaultMailBox();
            $ticket = $customerPortalService->createTicket($customer, $data, $request->getSafe('mailbox_id', $defaultMailbox->id , 'intval'));

            return [
                'message' => __('Ticket has been created successfully', 'fluent-support'),
                'ticket'  => $ticket
            ];
        } catch (\Exception $e) {

            return $this->sendError([
                'message'    => __($e->getMessage(), 'fluent-support'),
                'error_type' => $e->getCode()
            ]);
        }
    }

    /**
     * getTicket method will get the ticket information with customer and agent as well as response in a ticket by ticket id
     * @param Request $request
     * @param $ticketId
     * @return array
     */
    public function getTicket(Request $request, CustomerPortalService $customerPortalService, $ticketId)
    {
        $customerAdditionalData = [
            'intended_ticket_hash' => $request->getSafe('intended_ticket_hash', ''),
            'on_behalf'            => $request->getSafe('on_behalf', '', 'intval'),
            'user_ip'              => $request->getIp()
        ];

        try {
            return $customerPortalService->getTicket($customerAdditionalData, $ticketId);
        } catch (Exception $e) {
            return $this->sendError([
                'message'    => $e->getMessage(),
                'error_type' => $e->getCode()
            ]);
        }
    }

    /**
     * createResponse method will create response by customer in a ticket by ticket id
     * @param Request $request
     * @param $ticketId
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function createResponse(TicketResponseRequest $request, CustomerPortalService $customerPortalService, $ticketId)
    {
        $customerAdditionalData = [
            'intended_ticket_hash' => $request->getSafe('intended_ticket_hash', ''),
            'on_behalf'            => $request->getSafe('on_behalf', '', 'intval'),
            'user_ip'              => $request->getIp()
        ];

        $ticket = Ticket::findOrFail($ticketId);
        $data = $request->getSafe(null, [], 'wp_kses_post');

        $canCreateResponse = apply_filters('fluent_support/can_customer_create_response', true, $ticket->customer, $ticket, $data);

        if (!$canCreateResponse || is_wp_error($canCreateResponse)) {
            return [
                'type'    => 'error',
                'message' => (is_wp_error($canCreateResponse)) ? $canCreateResponse->get_error_message() : __('Sorry you can not create response', 'fluent-support')
            ];
        }

        try {
            return $customerPortalService->createResponse($customerAdditionalData, $ticketId, $data);
        } catch (Exception $e) {
            return $this->sendError([
                'message'    => $e->getMessage(),
                'error_type' => $e->getCode()
            ]);
        }
    }

    /**
     * This `closeTicket` is responsible for closing ticket by ticket id
     * @param Request $request
     * @param $ticketId
     * @return array
     */
    public function closeTicket(Request $request, CustomerPortalService $customerPortalService, $ticketId)
    {
        $customerAdditionalData = [
            'intended_ticket_hash' => $request->getSafe('intended_ticket_hash', ''),
            'on_behalf'            => $request->getSafe('on_behalf', '', 'intval'),
            'user_ip'              => $request->getIp()
        ];
        try {
            return $customerPortalService->closeTicket($customerAdditionalData, $ticketId);
        } catch (Exception $e) {
            return $this->sendError([
                'message'    => $e->getMessage(),
                'error_type' => $e->getCode()
            ]);
        }
    }

    /**
     * closeTicket method will re-open a ticket by customer using ticket id
     * @param Request $request
     * @param $ticketId
     * @return array
     */
    public function reOpenTicket(Request $request, CustomerPortalService $customerPortalService, $ticketId)
    {
        $customerAdditionalData = [
            'intended_ticket_hash' => $request->getSafe('intended_ticket_hash', ''),
            'on_behalf'            => $request->getSafe('on_behalf', '', 'intval'),
            'user_ip'              => $request->getIp()
        ];
        try {
            return $customerPortalService->reOpenTicket($customerAdditionalData, $ticketId);
        } catch (Exception $e) {
            return $this->sendError([
                'message'    => $e->getMessage(),
                'error_type' => $e->getCode()
            ]);
        }
    }

    /**
     * getPublicOptions method will return the list of product and customer priorities
     * @return array
     */
    public function getPublicOptions()
    {
        $products = Product::select(['id', 'title'])->get();

        return [
            'support_products'           => $products,
            'customer_ticket_priorities' => Helper::customerTicketPriorities()
        ];
    }

    /**
     * getCustomFieldsRender method will return the list of custom fields
     * @return array|array[]
     */
    public function getCustomFieldsRender()
    {
        if (!defined('FLUENTSUPPORTPRO')) {
            return [
                'custom_fields_rendered' => []
            ];
        }

        return [
            'custom_fields_rendered' => \FluentSupportPro\App\Services\CustomFieldsService::getRenderedPublicFields()
        ];
    }


    /**
     * logout method will logout the customer
     * @return mixed
     */
    public function logout()
    {
        wp_logout();

        return $this->sendSuccess([
            'message' => __('You have been logged out', 'fluent-support')
        ]);
    }
}
