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
			wp_die( esc_html__( 'Access Denied', 'mpr-likebtn' ) );
		}

        $filter_post_id = isset( $_GET['filter_post_id'] ) ? absint( $_GET['filter_post_id'] ) : 0;

        $table = new MPR_Logs_Table();
        $table->set_logs_data( $this->logsData );

        include_once MPR_PLUGIN_DIR . 'partials/admin/logs-table.php';
	}

}

endif;
