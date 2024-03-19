<div class="footer__inner">
	<?php
		if (!empty($footerBlock)) {
			$blockContent = parse_blocks(get_post_field('post_content', $footerBlock));
			if (!empty($blockContent)) {
				echo render_block($blockContent[0]);
			}
		}
	?>
</div>
