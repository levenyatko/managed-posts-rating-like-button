<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('MPR_Button_Display') ) :

class MPR_Button_Display
{
	public function __construct()
    {
		$this->define_hooks();
	}

    private function define_hooks()
    {
        add_shortcode('mpr-button', 'mpr_button' );

        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_styles'] );

        add_filter('the_content', [$this, 'maybe_add_post_rating'], 50);
    }

    public function enqueue_styles()
    {
        wp_enqueue_style( 'mpr-style', MPR_PLUGIN_URL . 'assets/css/frontend.min.css', false, null );

        wp_register_script( 'mpr-script', MPR_PLUGIN_URL . 'assets/js/frontend.js', false, null, true );
        wp_localize_script( 'mpr-script', 'mpr_vars',
            [
                'apibase' => get_rest_url(null, 'mpr'),
                'nonce'   => wp_create_nonce( 'wp_rest' )
            ]
        );
        wp_enqueue_script( 'mpr-script');
    }

    public function maybe_add_post_rating($content)
    {
        $display_type = mpr_get_option( 'display_type', 'mpr_general_section', 'manually' );
        $screens = apply_filters('mpr_edit_meta_box_screens', []);

        if ( empty($screens) ) {
            return $content;
        }

        if ( 'manual' != $display_type && is_singular($screens) && is_main_query() ) {

            if ( function_exists('is_woocommerce') ) {
                if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page()  ) {
                    return $content;
                }
            }

            global $post;

            $args = [
                'id'        => $post->ID,
                'disabled'  => false,
                'return'    => true
            ];

            if ( 'before' == $display_type ) {
                $content = mpr_button($args) . $content;
            } elseif ( 'after' == $display_type ) {
                $content .= mpr_button($args);
            }
        }

        return $content;
    }

}
endif;