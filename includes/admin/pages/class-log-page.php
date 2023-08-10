<?php

    namespace MPRating\Admin\Pages;

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }

if ( ! class_exists('Log_Page') ) :

    class Log_Page extends Base_Page
    {
        /**
         * Initialize the class and set its properties.
         */
        public function __construct()
        {
            $this->init_hooks();
        }

        /**
         * Register the stylesheets for the admin area.
         */
        public function enqueue_styles( $hook )
        {
            if ( 'toplevel_page_mpr-plugin-page' != $hook ) {
                return;
            }

            wp_enqueue_style( 'mpr-admin-style' );
        }

        /*
         * Add admin menus
         */
        public function add_settings_page()
        {
            add_submenu_page(
                'mpr-plugin-page',
                __( 'Rating Log', 'mpr-likebtn' ),
                __( 'Logs', 'mpr-likebtn' ),
                'manage_mpr_log',
                'mpr-plugin-page',
                [$this, 'display']
            );

        }

        public function display()
        {
            if ( ! current_user_can( 'manage_mpr_log' ) ) {
                wp_die( esc_html__( 'Access Denied', 'mpr-likebtn' ) );
            }

            $filter_post_id = isset( $_GET['filter_post_id'] ) ? absint( $_GET['filter_post_id'] ) : 0;

            $table = new \MPRating\Admin\ListTables\Log_Table();

            include_once MPR_PLUGIN_DIR . 'partials/admin/logs-table.php';
        }

    }

endif;
