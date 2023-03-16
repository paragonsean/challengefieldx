<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Models\Attachment;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\EmailNotification\Settings;
use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Includes\FileSystem;
use FluentSupport\Framework\Request\Request;

/**
 * UploaderController class is responsible for uploading file
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */
class UploaderController extends Controller
{
    /**
     * uploadTicketFiles method will upload all the attached file in a ticket
     * @param Request $request
     * @return array[]
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function uploadTicketFiles(Request $request)
    {
        //get settings  from settings table
        $settings = (new Settings())->globalBusinessSettings();
        $maxFileSize = floatval($settings['max_file_size']);
        $mimeHeadings = Helper::getAcceptedMimeHeadings();

        $maxSizeBytes = $maxFileSize * 1024;
        //Validate the file type and size
        $files = $this->validate($this->request->files(), [
            'file' => 'max:' . $maxSizeBytes . '|mimetypes:' . implode(',', Helper::ticketAcceptedFileMiles())
        ], [
            'file.mimetypes' => sprintf(__('Only %s files are allowed.', 'fluent-support'), implode(', ', $mimeHeadings)),
            'file.max'       => sprintf(__('The file can not be more than %.01fMB. Please upload somewhere like dropbox/google drive and paste the link in the response', 'fluent-support'), $maxFileSize)
        ]);


        //get ticket by ticket id
        $ticketId = $request->getSafe('ticket_id', '', 'intval');

        if ($ticketId == 'undefined') {
            $ticketId = NULL;
        }

        //Get customer or agent
        if ($ticketId && $request->getSafe('intended_ticket_hash') && Helper::isPublicSignedTicketEnabled()) {
            $ticket = Ticket::with(['customer'])->findOrFail($ticketId);
            $person = $ticket->customer;
        } else {
            $person = Helper::getCurrentPerson();
        }

        //Check if customer has permission to upload file
        if ($person->person_type == 'customer') {
            $disabledFields = apply_filters('fluent_support/disabled_ticket_fields', []);
            if (in_array('file_upload', $disabledFields)) {
                return $this->sendError([
                    'message' => __('You do not have permission to upload a file', 'fluent-support')
                ]);
            }
        }
        //Move file into the directory
        $uploadedFiles = FileSystem::setSubDir('ticket_' . $ticketId)->put($files);

        $attachments = [];
        //Create records in attachment table
        foreach ($uploadedFiles as $file) {

            $fileData = [
                'ticket_id' => intval($ticketId) ?: NULL,
                'person_id' => intval($person->id),
                'file_type' => $file['type'],
                'file_path' => $file['file_path'],
                'full_url'  => sanitize_url($file['url']),
                'title'     => sanitize_file_name($file['name']),
                'driver'    => 'local',
                'status'    => 'in-active'
            ];

            $attachment = Attachment::create($fileData);
            $attachments[] = $attachment->file_hash;
        }

        return [
            'attachments' => $attachments
        ];

    }
}
