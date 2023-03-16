<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\Framework\Request\Request;
use FluentSupportPro\App\Services\ProTicketService;
use FluentSupportPro\App\Services\TicketBookmarkService;

class TicketController extends Controller
{
    /**
     * getCustomerTickets method will return customer tickets by customer id
     * @param Request $request
     * @param int $customerId
     * @return array|array[]
     */
    public function getCustomerTickets(Request $request, $customerId)
    {
        $tickets = Ticket::where('customer_id', $customerId)
            ->where('id', '!=', $request->get('exclude_ticket_id'))
            ->latest()
            ->paginate();

        return [
            'tickets' => $tickets
        ];
    }


    /**
     * mergeCustomerTickets will merge tickets into one
     * @param Request $request
     * @param $ticketId //ticket id where the tickets will be merged
     * @return array|array[]
     */
    public function mergeCustomerTickets(Request $request, $ticketId)
    {
        if (!PermissionManager::currentUserCan('fst_merge_tickets')) {
            return $this->sendError([
                'message' => __('You do not have permission to merge tickets', 'fluent-support')
            ]);
        }

        $ticketIDToMerge = $request->get('ticket_to_merge');
        return (new ProTicketService())->mergeCustomerTickets($ticketIDToMerge, $ticketId);
    }

    public function syncTicketWatchers(Request $request, $ticketId)
    {
        $watchers = $request->get('watchers', []);
        $agentIds = [];
        foreach($watchers as $watcher){
            is_array($watcher) ? $agentIds[] = $watcher['id'] : $agentIds[] = $watcher;
        }

        return (new ProTicketService())->syncTicketWatchers($agentIds, $ticketId);
    }

    public function addTicketWatchers(Request $request, TicketBookmarkService $bookmarkService, $ticketId)
    {
        $watchers = $request->get('watchers', []);

        if( ! $watchers ){
            return $this->sendError([
                'message' => __('Watchers is required', 'fluent-support')
            ]);
        }

        $bookmarkService->addBookmarks( $watchers, $ticketId );

        return [
            'message' => __('Watchers has been added to this ticket', 'fluent-support'),
        ];
    }

    public function splitToNewTicket ( Request $request, ProTicketService $proTicketService, $actualTicketId )
    {
        $newTicketData = $request->getSafe('split_ticket', [], 'wp_kses_post');

        try {
            return $proTicketService->splitToNewTicket( $actualTicketId, $newTicketData );
        } catch (\Exception $e) {
            return $this->sendError([
                'message' =>__( $e->getMessage(), 'fluent-support')
            ]);
        }
    }
}
