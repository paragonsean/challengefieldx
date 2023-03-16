<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\App;
use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Meta;
use FluentSupport\Framework\Request\Request;

class IncomingWebhookController extends Controller
{
    public function index(Request $request)
    {
        $webhook = Meta::where('object_type', 'fs_incoming_webhook')->select(['value'])->get()->first();
        if(!$webhook){
            $webhook = $this->createWebhook()['value'];
        }

        return [
            'webhook' => $webhook->value
        ];
    }

    private function createWebhook()
    {
        $token = wp_generate_uuid4();
        $app = App::getInstance();
        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');

        return Meta::create([
            'object_type' => 'fs_incoming_webhook',
            'key' => $token,
            'value' => rest_url($ns . '/' . $v . '/public/incoming_webhook/' . $token)
        ]);
    }

    public function updateWebhook()
    {
        $app = App::getInstance();
        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');
        $updateMeta = Meta::where('object_type', 'fs_incoming_webhook')->update([
                'key' => $token = wp_generate_uuid4(),
                'value' => rest_url($ns . '/' . $v . '/public/incoming_webhook/' . $token)
            ]);
        return [
            'updatedData' => $updateMeta,
            'message' => __('Webhook regenerated successfully', 'fluent-support-pro')
        ];
    }

}