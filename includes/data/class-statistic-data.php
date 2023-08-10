<?php
    namespace MPRating\Data;

    class Statistic_Data
    {
        public static function get_ranges()
        {
            return [
                'year'  => __('Year', 'mpr-likebtn'),
                'month' => __('Month', 'mpr-likebtn'),
            ];
        }

        public static function get_start_date()
        {
            global $wpdb;

            $table_name = Logs_Data_Store::get_logs_table_name();

            $select = "SELECT CAST( `timestamp` AS date) as 'date' FROM {$table_name} ORDER BY `date` ASC ";
            $r = $wpdb->get_var($select);

            if ( ''  !== $wpdb->last_error ) {
                return '';
            }

            return $r;
        }

    }