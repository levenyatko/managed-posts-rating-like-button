<?php
add_filter('mpr_edit_meta_box_screens', 'mpr_supported_post_types');

function mpr_get_option( $option, $section, $default = '' )
{
    $options = get_option( $section );

    if ( isset( $options[ $option ] ) ) {
        return $options[ $option ];
    }
    return $default;
}


function mpr_get_ipaddress()
{
    return wp_hash( get_ipaddress() ) ;
}

if ( ! function_exists( 'get_ipaddress' ) ) {
    function get_ipaddress() {
        foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                foreach ( explode( ',', $_SERVER[$key] ) as $ip ) {
                    $ip = trim( $ip );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
                        return esc_attr( $ip );
                    }
                }
            }
        }
    }
}

function mpr_process_post_voting( $post_id, $add_rating, $parent_id )
{
    $post_custom = get_post_custom($post_id);
    $post_ratings_score = ! empty( $post_custom['mpr_score'] ) ? (int) $post_custom['mpr_score'][0] : 0;

    $post_ratings_score += $add_rating;

    if( $post_ratings_score < 0 ){
        $post_ratings_score = 0;
    }

    MPR_Like_Btn()->logs_data->add_row($post_id, $add_rating, $parent_id );

    update_post_meta($post_id, 'mpr_score', $post_ratings_score);

    return $post_ratings_score;
}

function mpr_get_log_link($post_ID)
{
    return admin_url('/admin.php?page=mpr-plugin-page&filter_post_id=' . $post_ID);
}

function mpr_check_if_post_max_rated($post_id)
{
    if (0 == $post_id) {
        return true;
    }

    $max_user_votings = mpr_get_max_voting_count();

    $user_votings = mpr_current_user_voted($post_id);

    if ( $user_votings < $max_user_votings) {
        return false;
    }

    return true;

}

function mpr_get_max_voting_count()
{
    return (int)mpr_get_option( 'max_voting_count', 'mpr_general_section', 1);
}

function mpr_current_user_voted($post_id)
{
    global $user_ID;

    if ($post_id == 0) {
        return 0;
    }

    $allow_to_vote = mpr_get_option( 'like_method', 'mpr_general_section' );

    if ( ! empty( $allow_to_vote ) && 'logged' == $allow_to_vote ) {
        // search user votes by id
        $voting_count = MPR_Like_Btn()->logs_data->get_post_rating_by_user_id($post_id, $user_ID );
    } else {
        $voting_count = MPR_Like_Btn()->logs_data->get_post_rating_by_user_ip($post_id, mpr_get_ipaddress() );
    }

    return $voting_count;
}

function mpr_check_possibility_to_vote($post_id)
{
    $is_vote_allowed = false;

    $post = get_post($post_id);
    $display = mpr_is_btn_display_enabled( $post );

    if ( ! $display ) {
        return apply_filters('mpr_is_user_can_vote_unsupported', $is_vote_allowed);
    }

    if ( current_user_can('mpr_freely_likes') ) {
        $is_vote_allowed = true;
    } else {

        $allow_to_vote = mpr_get_option( 'like_method', 'mpr_general_section' );

        // if only logged users allowed to vote
        if ( ! empty( $allow_to_vote ) && 'logged' == $allow_to_vote ) {
            if ( is_user_logged_in() ) {
                $is_vote_allowed = true;
            }
        } else {
            // allow vote to all users
            $is_vote_allowed = true;
        }
    }

    return apply_filters('mpr_is_user_can_vote', $is_vote_allowed);
}

function mpr_supported_post_types($screens)
{
    return mpr_get_option( 'post_types', 'mpr_general_section', [ 'post' => 'post' ] );
}

function mpr_is_post_type_supported($post_type)
{
    $screens = apply_filters('mpr_edit_meta_box_screens', []);

    if ( empty($screens)) {
        return false;
    }

    return in_array($post_type, $screens);
}

function mpr_is_btn_display_enabled($post)
{
    $is_post_type_supported = mpr_is_post_type_supported( $post->post_type );

    if ( ! $is_post_type_supported ) {
        $button_display = mpr_get_option( 'disabled_btn_display', 'mpr_general_section', 'hide' );
        if ( 'show_enabled' != $button_display ) {
            return false;
        }
    }
    return  true;
}

function mpr_search_template_path($file_name, $folder = 'front')
{
    $file_path = $folder . '/' . $file_name . '.php';

    $template_name = 'mpr/' . $file_path;
    $template_loc = locate_template([$template_name]);

    $template_def = 'partials/' . $file_path;

    return ( $template_loc != '' && file_exists($template_loc) ) ? $template_loc : MPR_PLUGIN_DIR.$template_def;
}

function mpr_button($atts)
{
    $attributes = shortcode_atts( ['id' => 0, 'disabled' => false, 'return' => true ], $atts );

    if ( (int) $attributes['id'] > 0 ) {
        $ratings_id = (int)$attributes['id'];
    } else {
        global $post;
        $ratings_id = $post->ID;
    }

    $voted_post = get_post($ratings_id);
    $is_post_type_supported = mpr_is_post_type_supported( $voted_post->post_type );

    if ( ! $is_post_type_supported ) {
        $button_display = mpr_get_option( 'disabled_btn_display', 'mpr_general_section', 'hide' );
        if ( 'hide' == $button_display ) {
            echo apply_filters('the_mpr_button_shortcode_hidden', '');
            return;
        } elseif ( 'show_disabled' == $button_display ) {
            $attributes['disabled'] = true;
        }
    }

    // if user voted the post
    $user_voted = mpr_current_user_voted($ratings_id);


    // if we allowed voting
    if ( $attributes['disabled'] ) {
        $html = mpr_get_button_template($ratings_id, $user_voted, true );
    } else {

        // If User Is Allowed To Vote
        $allowed = mpr_check_possibility_to_vote($ratings_id);

        $html = mpr_get_button_template($ratings_id, $user_voted, !$allowed );

    }

    if ( $attributes['return'] ) {
        return $html;
    }

    echo apply_filters('the_mpr_button_shortcode', $html);
}

function mpr_get_button_template($post_id, $user_voted, $disabled = false)
{
    $classes = 'mpr-button ';
    if ( $disabled ) {
        $classes .= 'mpr-button__disabled';
    } else {
        $classes .= 'mpr-button__active';
    }

    if ( $user_voted ) {
        $classes .= ' mpr-voted';
    }

    $filepath = mpr_search_template_path('shortcode');

    ob_start();
    include $filepath;
    $rating_html = ob_get_contents();
    ob_end_clean();

    return $rating_html;
}

/**
* @param $post_id
* @param array $args {
*     The array to filter post voting. All arguments are optional and may be empty.
*
*     @type date $start  The start date from which the post rating is calculated.
*     @type date $end    The end date to which the post rating is calculated.
* }
*
* @return int
*/
function mpr_get_post_rating($post_id, $args = [])
{
    $rating = 0;

    $plugin = MPR_Like_Btn();

    if ( ! isset($plugin->logs_data) ) {
        return $rating;
    }

    $post_obj = get_post($post_id);
    if ( empty($post_obj) ) {
        return $rating;
    }

    // get post rating on specific date/datetime
    if ( ! empty($args['start']) || ! empty($args['end']) ) {

        $date_start = '';
        $date_end = '';

        if ( ! empty($args['start'])) {
            $date = new DateTime($args['start']);
            if ( ! empty($date)) {
                $date_start = $date->format('Y-m-d H:i:s');
            }
        }

        if ( ! empty($args['end'])) {
            $date = new DateTime($args['end']);
            if ( ! empty($date)) {
                $date_end = $date->format('Y-m-d H:i:s');
            }
        }

        $rating = $plugin->logs_data->get_post_rating($post_id, $date_start, $date_end);

    } else {
        $rating = (int)get_post_meta($post_id, 'mpr_score', 1);
    }

    return $rating;
}