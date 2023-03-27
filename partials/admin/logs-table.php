<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    $log_th = apply_filters('mpr_log_table_headings', []);

    $page_title = get_admin_page_title();
    if ( 0 < $filter_post_id ) {
        $page_title .= __(' for: ', 'mpr-likebtn');
        $page_title .= get_the_title($filter_post_id);
    }

    $allowed_cell_tags = [
        'td' => [
            'class' => []
        ],
        'a'  => [
            'href'  => [],
            'title' => [],
            'class' => []
        ],
        'span' => [
            'class' => []
        ]
    ];

    $allowed_cell_tags = apply_filters('mpr_allowed_settings_cell_tags', $allowed_cell_tags);
?>
<div class="wrap mpr-logs--wrapper">
    <h1 class="wp-heading-inline">
        <?php echo esc_html($page_title); ?>
    </h1>
    <?php
        if ( 0 < $filter_post_id ) {
            $update_url = add_query_arg([
                'mpr-log-action' => 'recalculate'
            ]);

            $post_total = get_post_meta($filter_post_id, 'mpr_score',1);;
            ?>
            <a class="page-title-action" href="<?php echo esc_url($update_url); ?>" >
                <?php esc_html_e('Update calculated rating', 'mpr-likebtn'); ?>
            </a>
            <p>
                <?php esc_html_e('Post total rating:', 'mpr-likebtn'); ?>
                <strong><?php echo (int)$post_total; ?></strong>
            </p>
            <p>
                <a href="<?php echo esc_url( admin_url('/admin.php?page=mpr-plugin-page') ); ?>">
                    <?php esc_html_e('Back to Log for all posts', 'mpr-likebtn'); ?>
                </a>
            </p>
            <?php
        }
    ?>
    <div class="mpr-logs-table--wrapper">
        <?php
        if ( ! $logs_data ) {
            ?>
            <p><?php esc_html_e('No data to display', 'mpr-likebtn'); ?></p>
            <?php
            return;
        }
        ?>
        <table class="wp-list-table widefat striped table-view-list">
            <?php if ( ! empty($log_th) ) { ?>
                <tr>
                    <?php foreach ($log_th as $heading) { ?>
                        <th><?php echo esc_html($heading); ?></th>
                    <?php } ?>
                </tr>
            <?php } ?>
            <?php
                foreach($logs_data as $row) {
                    $row_data = apply_filters('mpr_log_table_row', (array)$row );
                    ?>
                    <tr>
                        <?php
                        foreach ($row_data as $cell) {
                            $cell_attributes = [];

                            if ( ! empty($cell['attrs']) ) {
                                foreach ($cell['attrs'] as $attr => $val) {
                                    $cell_attributes[] = esc_attr($attr) . '="' . esc_attr($val) . '"';
                                }
                            }

                            $cell_attributes_string = implode(' ', $cell_attributes);

                            echo wp_kses( sprintf('<td %s>%s</td>', $cell_attributes_string, $cell['value']),
                                $allowed_cell_tags);
                        }
                        ?>
                    </tr>
                    <?php
                }
            ?>
        </table>
        <?php
            if ( $num_of_pages > 0 ) {
                ?>
                <div class="tablenav wpr-log-pagination">
                    <div class="tablenav-pages">
                        <div class="pagination-links">
                            <?php
                                echo paginate_links( [
                                    'base'      => add_query_arg( 'pagenum', '%#%' ),
                                    'format'    => '',
                                    'prev_text' => __( '&laquo;'),
                                    'next_text' => __( '&raquo;'),
                                    'total'     => $num_of_pages,
                                    'current'   => $pagenum
                                ] );
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        ?>
    </div>
</div>