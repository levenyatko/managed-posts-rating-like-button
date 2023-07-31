<?php
/**
 * Metabox to add post rating rows for Classic editor page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('MPR_Metabox') ) :

class MPR_Metabox
{
    public function __construct()
    {
        $this->define_hooks();
    }

    private function define_hooks()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post', [$this, 'add_rating']);

        add_action( 'enqueue_block_editor_assets', [$this, 'enqueue_scripts'] );

        add_action('init', [$this, 'register_post_meta'] );
    }

    public function register_post_meta()
    {
        $screens = self::get_screens();

        if ( empty($screens) ) {
            return;
        }

        foreach ($screens as $screen ) {

            register_post_meta($screen, 'mpr_score', [
                'show_in_rest' => true,
                'type' => 'string',
                'single' => true,
                'sanitize_callback' => 'absint',
                'auth_callback' => function () {
                    return current_user_can('manage_mpr_log');
                }
            ]);

            register_post_meta($screen, 'add_rating_to_post', [
                'type' => 'string',
                'single' => true,
                'sanitize_callback' => 'absint'
            ]);
        }
    }

    public function add_meta_box()
    {
        if ( ! current_user_can( 'manage_mpr_log' ) ) {
            return;
        }

        $screens = self::get_screens();

        if ( ! empty($screens) ) {

            add_meta_box('mpr_manually_add_values',
                __('MPRating', 'mpr-likebtn'),
                [ $this, 'show_add_rating_meta_box'],
                $screens,
                'side',
                'low',
                [
                    '__back_compat_meta_box' => true,
                ]
            );
        }
    }

    public function show_add_rating_meta_box( $post, $meta )
    {
        wp_nonce_field( 'mpr-likebtn-metabox', 'mpr_metabox_nonce' );

        $post_score = get_post_meta($post->ID, 'mpr_score', 1);
        $post_score = (int)$post_score;

        $fix_log_link = mpr_get_log_link($post->ID);

        ?>
        <div class="form-wrap">
            <h4><?php echo esc_html( sprintf( __('Rating: %s', 'mpr-likebtn'), $post_score ) ); ?></h4>
            <hr>
            <div class="form-field">
                <p><?php esc_html_e('Enter the number of likes you wish to add', 'mpr-likebtn') ?></p>
                <input type="number" id="add_rating_to_post" name="add_rating_to_post" value="0" />
                <p><?php esc_html_e('and update the post', 'mpr-likebtn') ?><br></p>
                <p><?php esc_html_e('You can enter negative number to decrease post rating.', 'mpr-likebtn') ?></p>
            </div>
            <a href="<?php echo esc_url($fix_log_link); ?>"><?php esc_html_e('View Log', 'mpr-likebtn') ?></a>
        </div>
        <?php
    }

    function add_rating( $post_id )
    {
        if ( ! isset( $_POST['add_rating_to_post'] ) || 0 === (int)$_POST['add_rating_to_post'] )
            return;

        if ( ! wp_verify_nonce( $_POST['mpr_metabox_nonce'], 'mpr-likebtn-metabox' ) )
            return;

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
            return;

        if( ! current_user_can( 'manage_mpr_log', $post_id ) )
            return;

        mpr_process_post_voting( $post_id, (int)$_POST['add_rating_to_post'], -1 );

    }

    public function enqueue_scripts()
    {
        if ( ! current_user_can( 'manage_mpr_log' ) ) {
            return;
        }

        $screens = self::get_screens();

        if ( ! empty($screens) ) {

            $screen = get_current_screen();

            if ( ! in_array( $screen->post_type, $screens ) ) {
                return;
            }

            wp_enqueue_script('mpr-admin-metabox', MPR_PLUGIN_URL . 'assets/build/metabox.js', array( 'wp-edit-post', 'wp-element', 'wp-components', 'wp-plugins'), null);
        }
    }

    public static function get_screens()
    {
        $screens = apply_filters('mpr_edit_meta_box_screens', []);
        return $screens;
    }

}

endif;
