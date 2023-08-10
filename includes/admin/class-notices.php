<?php
    namespace MPRating\Admin;

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }

    class Notices
    {
        public static function init_hooks()
        {
            add_action( 'admin_notices', [self::class, 'show_error_notices'] );
            add_action( 'admin_notices', [self::class, 'show_success_notices'] );
        }

        public static function show_error_notices()
        {
            if ( isset($_GET['mpr-error']) ) {

                $msg = '';
                switch ($_GET['mpr-error']) {
                    case 'nonce':
                        $msg = __('Action failed. Probably your link was timed out. Please, refresh the page and retry.');
                        break;
                    case 'row-delete':
                        $msg = __('Row deletion was failed.');
                        break;
                    case 'recalculate':
                        $msg = __('Error. Please, check if post id is specified.');
                        break;
                    default: break;
                }

                if ( $msg ) {
                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>' . esc_html($msg ) . '</p>';
                    echo '</div>';
                }

            }
        }

        public static function show_success_notices()
        {
            if ( isset($_GET['mpr-success']) ) {

                $msg = '';
                switch ($_GET['mpr-success']) {
                    case 'row-delete':
                        $msg = __('Row deleted successfully.');
                        break;
                    case 'rows-delete':
                        $msg = __('Rows deleted successfully.');
                        break;
                    case 'recalculate':
                        $msg = __('Post rating updated successfully.');
                        break;
                    default: break;
                }

                if ( $msg ) {
                    echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>' . esc_html($msg ) . '</p>';
                    echo '</div>';
                }

            }
        }

    }