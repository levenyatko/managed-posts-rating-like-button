<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    $page_title = get_admin_page_title();
    ?>
<div class="wrap mpr-logs--wrapper">
    <h1>
        <?php echo esc_html($page_title); ?>
    </h1>
    <hr>
    <div class="mpr-logs-table--wrapper">
        <?php
            if ( 0 < $filter_post_id ) {

                $page_title = __(' for: ', 'mpr-likebtn');
                $page_title .= get_the_title($filter_post_id);

                $update_url = add_query_arg([
                    'action' => 'mpr-recalculate'
                ]);

                $post_total = get_post_meta($filter_post_id, 'mpr_score',1);;
                ?>
                <h3><?php echo esc_html($page_title); ?></h3>
                <p>
                    <?php esc_html_e('Post total rating:', 'mpr-likebtn'); ?>
                    <strong><?php echo (int)$post_total; ?></strong>
                    <a class="page-title-action" href="<?php echo esc_url($update_url); ?>" >
                        <?php esc_html_e('Update calculated rating', 'mpr-likebtn'); ?>
                    </a>
                </p>
                <p>
                    <a href="<?php echo esc_url( admin_url('/admin.php?page=mpr-plugin-page') ); ?>">
                        <?php esc_html_e('Show Log for all posts', 'mpr-likebtn'); ?>
                    </a>
                </p>
                <?php
            }
        ?>
        <form method="post" id="posts-filter">
            <?php
                $table->prepare_items();
                $table->display();
            ?>
        </form>
    </div>
</div>