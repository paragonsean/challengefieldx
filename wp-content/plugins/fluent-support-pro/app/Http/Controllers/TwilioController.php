<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Modules\IntegrationSettingsModule;
use FluentSupport\App\Services\Helper;
use FluentSupport\App\Services\Tickets\ResponseService;
use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Http\Controllers\Controller;
use FluentSupportPro\App\Services\Integrations\Twilio\TwilioHelper;

class TwilioController extends Controller
{
    public function getSettings(Request $request)
    {
        $settingsKey = $request->get('integration_key');

        return IntegrationSettingsModule::getSettings($settingsKey, true);
    }

    public function saveSettings(Request $request)
    {
        $settingsKey = $request->get('integration_key');
        $settings = wp_unslash($request->get('settings'));

        $settings = IntegrationSettingsModule::saveSettings($settingsKey, $settings);

        if(!$settings || is_wp_error($settings)) {
            $errorMessage = (is_wp_error($settings)) ? $settings->get_error_message() : 'Settings failed to save';
            return $this->sendError([
                'message' => __($errorMessage, 'fluent-support')
            ]);
        }

        return [
            'message' => __('Settings has been updated', 'fluent-support'),
            'settings' => $settings
        ];
    }

    public function handleResponse(Request $request)
    {
        $isEnabled = Helper::getIntegrationOption('twilio_settings', [])['reply_from_whatsapp'];
        if($isEnabled!='yes'){
            return $this->sendError([
                'message' => __('Reply from WhatsApp is disabled', 'fluent-support')
            ]);
        }
        // WhatsApp accept only text/plain type content so simply changing the content type here
        header('Content-Type:text/plain');
        $response = $request->get();

        preg_match_all('/^##\d+##/', $response['Body'], $matchId);
        // If no ticket numbers defined then it will return
        if(empty($matchId[0])){
            return 'No ticket id declared to message body, declare ticket id like this: ##TICKET_ID_NUMBER##';
        }

        $agentNumber = str_ireplace('whatsapp:', '', $response['From']);
        $agent = TwilioHelper::resolveAgent($agentNumber);
        $ticket_id = intval(trim($matchId[0][0], '##'));
        $ticket = Ticket::findOrFail($ticket_id);
        $text = str_ireplace($matchId[0][0], '', $response['Body']);

        if($ticket){
            $data = [
                'person_id'         => intval($agent['user_id']),
                'ticket_id'         => $ticket->id,
                'conversation_type' => 'response',
                'content'           => $text,
                'source'            => 'whatsapp',
                'message_id'        => $response['MessageSid']
            ];

            (new ResponseService)->createResponse($data, $agent, $ticket);

            return 'Response Added Successfully To Ticket No. '. $ticket->id;
        }

        return 'Sorry No Ticket Found With Ticket No. '. $ticket->id;
    }
}
