<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry__wrapper">
        <?php
            if(has_post_thumbnail()){
	            $thumbnailView = new \WBF\components\mvc\HTMLView('templates/view-parts/entry-thumbnail.php');
	            $thumbnailPreset = apply_filters('waboot/layout/entry/thumbnail/preset','thumbnail');
	            $thumbnailClasses = apply_filters('waboot/layout/entry/thumbnail/class','img-responsive');
	            $thumbnailView->display([
		            'thumbnail_html' => get_the_post_thumbnail( $post->ID, $thumbnailPreset, array( 'class' => $thumbnailClasses, 'title' => get_the_title().' thumbnail' ) ),
                    'thumbnail_src' => \WBF\components\utils\Posts::get_post_thumbnail_src($post->ID,$thumbnailPreset)
                ]);
            }
        ?>
        <div class="entry__content">
            <?php do_action( 'waboot/entry/header', 'list' ); ?>
            <?php
                $contentView = new \WBF\components\mvc\HTMLView('templates/view-parts/entry-content.php');
                $contentView->display();
            ?>
            <?php wp_link_pages(); ?>
            <?php do_action( 'waboot/entry/footer' ); ?>
        </div>
    </div>
</article>
