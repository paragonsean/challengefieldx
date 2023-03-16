<?php

namespace FluentSupport\App\Hooks\Handlers;


use FluentSupport\App\Models\Attachment;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;

class ExternalPages
{

    public function route()
    {
        $route = sanitize_text_field($_REQUEST['fs_view']);

        $methodMaps = [
            'ticket' => 'handleTicketView'
        ];

        if(isset($methodMaps[$route])) {
            $this->{$methodMaps[$route]}();
        }

    }

    public function handleTicketView()
    {


        if(!Helper::isPublicSignedTicketEnabled()) {
            $ticketId = absint(Arr::get($_REQUEST, 'ticket_id'));
            $ticket = Ticket::where('id', $ticketId)->first();

            if(!$ticket) {
                // Sorry no ticket found;
                echo '<h3 style="text-align: center; margin: 50px 0;">'.__('Invalid Support Portal URL', 'fluent-support').'</h3>';
                die();
            }

            $redirectUrl = Helper::getTicketViewUrl($ticket);
            wp_redirect($redirectUrl, 307);
            exit();
        }

        $ticketHash = sanitize_text_field(Arr::get($_REQUEST, 'support_hash'));
        $ticketId = absint(Arr::get($_REQUEST, 'ticket_id'));
        $ticket = Ticket::where('hash', $ticketHash)->where('id', $ticketId)->first();

        if(!$ticket) {
            // Sorry no ticket found;
            echo '<h3 style="text-align: center; margin: 50px 0;">'.__('Invalid Support Portal URL', 'fluent-support').'</h3>';
            die();
        }

        if(get_current_user_id()) {
            // We have to re-route the URL
            $redirectUrl = Helper::getTicketViewUrl($ticket);
            wp_redirect($redirectUrl, 307);
            exit();
        }

    }

    /**
     * Display the attachment.
     *
     * Uses the new rewrite endpoint to get an attachment ID
     * and display the attachment if the currently logged in user
     * has the authorization to.
     *
     * @return void
     * @since 3.2.0
     */
    public function view_attachment()
    {

        $attachmentHash = sanitize_text_field($_REQUEST['fst_file']);


        if (!empty($attachmentHash)) {

            $attachment = Attachment::where('file_hash', $attachmentHash)->first();

            /**
             * Return a 404 page if the attachment ID
             * does not match any attachment in the database.
             */
            if (empty($attachment)) {

                /**
                 * @var WP_Query $wp_query WordPress main query
                 */
                global $wp_query;

                $wp_query->set_404();

                status_header(404);
                include(get_query_template('404'));

                die();
            }

            // check signature hash
            $sign = md5($attachment->id . date('YmdH'));
            if($sign != $_REQUEST['secure_sign']) {
                $dieMessage = __('Sorry, Your secure sign is invalid, Please reload the previous page and get new signed url', 'fluent-support');
                die($dieMessage);
            }

            ob_clean();
            ob_end_flush();

            ini_set('user_agent', 'Fluent Support/' . FLUENT_SUPPORT_VERSION . '; ' . get_bloginfo('url'));
            header("Content-Type: $attachment->file_type");
            header("Content-Disposition: inline; filename=\"$attachment->title\"");

            echo readfile( $attachment->file_path );
            die();
        }

    }


}
