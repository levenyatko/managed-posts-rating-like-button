<?php

    if ( ! defined( 'ABSPATH' ) ) exit;

    if ( ! class_exists( 'WP_List_Table' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }

    class MPR_Logs_Table extends WP_List_Table
    {
        private $logsData;

        public function __construct ()
        {
            parent::__construct( [
                'singular'  => 'mpr-stat-item',
                'plural'    => 'mpr-plugin-page',
                'ajax'      => false,
            ] );

        }

        public function set_logs_data($logsData)
        {
            $this->logsData = $logsData;
        }

        public function get_columns()
        {
            return [
                'cb'         => '<input type="checkbox" />',
                'id'         => __('ID', 'mpr-likebtn'),
                'liked_post' => __('Post', 'mpr-likebtn'),
                'rating'     => __('Rating', 'mpr-likebtn'),
                'page_from'  => __('Page From', 'mpr-likebtn'),
                'user'       => __('User', 'mpr-likebtn'),
                'date'       => __('Date', 'mpr-likebtn'),
            ];
        }

        function column_default ( $item, $column_name )
        {
            return $item[ $column_name ];
        }

        function column_cb ($item)
        {
            return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                $this->_args['singular'],
                $item['id']
            );
        }

        function column_liked_post( $item )
        {
            $url_delete_args = [
                'page'             => sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ),
                'mpr-log-action'   => 'delete-row',
                'row_id'           => absint( $item['id'] ),
            ];

            $delete_url = wp_nonce_url( add_query_arg( $url_delete_args ), 'mpr_delete_row_nonce' );

            $filter_url = mpr_get_log_link($item['post_id']);
            $page_url = get_permalink($item['post_id']);

            $actions = array(
                'delete' => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $delete_url ), esc_html__( 'Delete', 'mpr-likebtn') ),
                'view' => sprintf( '<a href="%1$s">%2$s</a>', esc_url( $filter_url ), esc_html__( 'Filter', 'mpr-likebtn') ),
            );

            return sprintf('<a href="%1$s" target="_blank">%2$s</a><br>%3$s',
                $page_url,
                $item['post_title'],
                $this->row_actions( $actions )
            );
        }

        function column_page_from( $item )
        {
            if ( 0 > $item['rating_source_id'] ) {
                $from_html = $item['rating_source_name'];
            } else {
                $page_url = get_the_permalink( $item['rating_source_id'] );
                $from_html = sprintf('<a href="%s" target="_blank">%s</a>', esc_attr($page_url), esc_html($item['rating_source_name']));
            }

            return $from_html;
        }

        function column_rating( $item )
        {
            if ( 0 < $item['rating'] ) {
                $class = 'item-like';
            } else {
                $class = 'item-dislike';
            }

            return '<p><span class="mpr-icon ' . $class . '"></span>' . $item['rating'] . '</p>';
        }

        public function get_bulk_actions()
        {
            return [
                'delete'       => 'Delete',
            ];
        }

        function prepare_items()
        {
            if ( null == $this->logsData ) {
                return;
            }

            $per_page = $this->get_items_per_page('mpr_rows_per_page', 10);
            $columns = $this->get_columns();

            $hidden = [];
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = [ $columns, $hidden, $sortable ];

            $this->process_bulk_action();

            $data = $this->get_table_data();

            if ( ! empty( $data ) ) {

                $total_items = ( $data['total_records'] )? (int) $data['total_records'] : 0;
                $this->items = ( $data['data'] ) ? $data['data'] : [];

                $this->set_pagination_args( [
                    'total_items' => $total_items,
                    'per_page'    => $per_page,
                    'total_pages' => ceil( $total_items / $per_page ),
                ] );

            }
        }

        function get_table_data()
        {
            if ( null == $this->logsData ) {
                return [];
            }

            $current_page = $this->get_pagenum();

            $filter_post_id = isset( $_GET['filter_post_id'] ) ? absint( $_GET['filter_post_id'] ) : 0;

            $total_records = $this->logsData->get_post_rows_count($filter_post_id);

            $per_page = $this->get_items_per_page('mpr_rows_per_page', 10);
            $data = $this->logsData->get_data($current_page, $per_page, $filter_post_id);

            $table_data = [];

            if ( ! empty($data) ) {
                foreach ($data as $row) {

                    $user = get_userdata($row['user_id']);
                    if ( $user ) {
                        $profile_url = get_edit_profile_url($row['user_id']);
                        $user_string = sprintf('<a href="%s" target="_blank">%s</a>', esc_url($profile_url), esc_html($user->display_name));
                    } else {
                        $user_string = esc_html__('No data', 'mpr-likebtn');
                    }

                    $user_string .= '<br>' . $row['voting_ip'];

                    $table_data[] = [
                        'id'                  => $row['rating_id'],
                        'post_id'             => $row['post_id'],
                        'post_title'          => $row['post_title'],
                        'rating_source_name'  => $row['ref_post_title'],
                        'rating_source_id'    => $row['ref_post_id'],
                        'user'                => $user_string,
                        'date'                => $row['timestamp'],
                        'rating'              => $row['rating'],
                    ];
                }
            }

            return [
                'total_records' => $total_records,
                'data'          => $table_data,
            ];

        }

    }
