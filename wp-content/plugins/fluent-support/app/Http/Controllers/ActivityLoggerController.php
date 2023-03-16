<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Models\Activity;
use FluentSupport\Framework\Request\Request;

/**
 *  ActivityLoggerController class for REST API
 * This class is responsible for getting data for all request related to activity and activity settings
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */
class ActivityLoggerController extends Controller
{
    /**
     * getActivities method will get information regarding all activity with users(agent/customer) and activity settings
     * @return array
     */

    public function getActivities (Request $request, Activity $activity)
    {
        try {
            return $activity->getActivities( $request->getSafe() );
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => __($e->getMessage(), 'fluent-support')
            ]);
        }
    }

    /**
     * updateSettings method will update existing activity settings
     * @return array
     */
    public function updateSettings (Request $request, Activity $activity)
    {
        try {
            return $activity->updateSettings($request->getSafe('activity_settings', []));
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => __($e->getMessage(), 'fluent-support')
            ]);
        }
    }

    /**
     * getSettings method will get the list of activity settings and return
     * @return array
     */
    public function getSettings(Activity $activity)
    {
        try {
            return $activity->getSettings();
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => __($e->getMessage(), 'fluent-support')
            ]);
        }
    }
}
