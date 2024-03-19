<div class="footer__inner">
	<?php
		if (!empty($footerBlock)) {
			echo apply_filters('the_content', get_post_field('post_content', $footerBlock));
		}
		
	?>
</div>
