<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('MPR_Columns') ) :

class MPR_Columns
{
    public function __construct()
    {
        $this->define_hooks();
    }

    private function define_hooks()
    {
        $screens = mpr_get_option( 'post_types', 'mpr_general_section', [ 'post' => 'post' ] );

        $screens = apply_filters('mpr_column_screens_display', $screens);

        if ( ! empty($screens) ) {
            foreach ( $screens as $screen ) {
                // add rating column to posts list
                add_filter( 'manage_' . $screen . '_posts_columns', [ $this, 'add_rating_column' ] );
                add_action( 'manage_' . $screen . '_posts_custom_column', [ $this, 'show_rating_column' ] );

                // make rating column sortable
                add_filter( 'manage_edit-' . $screen . '_sortable_columns', [ $this, 'rating_sortable_columns' ] );
            }
        }
        add_action( 'pre_get_posts', [$this, 'custom_rating_orderby'] );

    }

	public function add_rating_column($columns)
    {
		$columns['mpr_column'] = __( 'Rating', 'mpr-likebtn' );

		return $columns;
	}

	public function show_rating_column( $column )
    {
		global $post;

		switch ( $column ) {

			case 'mpr_column' :
				$v = get_post_meta($post->ID, 'mpr_score',1);

				echo esc_html( intval($v) );
                ?>
                <div class="row-actions">
                    <span class="0">
                        <a href="<?php echo esc_url( mpr_get_log_link($post->ID) ); ?>" >
                            <?php esc_html_e('View Log', 'mpr-likebtn'); ?>
                        </a>
                    </span>
                </div>
                <?php

				break;

		}
	}

	public function rating_sortable_columns($columns)
    {
		$columns['mpr_column'] = 'mprating';
		return $columns;
	}

	public function custom_rating_orderby( $query )
    {
		if( ! is_admin() )
			return;

		$orderby = $query->get( 'orderby');

		if ( 'mprating' == $orderby ) {
			$query->set('meta_key', 'mpr_score');
			$query->set('orderby', 'meta_value_num');
		}
	}

    public function add_default_rating_value($post_ID)
    {
        if ( ! wp_is_post_revision($post_ID) ) {
            add_post_meta($post_ID, 'mpr_score', 0, true);
        }
    }

}

endif;
