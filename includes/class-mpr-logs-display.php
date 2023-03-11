<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('MPR_Logs_Display') ) :

class MPR_Logs_Display
{

	private $logs_data;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $logs_data )
    {
        $this->logs_data = $logs_data;

		$this->define_hooks();
	}

    private function define_hooks()
    {
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );

        // add menu item in wp-admin
        add_action( 'admin_menu', [$this, 'add_settings_page'] );

        add_filter('mpr_log_table_headings', [$this, 'log_table_headings']);
        add_filter('mpr_log_table_row', [$this, 'log_table_row_data']);

    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles()
    {
        wp_enqueue_style( 'mpr-admin-style', MPR_PLUGIN_URL . 'assets/css/mpr-admin.css', false, null );
    }

	/*
	 * Add admin menus
	 */
	public function add_settings_page()
    {
		add_menu_page(
					__( 'Rating Log', 'mpr-likebtn' ),
					__( 'MPRating', 'mpr-likebtn' ),
					'manage_mpr_log',
					'mpr-plugin-page',
					[$this, 'display_log'],
					'dashicons-star-filled'
				);

	}

	public function display_log()
    {
		if ( ! current_user_can( 'manage_mpr_log' ) ) {
			wp_die( __( 'Access Denied', 'mpr-likebtn' ) );
		}

        if ( isset($_GET['mpr-delete-row']) ) {
            $this->logs_data->delete_row($_GET['mpr-delete-row']);
        }

        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $filter_post_id = isset( $_GET['filter_post_id'] ) ? absint( $_GET['filter_post_id'] ) : 0;

	    if ( isset($_GET['mpr-log-action']) && 'recalculate' == $_GET['mpr-log-action'] && ! empty( $filter_post_id ) ) {
		    $this->logs_data->update_calculated_rating($filter_post_id);
	    }

		$num_of_pages = $this->logs_data->get_post_rows_count($filter_post_id);

		if ($num_of_pages > 0) {
			$num_of_pages = ceil($num_of_pages/$this->logs_data->rows_per_page);
		}

		$logs_data = $this->logs_data->get_data($pagenum, $filter_post_id);

        include_once MPR_PLUGIN_DIR . 'partials/admin/logs-table.php';
	}

	public function log_table_headings()
    {
        return [
            __('ID', 'mpr-likebtn'),
            __('Post', 'mpr-likebtn'),
            __('Page From', 'mpr-likebtn'),
            __('User Name', 'mpr-likebtn'),
            __('User IP (hash)', 'mpr-likebtn'),
            __('Date', 'mpr-likebtn'),
            __('Voting', 'mpr-likebtn'),
            __('Actions', 'mpr-likebtn')
        ];
    }

    public function log_table_row_data($row_data)
    {
        $result = [];

        if ( ! array($row_data) ) {
            return $result;
        }

        $user = get_userdata($row_data['user_id']);

        $link_args = array_merge($_GET, array('mpr-delete-row' => $row_data['rating_id']));
		if ( isset($link_args['mpr-log-action']) ) {
			unset($link_args['mpr-log-action']);
		}
        $delete_url = add_query_arg($link_args, '/wp-admin/admin.php' );

        $result['id'] = [
            'value' => $row_data['rating_id']
        ];
        $result['post'] = [
                'value' => '<a href="' . get_edit_post_link($row_data['post_id']) . '" target="_blank">' . $row_data['post_title'] . '</a>'
            ];

        if ( 0 > $row_data['ref_post_id'] ) {
            $result['ref'] = [
                'value' => $row_data['ref_post_title']
            ];
        } else {
            $result['ref'] = [
                'value' => '<a href="' . get_the_permalink($row_data['ref_post_id']) . '" target="_blank">' . $row_data['ref_post_title'] . '</a>'
            ];
        }

        if ( $user ) {
            $result['user'] = [
                'value' => '<a href="' . get_edit_profile_url($row_data['user_id']) . '">' .  $user->display_name . '</a>'
            ];
        } else {
            $result['user'] = [
                'value' => __('No data', 'mpr-likebtn')
            ];
        }

        $result['ip'] = [
            'value' => $row_data['voting_ip']
        ];
        $result['time'] = [
            'value' => $row_data['timestamp']
        ];

        if ( 0 < $row_data['rating'] ) {
             $result['rating'] = [
                 'attrs' => [
                     'class' => 'item-like',
                 ],
                 'value' => $row_data['rating']
             ];
        } else {
            $result['rating'] = [
                'attrs' => [
                    'class' => 'item-dislike',
                ],
                'value' => $row_data['rating']
            ];
        }

        $actions_string = '<a href="' . mpr_get_log_link($row_data['post_id']) . '" title="' . __('Filter', 'mpr-likebtn') . '"><div class="dashicons dashicons-filter" aria-hidden="true"></div></a>';
        $actions_string .= '<a href="' . $delete_url . '" title="' . __('Delete', 'mpr-likebtn') . '"><div class="dashicons dashicons-no" aria-hidden="true"></div></a>';


        $result['actions'] = [
            'attrs' => [
                'class' => 'wpr-actions',
            ],
            'value' => $actions_string
        ];

        return $result;
    }

}

endif;
