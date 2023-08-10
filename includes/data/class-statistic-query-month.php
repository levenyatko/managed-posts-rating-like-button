<?php
    namespace MPRating\Data;

    defined( 'ABSPATH' ) || exit;

    class Statistic_Query_Month extends Statistic_Query
    {
        public function __construct($selected_period = '')
        {
            parent::__construct($selected_period);
        }

        protected function prepare_dates_array($period)
        {
            $queried_dates = [];

            $start_date = new \DateTime($period . '-01');
            $end_date   = new \DateTime($period . '-01');
            $end_date->modify('+1 month');

            $interval      = new \DateInterval("P5D");
            $date_period   = new \DatePeriod($start_date, $interval, $end_date);

            foreach ($date_period as $dt) {
                $queried_dates[] = [
                    'value' => $dt->format('Y-m-d'),
                    'date'  => $dt->format('Y-m-d'),
                ];
            }

            if ( $dt->format('Y-m-d') != $end_date->format('Y-m-d') ) {
                $queried_dates[] = [
                    'value' => $end_date->format('Y-m-d'),
                    'date'  => $end_date->format('Y-m-d'),
                ];
            }

            return $queried_dates;
        }

        protected function make_total_query_part($date_start, $date_end, $date_ind, &$query_params )
        {
            $table_name = Logs_Data_Store::get_logs_table_name();

            $alias = 't' . $date_ind;

            $parts_query = "SELECT count(`rating_id`) FROM {$table_name} as {$alias} WHERE `timestamp` >= %s AND `timestamp` < %s";

            $query_params[] = $date_start;
            $query_params[] = $date_end;

            return $parts_query;
        }

        protected function make_cpt_query_part($date_start, $date_end, $date_ind, $post_type, &$query_params)
        {
            global $wpdb;

            $table_name = Logs_Data_Store::get_logs_table_name();

            $alias_log = 't' . $date_ind;
            $alias_posts = 'tp' . $date_ind;

            $parts_query = "SELECT count(`rating_id`) FROM {$table_name} as {$alias_log}";
            $parts_query .= " INNER JOIN {$wpdb->posts} as {$alias_posts} ON {$alias_log}.post_id = {$alias_posts}.ID";
            $parts_query .= " WHERE `timestamp` >= %s AND `timestamp` < %s AND post_type = %s";

            $query_params[] = $date_start;
            $query_params[] = $date_end;
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

            $ranges_count = count($this->date_ranges);

            for ($i = 0; $i < $ranges_count-1; $i++) {

                $date_start = $this->date_ranges[ $i ];
                $date_end = $this->date_ranges[ $i+1 ];

                $date_start_obj = new \DateTime( $date_start['date'] );
                $date_end_obj   = new \DateTime( $date_end['date'] );

                if ( empty($date_start_obj) || empty($date_end_obj) ) {
                    continue;
                }

                if ( empty($post_type) ) {
                    $query_part = $this->make_total_query_part($date_start['value'], $date_end['value'], $i, $query_params);
                } else {
                    $query_part = $this->make_cpt_query_part($date_start['value'], $date_end['value'], $i, $post_type, $query_params);
                }

                if ( 0 < $i ) {
                    $query .= ', ';
                }

                $query .= '(' . $query_part . ') as %s';

                $date_end_obj->modify('-1 day');
                $query_params[] = $date_start_obj->format('d M') . ' - ' . $date_end_obj->format('d M');

            }

            $sql = $wpdb->prepare($query, $query_params);
            $r = $wpdb->get_results($sql, ARRAY_A);

            if ( ''  !== $wpdb->last_error ) {
                $wpdb->print_error();
            }

            return $r;
        }
    }