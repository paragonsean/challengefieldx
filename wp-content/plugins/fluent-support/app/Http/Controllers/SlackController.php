<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Modules\IntegrationSettingsModule;
use FluentSupport\Framework\Request\Request;

/**
 *  SlackController class is responsible for getting and save Slack settings
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */
class SlackController extends Controller
{
    /**
     * getSettings method will generate data for Slack settings
     * @param Request $request
     * @return false
     */
    public function getSettings(Request $request)
    {
        $settingsKey = $request->getSafe('integration_key');

        return IntegrationSettingsModule::getSettings($settingsKey, true);
    }

    /**
     * saveSettings method will save settings data for Slack
     * @param Request $request
     * @return array
     */
    public function saveSettings(Request $request)
    {
        $settingsKey = $request->getSafe('integration_key');
        $settings = wp_unslash($request->getSafe('settings'));

        $settings = IntegrationSettingsModule::saveSettings($settingsKey, $settings);

        if(!$settings || is_wp_error($settings)) {
            $errorMessage = (is_wp_error($settings)) ? $settings->get_error_message() : __('Settings failed to save', 'fluent-support');
            return $this->sendError([
                'message' => __($errorMessage, 'fluent-support')
            ]);
        }

        return [
            'message' => __('Settings has been updated', 'fluent-support'),
            'settings' => $settings
        ];
    }
}
