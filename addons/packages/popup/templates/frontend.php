<?php
$popups = get_posts([
    'post_type'      => 'popup',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);

if (!$popups) return;

foreach($popups as $popup) {
    $global = get_field('global_popup', $popup->ID);

    if(!$global) {
        if (is_page() || is_single()) {
            $pages = get_field('pages', $popup->ID);
            $pages_exclude = get_field('pages_exclude', $popup->ID);
            if($pages) {
                if($pages_exclude && in_array(get_the_ID(), (array)$pages)) continue;
                if(!$pages_exclude && !in_array(get_the_ID(), (array)$pages)) continue;
            }

            $post_cats = get_field('post_category', $popup->ID);
            $post_cats_exclude = get_field('post_category_exclude', $popup->ID);
            if($post_cats && is_single()) {
                $terms = wp_get_post_terms(get_the_ID(), 'category', ['fields'=>'ids']);
                if($post_cats_exclude && array_intersect($terms, (array)$post_cats)) continue;
                if(!$post_cats_exclude && !array_intersect($terms, (array)$post_cats)) continue;
            }
        }
    }

    $show_after = get_field('show_after', $popup->ID) ?: 3;
    $max_views = get_field('max_views', $popup->ID) ?: 1;
    $width = get_field('width', $popup->ID) ?: 600;
    $position = get_field('position', $popup->ID) ?: 'center';
    ?>
    <div class="custom-popup"
         id="popup-<?php echo esc_attr($popup->ID);?>"
         data-delay="<?php echo esc_attr($show_after);?>"
         data-maxviews="<?php echo esc_attr($max_views);?>"
         data-width="<?php echo esc_attr($width);?>"
         data-position="<?php echo esc_attr($position);?>">
        <div class="custom-popup__overlay"></div>
        <div class="custom-popup__content">
            <?php echo apply_filters('the_content', $popup->post_content); ?>
            <button class="custom-popup__close">&times;</button>
        </div>
    </div>
    <?php
}
