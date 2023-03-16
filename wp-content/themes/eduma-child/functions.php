<?php

function thim_child_enqueue_styles() {
	wp_enqueue_style( 'thim-parent-style', get_template_directory_uri() . '/style.css', array(), THIM_THEME_VERSION  );
}

add_action( 'wp_enqueue_scripts', 'thim_child_enqueue_styles', 1000 );


// checks if the user is logged in, if not, redirects to the user account page uses //pmpro_checkout_before_submit_button hook
add_action('pmpro_checkout_before_submit_button', 'pmpro_checkout_redirect');

function pmpro_checkout_redirect(){
    if( !is_user_logged_in() ){
        wp_redirect( home_url( '/account/' ) );
        exit;
    }
}

add_shortcode('Post-shortcode', 'wp_get_post_by_slug');


function wp_get_post_by_slug(){
             if( have_rows('testimonial_group') ):
     while( have_rows('testimonial_group') ): the_row(); 

        // Get sub field values.
        $username = get_sub_field('user_name');
        $description = get_sub_field('testimonial');
        $profile_photo = get_sub_field('profile_photo');
        
        $trimmed_content = wp_trim_words($description, 40);
        echo '<div class="testimonial_container">
                <div class="testimonial_wrapper">
                    <img class="testimonial_img" decoding="async" width="113" height="113" src='.$profile_photo.' loading="lazy">
                    <div>
                        <h4>'.$username.'</h4>
                        <i aria-hidden="true" class="far fa-play-circle"></i>
                    </div>
                </div>
                <div class="testimonial_content">'.$trimmed_content.'</div>
               </div>';
       
     endwhile;
        endif;
        
}