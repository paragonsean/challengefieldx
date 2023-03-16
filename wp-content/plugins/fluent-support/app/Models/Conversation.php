<?php

namespace FluentSupport\App\Models;

use Exception;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Models\Attachment;
use FluentSupport\App\Services\Tickets\ResponseService;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;

class Conversation extends Model
{
    protected $table = 'fs_conversations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'person_id',
        'message_id',
        'conversation_type',
        'content',
        'source',
        'is_important'
    ];

    public static function boot()
    {
        static::creating(function ($model) {
            if(empty($model->content_hash)) {
                $model->content_hash = md5($model->content);
            }
        });
    }

    /**
     * $searchable Columns in table to search
     * @var array
     */
    protected $searchable = [
        'content'
    ];

    /**
     * Local scope to filter subscribers by search/query string
     * @param ModelQueryBuilder $query
     * @param string $search
     * @return ModelQueryBuilder
     */
    public function scopeSearchBy($query, $search)
    {
        if ($search) {
            $fields = $this->searchable;
            $query->where(function ($query) use ($fields, $search) {
                $query->where(array_shift($fields), 'LIKE', "%$search%");

                foreach ($fields as $field) {
                    $query->orWhere($field, 'LIKE', "$search%");
                }
            });
        }

        return $query;
    }

    /**
     * Local scope to filter subscribers by search/query string
     * @param ModelQueryBuilder $query
     * @param string $type
     * @return ModelQueryBuilder
     */
    public function scopeFilterByType($query, $type)
    {
        $query->whereIn('conversation_type', $type);

        return $query;
    }

    /**
     * One2Many: Customer has to many Click Tickets
     * @return Model Collection
     */
    public function ticket()
    {
        $class = __NAMESPACE__ . '\Ticket';

        return $this->belongsTo(
            $class, 'ticket_id', 'id'
        );
    }

    /**
     * One2Many: Customer has to many Click Tickets
     * @return Model Collection
     */
    public function person()
    {
        $class = __NAMESPACE__ . '\Person';

        return $this->belongsTo(
            $class, 'person_id', 'id'
        );
    }

    /**
     * A Conversation belongs to a Customer.
     *
     * @return \FluentSupport\App\Models\Model
     */
    public function customer()
    {
        return $this->person();
    }

    public function attachments()
    {
        $class = __NAMESPACE__ . '\Attachment';
        return $this->hasMany($class, 'conversation_id', 'id');
    }


    /**
     * This `doBulkReplies` will handle bulk replies
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function doBulkReplies ( $data )
    {
        $agent = Helper::getAgentByUserId();
        $tickets = $this->getTicketsToForBulkReply( $data, $agent );

        $responseData = [
            'content'           => wp_kses_post(Arr::get($data, 'content', '')),
            'conversation_type' => 'response',
            'close_ticket'      => Arr::get($data, 'close_ticket'),
        ];

        $attachments = Arr::get($data, 'attachments', []);
        $attachments = $this->getAttachsForBulkReplies( $attachments );

        foreach ( $tickets as $ticket ) {
            if ( $attachments ) {
                $responseData['attachments'] = [];
                foreach ( $attachments as $attachment ) {
                    $responseData['attachments'][] = $this->handleAttachmentOnBulkReplies ( $attachment, $ticket );
                }
            }
            (new ResponseService())->createResponse( $responseData, $agent, $ticket );
        }

        return [
            'message' => __( 'Response has been added to the selected tickets', 'fluent-support' )
        ];
    }

    // This is a supporting method of `doBulkReplies` it will return all selected tickets
    // Also it will check all check permission
    // @param array $data
    // @param object $agent
    private function getTicketsToForBulkReply( $data, $agent )
    {
        $ticketIds = array_filter($data['ticket_ids'], 'absint');

        //Get logged in agent information
        $hasAllPermission = PermissionManager::currentUserCan('fst_manage_other_tickets');
        $query = Ticket::whereIn('id', $ticketIds)->where('status', '!=', 'closed');

        //If the agent does not have permission
        if ( !$hasAllPermission ) {
            $query->where('agent_id', $agent->id); //Filter ticket by agent_id
        }

        $tickets = $query->get();

        //if not ticket found
        if ( $tickets->isEmpty() ) {
            throw new \Exception( 'Sorry no tickets found based on your filter and bulk actions');
        }

        return $tickets;
    }

    // This is a supporting method of `doBulkReplies` it will return it will return attachments
    // if agent add attachments to bulk replies if there is no attachments then it will return false
    // @param array $attachments
    private function getAttachsForBulkReplies ( $attachments )
    {
        if ( $attachments ) {
            $attachs = Attachment::whereNull('ticket_id')
                ->orderBy('id', 'asc')
                ->whereIn('file_hash', $attachments)
                ->get();
            return $attachs;
        }
        return false;
    }

    // This is a supporting method of `doBulkReplies` this method will prepare the uploaded attachments
    // for adding in response
    // @param object $attachment
    // @param object $ticket
    private function handleAttachmentOnBulkReplies ( $attachment, $ticket )
    {
        $attachedFile = $attachment->replicate();
        $attachedFile->ticket_id = $ticket->id;
        $attachedFile->save();
        return $attachedFile->file_hash;
    }


    /**
     * This `deleteResponse` is responsible for deleting response
     * @param int $ticketId
     * @param int $responseId
     * @return array
     * @throws Exception
     */
    public function deleteResponse ( $ticketId, $responseId )
    {
        $ticket = Ticket::findOrFail($ticketId);
        $response = static::findOrFail($responseId);
        $agent = Helper::getAgentByUserId();

        $this->checkUserTaskPermission( $ticket->agent_id, $agent->id, 'delete' );

        static::where('id', $response->id)->delete();

        return [
            'message' => __('Selected response has been deleted', 'fluent-support')
        ];
    }

    public function updateResponse ( $data, $ticketId, $responseId )
    {
        $ticket = Ticket::findOrFail($ticketId);
        $response = static::findOrFail($responseId);
        $agent = Helper::getAgentByUserId();

        $this->checkUserTaskPermission( $ticket->agent_id, $agent->id, 'update' );

        $response->content = wp_unslash(wp_kses_post($data['content']));
        $response->save();

        return [
            'message'  => __('Selected response has been updated', 'fluent-support'),
            'response' => $response
        ];
    }

    // This function will check agent permission to specific task regarding response response
    private function checkUserTaskPermission ( $ticketAgentId, $agentId, $task )
    {
        if ( !PermissionManager::currentUserCan('fst_manage_other_tickets') ) {
            if ( $ticketAgentId != $agentId ) {
                throw new \Exception("Sorry, You do not have permission to {$task} this response");
            }
        } else {
            return true;
        }
    }

}
