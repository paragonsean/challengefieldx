<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Models\MailBox;
use FluentSupport\App\Services\EmailNotification\Settings;
use FluentSupport\App\Services\MailerInbox\MailBoxService;
use FluentSupport\Framework\Request\Request;

class MailBoxController extends Controller
{
    /**
     * index method will return the list of business inbox
     * @param Request $request
     * @return array
     */
    public function index(MailBoxService $mailboxService)
    {
        return $mailboxService->getMailBoxes();
    }

    /**
     * get method will fetch and return information related to business box
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function get( MailBox $mailBox, $id )
    {
        return [
            'mailbox' => $mailBox->getMailBox( $id )
        ];

    }


    /**
     * Save method will create new business box
     * @param Request $request
     * @param MailBox $mailBox
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function save(Request $request, MailBox $mailBox)
    {
        $data = wp_unslash( $request->getSafe('business') );

        $this->validate($data, [
            'name' => 'required',
            'email' => 'required'
        ]);

        return [
            'message' => __('Mailbox has been created successfully', 'fluent-support'),
            'mailbox' => $mailBox->createMailBox( $data )
        ];
    }

    /**
     * This `update` method will update existing information for a business by mailbox id
     * @param Request $request
     * @param MailBox $mailBox
     * @param int $mailBoxId
     * @return array
     * @throws \Exception
     */
    public function update(Request $request, MailBox $mailBox, $mailBoxId)
    {
        try{
            $data = wp_unslash( $request->getSafe('business') );

            $this->validate($data, [
                'name' => 'required',
                'email' => 'required'
            ]);

            return [
                'message' => __( 'Mailbox has been saved', 'fluent-support' ),
                'mailbox' => $mailBox->updateMailBox( $data, $mailBoxId )
            ];
        }catch (\Exception $e){
            return [
                'message' => __( $e->getMessage(), 'fluent-support' ),
            ];
        }
    }

    /**
     * This `delete` method will delete a business from mailbox and replaced with alternative
     * @param Request $request
     * @param MailBoxService $mailBoxService
     * @param int $mailBoxId
     * @throws \Exception
     * @return array
     */
    public function delete(Request $request, MailBoxService $mailBoxService, $mailBoxId)
    {
        try {
            return $mailBoxService->deleteMailBox( $mailBoxId, $request->getSafe('fallback_id', '', 'intval') );
        } catch (\Exception $e) {
            return [
                'message' => __( $e->getMessage(), 'fluent-support' ),
            ];
        }
    }


    /**
     * This `moveTickets` method will move tickets from one mailbox to another
     * @param Request $request
     * @param MailBoxService $mailBoxService
     * @param int $mailBoxId
     * @throws \Exception
     * @return array
     */
    public function moveTickets(Request $request, MailBoxService $mailBoxService, $mailBoxId)
    {
        try {
            $data = $request->only(['ticket_ids', 'new_box_id', 'move_type']);
            return $mailBoxService->moveTickets( $data, $mailBoxId );
        } catch (\Exception $e) {
            return [
                'message' => __( $e->getMessage(), 'fluent-support' ),
            ];
        }
    }

    /**
     * This `getEmailSettings` method will get and return the mailbox email settings
     * @param Request $request
     * @param Settings $settings
     * @param $boxId
     * @return array
     */
    public function getEmailSettings(Request $request, Settings $settings, $boxId)
    {
        $box = MailBox::findOrFail($boxId);
        $emailType = $request->getSafe('email_type');

        return [
            'email_settings' => $settings->getBoxEmailSettings($box, $emailType)
        ];
    }

    /**
     * This `getEmailsSetups` method will return email settings for a business box by box id
     * @param MailBoxService $mailBoxService
     * @param $boxId
     * @return array
     */
    public function getEmailsSetups( MailBoxService $mailBoxService, $boxId )
    {
       return $mailBoxService->getEmailsSetups($boxId);
    }

    /**
     * This `saveEmailSettings` method will save the email settings for a business box using box id
     * @param Request $request
     * @param Settings $settings
     * @param $boxId
     * @return array
     * @throws \FluentSupport\Framework\Validator\ValidationException
     */
    public function saveEmailSettings( Request $request, MailBoxService $mailBoxService, $boxId )
    {
        $data = wp_unslash($request->getSafe('email_settings'));
        $emailType = $request->getSafe('email_type');

        $this->validate($data, [
            'email_subject' => 'required',
            'email_body' => 'required'
        ]);

        return $mailBoxService->saveEmailSettings( $emailType, $boxId, $data );
    }


    /**
     * This `getTickets` method will return the list of tickets for a business box
     * @param Request $request
     * @param MailBox $mailBox
     * @param int $boxId
     * @return array
     */
    public function getTickets(Request $request, MailBoxService $mailBoxService, $boxId)
    {
        return $mailBoxService->getTickets( $request->getSafe('filters'), $boxId );
    }
}
