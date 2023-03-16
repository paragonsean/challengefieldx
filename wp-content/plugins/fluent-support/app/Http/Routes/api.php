<?php

/**
 * @var $router FluentSupport\Framework\Http\Router
 */

$router->prefix('mailboxes')->withPolicy('AdminSettingsPolicy')->group(function ($router) {
    $router->get('/', 'MailBoxController@index');
    $router->post('/', 'MailBoxController@save');
    $router->get('/{id}', 'MailBoxController@get')->int('id');
    $router->put('/{id}', 'MailBoxController@update')->int('id');
    $router->delete('/{id}', 'MailBoxController@delete')->int('id');
    $router->put('/{id}/move_tickets', 'MailBoxController@moveTickets')->int('id');

    $router->get('/{id}/tickets', 'MailBoxController@getTickets')->int('id');

    $router->get('/{id}/email_settings', 'MailBoxController@getEmailSettings')->int('id');
    $router->get('/{id}/email_configs', 'MailBoxController@getEmailsSetups');
    $router->put('/{id}/email_settings', 'MailBoxController@saveEmailSettings')->int('id');
});

$router->prefix('tickets')->withPolicy('AgentTicketPolicy')->group(function ($router) {

    $router->get('my_stats', 'AgentController@myStats');
    $router->get('/', 'TicketController@index');
    $router->post('/', 'TicketController@createTicket');

    $router->get('/{ticket_id}', 'TicketController@getTicket')->int('ticket_id');

    $router->get('/{ticket_id}/widgets', 'TicketController@getTicketWidgets')->int('ticket_id');
    $router->post('/{ticket_id}/responses', 'TicketController@createResponse')->int('ticket_id');

    $router->get('/{ticket_id}/live_activity', 'TicketController@getLiveActivity')->int('ticket_id');
    $router->delete('/{ticket_id}/live_activity', 'TicketController@removeLiveActivity')->int('ticket_id');

    $router->put('/{ticket_id}/responses/{response_id}', 'TicketController@updateResponse')
        ->int('ticket_id')
        ->int('response_id');

    $router->delete('/{ticket_id}/responses/{response_id}', 'TicketController@deleteResponse')
        ->int('ticket_id')
        ->int('response_id');

    $router->post('/{ticket_id}/customer-responses', 'ConversationController@createCustomerReply')
        ->int('ticket_id');

    $router->put('/{ticket_id}/property', 'TicketController@updateTicketProperty')->int('ticket_id');

    $router->post('/{ticket_id}/tags', 'TicketController@addTag')->int('ticket_id');
    $router->delete('/{ticket_id}/tags/{tag_id}', 'TicketController@detachTag')->int('ticket_id')->int('tag_id');

    $router->post('/{ticket_id}/close', 'TicketController@closeTicket')->int('ticket_id');
    $router->post('/{ticket_id}/re-open', 'TicketController@reOpenTicket')->int('ticket_id');
    $router->put('/{ticket_id}/change-customer', 'TicketController@changeTicketCustomer')->int('ticket_id');
    $router->get('/{ticket_id}/custom-data', 'TicketController@getTicketCustomData')->int('ticket_id');

    $router->post('bulk-actions', 'TicketController@doBulkActions'); // close_tickets | delete_tickets | assign_agent | assign_tags
    $router->post('bulk-reply', 'TicketController@doBulkReplies');

    $router->post('sync-fluentcrm-tags', 'TicketController@syncFluentCrmTags');
    $router->post('sync-fluentcrm-lists', 'TicketController@syncFluentCrmLists');

    $router->get('search-contact', 'CustomerController@searchContact');
});

$router->prefix('products')->withPolicy('AdminSettingsPolicy')->group(function ($router) {
    $router->get('/', 'ProductController@index');
    $router->post('/', 'ProductController@create');
    $router->get('/{product_id}', 'ProductController@get')->int('product_id');
    $router->post('/{product_id}', 'ProductController@create')->int('product_id');
    $router->put('/{product_id}', 'ProductController@update')->int('product_id');
    $router->delete('/{product_id}', 'ProductController@delete')->int('product_id');
});

$router->get('me', 'TicketController@me')->withPolicy('PortalPolicy');

$router->get('options/countries', 'OptionsController@getCountries')->withPolicy('PortalPolicy');

$router->post('ticket_file_upload', 'UploaderController@uploadTicketFiles')
    ->withPolicy('PortalPolicy');

$router->prefix('settings')->withPolicy('AdminSettingsPolicy')->group(function ($router) {
    $router->get('/', 'SettingsController@getSettings');
    $router->post('/', 'SettingsController@saveSettings');
    $router->get('/integration-settings', 'SettingsController@getIntegrationSettings');
    $router->get('/integration', 'IntegrationController@getSettings');
    $router->post('/integration', 'IntegrationController@saveSettings');
    $router->get('/slack-integration', 'SlackController@getSettings');
    $router->post('/slack-integration', 'SlackController@saveSettings');
    $router->get('/pages', 'SettingsController@getPages');
    $router->post('/setup', 'SettingsController@setupPortal');
    $router->post('/setup-installation', 'SettingsController@setupInstallation');

    $router->get('/fluentcrm-settings', 'SettingsController@getFluentCRMSettings');
    $router->post('/intsall-fluentcrm', 'SettingsController@installFluentCRM');
});

$router->prefix('agents')->withPolicy('AdminSensitivePolicy')->group(function ($router) {
    $router->get('/', 'AgentController@index');
    $router->post('/', 'AgentController@addAgent');
    $router->put('/{agent_id}', 'AgentController@updateAgent')->int('agent_id');
    $router->delete('/{agent_id}', 'AgentController@deleteAgent')->int('agent_id');
    $router->post('/{agent_id}/avatar', 'AgentController@addOrUpdateProfileImage')->int('agent_id');
    $router->post('/{agent_id}/reset_avatar', 'AgentController@resetAvatar')->int('agent_id');
});

$router->prefix('reports')->withPolicy('ReportPolicy')->group(function ($router) {
    $router->get('/', 'ReportingController@getOverallReports');
    $router->get('/tickets-growth', 'ReportingController@getTicketsChart');
    $router->get('/tickets-resolve-growth', 'ReportingController@getResolveChart');
    $router->get('/response-growth', 'ReportingController@getResponseChart');
    $router->get('/agents-summary', 'ReportingController@getAgentsSummary');
});

$router->prefix('my-reports')->withPolicy('AgentTicketPolicy')->group(function ($router) {
    $router->get('/', 'ReportingController@getAgentOverallReports');
    $router->get('/tickets-resolve-growth', 'ReportingController@getAgentResolveChart');
    $router->get('/response-growth', 'ReportingController@getAgentResponseChart');
    $router->get('/my-summary', 'ReportingController@getPersonalSummary');
});

$router->prefix('customers')->withPolicy('AdminSensitivePolicy')->group(function ($router) {
    $router->get('/', 'CustomerController@index');
    $router->post('/', 'CustomerController@create');

    $router->get('/{customer_id}', 'CustomerController@getCustomer')->int('customer_id');
    $router->put('/{customer_id}', 'CustomerController@update')->int('customer_id');
    $router->delete('/{customer_id}', 'CustomerController@delete')->int('customer_id');

    $router->post('/profile_image/{customer_id}', 'CustomerController@addOrUpdateProfileImage')->int('customer_id');
    $router->post('/reset_avatar/{customer_id}', 'CustomerController@resetAvatar')->int('customer_id');
});

$router->prefix('customer-portal')->withPolicy('PortalPolicy')->group(function ($router) {

    $router->get('public_options', 'CustomerPortalController@getPublicOptions');
    $router->get('custom-fields-rendered', 'CustomerPortalController@getCustomFieldsRender');

    $router->get('tickets', 'CustomerPortalController@getTickets');
    $router->post('tickets', 'CustomerPortalController@createTicket');

    $router->get('tickets/{ticket_id}', 'CustomerPortalController@getTicket')->int('ticket_id');
    $router->post('tickets/{ticket_id}/responses', 'CustomerPortalController@createResponse')->int('ticket_id');

    $router->post('/tickets/{ticket_id}/close', 'CustomerPortalController@closeTicket')->int('ticket_id');
    $router->post('/tickets/{ticket_id}/re-open', 'CustomerPortalController@reOpenTicket')->int('ticket_id');

    $router->post('ticket_file_upload','UploaderController@uploadTicketFiles');

    $router->get('me', 'TicketController@me');

    $router->post('logout', 'CustomerPortalController@logout');
});

$router->prefix('public')->withPolicy('PublicPolicy')->group(function($router) {
    $router->post('telegram_bot_response/{token}', 'ChatMessageParserController@handleTelegramWebhook')->alphaNumDash('token');
    $router->post('slack_response/{token}', 'ChatMessageParserController@handleSlackEvent')->alphaNumDash('token');
});

$router->prefix('activity-logger')->withPolicy('ActivityLoggerPolicy')->group(function ($router) {
    $router->get('/', 'ActivityLoggerController@getActivities');
    $router->get('/settings', 'ActivityLoggerController@getSettings');
    $router->post('/settings', 'ActivityLoggerController@updateSettings');
});

$router->post('signup', 'AuthController@signup')->withPolicy('PublicPolicy');

$router->post('login', 'AuthController@handleLogin')->withPolicy('PublicPolicy');

$router->post('reset_pass', 'AuthController@resetPassword')->withPolicy('PublicPolicy');

$router->prefix('ticket_importer')->withPolicy('AdminSettingsPolicy')->group( function ( $router ) {
    $router->get('/', 'TicketImportController@getStats');
    $router->post('/import', 'TicketImportController@importTickets');
    $router->delete('/delete', 'TicketImportController@deleteTickets');
} );
