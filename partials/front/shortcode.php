<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( empty($post_id) ) {
    return;
}

if ( ! isset($classes) ) {
    $classes = 'mpr-button';
}

$post_rating = get_post_meta($post_id, 'mpr_score', 1);
?>
<span class="<?php echo esc_attr($classes); ?>"
      data-post="<?php echo $post_id; ?>"
      data-parent="<?php echo get_the_ID(); ?>"
>
    <div class="mpr-tooltip" >
        <p></p>
    </div>
    <span class="mpr-votes-number"><?php echo (int)$post_rating; ?></span>
    <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
        <path d="M7.9987 1.33337L10.0587 5.50671L14.6654 6.18004L11.332 9.42671L12.1187 14.0134L7.9987 11.8467L3.8787 14.0134L4.66536 9.42671L1.33203 6.18004L5.9387 5.50671L7.9987 1.33337Z" stroke="#5EB761" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</span>
