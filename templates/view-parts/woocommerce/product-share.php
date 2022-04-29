<?php
define('WEBSITE_NAME', '');

global $post;
$post_id = $post->ID;
$permalink = get_permalink($post_id);
$image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'original');
$title = get_the_title($post_id);
$summary = get_the_excerpt($post_id);

?>
<section class="product-share">

    <span><?php _e('Share with', LANG_TEXTDOMAIN) ?>:</span>

    <?php $fb_url = 'http://www.facebook.com/share.php?u=' . urlencode($permalink) . '&title=' . urlencode($title); ?>
    <a href="<?php echo $fb_url; ?>" class="icon__share icon__share--facebook" target="_blank"><span
            style="display: none">FB</span><i class="fab fa-facebook-f"></i></a>

    <?php $tw_url = 'http://twitter.com/share?text=' . urlencode($title) . '&url=' . urlencode($permalink) . '&via=' . WEBSITE_NAME; ?>
    <a href="<?php echo $tw_url; ?>" class="icon__share icon__share--twitter" target="_blank"><span
            style="display: none">TW</span><i class="fab fa-twitter"></i></a>

    <?php if(!empty($image)) : ?>
    <?php $pt_url = 'http://pinterest.com/pin/create/button/?url=' . urlencode($permalink) . '&description=' . urlencode($title) . '&media=' . urlencode($image[0]); ?>
    <a data-pin-do="buttonPin" data-pin-config="above" href="<?php echo $pt_url; ?>"
       class="icon__share icon__share--pinterest" target="_blank">
        <span style="display: none">Pin it</span><i class="fab fa-pinterest-p"></i></a>
    <?php endif; ?>

    <?php $tg_url = 'https://telegram.me/share/url?url=' . urlencode($permalink) . '&text=' . urlencode($title); ?>
    <a href="<?php echo $tg_url; ?>" class="icon__share icon__share--telegram" target="_blank">
        <span style="display: none">Telegram</span><i class="fab fa-telegram"></i></a>

    <a href="whatsapp://send?text=<?php echo urlencode('Guarda questo articolo:' . $permalink); ?>"
       data-action="share/whatsapp/share" class="icon__share icon__share--whatsapp"><span
            style="display: none">Whatsapp</span><i class="fab fa-whatsapp"></i></a>

    <!--<a href="javascript:if(window.print)window.print()"
       class="icon__share--nopopup icon__share--print"><span
                style="display: none">Stampa</span><i class="fa fa-print"></i></a>-->

    <a href="mailto:?subject=Guarda questo articolo su <?php echo WEBSITE_NAME; ?>: <?php echo $title; ?> &body=<?php echo $title; ?> | <?php echo urlencode($permalink); ?>"
       class="icon__share--nopopup icon__share--email" target="_blank"><span
            style="display: none">EMAIL</span><i class="far fa-envelope"></i></a>

</section>
