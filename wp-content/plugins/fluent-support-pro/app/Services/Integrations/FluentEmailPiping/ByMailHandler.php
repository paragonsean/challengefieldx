<?php

namespace FluentSupportPro\App\Services\Integrations\FluentEmailPiping;

use FluentSupport\App\Models\Attachment;
use FluentSupport\App\Models\Customer;
use FluentSupport\App\Models\MailBox;
use FluentSupport\App\Models\Conversation;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\Parser\Parsedown;
use FluentSupport\Framework\Support\Arr;

class ByMailHandler
{
    public static function handleEmailData($data, $mailBox = false, $source = 'email')
    {
        $data['subject'] = self::getActualSubject(Arr::get($data, 'subject', 'Subject not defined'));

        $onBehalf = Arr::get($data, 'sender');
        $fullName = sanitize_text_field(Arr::get($onBehalf, 'name', ''));
        unset($onBehalf['name']);

        $nameArray = explode(' ', $fullName);

        if (count($nameArray) >= 2) {
            $onBehalf['last_name'] = array_pop($nameArray);
            $onBehalf['first_name'] = implode(' ', $nameArray);
        } else if ($fullName) {
            $onBehalf['first_name'] = $fullName;
        }

        $customer = Customer::maybeCreateCustomer($onBehalf);

        if (!$customer || $customer->status == 'inactive') {
            return [
                'type'    => 'error',
                'message' => 'Customer is inactive so no ticket can be created'
            ];
        }

        $existingTicket = false;

        if (!$customer->wasRecentlyCreated) {
            $existingTicket = self::guessTicket($customer, $data);
        }

        $responseOrTicketData = [
            'title'      => sanitize_text_field($data['subject']),
            'message_id' => \FluentSupportPro\App\Services\ProHelper::sanitizeMessageId($data['message_id']),
            'content'    => wp_specialchars_decode(wp_unslash(wp_kses_post($data['content'])))
        ];

        if(empty($responseOrTicketData['message_id']) && $messageId = \FluentSupportPro\App\Services\ProHelper::generateMessageID($customer->email)) {
            $responseOrTicketData['message_id'] = $messageId;
        }

        if (!empty($data['isMarkDown'])) {
            if ($parsedContent = (new Parsedown())->text(wp_specialchars_decode(wp_unslash(wp_kses_post($data['content']))))) {
                $responseOrTicketData['content'] = $parsedContent;
            }
        }

        if ($existingTicket && !empty($data['message_id']) && empty($existingTicket->message_id)) {
            $existingTicket->message_id = $data['message_id'];
            $existingTicket->save();
        }

        if (!$existingTicket) {
            $responseOrTicketData['customer_id'] = $customer->id;
            $responseOrTicketData['source'] = $source;
            $responseOrTicketData['client_priority'] = sanitize_text_field(Arr::get($data, 'priority', 'normal'));
            $responseOrTicketData['priority'] = sanitize_text_field(Arr::get($data, 'priority', 'normal'));

            if (!$mailBox) {
                $mailBox = MailBox::where('is_default', 'yes')->orderBy('id', 'ASC')->first();
            }

            if ($mailBox) {
                $responseOrTicketData['mailbox_id'] = $mailBox->id;
            }

            $responseOrTicketData['source'] = $source;

            $ticketData = apply_filters('fluent_support/create_ticket_data', $responseOrTicketData, $customer);
            // Check if the ticket is already added or not

            if ($mailBox) {
                $contentHash = md5($ticketData['content']);
                $maybeDuplicate = Ticket::where('content_hash', $contentHash)
                    ->where('customer_id', $customer->id)
                    ->where('status', '!=', 'closed')
                    ->where('mailbox_id', $mailBox->id)
                    ->first();

                if ($maybeDuplicate) {
                    return false;
                }
            }

            $canCreateTicket = apply_filters('fluent_support/can_customer_create_ticket', true, $customer, $data);

            if (!$canCreateTicket || is_wp_error($canCreateTicket)) {
                return [
                    'type'    => 'error',
                    'message' => (is_wp_error($canCreateTicket)) ? $canCreateTicket->get_error_message() : __('Sorry you can not create ticket', 'fluent-support')
                ];
            }

            do_action('fluent_support/before_ticket_create', $responseOrTicketData, $customer);

            /**
             * This hook will action before ticket create via email
             * @param array $responseOrTicketData
             * @param object $customer
             */
            do_action('fluent_support/before_ticket_create_from_email', $responseOrTicketData, $customer);

            $createdTicket = Ticket::create($ticketData);

            if ($createdTicket && isset($data['custom_fields']) && !empty($data['custom_fields'])) {
                $customData = wp_unslash($data['custom_fields']);
                $createdTicket->syncCustomFields($customData);
            }

            self::handleAttachments(Arr::get($data, 'attachments', []), $createdTicket, $customer);

            do_action('fluent_support/ticket_created', $createdTicket, $customer);

            /**
             * This hook will action after ticket create via email
             * @param object $createdTicket
             * @param object $customer
             */
            do_action('fluent_support/after_ticket_create_from_email', $createdTicket, $customer);

            return [
                'type'      => 'new_ticket',
                'ticket_id' => $createdTicket->id,
                'ticket'    => $createdTicket
            ];
        }

        if (!empty($existingTicket->extra_content)) {
            $responseOrTicketData['content'] = $existingTicket->extra_content . $responseOrTicketData['content'];
            unset($existingTicket->extra_content);
        }

        // we have to create a response
        unset($responseOrTicketData['title']);
        $responseOrTicketData['person_id'] = $customer->id;
        $responseOrTicketData['ticket_id'] = $existingTicket->id;
        $responseOrTicketData['conversation_type'] = 'response';
        $responseOrTicketData['source'] = $source;

        $canCreateResponse = apply_filters('fluent_support/can_customer_create_response', true, $customer, $existingTicket, $data);

        if (!$canCreateResponse || is_wp_error($canCreateResponse)) {
            return [
                'type'    => 'error',
                'message' => (is_wp_error($canCreateResponse)) ? $canCreateResponse->get_error_message() : __('Sorry you can not create response', 'fluent-support')
            ];
        }

        if ($existingTicket->last_agent_response && strtotime($existingTicket->last_agent_response) > strtotime($existingTicket->last_customer_response)) {
            $existingTicket->waiting_since = current_time('mysql');
        }

        $createdResponse = Conversation::create($responseOrTicketData);

        if ($existingTicket->status != 'active') {
            $existingTicket->status = 'active';
        }

        $existingTicket->last_customer_response = current_time('mysql');
        $existingTicket->response_count += 1;

        if (!empty($data['message_id']) && !$existingTicket->message_id) {
            $existingTicket->message_id = sanitize_text_field($data['message_id']);
        }

        $existingTicket->save();

        self::handleAttachments(Arr::get($data, 'attachments', []), $existingTicket, $customer, $createdResponse);

        do_action('fluent_support/response_added_by_customer', $createdResponse, $existingTicket, $customer);

        return [
            'type'        => 'new_response',
            'ticket_id'   => $existingTicket->id,
            'response_id' => $createdResponse->id,
            'response'    => $createdResponse,
            'customer'    => $customer
        ];
    }

    private static function getActualSubject($string)
    {

        if (!strpos($string, ':')) {
            return $string;
        }

        $prefix = 'Re: ';
        if (substr($string, 0, strlen($prefix)) == $prefix) {
            $string = substr($string, strlen($prefix));
        }

        $prefix = 'RE: ';
        if (substr($string, 0, strlen($prefix)) == $prefix) {
            $string = substr($string, strlen($prefix));
        }

        $prefix = 'Fwd: ';
        if (substr($string, 0, strlen($prefix)) == $prefix) {
            $string = substr($string, strlen($prefix));
        }

        $prefix = 'Request Received: ';
        if (substr($string, 0, strlen($prefix)) == $prefix) {
            $string = substr($string, strlen($prefix));
        }

        return $string;
    }

    protected static function guessTicket($customer, $data)
    {
        $subject = $data['subject'];

        // check if the customer has any ticket or not
        if (!Ticket::where('customer_id', $customer->id)->first()) {
            return false;
        }


        if (!empty($data['message_id'])) {
            $ticket = Ticket::where('customer_id', $customer->id)
                ->where('message_id', $data['message_id'])
                ->first();
            if ($ticket) {
                return $ticket;
            }
        }

        preg_match_all('/#([0-9]*)/', $subject, $matches);

        $ticketId = false;
        if (count($matches[1])) {
            $ticketId = array_pop($matches[1]);
        }

        if ($ticketId) {
            $existingTicket = Ticket::where('customer_id', $customer->id)
                ->where('id', $ticketId)
                ->first();

            if ($existingTicket) {
                return $existingTicket;
            }

            $subject = str_replace('#' . $ticketId, '', $subject);
        }

        $existingTicket = Ticket::where('customer_id', $customer->id)
            ->where('title', 'like', '%%' . $subject . '%%')
            ->orderBy('ID', 'DESC')
            ->first();

        if ($existingTicket) {
            return $existingTicket;
        }

        if (apply_filters('fluent_support/ticket_partial_match', true)) {
            // Let's try to guess ticket from ticket subject part
            $subjectParts = explode(' ', $subject);
            $subjectParts = array_filter($subjectParts);

            $partCounts = count($subjectParts);
            if ($partCounts <= 5) {
                return $existingTicket;
            }

            $middleItem = intval($partCounts / 2);
            $subjectPart = $subjectParts[$middleItem - 1] . ' ' . $subjectParts[$middleItem] . ' ' . $subjectParts[$middleItem + 1];

            $existingTicket = Ticket::where('customer_id', $customer->id)
                ->where('title', 'like', '%%' . $subjectPart . '%%')
                ->orderBy('ID', 'DESC')
                ->first();
        }

        return $existingTicket;
    }

    private static function handleAttachments($attachments, $ticket, $customer, $convo = false)
    {

        if (!$attachments) {
            return false;
        }

        $acceptedMimes = \FluentSupport\App\Services\Helper::ticketAcceptedFileMiles();

        // add_filter('upload_dir', array(self::class, 'overWriteUpDir'));

        preg_match_all('/\[image: (.*?)]/', $ticket->content, $inlineImages);
        $inlineImageMapper = array_combine($inlineImages[1], $inlineImages[0]);
        $inlineImages = false;
        $modelThatNeedsInlineImages = $convo ? $convo : $ticket;

        foreach ($attachments as $attachment) {

            $attachmentData = [
                'ticket_id' => $ticket->id,
                'person_id' => $customer->id,
            ];

            if ($convo) {
                // print_r($convo->id.' ');
                $attachmentData['conversation_id'] = $convo->id;
            }

            // download and save the file from attachment URL

            $response = wp_remote_request($attachment['url'], [
                'sslverify' => false,
                'method'    => 'GET'
            ]);

            if (is_wp_error($response)) {
                continue;
            }

            $contentType = wp_remote_retrieve_header($response, 'content-type');

            if (!in_array($contentType, $acceptedMimes)) {
                continue;
            }

            if (wp_remote_retrieve_response_code($response) >= 300) {
                continue;
            }

            $responseBody = wp_remote_retrieve_body($response);

            $upload = wp_upload_bits($attachment['filename'], null, $responseBody);

            $attachmentData['file_type'] = $contentType;
            $attachmentData['file_path'] = $upload['file'];
            $attachmentData['full_url'] = $upload['url'];
            $attachmentData['title'] = $attachment['filename'];

            if ($attachment['contentDisposition'] === 'inline') {
                $attachmentData['status'] = 'inline';

                if (array_key_exists($attachment['filename'], $inlineImageMapper)) {
                    $inlineImages = true;

                    $modelThatNeedsInlineImages->content = str_replace(
                        $inlineImageMapper[$attachment['filename']],
                        "<img src='{$attachmentData['full_url']}' alt='{$attachment['filename']}'>",
                        $modelThatNeedsInlineImages->content
                    );
                }
            }

            Attachment::create($attachmentData);
        }

        if ($inlineImages) {
            $modelThatNeedsInlineImages->save();
        }

        //  remove_filter('upload_dir', array(self::class, 'overWriteUpDir'));

        return true;
    }

    public static function overWriteUpDir($upload)
    {
        $uploadDir = wp_upload_dir();

        $upload['path'] = $uploadDir['basedir'] . '/' . FLUENT_SUPPORT_UPLOAD_DIR . '/email_attachments';
        $upload['url'] = $uploadDir['baseurl'] . '/' . FLUENT_SUPPORT_UPLOAD_DIR . '/email_attachments';
        $upload['subdir'] = '/email_attachments';
        return $upload;
    }

    public static function isCustomPipeSupported()
    {
        if (defined('FLUENTSUPPORT_ENABLE_CUSTOM_PIPE') && FLUENTSUPPORT_ENABLE_CUSTOM_PIPE) {
            return true;
        }

        return apply_filters('fluent_support/enable_custom_piping', false);
    }
}
