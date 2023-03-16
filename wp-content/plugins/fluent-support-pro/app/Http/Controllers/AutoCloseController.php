<?php

namespace FluentSupportPro\App\Http\Controllers;


use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Http\Controllers\Controller;
use FluentSupportPro\App\Services\AutoCloseService;

class AutoCloseController extends Controller
{
    public function getSettings(Request $request)
    {
        $settings = AutoCloseService::getSettings();

        return [
            'settings' => $settings,
            'fields'   => AutoCloseService::getSettingsFields()
        ];
    }

    public function saveSettings(Request $request)
    {
        $settings = $request->getSafe('settings', []);
        $settings['close_response_body'] = wp_kses_post($settings['close_response_body']);

        if ($settings['enabled'] == 'yes') {
            // validate
            $this->validate($settings, [
                'inactive_days'               => 'required|min:1',
                'exclude_if_customer_waiting' => 'required',
                'close_silently'              => 'required',
                'add_close_response'          => 'required'
            ]);

            if (!empty($settings['include_tags']) && !empty($settings['exclude_tags'])) {
                if (array_intersect($settings['include_tags'], $settings['exclude_tags'])) {
                    return $this->sendError([
                        'message' => 'Please use different tags in include and exclude tags field'
                    ]);
                }
            }

            if ($settings['exclude_if_customer_waiting'] == 'no' && !$settings['closed_by_agent']) {
                return $this->sendError([
                    'message' => 'Default fallback agent is required'
                ]);
            }

            if ($settings['add_close_response'] == 'yes' && empty($settings['close_response_body'])) {
                return $this->sendError([
                    'message' => 'Response body is required'
                ]);
            }
        }

        AutoCloseService::saveSettings($settings);

        return [
            'message'  => 'Settings has been updated',
            'settings' => AutoCloseService::getSettingsFields()
        ];

    }

}
