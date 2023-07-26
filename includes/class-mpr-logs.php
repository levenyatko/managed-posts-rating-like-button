<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('MPR_Logs') ) :

class MPR_Logs
{
	private $logsData;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct( $logsData )
    {
        $this->logsData = $logsData;
		$this->define_hooks();
	}

    private function define_hooks()
    {
        add_action( 'admin_menu', [$this, 'add_settings_page'] );

        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );

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
        $hook = add_menu_page(
					__( 'Rating Log', 'mpr-likebtn' ),
					__( 'MPRating', 'mpr-likebtn' ),
					'manage_mpr_log',
					'mpr-plugin-page',
					[$this, 'display_log'],
					'dashicons-star-filled'
				);

        // add_action("load-$hook", [$this, 'add_table_options'] );

	}

	public function display_log()
    {
		if ( ! current_user_can( 'manage_mpr_log' ) ) {
            //'show_in_rest' => true,
			wp_die( esc_html__( 'Access Denied', 'mpr-likebtn' ) );
		}

        $filter_post_id = isset( $_GET['filter_post_id'] ) ? absint( $_GET['filter_post_id'] ) : 0;

	    if ( isset($_GET['mpr-log-action'])) {
            if ( 'recalculate' == $_GET['mpr-log-action'] && ! empty( $filter_post_id ) ) {
                $this->logsData->update_calculated_rating($filter_post_id);
            } elseif ( 'delete-row' == $_GET['mpr-log-action'] && ! empty( $_GET['row_id'] ) ) {
                $this->logsData->delete_row( (int)$_GET['row_id'] );
            }
	    }

        $table = new MPR_Logs_Table();
        $table->set_logs_data( $this->logsData );

        include_once MPR_PLUGIN_DIR . 'partials/admin/logs-table.php';
	}

    public function add_table_options()
    {
        add_screen_option('per_page', [
            'label'   => __('Number of items per page:'),
            'default' => 10,
            'option'  => 'mpr_rows_per_page'
        ]);
    }

}

endif;
