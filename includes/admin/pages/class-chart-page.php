<?php
    namespace MPRating\Admin\Pages;

    use MPRating\Data\Statistic_Data;

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }

    class Chart_Page extends Base_Page
    {
        private string $range;

        private string $period;

        private string $display_format;

        private string $value_format;

        public function __construct()
        {
            $this->init_hooks();
        }

        /**
         * Register the stylesheets for the admin area.
         */
        public function enqueue_styles($hook)
        {
            if ( 'mprating_page_mpr-stat-page' != $hook ) {
                return;
            }

            wp_enqueue_style( 'mpr-admin-style' );

            wp_enqueue_script('mpr-chart-js', MPR_PLUGIN_URL . 'assets/js/chart.js', [], '4.2.1');
            wp_enqueue_script('mpr-chart-page', MPR_PLUGIN_URL . 'assets/js/chart-page.js', ['mpr-chart-js', 'wp-api'], '4.2.1');

        }

        /*
         * Add admin menus
         */
        public function add_settings_page()
        {
            add_submenu_page(
                'mpr-plugin-page',
                __( 'Statistic Chart', 'mpr-likebtn' ),
                __( 'Statistic', 'mpr-likebtn' ),
                'manage_mpr_log',
                'mpr-stat-page',
                [$this, 'display']
            );

        }

        private function set_selected_range()
        {
            $range = 'year';

            if ( ! empty($_REQUEST['mpr-stat-key']) ) {
                $available_ranges = Statistic_Data::get_ranges();
                $selected = trim($_REQUEST['mpr-stat-key']);

                if ( ! empty($available_ranges[ $selected ]) ) {
                    $range = $selected;
                }
            }

            $this->range = $range;
        }

        private function set_range_date_formats()
        {
            if ('year' == $this->range) {
                $this->display_format = 'Y';
                $this->value_format = 'Y';
            } elseif ('month' == $this->range) {
                $this->display_format = 'M Y';
                $this->value_format = 'Y-m';
            }
        }

        private function set_selected_period()
        {
            if ( ! empty($_REQUEST['mpr-stat-value']) ) {
                $period = $_REQUEST['mpr-stat-value'];
            } else {
                $date_now = new \DateTime();
                $period = $date_now->format( $this->value_format );
            }

            $this->period = $period;
        }

        private function get_navigation($range, $selected_period)
        {
            $nav_array = [
                'prev' => [
                    'text'  => ''
                ],
                'current' => [
                    'value'  => $selected_period,
                    'text'   => $selected_period
                ],
                'next' => [
                    'text'  => ''
                ]
            ];

            $min_available_date = Statistic_Data::get_start_date();
            if ( empty($min_available_date) ) {
                $min_available_date = current_time('mysql');
            }
            $min_available_date = new \DateTime( $min_available_date );

            $date = new \DateTime();

            $next_modifier = '+1 ' . $range;
            $prev_modifier = '-1 ' . $range;

            if ( 'year' == $range ) {
                $date->setDate($selected_period, $date->format('m'), $date->format('j'));
            } elseif ( 'month' == $range ) {
                $selected_parts = explode('-', $selected_period);
                $date->setDate($selected_parts[0], $selected_parts[1], '1');

                $nav_array['current']['text'] = $date->format( $this->display_format );
            }

            // maybe add next nav item
            $current_date_part = date( $this->value_format );
            if ( $current_date_part > $selected_period ) {
                $date->modify( $next_modifier );
                $nav_array['next'] = [
                    'text'   => $date->format( $this->display_format ),
                    'value'  => $date->format( $this->value_format ),
                ];
                $date->modify( $prev_modifier );
            }

            // maybe add prev nav item
            $date->modify( $prev_modifier );

            if ( $min_available_date->format( $this->value_format ) <= $date->format( $this->value_format ) ) {
                $nav_array['prev'] = [
                    'text'  => $date->format( $this->display_format ),
                    'value' => $date->format( $this->value_format ),
                ];
            }

            return $nav_array;
        }

        public static function get_nav_link($key, $value)
        {
            $link = admin_url('/admin.php?page=mpr-stat-page');

            $link = add_query_arg([
                'mpr-stat-key'      => $key,
                'mpr-stat-value'    => $value
            ], $link);

            return $link;
        }

        public function display()
        {
            if ( ! current_user_can( 'manage_mpr_log' ) ) {
                wp_die( esc_html__( 'Access Denied', 'mpr-likebtn' ) );
            }

            $min_available_date = Statistic_Data::get_start_date();
            if ( empty($min_date) ) {
                $min_available_date = current_time('mysql');
            }

            $this->set_selected_range();
            $this->set_range_date_formats();
            $this->set_selected_period();

            $ranges = Statistic_Data::get_ranges();
            $selected_range = $this->range;
            $selected_period = $this->period;

            $navigation_array = $this->get_navigation($this->range, $this->period);

            include_once MPR_PLUGIN_DIR . 'partials/admin/logs-chart.php';
        }

    }