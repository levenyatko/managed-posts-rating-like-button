<?php
    namespace MPRating;

    defined( 'ABSPATH' ) || exit;

    final class Plugin
    {

        public $log_data_store = null;

        public $api = null;

        public $frontend = null;

        public $admin = null;

        protected static $instance = null;

        public function __construct()
        {
            $this->init();
            $this->init_hooks();
        }

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
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function init()
        {
            $this->log_data_store   = new Data\Logs_Data_Store();
            $this->api              = new Rest_Api();
            $this->frontend         = new Frontend();

            if ( is_admin() ) {
                $this->admin         = new Admin\Admin();
            }
        }

        public function init_hooks()
        {
            register_activation_hook( MPR_PLUGIN_FILE, [ $this, 'plugin_activation' ] );
            register_deactivation_hook( MPR_PLUGIN_FILE, [ $this, 'plugin_deactivation' ] );

            add_action( 'init', [ $this, 'load_textdomain' ] );
        }

        public function plugin_activation()
        {
            $this->log_data_store->create_table();

            // Set Capabilities To Administrator
            $role = get_role( 'administrator' );
            $role->add_cap( 'manage_mpr_log' );
            $role->add_cap( 'mpr_freely_likes' );

        }

        public function plugin_deactivation()
        {
            $maybe_clear_all = mpr_get_option( 'clear_all_settings', 'mpr_general_section', false );

            if ( $maybe_clear_all ) {

                $this->log_data_store->delete_table();

                delete_option("mpr_general_section");

                $role = get_role( 'administrator' );
                $role->remove_cap( 'manage_mpr_log' );
                $role->remove_cap( 'mpr_freely_likes' );
            }

        }

        public function load_textdomain()
        {
            load_plugin_textdomain( 'mpr-likebtn', false, dirname( plugin_basename( MPR_PLUGIN_FILE ) ) . '/languages' );
        }

    }
