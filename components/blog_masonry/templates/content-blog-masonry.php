<div class="item <?php echo \Waboot\functions\get_option('blog_masonry_column_width','col-sm-4'); ?>">
    <div class="post-inner well">
        <a href="<?php the_permalink(); ?>">
            <?php
            if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
                the_post_thumbnail('medium');	}
            ?>
        </a>
        <small><?php the_time('j/m/Y') ?> | <?php the_category(', ') ?></small>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

        <?php the_excerpt(); ?>
    </div>
</div>