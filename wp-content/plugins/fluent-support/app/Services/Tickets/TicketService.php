<?php

namespace FluentSupport\App\Services\Tickets;

use FluentSupport\App\Models\Conversation;
use FluentSupport\App\Services\TicketHelper;
use FluentSupport\App\Services\TicketQueryService;
use FluentSupport\Framework\Support\Arr;

class TicketService
{
    /**
     * this function will set the ticket status as closed
     * @param $ticket
     * @param $person
     * @param string $internalNote
     * @param bool $silently
     * @return mixed
     */

    public function close($ticket, $person, $internalNote = '', $silently = 'no')

    {
        if ($ticket->status != 'closed') {
            $ticket->status = 'closed';
            $ticket->resolved_at = current_time('mysql');
            $ticket->closed_by = $person->id;
            $ticket->total_close_time = current_time('timestamp') - strtotime($ticket->created_at);
            $ticket->save();

            if ('no' == $silently) {
                do_action('fluent_support/ticket_closed', $ticket, $person);
                do_action('fluent_support/ticket_closed_by_' . $person->person_type, $ticket, $person);
            }

            if (!$internalNote) {
                $internalNote = __('Ticket has been closed', 'fluent-support');
            }

            //Keep track in conversation
            Conversation::create([
                'ticket_id'         => $ticket->id,
                'person_id'         => $person->id,
                'conversation_type' => 'internal_info',
                'content'           => $internalNote
            ]);
        }

        return $ticket;
    }

    public function reopen($ticket, $person)
    {
        if ($ticket->status == 'closed') {
            $ticket->status = 'active';
            $ticket->waiting_since = current_time('mysql');
            $ticket->save();

            /*
             * Action on ticket reopen
             *
             * @since v1.0.0
             * @param object $ticket
             * @param object $person
             */
            do_action('fluent_support/ticket_reopen', $ticket, $person);
            do_action('fluent_support/ticket_reopen_by_' . $person->person_type, $ticket, $person);
            Conversation::create([
                'ticket_id'         => $ticket->id,
                'person_id'         => $person->id,
                'conversation_type' => 'internal_info',
                'content'           => __('Ticket has been reopened', 'fluent-support')
            ]);
        }

        return $person;
    }

    public function onAgentChange($ticket, $person)
    {
        do_action('fluent_support/ticket_agent_change', $ticket, $person);
        Conversation::create([
            'ticket_id'         => $ticket->id,
            'person_id'         => $person->id,
            'conversation_type' => 'internal_info',
            'content'           => $ticket->agent->user_id !== $person->user_id ?
                __($person->full_name . ' assign ' . $ticket->agent->full_name . ' in this ticket', 'fluent-support') :
                __($person->full_name . ' assign this ticket to self', 'fluent-support')
        ]);

        return $person;
    }

    /**
     * This `getTickets` method will return the all tickets
     * @param array $data This is the data that will be used to filter the tickets
     * @param string $filterType This is the type of filter that will be used to filter the tickets
     * @return array $tickets
     */
    public function getTickets($data, $filterType)
    {
        $queryArgs = $this->prepareQuery($data, $filterType);
        $tickets = $this->getTicketsByQuery($queryArgs);

        foreach ($tickets as $ticket) {
            if (Arr::get($data, 'per_page') < 15) {
                if ($ticket->status != 'closed') {
                    $ticket->live_activity = TicketHelper::getActivity($ticket->id);
                } else {
                    $ticket->live_activity = [];
                }
            }
        }

        return [
            'tickets' => $tickets
        ];
    }

    /**
     * This is a supporting method for getTickets method
     * it prepares the query arguments for tickets filtering
     * @param array $data
     * @param string $filterType
     * @return array
     */
    private function prepareQuery($data, $filterType)
    {
        $queryArgs = [
            'with'        => [],
            'filter_type' => $filterType,
            'sort_by'     => Arr::get($data, 'order_by', 'id'),
            'sort_type'   => Arr::get($data,'order_type', 'DESC'),
        ];

        if ($filterType == 'advanced') {
            //Get the selected query params for advanced filter
            $queryArgs['filters_groups_raw'] = json_decode(Arr::get($data, 'advanced_filters', '[]'), true);
        } else {
            //Selected filter type is simple
            $queryArgs['simple_filters'] = Arr::get($data, 'filters', []);
            $queryArgs['search'] = trim(Arr::get($data, 'search', ''));
            if ($customerId = Arr::get($data, 'customer_id')) {
                $queryArgs['customer_id'] = $customerId;
            }
        }

        return $queryArgs;
    }

    // This is a supporting method for getTickets method
    // it returns the tickets by query arguments
    private function getTicketsByQuery($queryArgs)
    {
        $ticketsModel = (new TicketQueryService($queryArgs))->getModel();

        $ticketsModel = $ticketsModel->with([
            'customer'         => function ($query) {
                $query->select(['first_name', 'last_name', 'email', 'id', 'avatar']);
            }, 'agent'         => function ($query) {
                $query->select(['first_name', 'last_name', 'id']);
            },
            'mailbox',
            'product',
            'tags',
            'preview_response' => function ($query) {
                $query->latest('id');
            }
        ]);


        // apply filters by access level
        do_action_ref_array('fluent_support/tickets_query_by_permission_ref', [&$ticketsModel, false]);

        return $ticketsModel->paginate();
    }
}
