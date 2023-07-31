<?php

    class MPR_Logs_Screen_Settings
    {
        public static function init_hooks()
        {
            add_filter( "manage_toplevel_page_mpr-plugin-page_columns", [MPR_Logs_Table::class, 'get_columns_list'] );
            add_action("load-toplevel_page_mpr-plugin-page", [self::class, 'add_table_options'] );
            add_filter( "set-screen-option", [self::class, 'set_option'], 11, 3 );
        }

        public static function add_table_options()
        {
            add_screen_option('per_page', [
                'label'   => __('Number of items per page:'),
                'default' => 10,
                'option'  => 'mpr_rows_per_page'
            ]);
        }

        public static function set_option( $status, $option, $value )
        {
            if ( ! empty( $_POST['screenoptionnonce'] ) ) {
                $nonce_value = sanitize_text_field( wp_unslash( $_POST['screenoptionnonce'] ) );
                if ( wp_verify_nonce( $nonce_value, 'screen-options-nonce' ) ) {
                    if ('mpr_rows_per_page' == $option) {
                        return (int)$value;
                    }
                }
            }

            return $value;
        }

    }