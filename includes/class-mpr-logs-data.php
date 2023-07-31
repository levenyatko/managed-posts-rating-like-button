<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('MPR_Logs_Data') ) :

class MPR_Logs_Data
{
    private $logs_table_name;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct()
    {
        global $wpdb;
        $this->logs_table_name = $wpdb->prefix . 'mpr_rating_log';
	}

	public function create_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $create_sql = "CREATE TABLE {$this->logs_table_name} (".
            "rating_id bigint(20) NOT NULL auto_increment, ".
            "post_id bigint(20) NOT NULL, ".
            "post_title VARCHAR(100) NOT NULL ,".
            "ref_post_id bigint(20), ".
            "ref_post_title VARCHAR(100),".
            "rating INT(2) NOT NULL,".
            "timestamp TIMESTAMP NOT NULL ,".
            "voting_ip VARCHAR(40) NOT NULL ,".
            "user_id bigint(20) NOT NULL default '0',".
            "PRIMARY KEY (rating_id)".
            ") ".
            "$charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $create_sql );
    }

    public function delete_table()
    {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS {$this->logs_table_name}" );
    }

    public function get_data($page = 1, $per_page = 10, $post_id = 0)
    {
        global $wpdb;

        $offset = ($page-1)*$per_page;

        $query_params = [];

        $select = "SELECT * FROM {$this->logs_table_name}";

        if ( 0 < $post_id ) {
            $select .= " WHERE post_id = %d";
            $query_params[] = $post_id;
        }

        $select .= " ORDER BY timestamp DESC LIMIT %d OFFSET %d";
        $query_params[] = $per_page;
        $query_params[] = $offset;

        $sql = $wpdb->prepare($select, $query_params);

        $r = $wpdb->get_results($sql, ARRAY_A);

        if ( ''  !== $wpdb->last_error ) {
            $wpdb->print_error();
        }

        return $r;
    }

    public function get_post_rows_count( $post_id = 0 )
    {
        global $wpdb;

        $select = "SELECT count(rating_id) as c FROM {$this->logs_table_name}";

        if ( 0 < $post_id ) {
            $select .= ' WHERE post_id = ' . (int)$post_id;
        }

        $r = $wpdb->get_var($select);

        if ( ''  !== $wpdb->last_error ) {
            return 0;
        }

        return (int)$r;
    }

    public function add_row($post_id, $ratings_value, $parent_id )
    {
        global $wpdb, $user_ID;

        if ( 0 >= $parent_id ) {
            $parent_title = __('Manually', 'mpr-likebtn');
        } else {
            $parent_title = get_the_title($parent_id);
        }

        $query = "INSERT INTO {$this->logs_table_name} VALUES (%d, %d, %s, %d, %s, %d, %s, %s, %d )";
        $query_prepeared = $wpdb->prepare($query, 0, $post_id, get_the_title($post_id), $parent_id, $parent_title, $ratings_value, current_time('mysql'), mpr_get_ipaddress(), $user_ID);

        $wpdb->query($query_prepeared);

        if ( ''  === $wpdb->last_error ) {
            do_action( 'mpr_after_post_voted', $post_id, $ratings_value, $parent_id, $wpdb->insert_id );
        }
    }

    public function delete_row($row_id)
    {
        if ($row_id) {
            global $wpdb;

            $select = "SELECT * FROM {$this->logs_table_name} WHERE rating_id = %d";
            $query = $wpdb->prepare($select, (int)$row_id);
            $log_values = $wpdb->get_results($query);

            if ('' !== $wpdb->last_error) {
                return 0;
            }

            foreach ($log_values as $row) {
                $current_rating = get_post_meta($row->post_id, 'mpr_score', 1);

                if ( $current_rating ) {
                    $current_rating = $current_rating - $row->rating;
                    update_post_meta($row->post_id, 'mpr_score', $current_rating);
                }

                return $wpdb->delete( $this->logs_table_name, array( 'rating_id' => intval($row_id) ) );
            }

        }

        return 0;
    }

	public function update_calculated_rating($post_id)
	{
        if ( empty($post_id) ) {
            return;
        }

		global $wpdb;

		$select = "SELECT sum(rating) as c FROM {$this->logs_table_name} WHERE post_id =%d";
		$query = $wpdb->prepare($select, (int)$post_id);

		$r = $wpdb->get_var($query);

		update_post_meta($post_id, 'mpr_score', $r);

	}

    public function get_post_rating_by_user_id($post_id, $user_id)
    {
        global $wpdb;

        $query = "SELECT SUM(rating) FROM {$this->logs_table_name} WHERE post_id = %d AND user_id = %d";
        $prepared_query = $wpdb->prepare( $query, $post_id, $user_id );

        return $wpdb->get_var( $prepared_query );

    }

    public function get_post_rating_by_user_ip($post_id, $ip_hash)
    {
        global $wpdb;

        $query = "SELECT SUM(rating) FROM {$this->logs_table_name} WHERE post_id = %d AND voting_ip = %s";
        $prepared_query = $wpdb->prepare( $query, $post_id, $ip_hash );

        return $wpdb->get_var( $prepared_query );

    }
}

endif;
