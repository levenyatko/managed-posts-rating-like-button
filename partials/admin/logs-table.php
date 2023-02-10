<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    $log_th = apply_filters('mpr_log_table_headings', []);
?>
<div class="wrap mpr-logs--wrapper">
    <h1 class="wp-heading-inline">
        <?php echo get_admin_page_title(); ?>
        <?php
        if ( 0 < $filter_post_id ) {
            echo __('for: ', 'mpr-likebtn');
            echo get_the_title($filter_post_id);
        }
        ?>
    </h1>
    <?php
    if ( 0 < $filter_post_id ) {
        $update_url = add_query_arg([
            'mpr-log-action' => 'recalculate'
        ]);

        $post_total = get_post_meta($filter_post_id, 'mpr_score',1);;

        echo '<a class="page-title-action" href="' . $update_url . '" >' . __('Update calculated rating', 'mpr-likebtn') . '</a>';
        echo '<p>' . __('Post total rating:', 'mpr-likebtn') . '<strong> ' . $post_total . ' </strong></p>';
	    echo '<p><a href="' . admin_url('/admin.php?page=mpr-plugin-page') . '">' . __('Back to Log for all posts', 'mpr-likebtn') . '</a></p>';
    }
    ?>
    <div class="mpr-logs-table--wrapper">
        <?php
        if ( ! $logs_data ) {
            ?>
            <p><?php _e('No data to display', 'mpr-likebtn'); ?></p>
            <?php
            return;
        }
        ?>
        <table class="wp-list-table widefat striped table-view-list">
            <?php if ( ! empty($log_th) ) { ?>
                <tr>
                    <?php foreach ($log_th as $heading) { ?>
                        <th><?php echo $heading; ?></th>
                    <?php } ?>
                </tr>
            <?php } ?>
            <?php
                foreach($logs_data as $row) {

                    $row_data = apply_filters('mpr_log_table_row', (array)$row );

                    echo '<tr>';
                    foreach ($row_data as $cell) {
                        $cell_attributes = '';

                        if ( ! empty($cell['attrs']) ) {
                            foreach ($cell['attrs'] as $attr => $val) {
                                $cell_attributes .= $attr . '="' . esc_attr($val) . '" ';
                            }
                        }

                        echo sprintf('<td %s>%s</td>', $cell_attributes, $cell['value']);
                    }

                    echo '</tr>';
                }
            ?>
        </table>
        <?php
        $page_links = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;'),
            'next_text' => __( '&raquo;'),
            'total' => $num_of_pages,
            'current' => $pagenum
        ) );

        if ( $page_links ) {
            echo '<div class="tablenav wpr-log-pagination"><div class="tablenav-pages"><span class="pagination-links">' . $page_links . '</span></div></div>';
        }
        ?>
    </div>
</div>