<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    if ( ! isset($selected_period) ) {
        $selected_period = '';
    }

    if ( ! isset($selected_range) ) {
        $selected_range = '';
    }

    ?>
<div class="wrap">
    <h1><?php echo get_admin_page_title(); ?></h1>
    <hr>
    <div class="mpr-chart--wrapper">
        <div class="mpr-chart__filters">
            <div class="mpr-chart--filter-row">
                <form method="get" id="mpr-chart-filter-form" action="<?php echo esc_attr( admin_url('/admin.php')) ?>">
                    <input type="hidden" name="page" value="mpr-stat-page">
                    <input type="hidden" id="mpr-stat-value" value="<?php echo esc_attr($selected_period); ?>">
                    <label for="mpr-chart-filter-period">
                        <?php esc_html_e('Period to show data:', 'mpr-likebtn'); ?>
                        <select id="mpr-chart-filter-period" name="mpr-stat-key">
                            <?php
                                if ( isset($ranges) ) {
                                    foreach ($ranges as $range => $label) {
                                        $attrs = '';
                                        if ($selected_range == $range) {
                                            $attrs = ' selected';
                                        }
                                        echo sprintf('<option value="%s"%s>%s</option>', $range, $attrs, esc_html($label));
                                    }
                                }
                            ?>
                        </select>
                    </label>
                </form>
            </div>
            <div class="mpr-chart__navigation" >
                <?php
                    if ( isset($navigation_array) ) {
                        foreach ($navigation_array as $key => $item ) {
                            ?>
                            <div class="mpr-chart__navigation--<?php echo esc_attr($key) ?>">
                                <?php
                                    if ( ! empty($item['text']) ) {
                                        if ('current' == $key) {
                                            echo esc_html($item['text']);
                                        } else {
                                            $link = \MPRating\Admin\Pages\Chart_Page::get_nav_link($selected_range, $item['value']);
                                            echo sprintf('<a href="%s">%s</a>', esc_attr($link), esc_html($item['text']));
                                        }
                                    }
                                ?>
                            </div>
                            <?php
                        }
                    }
                ?>
            </div>
        </div>
        <span id="mpr-loader"></span>
        <canvas id="mpr-chart"></canvas>
    </div>
</div>

