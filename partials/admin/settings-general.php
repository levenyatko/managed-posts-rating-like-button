<?php
/**
 * Displays plugin general settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wp_settings_sections;

?>
<div class="wrap">
    <h1><?php echo get_admin_page_title(); ?></h1>
    <?php settings_errors(); ?>
    <?php
        if ( ! empty($wp_settings_sections) ) {
            foreach ($wp_settings_sections as $section_id => $section) {
                if ( false === strpos($section_id, 'mpr_') ) {
                    continue;
                }
                ?>
                <form method="POST" action="options.php">
                    <?php

                    settings_fields( $section_id );
                    do_settings_sections( $section_id );

                    ?>
                    <?php submit_button(); ?>
                </form>
                <?php
            }
        }
    ?>
</div>