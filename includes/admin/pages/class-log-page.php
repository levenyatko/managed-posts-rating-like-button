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
            $this->process_bulk_action();
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

        private function current_action()
        {
            if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
                return $_REQUEST['action'];
            }

            if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] ) {
                return $_REQUEST['action2'];
            }

            return false;
        }

        public function process_bulk_action()
        {
            $redirect_url = admin_url('/admin.php?page=mpr-plugin-page');

            $action = $this->current_action();
            $data_store = MPR_Like_Btn()->log_data_store;

            if ( 'mpr-delete-row' ===  $action ) {
                $query_args = [];

                if ( ! empty($_GET['filter_post_id']) ) {
                    $query_args['filter_post_id'] = $_GET['filter_post_id'];
                }

                $nonce = esc_attr( $_REQUEST['_wpnonce'] );

                if ( ! wp_verify_nonce( $nonce, 'mpr_delete_row_nonce' ) ) {
                    $query_args['mpr-error'] = 'nonce';
                } else {
                    $r             = 0;
                    $row_to_delete = absint($_GET['row_id']);
                    if ($row_to_delete) {
                        $r = $data_store->delete_row($row_to_delete);
                    }

                    if ($r) {
                        $query_args['mpr-success'] = 'row-delete';
                    } else {
                        $query_args['mpr-error'] = 'row-delete';
                    }
                }

                $redirect_url = add_query_arg($query_args, $redirect_url);
                wp_redirect( esc_url_raw($redirect_url) );
                exit;

            } elseif ( 'mpr-recalculate' == $action && ! empty( $_GET['filter_post_id'] ) ) {

                if ( ! empty($_GET['filter_post_id']) ) {
                    $filter_post_id = absint( $_GET['filter_post_id'] );
                    $rating =  $data_store->get_post_rating_by($filter_post_id);
                    update_post_meta($filter_post_id, 'mpr_score', $rating);

                    $redirect_url = add_query_arg([
                        'mpr-success'    => 'recalculate',
                        'filter_post_id' => $filter_post_id
                    ], $redirect_url);

                } else {
                    $redirect_url = add_query_arg([
                        'mpr-error'    => 'recalculate',
                    ], $redirect_url);
                }

                wp_redirect( esc_url_raw($redirect_url) );
                exit;
            } elseif ( 'mpr_delete_rows' == $action ) {

                $delete_ids = esc_sql( $_POST['mpr-stat-item'] );

                // loop over the array of record IDs and delete them
                foreach ( $delete_ids as $id ) {
                    $data_store->delete_row( $id );
                }

                $redirect_url = add_query_arg(['mpr-success' => 'rows-delete'], $redirect_url);
                wp_redirect( esc_url_raw($redirect_url) );
                exit;
            }
        }

    }

endif;
