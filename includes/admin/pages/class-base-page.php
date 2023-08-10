<?php
    namespace MPRating\Admin\Pages;

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }

    class Base_Page
    {
        public function init_hooks()
        {
            add_action( 'admin_menu', [$this, 'add_settings_page'] );
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );
        }

        public function enqueue_styles($hook)
        {
            wp_register_style( 'mpr-admin-style', MPR_PLUGIN_URL . 'assets/css/mpr-admin.css', false, null );
        }

        public function add_settings_page()
        {
            add_menu_page(
                __( 'Rating Log', 'mpr-likebtn' ),
                __( 'MPRating', 'mpr-likebtn' ),
                'manage_mpr_log',
                'mpr-plugin-page',
                '',
                'dashicons-star-filled'
            );
        }

        public function display()
        {

        }
    }