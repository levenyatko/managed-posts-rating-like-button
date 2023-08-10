<?php
    namespace MPRating\Admin\ListTables;

    if ( ! defined( 'ABSPATH' ) ) exit;

    if ( ! class_exists( '\WP_List_Table' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }

    class Log_Table extends \WP_List_Table
    {
        private $log_data_store = null;

        public function __construct ()
        {
            parent::__construct( [
                'singular'  => 'mpr-stat-item',
                'plural'    => 'mpr-plugin-page',
                'ajax'      => false,
            ] );

            $this->log_data_store = MPR_Like_Btn()->log_data_store;
        }

        public static function get_columns_list()
        {
            return [
                'id'         => __('ID', 'mpr-likebtn'),
                'liked_post' => __('Post', 'mpr-likebtn'),
                'rating'     => __('Rating', 'mpr-likebtn'),
                'page_from'  => __('Page From', 'mpr-likebtn'),
                'user'       => __('User', 'mpr-likebtn'),
                'date'       => __('Date', 'mpr-likebtn'),
            ];
        }

        function get_columns()
        {
            return array_merge(['cb' => '<input type="checkbox" />'], self::get_columns_list());
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
                'page'      => sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ),
                'action'    => 'mpr-delete-row',
                'row_id'    => absint( $item['id'] ),
            ];

            if ( ! empty($_GET['filter_post_id']) ) {
                $url_delete_args['filter_post_id'] = $_REQUEST['filter_post_id'];
            }

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
                'mpr_delete_rows' => 'Delete',
            ];
        }

        function prepare_items()
        {
            $per_page = $this->get_items_per_page('mpr_rows_per_page', 10);
            $columns = $this->get_columns();

            $hidden = get_hidden_columns( $this->screen );
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
            if ( null == $this->log_data_store ) {
                return [];
            }

            $current_page = $this->get_pagenum();

            $filter_post_id = isset( $_GET['filter_post_id'] ) ? absint( $_GET['filter_post_id'] ) : 0;

            $total_records = $this->log_data_store->get_data_rows_count($filter_post_id);

            $per_page = $this->get_items_per_page('mpr_rows_per_page', 10);
            $data = $this->log_data_store->get_data($current_page, $per_page, $filter_post_id);

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

        public function process_bulk_action()
        {
            $redirect_url = admin_url('/admin.php?page=mpr-plugin-page');

            $action = $this->current_action();

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
                        $r = $this->log_data_store->delete_row($row_to_delete);
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
                    $rating =  $this->log_data_store->get_post_rating_by($filter_post_id);
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
            }

            // If the delete bulk action is triggered
            if ( ( ! empty( $_POST['action'] ) && $_POST['action'] == 'mpr_delete_rows' )
                 || ( ! empty( $_POST['action2'] ) && $_POST['action2'] == 'mpr_delete_rows' )
            ) {

                $delete_ids = esc_sql( $_POST['mpr-stat-item'] );

                // loop over the array of record IDs and delete them
                foreach ( $delete_ids as $id ) {
                    $this->log_data_store->delete_row( $id );
                }

                $redirect_url = add_query_arg(['mpr-success' => 'rows-delete'], $redirect_url);
                wp_redirect( esc_url_raw($redirect_url) );
                exit;
            }
        }

    }
