<?php

namespace FluentSupport\App\Services\Integrations\FluentForm;

use FluentForm\App\Services\Integrations\IntegrationManager;
use FluentForm\Framework\Foundation\Application;
use FluentSupport\App\App;
use FluentSupport\App\Models\Attachment;
use FluentSupport\App\Models\Customer;
use FluentSupport\App\Models\MailBox;
use FluentSupport\App\Models\Product;
use FluentSupport\App\Models\Ticket;
use FluentSupport\Framework\Support\Arr;

class FeedIntegration extends IntegrationManager
{

    public $hasGlobalMenu = false;

    public $disableGlobalSettings = 'yes';

    public function __construct(Application $app)
    {
        parent::__construct(
            $app,
            'FluentSupport',
            'fluent_support',
            '_fluentsupport_settings',
            'fluentform_fluentsupport_feed',
            16
        );

        $app = App::getInstance();

        $assets = $app['url.assets'];

        $this->logo = $assets.'/images/fluent-support-color-logo.png';

        $this->description = __('Create Support Ticket From Your Form Submission in FluentSupport', 'fluentform');

        $this->registerAdminHooks();

        add_filter('fluentform_notifying_async_fluent_support', '__return_false');

    }

    public function pushIntegration($integrations, $formId)
    {
        $integrations[$this->integrationKey] = [
            'title'                 => $this->title . ' Integration',
            'logo'                  => $this->logo,
            'is_active'             => $this->isConfigured(),
            'configure_title'       => __('Configuration required!', 'fluentform'),
            'global_configure_url'  => '#',
            'configure_message'     => __('FluentSupport is not configured yet! Please configure your FluentSupport api first', 'fluentform'),
            'configure_button_text' => __('Set FluentSupport', 'fluentform')
        ];
        return $integrations;
    }

    public function getIntegrationDefaults($settings, $formId)
    {
        return [
            'name'                      => '',
            'first_name'                => '',
            'last_name'                 => '',
            'email'                     => '',
            'list_id'                   => '', // this is the business ID
            'ticket_title'              => '',
            'ticket_body'               => '',
            'client_priority'           => '',
            'attachments'               => '',
            'product_id'                => '',
            'product_id_selection_type' => 'simple',
            'product_routers'           => [],
            'customer_other_fields'           => [
                [
                    'item_value' => '',
                    'label'      => ''
                ]
            ],
            'conditionals'              => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all'
            ],
            'enabled'                   => true
        ];
    }

    public function getSettingsFields($settings, $formId)
    {
        $fieldOptions = [];
        foreach (Customer::mappables() as $key => $column) {
            $fieldOptions[$key] = $column;
        }
        return [
            'fields'              => [
                [
                    'key'         => 'name',
                    'label'       => __('Feed Name', 'fluentform'),
                    'required'    => true,
                    'placeholder' => __('Your Feed Name', 'fluentform'),
                    'component'   => 'text'
                ],
                [
                    'key'         => 'list_id',
                    'label'       => __('Business', 'fluentform'),
                    'placeholder' => __('Select Business', 'fluentform'),
                    'tips'        => __('Select the Business you would like to add your Support Ticket to.', 'fluentform'),
                    'component'   => 'select',
                    'required'    => true,
                    'options'     => $this->geMailBoxes(),
                ],
                [
                    'key'                => 'product_id',
                    'require_list'       => false,
                    'label'              => __('Product', 'fluentform'),
                    'placeholder'        => __('Select Support Product', 'fluentform'),
                    'component'          => 'selection_routing',
                    'simple_component'   => 'select',
                    'routing_input_type' => 'select',
                    'routing_key'        => 'product_id_selection_type',
                    'settings_key'       => 'product_routers',
                    'is_multiple'        => false,
                    'labels'             => [
                        'choice_label'      => __('Enable Dynamic Product Selection', 'fluentform'),
                        'input_label'       => '',
                        'input_placeholder' => __('Set Product', 'fluentform')
                    ],
                    'options'            => $this->getProducts()
                ],
                [
                    'key'          => 'ticket_title',
                    'require_list' => false,
                    'label'        => __('Ticket Title', 'fluentform'),
                    'placeholder'  => __('Ticket Title', 'fluentform'),
                    'component'    => 'value_text'
                ],
                [
                    'key'          => 'ticket_content',
                    'require_list' => false,
                    'label'        => __('Ticket Content', 'fluentform'),
                    'placeholder'  => __('Ticket Content', 'fluentform'),
                    'component'    => 'value_textarea'
                ],
                [
                    'key'            => 'ticket_attachments',
                    'require_list'   => false,
                    'label'          => __('Ticket Attachments', 'fluentform'),
                    'Placeholder'    => __('Ticket Attachments', 'fluentform'),
                    'tips'           => __('Please input your file upload or image upload field shortcode here', 'fluentform'),
                    'component'      => 'value_text'
                ],
                [
                    'key'            => 'client_priority',
                    'require_list'   => false,
                    'label'          => __('Ticket Priority', 'fluentform'),
                    'Placeholder'    => __('Ticket Priority', 'fluentform'),
                    'tips'           => __('Make sure form field values match with Fluent Support priorities', 'fluentform'),
                    'component'      => 'value_text'
                ],
                $this->getCustomField(), //Getting Fluent Support Custom Field To Map
                [
                    'component' => 'html_info',
                    'html_info' => __('<h4>Please provide the ticket provider info. If user is logged in then it will use that info. For Public users you can set your customer info</h4>', 'fluentform')
                ],
                [
                    'key'                => 'CustomerFields',
                    'require_list'       => false,
                    'label'              => __('Customer Data', 'fluentform'),
                    'tips'               => __('Please Map Your Customer Data for this form. If your customer already logged in you can leave this', 'fluentform'),
                    'component'          => 'map_fields',
                    'field_label_remote' => __('Support Customer Field', 'fluentform'),
                    'field_label_local'  => __('Form Field', 'fluentform'),
                    'primary_fileds'     => [
                        [
                            'key'           => 'email',
                            'label'         => __('Email Address', 'fluentform'),
                            'required'      => true,
                            'input_options' => 'emails'
                        ],
                        [
                            'key'   => 'first_name',
                            'label' => __('First Name', 'fluentform')
                        ],
                        [
                            'key'   => 'last_name',
                            'label' => __('Last Name', 'fluentform')
                        ]
                    ]
                ],
                [
                    'key'                => 'customer_other_fields',
                    'require_list'       => false,
                    'label'              => __('Customer Other Fields', 'fluentform'),
                    'tips'               => 'Select which Fluent Forms fields pair with their<br /> respective Fluent Support fields.',
                    'field_label_remote' => __('Fluent Support Field', 'fluentform'),
                    'field_label_local'  => __('Form Field', 'fluentform'),
                    'component'          => 'dropdown_many_fields',
                    'options'            => $fieldOptions
                ],
            ],
            'button_require_list' => false,
            'integration_title'   => $this->title
        ];
    }

    private function geMailBoxes()
    {
        $items = MailBox::all();
        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[strval($item->id)] = $item->name;
        }
        return $formattedItems;
    }

    private function getProducts()
    {
        $products = Product::all();
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[strval($product->id)] = $product->title;
        }
        return $formattedProducts;
    }

    private function getCustomField()
    {
        $customFields = apply_filters('fluent_support/ticket_custom_fields', []);
        $fieldToExclude = apply_filters('fluent_support/custom_field_types', []);

        if(empty($customFields)){
            return[
                'component' => 'html_info',
                'html_info' => __('', 'fluentform')
            ];
        }

        $fields = [];
        foreach ($customFields as $customFieldKey=>$customFieldValue) {

            if(in_array($customFieldValue['type'], array_keys($fieldToExclude))){
                unset($customFieldValue);
            }

            $fields[] = [
                'key'      => $customFieldValue['slug'],
                'label'    => __($customFieldValue['label'], 'fluentform')
            ];
        }

        $fields = array_map('array_filter', $fields);
        $fields = array_filter( $fields );

        return  $customFieldsMapping = [
            'key'                => 'TicketCustomFields',
            'require_list'       => false,
            'label'              => __('Ticket Custom Fields', 'fluentform'),
            'tips'               => __('Please Map Your Ticket Custom Field Data for this form.', 'fluentform'),
            'component'          => 'map_fields',
            'field_label_remote'  => __('Fluent Support Custom Field', 'fluentform'),
            'field_label_local'   => __('Form Field', 'fluentform'),
            'primary_fileds'      => $fields
        ];
    }

    public function getMergeFields($list, $listId, $formId)
    {
        return [];
    }

    public function notify($feed, $formData, $entry, $form)
    {
        $data = $feed['processedValues'];

        if (!empty($data['email']) && !is_email($data['email'])) {
            $data['email'] = Arr::get($formData, $data['email']);
        }

        $ticketCustomField = array_filter($data, function($key) {
            return strpos($key, 'cf_') === 0;
        }, ARRAY_FILTER_USE_KEY);

        $customFields = apply_filters('fluent_support/ticket_custom_fields', []);

        foreach($customFields as $key=>$value){
            $type = [$key=>$value][$key]['type'];
            $slug = [$key=>$value][$key]['slug'];
            $type=='checkbox' &&  array_key_exists($slug, $ticketCustomField) ?
                $ticketCustomField[$slug] = explode(',', Arr::get($ticketCustomField, $slug)) : $ticketCustomField;
        }

        $ticketData = [
            'product_source' => 'local',
            'mailbox_id' => Arr::get($data, 'list_id'),
            'title' => sanitize_text_field(wp_unslash(Arr::get($data, 'ticket_title'))),
            'content' => wp_unslash(wp_kses_post(Arr::get($data, 'ticket_content'))),
            'attachments' => sanitize_text_field(Arr::get($data, 'ticket_attachments')),
            'client_priority' => strtolower(sanitize_text_field(Arr::get($data, 'client_priority'))),
            'priority' => strtolower(sanitize_text_field(Arr::get($data, 'client_priority'))),
            'custom_fields' => $ticketCustomField,
            'source' => 'web'
        ];

        $selectedProductArray = (array) $this->getSelectedTagIds($data, $formData, 'product_id', 'product_id_selection_type', 'product_routers');

        if($selectedProductArray) {
            $selectedProduct = $selectedProductArray[0];
            $ticketData['product_id'] = $selectedProduct;
        }

        $customerData = Arr::only($data, ['first_name', 'last_name', 'email']);

        foreach (Arr::get($data, 'customer_other_fields') as $field) {
            if ($field['item_value']) {
                $customerData[$field['label']] = $field['item_value'];
            }
        }


        $user = get_user_by('ID', get_current_user_id());

        if(!$user) {
            $user = get_user_by('email', $customerData['email']);
        }

        if($user) {
            $customerData['email'] = $user->user_email;
            $customerData['user_id'] = $user->ID;
        }

        if(empty($customerData['email'])) {
            do_action('ff_log_data', [
                'title'            => $feed['settings']['name'],
                'status'           => 'failed',
                'description'      => __('Support ticket creation failed, because no valid customer email found', 'fluentform'),
                'parent_source_id' => $form->id,
                'source_id'        => $entry->id,
                'component'        => $this->integrationKey,
                'source_type'      => 'submission_item'
            ]);
            return false;
        }

        $customerData['last_ip_address'] = $entry->ip;

        // Don't create a ticket if customer is blocked
        if($this->isBlockedCustomer($customerData['email'])) {
            do_action('ff_log_data', [
                'title'            => $feed['settings']['name'],
                'status'           => 'failed',
                'description'      => __('Support ticket creation failed, because customer email is blocked', 'fluentform'),
                'parent_source_id' => $form->id,
                'source_id'        => $entry->id,
                'component'        => $this->integrationKey,
                'source_type'      => 'submission_item'
            ]);
            return false;
        }

        $customer = Customer::maybeCreateCustomer($customerData);

        $ticketData['customer_id'] = $customer->id;

        $ticketData = apply_filters('fluent_support/create_ticket_data', $ticketData, $customer);
        do_action('fluent_support/before_ticket_create', $ticketData, $customer);

        $ticket = Ticket::create($ticketData);

        if(defined('FLUENTSUPPORTPRO') && !empty($ticketData['custom_fields'])) {
            $ticket->syncCustomFields($ticketData['custom_fields']);
            /*
             * This custom_fields is causing issues in all webhook to where we called $ticket->save()
             * As we are calling $ticket->syncCustomFields() above, we don't need this anymore
             * TODO: need to remove this line in future
             */
            //$ticket->custom_fields = $ticket->customData();
        }

        do_action('fluent_support/ticket_created', $ticket, $customer);

        do_action('ff_log_data', [
            'title'            => $feed['settings']['name'],
            'status'           => 'success',
            'description'      => __('Support ticket has been created at Fluent Support. Ticket ID: '.$ticket->id, 'fluentform'),
            'parent_source_id' => $form->id,
            'source_id'        => $entry->id,
            'component'        => $this->integrationKey,
            'source_type'      => 'submission_item'
        ]);

        if($ticketData['attachments']) {
            $attachments = explode(',', $ticketData['attachments']);
            $fluentFormUploadDir = wp_upload_dir()['basedir'] . FLUENTFORM_UPLOAD_DIR;

            foreach ($attachments as $attachment){
                $fileName = explode('/', $attachment);
                $fileName = end($fileName);
                $filePath = $fluentFormUploadDir . '/' . $fileName;
                $fileInfo = wp_check_filetype($filePath);

                Attachment::create(
                    [
                        'ticket_id' => $ticket->id,
                        'file_path' => sanitize_text_field($filePath),
                        'full_url'  => sanitize_url($attachment),
                        'title'     => $fileName,
                        'person_id' => $customer->id,
                        'file_type' => (!empty($fileInfo['type'])) ? $fileInfo['type'] : '',
                    ]
                );
            }
            $ticket->load('attachments');
        }
        return true;
    }

    public function isConfigured()
    {
        return true;
    }

    public function isEnabled()
    {
        return true;
    }

    // check if customer is blocked or not
    private function isBlockedCustomer($customerEmail)
    {
        $customer = Customer::where('email', $customerEmail)->first();

        if('inactive' == $customer->status) {
            return true;
        }

        return false;
    }

}
