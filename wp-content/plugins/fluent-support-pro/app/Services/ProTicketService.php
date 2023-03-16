<?php

namespace FluentSupportPro\App\Services;

use FluentSupport\App\Models\Attachment;
use FluentSupport\App\Models\Conversation;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Tickets\TicketService;
use FluentSupport\Framework\Support\Arr;

class ProTicketService extends TicketService
{
    public function addNoteOnMerge($ticket, $mergedTicket)
    {
        do_action('fluent_support/ticket_merge', $ticket, $mergedTicket);
        $message = sprintf(__('Ticket #%s has been merged with this ticket at %s', 'fluent-support'),  $mergedTicket->id, current_time('mysql'));
        Conversation::create([
            'ticket_id'         => $ticket->id,
            'person_id'         => Helper::getCurrentAgent()->id,
            'conversation_type' => 'ticket_merge_activity',
            'content'           => $message
        ]);

        return $ticket;
    }

    public function mergeCustomerTickets($ticketIDToMerge, $currentTicketId)
    {
        $parentTicket = Ticket::findOrFail($currentTicketId);
        $ticket = Ticket::findOrFail($ticketIDToMerge);

        $conversation =  Conversation::create(
            [
                'ticket_id'  => $currentTicketId,
                'content'    => $ticket->content,
                'source'     => $ticket->source,
                'person_id'  => $parentTicket->customer_id
            ]
        );
        $conversation->created_at = $ticket->created_at;
        $conversation->save();


        Conversation::where('ticket_id', $ticketIDToMerge)->update([ 'ticket_id' => $currentTicketId ]);

        Attachment::where('ticket_id', $ticketIDToMerge)->update([ 'ticket_id' => $currentTicketId ]);

        $this->addNoteOnMerge($parentTicket, $ticket);
        $ticket->deleteTicket();

        return [
            'message' => __('Tickets has been merged', 'fluent-support')
        ];
    }

    public function syncTicketWatchers( $watchers, $ticketId )
    {
        $bookmarkService = new TicketBookmarkService;

        $existingWatchers = $bookmarkService->getExistingBookmarks( $ticketId );

        $maybeAddWatchers = $bookmarkService->removeOrAddToBookmarksList( $existingWatchers, $watchers, $ticketId ) ?? [] ;

        if ( $maybeAddWatchers ) {
            $bookmarkService->addBookmarks( $maybeAddWatchers, $ticketId );
        }

        return [
            'message' => __('Watchers has been updated', 'fluent-support')
        ];
    }

    public function splitToNewTicket ( $actualTicketId, $data )
    {
        $conversationId = Arr::get($data, 'conversation_id');

        unset($data['conversation_id']);

        $this->checkPermission('fst_split_ticket', 'You do not have permission to split tickets');

        do_action('fluent_support/before_ticket_split', $actualTicketId, $conversationId, $data);

        $newTicket = $this->handleSplitTicket( $conversationId, $data );

        $agent = Helper::getCurrentAgent();

        $this->createInternalInfoForSplit( $newTicket->id, $actualTicketId, $conversationId, $agent->id );

        do_action('fluent_support/after_ticket_split', $actualTicketId, $newTicket);

        return [
            'message' => __('Ticket has been split to new ticket', 'fluent-support'),
            'new_ticket_id' => $newTicket->id
        ];
    }

    private function createInternalInfoForSplit ( $newTicketId, $oldTicketId, $conversationId, $agentId )
    {
        Conversation::create(
            [
                'conversation_type' => 'ticket_split_activity',
                'ticket_id' => $newTicketId,
                'person_id' => $agentId,
                'content' => sprintf(__('Ticket #%s has been splited from #%s at %s', 'fluent-support'),  $newTicketId, $oldTicketId, current_time('mysql'))
            ]
        );

        Conversation::create(
            [
                'conversation_type' => 'ticket_split_activity',
                'ticket_id' => $oldTicketId,
                'person_id' => $agentId,
                'content' => sprintf(__('Conversation #%s has been splited as a new ticket at #%s at %s', 'fluent-support'),  $conversationId, $newTicketId, current_time('mysql'))
            ]
        );
    }

    private function handleSplitTicket ( $conversationId, $data )
    {
        $newTicket = (new Ticket())->createTicket( $data );
        Conversation::where('id', $conversationId)->delete();
        Attachment::where('conversation_id', $conversationId)
            ->update([
                'ticket_id' => $newTicket->id,
                'conversation_id' => NULL
            ]);

        return $newTicket;
    }

    private function checkPermission ( $permission, $message )
    {
        if ( !PermissionManager::currentUserCan($permission) ) {
            throw new \Exception( $message );
        }
    }
}
