<?php
/**
 * Plugin Name:       Managed posts rating ★ Like button
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

    if ( ! defined( 'MPR_PLUGIN_FILE' ) ) {
        define( 'MPR_PLUGIN_FILE', __FILE__ );
    }

    if ( ! defined( 'MPR_PLUGIN_DIR' ) ) {
        define('MPR_PLUGIN_DIR', plugin_dir_path(__FILE__));
    }

    if ( ! defined( 'MPR_PLUGIN_URL' ) ) {
        define('MPR_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    require_once MPR_PLUGIN_DIR . 'includes/autoload.php';
    require_once MPR_PLUGIN_DIR . 'includes/functions.php';

    function MPR_Like_Btn()
    {
        return MPRating\Plugin::instance();
    }

    MPR_Like_Btn();
