<?php if($settings['readmore']) : ?>
	<?php \Waboot\template_tags\the_trimmed_excerpt($settings['excerpt_length'],$settings['readmore_prefix']."<a href='".get_the_permalink()."' class='more-link'>".$settings['readmore_text']."</a>");?>
<?php else: ?>
	<?php \Waboot\template_tags\the_trimmed_excerpt($settings['excerpt_length'],false) ?>
<?php endif; ?>