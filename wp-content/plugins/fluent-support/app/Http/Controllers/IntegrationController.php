<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Modules\IntegrationSettingsModule;
use FluentSupport\Framework\Request\Request;

class IntegrationController extends Controller
{
    /**
     * getSettings method will fetch the list of integration settings by integration key
     * @param Request $request
     * @return false
     */
    public function getSettings(Request $request)
    {
        $settingsKey = $request->getSafe('integration_key');

        return IntegrationSettingsModule::getSettings($settingsKey, true);
    }

    /**
     * saveSettings method will save the integration settings by integration key
     * @param Request $request
     * @return array
     */
    public function saveSettings(Request $request)
    {
        $settingsKey = $request->getSafe('integration_key');
        $settings = wp_unslash($request->getSafe('settings'));

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
}
