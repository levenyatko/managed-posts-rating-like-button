<?php
/**
 * Plugin Name:       Managed posts rating ★ Like button
 * Plugin URI:        https://github.com/levenyatko/mpr-likebtn
 * Description:       Adds rating to any post type, allows to manage user likes.
 * Version:           1.2.0
 * Author:            Daria Levchenko
 * Author URI:        https://github.com/levenyatko
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mpr-likebtn
 * Domain Path:       /languages
 * Tested up to:      6.2
 * Requires PHP:      7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'MPR_Like_Btn' ) ) {

    final class MPR_Like_Btn {

        private static $instance;

        /**
         * Disable object cloning.
         *
         * @return void
         */
        public function __clone() {}

        /**
         * Disable unserializing of the class.
         *
         * @return void
         */
        public function __wakeup() {}

        /**
         * Main plugin instance, insures that only one instance of the plugin exists in memory at one time.
         *
         * @return object
         */
        public static function instance()
        {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MPR_Like_Btn ) ) {
                self::$instance = new MPR_Like_Btn;
                self::$instance->define_constants();

                self::$instance->load_dependencies();
                self::$instance->load_admin_dependencies();

                self::$instance->logs_data = new MPR_Logs_Data();
                self::$instance->front_api = new MPR_Rest_Api();
                self::$instance->frontend = new MPR_Button_Display();

                if ( is_admin() ) {
                    self::$instance->columns = new MPR_Columns();
                    self::$instance->metabox = new MPR_Metabox();

                    self::$instance->logs_page = new MPR_Logs( self::$instance->logs_data );
                    MPR_Logs_Screen_Settings::init_hooks();

                    self::$instance->settings = new MPR_Settings();

                    MPR_Notices::init_hooks();
                }

            }

            return self::$instance;
        }

        private function define_constants()
        {
            define( 'MPR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            define( 'MPR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }

        private function load_dependencies()
        {
            require_once MPR_PLUGIN_DIR . 'includes/functions.php';

            require_once MPR_PLUGIN_DIR . 'includes/class-mpr-logs-data.php';

            require_once MPR_PLUGIN_DIR . 'includes/class-mpr-rest-api.php';
            require_once MPR_PLUGIN_DIR . 'includes/class-mpr-button-display.php';
        }

        private function load_admin_dependencies()
        {
            if ( is_admin() ) {

                require_once MPR_PLUGIN_DIR . 'includes/class-mpr-columns.php';
                require_once MPR_PLUGIN_DIR . 'includes/class-mpr-metabox.php';

                require_once MPR_PLUGIN_DIR . 'includes/class-mpr-logs.php';
                require_once MPR_PLUGIN_DIR . 'includes/class-mpr-logs-screen-settings.php';
                require_once MPR_PLUGIN_DIR . 'includes/class-mpr-logs-table.php';

                require_once MPR_PLUGIN_DIR . 'includes/class-mpr-settings.php';

                require_once MPR_PLUGIN_DIR . 'includes/class-mpr-notices.php';
            }
        }

        public function __construct()
        {
            register_activation_hook( __FILE__, [ $this, 'plugin_activation' ] );
            register_deactivation_hook( __FILE__, [ $this, 'plugin_deactivation' ] );
        }

        public function plugin_activation()
        {
            self::$instance->logs_data->create_table();

            // Set Capabilities To Administrator
            $role = get_role( 'administrator' );
            $role->add_cap( 'manage_mpr_log' );
            $role->add_cap( 'mpr_freely_likes' );

        }

        public function plugin_deactivation()
        {
	        $maybe_clear_all = mpr_get_option( 'clear_all_settings', 'mpr_general_section', false );

            if ( $maybe_clear_all ) {

                self::$instance->logs_data->delete_table();

	            delete_option("mpr_general_section");
            }

        }

    }
}

function MPR_Like_Btn()
{
    static $instance;

    // first call to instance() initializes the plugin
    if ( $instance === null || ! ( $instance instanceof MPR_Like_Btn ) )
        $instance = MPR_Like_Btn::instance();

    return $instance;
}

function mpr_load_textdomain()
{
    load_plugin_textdomain( 'mpr-likebtn', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'mpr_load_textdomain' );

MPR_Like_Btn();
