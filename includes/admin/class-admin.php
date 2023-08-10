<?php

    namespace MPRating\Admin;

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }

    class Admin
    {
        public $admin_menu_base = null;

        public $admin_log_page = null;

        public $admin_chart_page = null;

        public $admin_settings_page = null;

        public $post_metabox = null;

        public function __construct()
        {
            add_action('init', [$this, 'init']);
        }

        public function init()
        {
            $this->admin_menu_base = new Pages\Base_Page();
            $this->admin_menu_base->init_hooks();

            $this->admin_log_page = new Pages\Log_Page();
            Logs_Screen_Settings::init_hooks();

            $this->admin_chart_page = new Pages\Chart_Page();
            $this->admin_settings_page = new Pages\Settings();

            Notices::init_hooks();

            new Post_Columns();

            $this->post_metabox = new Metabox();
            $this->post_metabox->init_hooks();
            $this->post_metabox->register_post_meta(); // function for same hook
        }

    }
