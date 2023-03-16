<?php

namespace FluentSupport\App\Services\Integrations;

class IntegrationInit
{
    public function init()
    {
        if(defined('FLUENTCRM')) {
            (new \FluentSupport\App\Services\Integrations\FluentCrm\FluentCRMWidgets())->boot();
        }

        if(defined('FLUENTFORM')) {
            new \FluentSupport\App\Services\Integrations\FluentForm\FeedIntegration(wpFluentForm());
        }
    }

}
