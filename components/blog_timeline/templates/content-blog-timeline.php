<article>
    <div class="timeline-badge primary">
        <a><i class="glyphicon glyphicon-record" rel="tooltip" title="<?php the_date(); ?>"></i></a>
    </div>

    <div class="timeline-panel">
        <div class="timeline-heading">
            <small><?php the_time('j/m/Y') ?> | <?php the_category(', ') ?></small>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        </div>
        <div class="timeline-body">
            <div class="post-image">
                <a href="<?php the_permalink(); ?>">
                    <?php
                    if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
                        the_post_thumbnail('medium');
                    }
                    ?>
                </a>
            </div>
            <div class="post-excerpt">
                <?php the_excerpt(); ?>
            </div>
        </div>
    </div>
</article>
