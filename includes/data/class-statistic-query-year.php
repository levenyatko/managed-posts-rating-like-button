<?php
    namespace MPRating\Data;

    defined( 'ABSPATH' ) || exit;

    class Statistic_Query_Year extends Statistic_Query
    {
        private static int $date_len = 7;

        public function __construct($selected_period = '')
        {
            parent::__construct($selected_period);
        }

        protected function prepare_dates_array($period)
        {
            $queried_dates = [];
            $start_date = new \DateTime();

            for ($i = 1; $i <= 12; $i++) {
                $start_date->setDate($period, $i,1);
                $queried_dates[] = [
                    'value' => $start_date->format('Y-m'),
                    'date'  => $start_date->format('Y-m-d'),
                ];
            }

            return $queried_dates;
        }

        protected function make_total_query_part($date, $date_ind, &$query_params )
        {
            $table_name = Logs_Data_Store::get_logs_table_name();

            $alias = 't' . $date_ind;

            $parts_query = "SELECT count(`rating_id`) FROM {$table_name} as {$alias} WHERE SUBSTRING(`timestamp`, 1, %d) = %s";

            $query_params[] = self::$date_len;
            $query_params[] = $date['value'];

            return $parts_query;
        }

        protected function make_cpt_query_part($date, $date_ind, $post_type, &$query_params)
        {
            global $wpdb;

            $table_name = Logs_Data_Store::get_logs_table_name();

            $alias_log = 't' . $date_ind;
            $alias_posts = 'tp' . $date_ind;

            $parts_query = "SELECT count(`rating_id`) FROM {$table_name} as {$alias_log}";
            $parts_query .= " INNER JOIN {$wpdb->posts} as {$alias_posts} ON {$alias_log}.post_id = {$alias_posts}.ID";
            $parts_query .= " WHERE SUBSTRING(`timestamp`, 1, %d) = %s AND post_type = %s";

            $query_params[] = self::$date_len;
            $query_params[] = $date['value'];
            $query_params[] = $post_type;

            return $parts_query;
        }

        public function get_totals($post_type = '')
        {
            if ( empty($this->date_ranges ) ) {
                return [];
            }

            global $wpdb;

            $query = 'SELECT ';
            $query_params = [];

            foreach ($this->date_ranges as $i => $date) {

                if ( empty($date['value']) ) {
                    continue;
                }

                if ( empty($post_type) ) {
                    $query_part = $this->make_total_query_part($date, $i,$query_params);
                } else {
                    $query_part = $this->make_cpt_query_part($date, $i, $post_type, $query_params);
                }

                if ( 0 < $i ) {
                    $query .= ', ';
                }

                $query .= '(' . $query_part . ') as %s';

                $label_date = new \DateTime($date['date']);
                $query_params[] = $label_date->format('M Y');

            }

            $sql = $wpdb->prepare($query, $query_params);
            $r = $wpdb->get_results($sql, ARRAY_A);

            if ( ''  !== $wpdb->last_error ) {
                $wpdb->print_error();
            }

            return $r;
        }
    }